<?php
/**
 * Controls: Form Label Control
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
 * Implements a form label control.
 *
 * @since 1.0.0
 *
 * @see Form_Control
 */
final class Label_Control extends Base_Control {

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
	 *         @type string       $value      Label text.
	 *         @type string       $href       URL for the label link (if set).
	 *         @type string|array $href_class Classes for the label link (if set).
	 *     }
	 *     @type array  $atts     {
	 *         Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *         the control-specific attributes whitelist during validation.
	 *
	 *         @type string $for Specifies which form element a label is bound to. Default empty.
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
		return 'label';
	}

	/**
	 * @inheritDoc
	 */
	public function get_atts_whitelist() {
		$whitelist = array( 'for' );

		return array_merge( parent::get_atts_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'href', 'href_class', 'value' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base = $this->get_id_base();

		$value      = $this->get_argument( 'value' );
		$href       = $this->get_argument( 'href' );
		$href_class = $this->get_argument( 'href_class' );

		$output = '';

		$atts = $this->process_atts( true );

		if ( ! empty( $href ) ) {

			$link = new Link_Control( array(
				'id'   => "{$id_base}-link",
				'atts' => array(
					'class'  => $href_class,
					'href'   => $href,
					'target' => '_blank',
				),
				'args' => array(
					'label' => esc_html( $value ),
				),
			) );

			if ( ! $link->has_errors() ) {
				$output .= sprintf( '<label%1$s>%2$s</label>', $atts, $link->render( false ) );
			} else {
				$link->log_errors();
			}


		} else {

			$output .= sprintf( '<label%1$s>%2$s</label>', $atts, esc_html( $value ) );

		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}

	}

}