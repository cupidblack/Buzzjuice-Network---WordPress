<?php
/**
 * Views: Home/Dashboard View
 *
 * @package   Core/Components
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Components\Views;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Components\Portal;
use AffiliateWP_Affiliate_Portal\Core\Schemas\Referrals_Table_Schema;
use AffiliateWP_Affiliate_Portal\Core\Interfaces\View;

#[\AllowDynamicProperties]

/**
 * Sets up the Home/Dashboard view.
 *
 * @since 1.0.0
 */
class Home_View implements View {

	/**
	 * Retrieves the view sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_sections() {
		return array(
			'home' => array(
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
	 * @return array[] Sections.
	 */
	public function get_controls() {
		return array(
			new Controls\Wrapper_Control( array(
				'id'      => 'wrapper',
				'view_id' => 'home',
				'section' => 'wrapper',
				'atts'    => array(
					'id' => 'affwp-affiliate-portal-home',
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'last_30_days_card_group',
				'view_id' => 'home',
				'section' => 'home',
				'args'    => array(
					'title' => __( 'Last 30 days', 'affiliatewp-affiliate-portal' ),
					'cards' => array(
						array(
							'title'    => __( 'Referrals', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'referrals',
							'data'     => array( $this, 'get_last_month_report_data' ),
							'compare'  => array( $this, 'get_last_month_compare_report_data' ),
							'link'     => function( $data_key, $affiliate_id ) {
								return Portal::get_page_url( 'referrals' );
							},
							'icon'    => 'currency-dollar',
						),
						array(
							'title'    => __( 'Visits', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'visits',
							'data'     => array( $this, 'get_last_month_report_data' ),
							'compare'  => array( $this, 'get_last_month_compare_report_data' ),
							'link'     => function( $data_key, $affiliate_id ) {
								return Portal::get_page_url( 'visits' );
							},
							'icon'    => 'cursor-click',
						),
						array(
							'title'    => __( 'Conversion Rate', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'conversion_rate',
							'data'     => array( $this, 'get_last_month_report_data' ),
							'compare'  => array( $this, 'get_last_month_compare_report_data' ),
							'format'   => 'percentage',
							'icon'     => 'scale',
						),
					),
				),
			) ),
			new Controls\Card_Group_Control( array(
				'id'      => 'all_time_card_group',
				'view_id' => 'home',
				'section' => 'home',
				'args'    => array(
					'columns' => 4,
					'title' => __( 'All-time', 'affiliatewp-affiliate-portal' ),
					'cards'   => array(
						array(
							'title'    => __( 'Referrals', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'referrals',
							'data'     => array( $this, 'get_all_time_report_data' ),
							'link'     => function( $data_key, $affiliate_id ) {
								return Portal::get_page_url( 'referrals' );
							},
							'icon'     => 'currency-dollar',
						),
						array(
							'title'    => __( 'Visits', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'visits',
							'data'     => array( $this, 'get_all_time_report_data' ),
							'link'     => function( $data_key, $affiliate_id ) {
								return Portal::get_page_url( 'visits' );
							},
							'icon'     => 'cursor-click',
						),
						array(
							'title'    => __( 'Conversion Rate', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'conversion_rate',
							'data'     => array( $this, 'get_all_time_report_data' ),
							'icon'     => 'scale',
						),
						array(
							'title'    => __( 'Unpaid Referrals', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'unpaid_referrals',
							'data'     => array( $this, 'get_all_time_report_data' ),
							'link'     => function( $data_key, $affiliate_id ) {
								return Portal::get_page_url( 'referrals' );
							},
							'icon'     => 'cursor-click',
						),
						array(
							'title'    => __( 'Paid Referrals', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'paid_referrals',
							'data'     => array( $this, 'get_all_time_report_data' ),
							'link'     => function( $data_key, $affiliate_id ) {
								return Portal::get_page_url( 'referrals' );
							},
							'icon'     => 'currency-dollar',
						),
						array(
							'title'    => __( 'Unpaid Earnings', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'unpaid_earnings',
							'data'     => array( $this, 'get_all_time_report_data' ),
							'icon'     => 'currency-dollar',
						),
						array(
							'title'    => __( 'Total Earnings', 'affiliatewp-affiliate-portal' ),
							'data_key' => 'paid_earnings',
							'data'     => array( $this, 'get_all_time_report_data' ),
							'icon'     => 'currency-dollar',
						),
					),
				),
			) ),
			new Controls\Table_Control( array(
				'id'      => 'referral-activity-table',
				'view_id' => 'home',
				'section' => 'home',
				'args'    => array(
					'schema' => new Referrals_Table_Schema( 'referral-activity-table' ),
					'header' => array(
						'text'  => __( 'Recent referral activity', 'affiliatewp-affiliate-portal' ),
						'level' => 3,
					),
					'data'   => array(
						'perPage'        => 5,
						'showPagination' => false,
						'allowSorting'   => false,
						'orderby'        => 'date',
					),
				),
			) ),
		);
	}

	/**
	 * Retrieves the report data for the last month for the given key and affiliate.
	 *
	 * @since 1.0.0
	 *
	 * @param string $data_key     Data key to use for filtering data collections.
	 * @param int    $affiliate_id Current affiliate ID.
	 * @return mixed|string Report data.
	 */
	public function get_last_month_report_data( $data_key, $affiliate_id ) {
		$data = affwp_rest_get( '/affwp/v2/portal/reports', array(
			'affiliate_id' 	=> $affiliate_id,
			'defaults' => array(
				'date' => array(
					'start' => 'today - 30 days',
					'end'   => 'Today',
				),
			),
			'reports'  => array(
				array(
					'type' => 'referralsCount',
					'name' => 'referrals',
					'status' => array( 'paid', 'unpaid' ),
				),
				array(
					'type' => 'visitsCount',
					'name' => 'visits',
				),
				array(
					'type' => 'conversionRate',
					'name' => 'conversion_rate',
					'formatted' => false,
				),
			),
		) );

		return isset( $data[ $data_key ] ) ? $data[ $data_key ] : 0;
	}

	/**
	 * Retrieves the comparison report data for 30-60 days ago for the given data key and affiliate.
	 *
	 * @since 1.0.0
	 *
	 * @param string $data_key     Data key to use for filtering data collections.
	 * @param int    $affiliate_id Current affiliate ID.
	 * @return mixed|string Comparison report data.
	 */
	public function get_last_month_compare_report_data( $data_key, $affiliate_id ) {
		$data = affwp_rest_get( '/affwp/v2/portal/reports', array(
			'affiliate_id' => $affiliate_id,
			'defaults'     => array(
				'date' => array(
					'start' => 'today - 60 days',
					'end'   => 'today - 30 days',
				),
			),
			'reports'  => array(
				array(
					'type' => 'referralsCount',
					'name' => 'referrals',
					'status' => array( 'paid', 'unpaid' ),
				),
				array(
					'type' => 'visitsCount',
					'name' => 'visits',
				),
				array(
					'type' => 'conversionRate',
					'name' => 'conversion_rate',
					'formatted' => false,
				),
			),
		) );

		return isset( $data[ $data_key ] ) ? $data[ $data_key ] : 0;
	}

	/**
	 * Retrieves the all-time report data based on the given key and affiliate.
	 *
	 * @since 1.0.0
	 *
	 * @param string $data_key     Data key to use for filtering data collections.
	 * @param int    $affiliate_id Current affiliate ID.
	 * @return mixed|string All-time time report data.
	 */
	public function get_all_time_report_data( $data_key, $affiliate_id ) {
		$data = affwp_rest_get( '/affwp/v2/portal/reports', array(
			'affiliate_id' => $affiliate_id,
			'reports'  => array(
				array(
					'type'   => 'referralsCount',
					'name'   => 'referrals',
					'status' => array( 'paid', 'unpaid' ),
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
					'type'   => 'referralsCount',
					'name'   => 'unpaid_referrals',
					'status' => array( 'unpaid' ),
				),
				array(
					'type'   => 'referralsCount',
					'name'   => 'paid_referrals',
					'status' => array( 'paid' ),
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
			),
		) );

		return isset( $data[ $data_key ] ) ? $data[ $data_key ] : 0;
	}
}
