<?php
/**
 * Controls: Modal Control
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
 * Implements a modal control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class Modal_Control extends Base_Control {

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
	 *         @type mixed|callable   $data        The value to display on this card or a callback that returns the data.
	 *                                             Callback will be passed the current affiliate ID. Default empty string.
	 *         @type string           $data_key    Key identifier to pass to `$data` if a callback. Default is the value
	 *                                             of the control ID.
	 *     }
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 * }
	 * @param bool  $validate   Optional. Whether to validate the attributes (and split off any arguments).
	 *                           Default true.
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'modal';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array(
			'data',
			'data_key',
		);

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base      = $this->get_id_base();
		$args         = $this->get_arguments();
		$affiliate_id = affwp_get_affiliate_id();

		$defaults = array(
			'data'     => '',
			'data_key' => $this->get_id(),
		);

		$args = wp_parse_args( $args, $defaults );

		if ( is_callable( $args['data'] ) ) {
			$data = call_user_func_array( $args['data'], array( $args['data_key'], $affiliate_id ) );
		} else {
			$data = $args['data'];
		}

		$output = '';

		$output .= '<div x-show="open" class="fixed z-10 inset-0 overflow-y-auto" style="display:none;">';

		$output .= html()->div_start( array(
			'class' => array(
				'flex',
				'items-center',
				'justify-center',
				'min-h-screen',
				'pt-4',
				'px-4',
				'pb-20',
				'text-center',
				'sm:p-0',
			),
		), false );

		// Background overlay, show/hide based on modal state.
		$output .= '<div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity z-40b">';
		$output .= '<div class="absolute inset-0 bg-gray-500 opacity-75"></div>';
		$output .= '</div>';

		// This element is to trick the browser into centering the modal contents.
		$output .= '<span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;';

		$output .= '<div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3/4b sm:p-10 min-w-1/4b" role="dialog" aria-modal="true" aria-labelledby="modal-headline">';

		// Modal close button.
		$output .= html()->div_start( array(
			'class' => array(
				'absolute',
				'top-0',
				'right-0',
				'p-1',
			)
		), false );

		$output .= html()->div_start( array(
			'class' => array(
				'flex',
				'items-center',
				'justify-center',
				'h-12',
				'w-12',
				'rounded-full',
				'focus:outline-none',
				'focus:bg-gray-600',
				'cursor-pointer',
			),
			'directives' => array(
				'@click' => 'open = false',
			)
		), false );

		$modal_close_icon = new Icon_Control( array(
			'id'   => 'modal_close',
			'args' => array(
				'name'  => 'x',
				'color' => 'black',
				'size'  => 6,
			),
		) );
		if ( ! $modal_close_icon->has_errors() ) {
			$output .= $modal_close_icon->render( false) ;
		} else {
			$modal_close_icon->log_errors( 'modal-control' );
		}

		$output .= html()->div_end( false );
		$output .= html()->div_end( false );

		// Modal content.
		$output .= $data;

		$output .= '</div>';

		$output .= html()->div_end( false );
		$output .= html()->div_end( false );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}

	}

}
