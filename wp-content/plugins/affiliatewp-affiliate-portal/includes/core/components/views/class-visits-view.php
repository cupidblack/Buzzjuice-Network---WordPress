<?php
/**
 * Views: Visits View
 *
 * @package   Core/Components
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Views;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Interfaces\View;

#[\AllowDynamicProperties]

/**
 * Sets up the Visits view.
 *
 * @since 1.0.0
 */
class Visits_View implements View {

	/**
	 * Retrieves the view sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_sections() {
		return array(
			'visits-table' => array(
				'wrapper'  => false,
				'priority' => 5,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
			)
		);
	}

	/**
	 * Retrieves the view controls.
	 *
	 * @since 1.0.0
	 *
	 * @return array Sections.
	 */
	public function get_controls() {
		return array(
			new Controls\Wrapper_Control( array(
				'id'      => 'affwp-affiliate-portal-visits',
				'view_id' => 'visits',
				'section' => 'wrapper',
			) ),
			new Controls\Table_Control( array(
				'id'      => 'visits-table',
				'view_id' => 'visits',
				'section' => 'visits-table',
				'args'    => array(
					'schema' => array(
						'table_name'          => 'visits-table',
						'page_count_callback' => function ( $args ) {
							$number = isset( $args[ 'number' ] ) ? $args[ 'number' ] : 20;
							return absint( ceil( affwp_count_visits( $args[ 'affiliate_id' ] ) / $number ) );
						},
						'data_callback'       => function ( $args ) {
							return affiliate_wp()->visits->get_visits( $args );
						},
						'schema'              => array(
							'url'       => array(
								'title'           => __( 'URL', 'affiliatewp-affiliate-portal' ),
								'priority'        => 5,
								'render_callback' => function ( \AffWP\Visit $row, $table_control_id ) {
									$url = affwp_make_url_human_readable( $row->url );

									$control_id = "{$table_control_id}_url";

									if ( empty( $url ) ) {
										return Controls\Text_Control::create(
											$control_id,
											_x( 'None set', 'visit URL', 'affiliatewp-affiliate-portal' )
										);
									} else {
										return new Controls\Link_Control( array(
											'id'   => $control_id,
											'args' => array(
												'label' => $url,
												'icon'  => new Controls\Icon_Control( array(
													'id'   => "{$table_control_id}_url_icon",
													'args' => array(
														'name'  => 'external-link',
														'class' => array( 'transition', 'ease-in-out', 'duration-150', 'opacity-0', 'group-hover:opacity-100', 'ml-2', 'h-5', 'w-5' ),
													),
												) ),
											),
											'atts' => array(
												'href'   => $row->url,
												'class'  => array( 'group', 'flex', 'items-center', 'font-medium', 'text-indigo-600', 'hover:text-indigo-500', 'transition', 'ease-in-out', 'duration-150' ),
												'target' => 'blank',
											),
										) );
									}
								},
							),
							'referrer'  => array(
								'title'           => __( 'Referring URL', 'affiliatewp-affiliate-portal' ),
								'priority'        => 10,
								'render_callback' => function ( \AffWP\Visit $row, $table_control_id ) {
									if ( ! $row->referrer ) {
										return Controls\Text_Control::create(
											"{$table_control_id}_referrer",
											__( 'Direct Traffic', 'affiliatewp-affiliate-portal' )
										);
									}
									return new Controls\Link_Control( array(
										'id'   => "{$table_control_id}_referrer",
										'atts' => array(
											'href' => $row->referrer,
										),
										'args' => array(
											'label' => affwp_make_url_human_readable( $row->referrer ),
										),
									) );
								},
							),
							'converted' => array(
								'title'           => __( 'Converted', 'affiliatewp-affiliate-portal' ),
								'priority'        => 15,
								'render_callback' => function ( \AffWP\Visit $row, $table_control_id ) {
									$icon       = empty( $row->referral_id ) ? 'x-circle' : 'check-circle';
									$converted  = empty( $row->referral_id ) ? 'unconverted' : 'converted';
									$icon_color = empty( $row->referral_id ) ? 'text-red-400' : 'text-green-400';

									return new Controls\Icon_Control( array(
										'id'   => "{$table_control_id}_converted",
										'args' => array(
											'name'       => $icon,
											'size'       => 6,
											/* translators: 1: either converted, or not converted */
											'aria-label' => sprintf( __( 'Visit %s', 'affiliatewp-affiliate-portal' ), $converted ),
										),
										'atts' => array(
											'id'    => 'notice_icon',
											'class' => array( $icon_color ),
										),
									) );
								},
							),
							'date'      => array(
								'title'           => __( 'Date', 'affiliatewp-affiliate-portal' ),
								'priority'        => 20,
								'render_callback' => function ( \AffWP\Visit $row, $table_control_id ) {
									return Controls\Text_Control::create( "{$table_control_id}_date", $row->date_i18n( 'datetime' ) );
								},
							),
						),
					),
				),
			) ),
		);
	}
}
