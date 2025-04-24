<?php
/**
 * Controls: Copy Button Control
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
 * Implements a copy button control.
 *
 * @since 1.0.0
 *
 * @see Button_Control
 */
final class Copy_Button_Control extends Button_Control {

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
	 *         @type string $content Control content.
	 *     }
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 * }
	 * @param bool   $validate Optional. Whether to validate the attributes (and split off any arguments).
	 *                         Default true;
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array( 'target', 'content' );

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$atts = $this->get_attributes();
		$args = $this->get_arguments();

		$alpine_directives = $this->get_alpine_directives();

		$output = '';

		if ( ! is_ssl() ) {
			$this->add_error( 'no_ssl',
				sprintf( 'The \'%1$s\' copy button control for the \'%2$s\' view cannot render due to a lack of SSL.',
					$this->get_id(),
					$this->get_view_id()
				),
				$atts
			);
		} else {

			$content = ! empty( $args['content'] ) ? $args['content'] : '';

			if ( empty( $content ) && empty( $alpine_directives ) ) {
				$this->add_error( 'missing_content',
					sprintf( 'The \'%1$s\' copy button control for the \'%2$s\' view cannot render due to a missing button content.',
						$this->get_id(),
						$this->get_view_id()
					),
					$args
				);
			} else {
				$alpine = $alpine_directives;
				if ( empty( $alpine ) ) {
					$alpine = array(
						'@click' => "copy('$content')",
						'x-text' => "copyMessage",
					);
				}
				$button = new Button_Control( array(
					'id'      => $this->get_id(),
					'view_id' => $this->get_view_id(),
					'section' => $this->get_prop( 'section' ),
					'atts'    => array(
						'value'  => '',
						'class'  => array(
							'font-medium',
							'text-indigo-600',
							'hover:text-indigo-500',
							'transition',
							'duration-150',
							'ease-in-out',
							'ml-2',
						),
					),
					'args'    => array(
						'std_colors'  => false,
						'std_classes' => false,
					),
					'alpine'  => $alpine,
				) );

				if ( ! $button->has_errors() ) {
					$directives = array();
					if ( empty( $alpine_directives ) ) {
						$directives = array(
							'x-data' => 'AFFWP.portal.core.copyToClipboard()',
						);
					}
					// Copy Button Wrapper Start
					$output .= html()->div_start( array(
						'class' => array( 'sm:ml-4', 'sm:flex-shrink-0', 'w-full', 'sm:w-auto', 'text-center', 'mt-2', 'sm:mt-0' ),
						'directives' => $directives,
					), false );

					$output .= $button->render( false );

					// Copy Button Wrapper End
					$output .= '</div>';
				}
			}
		}

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}
}
