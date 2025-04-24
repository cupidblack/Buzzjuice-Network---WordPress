<?php
/**
 * Traits: REST Support
 *
 * @package   Core/Traits
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Traits;

/**
 * Implements REST support for a class.
 *
 * @since 1.0.0
 */
trait REST_Support {

	/**
	 * REST namespace.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $namespace = 'affwp/v2/portal';

	/**
	 * Sets up hook callbacks for registering REST routes and should be called by the composing class.
	 *
	 * @since 1.0.0
	 */
	public function bootstrap_rest_support() {
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	/**
	 * Required method for registering REST routes.
	 *
	 * @since 1.0.0
	 *
	 * @see register_rest_route()
	 */
	abstract public function register_rest_routes();

	/**
	 * Retrieves the collection parameters.
	 *
	 * @since 1.0.0
	 *
	 * @param string $collection REST route to retrieve collection parameters for.
	 * @return array Collection parameters.
	 */
	abstract public function get_rest_collection_params( $collection );

	/**
	 * Permission callback checking if the request matches the current affiliate.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request WP_REST_Request object.
	 * @return bool True if the request matches the current affiliate, otherwise false.
	 */
	public function rest_affiliate_permission_cb( \WP_REST_Request $request ) {
		return affwp_get_affiliate_id() === $request->get_param( 'affiliate_id' ) || current_user_can( 'manage_affiliate_options' );
	}

	/**
	 * Permission callback checking if the current user is an affiliate.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_REST_Request $request WP_REST_Request object.
	 * @return bool True if the current user is an affiliate, otherwise false.
	 */
	public function rest_is_affiliate_permission_cb( \WP_REST_Request $request ) {
		return false !== affwp_get_affiliate_id() || current_user_can( 'manage_affiliate_options' );
	}

}
