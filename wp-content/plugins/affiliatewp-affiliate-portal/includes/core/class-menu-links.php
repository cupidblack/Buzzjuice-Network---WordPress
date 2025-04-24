<?php
/**
 * Core: Menu Links
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core/Components
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.8
 */
namespace AffiliateWP_Affiliate_Portal\Core;

#[\AllowDynamicProperties]

/**
 * Manages retrieving and manipulating custom menu links.
 *
 * @since 1.0.8
 */
class Menu_Links {

	/**
	 * Gets set to true after init runs. Ensures that init only runs one time.
	 *
	 * @since 1.0.9
	 *
	 * @var bool True if init ran, otherwise false.
	 */
	protected static $init_ran = false;

	/**
	 * Sets up the menu links component.
	 *
	 * @since 1.0.9
	 */
	public function __construct() {
		if ( false === self::$init_ran ) {
			$this->init();
		}

		self::$init_ran = true;
	}

	/**
	 * Sets up hook callbacks needed by the component.
	 *
	 * @since 1.0.8
	 * @since 1.0.9 Now redirects non-affiliates who visit menu links directly to the affiliate area.
	 */
	public function init() {
		add_action( 'template_redirect', array( $this, 'redirect_non_affiliates_on_menu_pages' ) );
	}

	/**
	 * Determines whether the given item is a custom menu link.
	 *
	 * @since 1.0.8
	 * @since 1.0.9 Menu link can now be either a post ID or a slug.
	 *
	 * @param string|int $id_or_slug The link value to check. Can be a post ID, or a slug.
	 *
	 * @return bool True if the slug matches a custom menu link, otherwise false.
	 */
	public function is_menu_link( $id_or_slug ) {
		// Bail if empty.
		if ( empty( $id_or_slug ) ) {
			return false;
		}

		// First, we have to figure out what we're going to search by.
		if ( is_int( $id_or_slug ) ) {
			$key = 'id';
		} else {
			$key = 'slug';
		}

		// Loop through the links, and try to find the provided post ID.
		foreach ( $this->get_menu_links() as $link ) {

			// If the value matches, we found it. Stop looping and return true.
			if ( isset( $link[ $key ] ) && (int) $link[ $key ] === (int) $id_or_slug ) {
				return true;
			}
		}

		// Not found, return false.
		return false;
	}

	/**
	 * Retrieves the list of custom menu links.
	 *
	 * @since 1.0.8
	 *
	 * @return array Array of the custom menu links.
	 */
	public function get_menu_links() {
		$affiliate_portal_settings = affiliate_wp()->settings->get( 'affiliate_portal', array() );

		$menu_links = isset( $affiliate_portal_settings['portal_menu_links'] ) ? $affiliate_portal_settings['portal_menu_links'] : array();

		// filter only menu links with a page.
		$menu_links = array_filter(
			$menu_links,
			function( $menu_link) {
				return ! empty( $menu_link['id'] );
			}
		);

		return $menu_links;
	}

	/**
	 * Retrieves a list of pages minus the Affiliate Area page.
	 *
	 * @since 1.0.8
	 *
	 * @return array Array of pages.
	 */
	public function get_pages() {
		$pages             = affwp_get_pages();
		$affiliate_area_id = affiliate_wp()->settings->get( 'affiliates_page' );

		if ( ! empty( $pages[ $affiliate_area_id ] ) ) {
			unset( $pages[ $affiliate_area_id ] );
		}

		return $pages;
	}

	/**
	 * Makes a slug for the menu link.
	 *
	 * @since 1.0.8
	 *
	 * @param string $title Menu link title.
	 * @return string Slug.
	 */
	public function make_slug( $title ) {

		$slug = rawurldecode( sanitize_title_with_dashes( $title ) );

		return $slug;
	}

	/**
	 * Get links slug/title pairs.
	 *
	 * @since 1.0.8
	 *
	 * @return array The array of links to show.
	 */
	public function get_link_pairs() {

		$links = array();

		$saved_links = $this->get_menu_links();

		if ( $saved_links ) {

			foreach ( $saved_links as $link ) {
				if ( isset( $link['slug'] ) ) {
					$links[ $link['slug'] ] = $link['label'];
				}
			}
		}

		return $links;
	}

	/**
	 * Redirects any non-affiliate when visiting an affiliate menu page.
	 *
	 * @since 1.0.9
	 *
	 * @return void
	 */
	public function redirect_non_affiliates_on_menu_pages() {
		// Bail early if the current user is an affiliate.
		if ( affwp_is_affiliate() ) {
			return;
		}

		$affiliate_page_id = affwp_get_affiliate_area_page_id();
		$current_id        = get_the_ID();

		// Bail early if we're currently on the affiliate area page. This prevents redirect loops.
		if ( $affiliate_page_id === $current_id ) {
			return;
		}

		// If this is a portal menu link, redirect to the affiliate area
		if ( true === $this->is_menu_link( $current_id ) ) {
			wp_safe_redirect( affwp_get_affiliate_area_page_url() );
		}
	}
}
