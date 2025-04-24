<?php
/**
 * Components: Utilities Bootstrap
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core/Components
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components;

use AffiliateWP_Affiliate_Portal\Core\Reports_Registry;
use AffiliateWP_Affiliate_Portal\Core\Traits;
use AffiliateWP_Affiliate_Portal\Utilities\Compat;

#[\AllowDynamicProperties]

/**
 * Utilities (catchall) class.
 *
 * @since 1.0.0
 */
class Utilities {

	use Traits\REST_Support;

	/**
	 * Compatibility bootstrap.
	 *
	 * @since 1.0.0
	 * @var   Compat
	 */
	public $compat;

	/**
	 * Sets up the page routing API.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Set up REST support.
		$this->bootstrap_rest_support();

		$this->compat = new Compat;
	}

	/**
	 * Registers REST routes.
	 *
	 * @since 1.0.0
	 *
	 * @see register_rest_route()
	 */
	public function register_rest_routes() {

		/**
		 * Filters whether to allow bypassing the URL authority in the URL generator.
		 *
		 * @since 1.0.5
		 *
		 * @param bool $bypass Whether to bypass the URL authority. Default false.
		 */
		$bypass_url_authority = (bool) apply_filters( 'affwp_portal_bypass_url_authority', false );

		// affwp/v2/portal/settings
		register_rest_route( $this->namespace, 'settings', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'permission_callback' => array( $this, 'rest_is_affiliate_permission_cb' ),
				'callback'            => function( $request ) use ( $bypass_url_authority ) {
					return rest_ensure_response( array(
						'referral_var'           => affiliate_wp()->tracking->get_referral_var(),
						'pretty_affiliate_urls'  => affwp_is_pretty_referral_urls(),
						'referral_format_value'  => urldecode( affwp_get_referral_format_value() ),
						'enable_payouts_service' => affiliate_wp()->settings->get( 'enable_payouts_service', false ),
						'bypass_url_authority'   => $bypass_url_authority,
					) );
				},
			),
		) );

		// Registers a base_url field against the core affiliates/ID REST endpoint.
		register_rest_field( 'affwp_affiliate', 'base_url', array(
			'get_callback' => function( $object, $field_name, $request, $object_type ) {
				return affwp_get_affiliate_base_url();
			},
		) );
	}

	/**
	 * Retrieves parameters for the given collection.
	 *
	 * @since 1.0.0
	 *
	 * @param string $collection Collection to retrieve parameters for.
	 * @return array Collection parameters (if any), otherwise an empty array.
	 */
	public function get_rest_collection_params( $collection ) {
		$params = array(
			'context' => array(
				'default' => 'view'
			),
		);

		switch ( $collection ) {
			default:
				break;
		}

		return $params;
	}

}
