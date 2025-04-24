<?php
/**
 * Integrations: Advanced Coupons addon
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Integrations
 * @copyright   Copyright (c) 2024, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.0
 */
namespace AffiliateWP_Affiliate_Portal\Integrations;

use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Interfaces;
use ACFWF\Models\Objects\Advanced_Coupon;

/**
 * Class for integrating the Advanced Coupons addon.
 *
 * @since 1.3.0
 */
class Advanced_Coupons implements Interfaces\Integration {

	/**
	 * @inheritDoc
	 */
	public function init() {
		add_action( 'affwp_portal_controls_registry_init', array( $this, 'add_coupon_url_to_code_column' ), 9 );
	}

	/**
	 * Retrieves the valid coupon URL for the coupon if it exists.
	 *
	 * @since 1.3.0
	 *
	 * @param array $coupon_details Coupon details.
	 * @return string|false Coupon URL or false if not valid.
	 */
	public function get_valid_coupon_url( $coupon_details ) {
		// Get coupon.
		$coupon = new Advanced_Coupon( $coupon_details->id );

		// If the coupon URL is not valid, return false.
		if ( ! $coupon->get_id() || ! $coupon->is_coupon_url_valid() ) {
			return false;
		}

		// Return the coupon URL.
		return $coupon->get_coupon_url();
	}

	/**
	 * Sets up the coupon URL control. Otherwise, defaults to the coupon code.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $coupon_details Coupon details.
	 * @param string $parent_id      Parent ID.
	 * @param string $coupon_url     Coupon URL.
	 * @return Controls\Base_Control Either a text control or a div with copy control.
	 */
	public function maybe_create_coupon_url_control( $coupon_details, $parent_id, $coupon_url = '' ) {

		// Bail if not a manual WooCommerce coupon.
		if ( 'woocommerce' !== $coupon_details->integration || 'manual' !== $coupon_details->type ) {
			return Controls\Text_Control::create( "{$parent_id}-coupon-code", $coupon_details->code );
		}

		// If coupon URL is not set, attempt to get the valid coupon URL.
		if ( '' === $coupon_url ) {
			$coupon_url = $this->get_valid_coupon_url( $coupon_details );
		}

		// If the coupon URL is not valid, return the coupon code.
		if ( false === $coupon_url ) {
			return Controls\Text_Control::create( "{$parent_id}-coupon-code", $coupon_details->code );
		}

		// Return the coupon URL with the the affiliate's params for tracking that can be copied.
		return new Controls\Div_With_Copy_Control( array(
			'id'      => "{$parent_id}-coupon-url-{$coupon_details->id}",
			'args'    => array(
				'label'       => $coupon_details->code,
				// Prevent duplicate code when displayed by another parent id. Like Vanity Coupon Codes.
				'label_class' => 'coupons-table' === $parent_id ? "" : 'hidden',
				'desc'        => __( 'Coupon URL' ),
				'desc_class'  => array( 'mt-4' ),
				'content'     => $coupon_url,
			),
		) );
	}

	/**
	 * Adds the coupon url in the Affiliate Portal coupons table.
	 * This registers a replacement coupon code column.
	 *
	 * @param \AffiliateWP_Affiliate_Portal\Core\Controls_Registry $registry
	 */
	public function add_coupon_url_to_code_column( $registry ) {

		$registry->add_control( new Controls\Table_Column_Control( array(
			'id'     => 'affwp-coupon-url',
			'parent' => 'coupons-table',
			'args'   => array(
				'replaces_column' => 'coupon_code',
				'title'           => 'Coupon Code',
				'render_callback' => function( $row, $table_control_id ) {
					return $this->maybe_create_coupon_url_control( $row, $table_control_id );
				}
			)
		) ) );
	}
}
