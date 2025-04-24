<?php
/**
 * Views: Statistics View
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
 * Sets up the Statistics view.
 *
 * @since 1.0.0
 */
class Statistics_View implements View {

	/**
	 * Retrieves the view sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_sections() {
		return array(
			'stats' => array(
				'priority' => 1,
				'wrapper'  => false,
				'columns'  => array(
					'header'  => 3,
					'content' => 3,
				),
			),
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
				'id'      => 'wrapper',
				'view_id' => 'stats',
				'section' => 'wrapper',
				'atts'    => array(
					'id' => 'affwp-affiliate-portal-stats',
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'       => 'earnings_card_group',
				'view_id'  => 'stats',
				'section'  => 'stats',
				'priority' => 1,
				'args'     => array(
					'columns' => 4,
					'cards'   => array(
						array(
							'title'    => __( 'Unpaid referrals', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'unpaid_referrals',
							'data'     => array( $this, 'get_report_data' ),
						),
						array(
							'title'    => __( 'Paid referrals', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'paid_referrals',
							'data'     => array( $this, 'get_report_data' ),
						),
						array(
							'title'    => __( 'Visits', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'visits',
							'data'     => array( $this, 'get_report_data' ),
						),
						array(
							'title'    => __( 'Conversion rate', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'conversion_rate',
							'data'     => array( $this, 'get_report_data' ),
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'       => 'referrals_card_group',
				'view_id'  => 'stats',
				'section'  => 'stats',
				'priority' => 5,
				'args'     => array(
					'columns' => 4,
					'cards'   => array(
						array(
							'title'    => __( 'Unpaid earnings', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'unpaid_earnings',
							'data'     => array( $this, 'get_report_data' ),
						),
						array(
							'title'    => __( 'Paid earnings', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'paid_earnings',
							'data'     => array( $this, 'get_report_data' ),
						),
						array(
							'title'    => __( 'Commission rate', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'commission_rate',
							'data'     => array( $this, 'get_report_data' ),
						),
					),
				),
			) ),
			new Controls\Table_Control( array(
				'id'       => 'campaigns-table',
				'view_id'  => 'stats',
				'section'  => 'stats',
				'priority' => 11,
				'args' => array(
					'schema' => array(
						'name'          => 'campaigns-table',
						'page_count_callback' => function ( $args ) {
							$number    = isset( $args[ 'number' ] ) ? $args[ 'number' ] : 20;
							$campaigns = affiliate_wp()->campaigns->get_campaigns( array(
								'affiliate_id' => $args[ 'affiliate_id' ],
							), true );

							return absint( ceil( $campaigns / $number ) );
						},
						'data_callback'       => function ( $args ) {
							$campaigns = affiliate_wp()->campaigns->get_campaigns( $args );

							foreach ( $campaigns as $index => $campaign ) {
								if ( empty( $campaign->campaign ) ) {
									$campaign->campaign = _x( 'None set', 'campaign', 'affiliatewp-affiliate-portal' );

									$campaigns[ $index ] = $campaign;
								}
							}

							return $campaigns;
						},
						'schema'              => array(
							'campaign'        => array(
								'title'           => __( 'Campaign', 'affiliatewp-affiliate-portal' ),
								'render_callback' => function( $row, $table_control_id ) {
									return Controls\Text_Control::create( "{$table_control_id}_campaign", $row->campaign );
								},
							),
							'visits'          => array(
								'title'           => __( 'Visits', 'affiliatewp-affiliate-portal' ),
								'render_callback' => function( $row, $table_control_id ) {
									return Controls\Text_Control::create( "{$table_control_id}_visits", $row->visits );
								},
							),
							'unique_visits'   => array(
								'title'           => __( 'Unique Links', 'affiliatewp-affiliate-portal' ),
								'render_callback' => function( $row, $table_control_id ) {
									return Controls\Text_Control::create( "{$table_control_id}_unique_visits", $row->unique_visits );
								},
							),
							'referrals'       => array(
								'title'           => __( 'Converted', 'affiliatewp-affiliate-portal' ),
								'render_callback' => function( $row, $table_control_id ) {
									return Controls\Text_Control::create( "{$table_control_id}_referrals", $row->referrals );
								},
							),
							'conversion_rate' => array(
								'title'           => __( 'Conversion Rate', 'affiliatewp-affiliate-portal' ),
								'render_callback' => function( $row, $table_control_id ) {
									$conversion_rate = affwp_format_percentage( $row->conversion_rate, 2 );

									return Controls\Text_Control::create( "{$table_control_id}_conversion_rate", $conversion_rate );
								},
							),
						),
					),
					'header' => array(
						'text'  => __( 'Campaigns', 'affiliatewp-affiliate-portal' ),
						'level' => 3,
					),
					'data' => array(
						'schema'         => 'campaignsTable',
						'allowSorting'   => false,
					),
				),
			) ),
		);
	}

	/**
	 * Retrieves the report data for the stats cards.
	 *
	 * @since 1.0.0
	 *
	 * @param string $data_key     Data key to use for filtering data collections.
	 * @param int    $affiliate_id Current affiliate ID.
	 * @return mixed|string Report data.
	 */
	public function get_report_data( $data_key, $affiliate_id ) {
		$data = affwp_rest_get( '/affwp/v2/portal/reports', array(
			'affiliate_id' => $affiliate_id,
			'reports'      => array(
				array(
					'type'   => 'referralsCount',
					'name'   => 'unpaid_referrals',
					'status' => 'unpaid',
				),
				array(
					'type'   => 'referralsCount',
					'name'   => 'paid_referrals',
					'status' => 'paid',
				),
				array(
					'type' => 'visitsCount',
					'name' => 'visits',
				),
				array(
					'type' => 'conversionRate',
					'name' => 'conversion_rate',
				),
				array(
					'type'   => 'earnings',
					'name'   => 'unpaid_earnings',
					'status' => 'unpaid',
				),
				array(
					'type'   => 'earnings',
					'name'   => 'paid_earnings',
					'status' => 'paid',
				),
				array(
					'type' => 'commissionRate',
					'name' => 'commission_rate',
				),
			),
		) );

		return isset( $data[ $data_key ] ) ? $data[ $data_key ] : 0;
	}

}
