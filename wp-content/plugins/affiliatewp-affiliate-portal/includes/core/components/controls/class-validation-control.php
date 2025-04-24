<?php
/**
 * Controls: Validation Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */

namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use AffiliateWP_Affiliate_Portal\Core\Traits;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

#[\AllowDynamicProperties]

/**
 * Implements a single field validation control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
class Validation_Control extends Base_Control {

	use Traits\Data_Validator;

	/**
	 * Sets up the control.
	 *
	 * @param array $metadata {
	 *     Metadata for setting up the current control. Arguments are optional unless
	 *     otherwise stated.
	 *
	 *     Metadata for setting up the current control. Arguments are optional unless otherwise stated.
	 *
	 *     @type string $id       Required. Globally-unique ID for the current control.
	 *     @type string $view_id  Required unless `$section` is also omitted. View ID to associate a registered
	 *                            control with.
	 *     @type string $section  Required unless `$view_id` is also omitted. Section to associate a registered
	 *                            control with.
	 *     @type int    $priority Priority within the section to display the control. Default 25.
	 *     @type string $parent   Parent control ID for select types of controls. Unused if not set.
	 *     @type array  $alpine   Array of alpine directives to pass to the control.
	 *     @type array  $args     {
	 *         Arguments to pass to the control and influence display. Must pass the control-
	 *         specific arguments whitelist during validation. Default empty array.
	 *
	 *         @type string   $message           Message to display when validation fails. Default empty string.
	 *                                           This message should be translate-able.
	 *         @type callable $validate_callback Callback to determine if this validation passed. It receives the
	 *                                           validation data, and should return a boolean.
	 *                                           Callback signature: `( $data, $affiliate_id ) : bool`.
	 *     }
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 * @param bool  $validate Optional. Whether to validate the attributes (and split off any arguments).
	 *                        Default true;
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );

		$control_id = $this->get_id();

		if ( 'invalid' === $control_id ) {
			return;
		}

		$validate_callback = $this->get_argument( 'validate_callback' );

		if ( empty( $validate_callback ) ) {
			$this->add_error( 'missing_validate_callback',
				sprintf( 'The validate_callback for the \'%1$s\' control is missing.',
					$control_id
				),
				$this->to_array( array( 'args' => $this->get_arguments() ) )
			);
		}

		if ( ! empty( $validate_callback ) && ! is_callable( $validate_callback ) ) {
			$this->add_error( 'invalid_validate_callback',
				sprintf( 'The validate_callback \'%1$s\' for the \'%2$s\' control is invalid.',
					$validate_callback,
					$control_id
				),
				$this->to_array( array( 'args' => $this->get_arguments() ) )
			);
		}
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'validation';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'message', 'validate_callback' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base = $this->get_id_base();
		$message = $this->get_argument( 'message' );

		$output = '';

		// Get control classes.
		$default_classes = array( 'block', 'text-red-600', 'setting', 'text-control', 'mt-1', 'text-sm' );
		$classes         = $this->get_attribute( 'class', array() );
		if ( empty( $classes ) ) {
			$classes = $default_classes;
		} else {
			$classes = array_merge( $default_classes, $classes );
		}

		// Get control Alpine directives.
		$alpine_directives = $this->get_alpine_directives();
		if ( empty( $alpine_directives ) ) {
			$alpine_directives = array(
				'x-show' => "hasAnyError( '{$id_base}' )",
			);
		}

		// Create text control.
		$text_control = new Text_Control( array(
			'id'     => "{$id_base}-text",
			'atts'   => array(
				'class' => $classes,
			),
			'args'  => array(
				'text' => $message,
			),
			'alpine' => $alpine_directives,
		) );

		if ( ! $text_control->has_errors() ) {
			$output .= $text_control->render( false );
		}

		// Render control.
		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
}
