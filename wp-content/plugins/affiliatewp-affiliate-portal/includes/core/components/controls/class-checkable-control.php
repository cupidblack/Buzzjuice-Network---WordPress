<?php
/**
 * Controls: Checkable Control
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
 * Middleware for building form controls that support the checked attribute.
 *
 * @since 1.0.0
 *
 * @see Input_Control
 */
abstract class Checkable_Control extends Input_Control {

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
	 *     @type array  $args     Arguments to pass to the control and influence display. Must pass the control-
	 *                            specific arguments whitelist during validation. Default empty array.
	 *     @type array  $atts     {
	 *         Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *         the control-specific attributes whitelist during validation.
	 *
	 *         @type bool $checked Whether the control is checked or not.
	 *     }
	 * }
	 * @param bool   $validate   Optional. Whether to validate the attributes (and split off any arguments).
	 *                           Default true;
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_atts_whitelist() {
		$whitelist = array( 'checked' );

		return array_merge( parent::get_atts_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'desc', 'label_class', 'label_href', 'label_href_class', 'label' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function save_data( $data, $affiliate_id ) {
		if ( ! in_array( $data, array( 'on', 'off' ) ) ) {
			$data = 'off';
		}

		$meta_key = $this->get_meta_key();

		$saved = affwp_update_affiliate_meta( $affiliate_id, $meta_key, $data );

		return $saved;
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base = $this->get_id_base();

		$label            = $this->get_argument( 'label' );
		$label_class      = $this->get_argument( 'label_class' );
		$label_href       = $this->get_argument( 'label_href' );
		$label_href_class = $this->get_argument( 'label_href_class' );
		$description      = $this->get_argument( 'desc' );
		$validations      = $this->get_argument( 'validations', array() );

		$type = $this->get_input_type();

		$this->set_attribute( 'type', $type );

		$data = $this->get_control_data();

		if ( 'radio' === $type ) {
			$this->set_attribute( 'value', $this->get_id() );
		}

		if ( 'on' === $data ) {
			$this->set_attribute( 'checked', 'checked' );
		}

		// Setup validations directives.
		$directives = $this->get_alpine_directives();
		if ( ! isset( $directives['x-spread'] ) ) {
			if ( 'checkbox' === $type ) {
				$directives['x-spread'] = "setupField( '{$id_base}', 'checkbox' )";
			} else if ( 'radio' === $type ) {
				$value                  = $this->get_attribute( 'value', $this->get_id() );
				$directives['x-spread'] = "setupField( '{$id_base}', 'radio', '{$value}' )";
			}
		}

		$this->set_alpine_directives( $directives );

		$atts = $this->process_atts( true );

		$output = '<div class="flex items-start">';

		$output .= '<div class="flex items-center h-5">';

		$output .= sprintf( '<input%s/>', $atts );

		$output .= '</div>';

		$output .= '<div class="ml-3 text-sm leading-5">';

		if ( ! empty( $label ) ) {
			$control_label = new Label_Control( array(
				'id'   => "{$id_base}-label",
				'atts' => array(
					'for'   => $id_base,
					'class' => $label_class,
				),
				'args' => array(
					'value'      => $label,
					'href'       => $label_href,
					'href_class' => $label_href_class,
				),
			) );

			if ( ! $control_label->has_errors() ) {
				$output .= $control_label->render( false );
			} else {
				$control_label->log_errors();
			}
		}

		if ( ! empty( $description ) ) {
			$output .= sprintf( '<p class="text-gray-500">%s</p>',
				esc_html( $description )
			);
		}

		$output .= '</div>';
		$output .= '</div>';

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

}
