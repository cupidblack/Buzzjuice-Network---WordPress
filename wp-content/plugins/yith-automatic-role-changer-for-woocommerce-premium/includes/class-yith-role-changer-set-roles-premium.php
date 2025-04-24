<?php
/**
 * This file belongs to the YIT Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH\AutomaticRoleChanger\Classes
 */

if ( ! defined( 'YITH_WCARC_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Set Roles Premium Class.
 *
 * @class      YITH_Role_Changer_Set_Roles_Premium
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     YITH <plugins@yithemes.com>
 */
if ( ! class_exists( 'YITH_Role_Changer_Set_Roles_Premium' ) ) {
	/**
	 * Class YITH_Role_Changer_Set_Roles_Premium
	 *
	 */
	class YITH_Role_Changer_Set_Roles_Premium extends YITH_Role_Changer_Set_Roles {
		/**
		 * Construct
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			parent::__construct();
			if ( defined( 'YITH_YWSBS_PREMIUM' ) ) {
				add_action( 'ywsbs_subscription_status_changed', array( $this, 'search_for_subscriptions' ), 10, 3 );
			}
			add_action( 'ywarc_schedule_remove_role', array( $this, 'remove_role' ), 10, 2 );
			add_action( 'wp_ajax_ywarc_force_apply_rules', array( $this, 'force_apply_rules' ) );
			add_action( 'woocommerce_view_order', array( $this, 'myaccount_show_gained_roles' ), 20 );
			add_action( 'ywarc_after_metabox_content', array( $this, 'after_metabox_content' ) );
		}

		/**
		 * Search for suscription when status changed.
		 *
		 * @param  mixed $id         Suscription ID.
		 * @param  mixed $old_status Old suscription status.
		 * @param  mixed $new_status New suscription status.
		 * @return void
		 */
		public function search_for_subscriptions( $id, $old_status, $new_status ) {
			$subscription = ywsbs_get_subscription( $id );
			if ( $subscription && isset( $subscription->order_id ) && $subscription->order_id ) {
				$order                         = wc_get_order( $subscription->order_id );
				$user_id                       = $order->get_user_id();
				$granted_rules                 = $order->get_meta( '_ywarc_rules_granted' );
				$valid_subscription_statuses   = apply_filters(
					'ywarc_valid_subscription_statuses',
					array( 'active', 'trial' )
				);
				$invalid_subscription_statuses = apply_filters(
					'ywarc_invalid_subscription_statuses',
					array( 'paused', 'pending', 'overdue', 'cancelled', 'expired', 'suspended' )
				);
				if ( ! $granted_rules ) {
					if ( in_array( $new_status, $valid_subscription_statuses, true ) ) {
						$rules = $this->get_rules();
						if ( $rules ) {
							$valid_rules_by_product_id = $this->search_valid_rules_by_product_id( $rules, $subscription->order_id );

							$valid_rules = $valid_rules_by_product_id;
							$valid_rules = $this->filter_replace_role_rules( $valid_rules, $user_id );

							if ( $valid_rules ) {
								$valid_rules = $this->schedule_roles( $valid_rules, $user_id );
								$this->send_emails( $valid_rules, $user_id, $subscription->order_id );
								$order->update_meta_data( '_ywarc_rules_granted', $valid_rules );
								$order->save();
							}
						}
					}
				} else {
					if ( in_array( $new_status, $invalid_subscription_statuses, true ) ) {
						$clean_granted_rules = true;
						foreach ( $granted_rules as &$rule ) {
							if ( 'cancelled' === $new_status ) {
								// If Subscription have been cancelled NOW.
								if ( current_time( 'timestamp' ) >= $subscription->get( 'end_date' ) ) { //phpcs:ignore
									$this->remove_role( $rule, $user_id );

									// Unschedule possible cron jobs.
									$activation_date = ! empty( $rule['activation_date'] ) ? $rule['activation_date'] : '';
									$expiration_date = ! empty( $rule['expiration_date'] ) ? $rule['expiration_date'] : '';
									unset( $rule['expiration_date'] );

									wp_unschedule_event( $activation_date, 'ywarc_schedule_add_role', array( $rule, $user_id ) );
									wp_unschedule_event( $expiration_date, 'ywarc_schedule_remove_role', array( $rule, $user_id ) );
								} else {
									$rule['order_id'] = $order->get_id();
									wp_schedule_single_event( $subscription->get( 'end_date' ), 'ywarc_schedule_remove_role', array( $rule, $user_id ) );
									$rule['expiration_date'] = $subscription->get( 'end_date' );
									$clean_granted_rules     = false;
								}
							} else {
								$this->remove_role( $rule, $user_id );

								// Unschedule possible cron jobs.
								if ( isset( $rule['activation_date'] ) && isset( $rule['expiration_date'] ) ) {
									$activation_date = $rule['activation_date'];
									$expiration_date = $rule['expiration_date'];
									// 'activate_date' and 'expiration_date' must be unset in order to unschedule the events.
									// When the events were scheduled, 'activation_date' and 'expiration_date' didn't exist.
									// The parameters must match the original ones which scheduled the event.
									unset( $rule['activation_date'] );
									unset( $rule['expiration_date'] );
									wp_unschedule_event( $activation_date, 'ywarc_schedule_add_role', array( $rule, $user_id ) );
									wp_unschedule_event( $expiration_date, 'ywarc_schedule_remove_role', array( $rule, $user_id ) );
								}
							}
						}
						if ( $clean_granted_rules ) {
							$order->update_meta_data( '_ywarc_rules_granted', '' );
						} else {
							$order->update_meta_data( '_ywarc_rules_granted', $granted_rules );
						}
						$order->save();
					}
				}
			}
		}

		/**
		 * Search_for_rules.
		 *
		 * @param  mixed $order_id Current order ID.
		 * @return void
		 */
		public function search_for_rules( $order_id ) {
			$order      = wc_get_order( $order_id );
			$user_id    = $order->get_user_id();
			$new_status = $order->get_status();
			// Check if the order has already granted roles.
			$granted_rules          = $order->get_meta( '_ywarc_rules_granted' );
			$valid_order_statuses   = apply_filters( 'ywarc_valid_order_statuses', array( 'completed', 'processing' ) );
			$invalid_order_statuses = apply_filters( 'ywarc_invalid_order_statuses', array( 'cancelled', 'refunded' ) );
			// If the order has not granted roles, get in.
			if ( ! $granted_rules ) {
				if ( in_array( $new_status, $valid_order_statuses, true ) ) {
					$rules = $this->get_rules();
					if ( $rules ) {
						$valid_rules_by_product_id  = $this->search_valid_rules_by_product_id( $rules, $order_id );
						$valid_rules_by_price_range = $this->search_valid_rules_by_price_range( $rules, $order_id );
						$valid_rules_by_taxonomy    = $this->search_valid_rules_by_taxonomy( $rules, $order_id );

						$valid_rules = array_merge(
							$valid_rules_by_product_id,
							$valid_rules_by_price_range,
							$valid_rules_by_taxonomy
						);

						$valid_rules = $this->filter_rules( $valid_rules, $user_id );
						$valid_rules = $this->filter_replace_role_rules( $valid_rules, $user_id );

						if ( $valid_rules ) {
							$valid_rules = $this->schedule_roles( $valid_rules, $user_id );
							$this->send_emails( $valid_rules, $user_id, $order_id );
							$order->update_meta_data( '_ywarc_rules_granted', $valid_rules );
							$order->save();
						}
					}
				}
			} else {
				if ( in_array( $new_status, $invalid_order_statuses, true ) ) {
					foreach ( $granted_rules as $rule ) {
						$this->remove_role( $rule, $user_id );

						// Unschedule possible cron jobs.
						if ( isset( $rule['activation_date'] ) && isset( $rule['expiration_date'] ) ) {
							$activation_date = $rule['activation_date'];
							$expiration_date = $rule['expiration_date'];
							// 'activate_date' and 'expiration_date' must be unset in order to unschedule the events.
							// When the events were scheduled, 'activation_date' and 'expiration_date' didn't exist.
							// The parameters must match the original ones which scheduled the event.
							unset( $rule['activation_date'] );
							unset( $rule['expiration_date'] );
							wp_unschedule_event( $activation_date, 'ywarc_schedule_add_role', array( $rule, $user_id ) );
							wp_unschedule_event( $expiration_date, 'ywarc_schedule_remove_role', array( $rule, $user_id ) );
						}
					}
					$order->update_meta_data( '_ywarc_rules_granted', '' );
					$order->save();
				}
			}
		}

		/**
		 * Search valid rules according to  price range
		 *
		 * @param  mixed $rules Set of rules.
		 * @param  mixed $order_id ID of current Order.
		 * @return mixed
		 */
		public function search_valid_rules_by_price_range( $rules, $order_id ) {
			$order       = wc_get_order( $order_id );
			$valid_rules = array();

			foreach ( $rules as $rule_id => $rule ) {
				$type = ! empty( $rule['radio_group'] ) ? $rule['radio_group'] : '';
				if ( 'overall' === $type || 'range' === $type ) {
					$amount = $order->get_total();
					if ( 'overall' === $type ) {
						$amount = wc_get_customer_total_spent( $order->get_user_id() );
					}

					if ( ! empty( $rule['price_range_from'] ) && ! empty( $rule['price_range_to'] ) ) {
						if ( $rule['price_range_from'] <= $amount && $rule['price_range_to'] >= $amount ) {
							$valid_rules[ $rule_id ] = $rule;
						}
					} elseif ( ! empty( $rule['price_range_from'] ) && empty( $rule['price_range_to'] ) ) {
						if ( $rule['price_range_from'] <= $amount ) {
							$valid_rules[ $rule_id ] = $rule;
						}
					} elseif ( empty( $rule['price_range_from'] ) && ! empty( $rule['price_range_to'] ) ) {
						if ( $rule['price_range_to'] >= $amount ) {
							$valid_rules[ $rule_id ] = $rule;
						}
					}
				}
			}
			return $valid_rules;
		}

		/**
		 * Search valid rules by taxonomy.
		 *
		 * @param  mixed $rules Rules.
		 * @param  mixed $order_id Current Order ID.
		 * @return mixed
		 */
		public function search_valid_rules_by_taxonomy( $rules, $order_id ) {
			$order       = wc_get_order( $order_id );
			$valid_rules = array();
			foreach ( $order->get_items() as $item ) {
				$product = wc_get_product( $item['product_id'] );
				if ( $product instanceof WC_Product ) {
					foreach ( $rules as $rule_id => $rule ) {
						if ( ! empty( $rule['categories_selected'] ) ) {
							$categories_selected = is_array( $rule['categories_selected'] ) ? $rule['categories_selected'] : explode( ',', $rule['categories_selected'] );
							$categories          = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );
							foreach ( $categories as $category ) {
								if ( in_array( $category, $categories_selected ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
									$valid_rules[ $rule_id ] = $rule;
								}
							}
						}
						if ( ! empty( $rule['tags_selected'] ) ) {
							$tags_selected = is_array( $rule['tags_selected'] ) ? $rule['tags_selected'] : explode( ',', $rule['tags_selected'] );
							$tags          = wp_get_post_terms( $product->get_id(), 'product_tag', array( 'fields' => 'ids' ) );
							foreach ( $tags as $tag ) {
								if ( in_array( $tag, $tags_selected ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
									$valid_rules[ $rule_id ] = $rule;
								}
							}
						}
					}
				}
			}
			return $valid_rules;
		}

		/**
		 * Remove rules by filter.
		 *
		 * @param  array $valid_rules Valid Rules.
		 * @param  int   $user_id Current user ID.
		 * @return array
		 */
		public function filter_rules( $valid_rules, $user_id ) {
			$user = new WP_User( $user_id );
			foreach ( $valid_rules as $rule_id => $rule ) {
				if ( ! empty( $rule['role_filter'] ) ) {
					foreach ( $rule['role_filter'] as $role_filter ) {
						if ( in_array( $role_filter, $user->roles, true ) ) {
							unset( $valid_rules[ $rule_id ] );
						}
					}
				}
			}
			return $valid_rules;
		}

		/**
		 * Schedule roles, adding a date with a corn.
		 *
		 * @param array $valid_rules Set of valid rules.
		 * @param int   $user_id Current User ID.
		 *
		 * @return array $valid_rules
		 */
		public function schedule_roles( $valid_rules, $user_id ) {
			foreach ( $valid_rules as &$rule ) {
				$date_from = empty( $rule['date_from'] ) ? '' : $rule['date_from'];

				if ( $date_from ) {
					/**
					 * A start date is set, schedule the  application of the roles
					 */

					$date_from_unix_time = strtotime( $date_from );

					// If the date has passed.
					if ( time() >= $date_from_unix_time ) {
						/**
						 * The role will be applied now
						 */
						$this->schedule_add_role( $rule, $user_id );
						$activation_date = time();
					} else {
						wp_schedule_single_event( $date_from_unix_time, 'ywarc_schedule_add_role', array( $rule, $user_id ) );
						$activation_date = $date_from_unix_time;
					}

					$expiration_date = $this->schedule_expiration( $rule, $user_id );
				} else {
					/**
					 * The role will be applied now
					 */

					$this->schedule_add_role( $rule, $user_id );
					$activation_date = time();
					$expiration_date = $this->schedule_expiration( $rule, $user_id );
				}
				$rule['activation_date'] = $activation_date;
				$rule['expiration_date'] = $expiration_date;
			}
			return $valid_rules;
		}

		/**
		 * Set the expiration of a certain rule.
		 *
		 * @param  mixed $rule The current rule.
		 * @param  int   $user_id THe current User ID.
		 * @return mixed
		 */
		public function schedule_expiration( $rule, $user_id ) {
			$date_from       = empty( $rule['date_from'] ) ? '' : $rule['date_from'];
			$date_to         = empty( $rule['date_to'] ) ? '' : $rule['date_to'];
			$duration        = empty( $rule['duration'] ) ? '' : $rule['duration'];
			$expiration_date = '';

			if ( $date_to ) {
				/**
				 * An end date is set, schedule the removal of the roles
				 */
				$date_to_timestamp   = strtotime( $date_to );
				$date_from_timestamp = strtotime( $date_from );

				if ( $duration ) {
					$date_to_timestamp = strtotime( sprintf( '+%d days', $duration ), $date_from_timestamp );
				}

				wp_schedule_single_event( $date_to_timestamp, 'ywarc_schedule_remove_role', array( $rule, $user_id ) );
				$expiration_date = $date_to_timestamp;

			} elseif ( $duration ) {
				$date_to_timestamp = strtotime( sprintf( '+%d days', $duration ) );

				wp_schedule_single_event( $date_to_timestamp, 'ywarc_schedule_remove_role', array( $rule, $user_id ) );
				$expiration_date = $date_to_timestamp;
			}
			return $expiration_date;
		}

		/**
		 * Remove role from an user.
		 *
		 * @param  mixed $rule The role selected.
		 * @param  mixed $user_id The current User ID.
		 * @return void
		 */
		public function remove_role( $rule, $user_id ) {
			if ( defined( 'YITH_YWSBS_PREMIUM' ) && ! empty( $rule['product_selected'] ) ) {
				$is_subscribed = false;
				$product       = wc_get_product( $rule['product_selected'] );
				if ( $product instanceof WC_Product && $product->exists() && ywsbs_is_subscription_product( $product ) ) {
					$user_subscriptions = YWSBS_Subscription_Helper()->get_subscriptions_by_user( $user_id );

					if ( ! empty( $user_subscriptions ) ) {
						foreach ( $user_subscriptions as $subscription_post ) {
							$subscription = ywsbs_get_subscription( $subscription_post->ID );
							if ( $subscription->get( 'product_id' ) === $product->get_id() ) {
								if ( 'active' === $subscription->get( 'status' ) || 'trial' === $subscription->get( 'status' ) ) {
									$is_subscribed = true;
									break;
								}
							}
						}
					}
				}
				if ( $is_subscribed ) {
					return;
				}
				if ( isset( $rule['order_id'] ) && ! empty( $rule['order_id'] ) ) {
					$order = wc_get_order( $rule['order_id'] );
					$order->update_meta_data( '_ywarc_rules_granted', '' );
					$order->save();
				}
			}

			$user = new WP_User( $user_id );

			if ( 'add' === $rule['rule_type'] && ! empty( $rule['role_selected'] ) ) {
				foreach ( $rule['role_selected'] as $role ) {
					$user->remove_role( $role );
				}
			} elseif ( 'replace' === $rule['rule_type'] && ! empty( $rule['replace_roles'] ) ) {
				$user->add_role( $rule['replace_roles'][0] );
				$user->remove_role( $rule['replace_roles'][1] );
			}
		}

		/** Apply rules by force. */
		public function force_apply_rules() {

			check_ajax_referer( 'force_apply_rules', 'security' );

			$find_all_orders = ! empty( $_POST['ywarc_find_all_orders'] ) ? sanitize_text_field( wp_unslash( $_POST['ywarc_find_all_orders'] ) ) : '';
			$date_type       = ! empty( $_POST['ywarc_date_type'] ) ? sanitize_text_field( wp_unslash( $_POST['ywarc_date_type'] ) ) : '';
			$from            = ! empty( $_POST['ywarc_from'] ) ? sanitize_text_field( wp_unslash( $_POST['ywarc_from'] ) ) : '';
			$to              = ! empty( $_POST['ywarc_to'] ) ? sanitize_text_field( wp_unslash( $_POST['ywarc_to'] ) ) : '';
			$unix_from       = strtotime( $from );
			$unix_to         = strtotime( $to );

			if ( $unix_from && $unix_to ) {
				if ( $unix_from > $unix_to ) {
					wp_send_json_error( 'from_date_gt_to_date_error' );
				}
			}

			if ( ! $find_all_orders ) {
				$search = '';
				if ( $from && $to ) {
					$search = $from . '...' . $to;
				} elseif ( $from && ! $to ) {
					$search = '>=' . $from;
				} elseif ( ! $from && $to ) {
					$search = '<=' . $to;
				} elseif ( ! $from && ! $to ) {
					$search = '';
				}

				if ( $search ) {
					$orders = wc_get_orders(
						array(
							'type'               => 'shop_order',
							'return'             => 'ids',
							'limit'              => -1,
							'order'              => 'ASC',
							'date_' . $date_type => $search,
						)
					);
				} else {
					$orders = wc_get_orders(
						array(
							'type'   => 'shop_order',
							'return' => 'ids',
							'limit'  => -1,
							'order'  => 'ASC',
						)
					);
				}
			} else {
				$orders = wc_get_orders(
					array(
						'type'   => 'shop_order',
						'return' => 'ids',
						'limit'  => -1,
						'order'  => 'ASC',
					)
				);
			}

			if ( $orders ) {
				foreach ( $orders as $order_id ) {
					$this->search_for_rules( $order_id );
				}
				wp_send_json_success();
			}
			wp_send_json_error( 'no_orders_processed' );
		}

		/**
		 * Get template of show_gained_roles
		 *
		 * @param  mixed $order_id CUrrent Order ID.
		 */
		public function myaccount_show_gained_roles( $order_id ) {
			wc_get_template(
				'myaccount/ywarc-gained-roles.php',
				array( 'order_id' => $order_id ),
				'',
				YITH_WCARC_TEMPLATE_PATH . '/'
			);
		}

		/**
		 * COntent added after metabox
		 *
		 * @param  mixed $rule Current rule.
		 */
		public function after_metabox_content( $rule ) {
			$wp_date_format = get_option( 'date_format' );
			if ( $rule ) {
				echo '<div class="ywarc_metabox_dates">';
				if ( isset( $rule['activation_date'] ) && $rule['activation_date'] ) {
					$activation_date = gmdate( $wp_date_format, $rule['activation_date'] );
					echo '<div class="ywarc_metabox_date_from">' .
						esc_html__( 'Activation date: ', 'yith-automatic-role-changer-for-woocommerce' ) . esc_attr( $activation_date ) .
						'</div>';
				} elseif ( isset( $rule['date_from'] ) && $rule['date_from'] ) {
					echo '<div class="ywarc_metabox_date_from">' .
						esc_html__( 'Activation date: ', 'yith-automatic-role-changer-for-woocommerce' ) . esc_attr( $rule['date_from'] ) . '</div>';
				} else {
					echo '<div class="ywarc_metabox_date_from">' .
						esc_html__( 'Activated at the time of purchase', 'yith-automatic-role-changer-for-woocommerce' ) .
						'</div>';
				}
				if ( isset( $rule['expiration_date'] ) && $rule['expiration_date'] ) {
					$expiration_date = gmdate( $wp_date_format, $rule['expiration_date'] );
					echo '<div class="ywarc_metabox_date_to">' .
						esc_html__( 'Valid until: ', 'yith-automatic-role-changer-for-woocommerce' ) . esc_attr( $expiration_date ) .
						'</div>';
				} elseif ( isset( $rule['date_to'] ) && $rule['date_to'] ) {
					echo '<div class="ywarc_metabox_date_to">' .
						esc_html__( 'Valid until: ', 'yith-automatic-role-changer-for-woocommerce' ) . esc_attr( $rule['date_to'] ) . '</div>';
				} else {
					echo '<div class="ywarc_metabox_date_to">' .
						esc_html__( 'Permanent role', 'yith-automatic-role-changer-for-woocommerce' ) . '</div>';
				}
				echo '</div>';
			}
		}
	}
}
