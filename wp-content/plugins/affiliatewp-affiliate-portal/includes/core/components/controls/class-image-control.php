<?php
/**
 * Controls: Image Control
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.4
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Traits;

/**
 * Implements an image control.
 *
 * @since 1.0.4
 *
 * @see Base_Control
 */
final class Image_Control extends Base_Control {

	use Traits\Data_Getter;

	/**
	 * Sets up the control.
	 *
	 * @since 1.0.4
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
	 *         @type string|array $desc     {
	 *             Description for the image or an array containing directives and text key value pairs.
	 *             Default empty string.
	 *
	 *             @type string $text       Text to display inside the description element.
	 *             @type array  $directives Directives to pass along to the description element.
	 *             @type string $position   Position of the description. Accepts 'before' or 'after'. Default 'after'.
	 *         }
	 *         @type callable $get_callback Callback to return a value to display via the `%src` attribute. Ignored
	 *                                      if `$src` is defined. Callback will be passed the current affiliate ID
	 *                                      and any HTML will be stripped by kses before render. Signature:
	 *                                      `( $affiliate_id ) : string`. Default unused.
	 *     }
	 *     @type array  $atts     {
	 *         Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *         the control-specific attributes whitelist during validation.
	 *
	 *         @type string $src    Image source URL.
	 *         @type string $srcset Comma-separated list of image URLs to be used for different user agents.
	 *         @type int    $height Height in pixels to display the image.
	 *         @type int    $width  Width in pixels to display the image.
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
		return 'image';
	}

	/**
	 * @inheritDoc
	 */
	public function get_atts_whitelist() {
		$whitelist = array( 'src', 'srcset', 'height', 'width' );

		return array_merge( parent::get_atts_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'get_callback', 'desc' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * Retrieves the control data, in this case the src attribute.
	 *
	 * @since 1.0.4
	 *
	 * @param int $affiliate_id Current affiliate ID.
	 * @return string Value of the src attribute.
	 */
	public function get_data( $affiliate_id ) {
		return $this->get_attribute( 'src' );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base = $this->get_id_base();

		$description = $this->get_argument( 'desc', array() );

		$classes = $this->get_attribute( 'class', array() );
		$src     = $this->get_attribute( 'src', $this->get_control_data() );

		$src = esc_url( $src );

		$output = '';

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
			} else {
				$desc_text = $description;
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

		$atts = $this->process_atts( true );


		if ( ! empty( $description ) && 'before' === $desc_position ) {
			if ( ! $desc_control->has_errors() ) {
				$output .= $desc_control->render( false );
			} else {
				$desc_control->log_errors( $this->get_id() );
			}
		}

		$output .= sprintf( '<img%1$s />', $atts );

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
