<?php

class Affiliate_WP_Recurring_WooCommerce extends Affiliate_WP_Recurring_Base {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function init() {

		$this->context = 'woocommerce';

		add_action( 'woocommerce_subscription_renewal_payment_complete', array( $this, 'record_referral_on_payment' ), -1 );

		// Per product recurring referral rates settings.
		add_action( 'affwp_woocommerce_product_settings', array( $this, 'recurring_product_settings' ) );
		add_action( 'affwp_woocommerce_variation_settings', array( $this, 'recurring_variation_settings' ), 100, 3 );
		add_action( 'save_post', array( $this, 'save_meta' ) );
		add_action( 'woocommerce_ajax_save_product_variations', array( $this, 'save_variation_data' ) );

	}

	/**
	 * Insert referrals on subscription payments
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function record_referral_on_payment( $subscription ) {

		$last_order = $subscription->get_last_order( 'all' );

		$parent_reference = method_exists( $subscription, 'get_parent_id' ) ? $subscription->get_parent_id() : $subscription->order->id;

		$parent_referral  = affiliate_wp()->referrals->get_by( 'reference', $parent_reference, $this->context );

		if ( ! $parent_referral || ! is_object( $parent_referral ) || 'rejected' == $parent_referral->status ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: No referral found or referral is rejected. Subscription: ' . var_export( $subscription, true ) );
			return false; // This signup wasn't referred or is the very first payment of a referred subscription
		}

		$reference = method_exists( $last_order, 'get_id' ) ? $last_order->get_id() : $last_order->id;

		$cart_shipping = $last_order->get_total_shipping();

		if ( ! affiliate_wp()->settings->get( 'exclude_tax' ) ) {
			$cart_shipping += $last_order->get_shipping_tax();
		}

		$amount = 0.00;

		$items = $last_order->get_items();

		// Calculate the referral amount based on product prices
		$referral_amount = 0.00;

		$affiliate_id = $parent_referral->affiliate_id;

		foreach( $items as $product ) {

			// Bail if recurring referrals are disabled for this product.
			if ( get_post_meta( $product['product_id'], '_affwp_' . $this->context . '_recurring_referrals_disabled', true ) ) {
				affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because recurring referral is disabled for this product.' );
				continue;
			}

			// Bail if recurring referrals are disabled for this product.
			if ( ! empty( $product['variation_id'] ) && get_post_meta( $product['variation_id'], '_affwp_' . $this->context . '_recurring_referrals_disabled', true ) ) {
				affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because recurring referral is disabled for this product.' );
				continue;
			}

			// The order discount has to be divided across the items
			$product_total = $product['line_total'];
			$shipping      = 0;

			if ( $cart_shipping > 0 && ! affiliate_wp()->settings->get( 'exclude_shipping' ) ) {
				$shipping       = $cart_shipping / count( $items );
				$product_total += $shipping;
			}

			if ( ! affiliate_wp()->settings->get( 'exclude_tax' ) ) {
				$product_total += $product['line_tax'];
			}

			$amount += $product_total;

			$product_id_for_rate = $product['product_id'];

			if ( ! empty( $product['variation_id'] ) ) {
				$product_id_for_rate = $product['variation_id'];
			}

			$referral_amount += $this->calc_referral_amount( $product_total, $reference, $parent_referral->referral_id, $product_id_for_rate, $affiliate_id );

		}

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
			'description'  => sprintf( __( 'Subscription payment for %d', 'affiliate-wp-recurring-referrals' ), $parent_reference ),
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
	 * Adds per-product recurring referral rate settings input fields.
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function recurring_product_settings() {

		global $post;

		?>
			<div class="options_group show_if_subscription">
				<p><?php _e( 'Configure recurring rates for this product. These settings will be used to calculate earnings for recurring payments.', 'affiliate-wp-recurring-referrals' ); ?></p>
				<?php
				woocommerce_wp_select( array(
					'id'          => '_affwp_woocommerce_recurring_product_rate_type',
					'label'       => __( 'Recurring Rate Type', 'affiliate-wp-recurring-referrals' ),
					'options'     => array_merge( array( '' => __( 'Site Default', 'affiliate-wp-recurring-referrals' ) ),affwp_get_affiliate_rate_types() ),
					'desc_tip'    => true,
					'description' => __( 'Earnings can be based on either a percentage or a flat rate amount.', 'affiliate-wp-recurring-referrals' ),
				) );
				woocommerce_wp_text_input( array(
					'id'          => '_affwp_woocommerce_recurring_product_rate',
					'label'       => __( 'Recurring Rate', 'affiliate-wp-recurring-referrals' ),
					'desc_tip'    => true,
					'description' => __( 'Leave blank to use default recurring rate.', 'affiliate-wp-recurring-referrals' )
				) );
				woocommerce_wp_checkbox( array(
					'id'          => '_affwp_woocommerce_recurring_referrals_disabled',
					'label'       => __( 'Disable recurring referrals', 'affiliate-wp-recurring-referrals' ),
					'description' => __( 'This will prevent this product from generating recurring referral commissions for affiliates.', 'affiliate-wp-recurring-referrals' ),
					'cbvalue'     => 1
				) );
				?>
			</div>
			<div class="options_group show_if_subscription">
				<p><?php _e( 'Configure recurring referral limit for this product. This setting will be used to limit the number of recurring referral(s) that will be created for recurring payments for this product.', 'affiliate-wp-recurring-referrals' ); ?></p>
				<?php
				woocommerce_wp_text_input( array(
					'id'          => '_affwp_woocommerce_recurring_referrals_limit',
					'label'       => __( 'Recurring Referrals Limit', 'affiliate-wp-recurring-referrals' ),
					'desc_tip'    => true,
					'description' => __( 'The number of recurring referral(s) that will be created for recurring payments for this product.', 'affiliate-wp-recurring-referrals' )
				) );
				?>
			</div>
			<?php wp_nonce_field( 'affwp_woo_recurring_product_nonce', 'affwp_woo_recurring_product_nonce' ); ?>
		<?php

	}

	/**
	 * Adds per-product variation recurring referral rate settings input fields.
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function recurring_variation_settings( $loop, $variation_data, $variation ) {

		$rate      = get_post_meta( $variation->ID, '_affwp_' . $this->context . '_recurring_product_rate', true );
		$rate_type = get_post_meta( $variation->ID, '_affwp_' . $this->context . '_recurring_product_rate_type', true );
		$disabled  = get_post_meta( $variation->ID, '_affwp_' . $this->context . '_recurring_referrals_disabled', true );
		$limit     = get_post_meta( $variation->ID, '_affwp_' . $this->context . '_recurring_referrals_limit', true );
		?>

		<div id="affwp_product_recurring_variation_settings" class="show_if_variable-subscription">

			<p class="form-row form-row-full">
				<?php _e( 'Configure recurring rates for this product variation. These settings will be used to calculate earnings for recurring payments.', 'affiliate-wp-recurring-referrals' ); ?>
			</p>

			<p class="form-row form-row-full">
				<label for="_affwp_woocommerce_variation_recurring_rate_types[<?php echo $variation->ID; ?>]"><?php echo __( 'Recurring Rate Type', 'affiliate-wp-recurring-referrals' ); ?></label>
				<select name="_affwp_woocommerce_variation_recurring_rate_types[<?php echo $variation->ID; ?>]" id="_affwp_woocommerce_variation_recurring_rate_types[<?php echo $variation->ID; ?>]">
					<option value=""><?php _e( 'Site Default', 'affiliate-wp-recurring-referrals' ); ?></option>
					<?php foreach( affwp_get_affiliate_rate_types() as $key => $type ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $rate_type, $key ); ?>><?php echo esc_html( $type ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>

			<p class="form-row form-row-full">
				<label for="_affwp_woocommerce_variation_recurring_rates[<?php echo $variation->ID; ?>]"><?php echo __( 'Recurring Rate', 'affiliate-wp-recurring-referrals' ); ?></label>
				<input type="text" size="5" name="_affwp_woocommerce_variation_recurring_rates[<?php echo $variation->ID; ?>]" value="<?php echo esc_attr( $rate ); ?>" class="wc_input_price" id="_affwp_woocommerce_variation_recurring_rates[<?php echo $variation->ID; ?>]" placeholder="<?php esc_attr_e( 'Recurring rate (optional)', 'affiliate-wp-recurring-referrals' ); ?>" />
			</p>

			<p class="form-row form-row-full options">
				<label for="_affwp_woocommerce_variation_recurring_referrals_disabled[<?php echo $variation->ID; ?>]">
					<input type="checkbox" class="checkbox" name="_affwp_woocommerce_variation_recurring_referrals_disabled[<?php echo $variation->ID; ?>]" id="_affwp_woocommerce_variation_recurring_referrals_disabled[<?php echo $variation->ID; ?>]" <?php checked( $disabled, true ); ?> /> <?php _e( 'Disable recurring referrals for this product variation', 'affiliate-wp-recurring-referrals' ); ?>
				</label>
			</p>

		</div>

		<div id="affwp_product_recurring_variation_settings" class="show_if_variable-subscription">

			<p class="form-row form-row-full">
				<?php _e( 'Configure recurring referral limit for this product variation. This setting will be used to limit the number of recurring referral(s) that will be created for recurring payments for this product.', 'affiliate-wp-recurring-referrals' ); ?>
			</p>

			<p class="form-row form-row-full">
				<label for="_affwp_woocommerce_variation_recurring_referrals_limit[<?php echo $variation->ID; ?>]"><?php echo __( 'Recurring Referrals Limit', 'affiliate-wp-recurring-referrals' ); ?></label>
				<input type="text" size="5" name="_affwp_woocommerce_variation_recurring_referrals_limit[<?php echo $variation->ID; ?>]" value="<?php echo esc_attr( $limit ); ?>" class="wc_input_price" id="_affwp_woocommerce_variation_recurring_referrals_limit[<?php echo $variation->ID; ?>]" placeholder="<?php esc_attr_e( 'Recurring referrals limit (optional)', 'affiliate-wp-recurring-referrals' ); ?>" />
			</p>

		</div>

		<?php

	}

	/**
	 * Saves per-product recurring referral rate settings input fields.
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

		if ( empty( $_POST['affwp_woo_recurring_product_nonce'] ) || ! wp_verify_nonce( $_POST['affwp_woo_recurring_product_nonce'], 'affwp_woo_recurring_product_nonce' ) ) {
			return $post_id;
		}

		$post = get_post( $post_id );

		if ( ! $post ) {
			return $post_id;
		}

		// Check post type is product
		if ( 'product' != $post->post_type ) {
			return $post_id;
		}

		// Check user permission
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if ( ! empty( $_POST[ '_affwp_' . $this->context . '_recurring_product_rate' ] ) ) {

			$rate = sanitize_text_field( $_POST[ '_affwp_' . $this->context . '_recurring_product_rate' ] );
			update_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_product_rate', $rate );

		} else {

			delete_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_product_rate' );

		}

		if ( ! empty( $_POST[ '_affwp_' . $this->context . '_recurring_product_rate_type' ] ) ) {

			$rate_type = sanitize_text_field( $_POST[ '_affwp_' . $this->context . '_recurring_product_rate_type' ] );
			update_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_product_rate_type', $rate_type );

		} else {

			delete_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_product_rate_type' );

		}

		if ( isset( $_POST[ '_affwp_' . $this->context . '_recurring_referrals_limit' ] ) ) {

			$limit = sanitize_text_field( $_POST[ '_affwp_' . $this->context . '_recurring_referrals_limit' ] );
			update_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_referrals_limit', $limit );

		} else {

			delete_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_referrals_limit' );

		}

		$this->save_variation_data( $post_id );

		if ( isset( $_POST[ '_affwp_' . $this->context . '_recurring_referrals_disabled' ] ) ) {

			update_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_referrals_disabled', 1 );

		} else {

			delete_post_meta( $post_id, '_affwp_' . $this->context . '_recurring_referrals_disabled' );

		}

	}

	/**
	 * Saves variation data.
	 *
	 * @access  public
	 * @since   1.7
	 */
	public function save_variation_data( $product_id = 0 ) {

		if( ! empty( $_POST['variable_post_id'] ) && is_array( $_POST['variable_post_id'] ) ) {

			foreach( $_POST['variable_post_id'] as $variation_id ) {

				$variation_id = absint( $variation_id );

				if ( ! empty( $_POST['_affwp_woocommerce_variation_recurring_rates'] ) && ! empty( $_POST['_affwp_woocommerce_variation_recurring_rates'][ $variation_id ] ) ) {

					$rate = sanitize_text_field( $_POST['_affwp_woocommerce_variation_recurring_rates'][ $variation_id ] );
					update_post_meta( $variation_id, '_affwp_' . $this->context . '_recurring_product_rate', $rate );

				} else {

					delete_post_meta( $variation_id, '_affwp_' . $this->context . '_recurring_product_rate' );

				}

				if ( ! empty( $_POST['_affwp_woocommerce_variation_recurring_rate_types'] ) && ! empty( $_POST['_affwp_woocommerce_variation_recurring_rate_types'][ $variation_id ] ) ) {

					$rate_type = sanitize_text_field( $_POST['_affwp_woocommerce_variation_recurring_rate_types'][ $variation_id ] );
					update_post_meta( $variation_id, '_affwp_' . $this->context . '_recurring_product_rate_type', $rate_type );

				} else {

					delete_post_meta( $variation_id, '_affwp_' . $this->context . '_recurring_product_rate_type' );

				}

				if ( ! empty( $_POST['_affwp_woocommerce_variation_recurring_referrals_disabled'] ) && ! empty( $_POST['_affwp_woocommerce_variation_recurring_referrals_disabled'][ $variation_id ] ) ) {

					update_post_meta( $variation_id, '_affwp_' . $this->context . '_recurring_referrals_disabled', 1 );

				} else {

					delete_post_meta( $variation_id, '_affwp_' . $this->context . '_recurring_referrals_disabled' );

				}

				if ( ! empty( $_POST['_affwp_woocommerce_variation_recurring_referrals_limit'] ) && ! empty( $_POST['_affwp_woocommerce_variation_recurring_referrals_limit'][ $variation_id ] ) ) {

					$recurring_limit = sanitize_text_field( $_POST['_affwp_woocommerce_variation_recurring_referrals_limit'][ $variation_id ] );
					update_post_meta( $variation_id, '_affwp_' . $this->context . '_recurring_referrals_limit', $recurring_limit );

				} else {

					delete_post_meta( $variation_id, '_affwp_' . $this->context . '_recurring_referrals_limit' );

				}

			}

		}

	}

}
new Affiliate_WP_Recurring_WooCommerce;
