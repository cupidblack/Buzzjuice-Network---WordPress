<?php

use SimplePay\Core\Payments\Stripe_API;

class Affiliate_WP_Recurring_Stripe extends Affiliate_WP_Recurring_Base {

	/**
	 * Get things started.
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function init() {

		$this->context = 'stripe';

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_string' ), 11, 2 );

		// Process Stripe webhooks on init.
		add_action( 'init', array( $this, 'process_webhooks' ) );

		add_action( 'simpay_webhook_invoice_payment_succeeded', array( $this, 'record_referral_on_payment'), 10, 3 );
	}

	/**
	 * Retrieves the Stripe API secret key from WP Simple Pay.
	 *
	 * @since unknown
	 *
	 * @return string|bool Returns the Stripe API secret key, otherwise false.
	 */
	public function get_stripe_secret_key() {

		$key       = simpay_get_secret_key();
		$test_mode = simpay_is_test_mode();

		/**
		 * Gets the WP Simple Pay Stripe API keys.
		 *
		 * @param string $key        The API key
		 * @param bool   $test_mode  Whether or not Stripe test mode is active within WP Simple Pay.
		 *                           Returns true if active, otherwise false.
		 * @since 1.6
		 */
		$key = apply_filters( 'affwp_rr_stripe_secret_key', $key, $test_mode );

		return $key;
	}

	/**
	 * Processes incoming Stripe webhook events.
	 *
	 * @return void
	 * @since  1.6
	 * @since 1.9.1 Recurring referrals is now created using the simpay_webhook_invoice_payment_succeeded hook.
	 *              Sending a response code for customers that still have the AffiliateWP webhook URL set on their Stripe dashboard.
	 */
	public function process_webhooks() {

		if ( 'stripe' !== strtolower( filter_input( INPUT_GET, 'affwp-listener', FILTER_SANITIZE_STRING ) ) ) {
			return;
		}

		status_header( 200 );

		die( '1' );
	}

	/**
	 * Insert referrals on subscription payments.
	 *
	 * @since 1.9.1
	 *
	 * @param \SimplePay\Vendor\Stripe\Event        $event        Stripe Event object.
	 * @param \SimplePay\Vendor\Stripe\Invoice      $invoice      Stripe Invoice object.
	 * @param \SimplePay\Vendor\Stripe\Subscription $subscription Stripe Subscription object.
	 * @return void
	 */
	public function record_referral_on_payment( $event, $invoice, $subscription ) {

		if ( 'invoice.payment_succeeded' !== $event->type ) {
			return;
		}

		$referral_exist = affiliate_wp()->referrals->get_by( 'reference', $invoice->id, $this->context );

		if ( $referral_exist ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Referral already recorded for this invoice payment' );

			return;
		}

		if ( empty( $subscription ) ) {
			return;
		}

		$invoices = Stripe_API::request(
			'Invoice',
			'all',
			array(
				'limit'        => 2,
				'subscription' => $subscription->id,
			),
			array(
				'api_key' => $this->get_stripe_secret_key(),
			)
		);

		// Look to see how many invoices we have for the subscription associated with this invoice, if 1, it's the first invoice.
		if ( count( $invoices->data ) === 1 ) {
			// This is the first signup payment so do nothing
			affiliate_wp()->utils->log( 'Recurring Referrals: Recurring Referral not created because this is the first invoice on the subscription.' );

			return;
		}

		$parent_referral = affiliate_wp()->referrals->get_by( 'reference', $subscription->id, $this->context );

		if ( ! $parent_referral ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Parent referral not located for this subscription payment.' );

			return;
		}

		affiliate_wp()->utils->log( 'Recurring Referrals: Parent referral successfully located via reference for this Stripe subscription ID' );

		if ( simpay_is_zero_decimal( $invoice->currency ) ) {
			$amount = $invoice->total;
		} else {
			$amount = round( $invoice->total / 100, 2 );
		}

		$reference    = $invoice->id;
		$affiliate_id = $parent_referral->affiliate_id;

		$referral_amount = $this->calc_referral_amount( $amount, $reference, $parent_referral->referral_id, '', $affiliate_id );

		/**
		 * Fires when the amount of a recurring referral is calculated.
		 *
		 * @param float $referral_amount The referral amount.
		 * @param int   $affiliate_id    The affiliate ID.
		 * @param float $amount          The full transaction amount.
		 *
		 * @since 1.5
		 */
		$referral_amount = (string) apply_filters( 'affwp_recurring_calc_referral_amount', $referral_amount, $affiliate_id, $amount );

		$referral_data = array(
			'affiliate_id' => $affiliate_id,
			'context'      => $this->context,
			'amount'       => $referral_amount,
			'reference'    => $reference,
			'custom'       => array(
				'parent'   => $parent_referral->referral_id,
				'livemode' => $invoice->livemode,
			),
			'date'         => date_i18n( 'Y-m-d g:i:s', $event->created ),
			'description'  => sprintf( __( 'Subscription payment for: %d', 'affiliate-wp-recurring-referrals' ), $parent_referral->referral_id ),
			'currency'     => affwp_get_currency(),
			'visit_id'     => $parent_referral->visit_id,
			'parent_id'    => $parent_referral->referral_id,
		);

		// Insert this referral if it hasn't been recorded yet.
		$referral_id = $this->insert_referral( $referral_data );

		if ( $referral_id ) {

			$this->complete_referral( $referral_id );

			// Passes the parent referral ID into the action noted below.
			$parent_referral_id = $parent_referral->referral_id;

			/**
			 * Fires when a referral is successfully added.
			 *
			 * @param int $referral_id        The referral ID.
			 * @param int $parent_referral_id The ID of the parent referral.
			 *
			 * @since 1.6
			 */
			do_action( 'affwprr_stripe_subscription_payment_succeeded', $referral_id, $parent_referral_id );

			affiliate_wp()->utils->log( 'Recurring Referrals: The affwprr_stripe_subscription_payment_succeeded action fired successfully.' );
		}
	}

	/**
	 * Builds the reference string for the referrals table.
	 * Uses the Stripe transaction or subscription ID as the unique reference.
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function reference_string( $ref_string = '', $referral ) {

		if( empty( $referral->context ) || 'stripe' != $referral->context ) {

			return $ref_string;
		}

		$test = '';

		if( ! empty( $referral->custom ) ) {
			$custom = maybe_unserialize( $referral->custom );
			$test   = empty( $custom['livemode'] ) ? 'test/' : '';
		}

		if( false === strpos( $referral->reference, 'in_' ) ) {
			return $ref_string;
		}

		$url = 'https://dashboard.stripe.com/' . $test . 'invoices/' . $referral->reference ;

		return '<a href="' . esc_url( $url ) . '">' . $referral->reference . '</a>';

		return $ref_string;
	}

}
new Affiliate_WP_Recurring_Stripe;
