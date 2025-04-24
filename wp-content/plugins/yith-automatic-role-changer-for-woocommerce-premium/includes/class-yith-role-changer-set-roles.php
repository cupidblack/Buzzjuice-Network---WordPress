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
 * Set roles main class.
 *
 * @class      YITH_Role_Changer_Set_Roles
 * @since      Version 1.0.0
 * @author     YITH <plugins@yithemes.com>
 */

if ( ! class_exists( 'YITH_Role_Changer_Set_Roles' ) ) {
	/**
	 * Class YITH_Role_Changer_Set_Roles
	 *
	 */
	class YITH_Role_Changer_Set_Roles {
		/**
		 * Construct
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'woocommerce_order_status_changed', array( $this, 'search_for_rules' ) );

			// Email hooks.
			add_filter( 'woocommerce_email_classes', array( $this, 'register_email_classes' ), 35 );
			add_filter( 'woocommerce_locate_core_template', array( $this, 'locate_core_template' ), 10, 3 );
			add_action( 'ywarc_schedule_add_role', array( $this, 'schedule_add_role' ), 10, 2 );
		}

		/**
		 * Search for rules and save them.
		 *
		 * @param  mixed $order_id Affected Order ID.
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
						$valid_rules_by_product_id = $this->search_valid_rules_by_product_id( $rules, $order_id );

						$valid_rules = $valid_rules_by_product_id;
						$valid_rules = $this->filter_replace_role_rules( $valid_rules, $user_id );

						if ( $valid_rules ) {
							$this->schedule_roles( $valid_rules, $user_id );
							$this->send_emails( $valid_rules, $user_id, $order_id );
							$order->update_meta_data( '_ywarc_rules_granted', $valid_rules );
							$order->save();
						}
					}
				}
			} else {
				if ( in_array( $new_status, $invalid_order_statuses, true ) ) {
					$user = new WP_User( $user_id );
					foreach ( $granted_rules as $rule ) {
						if ( 'add' === $rule['rule_type'] && ! empty( $rule['role_selected'] ) ) {
							foreach ( $rule['role_selected'] as $role ) {
								$user->remove_role( $role );
							}
						} elseif ( 'replace' === $rule['rule_type'] && ! empty( $rule['replace_roles'] ) ) {
							$user->add_role( $rule['replace_roles'][0] );
							$user->remove_role( $rule['replace_roles'][1] );
						}
					}
					$order->update_meta_data( '_ywarc_rules_granted', '' );
					$order->save();
				}
			}
		}

		/** Get rules (stored in metaoption). */
		public function get_rules() {
			$rules = get_option( 'ywarc_rules' ) ? get_option( 'ywarc_rules' ) : '';
			return $rules;
		}

		/**
		 * Search affecting rules by an order ID.
		 *
		 * @param  array $rules List of all rules.
		 * @param  int   $order_id Affected Order Id.
		 * @return array
		 */
		public function search_valid_rules_by_product_id( $rules, $order_id ) {
			global $sitepress;
			$order       = wc_get_order( $order_id );
			$valid_rules = array();

			foreach ( $order->get_items() as $item ) {
				foreach ( $rules as $rule_id => $rule ) {
					if ( ! empty( $rule['product_selected'] ) ) {
						$variation_id = ! empty( $item['variation_id'] ) ? $item['variation_id'] : '';
						$variation_id = $sitepress && $variation_id ? yit_wpml_object_id( $variation_id, 'product', true, $sitepress->get_default_language() ) : $variation_id;
						$id           = ! empty( $item['product_id'] ) ? $item['product_id'] : '';
						$id           = $sitepress && $id ? yit_wpml_object_id( $id, 'product', true, $sitepress->get_default_language() ) : $id;
						if ( ( intval( $rule['product_selected'] ) === $id ) || ( intval( $rule['product_selected'] ) === $variation_id ) ) {
							$valid_rules[ $rule_id ] = $rule;
						}
					}
				}
			}
			return $valid_rules;
		}

		/**
		 * Remove from valid roles if it doesn't belong to user ID.
		 *
		 * @param  array $valid_rules Array of valid rules.
		 * @param  int   $user_id Affected User ID.
		 * @return array
		 */
		public function filter_replace_role_rules( $valid_rules, $user_id ) {
			$user = new WP_User( $user_id );

			foreach ( $valid_rules as $rule_id => $rule ) {

				if ( empty( $user->roles ) ) {
					unset( $valid_rules[ $rule_id ] );
				}

				if ( ! empty( $rule['replace_roles'] ) && ! empty( $user->roles ) && ! in_array( $rule['replace_roles'][0], $user->roles, true ) ) {
					unset( $valid_rules[ $rule_id ] );
				}
			}

			return $valid_rules;
		}

		/**
		 * Loop to apply all valid roles.
		 *
		 * @param  mixed $valid_rules List of available rules.
		 * @param  mixed $user_id Affected User ID.
		 * @return void
		 */
		public function schedule_roles( $valid_rules, $user_id ) {
			foreach ( $valid_rules as $rule ) {
				$this->schedule_add_role( $rule, $user_id );
			}
		}

		/**
		 * Schedule certain role to a single user.
		 *
		 * @param  mixed $rule Current rule.
		 * @param  mixed $user_id Current User ID.
		 * @return void
		 */
		public function schedule_add_role( $rule, $user_id ) {
			$user = new WP_User( $user_id );
			if ( 'add' === $rule['rule_type'] && ! empty( $rule['role_selected'] ) ) {
				foreach ( $rule['role_selected'] as $role ) {
					$user->add_role( $role );
				}
			} elseif ( 'replace' === $rule['rule_type'] && ! empty( $rule['replace_roles'] ) ) {
				// Double check for replacing roles: If the user has not got the initial role, switch roles won't be done.
				if ( ! empty( $user->roles ) && in_array( $rule['replace_roles'][0], $user->roles, true ) ) {
					$user->add_role( $rule['replace_roles'][1] );
					$user->remove_role( $rule['replace_roles'][0] );
				}
			}
		}

		/**
		 * Send emails to both admin and current user.
		 *
		 * @param  mixed $valid_rules List of rules to be mailed.
		 * @param  mixed $user_id ID of current user.
		 * @param  mixed $order_id ID of current order.
		 * @return void
		 */
		public function send_emails( $valid_rules, $user_id, $order_id ) {
			WC()->mailer();
			do_action( 'send_email_to_admin', $valid_rules, $user_id, $order_id );
			do_action( 'send_email_to_user', $valid_rules, $user_id, $order_id );
		}

		/**
		 * Register and includes classes.
		 *
		 * @param  array $email_classes List of email classes.
		 * @return array
		 */
		public function register_email_classes( $email_classes ) {
			$email_classes['YITH_Role_Changer_Admin_Email'] = include YITH_WCARC_PATH . 'includes/emails/class-yith-role-changer-admin-email.php';
			$email_classes['YITH_Role_Changer_User_Email']  = include YITH_WCARC_PATH . 'includes/emails/class-yith-role-changer-user-email.php';
			return $email_classes;

		}

		/**
		 * Locate the templates.
		 *
		 * @param  mixed $core_file The path of the file.
		 * @param  mixed $template  Template on use.
		 * @param  mixed $template_base Template to be compared.
		 * @return string
		 */
		public function locate_core_template( $core_file, $template, $template_base ) {
			$custom_template = array(
				'emails/role-changer-admin.php',
				'emails/role-changer-user.php',
			);

			if ( in_array( $template, $custom_template, true ) ) {
				$core_file = YITH_WCARC_TEMPLATE_PATH . $template;
			}
			return $core_file;
		}
	}
}
