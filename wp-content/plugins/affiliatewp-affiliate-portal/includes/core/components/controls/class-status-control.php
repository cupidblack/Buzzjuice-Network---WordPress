<?php
/**
 * Controls: Status Control
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
 * Implements a single status control.
 *
 * @since 1.0.0
 *
 * @see   Base_Control
 */
final class Status_Control extends Base_Control {

	/**
	 * Sets up the control.
	 *
	 * @param array $metadata    {
	 *     Metadata for setting up the current control. Arguments are optional unless
	 *     otherwise stated.
	 *
	 *     @type string          $id          Required. Globally-unique ID for the current control.
	 *     @type string          $view_id     Required unless `$section` is also omitted. View ID to associate a registered
	 *                                control with.
	 *     @type string          $section     Required unless `$view_id` is also omitted. Section to associate a registered
	 *                                control with.
	 *     @type int             $priority    Priority within the section to display the control. Default 25.
	 *     @type array           $alpine      Array of alpine directives to pass to the control.
	 *     @type array           $args        {
	 *         Arguments to pass to the control and influence display. Must pass the control-
	 *         specific arguments whitelist during validation. Default empty array.
	 *
	 *         @type string $label  Label to display from which the label should be retrieved.
	 *         @type string $type   Label type to retrieve. Can be 'approved', 'rejected', or 'pending'. Default 'pending'
	 *     }
	 *     @type array           $atts        Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                                        the control-specific attributes whitelist during validation.
	 *     }
	 *
	 *     @param bool           $validate    Optional. Whether to validate the attributes (and split off any arguments).
	 *                                        Default true;
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'status';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'label', 'type' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * Renders the markup for the current control.
	 *
	 * @since 1.0.0
	 *
	 * @see   Base_Control::get_attributes()
	 * @see   Base_Control::get_arguments()
	 *
	 * @param bool $echo Optional. Whether to echo the rendered control markup. Default true;
	 *
	 * @return string|void The rendered control markup if `$echo` is true, otherwise the markup will be output.
	 */
	public function render( $echo = true ) {

		$classes = array( 'px-2', 'inline-flex', 'text-xs', 'leading-5', 'font-semibold', 'rounded-full' );
		switch ( $this->get_argument( 'type', '' ) ) {
			case 'approved':
				$classes = array_merge( $classes, array( 'bg-green-100', 'text-green-800' ) );
				break;
			case 'rejected':
				$classes = array_merge( $classes, array( 'bg-red-100', 'text-red-800' ) );
				break;
			default:
				$classes = array_merge( $classes, array( 'bg-blue-100', 'text-blue-800' ) );
		}

		$result = html()->span( array(
			'class'      => $classes,
			'text'       => $this->get_argument( 'label' ),
			'directives' => $this->get_alpine_directives(),
		), false );

		if ( true === $echo ) {
			echo $result;
		} else {
			return $result;
		}
	}

}
