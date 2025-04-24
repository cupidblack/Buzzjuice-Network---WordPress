<?php
/**
 * Controls: View Wrapper Control
 *
 * @since       1.0.0
 * @subpackage  Core/Components/Controls
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @package     AffiliateWP Affiliate Portal
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Controls;

use function AffiliateWP_Affiliate_Portal\html;

#[\AllowDynamicProperties]

/**
 * Implements a view wrapper control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class Wrapper_Control extends Base_Control {

	/**
	 * Sets up the control.
	 *
	 * @since 1.0.0
	 *
	 * @param array $metadata  {
	 *     Metadata for setting up the current control. Arguments are optional unless otherwise stated.
	 *
	 *     @type string $id       Unused. Hard coded as {view_id}:wrapper.
	 *     @type string $view_id  Required unless `$section` is also omitted. View ID to associate a registered
	 *                            control with.
	 *     @type string $section  Required unless `$view_id` is also omitted. Section to associate a registered
	 *                            control with.
	 *     @type int    $priority Priority within the section to display the control. Default 25.
	 *     @type array  $alpine   Array of alpine directives to pass to the control.
	 *     @type array  $args     Arguments to pass to the control and influence display. Must pass the control-
	 *                            specific arguments whitelist during validation. Default empty array.
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 * }
	 * @param bool  $validate  Optional. Whether to validate the attributes (and split off any arguments).
	 *                         Default true.
	 */
	public function __construct( array $metadata, $validate = true ) {
		if ( ! empty( $metadata['view_id'] ) ) {
			$view_id = $metadata['view_id'];

			$metadata['id'] = "{$view_id}:wrapper";
		}

		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'wrapper';
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$atts = $this->get_attributes();
		$atts['directives'] = $this->get_alpine_directives();

		$output = html()->div_start( $atts, false );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
}
