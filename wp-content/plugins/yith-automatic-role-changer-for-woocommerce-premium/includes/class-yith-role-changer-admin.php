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

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! defined( 'YITH_WCARC_VERSION' ) ) {
	exit( 'Direct access forbidden.' );
}

/**
 * Main Admin Class of plugin.
 *
 * @class      YITH_Role_Changer_Admin
 * @since      Version 1.0.0
 * @author     YITH <plugins@yithemes.com>
 */
if ( ! class_exists( 'YITH_Role_Changer_Admin' ) ) {
	/**
	 * Class YITH_Role_Changer_Admin
	 *
	 */
	class YITH_Role_Changer_Admin {

		/**
		 * Panel Object.
		 *
		 * @var $panel
		 */
		protected $panel = null;

		/**
		 * Panel page.
		 *
		 * @var string
		 */
		protected $panel_page = 'yith_wcarc_panel';

		/**
		 * Show the premium landing page.
		 *
		 * @var bool
		 */
		public $show_premium_landing = true;

		/**
		 * Official plugin documentation.
		 *
		 * @var string
		 */
		protected $official_documentation = 'https://docs.yithemes.com/yith-automatic-role-changer-for-woocommerce/';

		/**
		 * Official plugin landing page.
		 *
		 * @var string
		 */
		protected $premium_landing = 'https://yithemes.com/themes/plugins/yith-woocommerce-automatic-role-changer';

		/**
		 * Official plugin landing page.
		 *
		 * @var string
		 */
		protected $premium_live = 'https://plugins.yithemes.com/yith-automatic-role-changer-for-woocommerce/';

		/**
		 * Single instance of the class
		 *
		 * @var $instance
		 * @since 1.0.0
		 */
		public static $instance;

		/**
		 * Returns single instance of the class
		 *
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Construct
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			/* === Register Panel Settings === */
			add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
			/* === Premium Tab === */
			add_action( 'yith_ywarc_automatic_role_changer_premium_tab', array( $this, 'premium_tab' ) );

			/* === Show Plugin Information === */
			add_filter( 'plugin_action_links_' . plugin_basename( YITH_WCARC_PATH . '/' . basename( YITH_WCARC_FILE ) ), array( $this, 'action_links' ) );
			add_filter( 'yith_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );

			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'yith_wcarc_rules_tab', array( $this, 'rules_tab' ) );
			add_action( 'ywarc_print_rules', array( $this, 'load_rules' ) );
			add_action( 'wp_ajax_ywarc_add_rule', array( $this, 'add_rule' ) );
			add_action( 'wp_ajax_ywarc_save_rule', array( $this, 'save_rule' ) );
			add_action( 'wp_ajax_ywarc_delete_rule', array( $this, 'delete_rule' ) );
			add_action( 'wp_ajax_ywarc_delete_all_rules', array( $this, 'delete_all_rules' ) );
			if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
				add_action( 'manage_woocommerce_page_wc-orders_custom_column', array( $this, 'role_column_content' ), 100, 2 );
				add_action( 'add_meta_boxes_woocommerce_page_wc-orders', array( $this, 'add_role_granted_info_meta_box' ) );
			} else {
				add_action( 'manage_shop_order_posts_custom_column', array( $this, 'role_column_content' ), 100, 2 );
				add_action( 'add_meta_boxes_shop_order', array( $this, 'add_role_granted_info_meta_box' ) );
			}
		}

		/**
		 * Add a panel under YITH Plugins tab
		 *
		 * @return   void
		 * @since    1.0
		 * @use     /Yit_Plugin_Panel class
		 * @see      plugin-fw/lib/yit-plugin-panel.php
		 */
		public function register_panel() {

			if ( ! empty( $this->panel ) ) {
				return;
			}

			$menu_title = 'Automatic Role Changer';

			$admin_tabs = apply_filters(
				'yith_wcarc_admin_tabs',
				array(
					'rules' => esc_html__( 'Rules', 'yith-automatic-role-changer-for-woocommerce' ),
				)
			);

			if ( $this->show_premium_landing ) {
				$admin_tabs['premium-landing'] = esc_html__( 'Premium Version', 'yith-automatic-role-changer-for-woocommerce' );
			}

			$args = array(
				'create_menu_page' => true,
				'parent_slug'      => '',
				'plugin_slug'      => YITH_WCARC_SLUG,
				'page_title'       => 'YITH Automatic Role Changer for WooCommerce',
				'menu_title'       => $menu_title,
				'capability'       => 'manage_options',
				'parent'           => '',
				'parent_page'      => 'yith_plugin_panel',
				'page'             => $this->panel_page,
				'admin-tabs'       => $admin_tabs,
				'options-path'     => YITH_WCARC_OPTIONS_PATH,
				'links'            => $this->get_sidebar_link(),
				'is_free'          => false,
				'is_premium'       => defined( 'YITH_WCARC_PREMIUM' ),
				'is_extended'      => false,
			);

			/* === Fixed: not updated theme/old plugin framework  === */
			if ( ! class_exists( 'YIT_Plugin_Panel_WooCommerce' ) ) {
				require_once YITH_WCARC_PATH . '/plugin-fw/lib/yit-plugin-panel-wc.php';
			}

			$this->panel = new YIT_Plugin_Panel_WooCommerce( $args );
		}

		/**
		 * Premium Tab Template
		 *
		 * Load the premium tab template on admin page
		 *
		 * @return   void
		 * @since    1.0
		 */
		public function premium_tab() {
			$premium_tab_template = YITH_WCARC_TEMPLATE_PATH . 'admin/premium_tab.php';
			if ( file_exists( $premium_tab_template ) ) {
				include_once $premium_tab_template;
			}
		}

		/**
		 * Add Action Links to Plugin Management.
		 *
		 * @param  mixed $links Links to be added.
		 * @return mixed
		 */
		public function action_links( $links ) {
			$links = yith_add_action_links( $links, $this->panel_page, false, YITH_WCARC_SLUG );
			return $links;
		}

		/**
		 * Define plugin meta and slug
		 *
		 * @param  mixed $new_row_meta_args New Row Meta Args.
		 * @param  mixed $plugin_meta Plugin current meta.
		 * @param  mixed $plugin_file Plugin file.
		 * @param  mixed $plugin_data Plugin data.
		 * @param  mixed $status CUrrent Status.
		 * @param  mixed $init_file File of the plugin's init.
		 * @return mixed
		 */
		public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'YITH_WCARC_FREE_INIT' ) {
			if ( defined( $init_file ) && constant( $init_file ) === $plugin_file ) {
				$new_row_meta_args['slug'] = YITH_WCARC_SLUG;
			}
			return $new_row_meta_args;
		}

		/**
		 * Sidebar links
		 *
		 * @return   array The links
		 * @since    1.2.1
		 */
		public function get_sidebar_link() {
			$links = array(
				array(
					'title' => esc_html__( 'Plugin documentation', 'yith-automatic-role-changer-for-woocommerce' ),
					'url'   => $this->official_documentation,
				),
				array(
					'title' => esc_html__( 'Help Center', 'yith-automatic-role-changer-for-woocommerce' ),
					'url'   => 'http://support.yithemes.com/hc/en-us/categories/202568518-Plugins',
				),
			);

			if ( defined( 'YITH_WCARC_FREE_INIT' ) ) {
				$links[] = array(
					'title' => esc_html__( 'Discover the premium version', 'yith-automatic-role-changer-for-woocommerce' ),
					'url'   => $this->premium_landing,
				);

				$links[] = array(
					'title' => esc_html__( 'Free vs Premium', 'yith-automatic-role-changer-for-woocommerce' ),
					'url'   => 'https://yithemes.com/themes/plugins/yith-woocommerce-pre-order/#tab-free_vs_premium_tab',
				);

				$links[] = array(
					'title' => esc_html__( 'Premium live demo', 'yith-automatic-role-changer-for-woocommerce' ),
					'url'   => $this->premium_live,
				);

				$links[] = array(
					'title' => esc_html__( 'WordPress support forum', 'yith-automatic-role-changer-for-woocommerce' ),
					'url'   => 'https://wordpress.org/plugins/yith-woocommerce-pre-order/',
				);

				$links[] = array(
					'title' => sprintf( '%s (%s %s)', esc_html__( 'Changelog', 'yith-automatic-role-changer-for-woocommerce' ), esc_html__( 'current version', 'yith-automatic-role-changer-for-woocommerce' ), YITH_WCARC_VERSION ),
					'url'   => 'https://yithemes.com/docs-plugins/yith-woocommerce-pre-order/06-changelog-free.html',
				);
			}

			if ( defined( 'YITH_WCARC_PREMIUM' ) ) {
				$links[] = array(
					'title' => esc_html__( 'Support platform', 'yith-automatic-role-changer-for-woocommerce' ),
					'url'   => 'https://yithemes.com/my-account/support/dashboard/',
				);

				$links[] = array(
					'title' => sprintf( '%s (%s %s)', esc_html__( 'Changelog', 'yith-automatic-role-changer-for-woocommerce' ), esc_html__( 'current version', 'yith-automatic-role-changer-for-woocommerce' ), YITH_WCARC_VERSION ),
					'url'   => 'https://yithemes.com/docs-plugins/yith-woocommerce-role-changer/07-changelog-premium.html',
				);
			}

			return $links;
		}

		/** Include the rules-tab template */
		public function rules_tab() {
			// No need to nonce it.
			if ( isset( $_GET['page'] ) && 'yith_wcarc_panel' === $_GET['page'] //phpcs:ignore WordPress.Security.NonceVerification
				&& file_exists( YITH_WCARC_TEMPLATE_PATH . '/admin/rules-tab.php' ) ) {
				include_once YITH_WCARC_TEMPLATE_PATH . '/admin/rules-tab.php';
			}
		}

		/** Include the load-rules template */
		public function load_rules() {
			include_once YITH_WCARC_TEMPLATE_PATH . '/admin/load-rules.php';
		}

		/** Add rule template. */
		public function add_rule() {
			// No need to nonce it.
			if ( isset( $_POST['title'] ) ) { //phpcs:ignore WordPress.Security.NonceVerification
				$rule_id      = uniqid();
				$title        = sanitize_text_field( wp_unslash( $_POST['title'] ) ); //phpcs:ignore WordPress.Security.NonceVerification
				$unique_title = true;

				$rules = get_option( 'ywarc_rules' );
				if ( $rules ) {
					foreach ( $rules as $rule ) {
						if ( $rule['title'] === $title ) {
							$unique_title = false;
							break;
						}
					}
				}

				if ( $unique_title ) {
					$new_rule = true;
					include YITH_WCARC_TEMPLATE_PATH . 'admin/add-rule.php';
				} else {
					echo 'duplicated_name_error';
				}
			}
			die();
		}

		//BlueCrownR&D
		/** Triggers when saving a rule, and saves it into a metaoption */ /*
		public function save_rule() {
			if ( isset( $_REQUEST['yith-ywarc-save_rule-nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['yith-ywarc-save_rule-nonce'] ) ), 'yith-ywarc-save_rule' ) ) {
				return;
			}

			$rules = get_option( 'ywarc_rules' );

			if ( isset( $_POST['rule_id'] ) ) {

				$new_rule_options = apply_filters(
					'ywarc_save_rule_array',
					array(
						'title'            => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
						'rule_type'        => isset( $_POST['rule_type'] ) ? sanitize_text_field( wp_unslash( $_POST['rule_type'] ) ) : 'add',
						'role_selected'    => isset( $_POST['role_selected'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['role_selected'] ) ) : array(),
						'replace_roles'    => ! empty( $_POST['replace_roles'] ) ? array(
							isset( $_POST['replace_roles'][0][0] ) ? sanitize_text_field( wp_unslash( $_POST['replace_roles'][0][0] ) ) : '',
							isset( $_POST['replace_roles'][1][0] ) ? sanitize_text_field( wp_unslash( $_POST['replace_roles'][1][0] ) ) : '',
						) : '',
						'radio_group'      => isset( $_POST['radio_group'] ) ? sanitize_text_field( wp_unslash( $_POST['radio_group'] ) ) : 'product',
						'product_selected' => isset( $_POST['product_selected'] ) ? sanitize_text_field( wp_unslash( $_POST['product_selected'] ) ) : '',
					)
				);

				$rules[ sanitize_text_field( wp_unslash( $_POST['rule_id'] ) ) ] = $new_rule_options;
				update_option( 'ywarc_rules', $rules );
			}
			die();
		}
*/
		public function save_rule() {
			if ( isset( $_REQUEST['yith-ywarc-save_rule-nonce'] ) && ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['yith-ywarc-save_rule-nonce'] ) ), 'yith-ywarc-save_rule' ) ) {
				return;
			}

			// Ensure $rules is always an array
			$rules = get_option( 'ywarc_rules', array() );

			if ( isset( $_POST['rule_id'] ) ) {
				$new_rule_options = apply_filters(
					'ywarc_save_rule_array',
					array(
						'title'            => isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '',
						'rule_type'        => isset( $_POST['rule_type'] ) ? sanitize_text_field( wp_unslash( $_POST['rule_type'] ) ) : 'add',
						'role_selected'    => isset( $_POST['role_selected'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['role_selected'] ) ) : array(),
						'replace_roles'    => ! empty( $_POST['replace_roles'] ) ? array(
							isset( $_POST['replace_roles'][0][0] ) ? sanitize_text_field( wp_unslash( $_POST['replace_roles'][0][0] ) ) : '',
							isset( $_POST['replace_roles'][1][0] ) ? sanitize_text_field( wp_unslash( $_POST['replace_roles'][1][0] ) ) : '',
						) : '',
						'radio_group'      => isset( $_POST['radio_group'] ) ? sanitize_text_field( wp_unslash( $_POST['radio_group'] ) ) : 'product',
						'product_selected' => isset( $_POST['product_selected'] ) ? sanitize_text_field( wp_unslash( $_POST['product_selected'] ) ) : '',
					)
				);

				$rules[ sanitize_text_field( wp_unslash( $_POST['rule_id'] ) ) ] = $new_rule_options;
				update_option( 'ywarc_rules', $rules );
			}
			die();
		}


		/** Delete a rule from metaoption (Using POST) */
		public function delete_rule() {
			$rules = get_option( 'ywarc_rules' );
			// No need to nonce it.
			unset( $rules[ $_POST['rule_id'] ] ); //phpcs:ignore WordPress.Security.NonceVerification
			update_option( 'ywarc_rules', $rules );
			die();
		}

		/** Delete the rule metaoption */
		public function delete_all_rules() {
			update_option( 'ywarc_rules', array() );
			die();
		}

		/**
		 * Create metabox of add_roles_granted.
		 *
		 * @param  mixed $post Current post.
		 */
		public function add_role_granted_info_meta_box( $post ) {
			$order = $post instanceof WC_Order ? $post : wc_get_order( $post->ID );
			$rules = $order->get_meta( '_ywarc_rules_granted' );

			if ( $rules ) {
				$screen = OrderUtil::custom_orders_table_usage_is_enabled() ? 'woocommerce_page_wc-orders' : 'shop_order';
				add_meta_box(
					'ywarc-order-roles-granted',
					esc_html__( 'Automatic role changer', 'yith-automatic-role-changer-for-woocommerce' ),
					array( $this, 'ywarc_order_roles_granted_content' ),
					$screen,
					'side',
					'core',
					$rules
				);
			}
		}

		/**
		 * Print content about Roles Granted.
		 *
		 * @param  mixed $post Current post ID.
		 * @param  array $meta Contain the rules.
		 */
		public function ywarc_order_roles_granted_content( $post, $meta ) {
			if ( $post && $meta['args'] ) {
				$rules = $meta['args'];

				if ( $rules ) {
					// Count the total number of roles granted.
					$roles_count = 0;
					foreach ( $rules as $rule_id => $rule ) {
						if ( 'add' === $rule['rule_type'] && ! empty( $rule['role_selected'] && is_countable( $rule['role_selected'] ) ) ) {
							$roles_count = $roles_count + count( $rule['role_selected'] );
						} elseif ( 'replace' === $rule['rule_type'] && ! empty( $rule['replace_roles'] ) ) {
							++$roles_count;
						}
					}

					echo '<p>';
					sprintf(
						_n(
							'Customer gains the following role: ',
							'Customer gains the following roles: ',
							$roles_count,
							'yith-automatic-role-changer-for-woocommerce'
						)
					);
					echo '</p>';

					foreach ( $rules as $rule_id => $rule ) {
						if ( 'add' === $rule['rule_type'] && ! empty( $rule['role_selected'] ) ) {
							foreach ( $rule['role_selected'] as $role ) {
								$role_name = wp_roles()->roles[ $role ]['name'];
								echo '<div class="ywarc_metabox_gained_role"><span class="ywarc_metabox_role_name">' .
									esc_attr( $role_name ) . '</span>';
								do_action( 'ywarc_after_metabox_content', $rule );
								echo '</div>';
							}
						} elseif ( 'replace' === $rule['rule_type'] && ! empty( $rule['replace_roles'] ) ) {
							$role_name = wp_roles()->roles[ $rule['replace_roles'][1] ]['name'];
							echo '<div class="ywarc_metabox_gained_role"><span class="ywarc_metabox_role_name">' .
								esc_attr( $role_name ) . '</span>';
							do_action( 'ywarc_after_metabox_content', $rule );
							echo '</div>';
						}
					}
				}
			}
		}

		/**
		 * Print role column.
		 *
		 * @param  mixed $column_name Current Column Name.
		 * @param  mixed $post_id Current Post ID.
		 * @return void
		 */
		public function role_column_content( $column_name, $post_id ) {
			$order = wc_get_order( $post_id );
			$rules = $order->get_meta( '_ywarc_rules_granted' );
			if ( $rules && ( 'order_status' === $column_name || 'order_number' === $column_name ) ) {

				// Count the total number of roles granted.
				$roles_count = 0;
				foreach ( $rules as $rule_id => $rule ) {
					if ( 'add' === $rule['rule_type'] && ! empty( $rule['role_selected'] && is_countable( $rule['role_selected'] ) ) ) {
						$roles_count = $roles_count + count( $rule['role_selected'] );
					} elseif ( 'replace' === $rule['rule_type'] && ! empty( $rule['replace_roles'] ) ) {
						++$roles_count;
					}
				}

				$html = '<img class="ywarc_role_icon" title="' . sprintf(
					/* translators: %s is replaced with number of roles. */
					_n( '%d new role gained with this order', '%d new roles gained with this order', $roles_count, 'yith-automatic-role-changer-for-woocommerce' ),
					$roles_count
				) .
						'" src="' . YITH_WCARC_ASSETS_URL . '/images/badge.png"></span>';

				if ( version_compare( WC()->version, '3.3.0', '>=' ) ) {
					if ( 'order_number' === $column_name ) {
						echo $html; // phpcs:ignore WordPress.Security.EscapeOutput
					}
				} else {
					if ( 'order_status' === $column_name ) {
						echo $html; // phpcs:ignore WordPress.Security.EscapeOutput
					}
				}
			}
		}

		/**
		 * Load admin JS & CSS.
		 *
		 * @param  mixed $hook_suffix Current hook.
		 */
		public function enqueue_scripts( $hook_suffix ) {
			wp_enqueue_style(
				'ywarc-admin-style',
				YITH_WCARC_ASSETS_URL . '/css/ywarc-admin.css',
				array(),
				YITH_WCARC_VERSION
			);

			// No need to verify nonce here.
			if ( ! isset( $_GET['page'] ) || 'yith_wcarc_panel' !== $_GET['page'] ) { //phpcs:ignore WordPress.Security.NonceVerification
				return;
			}
			$premium_suffix = defined( 'YITH_WCARC_PREMIUM' ) && YITH_WCARC_PREMIUM ? '-premium' : '';
			wp_register_script(
				'ywarc-admin',
				YITH_WCARC_ASSETS_JS_URL . yit_load_js_file( 'ywarc-admin' . $premium_suffix . '.js' ),
				array( 'jquery' ),
				YITH_WCARC_VERSION,
				false
			);
			wp_localize_script(
				'ywarc-admin',
				'localize_js_ywarc_admin',
				array(
					'ajax_url'                => admin_url( 'admin-ajax.php' ),
					'before_2_7'              => version_compare( WC()->version, '2.7', '<' ) ? true : false,
					'search_categories_nonce' => wp_create_nonce( 'search-categories' ),
					'search_tags_nonce'       => wp_create_nonce( 'search-tags' ),
					'empty_name_msg'          => esc_html__( 'Please, name this rule.', 'yith-automatic-role-changer-for-woocommerce' ),
					'duplicated_name_msg'     => esc_html__( 'This name already exists and is used to identify another rule. Please, try name.', 'yith-automatic-role-changer-for-woocommerce' ),
					'delete_rule_msg'         => esc_html__( 'Are you sure you want to delete this rule?', 'yith-automatic-role-changer-for-woocommerce' ),
					'delete_all_rules_msg'    => esc_html__( 'Are you sure you want to delete all the rules? This cannot be undone.', 'yith-automatic-role-changer-for-woocommerce' ),
				)
			);
			wp_enqueue_script( 'ywarc-admin' );
		}
	}
}
