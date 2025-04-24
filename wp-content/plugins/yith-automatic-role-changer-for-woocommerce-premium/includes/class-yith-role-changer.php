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
 * Main plugin class.
 *
 * @class      YITH_Role_Changer
 * @package    Yithemes
 * @since      Version 1.0.0
 * @author     YITH <plugins@yithemes.com>
 */
if ( ! class_exists( 'YITH_Role_Changer' ) ) {
	/**
	 * Class YITH_Role_Changer
	 *
	 */
	class YITH_Role_Changer {
		/**
		 * Plugin version
		 *
		 * @var string
		 * @since 1.0
		 */
		protected $version = YITH_WCARC_VERSION;

		/**
		 * Main Instance
		 *
		 * @var YITH_Role_Changer
		 * @since 1.0
		 * @access protected
		 */
		protected static $instance = null;

		/**
		 * Main Admin Instance
		 *
		 * @var YITH_Role_Changer_Admin
		 * @since 1.0
		 */
		public $admin = null;


		/**
		 * YITH_Role_Changer_Set_Roles Instance
		 *
		 * @var YITH_Role_Changer_Set_Roles
		 * @since 1.0
		 */
		public $set_roles = null;


		/**
		 * Construct
		 *
		 * @since 1.0
		 */
		protected function __construct() {
			/* == Plugin Init === */
			$this->init();
			add_action( 'plugins_loaded', array( $this, 'plugin_fw_loader' ), 15 );
		}

		/**
		 * Main plugin Instance
		 *
		 * @return YITH_Role_Changer Main instance
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

			if ( is_admin() ) {
				$this->admin = new YITH_Role_Changer_Admin();
				if ( ! function_exists( 'members_plugin' ) ) {
					$this->roles_manager = new YITH_Role_Changer_Roles_Manager();
				}
			}

			$this->set_roles = new YITH_Role_Changer_Set_Roles();
		}

		/**
		 * Load plugin framework
		 *
		 * @since  1.0
		 * @return void
		 */
		public function plugin_fw_loader() {
			if ( ! defined( 'YIT_CORE_PLUGIN' ) ) {
				global $plugin_fw_data;
				if ( ! empty( $plugin_fw_data ) ) {
					$plugin_fw_file = array_shift( $plugin_fw_data );
					require_once $plugin_fw_file;
				}
			}
		}
	}
}
