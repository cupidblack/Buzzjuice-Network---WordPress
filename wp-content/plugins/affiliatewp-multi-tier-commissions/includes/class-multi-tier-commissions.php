<?php
/**
 * Plugin Bootstrap
 *
 * @package     AffiliateWP
 * @subpackage  AffiliateWP\MTC;
 * @copyright   Copyright (c) 2024, Awesome Motive, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace AffiliateWP\MTC;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Boots up Multi-Tier Commissions.
 *
 * @since 1.0.0
 */
final class Multi_Tier_Commissions {

	/**
	 * Holds the instance.
	 *
	 * Ensures that only one instance of AffiliateWP_Multi_Tier_Commissions exists in memory at any one
	 * time, and it also prevents needing to define globals all over the place.
	 *
	 * TL;DR This is a static property that holds the singleton instance.
	 *
	 * @access private
	 * @var    Multi_Tier_Commissions
	 * @static
	 *
	 * @since 1.0.0
	 */
	private static Multi_Tier_Commissions $instance;

	/**
	 * Plugin Data
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private array $plugin_data = array();

	/**
	 * Maximum number of referral tiers allowed.
	 *
	 * This constant represents the maximum number of referral tiers or levels that
	 * the system supports. It is used to define the upper limit for the depth of
	 * referral structures or hierarchical levels in the multi-tier commission system.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	const MAX_REFERRAL_TIERS = 5;

	/**
	 * The version number.
	 *
	 * @access private
	 * @since  1.0.0
	 * @var    string
	 * @see self::set_plugin_data() where this is set.
	 */
	private string $version = '';

	/**
	 * Main plugin file.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private string $file = '';

	/**
	 * The network object.
	 *
	 * @since  1.0.0
	 * @var    Network
	 */
	public Network $network;

	/**
	 * The frontend object.
	 *
	 * @since  1.0.0
	 * @var    Frontend
	 */
	public Frontend $frontend;

	/**
	 * The number of tiers.
	 *
	 * @since  1.2.0
	 * @var    int
	 */
	public int $max_tiers = 0;

	/**
	 * If MTC is active or not.
	 *
	 * @since  1.0.0
	 * @var    mixed
	 */
	private $is_activated = null;

	/**
	 * The tiers' configuration.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private array $tiers;

	/**
	 * Main AffiliateWP_Multi_Tier_Commissions Instance
	 *
	 * Insures that only one instance of AffiliateWP_Multi_Tier_Commissions exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0.0
	 *
	 * @param string $file Main plugin file.
	 * @return Multi_Tier_Commissions The one true AffiliateWP_Multi_Tier_Commissions Plugin instance.
	 */
	public static function instance( string $file = '' ) : Multi_Tier_Commissions {

		if ( ! isset( self::$instance ) || ! ( self::$instance instanceof Multi_Tier_Commissions ) ) {

			self::$instance = new Multi_Tier_Commissions();

			self::$instance->file    = $file;
			self::$instance->version = get_plugin_data( self::$instance->file, false, false )['Version'] ?? '';

			self::$instance->set_plugin_data();
			self::$instance->setup_constants();
			self::$instance->load_textdomain();
			self::$instance->includes();
			self::$instance->init();
			self::$instance->hooks();
		}

		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {

		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?', 'affiliatewp-multi-tier-commissions' ) ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {

		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?', 'affiliatewp-multi-tier-commissions' ) ), '1.0.0' );
	}

	/**
	 * Sets up plugin constants.
	 *
	 * @since 1.0.0
	 */
	private function setup_constants() {

		// Plugin version.
		if ( ! defined( 'AFFWP_MTC_VERSION' ) ) {
			define( 'AFFWP_MTC_VERSION', $this->version );
		}

		// Plugin Folder Path.
		if ( ! defined( 'AFFWP_MTC_PLUGIN_DIR' ) ) {
			define( 'AFFWP_MTC_PLUGIN_DIR', plugin_dir_path( $this->file ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'AFFWP_MTC_PLUGIN_URL' ) ) {
			define( 'AFFWP_MTC_PLUGIN_URL', plugin_dir_url( $this->file ) );
		}

		// Plugin Root File.
		if ( ! defined( 'AFFWP_MTC_PLUGIN_FILE' ) ) {
			define( 'AFFWP_MTC_PLUGIN_FILE', $this->file );
		}
	}

	/**
	 * Loads the plugin language files.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory.
		$lang_dir = dirname( plugin_basename( $this->file ) ) . '/languages/';
		$lang_dir = apply_filters( 'aff_wp_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'affiliatewp-multi-tier-commissions' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'affiliatewp-multi-tier-commissions', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/affiliatewp-multi-tier-commissions/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/affiliate-wp-tiered/ folder.
			load_textdomain( 'affiliatewp-multi-tier-commissions', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/affiliate-wp-tiered/languages/ folder.
			load_textdomain( 'affiliatewp-multi-tier-commissions', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'affiliatewp-multi-tier-commissions', false, $lang_dir );
		}
	}

	/**
	 * Retrieve the chosen compensation plan.
	 *
	 * @since 1.4.0
	 *
	 * @return string The compensation plan option.
	 */
	public function get_compensation_plan() : string {
		return affiliate_wp()->settings->get( 'multi_tier_commissions_tier_compensation_plan', 'unilevel' );
	}

	/**
	 * Retrieve is MTC is activated.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function is_activated() : bool {

		if ( ! is_null( $this->is_activated ) ) {
			return $this->is_activated;
		}

		$this->is_activated = (bool) affiliate_wp()->settings->get( 'multi_tier_commissions' );

		return $this->is_activated;
	}

	/**
	 * Retrieve the max number of tiers.
	 *
	 * @since 1.0.0
	 *
	 * @return int The max number of tiers.
	 */
	public function get_max_tiers() : int {

		// Get the cached value if exists.
		if ( ! empty( self::$instance->max_tiers ) ) {
			return self::$instance->max_tiers;
		}

		/**
		 * Allow overriding the max number of tiers allowed when generating commissions.
		 *
		 * @since 1.1.0
		 *
		 * @param int $max_referral_tiers The max number of tiers.
		 */
		self::$instance->max_tiers = apply_filters( 'affiliatewp_mtc_max_tiers', self::MAX_REFERRAL_TIERS );

		// Check if we have any old filter calls. If not, we can return early.
		if ( ! has_filter( 'affwp_mtc_max_tiers' ) ) {
			return self::$instance->max_tiers;
		}

		/**
		 * Deprecated: Allow overriding the max number of tiers allowed when generating commissions.
		 *
		 * @param int $max_referral_tiers The max number of tiers.
		 * @deprecated 1.1.0 Throws deprecation notice.
		 *
		 * @since 1.0.0
		 */
		self::$instance->max_tiers = apply_filters( 'affwp_mtc_max_tiers', self::MAX_REFERRAL_TIERS );

		affiliatewp_deprecate_hook(
			'affwp_mtc_max_tiers',
			'will get deprecated next year. Use affiliatewp_mtc_max_tiers instead,',
			'1.1.0',
			E_USER_DEPRECATED,
			'AffiliateWP - Multi-Tier Commissions'
		);

		return self::$instance->max_tiers;
	}

	/**
	 * Retrieves the tiers' configuration.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function get_tiers() : array {

		if ( ! empty( $this->tiers ) ) {
			return $this->tiers;
		}

		$this->tiers = array_slice(
			affiliate_wp()->settings->get( 'tiers', array() ),
			0,
			affiliate_wp_mtc()->get_max_tiers()
		);

		return $this->tiers;
	}

	/**
	 * Include required files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		// Globally included.
		require_once AFFWP_MTC_PLUGIN_DIR . 'includes/class-network.php';
		require_once AFFWP_MTC_PLUGIN_DIR . 'includes/class-controller.php';

		// Adding globally will ensure support with Affiliate Area Tabs addon.
		require_once AFFWP_MTC_PLUGIN_DIR . 'includes/class-frontend.php';

		// Admin only files.
		if ( is_admin() ) {

			require_once AFFWP_MTC_PLUGIN_DIR . 'includes/admin/class-admin.php';
			require_once AFFWP_MTC_PLUGIN_DIR . 'includes/admin/class-settings.php';
		}
	}

	/**
	 * Initialize the bootstrap.
	 *
	 * @since 1.0.0
	 */
	private function init() {

		if ( is_admin() ) {
			self::$instance->updater();
		}
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		// Add template folder to hold the network template.
		add_filter( 'affwp_template_paths', [ $this, 'get_theme_template_paths' ] );
	}

	/**
	 * Attempts to run the updater script if it exists.
	 *
	 * @since 1.0.0
	 */
	public function updater() {

		if ( class_exists( 'AffWP_AddOn_Updater' ) ) {
			new \AffWP_AddOn_Updater( 812187, $this->file, $this->version );
		}
	}

	/**
	 * Set the plugin data from the plugin header.
	 *
	 * @since 1.0.0
	 */
	private function set_plugin_data() : void {

		require_once untrailingslashit( ABSPATH ) . '/wp-admin/includes/plugin.php';

		self::$instance->plugin_data = get_plugin_data( self::$instance->file, false, false );
		self::$instance->version     = self::$instance->plugin_data['Version'] ?? '';
	}

	/**
	 * Retrieve the plugin data.
	 *
	 * @since 1.0.0
	 *
	 * @return array Plugin data that correlates with the plugin's header.
	 */
	public function get_plugin_data() : array {
		return self::$instance->plugin_data;
	}

	/**
	 * Add template folder to hold the network templates.
	 *
	 * @since 1.0.0
	 *
	 * @param array $file_paths File paths.
	 */
	public function get_theme_template_paths( $file_paths ) {
		$file_paths[140] = plugin_dir_path( $this->file ) . 'templates';

		return $file_paths;
	}
}

/**
 * The main function responsible for returning the one true AffiliateWP_Multi_Tier_Commissions.
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $mtc = affiliate_wp_mtc(); ?>
 *
 * @since 1.0.0
 *
 * @return Multi_Tier_Commissions The one true plugin instance.
 */
function affiliate_wp_mtc() : Multi_Tier_Commissions {
	return Multi_Tier_Commissions::instance();
}
