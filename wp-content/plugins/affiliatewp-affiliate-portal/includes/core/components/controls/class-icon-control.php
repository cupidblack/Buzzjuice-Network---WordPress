<?php
/**
 * Controls: Icon Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Icons_Registry;
use AffiliateWP_Affiliate_Portal\Utilities\Attributes_Processor;
use function AffiliateWP_Affiliate_Portal\html;

/**
 * Implements an icon control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class Icon_Control extends Base_Control {

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
	 *         @type string $name        The name of the registered icon.
	 *         @type string $type        The icon type (solid or outline).
	 *         @type int    $size        Shorthand height and width (will use the same value) to use. (2-10) Default 5. 
	 *         @type string $path        Custom path to use instead of accessing the icons registry.
	 *                                   Default empty (unused).
	 *         @type string $color       Color to use for the icon. Default empty (unused).
	 *         @type string $hover_color Color to use for the icon on mouse over. Default empty (unused).
	 *     }
	 *     @type array  $atts     {
	 *         Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *         the control-specific attributes whitelist during validation.
	 *
	 *         @type array $stroke  Hyphenated stroke attributes for the icon.
	 *         @type array $viewBox Values to scale the svg. Provide 4 numbers for min-x, min-y, width, height.
	 *                              Optional to use when adding a custom $path.
	 *     }
	 * }
	 * @param bool  $validate Optional. Whether to validate the attributes (and split off any arguments).
	 *                        Default true.
	 */
	public function __construct( $metadata, $validate = true ) {
		$this->set_up_errors();

		if ( ! empty( $metadata['args']['name'] ) ) {
			$name = $metadata['args']['name'];

			$icons_registry = Icons_Registry::instance();

			$icon = $icons_registry->offsetExists( $name );

			if ( ! $icon && empty( $metadata['args']['path'] ) ) {
				$this->add_error(
					'invalid_icon_name',
					sprintf( 'The \'%1$s\' icon slug supplied for the \'%2$s\' icon control is invalid.',
						$name,
						isset( $metadata['id'] ) ? $metadata['id'] : 'invalid'
					),
					$metadata
				);
			}
		}

		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'icon';
	}

	/**
	 * @inheritDoc
	 */
	public function get_atts_whitelist() {
		$whitelist = array( 'stroke', 'viewBox' );

		return array_merge( parent::get_atts_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'name', 'type', 'size', 'path', 'color', 'hover_color' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {

		$defaults = array(
			'name'        => '',
			'type'        => 'outline',
			'size'        => 5,
			'path'        => '',
			'color'       => '',
			'hover_color' => '',
		);

		$args = $this->get_arguments();

		$args = wp_parse_args( $args, $defaults );

		$classes = array();

		$size_classes = array(
			2  => array( 'h-2', 'w-2' ),
			3  => array( 'h-3', 'w-3' ),
			4  => array( 'h-4', 'w-4' ),
			5  => array( 'h-5', 'w-5' ),
			6  => array( 'h-6', 'w-6' ),
			7  => array( 'h-7', 'w-7' ),
			8  => array( 'h-8', 'w-8' ),
			9  => array( 'h-9', 'w-9' ),
			10 => array( 'h-10', 'w-10' ),
		);

		if ( ! empty( $args['size'] ) || $args['size'] == 0 ) {

			if ( array_key_exists( $args['size'], $size_classes ) ) {
				$classes = array_merge( $classes, $size_classes[ $args['size'] ] );
			} else {
				$classes = array_merge( $classes, $size_classes[5] );
			}

		}

		if ( ! empty( $args['color'] ) ) {
			// TODO handle for dynamic classes here.
			$classes = array_merge( $classes, array( "text-{$args['color']}" ) );
		}

		if ( ! empty( $args['hover_color'] ) ) {
			// TODO handle for dynamic classes here.
			$classes = array_merge( $classes, array( "hover:text-{$args['hover_color']}" ) );
		}

		$class_attribute = $this->get_attribute( 'class', array() );

		if ( empty( $class_attribute ) ) {
			$this->set_attribute( 'class', $classes );
		} else {
			$this->set_attribute( 'class', array_merge( $class_attribute, $classes ) );
		}

		if ( ! in_array( $args['type'], array( 'outline', 'solid' ), true ) ) {
			$args['type'] = 'outline';
		}

		$viewBox = $this->get_attribute( 'viewBox' );

		if ( ! empty( $viewBox ) ){
			if ( is_array( $viewBox ) ) {
				$viewBox = implode( ' ', $viewBox );
			} else {
				// Set non-array to default.
				$viewBox = '';
			}

		}

		if ( ! empty( $args['name'] ) ) {
			$icons_registry = Icons_Registry::instance();

			$icon = $icons_registry->get_icon_type( $args['name'], $args['type'] );

			if ( ! empty( $icon ) ) {
				$args['path'] = $icon;
				// Set icon to default viewBox.
				$viewBox = '';
			}
		}

		$this->set_attribute( 'viewBox', $viewBox );

		$atts = $this->process_atts( true );

		$output = '';

		if ( ! empty( $args['path'] ) ) {
			if ( 'outline' === $args['type'] ) {

				$viewBox = empty( $viewBox ) ? '0 0 24 24' : $viewBox;

				$output .= sprintf( '<svg%1$s fill="none" viewBox="%2$s" stroke="currentColor">', $atts, $viewBox );
				$output .= $args['path'];
				$output .= '</svg>';

			} else {

				$viewBox = empty( $viewBox ) ? '0 0 20 20' : $viewBox;

				$output .= sprintf( '<svg%1$s fill="currentColor" viewBox="%2$s">', $atts, $viewBox );
				$output .= $args['path'];
				$output .= '</svg>';

			}
		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}

	}

}
