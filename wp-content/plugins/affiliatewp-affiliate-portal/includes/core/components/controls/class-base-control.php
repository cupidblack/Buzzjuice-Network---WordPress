<?php
/**
 * Controls: Base Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Traits;
use AffiliateWP_Affiliate_Portal\Utilities\Attributes_Processor;

#[\AllowDynamicProperties]

/**
 * Base control superclass.
 * 
 * @since 1.0.0
 */
abstract class Base_Control {

	use Traits\Error_Handler {
		log_errors as log_errors_base;
	}

	/**
	 * Unique ID for the current control.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $id;

	/**
	 * View ID.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $view_id;

	/**
	 * Section ID.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $section;

	/**
	 * Control priority within its section.
	 *
	 * @since 1.0.0
	 * @var   int
	 */
	private $priority = 25;

	/**
	 * Parent control ID.
	 *
	 * For use by eligible controls only.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	private $parent;

	/**
	 * Callback to determine whether the control has the necessary permission to render.
	 *
	 * @since 1.0.0
	 * @var   callable
	 */
	private $permission_callback = '__return_true';

	/**
	 * Raw, unvalidated metadata (mixed arguments and attributes).
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $raw_metadata = array();

	/**
	 * Validated attributes.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $attributes = array();

	/**
	 * Alpine directives as derived from passed attributes.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $alpine_directives = array();

	/**
	 * Argument key value pairs to make accessible to the render method.
	 *
	 * Sub-class controls should not set this property directly. They should instead rely on passing arguments
	 * alongside attributes during construction of the control.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	private $arguments = array();

	/**
	 * If true, this control's REST data will be preloaded.
	 *
	 * @since 1.0.4
	 *
	 * @var bool True if this should be preloaded. Otherwise false.
	 */
	protected $preload = false;

	/**
	 * Attributes global to all controls.
	 *
	 * @since 1.0.0
	 * @var   string[]
	 */
	private $global_atts = array( 'id', 'class', 'alpine', 'data', );

	/**
	 * Sets up the control.
	 *
	 * @since 1.0.0
	 *
	 * @param array $metadata {
	 *     Metadata for setting up the current control. Arguments are optional unless otherwise stated.
	 *
	 *     @type string   $id                  Required. Globally-unique ID for the current control.
	 *     @type string   $view_id             Required unless `$section` is also omitted. View ID to associate a registered
	 *                                         control with.
	 *     @type string   $section             Required unless `$view_id` is also omitted. Section to associate a registered
	 *                                         control with.
	 *     @type int      $priority            Priority within the section to display the control. Default 25.
	 *     @type string   $parent              Parent control ID for select types of controls. Unused if not set.
	 *     @type array    $alpine              Array of alpine directives to pass to the control.
	 *     @type callable $permission_callback Callback to determine whether the control has permission to render.
	 *                                         Callback must return true or false. Default true.
	 *                                         Signature: ( $control, $affiliate_id ) : bool.
	 *     @type array    $args                {
	 *         Arguments to pass to the control and influence display. Must pass the control-
	 *         specific arguments whitelist during validation. Default empty array.
	 *
	 *         @type array    $wrapper       Attributes and arguments to pass to `div_start()` for the control
	 *                                       wrapper. See `Portal::render_view()` for application.
	 *         @type callable $get_callback  Custom callback for retrieving the control data. Must be defined
	 *                                       if `$save_callback` is also defined and Data_Setter is supported
	 *                                       by the current control. The callback is passed the current affiliate ID.
	 *                                       Default callback is `Base_Control::get_data()`.
	 *                                       Signature: `( $affiliate_id ) : string`.
	 *         @type callable $save_callback Custom callback for saving the control The callback is passed the current
	 *                                       affiliate ID. Default callback is `Base_Control::save_data()`.
	 *                                       Signature: `( $affiliate_id ) : string`.
	 *     }
	 *     @type array    $atts             Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                                      the control-specific attributes whitelist during validation.
	 * }
	 * @param bool  $validate  Optional. Whether to validate the attributes (and split off any arguments).
	 *                         Default true.
	 */
	public function __construct( $metadata, $validate = true ) {
		$this->set_up_errors();

		// Skip some prop requirements if this is a one-off used outside a registered view.
		$one_time_control = empty( $metadata['view_id'] ) && empty( $metadata['section'] );

		if ( isset( $metadata['id'] ) ) {
			$this->id = $metadata['id'];
		} else {
			$this->add_error(
				'missing_control_id',
				'Every control must define a globally-unique \'id\' argument.',
				$metadata
			);

			$this->id = 'invalid';
		}

		if ( false === $one_time_control ) {
			if ( isset( $metadata['view_id'] ) ) {
				$this->view_id = sanitize_key( $metadata['view_id'] );
			} else {
				$this->add_error(
					'missing_view_id',
					sprintf( 'The \'view_id\' argument is missing for the \'%1$s\' ccontrol.', $this->id ),
					$metadata
				);
			}

			if ( isset( $metadata['section'] ) ) {
				$this->section = sanitize_text_field( $metadata['section'] );
			} else {
				$this->add_error(
					'missing_section',
					sprintf( 'The \'section\' argument is missing for the \'%1$s\' control.', $this->id ),
					$metadata
				);
			}
		}

		if ( isset( $metadata['priority'] ) ) {
			$this->priority = absint( $metadata['priority'] );
		}

		if ( isset( $metadata['parent'] ) ) {
			$this->parent = sanitize_text_field( $metadata['parent'] );
		}

		if ( isset( $metadata['permission_callback'] ) ) {
			if ( ! is_callable( $metadata['permission_callback'] ) ) {
				$this->add_error(
					'invalid_permission_callback',
					sprintf( 'The \'%1$s\' \'permission_callback\' argument for the \'%2$s\' control must be a valid callback.',
						$metadata['permission_callback'],
						$this->id
					),
					$metadata
				);
			} else {
				$this->permission_callback = $metadata['permission_callback'];
			}
		}

		$this->raw_metadata = $metadata;

		if ( true === $validate ) {
			$this->validate_metadata( $this->raw_metadata );
		}

		// Default the control id attribute to the control ID if not set.
		if ( ! $this->get_attribute( 'id', false ) && 'invalid' !== $this->id ) {
			$this->set_attribute( 'id', $this->id );
		}
	}

	/**
	 * Retrieves the value of a property.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property Property name.
	 * @return mixed Property value.
	 */
	public function get_prop( $property ) {
		$whitelist = array( 'section', 'priority', 'parent' );

		if ( isset( $this->$property ) && in_array( $property, $whitelist ) ) {
			return $this->$property;
		} else {
			return null;
		}
	}

	/**
	 * Sets a property of a given name (if allowed).
	 *
	 * @since 1.0.0
	 *
	 * @param string $name  Property name.
	 * @param mixed  $value Property value.
	 */
	public function set_prop( $name, $value ) {
		$whitelist = array( 'priority', 'parent' );
		$validation_whitelist = array( 'view_id', 'section' );

		if ( in_array( $name, $whitelist ) ||
			( 'validation' === $this->get_type() && in_array( $name, $validation_whitelist ) ) ) {
			$this->$name = $value;
		}
	}

	/**
	 * Retrieves the whitelist of attributes for the current control.
	 *
	 * @since 1.0.0
	 *
	 * @return array Attributes whitelist.
	 */
	public function get_atts_whitelist() {
		return $this->global_atts;
	}

	/**
	 * Retrieves the whitelist of arguments for the current control.
	 */
	public function get_args_whitelist() {
		return array( 'wrapper', 'get_callback', 'save_callback' );
	}

	/**
	 * Renders the markup for the current control.
	 *
	 * @since 1.0.0
	 *
	 * @see Base_Control::get_attributes()
	 * @see Base_Control::get_arguments()
	 *
	 * @param bool $echo Optional. Whether to echo the rendered control markup. Default true;
	 * @return string|void The rendered control markup if `$echo` is true, otherwise the markup will be output.
	 */
	abstract public function render( $echo = true );

	/**
	 * Retrieves the control type.
	 *
	 * @since 1.0.0
	 *
	 * @return string Control type.
	 */
	abstract public function get_type();

	/**
	 * Retrieves the ID of the current control.
	 *
	 * @since 1.0.0
	 *
	 * @return string Control ID.
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Helper function to retrieve a control ID base.
	 *
	 * @since 1.0.0
	 *
	 * @return string ID attribute if set, otherwise the control ID.
	 */
	public function get_id_base() {
		return $this->get_attribute( 'id', $this->get_id() );
	}

	/**
	 * Retrieves the view ID set for the control.
	 *
	 * @since 1.0.0
	 *
	 * @return string View ID passed during construction.
	 */
	public function get_view_id() {
		return $this->view_id;
	}

	/**
	 * Retrieves the current affiliate ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int Affiliate ID if set, otherwise 0.
	 */
	public function get_affiliate_id() {
		$affiliate_id = affwp_get_affiliate_id();

		return intval( $affiliate_id );
	}

	/**
	 * Retrieves the saved data for the current control.
	 *
	 * @since 1.0.0
	 *
	 * @see Traits\Control_Bootstrap::get_control()
	 *
	 * @param int $affiliate_id Current affiliate ID.
	 * @return mixed Data for the current control.
	 */
	public function get_data( $affiliate_id ) {
		return '';
	}

	/**
	 * Sets an attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param string $attribute Attribute key.
	 * @param mixed  $value     Attribute value.
	 */
	public function set_attribute( $attribute, $value ) {
		$this->attributes[ $attribute ] = $value;
	}

	/**
	 * Retrieves a given attribute if set, otherwise a default value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $attribute Attribute key.
	 * @param mixed  $default   Default attribute value.
	 * @return mixed Attribute value.
	 */
	public function get_attribute( $attribute, $default = '' ) {
		$attributes = $this->get_attributes();

		if ( isset( $attributes[ $attribute ] ) ) {
			return $attributes[ $attribute ];
		} else {
			return $default;
		}
	}

	/**
	 * Retrieves the validated attributes.
	 *
	 * @since 1.0.0
	 *
	 * @return array Validated attributes.
	 */
	public function get_attributes() {
		return $this->attributes;
	}

	/**
	 * Retrieves top-level Alpine directives.
	 *
	 * @since 1.0.0
	 *
	 * @return array Alpine directives.
	 */
	public function get_alpine_directives() {
		return $this->alpine_directives;
	}

	/**
	 * Sets Alpine directives.
	 *
	 * @since 1.0.0
	 *
	 * @param string $directives Alpine directives.
	 */
	public function set_alpine_directives( $directives ) {
		$this->alpine_directives = $directives;
	}

	/**
	 * Sets a non-attribute argument to pass to the control's render method for display.
	 *
	 * @since 1.0.0
	 *
	 * @param string $argument Argument name.
	 * @param mixed  $value    Argument value.
	 */
	public function set_argument( $argument, $value ) {
		$this->arguments[ $argument ] = $value;
	}

	/**
	 * Retrieves a given argument if set, otherwise a default value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $argument Argument key.
	 * @param mixed  $default  Default argument value.
	 * @return mixed Argument value.
	 */
	public function get_argument( $argument, $default = '' ) {
		$arguments = $this->get_arguments();

		if ( isset( $arguments[ $argument ] ) ) {
			return $arguments[ $argument ];
		} else {
			return $default;
		}
	}

	/**
	 * Retrieves arguments
	 * @return array
	 */
	public function get_arguments() {
		return $this->arguments;
	}

	/**
	 * Determines if this is a form control.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether this is a form control.
	 */
	public function form_control() {
		return false;
	}

	/**
	 * Determines if the control posts any data (via form submission).
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the control posts any data.
	 */
	public function posts_data() {
		return false;
	}

	/**
	 * Determines whether the control has the necessary permission to render.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether the control is permitted to render.
	 */
	public function can_render() {
		$permitted = false;

		if ( is_callable( $this->permission_callback ) ) {
			$permitted = call_user_func( $this->permission_callback, $this, $this->get_affiliate_id() );
		}

		return (bool) $permitted;
	}

	/**
	 * Validates metadata passed to the control.
	 *
	 * @since 1.0.0
	 *
	 * @param array $metadata {
	 *     Control metadata to validate.
	 *
	 *     @type array $alpine Array of alpine directives to pass to the control.
	 *     @type array $args   Arguments to pass to the control and influence display. Must pass the control-
	 *                         specific arguments whitelist during validation. Default empty array.
	 *     @type array $atts   Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                         the control-specific attributes whitelist during validation.
	 * }
	 * @return void
	 */
	public function validate_metadata( $metadata ) {
		if ( ! empty( $metadata['args'] ) && is_array( $metadata['args'] ) ) {
			$args_whitelist = $this->get_args_whitelist();

			foreach ( $metadata['args'] as $key => $value ) {
				if ( in_array( $key, $args_whitelist, true ) ) {
					$this->set_argument( $key, $value );
				}
			}
		}

		if ( ! empty( $metadata['atts'] ) && is_array( $metadata['atts'] ) ) {
			$processor = new Attributes_Processor();

			foreach ( $metadata['atts'] as $key => $value ) {
				$value = $processor::process_single( $key, $value, $this->get_type() );

				$this->set_attribute( $key, $value );
			}
		}

		if ( ! empty( $metadata['alpine'] ) ) {
			$this->alpine_directives = $metadata['alpine'];
		}
	}

	/**
	 * Helper method to process and sanitize the current control's attributes.
	 *
	 * Attributes are processed during control instantiation, but this is typically called
	 * a second time within the render() method if changes have been made to attributes
	 * since instantiation.
	 *
	 * @since 1.0.0
	 *
	 * @param bool $prepare Optional. Whether to additionally prepare the attributes. Default false.
	 * @return array Processed attributes.
	 */
	public function process_atts( $prepare = false ) {
		$atts = Attributes_Processor::process( $this->get_attributes(), $this->get_type() );

		if ( true === $prepare ) {
			$atts = $this->prepare_atts();
		}

		return $atts;
	}

	/**
	 * Helper method to prepare attributes and alpine directives for display.
	 *
	 * @since 1.0.0
	 *
	 * @return string Attributes ready for display in HTML tags.
	 */
	public function prepare_atts() {
		return Attributes_Processor::prepare( $this->get_attributes(), $this->get_alpine_directives() );
	}

	/**
	 * Logs errors to the AffiliateWP debug log.
	 *
	 * @since 1.0.0
	 *
	 * @see Error_Handler::log_errors()
	 *
	 * @param string $context Optional. Contextual data to include in the log message. Default null (unused).
	 */
	public function log_errors( $context = null ) {
		if ( null === $context ) {
			$context = $this->get_view_id();
		}

		$this->log_errors_base( $context );
	}

	/**
	 * Retrieves an array version of the object.
	 *
	 * @since 1.0.0
	 *
	 * @param array $extra Optional. Extra props to include in the output. Default empty array.
	 * @return array Array version of the control object.
	 */
	public function to_array( $extra = array() ) {
		$props = array(
			'id'       => $this->get_id(),
			'view_id'  => $this->get_view_id(),
			'type'     => $this->get_type(),
			'section'  => $this->get_prop( 'section' ),
			'priority' => $this->get_prop( 'priority' ),
			'parent'   => $this->get_prop( 'parent' ),
		);

		return array_merge( $props, $extra, get_object_vars( $this ) );
	}

	/**
	 * Retrieves REST endpoint preload data for this control.
	 *
	 * @since 1.0.4
	 *
	 * @return array|string List of prefetched data keyed by the preloaded REST endpoint, or a single endpoint.
	 */
	public function get_preload_routes() {
		return '/affwp/v2/portal/controls/' . $this->get_id();
	}

}
