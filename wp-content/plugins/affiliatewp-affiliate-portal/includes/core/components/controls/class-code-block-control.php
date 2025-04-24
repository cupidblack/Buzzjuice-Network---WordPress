<?php
/**
 * Controls: Code Block Control
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
 * Implements a code block control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class Code_Block_Control extends Base_Control {

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
	 *         @type bool             $show_copy   Show Copy to clipboard button. Default is false.
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
		return 'code_block';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array(
			'data',
			'data_key',
			'show_copy',
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

		// Wrapper div.
		$output .= html()->div_start( array(
			'class' => array(
				'code-block',
				'prose',
			),
		), false );

		// Code Block.
		$output .= '<pre class="max-w-full overflow-auto text-base leading-relaxed text-gray-500">';
		$output .= '<code x-ref="creativeCode" class="whitespace-pre-wrap break-normal2 text-left" style="hyphens: none;">';
		$output .= esc_html( $data );
		$output .= '</code>';
		$output .= '</pre>';

		// Show Copy button.
		if ( $args['show_copy'] ) {
			$output .= html()->div_start( array(
				'class' => array(
					'mt-5',
				),
			), false );

			$output .= html()->element_start( 'span', array(
				'class' => array(
					'inline-flex',
					'rounded-md',
					'shadow-sm'
				),
			), false );

			$copy_button = new Link_Control( array(
				'id'   => 'code_block_copy_to_clipboard',
				'atts' => array(
					'class' => array(
						'inline-flex',
						'items-center',
						'px-4',
						'py-2',
						'border',
						'border-transparent',
						'text-base',
						'leading-6',
						'font-medium',
						'rounded-md',
						'text-white',
						'bg-indigo-600',
						'hover:bg-indigo-500',
						'focus:outline-none',
						'focus:border-indigo-700',
						'focus:shadow-outline-indigo',
						'active:bg-indigo-700',
						'transition',
						'ease-in-out',
						'duration-150',
					),
					'href' => '#',
				),
				'args'    => array(
					'label' => __( 'Copy to clipboard', 'affiliatewp-affiliate-portal' ),
					'icon'  => new Icon_Control( array(
						'id' => 'code_block_copy_to_clipboard_icon',
						'args' => array(
							'name'  => 'duplicate',
							'type'  => 'solid',
						),
					) ),
				),
				'alpine'  => array(
					'@click.prevent' => 'copy($event)',
				),
			) );

			if ( ! $copy_button->has_errors() ) {
				$output .= $copy_button->render( false );
			} else {
				$copy_button->log_errors( $this->get_view_id() );
			}

			$output .= html()->element_end( 'span', false );

			$output .= html()->div_end( false );
		}

		$output .= html()->div_end( false );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}

	}

}
