<?php
/**
 * Controls: Input Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Utilities\Attributes_Processor;
use function AffiliateWP_Affiliate_Portal\html;

/**
 * Implements a generic input form control.
 *
 * @since 1.0.0
 *
 * @see Form_Control
 */
abstract class Input_Control extends Form_Control {

	/**
	 * Sets up the control.
	 *
	 * @param array $metadata  {
	 *     Metadata for setting up the current control. Arguments are optional unless otherwise stated.
	 *
	 *     @type string $id       Required. Globally-unique ID for the current control.
	 *     @type string $view_id  Required unless `$section` is also omitted. View ID to associate a registered
	 *                            control with.
	 *     @type string $section  Required unless `$view_id` is also omitted. Section to associate a registered
	 *                            control with.
	 *     @type int    $priority Priority within the section to display the control. Default 25.
	 *     @type array  $alpine   Array of alpine directives to pass to the control.
	 *     @type array  $args     {
	 *         Arguments to pass to the control and influence display. Must pass the control-
	 *         specific arguments whitelist during validation. Default empty array.
	 *
	 *         @type string       $label        Label for the text input. Default empty.
	 *         @type array        $label_href_class Label for the text input. Default empty.
	 *         @type string|array $desc         {
	 *             Description for the text input or an array containing directives and text key value pairs.
	 *             Default empty string.
	 *
	 *             @type string $text       Text to display inside the description element.
	 *             @type array  $directives Directives to pass along to the description element.
	 *             @type string $position   Position of the description. Accepts 'before' or 'after'. Default 'after'.
	 *         }
	 *         @type bool|array   $error        {
	 *             Whether this is an error. Accepts true, false, or an array of directives to pass to the opening div.
	 *             Default false.
	 *
	 *             @type array $directives Directives to pass to the error container.
	 *         }
	 *     }
	 *     @type array  $atts     {
	 *         Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *         the control-specific attributes whitelist during validation.
	 *
	 *         @type string $type         Input type. If not specified, will be set from the input sub-class.
	 *         @type string $placeholder  Placeholder attribute to use for the text input.
	 *         @type string $autocomplete Whether the input should allow autocomplete ('on') or not ('off').
	 *     }
	 * }
	 * @param bool  $validate  Optional. Whether to validate the attributes (and split off any arguments).
	 *                         Default true.
	 */
	public function __construct( $metadata, $validate = true ) {

		if ( ! isset( $metadata['atts']['type'] ) ) {
			$metadata['atts']['type'] = $this->get_input_type();
		}

		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'input';
	}

	/**
	 * Retrieves the input type.
	 *
	 * Sub-class controls set this to determine the input _type_ as the control type is always 'input'.
	 *
	 * @since 1.0.0
	 *
	 * @return string Input type.
	 */
	abstract public function get_input_type();

	/**
	 * Retrieves the attributes whitelist.
	 *
	 * @since 1.0.0
	 *
	 * @return array Attributes whitelist.
	 */
	public function get_atts_whitelist() {
		$whitelist = array( 'placeholder', 'autocomplete', 'type' );

		return array_merge( parent::get_atts_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'desc', 'error', 'label_href', 'label_href_class' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base = $this->get_id_base();

		$error         = $this->get_argument( 'error', array() );
		$description   = $this->get_argument( 'desc', array() );
		$label         = $this->get_argument( 'label' );
		$label_class   = $this->get_argument( 'label_class', array() );
		$validations   = $this->get_argument( 'validations', array() );
		$classes       = $this->get_attribute( 'class', array() );
		$type          = $this->get_attribute( 'type', 'text' );
		$value         = $this->get_attribute( 'value', $this->get_control_data() );

		$this->set_attribute( 'value', $value );

		$types = array( 'text', 'hidden', 'number', 'file', 'email', 'date', 'password' );

		if ( ! in_array( $type, $types, true ) ) {
			$type = 'text';
		}

		// get all validations ids.
		$validations_ids    = array_map( function ( $validation ) {
			return "'{$validation->get_id()}'";
		}, $validations );
		$validations_ids_js = implode( ',', $validations_ids );

		// Setup error from validations.
		if ( empty( $error ) && ! empty ( $validations_ids ) ) {
			$error = array(
				'alpine' => array(
					'x-show' => "hasAnyError( [{$validations_ids_js}] )",
				),
			);
		}

		// Setup validations directives.
		$directives = $this->get_alpine_directives();
		$directives = wp_parse_args( $directives, array(
			'x-spread'      => "setupField( '{$id_base}' )",
			':class'        => "{'border-red-300 text-red-900 placeholder-red-300 focus:border-red-300 focus:shadow-outline-red': hasAnyError( [{$validations_ids_js}] )}",
			':aria-invalid' => "hasAnyError( [{$validations_ids_js}] )",
		) );

		$this->set_alpine_directives( $directives );

		// Set the default class and label_class
		if ( in_array( $type, array( 'number', 'text', 'date', 'email', 'password' ) ) ) {
			$default_classes = array(
				'mt-1',
				'form-input',
				'block',
				'sm:text-sm',
				'sm:leading-5',
			);

			if ( 'date' !== $type ) {
				$default_classes[] = 'w-full';
			}

			$additional_classes = array(
				'py-2',
				'px-3',
				'border',
				'border-gray-300',
				'rounded-md',
				'shadow-sm',
				'focus:outline-none',
				'focus:shadow-outline-blue',
				'focus:border-blue-300',
				'transition',
				'duration-150',
				'ease-in-out',
			);

			$other_classes = array_merge( $default_classes, $additional_classes );

			if ( empty( $classes ) ) {
				$classes = $other_classes;
			} else {
				$classes = array_merge( $other_classes, $classes );
			}

			$this->set_attribute( 'class', $classes );
		}

		$output = '';

		if ( ! empty( $label ) ) {
			$input_label = new Label_Control( array(
				'id'   => "{$id_base}-label",
				'atts' => array(
					'for'   => $id_base,
					'class' => $label_class,
				),
				'args' => array(
					'value' => $label,
				),
			) );

			if ( ! $input_label->has_errors() ) {
				$output .= $input_label->render( false );
			} else {
				$input_label->log_errors();
			}
		}

		$desc_text       = '';
		$desc_position   = 'after';
		$desc_directives = array();
		$desc_classes    = array();

		if ( ! empty( $description ) ) {
			$desc_classes = array( 'mb-2', 'text-sm', 'leading-5', 'text-gray-500' );

			if ( is_array( $description ) ) {
				$desc_text       = ! empty( $description['text'] )       ? $description['text']       : '';
				$desc_directives = ! empty( $description['directives'] ) ? $description['directives'] : array();
				$desc_position   = ! empty( $description['position'] )   ? $description['position']   : $desc_position;
			}
		}

		$desc_control = new Paragraph_Control( array(
			'id'     => "{$id_base}-desc",
			'atts'   => array(
				'class'  => $desc_classes,
			),
			'args'   => array(
				'text'   => esc_html( $desc_text ),
			),
			'alpine' => $desc_directives,
		) );

		if ( ! empty( $description ) && 'before' === $desc_position ) {
			if ( ! $desc_control->has_errors() ) {
				$output .= $desc_control->render( false );
			} else {
				$desc_control->log_errors();
			}
		}

		if ( $error && in_array( $type, array( 'text', 'number', 'email', 'password' ), true ) ) {
			$output .= '<div class="relative">';
		}

		$atts = $this->process_atts( true );

		$output .= sprintf( '<input type="%1$s"%2$s />', esc_attr( $type ), $atts );

		if ( $error && in_array( $type, array( 'text', 'number', 'email', 'password' ), true ) ) {
			$div_classes = array( 'absolute', 'inset-y-0', 'right-0', 'pr-3', 'flex', 'items-center', 'pointer-events-none' );
			$directives  = is_array( $error ) && ! empty( $error['alpine'] ) ? $error['alpine'] : array();

			$div_atts = Attributes_Processor::prepare( array( 'class' => $div_classes ), $directives );

			$output .= sprintf( '<div%s>', $div_atts );

			$error_icon = new Icon_Control( array(
				'id'   => "{$id_base}-error-icon",
				'args' => array(
					'name'  => 'exclamation-circle',
					'type'  => 'solid',
					'size'  => 5,
				),
				'atts' => array(
					'class' => array( 'text-red-500' ),
				),
			) );

			if ( ! $error_icon->has_errors() ) {
				$output .= $error_icon->render( false );
			} else {
				$error_icon->log_errors();
			}

			$output .= '</div>';
		}

		if ( $error && in_array( $type, array( 'text', 'number', 'email', 'password' ), true ) ) {
			$output .= '</div>';
		}

		if ( ! empty( $description ) && 'after' === $desc_position ) {
			if ( ! $desc_control->has_errors() ) {
				$output .= $desc_control->render( false );
			} else {
				$desc_control->log_errors();
			}
		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

}
