<?php
/**
 * Controls: Textarea Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Utilities\HTML;
use function AffiliateWP_Affiliate_Portal\html;

/**
 * Implements a textarea form control.
 *
 * @since 1.0.0
 *
 * @see Form_Control
 */
final class Textarea_Control extends Form_Control {

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
	 *         @type string $content Contents to render in the textarea. Default empty string.
	 *         @type string $label Label attribute for the textarea. Default empty.
	 *         @type string $desc  Description for the textarea. Default empty.
	 *     }
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
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
		return 'textarea';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'content', 'desc', 'label' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function save_data( $data, $affiliate_id ) {
		$data = wp_filter_nohtml_kses( $data );

		parent::save_data( $data, $affiliate_id );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base = $this->get_id_base();

		$content = $this->get_argument( 'content', $this->get_control_data() );
		$label   = $this->get_argument( 'label' );
		$desc    = $this->get_argument( 'desc' );

		$output = '';

		$default_classes = array( 'block', 'w-full' );

		$classes = $this->get_attribute( 'class', array() );

		if ( empty( $classes ) ) {
			$classes = $default_classes;
		} else {
			$classes = array_merge( $default_classes, $classes );
		}

		$this->set_attribute( 'class', $classes );

		if ( ! empty( $label ) ) {
			$label = new Label_Control( array(
				'id'   => "{$id_base}-label",
				'atts' => array(
					'for'   => $id_base,
				),
				'args' => array(
					'value' => $label
				),
			) );

			if ( ! $label->has_errors() ) {
				$output .= $label->render( false );
			}
		}

		$atts = $this->process_atts( true );

		$output .= sprintf( '<textarea%1$s>%2$s</textarea>', $atts, $content );

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

}
