<?php
/**
 * Core: First Party Integrations
 *
 * @package   Core
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */

namespace AffiliateWP_Affiliate_Portal\Core;

use AffiliateWP_Affiliate_Portal\Integrations as AddOns;

#[\AllowDynamicProperties]

/**
 * Core class that facilitates integrating first-party add-ons with the Portal.
 *
 * @since 1.0.0
 */
class Integrations {

	/**
	 * Bootstraps the Integrations class and sets up callbacks.
	 *
	 * @since 1.0.0
	 * @since 1.3.1 Added Multi-Tier Commissions addon support.
	 */
	public function __construct() {
		// Store Credit Add-on integration.
		if ( class_exists( 'AffiliateWP_Store_Credit' ) ) {
			( new AddOns\Store_Credit )->init();
		}

		// Custom Affiliate Slugs Add-on integration.
		if ( function_exists( 'affiliatewp_custom_affiliate_slugs' ) ) {
			( new AddOns\Custom_Affiliate_Slugs )->init();
		}

		// Pushover Notifications Add-on Integration
		if ( class_exists( 'AffiliateWP_Pushover' ) ) {
			( new AddOns\Pushover_Notifications )->init();
		}

		// Order Details for Affiliates Add-on integration.
		if ( class_exists( 'AffiliateWP_Order_Details_For_Affiliates' ) ) {
			( new AddOns\Order_Details_for_Affiliates() )->init();
        }

		// Lifetime Commissions Add-on integration.
		if ( class_exists( 'AffiliateWP_Lifetime_Commissions' ) ) {
			( new AddOns\Lifetime_Commissions )->init();
		}

		// Direct Link Tracking Add-on integration.
		if ( class_exists( 'AffiliateWP_Direct_Link_Tracking' ) ) {
			( new AddOns\Direct_Link_Tracking )->init();
		}

		// Landing Pages Add-on integration.
		if ( function_exists( 'AffiliateWP_Affiliate_Landing_Pages' ) ) {
			( new AddOns\Landing_Pages )->init();
		}

		// Multi-Tier Add-on integration.
		if ( class_exists( '\AffiliateWP\MTC\Multi_Tier_Commissions' ) ) {
			( new AddOns\Multi_Tier_Commissions() )->init();
		}
		// Advanced Coupons Addon integration.
		if ( class_exists( 'ACFWF', false ) ) {
			$instance = new Addons\Advanced_Coupons();

			$instance->init();
		}

	}

}
