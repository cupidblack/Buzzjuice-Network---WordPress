<?php

class Affiliate_WP_Recurring_LifterLMS extends Affiliate_WP_Recurring_Base {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function init() {

		$this->context = 'lifterlms';

		add_action( 'lifterlms_transaction_status_succeeded', array( $this, 'record_referral_on_renewal' ), 10, 1 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );
	}

	/**
	 * Insert referrals on subscription payments
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function record_referral_on_renewal( $txn ) {

		$order = $txn->get_order();

		if ( ! $order ) {
			return false;
		}

		if ( $order->is_legacy() ) {
			return false;
		}

		if ( ! $order->is_recurring() ) {
			return false;
		}

		$transactions = $order->get_transactions();

		if ( $transactions['count'] < 2 ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because this is the first subscription payment.' );
			return;
		}

		$reference        = $txn->get( 'id' );
		$parent_reference = $order->get( 'id' );

		$parent_referral = affiliate_wp()->referrals->get_by( 'reference', $parent_reference, $this->context );

		if ( ! $parent_referral || ! is_object( $parent_referral ) || 'rejected' == $parent_referral->status ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: No referral found or referral is rejected. Subscription: ' . var_export( $txn, true ) );
			return false; // This signup wasn't referred or is the very first payment of a referred subscription
		}

		$amount       = $txn->get( 'amount' );
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
			'description'  => sprintf( __( 'Subscription payment for %s', 'affiliate-wp-recurring-referrals' ), $parent_reference ),
			'amount'       => $referral_amount,
			'custom'       => $parent_reference,
			'parent_id'    => $parent_referral->referral_id
		);

		$referral_id = $this->insert_referral( $args );

		if ( $referral_id ) {

			$this->complete_referral( $referral_id );

		}

	}

	/**
	 * Builds the reference link for the referrals table.
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function reference_link( $link = '', $referral ) {

		if ( empty( $referral->context ) || 'lifterlms' != $referral->context ) {

			return $link;

		}

		if ( ! empty( $referral->custom ) ) {

			$url  = get_edit_post_link( $referral->custom );

			$link = '<a href="' . esc_url( $url ) . '">' . $referral->reference . '</a>';

			return $link;

		}

		return $link;

	}

}
new Affiliate_WP_Recurring_LifterLMS;