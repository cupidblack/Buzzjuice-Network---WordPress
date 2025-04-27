<?php

class Affiliate_WP_Recurring_EDD extends Affiliate_WP_Recurring_Base {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function init() {

		$this->context = 'edd';

		add_action( 'edd_recurring_record_payment', array( $this, 'record_referral_on_payment' ), -1, 3 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

		// Per product recurring referral rates settings.
		add_action( 'edd_meta_box_settings_fields', array( $this, 'download_settings' ), 100 );
		add_filter( 'edd_metabox_fields_save', array( $this, 'download_save_fields' ) );
	}

	/**
	 * Insert referrals on subscription payments
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function record_referral_on_payment( $payment_id, $parent_id, $amount ) {

		$parent_referral = affiliate_wp()->referrals->get_by( 'reference', $parent_id, $this->context );

		if ( ! $parent_referral || ! is_object( $parent_referral ) || 'rejected' == $parent_referral->status ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: No referral found or referral is rejected. Payment ID: ' . $payment_id );
			return false; // This signup wasn't referred or is the very first payment of a referred subscription.
		}

		$reference    = $payment_id;
		$affiliate_id = $parent_referral->affiliate_id;

		$downloads = edd_get_payment_meta_cart_details( $payment_id );

		if ( is_array( $downloads ) ) {

			// Calculate the referral amount based on product prices
			$payment_total   = 0.00;
			$referral_amount = 0.00;

			foreach ( $downloads as $key => $download ) {

				if ( get_post_meta( $download['id'], '_affwp_' . $this->context . '_recurring_referrals_disabled', true ) ) {
					affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because recurring referral is disabled for this product.' );
					continue; // Recurring referrals are disabled on this product.
				}

				if ( affiliate_wp()->settings->get( 'exclude_tax' ) ) {
					$download_amount = $download['price'] - $download['tax'];
				} else {
					$download_amount = $download['price'];
				}

				if ( class_exists( 'EDD_Simple_Shipping' ) ) {

					if ( isset( $download['fees'] ) ) {

						foreach ( $download['fees'] as $fee_id => $fee ) {

							if ( false !== strpos( $fee_id, 'shipping' ) ) {

								if ( ! affiliate_wp()->settings->get( 'exclude_shipping' ) ) {
									$download_amount += $fee['amount'];
								}

							}

						}

					}

				}

				if ( class_exists( 'edd_dp' ) ) {

					if ( isset( $download['fees']['dp_'.$download['id']] ) ) {
						$download_amount += $download['fees']['dp_'.$download['id']]['amount'];
					}

				}

				// Check for Recurring Payments signup fee
				if ( ! empty( $download['item_number']['options']['recurring']['signup_fee'] ) ) {
					$download_amount += $download['item_number']['options']['recurring']['signup_fee'];
				}

				$payment_total += $download_amount;

				$referral_amount += $this->calc_referral_amount( $download_amount, $payment_id, $parent_referral->referral_id, $download['id'], $affiliate_id );
			}

		} else {

			if ( affiliate_wp()->settings->get( 'exclude_tax' ) ) {
				$payment_total = edd_get_payment_subtotal( $payment_id );
			} else {
				$payment_total = edd_get_payment_amount( $payment_id );
			}

			$referral_amount = $this->calc_referral_amount( $payment_total, $payment_id, $parent_referral->referral_id, '', $affiliate_id );
		}

		/**
		 * Fires when the amount of a recurring referral is calculated.
		 *
		 * @param float $referral_amount         The referral amount.
		 * @param int   $affiliate_id            The affiliate ID.
		 * @param float $payment_total           The full transaction amount.
		 *
		 * @since 1.5
		 */
		$referral_amount = (string) apply_filters( 'affwp_recurring_calc_referral_amount', $referral_amount, $affiliate_id, $payment_total );

		$args = array(
			'reference'    => $reference,
			'affiliate_id' => $affiliate_id,
			'description'  => sprintf( __( 'Subscription payment for %d', 'affiliate-wp-recurring-referrals' ), $parent_id ),
			'amount'       => $referral_amount,
			'custom'       => $parent_id,
			'parent_id'    => $parent_referral->referral_id
		);

		$referral_id = $this->insert_referral( $args );

		if ( $referral_id ) {

			$this->complete_referral( $referral_id );

		}

	}

	/**
	 * Builds the reference link for the referrals table
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || $this->context != $referral->context ) {

			return $reference;

		}

		if( ! empty( $referral->custom ) && is_int( $referral->custom ) ) {
			$reference = $referral->custom;
		}

		$url = admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}

	/**
	 * Adds per-product referral rate settings input fields
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function download_settings( $download_id = 0 ) {

		$recurring_rate            = get_post_meta( $download_id, '_affwp_' . $this->context . '_recurring_product_rate', true );
		$recurring_referrals_limit = get_post_meta( $download_id, '_affwp_' . $this->context . '_recurring_referrals_limit', true );
		$disabled                  = get_post_meta( $download_id, '_affwp_' . $this->context . '_recurring_referrals_disabled', true );
		?>
		<p>
			<strong><?php _e( 'Recurring Rates:', 'affiliate-wp-recurring-referrals' ); ?></strong>
		</p>

		<p>
			<label for="affwp_recurring_product_rate">
				<input type="number" step="0.01" max="999999" min="0" name="_affwp_edd_recurring_product_rate" id="affwp_recurring_product_rate" class="small-text" value="<?php echo esc_attr( $recurring_rate ); ?>" />
				<?php _e( 'Recurring Rate', 'affiliate-wp-recurring-referrals' ); ?>
			</label>
		</p>

		<p>
			<label for="affwp_disable_recurring_referrals">
				<input type="checkbox" name="_affwp_edd_recurring_referrals_disabled" id="affwp_disable_recurring_referrals" value="1"<?php checked( $disabled, true ); ?> />
				<?php printf( __( 'Disable recurring referrals on this %s', 'affiliate-wp-recurring-referrals' ), edd_get_label_singular() ); ?>
			</label>
		</p>

		<p><?php _e( 'These settings will be used to calculate earnings for recurring payments. Leave blank to use the site default recurring rate.', 'affiliate-wp-recurring-referrals' ); ?></p>

		<p>
			<strong><?php _e( 'Recurring Referrals Limit:', 'affiliate-wp-recurring-referrals' ); ?></strong>
		</p>

		<p>
			<label for="affwp_recurring_referrals_limit">
				<input type="number" step="1" max="999999" min="0" name="_affwp_edd_recurring_referrals_limit" id="affwp_recurring_referrals_limit" class="small-text" value="<?php echo esc_attr( $recurring_referrals_limit ); ?>" />
				<?php _e( 'Recurring Referrals Limit', 'affiliate-wp-recurring-referrals' ); ?>
			</label>
		</p>

		<p><?php _e( 'The number of recurring referral(s) that will be created for recurring payments for this download.', 'affiliate-wp-recurring-referrals' ); ?></p>

		<?php
	}

	/**
	 * Tells EDD to save our product settings
	 *
	 * @access  public
	 * @since   1.7
	 * @return  array
	 */
	public function download_save_fields( $fields = array() ) {
		$fields[] = '_affwp_edd_recurring_product_rate';
		$fields[] = '_affwp_edd_recurring_referrals_disabled';
		$fields[] = '_affwp_edd_recurring_referrals_limit';
		return $fields;
	}


}
new Affiliate_WP_Recurring_EDD;