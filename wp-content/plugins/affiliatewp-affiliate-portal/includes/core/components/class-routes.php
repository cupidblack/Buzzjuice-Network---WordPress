<?php
/**
 * Components: Routes API
 *
 * @package   Core/Components
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components;

use AffiliateWP_Affiliate_Portal\Core\Traits;
use AffiliateWP_Affiliate_Portal\Core\Routes_Registry;
use AffiliateWP_Affiliate_Portal\Core\Views_Registry;

#[\AllowDynamicProperties]

/**
 * Routes API.
 *
 * @since 1.0.0
 */
class Routes {

	use Traits\REST_Support;

	/**
	 * Sets up the page routing API.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Set up REST support.
		$this->bootstrap_rest_support();

		$routes_registry = Routes_Registry::instance();
		$routes_registry->init();
	}

	/**
	 * Registers REST routes.
	 *
	 * @since 1.0.0
	 *
	 * @see register_rest_route()
	 */
	public function register_rest_routes() {

		// affwp/v1/portal/routes
		register_rest_route( $this->namespace, 'routes', array(
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_routes' ),
				'args'                => $this->get_rest_collection_params( 'routes' ),
				'permission_callback' => array( $this, 'rest_affiliate_permission_cb' ),
			),
			'schema' => array( $this, 'get_route_schema' ),
		) );
	}

	/**
	 * Retrieves registered routes.
	 *
	 * @since 1.0.0
	 *
	 * @return \WP_REST_Response|\WP_HTTP_Response|\WP_Error Registered routes.
	 */
	public function get_routes() {
		$registry = Routes_Registry::instance();

		return $registry->get_rest_items( 'route' );
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
			case 'routes':
				$params = array_merge( $params, array(
					'routeId' => array(
						'description'       => __( 'The route pattern.', 'affiliatewp-affiliate-portal' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_string( $param );
						},
					),
					'vars'  => array(
						'description'       => __( 'Variables used to build the rewrite rule.', 'affiliatewp-affiliate-portal' ),
						'sanitize_callback' => '',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_array( $param );
						},
					),
					'viewId'   => array(
						'description'       => __( 'View ID the route is registered for.', 'affiliatewp-affiliate-portal' ),
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => function ( $param, $request, $key ) {
							return is_string( $param );
						},
					),
				) );
				break;

			default:
				break;
		}

		return $params;
	}

	/**
	 * Retrieves the schema for a single route, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_route_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'affwp_ad_route',
			'type'       => 'object',
			// Base properties for every route.
			'properties' => array(
				'routeId'       => array(
					'description' => __( 'The route pattern.', 'affiliatewp-affiliate-portal' ),
					'type'        => 'string',
				),
				'vars'   => array(
					'description' => __( 'Variables used to build the rewrite rule.', 'affiliatewp-affiliate-portal' ),
					'type'        => 'array',
				),
				'viewId'      => array(
					'description' => __( 'View ID the route is registered for.', 'affiliatewp-affiliate-portal' ),
					'type'        => 'string',
				),
			),
		);

		// TODO implement additional fields support.
		return $schema;
	}
}
