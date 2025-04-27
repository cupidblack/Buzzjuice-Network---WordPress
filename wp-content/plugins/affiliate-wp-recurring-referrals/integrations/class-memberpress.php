<?php

class Affiliate_WP_Recurring_MemberPress extends Affiliate_WP_Recurring_Base {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.4
	*/
	public function init() {

		$this->context = 'memberpress';

		add_action( 'mepr-txn-status-pending', array( $this, 'record_referral_on_payment' ), -1 );
		add_action( 'mepr-txn-status-complete', array( $this, 'record_referral_on_payment' ), -1 );
		add_action( 'mepr-txn-status-complete', array( $this, 'mark_referral_complete' ), 100 );

		// Per membership referral rate settings.
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );

	}

	/**
	 * Insert referrals on subscription payments
	 *
	 * @since 1.4
	 *
	 * @param \MeprTransaction $txn Memberpress transaction object.
	*/
	public function record_referral_on_payment( $txn ) {

		if ( empty( $txn->subscription_id ) ) {
			return;
		}

		if ( 'payment' !== $txn->txn_type ) {
			return; // This is not a payment transaction.
		}

		$reference = $this->get_reference_transaction( $txn );

		if ( $reference === false ) {
			if ( version_compare( AFFILIATEWP_VERSION, '2.3', '>=' ) ) {
				affiliate_wp()->utils->log( 'Recurring Referrals: No parent subscription was found, so no reference could be created.', $txn );
			} else {
				affiliate_wp()->utils->log( 'Recurring Referrals: No parent subscription was found, so no reference could be created. Transaction: ' . var_export( $txn, true ) );
			}
			return; // This transaction is not associated with a referred subscription.
		}

		// Check if the parent referral reference is the same as this transaction id and bail if so.
		if ( $reference == $txn->id ) {
			return;
		}

		$referrals = affiliate_wp()->referrals->get_referrals( array(
			'reference' => $reference,
			'context'   => $this->context,
		) );

		$parent_referral = is_array( $referrals ) && isset( $referrals[0] ) ? $referrals[0] : false;

		if ( ! $parent_referral
		     || ! is_object( $parent_referral )
		     || 'rejected' == $parent_referral->status
		     || 'pending' == $parent_referral->status
		) {
			affiliate_wp()->utils->log( 'Recurring Referrals: No referral found or referral is rejected. Transaction: ' . var_export( $txn, true ) );
			return; // This signup wasn't referred or is the very first payment of a referred subscription
		}

		if ( ! $txn->subscription()->trial ) {

			// Determine if this is the initial payment recorded moments after a subscription is purchased and bail if so
			$transactions = $txn->get_all_by_subscription_id( $txn->subscription_id );
			$transactions = wp_list_pluck( $transactions, 'txn_type' );

			if ( count( $transactions ) <= 2 && in_array( 'subscription_confirmation', $transactions ) ) {
				affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because this is the first subscription payment.' );
				return; // This is the first payment recorded right after a subscription, skip it
			}

		}

		// Bail if recurring referrals are disabled for this membership.
		if ( get_post_meta( $txn->product_id, '_affwp_' . $this->context . '_recurring_referrals_disabled', true ) ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because recurring referral is disabled for this membership.' );
			return false;
		}

		$amount       = $txn->amount;
		$reference    = $txn->id;
		$affiliate_id = $parent_referral->affiliate_id;

		$referral_amount = $this->calc_referral_amount(
			$amount,
			$reference,
			$parent_referral->referral_id,
			$txn->product_id,
			$affiliate_id
		);

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
			'description'  => sprintf( __( 'Subscription payment for %s', 'affiliate-wp-recurring-referrals' ), $txn->subscription_id ),
			'amount'       => $referral_amount,
			'custom'       => $parent_referral->reference,
			'parent_id'    => $parent_referral->referral_id
		);

		$referral_id = $this->insert_referral( $args );

		if ( $referral_id && 'complete' === $txn->status ) {

			$this->complete_referral( $referral_id );

		}

	}

	/**
	 * Mark referral as complete
	 *
	 * See https://github.com/AffiliateWP/affiliate-wp-recurring-referrals/issues/59
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function mark_referral_complete( $txn ) {

		$referral = affiliate_wp()->referrals->get_by( 'reference', $txn->id, $this->context );

		if( $referral && 'pending' === $referral->status ) {

			$this->complete_referral( $referral->referral_id );

		}

	}

	/**
	 * Retrieves the original transaction for the current transaction's subscription.
	 *
	 * @since 1.7.2
	 *
	 * @param \MeprTransaction $txn Memberpress transaction object.
	 * @return bool|int The original transaction ID if it exists. Otherwise false.
	 */
	public function get_reference_transaction( $txn ) {

		$subscription_id = $txn->subscription_id;
		$first_sub_txn   = \MeprTransaction::get_first_subscr_transaction( $subscription_id );

		if ( $first_sub_txn ) {
			$reference_txn_id = $first_sub_txn->id;

			return $reference_txn_id;
		}

		return false;
	}

	/**
	 * Register the metabox for recurring rates.
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function register_metabox() {

		add_meta_box( 'affwp_recurring_product_rate', __( 'Affiliate Recurring Referrals Settings', 'affiliate-wp-recurring-referrals' ),  array( $this, 'render_metabox' ), 'memberpressproduct', 'side', 'low' );

	}

	/**
	 * Render the recurring rates metabox.
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function render_metabox() {

		global $post;

		$product_id = ! empty( $post ) ? $post->ID : 0;

		$recurring_product_rate        = get_post_meta( $product_id, '_affwp_' . $this->context . '_recurring_product_rate', true );
		$recurring_referrals_limit     = get_post_meta( $product_id, '_affwp_' . $this->context . '_recurring_referrals_limit', true );
		$recurring_referral_disabled   = get_post_meta( $product_id, '_affwp_' . $this->context . '_recurring_referrals_disabled', true );
		?>
		<p>
			<label for="affwp_recurring_product_rate">
				<input type="text" name="_affwp_<?php echo $this->context; ?>_recurring_product_rate" id="affwp_recurring_product_rate" class="small-text" value="<?php echo esc_attr( $recurring_product_rate ); ?>" />
				<?php _e( 'Recurring Rate', 'affiliate-wp-recurring-referrals' ); ?>
			</label>
		</p>

		<p>
			<label for="affwp_disable_recurring_referrals">
				<input type="checkbox" name="_affwp_<?php echo $this->context; ?>_recurring_referrals_disabled" id="affwp_disable_recurring_referrals" value="1"<?php checked( $recurring_referral_disabled, true ); ?> />
				<?php _e( 'Disable recurring referrals on this membership', 'affiliate-wp-recurring-referrals' ); ?>
			</label>
		</p>
		<p><?php _e( 'These settings will be used to calculate earnings for recurring payments. Leave blank to use the site default recurring rate.', 'affiliate-wp-recurring-referrals' ); ?></p>

		<p>
			<label for="affwp_recurring_referrals_limit">
				<input type="text" name="_affwp_<?php echo $this->context; ?>_recurring_referrals_limit" id="affwp_recurring_referrals_limit" class="small-text" value="<?php echo esc_attr( $recurring_referrals_limit ); ?>" />
				<?php _e( 'Recurring Referrals Limit', 'affiliate-wp-recurring-referrals' ); ?>
			</label>
		</p>
		<p><?php _e( 'The number of recurring referral(s) that will be created for recurring payments for this membership.', 'affiliate-wp-recurring-referrals' ); ?></p>
		<?php
	}

	/**
	 * Saves per-product recurring rate settings input fields.
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function save_meta( $post_id = 0 ) {

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Don't save revisions and autosaves
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			return $post_id;
		}

		// Check post type is product
		if ( 'memberpressproduct' != $post->post_type ) {
			return $post_id;
		}

		// Check user permission
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( ! empty( $_POST[ '_affwp_' . $this->context . '_recurring_product_rate' ] ) ) {

			$recurring_rate = sanitize_text_field( $_POST[ '_affwp_' . $this->context . '_recurring_product_rate' ] );
			update_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_product_rate', $recurring_rate );

		} else {

			delete_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_product_rate' );

		}

		if ( isset( $_POST[ '_affwp_' . $this->context . '_recurring_referrals_disabled' ] ) ) {

			update_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_referrals_disabled', 1 );

		} else {

			delete_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_referrals_disabled' );

		}

		if ( isset( $_POST[ '_affwp_' . $this->context . '_recurring_referrals_limit' ] ) ) {

			$recurring_referral_limit = sanitize_text_field( $_POST[ '_affwp_' . $this->context . '_recurring_referrals_limit' ] );
			update_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_referrals_limit', $recurring_referral_limit );

		} else {

			delete_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_referrals_limit' );

		}

	}

}
new Affiliate_WP_Recurring_MemberPress;
