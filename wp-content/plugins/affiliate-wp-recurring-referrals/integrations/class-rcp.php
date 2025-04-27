<?php

class Affiliate_WP_Recurring_RCP extends Affiliate_WP_Recurring_Base {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function init() {

		$this->context = 'rcp';

		add_action( 'rcp_insert_payment', array( $this, 'record_referral_on_payment' ), -1, 3 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

		// Per product recurring referral rates settings.
		add_action( 'rcp_add_subscription_form', array( $this, 'subscription_new' ) );
		add_action( 'rcp_edit_subscription_form', array( $this, 'subscription_edit' ) );
		add_action( 'rcp_add_subscription', array( $this, 'store_subscription_meta' ), 10, 2 );
		add_action( 'rcp_edit_subscription_level', array( $this, 'store_subscription_meta' ), 10, 2 );
	}

	/**
	 * Insert referrals on subscription payments
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function record_referral_on_payment( $payment_id, $args, $amount ) {

		global $rcp_levels_db;

		$parent_referral = affiliate_wp()->referrals->get_by( 'reference', $args['subscription_key'], $this->context );

		if ( ! $parent_referral || ! is_object( $parent_referral ) || 'rejected' == $parent_referral->status || 'pending' == $parent_referral->status ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: No referral found or referral is rejected. Payment ID: ' . $payment_id );
			return false; // This signup wasn't referred or is the very first payment of a referred subscription
		}

		// Bail if recurring referrals are disabled on this subscription level
		if( $rcp_levels_db->get_meta( $args['object_id'], 'affwp_rcp_disable_recurring_referrals', true ) ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because recurring referral is disabled for this subscription level.' );
			return false;
		}

		$reference    = $payment_id;
		$affiliate_id = $parent_referral->affiliate_id;

		$referral_amount = $this->calc_referral_amount( $amount, $reference, $parent_referral->referral_id, $args['object_id'], $affiliate_id );

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
			'description'  => $args['subscription'],
			'amount'       => $referral_amount,
			'custom'       => $args['subscription_key'],
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
	public function reference_link( $link = '', $referral ) {

		if( empty( $referral->context ) || 'rcp' != $referral->context ) {

			return $link;

		}

		if( ! empty( $referral->custom ) && ! is_array( $referral->custom ) && 32 == strlen( $referral->custom ) ) {
			$url  = admin_url( 'admin.php?page=rcp-payments&s=' . $referral->custom );
			$link = '<a href="' . esc_url( $url ) . '">RCP: ' . $referral->reference . '</a>';
		}

		return $link;
	}

	/**
	 * Display Affiliate Rate field on add subscription screen
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function subscription_new() {
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="rcp-recurring-rate"><?php _e( 'Recurring Rate', 'affiliate-wp-recurring-referrals' ); ?></label>
			</th>
			<td>
				<input name="affwp_rcp_level_recurring_rate" id="rcp-recurring-rate" style="width:40px" type="number" min="0"/>
				<p class="description"><?php _e( 'This rate will be used to calculate earnings for recurring payments. Leave blank to use the site default recurring rate.', 'affiliate-wp-recurring-referrals' ); ?></p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="rcp-affwp-recurring-referrals-limit"><?php _e( 'Recurring Referral Limit', 'affiliate-wp-recurring-referrals' ); ?></label>
			</th>
			<td>
				<input name="affwp_rcp_recurring_referrals_limit" id="rcp-affwp-recurring-referrals-limit" style="width:50px" step="1" min="0" max="999999" type="number"/>
				<p class="description"><?php _e( 'The number of recurring referral(s) that will be created for recurring payments for this product.', 'affiliate-wp-recurring-referrals' ); ?></p>
			</td>
		</tr>
		<?php
	}

	/**
	 * Display Affiliate Rate field on subscription edit screen
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function subscription_edit( $level ) {

		global $rcp_levels_db;

		$rate     = get_option( 'affwp_rcp_level_recurring_rate_' . $level->id );
		$disabled = false;

		// Make sure RCP version is compatible
		if ( is_a( $rcp_levels_db, 'RCP_Levels' ) ) {

			$disabled       = (bool) $rcp_levels_db->get_meta( $level->id, 'affwp_rcp_disable_recurring_referrals', true );
			$referral_limit = $rcp_levels_db->get_meta( $level->id, 'affwp_rcp_recurring_referrals_limit', true );

		}
		?>
		<tr class="form-field">
			<th scope="row" valign="top">
				<label for="rcp-recurring-rate"><?php _e( 'Recurring Rate', 'affiliate-wp-recurring-referrals' ); ?></label>
			</th>
			<td>
				<input name="affwp_rcp_level_recurring_rate" id="rcp-recurring-rate" style="width:40px" type="number" min="0" value="<?php echo esc_attr( $rate ); ?>"/>
				<p class="description"><?php _e( 'This rate will be used to calculate earnings for recurring payments. Leave blank to use the site default recurring rate.', 'affiliate-wp-recurring-referrals' ); ?></p>
			</td>
		</tr>
		<?php if ( is_a( $rcp_levels_db, 'RCP_Levels' ) ) : ?>
			<tr class="form-field">
				<th scope="row" valign="top">
					<?php _e( 'Disable Recurring Referrals', 'affiliate-wp-recurring-referrals' ); ?>
				</th>
				<td>
					<label for="rcp-affwp-disable-recurring-referrals">
						<input name="affwp_rcp_disable_recurring_referrals" id="rcp-affwp-disable-recurring-referrals" type="checkbox" value="1"<?php checked( true, $disabled ); ?>/>
						<?php _e( 'Disable recurring referrals on this subscription level.', 'affiliate-wp-recurring-referrals' ); ?>
					</label>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top">
					<label for="rcp-affwp-recurring-referrals-limit"><?php _e( 'Recurring Referral Limit', 'affiliate-wp-recurring-referrals' ); ?></label>
				</th>
				<td>
					<input name="affwp_rcp_recurring_referrals_limit" id="rcp-affwp-recurring-referrals-limit" style="width:50px" step="1" min="0" max="999999" type="number" value="<?php echo esc_attr( $referral_limit ); ?>"/>
					<p class="description"><?php _e( 'The number of recurring referral(s) that will be created for recurring payments for this level.', 'affiliate-wp-recurring-referrals' ); ?></p>
				</td>
			</tr>
		<?php endif; ?>
		<?php
	}

	/**
	 * Store the rate for the subscription level
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function store_subscription_meta( $level_id = 0, $args ) {

		global $rcp_levels_db;

		if ( ! empty( $_POST['affwp_rcp_level_recurring_rate'] ) ) {

			update_option( 'affwp_rcp_level_recurring_rate_' . $level_id, sanitize_text_field( $_POST['affwp_rcp_level_recurring_rate'] ) );

		} else {

			delete_option( 'affwp_rcp_level_recurring_rate_' . $level_id );

		}

		// Make sure RCP version is compatible
		if ( ! is_a( $rcp_levels_db, 'RCP_Levels' ) ) {
			return;
		}

		if ( ! empty( $_POST['affwp_rcp_disable_recurring_referrals'] ) ) {

			$rcp_levels_db->update_meta( $level_id, 'affwp_rcp_disable_recurring_referrals', 1 );

		} else {

			$rcp_levels_db->delete_meta( $level_id, 'affwp_rcp_disable_recurring_referrals' );

		}

		if ( ! empty( $_POST['affwp_rcp_recurring_referrals_limit'] ) ) {

			$rcp_levels_db->update_meta( $level_id, 'affwp_rcp_recurring_referrals_limit', sanitize_text_field( $_POST['affwp_rcp_recurring_referrals_limit'] ) );

		} else {

			$rcp_levels_db->delete_meta( $level_id, 'affwp_rcp_recurring_referrals_limit' );

		}

	}

	/**
	 * Retrieves the recurring referral rate for a specific product.
	 *
	 * @access public
	 * @since  1.7
	 *
	 * @param int   $product_id  Optional. Subscription Level ID. Default 0.
	 * @param array $args {
	 *      Optional. Arguments for getting the product recurring rate.
	 *
	 *      @type string|int $reference    Optional. Referral reference (usually the order ID). Default empty.
	 *      @type int        $affiliate_id Optional. Affiliate ID.
	 * }
	 *
	 * @return float The recurring referral product rate.
	 */
	public function get_recurring_product_rate( $product_id = 0, $args = array() ) {

		$rate = get_option( 'affwp_rcp_level_recurring_rate_' . $product_id, true );

		if ( empty( $rate ) || ! is_numeric( $rate ) ) {

			$rate = null;

		}

		$affiliate_id = isset( $args['affiliate_id'] ) ? $args['affiliate_id'] : $this->affiliate_id;

		$type = affwp_get_affiliate_meta( $affiliate_id, 'recurring_rate_type', true );

		if ( empty ( $type ) ) {

			$type = affiliate_wp()->settings->get( 'recurring_rate_type' );

			if ( empty( $type ) ) {

				$type = affwp_get_affiliate_rate_type( $affiliate_id );

			}

		}

		if ( is_numeric( $rate ) && 'flat' !== $type ) {
			$rate /= 100;
		}

		/** This filter is documented in includes/integrations/class-base.php */
		return apply_filters( 'affwp_get_recurring_product_rate', $rate, $product_id, $args, $affiliate_id, $this->context );
	}

	/**
	 * Retrieves the recurring referral limit for a specific product.
	 *
	 * @since  1.7
	 *
	 * @param  integer $product_id  Optional. Product ID. Default 0.
	 *
	 * @return mixed int|bool $limit The recurring referral limit. Returns false by default if not enabled or set.
	 */
	public function get_recurring_product_referral_limit( $product_id = 0 ) {

		$limit = get_option( 'affwp_rcp_level_recurring_rate_' . $product_id, true );

		if ( empty( $limit ) || ! is_numeric( $limit ) ) {

			$limit = null;

		}

		/** This filter is documented in includes/integrations/class-base.php */
		return apply_filters( 'affwp_get_recurring_product_referral_limit', $limit, $product_id, $this->affiliate_id, $this->context );
	}

}
new Affiliate_WP_Recurring_RCP;