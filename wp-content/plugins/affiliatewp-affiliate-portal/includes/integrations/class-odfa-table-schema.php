<?php
/**
 * Integrations: Order Details for Affiliates Table Schema
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Integrations
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace AffiliateWP_Affiliate_Portal\Integrations;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Components\Portal;
use AffiliateWP_Affiliate_Portal\Core\Schemas\Table_Schema;
use AffiliateWP_Affiliate_Portal\Integrations\Order_Details_for_Affiliates as ODFA;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the columns, and other relevant information to render the Order Details for Affiliates table.
 *
 * @since 1.0.0
 */
class ODFA_Table_Schema extends Table_Schema {

	/**
	 * Number of standard detail fields that are allowed.
	 *
	 * @since 1.0.0
	 * @var   int
	 */
	private $allowed_fields_count;

	/**
	 * Sets up the defaults for the table schema.
	 *
	 * @since 1.0.0
	 *
	 * @param string $control_id Control ID.
	 * @param array  $args       Unused (overridden for this schema).
	 */
	public function __construct( $control_id, $args = array() ) {

		$this->allowed_fields_count = ( new ODFA )->get_allowed_fields( true );

		$args = array(
			'page_count_callback' => array( $this, 'table_page_count_callback' ),
			'data_callback'       => array( $this, 'table_data_callback' ),
			'schema'              => $this->get_allowed_columns(),
		);

		parent::__construct( $control_id, $args );
	}

	/**
	 * Callback to retrieve the page count for the ODFA table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments passed to the callback.
	 * @return int Number of orders to display in the table.
	 */
	public function table_page_count_callback( $args ) {
		$referral_args = affiliatewp_order_details_for_affiliates()->order_details->referral_args();

		$referral_args['affiliate_id'] = $args['affiliate_id'];
		$referral_args['number']       = 100;

		$referrals = affiliate_wp()->referrals->get_referrals( $referral_args );

		foreach ( $referrals as $index => $referral ) {
			if ( ! affiliatewp_order_details_for_affiliates()->order_details->exists( $referral ) ) {
				unset( $referrals[ $index ] );
			}
		}

		return absint( ceil( count( $referrals ) / $referral_args['number'] ) );
	}

	/**
	 * Callback to retrieve the ODFA table data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments passed to the callback.
	 * @return array ODFA table data.
	 */
	public function table_data_callback( $args ) {
		$referral_args = affiliatewp_order_details_for_affiliates()->order_details->referral_args();

		$data = array();

		if ( isset( $args['affiliate_id'] ) && isset( $args['number'] ) ) {
			$referral_args['affiliate_id'] = $args['affiliate_id'];
			$referral_args['number']       = $args['number'];

			$referrals = affiliate_wp()->referrals->get_referrals( $referral_args );

			foreach ( $referrals as $referral ) {
				if ( ! affiliatewp_order_details_for_affiliates()->order_details->exists( $referral ) ) {
					continue;
				}

				$data[] = ( new ODFA )->get_order_data( $referral, 'table', false );
			}
		}

		return $data;
	}

	/**
	 * Sets up the columns to supply to the schema.
	 *
	 * @since 1.0.0
	 *
	 * @param string $control_id Control ID.
	 * @param array  $args       Arguments passed to schema to override defaults.
	 */
	public function get_allowed_columns() {

		$table_fields = ( new ODFA )->get_allowed_table_fields_options();

		$count = 0;

		foreach ( $table_fields as $field => $title ) {
			if ( ! ( new ODFA )->is_field_allowed( $field, 'table' ) ) {
				continue;
			}

			$columns[ $field ] = array(
				'title'           => $title,
				'priority'        => 5 * ++$count,
				'render_callback' => function ( $row, $table_control_id ) use ( $field ) {
					$control_id = "{$table_control_id}_{$field}";

					if ( $this->allowed_fields_count > 0 ) {
						return new Controls\Link_Control( array(
							'id'   => $control_id,
							'atts' => array(
								'href' => ( new ODFA )->get_order_url( $row ),
							),
							'args' => array(
								'label' => $row[ $field ],
							),
						) );
					} else {
						return Controls\Text_Control::create( $control_id, $row[ $field ] );
					}
				},
			);
		}

		return $columns;
	}

}