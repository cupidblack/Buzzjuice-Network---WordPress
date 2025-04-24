<?php
/**
 * Controls: Select Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Utilities\Attributes_Processor;

/**
 * Implements a select (drop down) form control.
 *
 * @since 1.0.0
 *
 * @see Form_Control
 */
final class Select_Control extends Form_Control {

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
	 *         @type string 		 $desc        Description to display below the control.
	 *         @type string 		 $label       Label for the drop-down. Default empty.
	 *         @type string 		 $label_class Label class for the drop-down. Default empty.
	 *         @type array  		 $options     Options for the select in value/label pairs.
	 *         @type string|string[] $selected 	  Default value(s) to output as "selected".
	 *     }
	 *     @type array  $atts     {
	 *         Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *         the control-specific attributes whitelist during validation.
	 *
	 *         @type string     $multiple Whether this should be considered a multi-select element.
	 *     }
	 * }
	 * @param bool  $validate  Optional. Whether to validate the attributes (and split off any arguments).
	 *                         Default true.
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'select';
	}

	/**
	 * @inheritDoc
	 */
	public function get_atts_whitelist() {
		$whitelist = array( 'multiple' );

		return array_merge( parent::get_atts_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'desc', 'options', 'label', 'label_class', 'selected'  );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function save_data( $data, $affiliate_id ) {
		if ( ! is_array( $data ) ) {
			$data = (array) $data;
		}

		if ( ! empty( $data ) ) {
			$data = array_map( 'sanitize_text_field', $data );
		}

		parent::save_data( $data, $affiliate_id );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base = $this->get_id_base();

		$multiple = $this->get_attribute( 'multiple', false );
		$classes  = $this->get_attribute( 'class', array() );

		$selected      = $this->get_argument( 'selected', $this->get_control_data() );
		$options       = $this->get_argument( 'options', array() );
		$label_class   = $this->get_argument( 'label_class', array() );
		$label         = $this->get_argument( 'label' );
		$desc          = $this->get_argument( 'desc' );
		$validations   = $this->get_argument( 'validations', array() );

		// Setup validations directives.
		$directives = $this->get_alpine_directives();
		$directives = wp_parse_args( $directives, array(
			'x-spread'      => "setupField( '{$id_base}', 'checkbox' )",
		) );

		$this->set_alpine_directives( $directives );

		/*
		 * Ensure the `selected` argument is an array to handle comparisons
		 * for single and multiple selects.
		 */
		if ( ! empty( $selected ) && ! is_array( $selected ) ) {
			$selected = array( $selected );
		}

		$output = $options_output = '';

		$default_classes = array(
			'mt-1',
			'block',
		);

		$classes = array_merge( $default_classes, $classes );

		$this->set_attribute( 'class', $classes );

		if ( ! empty( $options ) ) {
			foreach ( $options as $key => $option ) {
				$default_selected = empty( $selected ) ? '' : selected( true, in_array( $key, $selected ), false );

				$options_output .= sprintf( '<option value="%1$s" %2$s>%3$s</option>',
					esc_attr( $key ),
					$default_selected,
					esc_html( $option )
				);
			}
		}

		if ( ! empty( $label ) ) {
			$label = new Label_Control( array(
				'id'   => "{$id_base}-label",
				'atts' => array(
					'for'   => $id_base,
					'class' => $label_class,
				),
				'args' => array(
					'value' => $label,
				),
			) );

			if ( ! $label->has_errors() ) {
				$output .= $label->render( false );
			}
		}

		$atts = $this->process_atts( true );

		$output .= sprintf( '<select%1$s>%2$s</select>', $atts, $options_output );

		if ( ! empty( $desc ) ) {
			$output .= sprintf( '<p class="text-sm leading-5 mt-2 text-gray-500">%s</p>',
				esc_html( $desc )
			);
		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Retrieves the input type.
	 *
	 * @since 1.0.0
	 *
	 * @return string Input type;
	 */
	public function get_input_type() {
		return 'select';
	}

}