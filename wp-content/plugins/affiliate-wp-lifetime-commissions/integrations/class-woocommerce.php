<?php

class Affiliate_WP_Lifetime_Commissions_WooCommerce extends Affiliate_WP_Lifetime_Commissions_Base {
	
	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function init() {
		$this->context = 'woocommerce';

		add_filter( 'affwp_woocommerce_add_pending_referral_amount', array( $this, 'calculate_lifetime_referral_amount' ), 10, 3 );
	}

	/**
	 * Retrieve the email address of a customer from the WC_Order
	 *
	 * @access  public
	 * @since   2.0
	 * @return  string
	 */
	public function get_email( $order_id = 0 ) {
		
		$email = '';
		$order = wc_get_order( $order_id );

		if( $order ) {

			if ( version_compare( WC()->version, '3.0.0', '>=' ) ) {
				$email = $order->get_billing_email();
			} else {
				$email = $order->billing_email;
			}

		}

		return $email;
	}

	/**
	 * Calculate the lifetime referral amount.
	 *
	 * @since 1.4.1
	 *
	 * @param float $amount       Referral amount.
	 * @param int   $order_id     WooCommerce Order ID.
	 * @param int   $affiliate_id Affiliate ID.
	 *
	 * @return float|int Lifetime referral amount.
	 */
	public function calculate_lifetime_referral_amount( $amount, $order_id, $affiliate_id ) {

		if ( empty( $this->customer ) ) {
			return $amount;
		}

		if ( ! $this->can_receive_lifetime_commissions( $affiliate_id )  ) {
			return $amount;
		}

		if ( ! $this->is_lifetime_customer( $order_id ) ) {
			return $amount;
		}

		$order = wc_get_order( $order_id );

		if ( ! $order instanceof \WC_Order ) {
			return $amount;
		}

		$lifetime_rate = $this->get_lifetime_rate( $affiliate_id );

		if ( is_numeric( $lifetime_rate ) ) {

			// per-affiliate lifetime referral rate type.
			$lifetime_affiliate_rate_type = $this->get_affiliate_lifetime_referral_rate_type( $affiliate_id );

			// lifetime referral rate type from Affiliates -> Settings -> Integrations.
			$lifetime_referral_rate_type = affiliate_wp()->settings->get( 'lifetime_commissions_lifetime_referral_rate_type' );

			if ( $lifetime_affiliate_rate_type ) {
				$type = $lifetime_affiliate_rate_type;
			} elseif ( $lifetime_referral_rate_type ) {
				$type = $lifetime_referral_rate_type;
			} else {
				$type = affwp_get_affiliate_rate_type( $affiliate_id );
			}

			$decimals = affwp_get_decimal_count();

			if ( affwp_is_per_order_rate( $affiliate_id ) ) {

				if ( 'percentage' === $type ) {
					$referral_amount = $lifetime_rate * $order->get_total();
					$referral_amount = round( $referral_amount, $decimals );
				} else {
					$referral_amount = $lifetime_rate;
				}
			} else {

				$items = $order->get_items();

				$cart_shipping = $order->get_total_shipping();

				if ( ! affiliate_wp()->settings->get( 'exclude_tax' ) ) {
					$cart_shipping += $order->get_shipping_tax();
				}

				// Calculate the referral amount based on product prices.
				$referral_amount = 0.00;

				foreach ( $items as $product ) {

					if ( get_post_meta( $product['product_id'], '_affwp_' . $this->context . '_referrals_disabled', true ) ) {
						continue; // Referrals are disabled on this product.
					}

					if ( ! empty( $product['variation_id'] ) && get_post_meta( $product['variation_id'], '_affwp_' . $this->context . '_referrals_disabled', true ) ) {
						continue; // Referrals are disabled on this variation.
					}

					// The order discount has to be divided across the items.
					$product_total = $product['line_total'];
					$shipping      = 0;

					if ( $cart_shipping > 0 && ! affiliate_wp()->settings->get( 'exclude_shipping' ) ) {
						$shipping      = $cart_shipping / count( $items );
						$product_total += $shipping;
					}

					if ( ! affiliate_wp()->settings->get( 'exclude_tax' ) ) {
						$product_total += $product['line_tax'];
					}

					if ( $product_total <= 0 && 'flat' !== $type ) {
						continue;
					}

					if ( 'percentage' === $type ) {
						$earnings        = $lifetime_rate * $product_total;
						$referral_amount += round( $earnings, $decimals );
					} else {
						$referral_amount += $lifetime_rate;
					}
				}
			}

			$amount = $referral_amount;
		}

		return $amount;
	}

}

if ( class_exists( 'WooCommerce' ) ) {
	new Affiliate_WP_Lifetime_Commissions_WooCommerce;
}
