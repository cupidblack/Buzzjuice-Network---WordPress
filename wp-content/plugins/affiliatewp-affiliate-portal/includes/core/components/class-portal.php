<?php
/**
 * Components: Portal Bootstrap
 *
 * @package   Core/Components
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components;

use AffiliateWP_Affiliate_Portal\Core;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Utilities;
use function AffiliateWP_Affiliate_Portal\html;
use AffiliateWP_Affiliate_Portal\Core\Menu_Links;

#[\AllowDynamicProperties]

/**
 * Class used for loading views within the affiliate portal.
 *
 * @since 1.0.0
 */
class Portal {

	use Core\Traits\Error_Handler;

	/**
	 * Sets up the API.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->set_up_errors();
		add_action( 'template_redirect', array( $this, 'template_include_override' ) );
		add_filter( 'query_vars', array( $this, 'query_vars' ) );
		add_filter( 'template_redirect', array( $this, 'maybe_soft_flush_rewrites' ) );
	}

	/**
	 * Overrides all other template_include actions.
	 *
	 * @since 1.0.0
	 */
	public function template_include_override() {

		if ( ! affwp_is_affiliate_portal() || ! affwp_is_affiliate() ) {
			return;
		}

		// Remove all template_include actions.
		remove_all_actions( 'template_include' );

		// Then add our template_include filter.
		add_filter( 'template_include', array( $this, 'load' ) );
	}

	/**
	 * Loads the main portal template.
	 *
	 * @since 1.0.0
	 *
	 * @param string $template Template include path.
	 * @return string (Maybe modified) template include path.
	 */
	public function load( $template ) {

		if ( ! ( affwp_is_affiliate() && affwp_is_active_affiliate() ) ) {
			return $template;
		}

		if ( affwp_is_affiliate_portal() ) {
			$template = AFFWP_PORTAL_PLUGIN_DIR . 'includes/template.php';
		}

		return $template;
	}

	/**
	 * Sets up query vars used by the affiliate portal.
	 *
	 * @since 1.0.0
	 *
	 * @param array $vars Query vars.
	 * @return array Modified query vars.
	 */
	public function query_vars( $vars ) {
		$vars[] = 'affwp_portal_view';
		$vars[] = 'affwp_portal_action';

		return $vars;
	}

	/**
	 * (Maybe) performs a soft rewrites flush if the given criteria are met.
	 *
	 * @since 1.0.0
	 * @since 1.3.6 Fixed redirect so it doesn't always redirect to the Dashboard page, instead it redirects
	 *               to whatever page you were on see https://github.com/awesomemotive/affiliate-wp/issues/5335.
	 */
	public function maybe_soft_flush_rewrites() {
		global $wp;

		$is_registration = isset( $_REQUEST['affwp_action'] ) && 'affiliate_register' === $_REQUEST['affwp_action'];
		if ( ( ! is_404() && ! $is_registration ) ) {
			return;
		}

		$affiliate_area_page = get_post( affwp_get_affiliate_area_page_id() );

		if ( isset( $affiliate_area_page->post_name ) ) {
			$post_name = $affiliate_area_page->post_name;
		} else {
			return;
		}

		$request_parts = explode( '/', $wp->request );

		if ( ! empty( $post_name ) && isset( $request_parts[0] ) && $post_name === $request_parts[0] ) {
			flush_rewrite_rules( false );

			$dashboard_url = home_url( $_SERVER['REQUEST_URI'] ?? get_permalink( $affiliate_area_page ) );

			if ( wp_redirect( $dashboard_url ) ) {
				exit;
			}
		}

	}

	/**
	 * Retrieves the display markup for a given view and component..
	 *
	 * @since 1.0.0
	 *
	 * @param string $view View ID combined with component ID via the query variable.
	 * @return string View markup (if any).
	 */
	public static function get_view( $view ) {
		$views_registry = Core\Views_Registry::instance();

		$view_atts = $views_registry->get( $view );

		$output = '';

		if ( false !== $view_atts ) {
			$view_atts['view_id'] = $view;

			ob_start();

			// TODO remove this stuff once Field API is fully implemented.
			if ( isset( $view_atts['template'] ) ) {
				$template_path = AFFWP_PORTAL_PLUGIN_DIR . 'templates/' . $view_atts['template'];

				if ( file_exists( $template_path ) ) {
					include $template_path;
				}
			} else {
				self::render_view( $view_atts );
			}

			$output = ob_get_clean();
		}

		return $output;
	}

	/**
	 * Renders a view and its corresponding fields.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @param array $view_atts View attributes.
	 */
	public static function render_view( $view_atts ) {
		$sections_registry = Core\Sections_Registry::instance();
		$controls_registry = Core\Controls_Registry::instance();

		$affiliate_id = affwp_get_affiliate_id();
		$view_id      = $view_atts['view_id'];

		$sections = $sections_registry->query( array(
			'view_id' => $view_id,
		) );

		$controls = $controls_registry->query( array(
			'view_id' => $view_id,
		) );

		$output = '';

		if ( empty( $controls ) ) {
			echo $output;
		}

		ob_start();

		$wrapper_field_id = "{$view_id}:wrapper";
		$wrapper_control  = $controls_registry->get( $wrapper_field_id );

		$has_wrapper  = false !== $wrapper_control;
		$has_loader   = isset( $view_atts['loader'] );
		$has_sections = ! empty( $sections );

		if ( true === $has_wrapper ) {
			$wrapper_control->set_attribute( 'id', 'affiliate-portal-content' );
			$wrapper_control->set_attribute( 'class', array( 'max-w-7xl', 'mx-auto', 'px-4', 'pb-8', 'sm:px-6', 'md:px-8' ) );

			$wrapper_control->render();
		}

		if ( ! empty( $view_atts['label'] ) ) {
			$header = new Controls\Heading_Control( array(
				'id'   => "{$view_id}-head",
				'args' => array(
					'text'  => $view_atts['label'],
					'level' => 1,
				),
			) );

			if ( ! $header->has_errors() ) {
				$header->render();
			} else {
				$header->log_errors( 'portal' );
			}
		}

		if ( true === $has_loader ) {
			$loader_directives = array( 'x-show' => '!isLoading' );
		} else {
			$loader_directives = array();
		}

		$loader_classes = array( 'mt-10', 'sm:mt-0' );

		$atts = Utilities\Attributes_Processor::prepare( array( 'class' => $loader_classes ), $loader_directives );

		printf( '<div%s>', $atts );

		// Include Payouts Service template on the settings view.
		// TODO convert to use registered controls.
		if ( 'settings' === $view_id ) {
			$template_path = AFFWP_PORTAL_PLUGIN_DIR . 'templates/payouts-service.php';

			if ( file_exists( $template_path ) ) {
				include $template_path;
			}
		}

		if ( true === $has_sections && is_array( $sections ) ) {

			uasort( $sections, function( $a, $b ) {
				if ( $a['priority'] == $b['priority'] ) {
					return 0;
				} elseif ( $a['priority'] < $b['priority'] ) {
					return -1;
				} else {
					return 1;
				}
			} );

			$count = 0;

			$total_sections = count( $sections );

			if ( isset( $sections['wrapper'] ) ) {
				unset( $sections['wrapper'] );
			}

			foreach ( $sections as $section_id => $section_atts ) {
				$section_can_render = call_user_func( $section_atts['permission_callback'], $section_id, $affiliate_id );

				/** @var Controls\Base_Control[] $section_controls */
				$section_controls = wp_list_filter( $controls, array( 'section' => $section_id ) );

				if ( false === $section_can_render || empty( $section_controls ) ) {
					$total_sections--;

					continue;
				}

				$not_last_section = $count > 0 && $count !== $total_sections;
				if ( $not_last_section ) {
					html()->divider();
				}

				// Check if section has form controls.
				$form_controls = wp_list_filter( $section_controls, array( 'formControl' => 1 ) );

				/**
				 * Filter whether or not to treat this page like a full form.
				 *
				 * @since 1.2.2
				 *
				 * @param $has_form_controls If the page has form controls we asume it's a form,
				 *                           and later will add a submit button for the form.
				 *                           Use: `apply_filters( "affwp_portal_has_form_controls_{$view_id}_{$section_id}", '__return_false' );`
				 *                           to disable treating the page like a form.
				 */
				$has_form_controls = apply_filters( "affwp_portal_has_form_controls_{$view_id}_{$section_id}", ! empty( $form_controls ) );

				// check if section already has x-data directive.
				$form_alpine = array();
				if ( ! empty( $section_atts['form_alpine'] ) ) {
					$form_alpine = $section_atts['form_alpine'];
				}
				$has_form_alpine = ! empty( $form_alpine );

				// Section Form wrapper.
				if ( true === $has_form_controls ) {
					$form_alpine = wp_parse_args( $form_alpine, array(
						'x-data'   => "AFFWP.portal.form.default( '{$section_id}' )",
						'x-init'   => 'init',
						'x-spread' => 'setupForm()',
					) );

					html()->form_start( array(
						'id'         => "{$view_id}-{$section_id}-form",
						'method'     => 'post',
						'directives' => $form_alpine,
					) );
				}

				// Content Wrapper Start.
				html()->div_start( array(
					'class' => array( 'md:grid', 'md:grid-cols-3', 'md:gap-6' ),
				) );

				html()->control_section_header( $section_id, $section_atts );
				html()->control_section_start( $section_atts );

				// Order fields by priority, ascending for each section.
				uasort( $section_controls, function( $a, $b ) {
					if ( $a->get_prop( 'priority' ) == $b->get_prop( 'priority' ) ) {
						return 0;
					} elseif ( $a->get_prop( 'priority' ) < $b->get_prop( 'priority' ) ) {
						return -1;
					} else {
						return 1;
					}
				} );

				$total_controls = count( $section_controls );
				$controls_count = 0;

				foreach ( $section_controls as $control ) {
					$control_type = $control->get_type();

					if ( 'card' === $control_type && $control->get_prop( 'parent' ) ) {
						continue;
					}

					$control_type_class = $control_type . '-control';

					/**
					 * Filter the classes for the control section.
					 *
					 * @since 1.2.2
					 *
					 * @param $control_classes Classes.
					 * @param $control         The Control.
					 */
					$control_classes = apply_filters( "affwp_portal_{$control_type}_section_classes", array( 'setting', $control_type_class ), $control );

					if ( $total_controls > 1 && 1 !== ++$controls_count &&
						! $control instanceof Controls\Hidden_Control
						&& ! $control instanceof Controls\Validation_Control ) {
						$control_classes[] = 'mt-5';
					}

					$args         = array( 'class' => $control_classes );
					$wrapper_args = $control->get_argument( 'wrapper' );

					if ( $wrapper_args ) {
						$args = array_merge( $args, $wrapper_args );
					}

					self::maybe_render_control( $control, $args, $view_id );
				}

				if ( true === $has_form_controls ) {
					// Section inner.
					html()->div_end();

					// Section footer.
					html()->div_start( array(
						'id'    => "section-footer-{$view_id}-{$section_id}",
						'class' => array( 'px-4', 'py-3', 'bg-gray-50', 'sm:px-6' ),
					) );

					// View ID.
					$view_id_control = new Controls\Hidden_Control( array(
						'id'     => 'view_id',
						'atts'   => array(
							'value' => $view_id,
							'name'  => 'view_id',
						),
						'alpine' => array(
							'x-spread'      => '',
							':class'        => '{}',
							':aria-invalid' => '',
						),
					) );

					echo $view_id_control->has_errors() ? '' : $view_id_control->render( false );

					// Section ID.
					$section_id_control = new Controls\Hidden_Control( array(
						'id'     => 'section_id',
						'atts'   => array(
							'value' => $section_id,
							'name'  => 'section_id',
						),
						'alpine' => array(
							'x-spread'      => '',
							':class'        => '{}',
							':aria-invalid' => '',
						),
					) );

					echo $section_id_control->has_errors() ? '' : $section_id_control->render( false );

					// Nonce.
					$nonce_control = new Controls\Hidden_Control( array(
						'id'     => "{$section_id}-nonce",
						'atts'   => array(
							'name'  => "{$section_id}-save-nonce",
							'value' => wp_create_nonce( "{$section_id}-save-nonce" ),
						),
						'alpine' => array(
							'x-spread'      => '',
							':class'        => '{}',
							':aria-invalid' => '',
						),
					) );

					// Only render nonce if field does not use REST.
					if ( true === $has_form_alpine ) {
						echo $nonce_control->has_errors() ? '' : $nonce_control->render( false );
					}

					// Affiliate ID
					$affiliate_id_control = new Controls\Hidden_Control( array(
						'id'     => 'affiliate_id',
						'atts'   => array(
							'value' => $affiliate_id,
							'name'  => 'affiliate_id',
						),
						'alpine' => array(
							'x-spread'      => '',
							':class'        => '{}',
							':aria-invalid' => '',
						),
					) );

					echo $affiliate_id_control->has_errors() ? '' : $affiliate_id_control->render( false );

					// Alpine directives for submit button.
					$submit_alpine = array(
						'x-spread' => 'setupSubmit()',
					);

					if ( ! empty( $section_atts['submit_alpine'] ) ) {
						$submit_alpine = array_merge( $submit_alpine, $section_atts['submit_alpine'] );
					}

					$save_button = new Controls\Submit_Button_Control( array(
						'id'     => "save-{$view_id}-{$section_id}-settings",
						'alpine' => $submit_alpine,
						'atts'   => array(
							'value' => $section_atts['submit_label'],
						),
					) );

					echo $save_button->has_errors() ? '' : $save_button->render( false );

					$icon_alpine = array(
						'x-show' => 'isLoading || isValidating || isSubmitting',
					);

					if ( ! empty( $section_atts['icon_alpine'] ) ) {
						$icon_alpine = array_merge( $icon_alpine, $section_atts['icon_alpine'] );
					}

					$loading_icon = new Controls\Icon_Control( array(
						'id'     => "save-{$section_id}-loading",
						'alpine' => $icon_alpine,
						'args'   => array(
							'name' => 'refresh',
						),
						'atts'   => array(
							'class' => array( 'animate-loading', 'text-gray-500', 'ml-1', 'inline' ),
						),
					) );

					echo $loading_icon->has_errors() ? '' : $loading_icon->render( false );

					$saved_icon = new Controls\Icon_Control( array(
						'id'     => "save-{$section_id}-saved",
						'alpine' => array(
							'x-show' => 'false !== showingSuccessMessage'
						),
						'args'   => array(
							'name' => 'check',
						),
						'atts'   => array(
							'class' => array( 'ml-1', 'text-green-400', 'inline' ),
						),
					) );

					echo $saved_icon->has_errors() ? '' : $saved_icon->render( false );

					$saved_icon = new Controls\Text_Control( array(
						'id'     => "save-{$section_id}-saved-message",
						'alpine' => array(
							'x-show' => 'true === showingSuccessMessage'
						),
						'args'   => array(
							'text' => __( 'Saved', 'affiliatewp-affiliate-portal' ),
						),
						'atts'   => array(
							'class' => array( 'ml-1', 'text-green-400', 'inline' ),
						),
					) );

					echo $saved_icon->has_errors() ? '' : $saved_icon->render( false );

					// Section Footer.
					html()->div_end();

					html()->div_end();

					// Content Wrapper End.
					html()->div_end();

					html()->form_end();
				} else {
					html()->control_section_end();

					// Content Wrapper End.
					html()->div_end();
				}

				$count++;
			}
		}

		// Content Wrapper End.
		html()->div_end();

		// (Maybe) Loader End.
		html()->div_end();

		if ( true === $has_wrapper ) {
			// Wrapper End.
			echo '</div>';
		}

		echo ob_get_clean();
	}

	/**
	 * Retrieves the markup for the current view, as set by query arguments.
	 *
	 * @since 1.0.0
	 *
	 * @return string Markup for the current view, if any.
	 */
	public static function get_current_view() {
		return self::get_view( self::get_current_view_slug() );
	}

	/**
	 * Retrieves the slug of the current view.
	 *
	 * @since 1.0.0
	 *
	 * @return string Current view slug if set, otherwise an empty string.
	 */
	public static function get_current_view_slug() {
		if ( true === affwp_is_affiliate_area() ) {
			$default = 'home';
		} else {
			$default = '';
		}

		return get_query_var( 'affwp_portal_view', $default );
	}

	/**
	 * Retrieves the portal URL for a given view.
	 *
	 * @since 1.0.0
	 * @static
	 *
	 * @param string $view View slug.
	 * @return string URL for the given view (if valid) otherwise the home URL.
	 */
	public static function get_page_url( $view ) {
		$url_base        = affwp_get_affiliate_area_page_url();
		$view_registry   = Core\Views_Registry::instance();
		$routes_registry = Core\Routes_Registry::instance();

		if ( ! $view_registry->get( $view ) ) {
			return $url_base;
		}

		$routes = $routes_registry->get_rest_items( 'route' );

		if ( is_wp_error( $routes ) ) {
			return $url_base;
		}

		$routes = $routes->get_data();

		$route_index = array_search( $view, array_column( $routes, 'view' ) );

		if ( false !== $route_index ) {
			$route = $routes[ $route_index ];
		} else {
			$route = array( 'slug' => '' );
		}

		$page_url = trailingslashit( $url_base ) . $route['slug'];

		// force trailing slash at the end.
		return trailingslashit( $page_url );
	}

	/**
	 * Determines whether the current view has permission to render.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the current view has permission to render, otherwise false.
	 */
	public static function view_can_render() {
		$views_registry = Core\Views_Registry::instance();
		$current_view   = self::get_current_view_slug();
		$affiliate_id   = affwp_get_affiliate_id();

		$view = $views_registry->get( $current_view );

		$view_can_render = false;

		if ( isset( $view['permission_callback'] ) ) {
			$view_can_render = call_user_func( $view['permission_callback'], $current_view, $affiliate_id );
		}

		return $view_can_render;
	}

	/**
	 * (Maybe) renders a control based on its permission callback.
	 *
	 * @since 1.0.0
	 *
	 * @param Controls\Base_Control $control       $control object.
	 * @param array                 $wrapper_args  Arguments to pass to the wrapper.
	 * @param string                $error_context View ID or other context to pass when logging errors (if any).
	 */
	public static function maybe_render_control( $control, $wrapper_args, $error_context = '' ) {
		if ( true !== $control->can_render() ) {
			return;
		} elseif ( true === $control->has_errors() ) {
			$control->log_errors( $error_context );
		} else {
			// Control wrapper start.
			html()->div_start( $wrapper_args );

			$control->render();

			// Control wrapper end.
			html()->div_end();
		}
	}

	/**
	 * Retrieves the main portal navigation markup.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $mobile Whether to retrieve the mobile-ready version of the nav. Default false.
	 * @return string Markup for the navigation.
	 */
	public static function get_navigation( $mobile = false ) {

		$bg_class       = 'bg-gray-900';
		$focus_bg_class = 'focus:bg-gray-700';
		$hover_bg_class = 'hover:bg-gray-700';
		$text_class     = 'text-gray-300';

		$view_registry   = Core\Views_Registry::instance();
		$routes_registry = Core\Routes_Registry::instance();

		$affiliate_id = affwp_get_affiliate_id();
		$current_view = self::get_current_view_slug();
		$views        = $view_registry->get_rest_items( 'view' );
		$routes       = $routes_registry->get_rest_items( 'route' );

		if ( is_wp_error( $views ) || is_wp_error( $routes ) ) {
			return;
		} else {
			$views  = $views->get_data();
			$routes = $routes->get_data();
		}

		if ( ! empty( $views ) ) {

			uasort( $views, function( $a, $b ) {
				if ( $a['priority'] == $b['priority'] ) {
					return 0;
				} elseif ( $a['priority'] < $b['priority'] ) {
					return -1;
				} else {
					return 1;
				}
			} );

			$base_classes = array(
				'mt-1',
				'group',
				'px-2',
				'py-2',
				'font-medium',
				'rounded-md',
				'focus:outline-none',
				'transition',
				'ease-in-out',
				'duration-150',
				$focus_bg_class,
			);

			if ( true === $mobile ) {
				$base_classes[] = 'text-base';
				$base_classes[] = 'leading-6';
			} else {
				$base_classes[] = 'text-sm';
				$base_classes[] = 'leading-5';
			}

			$icon_classes = array(
				'mr-3',
				'h-6',
				'w-6',
				'pt-0.5',
				'transition',
				'ease-in-out',
				'duration-150',
				'text-gray-300',
				'group-focus:text-gray-300',
			);

			foreach ( $views as $view ) :
				$view_can_render = call_user_func( $view['permission_callback'], $view['viewId'], $affiliate_id );

				if ( true === $view['hideFromMenu'] || ! $view_can_render ) {
					continue;
				}

				$classes = $base_classes;
				if ( $view['viewId'] === $current_view ) {
					$classes[] = 'text-white';
					$classes[] = $bg_class;
				} else {
					$classes[] = 'hover:text-white';
					$classes[] = 'focus:text-white';
					$classes[] = $text_class;
					$classes[] = $hover_bg_class;
				}

				if ( isset( $view['external_url'] ) ) {
					$view_url = $view['external_url'];
				} else {
					$view_url = self::get_page_url( $view['viewId'] );
				}

				if ( isset( $view['icon'] ) && ( $view['icon'] instanceof Controls\Icon_Control ) ) {
					$_classes = $view['icon']->get_attribute( 'class', array() );

					$icon_classes = array_merge( $_classes, $icon_classes );

					$view['icon']->set_attribute( 'class', $icon_classes );
				} else {
					$view['icon'] = false;
				}

				$atts = array(
					'class' => $classes,
					'href'  => $view_url,
				);

				if ( isset( $view['external_url'] ) ) {
					$atts['target'] = '_blank';
				}

				$menu_item = new Controls\Link_Control( array(
					'id' => "{$view['viewId']}_nav_item",
					'atts' => $atts,
					'args' => array(
						'label' => $view['menu_label'],
						'icon'  => false !== $view['icon'] ? $view['icon'] : '',
					),
				) );

				if ( ! $menu_item->has_errors() ) {
					$menu_item->render();
				} else {
					$menu_item->log_errors( 'navigation' );
				}
				?>
			<?php endforeach; ?>

			<?php
			// Render custom menu links.
			$menu_links = affiliatewp_affiliate_portal()->menu_links->get_menu_links();

			if ( count( $menu_links ) > 0 ) : ?>

				<div class="flex items-center py-2">
					<hr class="w-full border-gray-700" />
				</div>

				<?php
				$classes   = $base_classes;
				$classes[] = 'hover:text-white';
				$classes[] = 'focus:text-white';
				$classes[] = $text_class;
				$classes[] = $hover_bg_class;

				foreach ( $menu_links as $menu_link_index => $menu_link ) {
					$url  = get_permalink( $menu_link['id'] );
					$atts = array(
						'class'  => $classes,
						'href'   => $url,
						'target' => '_blank',
					);

					$link_icon = new Controls\Icon_Control( array(
						'id'   => "{$menu_link_index}_menu_link_icon",
						'atts' => array(
							'class' => $icon_classes,
						),
						'args' => array(
							'name' => 'external-link',
						),
					) );

					// Set icon classes outside the constructor.
					// TODO fix bug where decimals in classes get stripped when defined via the constructor.
					$link_icon->set_attribute( 'class', $icon_classes );

					$menu_item = new Controls\Link_Control( array(
						'id'   => "{$menu_link_index}_menu_link_nav_item",
						'atts' => $atts,
						'args' => array(
							'label' => $menu_link['label'],
							'icon'  => $link_icon,
						),
					) );

					if ( ! $menu_item->has_errors() ) {
						$menu_item->render();
					} else {
						$menu_item->log_errors( 'navigation' );
					}
				}

			endif;
		}

	}
}
