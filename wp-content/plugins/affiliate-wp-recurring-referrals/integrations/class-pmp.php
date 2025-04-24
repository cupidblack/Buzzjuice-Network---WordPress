<?php

class Affiliate_WP_Recurring_PMP extends Affiliate_WP_Recurring_Base {

	/**
	 * Membership-level recurring referrals settings.
	 *
	 * @since 1.7
	 * @access public
	 * @var array
	 */
	public $level_recurring_referrals_settings = array();

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function init() {

		$this->context = 'pmp';

		add_action( 'pmpro_added_order', array( $this, 'record_referral_on_payment' ), -1 );

		// Membership level referrals rate settings.
		add_action( 'pmpro_membership_level_after_other_settings', array( $this, 'membership_level_setting' ) );
		add_action( 'pmpro_save_membership_level', array( $this, 'save_membership_level_setting' ) );

	}

	/**
	 * Insert referrals on subscription payments
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function record_referral_on_payment( $order ) {

		$first_order = $this->get_first_order( $order );

		if ( empty( $first_order ) ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because this is the first subscription payment. Order: ' . var_export( $order, true )  );
			return;
		}

		$parent_referral = affiliate_wp()->referrals->get_by( 'reference', $first_order->id, $this->context );

		if ( ! $parent_referral || ! is_object( $parent_referral ) || 'rejected' == $parent_referral->status ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: No referral found or referral is rejected. Order: ' . var_export( $order, true ) );
			return false; // This signup wasn't referred or is the very first payment of a referred subscription
		}

		$membership_level = isset( $first_order->membership_id ) ? (int) $first_order->membership_id : 0;

		$this->level_recurring_referrals_settings = get_option( "_affwp_pmp_recurring_product_settings_{$membership_level}", array() );

		// Bail if recurring referrals are disabled for this membership level.
		if ( ! empty( $this->level_recurring_referrals_settings['disabled'] ) ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because recurring referral is disabled for this membership level.' );
			return false;
		}

		$amount       = $order->subtotal;
		$reference    = $order->id;
		$affiliate_id = $parent_referral->affiliate_id;

		$referral_amount = $this->calc_referral_amount( $amount, $reference, $parent_referral->referral_id, $membership_level, $affiliate_id );

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

		//make sure membership level data is populated
		$order->getMembershipLevel();

		$args = array(
			'reference'    => $reference,
			'affiliate_id' => $affiliate_id,
			'description'  => sprintf( __( 'Subscription payment for %s', 'affiliate-wp-recurring-referrals' ), $order->membership_level->name ),
			'amount'       => $referral_amount,
			'custom'       => $first_order->id,
			'parent_id'    => $parent_referral->referral_id
		);

		$referral_id = $this->insert_referral( $args );

		// Prevent infinite loop
		remove_action( 'pmpro_updated_order', array( $this, 'complete_referral' ), -1 );

		$order->affiliate_id = $parent_referral->affiliate_id;
		$amount = html_entity_decode( affwp_currency_filter( affwp_format_amount( $referral_amount ) ), ENT_QUOTES, 'UTF-8' );
		$name   = affiliate_wp()->affiliates->get_affiliate_name( $parent_referral->affiliate_id );
		$note   = sprintf( __( 'Referral #%d for %s recorded for %s', 'affiliate-wp-recurring-referrals' ), $referral_id, $amount, $name );

		if( empty( $order->notes ) ) {
			$order->notes = $note;
		} else {
			$order->notes = $order->notes . "\n\n" . $note;
		}

		$order->saveOrder();

		if ( $referral_id ) {

			$this->complete_referral( $referral_id );

		}

	}

	/**
	 * Retrieves the order ID of the first payment made for a subscription
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function get_first_order( $order ) {

		global $wpdb;

		// Make sure a subscription transaction ID is present
		if( empty( $order->subscription_transaction_id ) ) {
			return false;
		}

		// get the order ID of the first payment of this subscription
		$query = "SELECT id FROM $wpdb->pmpro_membership_orders WHERE
					gateway = '" . esc_sql( $order->gateway ) . "' AND
					gateway_environment = '" . esc_sql( $order->gateway_environment ) . "' AND
					user_id = '" . esc_sql( $order->user_id ) . "' AND
					membership_id = '" . esc_sql( $order->membership_id ) . "' AND
					subscription_transaction_id = '" . esc_sql( $order->subscription_transaction_id ) . "' ";

		//if this is an existing order, make sure we don't select our self
		if( ! empty( $order->id ) ) {
			$query .= "AND id < '" . esc_sql( $order->id ) . "' ";
		}

		//just the first
		$query .= "ORDER BY id ASC LIMIT 1";

		$id = $wpdb->get_col( $query );

		if( ! empty( $id ) ) {
			return new MemberOrder( $id[0] );
		}

		return false;

	}

	/**
	 * Outputs membership level recurring referral settings.
	 *
	 * @since 1.7
	 * @access public
	 */
	public function membership_level_setting() {
		$level = isset( $_REQUEST['edit'] ) ? intval( $_REQUEST['edit'] ) : 0;

		if ( ! $level ) {
			return;
		}

		$default_recurring_rate  = affiliate_wp()->settings->get( 'recurring_rate', '' );
		$default_recurring_limit = affiliate_wp()->settings->get( 'recurring_referral_limit', '' );

		$affwp_pmp_recurring_settings = get_option( "_affwp_pmp_recurring_product_settings_{$level}", array() );

		$rate     = ! empty( $affwp_pmp_recurring_settings['rate'] ) ? $affwp_pmp_recurring_settings['rate'] : '';
		$limit    = ! empty( $affwp_pmp_recurring_settings['limit'] ) ? $affwp_pmp_recurring_settings['limit'] : '';
		$disabled = empty( $affwp_pmp_recurring_settings['disabled'] ) ? false : true;
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top">
						<label for="affwp_pmp_recurring_product_rate"><?php _e( 'Recurring Rate', 'affiliate-wp-recurring-referrals' );?>:</label>
					</th>
					<td>
						<input id="affwp_pmp_recurring_product_rate" class="small-text" name="affwp_pmp_recurring_product_rate" type="number" min="0" max="999999" step="0.01" placeholder="<?php echo esc_attr( $default_recurring_rate ); ?>" value="<?php echo esc_attr( $rate ); ?>" />
						<p class="description"><?php printf( __( 'The recurring referral rate, such as 20 for 20%%. If left blank, the site default recurring rate of %s will be used.', 'affiliate-wp-recurring-referrals' ), esc_html( $default_recurring_rate ) ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="affwp_pmp_disable_recurring_referrals"><?php _e( 'Disable Recurring Referrals', 'affiliate-wp-recurring-referrals' );?>:</label>
					</th>
					<td><input id="affwp_pmp_disable_recurring_referrals" name="affwp_pmp_disable_recurring_referrals" type="checkbox" value="yes" <?php checked( $disabled, true ); ?> /> <label for="affwp_pmp_disable_referrals"><?php _e( 'Check to disable recurring referrals.', 'affiliate-wp-recurring-referrals' );?></label></td>
				</tr>
				<tr>
					<th scope="row" valign="top">
						<label for="affwp_pmp_recurring_referrals_limit"><?php _e( 'Recurring Referral Limit', 'affiliate-wp-recurring-referrals' );?>:</label>
					</th>
					<td>
						<input id="affwp_pmp_recurring_referrals_limit" class="small-text" name="affwp_pmp_recurring_referrals_limit" type="number" min="0" max="999999" step="1" placeholder="<?php echo esc_attr( $default_recurring_limit ); ?>" value="<?php echo esc_attr( $limit ); ?>" />
						<p class="description"><?php _e( 'The number of recurring referral(s) that will be created for recurring payments for this membership level.', 'affiliate-wp-recurring-referrals' ) ?></p>
					</td>
				</tr>
			</tbody>
		</table>
		<?php wp_nonce_field( 'affwp_pmp_membership_recurring_referrals_nonce', 'affwp_pmp_membership_recurring_referrals_nonce' );
	}

	/**
	 * Saves membership level recurring referral settings.
	 *
	 * @since 1.7
	 * @access public
	 *
	 * @param int $level_id Level ID.
	 */
	public function save_membership_level_setting( $level_id ) {
		if ( ! $level_id || empty( $_REQUEST['affwp_pmp_membership_recurring_referrals_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['affwp_pmp_membership_recurring_referrals_nonce'], 'affwp_pmp_membership_recurring_referrals_nonce' ) ) {
			return;
		}

		$recurring_rate              = isset( $_REQUEST['affwp_pmp_recurring_product_rate'] ) ? sanitize_text_field( $_REQUEST['affwp_pmp_recurring_product_rate'] ) : '';
		$recurring_referrals_limit   = isset( $_REQUEST['affwp_pmp_recurring_referrals_limit'] ) ? sanitize_text_field( $_REQUEST['affwp_pmp_recurring_referrals_limit'] ) : '';
		$recurring_referral_disabled = (bool) isset( $_REQUEST['affwp_pmp_disable_recurring_referrals'] );

		$settings = array(
			'rate'     => $recurring_rate,
			'limit'    => $recurring_referrals_limit,
			'disabled' => $recurring_referral_disabled,
		);

		update_option( "_affwp_pmp_recurring_product_settings_{$level_id}", $settings );
	}
	/**
	 * Retrieves the recurring referral rate for a specific product.
	 *
	 * @access public
	 * @since  1.7
	 *
	 * @param int   $product_id Optional. Membership Level ID. Default 0.
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

		$rate = '';

		if ( $this->level_recurring_referrals_settings ) {
			$rate = $this->level_recurring_referrals_settings['rate'];
		}

		// Product ID is expected to be 0 for PMP.
		$product_id = 0;

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

		$limit = '';

		if ( $this->level_recurring_referrals_settings ) {
			$limit = $this->level_recurring_referrals_settings['limit'];
		}

		// Product ID is expected to be 0 for PMP.
		$product_id = 0;

		if ( empty( $limit ) || ! is_numeric( $limit ) ) {

			$limit = null;

		}

		/** This filter is documented in includes/integrations/class-base.php */
		return apply_filters( 'affwp_get_recurring_product_referral_limit', $limit, $product_id, $this->affiliate_id, $this->context );
	}

}
new Affiliate_WP_Recurring_PMP;