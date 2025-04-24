<?php
/**
 * Schemas: Referrals Chart Schema
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core/Schemas
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Schemas;

use AffiliateWP_Affiliate_Portal\Utilities\Dataset_Parser;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Referrals chart schema class.
 *
 * @since 1.0.0
 *
 * @see Chart_Schema
 */
class Referrals_Chart_Schema extends Chart_Schema {

	/**
	 * Referrals_Chart_Schema constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $control_id Control ID.
	 * @param array  $args       Unused (overridden for this schema).
	 */
	public function __construct( $control_id, $args = array() ) {
		$args = array(
			'x_label_key' => 'formatted_date',
			'schema'      => array(
				'unpaid_earnings'   => array(
					'title'           => __( 'Unpaid Referral Earnings', 'affiliatewp-affiliate-portal' ),
					'priority'        => 5,
					'render_callback' => array( $this, 'render_callback' ),
					'data_callback'   => function () {
						return $this->referrals_data( array( 'status' => 'unpaid' ) );
					},
					'color'           => 'rgb(237,194,64)',
				),
				'pending_earnings'  => array(
					'title'           => __( 'Pending Referral Earnings', 'affiliatewp-affiliate-portal' ),
					'priority'        => 10,
					'render_callback' => array( $this, 'render_callback' ),
					'data_callback'   => function () {
						return $this->referrals_data( array( 'status' => 'pending' ) );
					},
					'color'           => 'rgb(175,216,248)',
				),
				'rejected_earnings' => array(
					'title'           => __( 'Rejected Referral Earnings', 'affiliatewp-affiliate-portal' ),
					'priority'        => 15,
					'render_callback' => array( $this, 'render_callback' ),
					'data_callback'   => function () {
						return $this->referrals_data( array( 'status' => 'rejected' ) );
					},
					'color'           => 'rgb(203,75,75)',
				),
				'paid_earnings'     => array(
					'title'           => __( 'Paid Referral Earnings', 'affiliatewp-affiliate-portal' ),
					'priority'        => 15,
					'render_callback' => array( $this, 'render_callback' ),
					'data_callback'   => function () {
						return $this->referrals_data( array( 'status' => 'paid' ) );
					},
					'color'           => 'rgb(77,167,77)',
				),

			),
		);

		parent::__construct( $control_id, $args );
	}

	/**
	 * Default render callback.
	 *
	 * @since 1.0.0
	 *
	 * @param object $row Row object.
	 * @return array Chart data.
	 */
	public function render_callback( $row ) {
		return $row->data;
	}

	/**
	 * Referrals Data callback.
	 *
	 * Fetches referrals data for the provided time period.
	 *
	 * @param $args
	 *
	 * @return array Array of dates and their respective referral totals.
	 */
	public function referrals_data( $args ) {
		$defaults = array(
			'orderby'      => 'date',
			'order'        => 'ASC',
			'fields'       => 'date',
			'sum_fields'   => 'amount',
			'groupby'      => 'formatted_date',
			'number'       => -1,
			'affiliate_id' => affwp_get_affiliate_id(),
		);

		$args = wp_parse_args( $args, $defaults );

		$dataset_parser = new Dataset_Parser( $args );

		$args['date']        = $dataset_parser->date_query;
		$args['date_format'] = $dataset_parser->mysql_date_format;

		$referrals = affiliate_wp()->referrals->get_referrals( $args );

		// Clean up the data before backfilling
		foreach ( $referrals as $key => $referral ) {
			$referral->data = $referral->amount_sum;
			unset( $referral->amount_sum );
			$referrals[ $key ] = $referral;
		}

		return $dataset_parser->backfill_data( $referrals );
	}

}