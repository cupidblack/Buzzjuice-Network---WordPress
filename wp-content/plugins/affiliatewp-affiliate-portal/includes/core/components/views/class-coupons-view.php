<?php
/**
 * Views: Coupons View
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
 * Sets up the Coupons view.
 *
 * @since 1.0.0
 */
class Coupons_View implements View {

	/**
	 * Retrieves the view sections.
	 *
	 * @since 1.0.0
	 *
	 * @return array[] Sections.
	 */
	public function get_sections() {
		return array(
			'coupons' => array(
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
				'view_id' => 'coupons',
				'section' => 'wrapper',
				'atts'    => array(
					'id' => 'affwp-affiliate-portal-coupons',
				),
			) ),
			new Controls\Table_Control( array(
				'id'      => 'coupons-table',
				'view_id' => 'coupons',
				'section' => 'coupons',
				'args'    => array(
					'data'                 => array(
						'allowSorting' => false,
					),
					'allow_column_replace' => true,
					'schema'               => array(
						'name'          => 'coupons-table',
						'page_count_callback' => function ( $args ) {

							$dynamic_coupons = affwp_get_dynamic_affiliate_coupons( $args['affiliate_id'] );
							$manual_coupons  = affwp_get_manual_affiliate_coupons( $args['affiliate_id'] );
							$all_coupons     = array_merge( $dynamic_coupons, $manual_coupons );
							$number          = isset( $args['number'] ) ? $args['number'] : 20;
							$count           = count( $all_coupons );


							return absint( ceil( $count / $number ) );
						},
						'data_callback'       => function ( $args ) {

							$coupon_obj  = array();
							$all_coupons = affwp_get_affiliate_coupons( $args['affiliate_id'] );
							$processed_coupons = array();

							foreach ( $all_coupons as $type => $coupons ) {
								foreach ( $coupons as $id => $coupon ) {
									$processed_coupons[] = array(
										'id'          => $id,
										'type'        => $type,
										'code'        => $coupon['code'],
										'amount'      => $coupon['amount'],
										'integration' => $coupon['integration']
									);
								}
							}

							if ( isset( $args['number'] ) && isset( $args['offset'] ) ) {
								// Determine max index for the coupon object given set number per page, offset, and # of coupons.
								$max_page_index = $args['number'] + $args['offset'];
								$coupon_count   = count( $processed_coupons );
								$max_index      = $max_page_index < $coupon_count ? $max_page_index : $coupon_count;

								// Only return coupons for this page of the table.
								for ($i = $args['offset']; $i < $max_index; $i++ ) {
									$coupon_obj[] = (object) $processed_coupons[ $i ];
								}
							}

							return $coupon_obj;
						},
						'schema'              => array(
							'coupon_code' => array(
								'title'           => __( 'Coupon Code', 'affiliatewp-affiliate-portal' ),
								'render_callback' => function ( $row, $table_control_id ) {
									return Controls\Text_Control::create( "{$table_control_id}_coupon_code", $row->code );
								},
							),
							'coupon_amount' => array(
								'title'           => __( 'Amount', 'affiliatewp-affiliate-portal' ),
								'render_callback' => function ( $row, $table_control_id ) {
									return Controls\Text_Control::create( "{$table_control_id}_coupon_amount", $row->amount );
								},
							),
						),
					),
				),
			) ),
		);
	}
}
