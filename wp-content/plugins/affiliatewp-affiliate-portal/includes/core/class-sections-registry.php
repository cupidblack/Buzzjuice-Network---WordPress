<?php
/**
 * Core: View Sections Registry
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core;

use function AffiliateWP_Affiliate_Portal\html;

#[\AllowDynamicProperties]

/**
 * Implements a registry for portal view sections.
 *
 * @since 1.0.0
 *
 * @see Registry
 */
class Sections_Registry extends Registry {

	use Traits\Static_Registry, Traits\Registry_Filter;

	/**
	 * Initializes the sections registry.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		/**
		 * Fires during instantiation of the registry.
		 *
		 * @since 1.0.0
		 *
		 * @param Sections_Registry $this Registry instance.
		 */
		do_action( 'affwp_portal_sections_registry_init', self::instance() );
	}

	/**
	 * Registers a new view section.
	 *
	 * @since 1.0.0
	 *
	 * @param string $section_id (Unique) section ID.
	 * @param array  $attributes {
	 *     The section's attributes.
	 *
	 *     @type string   $desc                Section description. HTML will be stripped.
	 *     @type int      $priority            Priority by which to order this section inside the view. Default 25.
	 *     @type callable $permission_callback Callback to determine whether the section has permission to render.
	 *                                         Callback must return true or false. Default '__return_true'.
	 *                                         Signature: ( $section_id, $affiliate_id ) : bool.
	 *     @type string   $submit_label        Submit button label if there are form controls in the view.
	 *                                         Default 'Save settings.'
	 *     @type array    $submit_alpine       Array of alpine directives to pass to the submit button.
	 *     @type array    $form_alpine         Array of alpine directives to pass to the section form.
	 *     @type array    $columns             {
	 *         Column specifications for the section header and content areas.
	 *
	 *         @type int $header  Number of columns wide to make the section header. Accepts 1, 2, or 3.
	 *                            Default 1.
	 *         @type int $content Number of columns wide to make the section content area. Accepts 1, 2, or 3.
	 *                            Default 2.
	 *     }
	 * }
	 * @return true|\WP_Error True if the section was added, otherwise false.
	 */
	public function register_section( $section_id, $attributes ) {
		if ( $this->offsetExists( $section_id ) ) {
			$this->add_error( 'duplicate_section',
				sprintf( 'The \'%1$s\' section already exists.', $section_id ),
				$attributes
			);
		}

		if ( ! empty( $attributes['desc'] ) ) {
			$attributes['desc'] = wp_kses( $attributes['desc'], 'strip' );
		}

		if ( isset( $attributes['priority'] ) ) {
			$attributes['priority'] = absint( $attributes['priority'] );
		} else {
			$attributes['priority'] = 25;
		}

		if ( ! isset( $attributes['permission_callback'] ) ) {
			$attributes['permission_callback'] = '__return_true';
		} else {
			if ( ! is_callable( $attributes['permission_callback'] ) ) {
				$this->add_error( 'invalid_permission_callback',
					sprintf( 'The \'%1$s\' permission_callback attribute for the \'%2$s\' section is invalid.',
						$attributes['permission_callback'],
						$section_id
					),
					$attributes
				);
			}
		}

		// Submit button label.
		if ( empty( $attributes['submit_label'] ) ) {
			$attributes['submit_label'] = __( 'Save settings', 'affiliatewp-affiliate-portal' );
		}

		if ( ! isset( $attributes['preload_routes'] ) ) {
			$attributes['preload_routes'] = false;
		}

		if ( true === $this->has_errors() ) {
			$this->log_errors( $section_id . ' section' );

			return $this->get_errors();
		} else {
			return parent::add_item( $section_id, $attributes );
		}
	}

	/**
	 * Retrieves sections from the registry using optional filters.
	 *
	 * @since 1.0.0
	 *
	 * @param string $filter Optional. Filters to use when returning sections. Default empty.
	 * @return array|\WP_REST_Response|\WP_Error (Maybe filtered) sections.
	 */
	public function get_sections( $filter = '' ) {
		$sections = $this->get_items();

		if ( 'rest' === $filter ) {
			$sections = $this->get_rest_items( 'section' );
		}

		return $sections;
	}

	/**
	 * Retrieves a given section from the registry using an optional filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $section_id Section ID.
	 * @param string $filter  Optional. Filter to use when returning a single section. Default empty.
	 * @return array|\WP_REST_Response|\WP_Error (Maybe filtered) section.
	 */
	public function get_section( $section_id, $filter = '' ) {
		if ( ! $this->offsetExists( $section_id ) ) {
			$this->add_error( 'invalid_section', sprintf( 'The \'%s\' section does not exist.', $section_id ) );
		} else {
			$section = $this->get( $section_id );

			if ( 'rest' === $filter ) {
				$section = $this->get_rest_item( 'section', $section_id );
			}
		}

		if ( $this->has_errors() ) {
			return $this->get_errors();
		} else {
			return $section;
		}
	}

}
