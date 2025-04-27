<?php
/**
 * Core: Plugin Bootstrap
 *
 * @package     AffiliateWP Recurring Referrals
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main plugin bootstrap.
 *
 * @since 1.0.0
 */
final class AffiliateWP_Recurring_Referrals {

	/**
	 * Holds the instance
	 *
	 * @var   AffiliateWP_Recurring_Referrals The one true AffiliateWP_Recurring_Referrals
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * Plugin version
	 *
	 * @var int  The plugin version.
	 */
	public $version = '1.9.2';

	/**
	 * Main plugin file.
	 *
	 * @since 1.1
	 * @var   string
	 */
	private $file = '';

	/**
	 * Main AffiliateWP_Recurring_Referrals Instance
	 *
	 * Insures that only one instance of AffiliateWP_Recurring_Referrals exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static
	 * @staticvar array $instance
	 *
	 * @param string $file Main plugin file.
	 * @return self The one true AffiliateWP_Recurring_Referrals
	 */
	public static function instance( $file = null ) {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AffiliateWP_Recurring_Referrals ) ) {
			self::$instance  = new AffiliateWP_Recurring_Referrals;

			self::$instance->file = $file;
			self::$instance->setup_constants();
			self::$instance->load_textdomain();
			self::$instance->includes();
			self::$instance->init();

		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliate-wp-recurring-referrals' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliate-wp-recurring-referrals' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since  1.7
	 * @return void
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'AFFWP_RR_VERSION' ) ) {
			define( 'AFFWP_RR_VERSION', $this->version );
		}

		// Plugin Folder Path.
		if ( ! defined( 'AFFWP_RR_PLUGIN_DIR' ) ) {
			define( 'AFFWP_RR_PLUGIN_DIR', plugin_dir_path( $this->file ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'AFFWP_RR_PLUGIN_URL' ) ) {
			define( 'AFFWP_RR_PLUGIN_URL', plugin_dir_url( $this->file ) );
		}

		// Plugin Root File.
		if ( ! defined( 'AFFWP_RR_PLUGIN_FILE' ) ) {
			define( 'AFFWP_RR_PLUGIN_FILE', $this->file );
		}
	}

	/**
	 * Loads the plugin language files
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory.
		$lang_dir = dirname( plugin_basename( $this->file ) ) . '/languages/';
		$lang_dir = apply_filters( 'aff_wp_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter.
		$locale   = apply_filters( 'plugin_locale', get_locale(), 'affiliate-wp-recurring-referrals' );
		$mofile   = sprintf( '%1$s-%2$s.mo', 'affiliate-wp-recurring-referrals', $locale );

		// Setup paths to current locale file.
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/affiliate-wp-recurring-referrals/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/affiliate-wp-recurring-referrals/ folder.
			load_textdomain( 'affiliate-wp-recurring-referrals', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/affiliate-wp-recurring-referrals/languages/ folder.
			load_textdomain( 'affiliate-wp-recurring-referrals', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'affiliate-wp-recurring-referrals', false, $lang_dir );
		}
	}

	/**
	 * Include required files
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function includes() {

		if ( is_admin() ) {

			require_once AFFWP_RR_PLUGIN_DIR . 'admin/affiliates.php';
			require_once AFFWP_RR_PLUGIN_DIR . 'admin/settings.php';
			require_once AFFWP_RR_PLUGIN_DIR . 'admin/scripts.php';
			require_once AFFWP_RR_PLUGIN_DIR . 'admin/upgrades.php';

		}

		// Check that recurring referrals are enabled.
		if ( ! affiliate_wp()->settings->get( 'recurring' ) ) {
			return;
		}

		require_once AFFWP_RR_PLUGIN_DIR . 'integrations/class-base.php';

		// Load the class for each integration enabled.
		foreach ( affiliate_wp()->integrations->get_enabled_integrations() as $filename => $integration ) {

			if ( file_exists( AFFWP_RR_PLUGIN_DIR . 'integrations/class-' . $filename . '.php' ) ) {
				require_once AFFWP_RR_PLUGIN_DIR . 'integrations/class-' . $filename . '.php';
			}
		}

	}

	/**
	 * Add in our filters to affect affiliate rates
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function init() {

		if ( is_admin() ) {
			self::$instance->updater();
		}

		add_action( 'admin_notices', array( $this, 'ignore_zero_referrals_notice' ) );

	}

	/**
	 * Load the custom plugin updater
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	public function updater() {

		if ( class_exists( 'AffWP_AddOn_Updater' ) ) {
			$updater = new AffWP_AddOn_Updater( 1670, $this->file, $this->version );
		}
	}

	/**
	 * Show a notice if the Ignore Zero Referrals option is enabled in AffiliateWP core.
	 *
	 * @since  1.6
	 * @return string Admin notice.
	 */
	public function ignore_zero_referrals_notice() {

		// Bail if not an AffiliateWP admin page.
		if ( ! affwp_is_admin_page() ) {
			return;
		}

		// Bail if ignore the zero referrals option isn't checked, or if recurring referrals are not enabled.
		if ( ! affiliate_wp()->settings->get( 'ignore_zero_referrals' ) || ! affiliate_wp()->settings->get( 'recurring' ) ) {
			return;
		}

		// Bail if settings are not being updated in AffiliateWP.
		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
			$setting = 'If using free trials in your subscriptions, you must disable the Ignore Zero Referrals option. ';
			$message = __( 'Recurring referral tracking does not work unless there is a referral record created for the initial payment in the subscription (even if that payment is 0.00), so when selling subscriptions with free trials, it is required that 0.00 referrals be allowed, as that 0.00 referral created when the trial is started acts as the canonical source for the recurring referrals.', 'affiliate-wp-recurring-referrals' );

			printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p><p>%2$s</p></div>', esc_attr( $setting ), esc_html( $message ) );
		}

	}

	/**
	 * Currently supported integrations for per affiliate per product recurring rates.
	 *
	 * @since  1.7
	 * @return array supported integrations
	 */
	public function per_affiliate_per_product_recurring_rates_supported_integrations() {

		$supported_integrations = array(
			'edd',
			'woocommerce',
		);

		return $supported_integrations;
	}

	/**
	 * Get recurring products.
	 *
	 * @since 1.7
	 *
	 * @param string $context Specific context to query products for.
	 * @return array $products The recurring/subscription products
	 */
	public function get_recurring_products( $context ) {

		$products = array();

		switch ( $context ) {

			case 'edd':
				// Get recurring downloads.
				$args = array(
					'post_type'      => 'download',
					'orderby'        => 'title',
					'order'          => 'ASC',
					'meta_key'       => 'edd_recurring',
					'meta_value'     => 'yes',
					'posts_per_page' => 300,
				);

				$downloads = get_posts( $args );

				foreach ( $downloads as $download ) {
					$products[ $download->ID ] = $download->post_title;
				}

				break;

			case 'woocommerce':
				if ( function_exists( 'wc_get_products' ) ) {

					// Get subscription products.
					$args = array(
						'orderby' => 'title',
						'order'   => 'ASC',
						'type'    => array( 'subscription', 'variable-subscription' ),
						'limit'   => 300,
					);

					$subscription_products = wc_get_products( $args );

					foreach ( $subscription_products as $product ) {
						$products[ $product->get_id() ] = $product->get_name();
					}
				}

				break;
		}

		if ( ! empty( $products ) ) {

			return $products;

		}

		// Return empty array.
		return array();
	}

	/**
	 * Retrieve the product rates from user meta
	 *
	 * @access public
	 * @since 1.7
	 *
	 * @param int $affiliate_id Affiliate ID.
	 * @return array
	 */
	public function get_affiliate_recurring_product_rates( $affiliate_id = 0 ) {
		$rates = affwp_get_affiliate_meta( $affiliate_id, 'recurring_product_rates', true );

		return $rates;
	}

}

/**
 * The main function responsible for returning the one true AffiliateWP_Recurring_Referrals
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $AffiliateWP_Recurring_Referrals = affiliate_wp_recurring(); ?>
 *
 * @since  1.0
 * @return object The one true AffiliateWP_Recurring_Referrals Instance
 */
function affiliate_wp_recurring() {
	return AffiliateWP_Recurring_Referrals::instance();
}
