<?php
/**
 * Controls: Link Control
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
use function AffiliateWP_Affiliate_Portal\html;

/**
 * Implements a link control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class Link_Control extends Base_Control {

	use Traits\Data_Getter;

	/**
	 * Sets up the control.
	 *
	 * @since 1.0.0
	 * @since 1.0.4 Added support for an `$image` argument.
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
	 *         @type string        $label         Link label text.
	 *         @type Image_Control $image         Image_Control object to optionally render within the link control.
	 *                                            If defined, `$label` and `$icon` will be ignored.
	 *         @type Icon_Control  $icon          Icon_Control object to optionally render within the link control.
	 *         @type string        $icon_position Position of the icon in the link label text. Accepts 'before' or 'after'.
	 *                                            Default 'after'.
	 *         @type callable      $get_callback  Callback to return a value to display via the `$href` attribute. Ignored
	 *                                            if `$href` is defined. Callback will be passed the current affiliate ID
	 *                                            and any HTML will be stripped by kses before render. Signature:
	 *                                            `( $affiliate_id ) : string`. Default unused.
	 *     }
	 *     @type array  $atts     {
	 *         Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *         the control-specific attributes whitelist during validation.
	 *
	 *         @type string $href Link URL.
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
		return 'link';
	}

	/**
	 * @inheritDoc
	 */
	public function get_atts_whitelist() {
		$whitelist = array( 'href' );

		return array_merge( parent::get_atts_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'icon', 'icon_position', 'label', 'image', );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * Retrieves the control data, in this case the href attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id Current affiliate ID.
	 * @return string Value of the href attribute.
	 */
	public function get_data( $affiliate_id ) {
		return $this->get_attribute( 'href' );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base = $this->get_id_base();

		$icon_position = $this->get_argument( 'icon_position', 'before' );
		$link_icon     = $this->get_argument( 'icon' );
		$label         = $this->get_argument( 'label' );
		$image         = $this->get_argument( 'image' );

		$classes = $this->get_attribute( 'class', array() );
		$href    = $this->get_attribute( 'href', $this->get_control_data() );

		$icon_markup = $image_markup = '';

		if ( ! empty( $image ) && $image instanceof Image_Control ) {
			if ( ! $image->has_errors() ) {
				$image_markup = $image->render( false );
			} else {
				$image->log_errors( $this->get_id() );
			}
		}

		if ( $link_icon instanceof Icon_Control ) {
			if ( ! in_array( $icon_position, array( 'before', 'after' ), true ) ) {
				$icon_position = 'before';
			}

			$classes = array_merge( $classes, array( 'flex', 'items-center' ) );

			$this->set_attribute( 'class', $classes );

			if ( 'before' === $icon_position ) {
				$icon_position_classes = array( 'mr-3' );
			} else {
				$icon_position_classes = array( 'ml-3', '-mr-1');
			}

			$icon_classes = $link_icon->get_attribute( 'class', array() );

			// Merge in icon positioning classes.
			$icon_classes = array_merge( $icon_position_classes, $icon_classes );

			$link_icon->set_attribute( 'class', $icon_classes );

			if ( ! $link_icon->has_errors() ) {
				$icon_markup = $link_icon->render( false );
			} else {
				$link_icon->log_errors( $this->get_view_id() );
			}
		}

		$this->set_attribute( 'href', $href );

		$label = empty( $label ) ? '' : sanitize_text_field( $label );

		$atts = $this->process_atts( true );

		if ( ! empty( $image_markup ) ) {

			$output = sprintf( '<a%1$s>%2$s</a>', $atts, $image_markup );

		} elseif ( 'before' === $icon_position ) {

			$output = sprintf( '<a%1$s>%2$s %3$s</a>', $atts, $icon_markup, $label );

		} else {

			$output = sprintf( '<a%1$s>%2$s %3$s</a>', $atts, $label, $icon_markup );

		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

}
