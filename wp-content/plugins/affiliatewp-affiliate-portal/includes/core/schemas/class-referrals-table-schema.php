<?php
/**
 * Schemas: Referrals Table Schema
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Core/Schemas
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Core\Schemas;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the columns, and other relevant information to render a referral's table.
 *
 * @since 1.0.0
 *
 * @see Table_Schema
 */
class Referrals_Table_Schema extends Table_Schema {

	/**
	 * Referrals_Table_Schema constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $control_id Control ID.
	 * @param array  $args       Unused (overridden for this schema).
	 */
	public function __construct( $control_id, $args = array() ) {

		$args = array(
			'page_count_callback' => function ( $args ) {
				$number = isset( $args['number'] ) ? $args['number'] : 20;
				return absint( ceil( affwp_count_referrals( $args['affiliate_id'] ) / $number ) );
			},
			'data_callback'       => function ( $args ) {
				return affiliate_wp()->referrals->get_referrals( $args );
			},
			'schema'              => array(
				'reference'   => array(
					'title'           => __( 'Reference', 'affiliatewp-affiliate-portal' ),
					'priority'        => 5,
					'render_callback' => function ( \AffWP\Referral $row, $table_control_id ) {
						return Controls\Text_Control::create( "{$table_control_id}_reference", $row->reference );
					},
				),
				'amount'      => array(
					'title'           => __( 'Amount', 'affiliatewp-affiliate-portal' ),
					'priority'        => 10,
					'render_callback' => function ( \AffWP\Referral $row, $table_control_id ) {
						$amount = affwp_currency_filter( $row->amount );

						return Controls\Text_Control::create( "{$table_control_id}_amount", $amount );
					},
				),
				'description' => array(
					'title'           => __( 'Description', 'affiliatewp-affiliate-portal' ),
					'priority'        => 15,
					'render_callback' => function( \AffWP\Referral $row, $table_control_id ) {
						return new Controls\Text_Control( array(
							'id' => "{$table_control_id}_description",
							'args' => array(
								'text' => $row->description,
							),
							'atts' => array(
								'class' => array( 'whitespace-normal' ),
							)
						) );
					},
				),
				'status'      => array(
					'title'           => __( 'Status', 'affiliatewp-affiliate-portal' ),
					'priority'        => 20,
					'render_callback' => function ( \AffWP\Referral $row, $table_control_id ) {
						switch ( $row->status ) {
							case 'paid':
								$type = 'approved';
								break;
							case 'rejected':
								$type = 'rejected';
								break;
							default:
								$type = 'pending';
								break;
						}

						return new Controls\Status_Control( array(
							'id'   => "{$table_control_id}_status",
							'args' => array(
								'type'  => $type,
								'label' => affwp_get_referral_status_label( $row->status ),
							),
						) );
					},
				),
				'date'        => array(
					'title'           => __( 'Date', 'affiliatewp-affiliate-portal' ),
					'priority'        => 25,
					'render_callback' => function( \AffWP\Referral $row, $table_control_id ) {
						return Controls\Text_Control::create( "{$table_control_id}_date", $row->date_i18n( 'datetime' ) );
					},
				),
			),
		);

		parent::__construct( $control_id, $args );
	}

}
