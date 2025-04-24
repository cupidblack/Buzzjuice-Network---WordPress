<?php
/**
 * Controls: Creative Card Control
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
 * Implements a single creative card control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class Creative_Card_Control extends Base_Control {

	/**
	 * Sets up the control.
	 *
	 * @since 1.0.0
	 * @since 1.0.4 Added support for the `$image` argument to accept an Image_Control object.
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
	 *     @type string $parent   Parent (card group) control ID. Unused if not set.
	 *     @type array  $alpine   Array of alpine directives to pass to the control.
	 *     @type array  $args     {
	 *         Arguments to pass to the control and influence display. Must pass the control-
	 *         specific arguments whitelist during validation. Default empty array.
	 *
	 *         @type string               $icon        Registered icon name to display alongside this card. Default empty string.
	 *         @type string               $title       Card title. Default empty string.
	 *         @type string|Image_Control $image       Creative image to display on the card and on the preview.
	 *                                                 Image src value if a string, otherwise accepts a fully-qualified
	 *                                                 Image_Control object. Default empty string.
	 *         @type string               $text        Creative text to display on the card and on the preview. Default empty string.
	 *         @type string               $url         Creative url. Default empty string.
	 *         @type string               $description Creative description. Default empty string.
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
		return 'creative_card';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array(
			'icon',
			'title',
			'image',
			'text',
			'description',
			'creative_id',
			'url',
			'qr_code',
		);

		return array_merge( parent::get_args_whitelist(), $whitelist );
	}

	/**
	 * @inheritDoc
	 */
	public function render( $echo = true ) {
		$id_base = $this->get_id_base();
		$args    = $this->get_arguments();

		$output = '';

		$groups = isset( affiliate_wp()->connections ) && isset( $args['creative_id'] )
			? affiliate_wp()->connections->get_connected(
				'group',
				'creative',
				intval( $args['creative_id'] )
			)
			: array();

		// Main card wrapper.
		$output .= html()->div_start( array(
			'class'      => array(
				'col-span-1',
				'flex',
				'flex-col',
				'text-center',
				'bg-white',
				'rounded-lg',
				'shadow',
				'creative-card',
				"creative-card-{$args['creative_id']}",
			),
			'directives' => array(
				'x-data' => 'AFFWP.portal.creatives.default()',
				'@keydown.window.escape' => 'open = false'
			),
			'data-id' => absint( $args['creative_id'] ),
			'data-groups' => is_array( $groups )

				// Have to use column because it doesn't like comma's.
				? implode( ':', array_values( array_map( 'intval', $groups ) ) )
				: '' ,
		), false );

		$output .= html()->div_start( array(
			'class'      => array(
				'cursor-pointer',
				'flex-1',
				'flex',
				'p-4',
				'items-center',
				'justify-center',
			),
			'directives' => array(
				'@click' => 'open = true',
			)
		), false );

		$output .= html()->div_start( array(
			'class' => array(
				'self-center',
				'p-4',
			),
		), false );

		// Card Content.
		$content = "";

		if ( ! empty ( $args['image'] ) ) {
			$image = $args['image'];

			if ( ! $image instanceof Image_Control ) {

				$image_control = new Image_Control( array(
					'id'   => "{$id_base}_creative_card_image",
					'atts' => array(
						'src'   => $image,
						'alt'   => $args['text'],
						'class' => array( 'object-scale-down', 'h-48', 'w-full', 'mx-auto' ),
					),
				) );
			} else {
				$image_control = $image;
			}

			if ( ! $image_control->has_errors() ) {
				$content = $image_control->render( false );
			} else {
				$image_control->log_errors( $this->get_id() );
			}

		} else {
			$content = '<span class="underline">' . esc_attr( $args['text'] ) . '</span>';
		}

		if ( ! empty( $args['qr_code'] ?? '' ) ) {
			$content = $args['qr_code'];
		}

		$output .= $content;

		$output .= html()->div_end( false );
		$output .= html()->div_end( false );

		// Card Footer.
		$output .= $this->get_footer( $args );

		// Modal.
		$output .= $this->get_modal( $args );

		$output .= html()->div_end( false );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Retrieves the creative card footer.
	 *
	 * @since 1.0.0
	 *
	 * @return string Card footer HTML output.
	 */
	private function get_footer( array $args ) {
		$output = '';

		// Card Footer Buttons.
		$output .= html()->div_start( array(
			'class' => array(
				'border-t',
				'border-gray-200',
			),
		), false );

		$output .= html()->div_start( array(
			'class' => array(
				'-mt-px',
				'flex',
			),
		), false );

		// Button Preview.
		$output .= html()->div_start( array(
			'class' => array(
				'w-0',
				'flex-1',
				'flex',
				'border-r',
				'border-gray-200',
			),
		), false );

		$button_preview = new Link_Control( array(
			'id'     => 'creative-card-preview-button',
			'atts'   => array(
				'class' => array(
					'relative',
					'-mr-px',
					'flex-1',
					'inline-flex',
					'items-center',
					'justify-center',
					'py-4',
					'text-sm',
					'leading-5',
					'text-gray-700',
					'font-medium',
					'border',
					'border-transparent',
					'rounded-bl-lg',
					'hover:text-gray-500',
					'focus:outline-none',
					'focus:shadow-outline-blue',
					'focus:border-blue-300',
					'focus:z-10',
					'transition',
					'ease-in-out',
					'duration-150'
				),
				'href'  => '#',
			),
			'args'   => array(
				'label' => __( 'View', 'affiliatewp-affiliate-portal' ),
				'icon'  => new Icon_Control( array(
					'id'   => 'creative-card-preview-button-icon',
					'args' => array(
						'name'  => 'eye',
						'color' => 'gray-400',
					),
				) ),
			),
			'alpine' => array(
				'@click.prevent' => 'open = true',
			),
		) );

		if ( ! $button_preview->has_errors() ) {
			$output .= $button_preview->render( false );
		} else {
			$button_preview->log_errors( $this->get_view_id() );
		}

		$output .= html()->div_end( false );

		// Button copy on everything else but QR codes.
		if ( empty( $args['qr_code'] ) ) {

			// Button Copy.
			$output .= html()->div_start( array(
				'class' => array(
					'-ml-px',
					'w-0',
					'flex-1',
					'flex',
				),
			), false );

			$button_copy = new Button_Control( array(
				'id'     => 'creative-card-copy-button',
				'atts'   => array(
					'class' => array(
						'relative',
						'-mr-px',
						'w-0',
						'flex-1',
						'inline-flex',
						'items-center',
						'justify-center',
						'py-4',
						'text-sm',
						'leading-5',
						'text-gray-700',
						'font-medium',
						'border',
						'border-transparent',
						'rounded-br-lg',
						'hover:text-gray-500',
						'focus:outline-none',
						'focus:shadow-outline-blue',
						'focus:border-blue-300',
						'focus:z-10',
						'transition',
						'ease-in-out',
						'duration-150',
					),
				),
				'args'   => array(
					'std_colors' => false,
					'value_atts' => array(
						'before' => '<span class="ml-2">',
						'value'  => _x( 'Copy', 'creative code', 'affiliatewp-affiliate-portal' ),
						'after'  => '</span>',
						'icon'   => new Icon_Control( array(
							'id'   => 'creative-card-copy-button-icon',
							'alpine' => array(
								'x-show' => '!copying',
							),
							'args' => array(
								'name'  => 'duplicate',
								'color' => 'gray-400',
							),
						) ),
					)
				),
				'alpine' => array(
					'@click' => 'copy($event)',
				),
			) );

			if ( ! $button_copy->has_errors() ) {
				$output .= $button_copy->render( false );
			} else {
				$button_copy->log_errors( $this->get_view_id() );
			}

			$output .= html()->div_end( false );
		}


		$output .= html()->div_end( false );
		$output .= html()->div_end( false );

		return $output;
	}

	/**
	 * Retrieves the creative card modal.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Argument key.
	 * @return string Modal HTML output.
	 */
	private function get_modal( $args ) {
		$id_base = $this->get_id_base();
		$output  = '';

		$modal_data = '';

		// Modal description.
		if ( isset( $args['description'] ) ) {

			$modal_data .= html()->div_start( array(
				'class' => array(
					'mb-5',
					'prose',
					'prose-lg2',
					'border-b-2',
					'border-gray-100',
					'pb-3',
				),
			), false );

			$desc_control = new Paragraph_Control( array(
				'id'      => 'creative-card-modal-desc',
				'view_id' => $this->get_view_id(),
				'section' => $this->get_prop( 'section' ),
				'atts'    => array(
					'class' => array( 'text-smb', 'leading-5b', 'text-gray-500b' ),
				),
				'args'    => array(
					'text' => $args['description'],
				),
			) );

			if ( ! $desc_control->has_errors() ) {
				$modal_data .= $desc_control->render( false );
			} else {
				$desc_control->log_errors( $this->get_view_id() );
			}

			$modal_data .= html()->div_end( false );
		}

		// Modal Preview.
		$modal_data .= html()->div_start( array(
			'class' => array(
				'creative-preview',
				'mb-10',
			),
		), false );

		// Only show Preview pill with non-qr codes.
		if ( empty( $args['qr_code'] ) ) {

			$modal_data .= html()->element_start( 'span', array(
				'class' => array(
					'mb-3',
					'inline-flex',
					'items-center',
					'px-3',
					'py-1',
					'rounded-md',
					'text-sm',
					'font-medium',
					'leading-5',
					'bg-indigo-100',
					'text-indigo-800',
				),
			), false );

			$modal_data .= _x( 'Preview', 'creative modal', 'affiliatewp-affiliate-portal' );
			$modal_data .= html()->element_end( 'span', false );
		}

		$image_markup = '';

		if ( ! empty( $args['image'] ) ) {

			$image = $args['image'];

			if ( ! $image instanceof Image_Control ) {
				$image_control = new Image_Control( array(
					'id' => "{$id_base}_creative_modal_image",
					'atts' => array(
						'src' => $image,
						'alt' => $args['text'],
					),
				) );
			} else {
				$image_control = $image;
			}

			if ( ! $image_control->has_errors() ) {
				$image_markup = $image_control->render( false );
			} else {
				$image_control->log_errors( $this->get_id() );
			}

		}

		// Image...
		if ( ! empty( $image_markup ) ) {

			$modal_data .= html()->div_start( array(
				'class' => array(
					'bg-gray-50b',
					'p-4b',
				),
			), false );

			$modal_data .= $image_markup;
			$modal_data .= html()->div_end( false );

		} else {

			// QR Code.
			if ( ! empty( $args['qr_code'] ?? '' ) ) {

				$modal_data .= html()->div_start( array(
					'class' => array(
						'bg-gray-50b',
						'p-4b',
						'flex',
						'items-center',
						'justify-center',
					),
				), false );

				$modal_data .= $args['qr_code'];

				$modal_data .= html()->div_end( false );

			// Must be text.
			} else {

				$text_control = new Paragraph_Control( array(
					'id'      => 'creative-card-modal-text',
					'view_id' => $this->get_view_id(),
					'section' => $this->get_prop( 'section' ),
					'atts'    => array(
						'class' => array( 'underline' ),
					),
					'args'    => array(
						'text' => esc_html( $args['text'] ),
					),
				) );

				if ( ! $text_control->has_errors() ) {
					$modal_data .= $text_control->render( false );
				} else {
					$text_control->log_errors();
				}
			}
		}

		if ( ! empty( $args['qr_code'] ) ) {

			$text_control = new Heading_Control( array(
				'id'      => 'creative-card-modal-text',
				'view_id' => $this->get_view_id(),
				'section' => $this->get_prop( 'section' ),
				'atts'    => array(
					'class' => array(
						'w-250px',
						'mt-12',
						'mb-6',
						'text-center',
					),
				),
				'args'    => array(
					'text' => affwp_get_creative( $args['creative_id'] )->get_name(),
				),
				'level' => 2,
			) );

			if ( ! $text_control->has_errors() ) {
				$modal_data .= $text_control->render( false );
			} else {
				$text_control->log_errors();
			}
		}

		$modal_data .= html()->div_end( false );

		// Modal Code.
		$modal_data .= html()->div_start( array(
			'class' => array(
				'creative-code',
			),
		), false );

		$image_or_text = '';

		if ( ! empty( $image_markup ) ) {
			$image_or_text = $image_markup;
		} else {
			$image_or_text = esc_attr( $args['text'] );
		}

		// Add code if not a QR code...
		if ( empty( $args['qr_code'] ) ) {

			$creative = '<a href="' . esc_url( affwp_get_affiliate_referral_url( array( 'base_url' => esc_url( $args['url'] ) ) ) ) .'" title="' . esc_attr( $args['text'] ) . '">' . $image_or_text . '</a>';

			$modal_data .= html()->element_start( 'span', array(
				'class' => array(
					'mb-3',
					'inline-flex',
					'items-center',
					'px-3',
					'py-1',
					'rounded-md',
					'text-sm',
					'font-medium',
					'leading-5',
					'bg-indigo-100',
					'text-indigo-800',
				)
			), false );

			$modal_data .= __( 'Code', 'affiliatewp-affiliate-portal' );
			$modal_data .= html()->element_end( 'span', false );

			$code_block = new Code_Block_Control( array(
				'id'      => 'creative-card-modal-code',
				'view_id' => $this->get_view_id(),
				'section' => $this->get_prop( 'section' ),
				'args' => array(
					'data'      => $creative,
					'show_copy' => true,
				)
			) );

			if ( ! $code_block->has_errors() ) {
				$modal_data .= $code_block->render( false );
			} else {
				$code_block->log_errors( $this->get_view_id() );
			}

		// QR Code.
		}

		$modal_data .= html()->div_end( false );

		$modal = new Modal_Control( array(
			'id'      => 'creative-card-modal',
			'view_id' => $this->get_view_id(),
			'section' => $this->get_prop( 'section' ),
			'args'    => array(
				'data' => $modal_data,
			),
		) );

		if ( ! $modal->has_errors() ) {
			$output .= $modal->render( false );
		} else {
			$modal->log_errors( $this->get_view_id() );
		}

		return $output;
	}

}
