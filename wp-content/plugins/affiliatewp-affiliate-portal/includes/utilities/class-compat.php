<?php
/**
 * Utilities: Compatibility Code
 *
 * Use this class to add compat code with other plugins and themes (as needed).
 *
 * @package   Core/Utilities
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Utilities;

/**
 * Class that implements compatibility with other plugins and themes.
 *
 * @since 1.0.0
 */
class Compat {

	/**
	 * Sets up compatibility hook callbacks.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// SG Optimizer.
		add_filter( 'sgo_js_minify_exclude', array( $this, 'sgo_js_minify_exclude') );

		// Payouts Service.
		add_filter( 'affwp_payout_methods', array( $this, 'add_ps_payout_method' ) );

		add_action( 'wp', function() {
			// Disable lazy loading for affiliate portal pages.
			if ( affwp_is_affiliate_portal() ) {
				// Smush.
				add_filter( 'smush_skip_image_from_lazy_load', '__return_true' );

				// Autoptimize.
				add_filter( 'autoptimize_filter_imgopt_should_lazyload', '__return_false' );

				// A3 Lazy Load.
				add_filter( 'a3_lazy_load_run_filter', '__return_false' );
			}
		} );
	}

	/**
	 * Adds our view scripts to the JS minification exclusion list in the SG Optimizer plugin.
	 *
	 * @since 1.0.0
	 *
	 * @param array $handles Script handles to exclude.
	 * @return array Modified list of script handles.
	 */
	public function sgo_js_minify_exclude( $handles ) {
		$view_scripts = affiliatewp_affiliate_portal()->assets->get_view_scripts();

		return array_merge( $handles, array_keys( $view_scripts ) );
	}

	/**
	 * Adds 'Payouts Service' as a payout method to AffiliateWP.
	 *
	 * As of AffiliateWP 2.6.4.1, the 'payouts-service' payout method is only registered if is_admin()
	 * in class-payouts-service.php. Whoops.
	 *
	 * @since 1.0.0
	 *
	 * @param array $payout_methods Payout methods.
	 * @return array Filtered payout methods.
	 */
	public function add_ps_payout_method( $payout_methods ) {
		$payout_methods['payouts-service'] = __( 'Payouts Service', 'affiliatewp-affiliate-portal' );

		return $payout_methods;
	}

}
