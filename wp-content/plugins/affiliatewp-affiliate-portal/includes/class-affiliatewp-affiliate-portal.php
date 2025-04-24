<?php
/**
 * Affiliate Portal Plugin Bootstrap
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AffiliateWP_Affiliate_Portal\Core;
use function AffiliateWP_Affiliate_Portal\html;

if ( ! class_exists( 'AffiliateWP_Affiliate_Portal' ) ) {

	#[\AllowDynamicProperties]

	/**
	 * Setup class.
	 *
	 * @since 1.0.0
	 */
	final class AffiliateWP_Affiliate_Portal {

		/**
		 * Holds the instance.
		 *
		 * Ensures that only one instance of AffiliateWP_Affiliate_Portal exists in memory at any one
		 * time and it also prevents needing to define globals all over the place.
		 *
		 * TL;DR This is a static property property that holds the singleton instance.
		 *
		 * @access private
		 * @var    \AffiliateWP_Affiliate_Portal
		 * @static
		 *
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * The version number.
		 *
		 * @since 1.2.5 The version here is automatically
		 *              updated to the version in the plugin header; no
		 *              need to change this.
		 *
		 * @access private
		 * @since  1.0.0
		 * @var    string
		 */
		private $version = '0.0.0';

		/**
		 * Main plugin file.
		 *
		 * @since 1.0.0
		 * @var   string
		 */
		private $file = '';

		/**
		 * Portal container.
		 *
		 * @since 1.0.0
		 * @var   Core\Components\Portal
		 */
		public $portal;

		/**
		 * Assets API container.
		 *
		 * @since 1.0.0
		 * @var   Core\Components\Assets
		 */
		public $assets;

		/**
		 * Icons API container.
		 *
		 * @since 1.0.0
		 * @var   Core\Components\Icons
		 */
		public $icons;

		/**
		 * Reports API container.
		 *
		 * @since 1.0.0
		 * @var   Core\Components\Reports
		 */
		public $reports;

		/**
		 * Datasets API container.
		 *
		 * @since 1.0.0
		 * @var   Core\Components\Datasets
		 */
		public $datasets;

		/**
		 * Views API container.
		 *
		 * @since 1.0.0
		 * @var   Core\Components\Views
		 */
		public $views;

		/**
		 * Controls API container.
		 *
		 * @since 1.0.0
		 * @var   Core\Components\Controls
		 */
		public $controls;

		/**
		 * Routes API container.
		 *
		 * @since 1.0.0
		 * @var   Core\Components\Routes
		 */
		public $routes;

		/**
		 * Requests controller.
		 *
		 * @since 1.0.0
		 * @var   Core\Components\Requests
		 */
		public $requests;

		/**
		 * Utilities API container.
		 *
		 * @since 1.0.0
		 * @var   Core\Components\Utilities
		 */
		public $utilities;

		/**
		 * Notifications API container.
		 *
		 * @since 1.0.0
		 * @var   Core\Components\Notifications
		 */
		public $notifications;

		/**
		 * Integrations container.
		 *
		 * @since 1.0.0
		 * @var   Core\Integrations
		 */
		public $integrations;

		/**
		 * The template loading API.
		 *
		 * @since  1.0.0
		 * @var    Core\Templates
		 */
		public $templates;

		/**
		 * The Admin instance.
		 *
		 * @since  1.0.0
		 * @var    Core\Admin
		 */
		public $admin;

		/**
		 * Menu links instance.
		 *
		 * @since 1.0.9
		 *
		 * @var Core\Menu_Links
		 */
		public $menu_links;

		/**
		 * Generates the main AffiliateWP_Affiliate_Portal instance.
		 *
		 * Insures that only one instance of AffiliateWP_Affiliate_Portal exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 * @static
		 *
		 * @param string $file Main plugin file.
		 * @return \AffiliateWP_Affiliate_Portal The one true AffiliateWP_Affiliate_Portal instance.
		 */
		public static function instance( $file = null ) {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AffiliateWP_Affiliate_Portal ) ) {

				self::$instance = new \AffiliateWP_Affiliate_Portal();

				self::$instance->file    = $file;
				self::$instance->version = get_plugin_data( self::$instance->file, false, false )['Version'] ?? '';

				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->init();
				self::$instance->hooks();
				self::$instance->setup_objects();
			}

			return self::$instance;
		}

		/**
		 * Throws an error on object clone.
		 *
		 * The whole idea of the singleton design pattern is that there is a single
		 * object therefore, we don't want the object to be cloned.
		 *
 		 * @access protected
		 * @since  1.0.0
		 *
		 * @return void
		 */
		protected function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh? This object cannot be cloned.', 'affiliatewp-affiliate-portal' ), '1.0.0' );
		}

		/**
		 * Disables unserializing of the class.
		 *
		 * @access public
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh? This class cannot be unserialized.', 'affiliatewp-affiliate-portal' ), '1.0.0' );
		}

		/**
		 * Sets up the class.
		 *
		 * @access private
		 * @since  1.0.0
		 */
		private function __construct() {
			self::$instance = $this;
		}

		/**
		 * Resets the instance of the class.
		 *
		 * @access public
		 * @since  1.0.0
		 * @static
		 */
		public static function reset() {
			self::$instance = null;
		}

		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function setup_constants() {
			// Plugin version
			if ( ! defined( 'AFFWP_PORTAL_VERSION' ) ) {
				define( 'AFFWP_PORTAL_VERSION', $this->version );
			}

			// Plugin Folder Path
			if ( ! defined( 'AFFWP_PORTAL_PLUGIN_DIR' ) ) {
				define( 'AFFWP_PORTAL_PLUGIN_DIR', plugin_dir_path( $this->file ) );
			}

			// Plugin Folder URL
			if ( ! defined( 'AFFWP_PORTAL_PLUGIN_URL' ) ) {
				define( 'AFFWP_PORTAL_PLUGIN_URL', plugin_dir_url( $this->file ) );
			}

			// Plugin Root File
			if ( ! defined( 'AFFWP_PORTAL_PLUGIN_FILE' ) ) {
				define( 'AFFWP_PORTAL_PLUGIN_FILE', $this->file );
			}
		}

		/**
		 * Include necessary files.
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function includes() {
			// Bring in the autoloader.
			require_once __DIR__ . '/lib/affwp/affiliatewp-autoloader.php';

			require_once AFFWP_PORTAL_PLUGIN_DIR . 'includes/functions.php';
		}

		/**
		 * Initializes the plugin.
		 *
		 * @since 1.0.0
		 */
		private function init() {
			if ( is_admin() ) {
				self::$instance->updater();
			}
		}

		/**
		 * Loads the custom plugin updater.
		 *
		 * @since 1.0.0
		 *
		 * @see \AffWP_AddOn_Updater
		 */
		private function updater() {
			if ( class_exists( '\AffWP_AddOn_Updater' ) ) {
				$updater = new \AffWP_AddOn_Updater( 570647, self::$instance->file, $this->version );
			}
		}

		/**
		 * Setup all objects
		 *
		 * @access public
		 * @since 1.0.0
		 * @return void
		 */
		public function setup_objects() {
			if ( is_admin() ) {
				self::$instance->admin = new Core\Admin;
			}
			self::$instance->assets        = new Core\Components\Assets;
			self::$instance->icons         = new Core\Components\Icons;
			self::$instance->reports       = new Core\Components\Reports;
			self::$instance->requests      = new Core\Components\Requests;
			self::$instance->datasets      = new Core\Components\Datasets;
			self::$instance->portal        = new Core\Components\Portal;
			self::$instance->integrations  = new Core\Integrations;
			self::$instance->views         = new Core\Components\Views;
			self::$instance->sections      = new Core\Components\Sections;
			self::$instance->controls      = new Core\Components\Controls;
			self::$instance->routes        = new Core\Components\Routes;
			self::$instance->utilities     = new Core\Components\Utilities;
			self::$instance->notifications = new Core\Components\Notifications;
			self::$instance->templates     = new Core\Templates;
			self::$instance->menu_links    = new Core\Menu_Links;
		}

		/**
		 * Sets up the default hooks and actions.
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function hooks() {

			// Plugin meta.
			add_filter( 'plugin_row_meta', array( $this, 'plugin_meta' ), null, 2 );

			// Hide the admin bar. Affiliates should never see this.
			add_filter( 'show_admin_bar', function( $show ) {
				if ( true === affwp_is_affiliate_portal() ) {
					$show = false;
				}

				return $show;
			} );

			// Maybe deactivate the pre-1.0.0-beta3 Affiliate Dashboard add-on.
			add_action( 'admin_init', function() {
				$old_plugin_path = $this->maybe_get_ad_plugin_path();

				if ( class_exists( 'AffiliateWP_Affiliate_Dashboard' )
					|| ( ! empty( $old_plugin_path ) && is_plugin_active( $old_plugin_path ) )
				) {
					deactivate_plugins( $old_plugin_path );
				}
			} );

			// If Affiliate Dashboard is still installed, show an admin notice to uninstall it.
			add_action( 'admin_notices', function() {
				if ( ! affwp_is_admin_page() || ! current_user_can( 'manage_options' ) ) {
					return;
				}

				$old_plugin_path = $this->maybe_get_ad_plugin_path();

				if ( ! empty( $old_plugin_path ) && 0 === validate_plugin( $old_plugin_path ) ) {
					?>
					<div class="notice notice-info">
						<p>
							<?php _e( 'The Affiliate Dashboard plugin has been renamed to <strong>Affiliate Portal</strong>.', 'affiliatewp-affiliate-portal' ); ?>
						</p>
						<p>
							<?php _e( 'It&#8217;s now safe to deactivate and delete your old copy of the <strong>AffiliateWP - Affiliate Dashboard</strong> plugin.', 'affiliatewp-affiliate-portal' ); ?>
						</p>
					</div>
					<?php
				}
			} );

		}

		/**
		 * (Maybe) retrieves the plugin path for an old outdated copy of the Affiliate Dashboard plugin.
		 *
		 * @since 1.0.0
		 * @private
		 *
		 * @return string Plugin path if found, otherwise an empty string.
		 */
		private function maybe_get_ad_plugin_path() {
			if ( ! function_exists( 'get_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}

			$found = preg_grep( '/affiliatewp-affiliate-dashboard.php$/', array_keys( get_plugins() ) );

			if ( ! empty( $found ) ) {
				$found = reset( $found );
			}

			return empty( $found ) ? '' : $found;
		}

		/**
		 * Modifies the plugin list table meta links.
		 *
		 * @access public
		 * @since  1.0.0
		 *
		 * @param array  $links The current links array.
		 * @param string $file  A specific plugin table entry.
		 * @return array The modified links array.
		 */
		public function plugin_meta( $links, $file ) {

		    if ( $file == plugin_basename( $this->file ) ) {

				$plugins_link = affwp_admin_link( 'add-ons', __( 'More add-ons', 'affiliatewp-affiliate-portal' ), array(), array(
					'title' => __( 'Get more add-ons for AffiliateWP', 'affiliatewp-affiliate-portal' ),
				) );

		        $links = array_merge( $links, array( $plugins_link ) );
		    }

		    return $links;

		}
	}

	/**
	 * The main function responsible for returning the one true AffiliateWP_Affiliate_Portal
	 * Instance to functions everywhere.
	 *
	 * Use this function like you would a global variable, except without needing
	 * to declare the global.
	 *
	 * Example: <?php $affiliatewp_affiliate_portal = affiliatewp_affiliate_portal(); ?>
	 *
	 * @since  1.0.0
	 *
	 * @return \AffiliateWP_Affiliate_Portal The one true AffiliateWP_Affiliate_Portal Instance
	 */
	function affiliatewp_affiliate_portal() {
		return AffiliateWP_Affiliate_Portal::instance();
	}

}
