<?php
/**
 * Controls: Stat Card Control
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
 * Implements a single statistic card control.
 *
 * @since 1.0.0
 *
 * @see Base_Control
 */
final class Card_Control extends Base_Control {

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
	 *     @type string $parent   Parent (card group) control ID. Unused if not set.
	 *     @type array  $alpine   Array of alpine directives to pass to the control.
	 *     @type array  $args     {
	 *         Arguments to pass to the control and influence display. Must pass the control-
	 *         specific arguments whitelist during validation. Default empty array.
	 *
	 *         @type array|string     $title {
	 *             String for the card title or an array of arguments for displaying the title.
	 *
	 *             @type string $text Title text.
	 *             @type array  $atts Attributes to pass to the Heading_Control for the card.
	 *         }
	 *         @type mixed|callable   $data        The value to display on this card or a callback that returns the data.
	 *                                             Callback will be passed the current affiliate ID. Default empty string.
	 *         @type string           $data_key    Key identifier to pass to `$data` if a callback. Default is the value
	 *                                             of the control ID.
	 *         @type string           $icon        Registered icon name to display alongside this card. Default empty string.
	 *         @type string|callable  $link        The "View all" URL (or a callback to retrieve a URL) to display under
	 *                                             the data. Leave blank to exclude link. Default empty string.
	 *         @type string           $link_label  Label to use with the link. Default 'View all'.
	 *         @type string           $link_target Link target. Default empty (unused).
	 *         @type mixed|callable   $compare     The data to compare against the primary data. Accepts a value or a
	 *                                             callback to retrieve a value. Callback will be passed the current
	 *                                             affiliate ID. Leave blank to skip comparison. Default false
	 *         @type string           $format      The format in which the data should be displayed. Can be "percentage". Leave blank to skip
	 *                                             formatting value. Default empty string.
	 *         @type string           $layout      Card layout to use. Accepts 'info' or 'stat'. Default 'stat'.
	 *         @type bool             $show_empty  Whether to show an empty card with no value. Default true (show).
	 *     }
	 *     @type array  $atts     Attributes, specifically HTML attributes to use for display purposes. Must pass
	 *                            the control-specific attributes whitelist during validation.
	 * }
	 * @param bool   $validate   Optional. Whether to validate the attributes (and split off any arguments).
	 *                           Default true;
	 */
	public function __construct( $metadata, $validate = true ) {
		parent::__construct( $metadata, $validate );
	}

	/**
	 * @inheritDoc
	 */
	public function get_type() {
		return 'card';
	}

	/**
	 * @inheritDoc
	 */
	public function get_args_whitelist() {
		$whitelist = array(
			'data', 'data_key', 'icon', 'title', 'link', 'link_label',
			'link_target', 'compare', 'comparison', 'format',
			'layout', 'show_empty',
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
			'data'        => '',
			'data_key'    => $this->get_id(),
			'icon'        => '',
			'title'       => '',
			'link'        => '',
			'link_label'  => __( 'View all', 'affiliatewp-affiliate-portal' ),
			'link_target' => '',
			'compare'     => false,
			'comparison'  => false,
			'format'      => '',
			'layout'      => 'stat',
			'show_empty'  => true,
		);

		$args = wp_parse_args( $args, $defaults );

		if ( is_callable( $args['data'] ) ) {
			$data = call_user_func_array( $args['data'], array( $args['data_key'], $affiliate_id ) );
		} else {
			$data = $args['data'];
		}

		// If show_empty is not true and there's no data, bail.
		if ( true !== $args['show_empty'] && empty( $data ) ) {
			return;
		}

		if ( is_callable( $args['compare'] ) ) {
			$compare = call_user_func_array( $args['compare'], array( $args['data_key'], $affiliate_id ) );
		} else {
			$compare = $args['compare'];
		}

		if ( is_callable( $args['link'] ) ) {
			$link_url = call_user_func_array( $args['link'], array( $args['data_key'], $affiliate_id ) );
		} else {
			$link_url = $args['link'];
		}

		if ( ! empty( $link_url ) ) {
			$link_url = esc_url( $link_url );
		}

		if ( ! empty( $args['layout'] ) && ! in_array( $args['layout'], array( 'stat', 'info' ) ) ) {
			$args['layout'] = 'stat';
		}

		$inner_wrap_classes = array( 'flex-1' );

		$output = '';

		// Start Card Wrapper
		$output .= html()->div_start( array(
			'class' => array(
				'bg-white',
				'overflow-hidden',
				'shadow',
				'rounded-lg',
				'flex',
				'flex-col',
				'place-content-between',
			),
		), false );

		// Data Wrap Start
		$output .= html()->div_start( array(
			'class' => array(
				'px-4',
				'py-5',
				'sm:p-6',
				'flex',
				'items-center',
			),
		), false );

		if ( false === empty( $args['icon'] ) ) {
			$inner_wrap_classes[] = 'ml-4';
			$inner_wrap_classes[] = 'w-0';

			if ( 'info' === $args['layout'] ) {
				$icon_size = 12;
			} else {
				$icon_size = 8;
			}

			$card_icon = new Icon_Control( array(
				'id'      => "{$id_base}-icon",
				'view_id' => $this->get_view_id(),
				'section' => $this->get_prop( 'section' ),
				'args'    => array(
					'name' => $args['icon'],
					'size' => $icon_size,
				),
			) );

			if ( ! $card_icon->has_errors() ) {
				$output .= html()->div_start( array(
					'class' => array( 'flex-shrink-0', 'text-gray-400' ),
				), false );

				$output .= $card_icon->render( false );

				$output .= html()->div_end( false );
			} else {
				$card_icon->log_errors();
			}
		}

		//
		// Layout specific innards.
		//

		switch ( $args['layout'] ) {
			case 'info':
				$inner_wrap_classes[] = 'block';

				// Inner wrap start
				$output .= html()->div_start( array(
					'class' => $inner_wrap_classes,
				), false );

				if ( ! empty( $args['title'] ) ) {
					if ( is_array( $args['title'] ) && isset( $args['title']['text'] ) ) {
						$title = esc_html( $args['title']['text'] );
					} else {
						$title = esc_html( $args['title'] );
					}

					if ( isset( $args['title']['atts'] ) ) {
						$title_atts = $args['title']['atts'];
					} else {
						$title_atts = array();
					}

					$heading = new Heading_Control( array(
						'id'      => "{$id_base}-head",
						'view_id' => $this->get_view_id(),
						'section' => $this->get_prop( 'section' ),
						'args'    => array(
							'text'  => $title,
							'level' => 3,
						),
						'atts'    => $title_atts,
					) );

					if ( ! $heading->has_errors() ) {
						$output .= $heading->render( false );
					} else {
						$heading->log_errors();
					}
				}

				if ( ! empty( $data ) ) {
					$card_data = new Paragraph_Control( array(
						'id'      => "{$id_base}-data",
						'view_id' => $this->get_view_id(),
						'section' => $this->get_prop( 'section' ),
						'atts'    => array(
							'class' => array( 'block' ),
						),
						'args'    => array(
							'text'  => $data,
						),
					) );

					if ( ! $card_data->has_errors() ) {
						$output .= $card_data->render( false );
					} else {
						$card_data->log_errors();
					}
				}

				// Inner wrap end
				$output .= '</div>';
				break;

			case 'stat':
			default:
				// Inner wrap start
				$output .= html()->div_start( array(
					'class' => $inner_wrap_classes,
				), false );


				if ( false !== $compare ) {

					if ( $data > $compare ) {
						$type       = 'increase';
						$percentage = 100 - affwp_calculate_percentage( $compare, $data );

					} elseif ( $data < $compare ) {
						$type       = 'decrease';
						$percentage = 100 - affwp_calculate_percentage( $data, $compare );

					} else {
						$type       = 'equal';
						$percentage = 0;
					}

					if ( is_infinite( $percentage ) || 'equal' === $type ) {
						$args['comparison'] = false;
					} else {
						$args['comparison'] = array(
							'percentage' => affwp_format_percentage( (float) $percentage, 1 ),
							'type'       => $type,
						);
					}
				}

				$output .= html()->element_start( 'dl', array(), false );

				$output .= html()->dt( array(
					'text'  => $args['title'],
					'class' => array(
						"text-sm",
						"leading-5",
						"font-medium",
						"text-gray-500",
						"truncate",
					),
				), false );

				$output .= html()->element_start( 'dd', array(
					'class' => array(
						'flex',
						'items-baseline',
					),
				), false );

				$output .= html()->div_start( array(
					'class' => array( 'text-3xl', 'leading-8', 'font-semibold', 'text-gray-900' ),
				), false );

				$output .= 'percentage' === $args['format'] ? affwp_format_percentage( (float) $data, 2 ) : $data;

				$output .= html()->div_end( false );

				if ( is_array( $args['comparison'] ) ) {
					$compare_wrapper_class = array(
						'ml-2',
						'flex',
						'items-baseline',
						'text-sm',
						'leading-5',
						'font-semibold',
					);

					$icon_class = array( 'self-center', 'flex-shrink-0', 'h-4', 'w-4' );

					if ( isset( $args['comparison']['type'] ) && 'increase' === $args['comparison']['type'] ) {
						$compare_wrapper_class[] = 'text-green-600';
						$icon_class[]            = 'text-green-500';
						$screen_reader_text      = _x( 'Increased by', 'statistical comparison', 'affiliatewp-affiliate-portal' );
						$icon                    = 'arrow-up';
					} else {
						$compare_wrapper_class[] = 'text-red-600';
						$icon_class[]            = 'text-red-500';
						$screen_reader_text      = _x( 'Decreased by', 'statistical comparison', 'affiliatewp-affiliate-portal' );
						$icon                    = 'arrow-down';
					}

					$output .= html()->div_start( array(
						'class' => $compare_wrapper_class,
					), false );

					$compare_icon = new Icon_Control( array(
						'id'      => "{$id_base}-compare-icon",
						'view_id' => $this->get_view_id(),
						'section' => $this->get_prop( 'section' ),
						'args'    => array(
							'name'  => $icon,
							'type'  => 'solid',
						),
						'atts'    => array(
							'class' => $icon_class,
						),
					) );

					if ( ! $compare_icon->has_errors() ) {
						$output .= $compare_icon->render( false );
					} else {
						$compare_icon->log_errors();
					}

					$output .= html()->span( array(
						'text'  => $screen_reader_text,
						'class' => array( 'sr-only' ),
					), false );

					$output .= $args['comparison']['percentage'];

					$output .= html()->div_end( false );
				}

				$output .= html()->element_end( 'dd', false );
				$output .= html()->element_end( 'dl', false );

				// Inner wrap end
				$output .= html()->div_end( false );

				break;

		}

		// Data Wrap End
		$output .= html()->div_end( false );

		// Maybe append a link
		if ( false === empty( $link_url ) ) {
			$card_link = new Link_Control( array(
				'id'   => "{$id_base}-link",
				'atts' => array(
					'class'  => array(
						'font-medium',
						'text-indigo-600',
						'hover:text-indigo-500',
						'transition',
						'ease-in-out',
						'duration-150',
					),
					'href' => $link_url,
				),
				'args' => array(
					'label'  => $args['link_label'],
					'target' => $args['link_target']
				),
			) );

			if ( ! $card_link->has_errors() ) {
				$output .= html()->div_start( array(
					'class' => array( 'bg-gray-50', 'px-4', 'py-4', 'sm:px-6', 'text-sm', 'leading-5' ),
				), false );

				$output .= $card_link->render( false );

				$output .= '</div>';
			} else {
				$card_link->log_errors( $this->get_view_id() );
			}
		}

		// End Card Wrapper
		$output .= html()->div_end( false );

		if ( true === $echo ) {
			echo $output;
		} else {
			return $output;
		}

	}

}
