<?php
/**
 * Core: Routes Registry
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core;

#[\AllowDynamicProperties]

/**
 * Implements a registry for routing component views.
 *
 * @since 1.0.0
 *
 * @see Registry
 */
class Routes_Registry extends Registry {

	use Traits\Static_Registry, Traits\Registry_Filter;

	/**
	 * Rewrite rules to register.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $rewrites = array();

	/**
	 * Initializes the routes registry.
	 *
	 * @since 1.0.0
	 * @since 1.3.6 Added hook for flushing permalinks when rewrites are flushed
	 *               before portal gets to re-add it's rewrites, see https://github.com/awesomemotive/affiliate-wp/issues/5335.
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_rewrites' ], -9999 );
	}

	/**
	 * Registers a new route and any of its variations.
	 *
	 * @since 1.0.0
	 *
	 * @param string $route      Route.
	 * @param array  $attributes {
	 *     Route attributes.
	 *
	 *     @type string $slug      Optional. Route base slug. This is the primary slug used to register the rewrite
	 *                             for accessing the view. If unspecified, default is the value of the view ID.
	 *     @type array  $vars      Optional. Additional rewrite variables to save along with the base `$slug` when
	 *                             the rewrite rule is added.
	 *     @type array  $secondary {
	 *         Optional. Secondary rewrite slug/pattern to match against in addition to the base. Accepts an array
	 *         containing the rewrite pattern (`$pattern`) and optionally extra pattern-related rewrite variables
	 *         (`$vars`).
	 *
	 *         @type string $pattern Optional. The rewrite pattern to append to `$slug` when adding the secondary
	 *                               rewrite rule.
	 *         @type array  $vars    Optional. The rewrite variables to save against the `$pattern` value when
	 *                               the secondary rewrite rule is added.
	 *     }
	 * }
	 * @return true|\WP_Error True if the route was added, otherwise false.
	 */
	public function add_route( $route, $attributes ) {

		if ( $this->offsetExists( $route ) ) {
			$this->add_error( 'duplicate_component_route',
				sprintf( 'The %s component route already exists.', $route ),
				$attributes
			);
		}

		if ( true === $this->has_errors() ) {
			return $this->get_errors();
		}

		$vars = array();

		if ( ! empty( $attributes['vars'] ) ) {
			$vars = $attributes['vars'];
		}

		$secondary = ! empty( $attributes['secondary'] ) ? $attributes['secondary'] : array();

		//
		// Rewrites (collected now, later registered on the 'init' hook)
		//

		// Always add the base route.
		$this->rewrites[ $route . '/?$' ] = $vars;

		// If a secondary, pattern-based route was defined, add it in addition to the base route.
		if ( ! empty( $secondary ) ) {
			if ( ! empty( $secondary['pattern'] ) && ! empty( $secondary['vars'] ) ) {
				$this->rewrites[ $route . $secondary['pattern'] ] = $secondary['vars'];
			}
		}

		// Register the route.
		return parent::add_item( $route, $attributes );
	}

	/**
	 * Registers rewrite rules for routes.
	 *
	 * @since 1.0.0
	 * @since 1.0.1  Adjusted to account for Affiliate Area as a sub-page.
	 * @since 1.3.6 Added rewrite flush after registering rewrites with WordPress;
	 *               Note this happens only when the plugin is initially activated
	 *               or a system flush happens before the portal addon gets to add
	 *               these rewrites to the system, see https://github.com/awesomemotive/affiliate-wp/issues/5335.
	 */
	public function register_rewrites() {

		if ( empty( $this->rewrites ) ) {
			return; // No rewrites.
		}

		if ( ! affwp_is_portal_enabled() ) {
			return; // Portal not enabled.
		}

		$affiliate_area_page_url = affwp_get_affiliate_area_page_url();

		if ( $affiliate_area_page_url ) {
			$base = str_replace( home_url(), '', $affiliate_area_page_url );
			$base = trim( $base, '/' );
		} else {
			$base = '';
		}

		// Remove index.php from base for pagename.
		$pagename = str_replace( 'index.php/', '', $base );

		foreach ( $this->rewrites as $route => $vars ) {

			// Inject the affiliate area page info into the rewrite rule info.
			if ( ! empty( $base ) ) {
				$route = $base . '/' . $route;

				$vars = array_merge( $vars, array(
					'pagename' => $pagename,
				) );
			}

			add_rewrite_rule( $route, add_query_arg( $vars, 'index.php' ), 'top' );
		}

		// Should we flush the rewrite rules after adding the rewrite rules?
		if ( 'flush_rewrites' !== get_option( 'affiliatewp_portal_flush_rewrite_rules_after_register_rewrites' ) ) {
			return; // Don't flush rewrites, not instructed to.
		}

		flush_rewrite_rules( false );

		delete_option( 'affiliatewp_portal_flush_rewrite_rules_after_register_rewrites' ); // Don't flush again unless instructed to.
	}
}
