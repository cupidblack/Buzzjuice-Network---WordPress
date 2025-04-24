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
 * Premium class of the plugin.
 *
 * @class      YITH_Role_Changer_Premium
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     YITH <plugins@yithemes.com>
 */

if ( ! class_exists( 'YITH_Role_Changer_Premium' ) ) {
	/**
	 * Class YITH_Role_Changer_Premium
	 *
	 */
	class YITH_Role_Changer_Premium extends YITH_Role_Changer {

		/**
		 * Admin instance.
		 *
		 * @var YITH_Role_Changer_Admin_Premium
		 */
		public $admin; // Changed from protected to public

		/**
		 * Roles manager instance.
		 *
		 * @var YITH_Role_Changer_Roles_Manager
		 */
		protected $roles_manager;

		/**
		 * Set roles instance.
		 *
		 * @var YITH_Role_Changer_Set_Roles_Premium
		 */
		public $set_roles; // Changed from protected to public

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_Role_Changer_Premium Main instance
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Class Initialization
		 *
		 * Instance the admin or frontend classes
		 *
		 * @since  1.0
		 * @return void
		 * @access protected
		 */
		public function init() {
			/* === Require Main Files === */
			require_once YITH_WCARC_PATH . 'includes/class-yith-role-changer-admin.php';
			require_once YITH_WCARC_PATH . 'includes/class-yith-role-changer-set-roles.php';
			require_once YITH_WCARC_PATH . 'includes/class-yith-role-changer-roles-manager.php';
			require_once YITH_WCARC_PATH . 'includes/class-yith-role-changer-admin-premium.php';
			require_once YITH_WCARC_PATH . 'includes/class-yith-role-changer-set-roles-premium.php';

			if ( is_admin() ) {
				$this->admin = new YITH_Role_Changer_Admin_Premium();
				if ( ! function_exists( 'members_plugin' ) ) {
					$this->roles_manager = new YITH_Role_Changer_Roles_Manager();
				}
			}

			$this->set_roles = new YITH_Role_Changer_Set_Roles_Premium();

			add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
			add_action( 'admin_init', array( $this, 'register_plugin_for_updates' ) );
		}

		/**
		 * Register plugins for activation tab
		 *
		 * @return void
		 * @since    2.0.0
		 */
		public function register_plugin_for_activation() {
			if ( ! class_exists( 'YIT_Plugin_Licence' ) ) {
				require_once YITH_WCARC_PATH . '/plugin-fw/licence/lib/yit-licence.php';
				require_once YITH_WCARC_PATH . '/plugin-fw/lib/yit-plugin-licence.php';
			}

			YIT_Plugin_Licence()->register( YITH_WCARC_INIT, YITH_WCARC_SECRETKEY, YITH_WCARC_SLUG );
		}

		/**
		 * Register plugins for update tab
		 *
		 * @return void
		 * @since    2.0.0
		 */
		public function register_plugin_for_updates() {
			if ( ! class_exists( 'YIT_Upgrade' ) ) {
				require_once YITH_WCARC_PATH . '/plugin-fw/lib/yit-upgrade.php';
			}
			YIT_Upgrade()->register( YITH_WCARC_SLUG, YITH_WCARC_INIT );
		}
	}
}
