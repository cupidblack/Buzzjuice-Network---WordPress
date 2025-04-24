<?php
/**
 * Controls: Heading Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Utilities\HTML;

/**
 * Implements a heading control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class Heading_Control extends Base_Control {

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
	 *         @type string $text  The paragraph text.
	 *         @type int    $level The heading level (h1-h3).
	 *     }
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 * }
	 * @param bool   $validate Optional. Whether to validate the attributes (and split off any arguments).
	 *                         Default true.
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'heading';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'text', 'level' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$text  = $this->get_argument( 'text', '' );
		$level = $this->get_argument( 'level', 3 );

		if ( ! in_array( $level, array( 1, 2, 3), true ) ) {
			$level = 3;
		}

		switch ( $level ) {
			case 1:
				$classes = array( 'text-3xl', 'font-semibold', 'text-gray-900', 'mb-5', 'sm:mb-10' );
				break;

			case 2:
				$classes = array( 'text-xl', 'font-medium', 'leading-6', 'text-gray-900', 'mb-3' );
				break;

			case 3:
				$classes = array( 'text-lg', 'font-medium', 'leading-6', 'text-gray-900', 'mb-2' );
				break;

			default:
				$classes = array( 'text-base', 'leading-6', 'font-medium', 'text-gray-900' );
				break;
		}

		$class_attr = $this->get_attribute( 'class', array() );

		if ( empty( $class_attr ) ) {
			$class_attr = $classes;
		} else {
			$class_attr = array_merge( $classes, $class_attr );
		}

		$this->set_attribute( 'class', $class_attr );

		$output = sprintf( '<h%1$s%2$s>%3$s</h%1$s>', $level, $this->process_atts( true ), esc_html( $text ) );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}

	}

}
