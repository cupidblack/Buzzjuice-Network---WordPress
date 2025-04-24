<?php
/**
 * Core: Views Registry
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls\Base_Control;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls\Icon_Control;
use function AffiliateWP_Affiliate_Portal\html;

#[\AllowDynamicProperties]

/**
 * Implements a registry for portal views.
 *
 * @since 1.0.0
 *
 * @see Registry
 */
class Views_Registry extends Registry {

	use Traits\Static_Registry, Traits\Registry_Filter;

	/**
	 * Initializes the views registry.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		if ( is_admin() ) {
			return; // Do not fire view registry in the admin, isn't necessary.
		}

		/**
		 * Fires during instantiation of the views registry.
		 *
		 * @since 1.0.0
		 *
		 * @param Views_Registry $this Registry instance.
		 */
		do_action( 'affwp_portal_views_registry_init', self::instance() );
	}

	/**
	 * Registers a new view.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view_id    (Unique) view ID.
	 * @param array  $attributes {
	 *     The view's attributes.
	 *
	 *     @type int           $priority            Priority by which to order this section inside the view. Default 25.
	 *     @type array         $route               {
	 *         Optional. Route-related attributes. Ignored if `$external_url` is defined.
	 *
	 *         @type string $slug      Optional. Route base slug. This is the primary slug used to register the rewrite
	 *                                 for accessing the view. If unspecified, default is the value of the view ID.
	 *         @type array  $vars      Optional. Additional rewrite variables to save along with the base `$slug` when
	 *                                 the rewrite rule is added.
	 *         @type array  $secondary {
	 *             Optional. Secondary rewrite slug/pattern to match against in addition to the base. Accepts an array
	 *             containing the rewrite pattern (`$pattern`) and optionally extra pattern-related rewrite variables
	 *             (`$vars`).
	 *
	 *             @type string $pattern Optional. The rewrite pattern to append to `$slug` when adding the secondary
	 *                                   rewrite rule.
	 *             @type array  $vars    Optional. The rewrite variables to save against the `$pattern` value when
	 *                                   the secondary rewrite rule is added.
	 *         }
	 *     }
	 *     @type string         $external_url        External URL to link the menu item to rather than a portal-generated
	 *                                               view. If defined, `$icon` will be ignored.
	 *     @type string         $label               View link label.
	 *     @type string         $menu_label          View link menu label. Default is the value of `$label`.
	 *     @type string         $icon                Optional. View link icon. If `$external_url` is defined, will always
	 *                                               use 'external-link'. Default empty.
	 *     @type array          $sections            {
	 *         Section attributes. Required unless `$external_url` is defined.
	 *
	 *         @type string $label   Section label.
	 *         @type string $desc    Optional. Description to display below the section label as a paragraph.
	 *                               HTML not allowed.
	 *         @type bool   $wrapper Whether to style the wrapper around the section content area. Default true.
	 *         @type array  $columns {
	 *             Column specifications for the header and content areas with a combined total of 3 columns.
	 *
	 *             @type int  $header  Header col-span. Accepts 1-3. Default 1.
	 *             @type int  $content Section content col-span. Accepts 1-3. Default 2.
	 *         }
	 *     }
	 *     @type Base_Control[] $controls            View control objects. Required unless `$external_url` is defined.
	 *     @type bool           $hideFromMenu        Whether to hide the view link from the menu. Always false if
	 *                                               `$external_url` is defined. Default false.
	 *     @type callable       $permission_callback Callback to determine whether the view has permission to render.
	 *                                               Callback must return true or false. Default '__return_true'.
	 *                                               Signature: ( $view_id, $affiliate_id ) : bool.
	 * }
	 * @return true|\WP_Error True if the view was added, otherwise false.
	 */
	public function register_view( $view_id, $attributes ) {

		$routes_registry = Routes_Registry::instance();

		$defaults = array(
			'hideFromMenu' => false,
		);

		$attributes = array_merge( $defaults, $attributes );

		if ( $this->offsetExists( $view_id ) ) {
			$this->add_error( 'duplicate_view',
				sprintf( 'The \'%1$s\' view already exists.', $view_id ),
				$attributes
			);
		}

		if ( 'home' === $view_id ) {
				$attributes['priority'] = 0;
		} else {
			if ( isset( $attributes['priority'] ) ) {
				$attributes['priority'] = absint( $attributes['priority'] );

				if ( 0 === $attributes['priority'] ) {
					$attributes['priority'] = 25;
				}
			} else {
				$attributes['priority'] = 25;
			}
		}

		$external_url = ! empty( $attributes['external_url'] ) ? sanitize_text_field( $attributes['external_url'] ) : '';

		if ( ! empty( $external_url ) ) {
			$attributes['hideFromMenu'] = false;
			$attributes['icon'] = 'external-link';
		}

		// Handle for built-in icons.
		if ( false === $attributes['hideFromMenu'] && ! empty( $attributes['icon'] ) ) {
			$icons_registry = Icons_Registry::instance();

			if ( true === $icons_registry->offsetExists( $attributes['icon'] ) ) {

				$attributes['icon'] = new Icon_Control( array(
					'id'   => 'view_icon',
					'atts' => array(
						'stroke' => array(
							'linecap'  => 'round',
							'linejoin' => 'round',
							'width'    => 2
						),
					),
					'args' => array(
						'name' => $attributes['icon'],
					),
				) );
			} else {
				$this->add_error(
					'invalid_view_icon',
					sprintf( 'The \'%1$s\' icon supplied for the \'%2$s\' view is invalid.',
						$attributes['icon'],
						$view_id
					)
				);

			}
		}

		if ( ! isset( $attributes['permission_callback'] ) ) {
			$attributes['permission_callback'] = '__return_true';
		} else {
			if ( ! is_callable( $attributes['permission_callback'] ) ) {
				$this->add_error( 'invalid_permission_callback',
					sprintf( 'The \'%1$s\' permission_callback attribute for the \'%2$s\' view is invalid.',
						$attributes['permission_callback'],
						$view_id
					),
					$attributes
				);
			}
		}

		// Default the menu_label attribute to the value of the label attribute if not set.
		if ( empty( $attributes['menu_label'] ) && ! empty( $attributes['label'] ) ) {
			$attributes['menu_label'] = $attributes['label'];
		}

		if ( empty( $external_url ) ) {

			// Submit button label
			if ( empty( $attributes['submit_label'] ) ) {
				$attributes['submit_label'] = __( 'Save settings', 'affiliatewp-affiliate-portal' );
			}

			if ( empty( $attributes['controls'] ) ) {
				$this->add_error( 'missing_view_controls',
					sprintf( 'No controls were defined for the \'%1$s\' view.', $view_id ),
					$attributes
				);
			} else {
				if ( empty( $external_url ) ) {
					$result = $this->add_controls( $attributes['controls'] );

					if ( is_wp_error( $result ) ) {
						$this->add_error( $result->get_error_code(), $result->get_error_message(), $result->get_error_data() );
					}
				}

				unset( $attributes['controls'] );
			}

			if ( ! empty( $attributes['sections'] ) ) {
				if ( is_array( $attributes['sections'] ) ) {

					$result = $this->add_sections( $view_id, $attributes['sections'] );

					if ( is_wp_error( $result ) ) {
						$this->add_error( $result->get_error_code(), $result->get_error_message(), $result->get_error_data() );
					}
				} else {
					$this->add_error( 'invalid_sections_format',
						sprintf( 'Sections for the \'%s\' view must be defined as an array.', $view_id ),
						$attributes['sections']
					);
				}
			}

			$raw_route_atts = isset( $attributes['route'] ) ? $attributes['route'] : array();

			// Parse the route attributes (if any).
			$route_atts = $this->parse_route_attributes( $view_id, $raw_route_atts );

			// Add the route(s).
			$route_added = $routes_registry->add_route( $route_atts['slug'], $route_atts );

			if ( is_wp_error( $route_added ) ) {
				$this->add_error(
					$route_added->get_error_code(),
					$route_added->get_error_message(),
					$route_added->get_error_data()
				);
			}

			unset( $attributes['route'] );
		}

		// Set default for preload routes
		if ( ! isset( $attributes['preload_routes'] ) ) {
			$attributes['preload_routes'] = false;
		}

		if ( true === $this->has_errors() ) {
			$errors = $this->get_errors( true );

			affiliate_wp()->utils->log( sprintf( 'Affiliate Portal: There was a problem registering the \'%s\' view', $view_id ), $errors );

			return $errors;
		} else {
			return parent::add_item( $view_id, $attributes );
		}
	}

	/**
	 * Registers a component view's sections.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view_id  View ID.
	 * @param array  $sections Sections to register.
	 * @return true|\WP_Error True if the sections were registered, otherwise a \WP_Error if there was a problem.
	 */
	protected function add_sections( $view_id, $sections ) {
		$sections_registry = Sections_Registry::instance();

		foreach ( $sections as $section_id => $section_atts ) {
			if ( empty( $section_atts['view_id'] ) ) {
				$section_atts['view_id'] = $view_id;
			}

			if ( ! isset( $section_atts['wrapper'] ) ) {
				$section_atts['wrapper'] = false;
			}

			$sections_registry->register_section( $section_id, $section_atts );
		}

	}

	/**
	 * Registers a component view's controls.
	 *
	 * @since 1.0.0
	 *
	 * @param Base_Control[] $controls Controls to register.
	 * @return true|\WP_Error True if the controls were registered, otherwise a \WP_Error if there was a problem.
	 */
	protected function add_controls( $controls ) {
		$controls_registry = Controls_Registry::instance();

		foreach ( $controls as $control ) {
			if ( 'wrapper' === $control->get_type() ) {
				$classes = $control->get_attribute( 'class', array() );

				if ( ! empty( $classes ) ) {
					$classes = $classes + array( 'max-w-7xl', 'mx-auto', 'px-4', 'sm:px-6', 'md:px-8' );
				} else {
					$classes = array( 'max-w-7xl', 'mx-auto', 'px-4', 'sm:px-6', 'md:px-8' );
				}

				$control->set_attribute( 'class', $classes );
			}

			$result = $controls_registry->add_control( $control );

			if ( is_wp_error( $result ) ) {
				$this->add_error( $result->get_error_code(), $result->get_error_message(), $result->get_error_data() );
			}
		}

		if ( true === $this->has_errors() ) {
			return $this->get_errors();
		} else {
			return true;
		}
	}

	/**
	 * Parses and prepares the route attributes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view_id    View ID.
	 * @param array  $raw_atts {
	 *     Optional. Raw route-related attributes (if any). Default empty array.
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
	 * @return array Parsed route attributes.
	 */
	private function parse_route_attributes( $view_id, $raw_atts = array() ) {
		// Default the route slug to the view ID.
		$parsed_atts = array(
			'view'      => $view_id,
			'slug'      => 'home' === $view_id ? '' : $view_id,
			'vars'      => array(
				'affwp_portal_view' => $view_id,
			),
			'secondary' => array(),
		);

		// Bail if there's nothing custom to parse.
		if ( empty( $raw_atts ) ) {
			return $parsed_atts;
		}

		// Grab a copy of the default vars before parsing.
		$secondary_vars = $parsed_atts['vars'];

		//
		// Primary rewrite
		//

		if ( ! empty( $raw_atts['slug'] ) ) {
			$parsed_atts['slug'] = sanitize_key( $raw_atts['slug'] );
		}

		if ( ! empty( $raw_atts['vars'] ) && is_array( $raw_atts['vars'] ) ) {
			$parsed_atts['vars'] = array_merge( $parsed_atts['vars'], $raw_atts['vars'] );
		}

		//
		// Secondary rewrite
		//

		if ( ! empty( $raw_atts['secondary'] ) ) {
			$secondary = $raw_atts['secondary'];

			if ( ! empty( $secondary['pattern'] ) ) {
				$parsed_atts['secondary']['pattern'] = $secondary['pattern'];
			}

			if ( ! empty( $secondary['vars'] ) && is_array( $secondary['vars'] ) ) {
				$parsed_atts['secondary']['vars'] = array_merge( $secondary_vars, $secondary['vars'] );
			}
		}

		return $parsed_atts;
	}

	/**
	 * Retrieves a given view from the registry using an optional filter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $view_id View ID.
	 * @return array|\WP_Error (Maybe filtered) view.
	 */
	public function get_view( $view_id ) {
		if ( ! $this->offsetExists( $view_id ) ) {
			$this->add_error( 'invalid_view', sprintf( 'The \'%s\' view does not exist.', $view_id ) );
		} else {
			$view = $this->get( $view_id );
		}

		if ( $this->has_errors() ) {
			return $this->get_errors();
		} else {
			return $view;
		}
	}

}
