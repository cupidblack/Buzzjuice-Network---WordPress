<?php
/**
 * Core: Functions
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal {
	/**
	 * Helper used to retrieve an instance of the HTML util class for outputting (mostly) HTML form elements.
	 *
	 * @since 1.0.0
	 *
	 * @return Utilities\HTML HTML instance.
	 */
	function html() {
		return ( new Utilities\HTML );
	}
}

namespace {

	use AffiliateWP_Affiliate_Portal\Core\Components\Portal;
	use AffiliateWP_Affiliate_Portal\Core\Views_Registry;
	use function AffiliateWP_Affiliate_Portal\html;

	/**
	 * Determines whether or not we're within the portal.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $view_or_views Optional. View or view slugs to check. Default empty array (unused).
	 * @return bool If `$view_or_views` is defined, true if the current view is a match, otherwise false. If no
	 *              views are defined, whether the current request is for the affiliate portal.
	 */
	function affwp_is_affiliate_portal( $view_or_views = array() ) {

		// Check if the current view matches any of those supplied.
		if ( ! empty( $view_or_views ) ) {

			if ( ! is_array( $view_or_views ) ) {
				$view_or_views = array( $view_or_views );
			}

			$current_view = Portal::get_current_view_slug();

			return in_array( $current_view, $view_or_views, true );

		} else {

			// Otherwise just check if this is a general affiliate portal request.
			return affwp_is_portal_enabled() && affwp_is_affiliate_area();

		}
	}

	/**
	 * Determines whether or not the Affiliate Portal is enabled.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the affiliate portal is enabled.
	 */
	function affwp_is_portal_enabled() {
		return (bool) affiliate_wp()->settings->get( 'portal_enabled', false );
	}

}
