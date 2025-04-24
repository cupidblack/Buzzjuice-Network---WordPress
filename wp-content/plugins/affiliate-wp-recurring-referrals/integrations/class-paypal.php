<?php

class Affiliate_WP_Recurring_PayPal extends Affiliate_WP_Recurring_Base {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function init() {

		$this->context = 'paypal';

		add_action( 'init', array( $this, 'record_referral_on_ipn' ) );
	}

	/**
	 * Process PayPal IPN requests in order to create recurring referrals referrals.
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function record_referral_on_ipn() {

		if ( empty( $_GET['affwp-listener'] ) || 'paypal' !== strtolower( $_GET['affwp-listener'] ) ) {
			return;
		}

		$ipn_data = $_POST;

		if ( ! is_array( $ipn_data ) ) {
			wp_parse_str( $ipn_data, $ipn_data );
		}

		$verified = $this->verify_ipn( $ipn_data );

		if ( ! $verified ) {
			die( 'Recurring Referrals: IPN verification failed' );
		}

		$to_process = array(
			'subscr_payment',
			'recurring_payment'
		);

		if ( ! empty( $ipn_data['txn_type'] ) && ! in_array( $ipn_data['txn_type'], $to_process ) ) {

			affiliate_wp()->utils->log( 'Recurring Referrals: IPN not processed because invalid transaction type: ' . $ipn_data['txn_type'] );

			return;
		}

		if ( empty( $ipn_data['mc_gross'] ) ) {

			affiliate_wp()->utils->log( 'Recurring Referrals: IPN not processed because mc_gross was empty' );

			return;
		}

		if ( empty( $ipn_data['custom'] ) ) {

			affiliate_wp()->utils->log( 'Recurring Referrals: IPN not processed because custom was empty' );

			return;
		}

		if ( 'completed' !== strtolower( $ipn_data['payment_status'] ) ) {

			affiliate_wp()->utils->log( 'Recurring Referrals: Payment status in IPN data not Completed' );

			return;
		}

		$custom             = explode( '|', $ipn_data['custom'] );
		$parent_referral_id = $custom[2];
		$reference          = $ipn_data['txn_id'];

		$parent_referral = affiliate_wp()->referrals->get_by( 'referral_id', $parent_referral_id, $this->context );

		if ( ! $parent_referral || ! is_object( $parent_referral ) || 'rejected' == $parent_referral->status ) {

			affiliate_wp()->utils->log( 'Recurring Referrals: No referral found or referral is rejected. Transaction ID: ' . $reference );

			return false; // This signup wasn't referred or is the very first payment of a referred subscription
		}

		$amount       = sanitize_text_field( $ipn_data['mc_gross'] );
		$affiliate_id = $parent_referral->affiliate_id;

		$referral_amount = $this->calc_referral_amount( $amount, $reference, $parent_referral->referral_id, '', $affiliate_id );

		/**
		 * Fires when the amount of a recurring referral is calculated.
		 *
		 * @param float $referral_amount  The referral amount.
		 * @param int   $affiliate_id     The affiliate ID.
		 * @param float $amount           The full transaction amount.
		 *
		 * @since 1.7
		 */
		$referral_amount = (string) apply_filters( 'affwp_recurring_calc_referral_amount', $referral_amount, $affiliate_id, $amount );

		$args = array(
			'reference'    => $reference,
			'affiliate_id' => $affiliate_id,
			'description'  => sprintf( __( 'Subscription payment for %d', 'affiliate-wp-recurring-referrals' ), $parent_referral->referral_id ),
			'amount'       => $referral_amount,
			'custom'       => $parent_referral->referral_id,
			'parent_id'    => $parent_referral->referral_id
		);

		$referral_id = $this->insert_referral( $args );

		if ( $referral_id ) {

			$completed = $this->complete_referral( $referral_id );

			if ( $completed ) {

				affiliate_wp()->utils->log( sprintf( 'Recurring Referrals: Recurring referral #%d completed successfully during process_ipn()', $referral_id ) );

				return;

			}

		}

	}

	/**
	 * Verify IPN from PayPal.
	 *
	 * @access  public
	 * @since   1.7
	 * @return  bool True|false
	 */
	private function verify_ipn( $post_data ) {

		$verified = false;
		$endpoint = array_key_exists( 'test_ipn', $post_data ) ? 'https://www.sandbox.paypal.com/cgi-bin/webscr' : 'https://www.paypal.com/cgi-bin/webscr';
		$args     = wp_unslash( array_merge( array( 'cmd' => '_notify-validate' ), $post_data ) );

		$request  = wp_remote_post( $endpoint, array( 'timeout' => 45, 'sslverify' => false, 'httpversion' => '1.1', 'body' => $args ) );
		$body     = wp_remote_retrieve_body( $request );
		$code     = wp_remote_retrieve_response_code( $request );
		$message  = wp_remote_retrieve_response_message( $request );

		if ( ! is_wp_error( $request ) && 200 === (int) $code && 'OK' == $message ) {

			if ( 'VERIFIED' == strtoupper( $body ) ) {

				$verified = true;

				affiliate_wp()->utils->log( 'Recurring Referrals: IPN successfully verified' );

			} else {

				affiliate_wp()->utils->log( 'Recurring Referrals: IPN response came back as INVALID' );

			}

		} else {

			affiliate_wp()->utils->log( 'Recurring Referrals: IPN verification request failed' );
			affiliate_wp()->utils->log( 'Recurring Referrals: Request: ' . print_r( $request, true ) );

		}

		return $verified;
	}

}
new Affiliate_WP_Recurring_PayPal;