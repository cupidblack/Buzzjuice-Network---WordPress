<?php
/**
 * Core: Icons Registry
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
 * Implements an icons registry class.
 *
 * @since 1.0.0
 *
 * @see \AffWP\Utils\Registry
 */
class Icons_Registry extends \AffWP\Utils\Registry {

	use Traits\Static_Registry, Traits\Error_Handler, Traits\Registry_Filter;

	/**
	 * Initializes the icons registry.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		/**
		 * Fires during instantiation of the icons registry.
		 *
		 * @since 1.0.0
		 *
		 * @param Icons_Registry $this Registry instance.
		 */
		do_action( 'affwp_portal_icons_registry_init', self::instance() );
	}

	/**
	 * Adds an icon to the registry.
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon_slug Icon slug (unique).
	 * @param array  $attributes {
	 *     Attributes associated with the icon.
	 *
	 *     @type string $outline Outline SVG code for the outline version (if any).
	 *     @type string $solid SVG code for the solid version (if any).
	 * }
	 * @return true|\WP_Error True on success, otherwise \WP_Error object.
	 */
	public function add_icon( $icon_slug, $attributes ) {

		if ( $this->offsetExists( $icon_slug ) ) {
			$this->add_error( 'duplicate_icon_slug',
				sprintf( 'The %s icon already exists.', $icon_slug ),
				$attributes
			);
		}

		if ( $this->has_errors() ) {
			return $this->get_errors();
		}

		return parent::add_item( $icon_slug, $attributes );
	}

	/**
	 * Retrieves the markup for a given icon and type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $icon_slug Icon slug.
	 * @param string $type      Icon type attribute to retrieve (if set).
	 * @return string Markup if the icon type attribute is set, otherwise an empty string.
	 */
	public function get_icon_type( $icon_slug, $type ) {
		$output = '';

		$icon = $this->get( $icon_slug );

		if ( false === $icon ) {
			return $output;
		}

		if ( isset( $icon[ $type ] ) ) {
			$output = $icon[ $type ];
		}

		return $output;
	}
}
