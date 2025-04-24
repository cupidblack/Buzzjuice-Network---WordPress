<?php
/**
 * Controls: Button Control
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
 * Implements a button form control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
class Button_Control extends Base_Control {

	/**
	 * Sets up the control.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 The `$icon` argument under `$value_atts` now supports accepting an Icon_Control object.
	 *
	 * @param array $metadata {
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
	 *         @type array  $value_atts  {
	 *             Value attributes for the button. Default empty.
	 *
	 *             @type string              $before Content to display before the value.
	 *             @type string              $after  Content to display after the value.
	 *             @type string              $value  Value to display.
	 *             @type string|Icon_Control $icon   Icon markup or Icon_Control object to render before
	 *                                               the button value.
	 *         }
	 *         @type string $url         URL to redirect the user to via onclick.
	 *         @type bool   $std_colors  Whether to apply standard color classes to the button. Default true.
	 *         @type bool   $std_classes Whether to apply standard classes to the button. Default true.
	 *     }
	 *     @type array  $atts     {
	 *         Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *         the control-specific attributes whitelist during validation.
	 *
	 *         @type string $value Value attribute for the button. Default empty.
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
	public function get_type() {
		return 'button';
	}

	/**
	 * @inheritDoc
	 */
	public function get_atts_whitelist() {
		$whitelist = array( 'value' );

		return array_merge( parent::get_atts_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'value_atts', 'url', 'std_colors', 'std_classes' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$default_classes = array(
			'py-2',
			'px-4',
			'border',
			'border-transparent',
			'text-sm',
			'leading-5',
			'font-medium',
			'rounded-md',
			'shadow-sm',
			'focus:outline-none',
			'focus:shadow-outline-blue',
			'transition duration-150',
			'ease-in-out',
		);

		$standard_colors = array(
			'text-white',
			'bg-indigo-600',
			'hover:bg-indigo-500',
			'active:bg-indigo-600',
		);

		$value   = $this->get_attribute( 'value' );
		$classes = $this->get_attribute( 'class', array() );

		$url        = $this->get_argument( 'url' );
		$value_atts = $this->get_argument( 'value_atts', array() );

		$use_standard_colors  = $this->get_argument( 'std_colors', true );
		$use_standard_classes = $this->get_argument( 'std_classes', true );

		if ( true === $use_standard_classes ) {
			$classes = array_merge( $default_classes, $classes );
		}

		if ( true === $use_standard_colors ) {
			$classes = array_merge( $classes, $standard_colors );
		}

		$this->set_attribute( 'class', $classes );

		if ( ! empty( $url ) ) {
			$url = sprintf( 'window.location=\'%1$s\';', esc_url( $url ) );

			$this->set_attribute( 'onclick', $url );
		}

		if ( ! empty( $value ) ) {
			$value = esc_html( $value );
		} else {
			// Value icon.
			if ( ! empty( $value_atts['icon'] ) ) {
				$icon = $value_atts['icon'];

				if ( is_string( $icon ) ) {
					$value .= $icon;
				} elseif ( $icon instanceof Icon_Control ) {
					if ( $icon->has_errors() ) {
						$icon->log_errors( $this->get_id() );
					} else {
						$value .= $icon->render( false );
					}
				}
			}

			// Before value.
			if ( ! empty( $value_atts['before'] ) ) {
				$value .= $value_atts['before'];
			}

			// The actual value.
			if ( ! empty( $value_atts['value'] ) ) {
				$value .= esc_html( $value_atts['value'] );
			}

			// After value;
			if ( ! empty( $value_atts['after'] ) ) {
				$value .= $value_atts['after'];
			}
		}

		$atts = $this->process_atts( true );

		$output = sprintf( '<button type="%1$s"%2$s>%3$s</button>', $this->get_type(), $atts, $value );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
}
