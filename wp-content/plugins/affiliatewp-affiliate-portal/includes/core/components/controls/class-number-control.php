<?php
/**
 * Controls: Number Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

/**
 * Implements a number form control.
 *
 * @since 1.0.0
 *
 * @see Input_Control
 */
final class Number_Control extends Input_Control {

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
	 *     @type array  $args     Arguments to pass to the control and influence display. Must pass the control-
	 *                            specific arguments whitelist during validation. Default empty array.
	 *     @type array  $atts     {
	 *         Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *         the control-specific attributes whitelist during validation.
	 *
	 *         @type mixed $value Value for the number input. Default empty.
	 *         @type int   $min   Minimum value for the number input. Default empty.
	 *         @type int   $max   Maximum value for the number input. Default empty.
	 *         @type int   $step  Step by which numbers can be incremented. Default '0.01'.
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
	public function get_input_type() {
		return 'number';
	}

	/**
	 * @inheritDoc
	 */
	public function get_atts_whitelist() {
		$whitelist = array( 'value', 'min', 'max', 'step' );

		return array_merge( parent::get_atts_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function save_data( $data, $affiliate_id ) {
		$data = intval( $data );

		return parent::save_data( $data, $affiliate_id );
	}

}
