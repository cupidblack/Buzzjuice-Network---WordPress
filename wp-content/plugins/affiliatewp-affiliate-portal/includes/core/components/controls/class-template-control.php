<?php
/**
 * Controls: Template Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */

namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use function AffiliateWP_Affiliate_Portal\html;

/**
 * Implements a template control that can have multiple controls.
 *
 * @since 1.0.0
 *
 * @see Form_Control
 */
final class Template_Control extends Form_Control {

	/**
	 * Sets up the control.
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
	 *         @type Base_Control[] $controls List of controls for display.
	 *     }
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 * }
	 * @param bool  $validate   Optional. Whether to validate the attributes (and split off any arguments).
	 *                          Default true.
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'template';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'controls' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		/** @var Base_Control[] $controls List of controls */
		$controls = $this->get_argument( 'controls', array() );

		if ( empty( $controls ) ) {
			$this->add_error( 'missing_template_controls',
				sprintf( 'No controls were defined for the \'%1$s\' template control for the \'%2$s\' view.',
					$this->get_id(),
					$this->get_view_id()
				),
				$this->get_arguments()
			);
		}

		if ( $this->has_errors() ) {
			$this->log_errors();

			return;
		}

		$atts               = $this->get_attributes();
		$atts['directives'] = $this->get_alpine_directives();

		// Template wrapper.
		$output = html()->element_start( 'template', $atts, false );

		// Add single node inside template with same attributes.
		$output .= html()->div_start( $this->get_attributes(), false );

		// Render each control inside template control.
		foreach ( $controls as $control ) {
			if ( ! $control->has_errors() ) {
				$output .= $control->render( false );
			} else {
				$control->log_errors( "{$this->get_id()} template" );
			}
		}

		$output .= html()->div_end( false );

		$output .= html()->element_end( 'template', false );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}

	}

}
