<?php
/**
 * Controls: List Control
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
 * Implements an ordered/unordered list control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class List_Control extends Base_Control {

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
	 *         @type string   $list_type Type of list. Accepts 'ordered' or 'unordered'. Default 'unordered'.
	 *         @type string[] $items     List items. Allowed HTML tags are `<span>`, `<a>`, `<p>`, `<ul>`, `<ol>`,
	 *                                   and `<li>`.
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
		return 'list';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'list_type', 'items' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$items     = $this->get_argument( 'items', array() );
		$list_type = $this->get_argument( 'list_type', 'unordered' );

		if ( ! in_array( $list_type, array( 'ordered', 'unordered') ) ) {
			$list_type = 'unordered';
		}

		$classes = $this->get_attribute( 'class', array() );

		// set default list type
		if ( 'ordered' === $list_type ) {
			$extra_classes = array(
				'list-inside',
				'list-decimal',
			);
		} elseif ( 'unordered' === $list_type ) {
			$extra_classes = array(
				'list-inside',
				'list-disc',
			);
		} else {
			$extra_classes = array();
		}

		if ( empty( $classes ) ) {
			$classes = $extra_classes;
		} else {
			$classes = array_merge( $extra_classes, $classes );
		}

		$this->set_attribute( 'class', $classes );

		$allowed_tags = array(
			'span' => array(),
			'ul'   => array(),
			'ol'   => array(),
			'li'   => array(),
			'p'    => array(),
			'a'    => array(
				'href'   => array(),
				'rel'    => array(),
				'target' => array(),
			),
		);

		if ( ! empty( $items ) && is_array( $items ) ) {
			foreach ( $items as $index => $item ) {
				$value = wp_kses( $item, $allowed_tags );

				if ( empty( $value ) ) {
					unset( $items[ $index ] );
				} else {
					$items[ $index ] = $value;
				}
			}
		} else {
			$this->add_error( 'missing_items',
				sprintf( 'The \'%1$s\' list control for the \'%2$s\' view does not have any items.',
					$this->get_id(),
					$this->get_view_id()
				),
				$this->get_arguments()
			);

			$items = array();
		}

		$atts = $this->process_atts( true );

		$output = '';

		if ( ! empty( $items ) ) {
			if ( 'unordered' === $list_type ) {
				$output .= sprintf( '<ul%1$s>', $atts );
			} else {
				$output .= sprintf( '<ol%1$s>', $atts );
			}

			foreach ( $items as $item ) {
				$output .= sprintf( '<li class="mb-2 ml-5">%s</li>', $item );
			}

			if ( 'unordered' === $list_type ) {
				$output .= '</ul>';
			} else {
				$output .= '</ol>';
			}
		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

}
