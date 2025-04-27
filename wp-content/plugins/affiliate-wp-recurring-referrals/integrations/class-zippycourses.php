<?php

class Affiliate_WP_Recurring_ZippyCourses extends Affiliate_WP_Recurring_Base {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function init() {
		$this->context = 'zippycourses';
		add_action( 'zippy_event_save_transaction', array( $this, 'record_referral_on_payment' ), 10 );
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );
	}

	/**
	 * Insert referrals on Zippy Courses subscription payments.
	 *
	 * object  $event   Zippy Courses Transaction Notification Event.
	 * @since  1.6
	 */
	public function record_referral_on_payment( Zippy_Event $event ) {

		if ( ! isset ( $event->transaction->order->id ) ) {
			return; // Order details hasn't been added to the transaction yet.
		}

		// Bail if this isn't a subscription transaction.
		if ( ! $event->transaction->recurring ) {
			$msg = 'Recurring Referrals: Zippy Courses transaction is not a recurring subscription. No referral recorded.';
			affiliate_wp()->utils->log( $msg );
			return false;
		}

		$order_id       = $event->transaction->order->id;
		$transaction_id = $event->transaction->id;

		$parent_referral = affiliate_wp()->referrals->get_by( 'reference', $order_id, $this->context );

		if ( empty( $parent_referral ) ) {
			$msg = 'Recurring Referrals: Zippy Courses - Parent referral not located. No recurring referral generated.';
			affiliate_wp()->utils->log( $msg );
			return false;
		}

		$zippy = Zippy::instance();

		$order = $zippy->make( 'order' );
		$order->build( $order_id );

		$transactions = $order->transactions->fetchByMeta( 'order_id', $order_id );

		// Bail if this is the first transaction for the order.
		if ( $transactions->count() < 2 ) {
			$msg = 'Recurring Referrals: Referral not created because this is the first subscription payment.';
			affiliate_wp()->utils->log( $msg );
			return; // This is the first payment recorded right after a subscription, skip it
		}

		$transaction = $zippy->make('transaction');
		$transaction->build( $transaction_id );
		$transaction_status = $transaction->getStatus();

		// Bail if the transaction status is not complete.
		if ( 'complete' != $transaction_status ) {
			$msg = 'Recurring Referrals: Zippy Courses transaction status is not yet complete. No referral recorded yet.';
			affiliate_wp()->utils->log( $msg );
			return false;
		}

		$amount       = $event->transaction->total;
		$reference    = $transaction_id;
		$affiliate_id = $parent_referral->affiliate_id;

		$referral_amount = $this->calc_referral_amount( $amount, $reference, $parent_referral->referral_id, '', $affiliate_id );

		/**
		 * Fires when the amount of a recurring referral is calculated.
		 *
		 * @param float $referral_amount  The referral amount.
		 * @param int   $affiliate_id     The affiliate ID.
		 * @param float $amount           The full transaction amount.
		 *
		 * @since 1.5
		 */
		$referral_amount = (string) apply_filters( 'affwp_recurring_calc_referral_amount', $referral_amount, $affiliate_id, $amount );

		$args = array(
			'reference'    => $reference,
			'affiliate_id' => $affiliate_id,
			'description'  => sprintf( __( 'Subscription payment for order %d', 'affiliate-wp-recurring-referrals' ), $order_id ),
			'amount'       => $referral_amount,
			'custom'       => array(
				'parent' => $parent_referral->referral_id
			),
			'parent_id'    => $parent_referral->referral_id
		);

		$referral_id = $this->insert_referral( $args );

		if ( $referral_id ) {

			$this->complete_referral( $referral_id );

			/**
			 * Fires when a recurring referral is successfully generated.
			 *
			 * @param int $referral_id The generated referral ID.
			 *
			 * @since 1.6
			 */
			do_action( 'affwprr_zippycourses_insert_referral', $referral_id );

			$msg = 'Recurring Referrals: affwprr_zippycourses_insert_referral action fired successfully.';
			affiliate_wp()->utils->log( $msg );

		}
	}

	/**
	 * Sets up the reference link in the Referrals list table.
	 *
	 * @param  int                $link      Zippy Courses transaction array.
	 * @param  object             $referral  Referral object.
	 * @return mixed bool|string             Reference link.
	 * @since  1.6
	 */
	public function reference_link( $link, $referral ) {

		// Bail if the referral context is not zippycourses.
		if ( empty( $referral->context ) || 'zippycourses' != $referral->context ) {
			return $link;
		}

		// The transaction ID is used as the referral reference.
		$post_ID         = get_post( $referral->reference );
		$transaction_id  = $post_ID ? $referral->reference : 0;

		if ( ! $transaction_id ) {
			$msg = 'Recurring Referrals: No transaction post ID could be located for referral ' . $post_ID;
			affiliate_wp()->utils->log( $msg );
		}

		$url  = get_edit_post_link( $transaction_id, '' );
		$link = '<a href="' . esc_url( $url ) . '">' . $transaction_id . '</a>';

		return $link;
	}

}
new Affiliate_WP_Recurring_ZippyCourses;
