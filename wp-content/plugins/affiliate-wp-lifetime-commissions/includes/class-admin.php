<?php

class Affiliate_WP_Lifetime_Commissions_Admin {

	public function __construct() {

		// Settings tab.
		add_action( 'affwp_settings_tabs', array( $this, 'settings_tab' ) );
		add_filter( 'affwp_settings', array( $this, 'register_settings' ) );

		// Save lifetime commissions option in the affiliate's user meta table.
		add_action( 'affwp_update_affiliate', array( $this, 'update_affiliate' ), 0 );

		// Add checkbox to edit affiliate page.
		add_action( 'affwp_edit_affiliate_bottom', array( $this, 'affiliate_admin_edit' ) );

		// Delink a linked customer from an affiliate.
		add_action( 'affwp_lc_delink_customer', array( $this, 'delink_customer' ) );

		// Delink a linked customer from a deleted affiliate.
		add_action( 'affwp_affiliate_deleted', array( $this, 'delink_customer_affiliate_deleted' ) );

		// Load JS.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 100 );

		// Ajax add customer email.
		add_action( 'wp_ajax_affwp_lc_add_email', array( $this, 'add_customer_email' ), 100 );

		// Add link icon to the referral id column to indicate a lifetime referral.
		add_filter( 'affwp_referral_table_referral_id', array( $this, 'affwp_custom_referral_id_column' ), 10, 2 );

		// add user profile field
		add_action( 'show_user_profile', array( $this, 'profile_field' ) );
		add_action( 'edit_user_profile', array( $this, 'profile_field' ) );

		// save user profile field
		add_action( 'personal_options_update', array( $this, 'save_profile_field' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_profile_field' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_js' ) );

		// Add the default global rate to LC referral rate if empty.
		add_filter( 'affwp_settings_sanitize_number', array( $this, 'sanitize_referral_rate' ), 1, 2 );
	}

	/**
	 * Enqueues AffiliateWP admin JS on user edit screen
	 *
	 * @since 1.5
	 *
	 * @param string $hook The current admin page
	 */
	public function enqueue_admin_js( $hook ) {
		if ( $hook === 'user-edit.php' ) {
			affwp_enqueue_admin_js();
		}
	}

	/**
	 * Add Lifetime Commissions section to user's WP profile
	 * Allows an admin to link an affiliate to the user.
	 *
	 * @since  1.5
	 * @param WP_User $user The WP User to use.
	 */
	public function profile_field( $user ) {

		// return if not admin
		if ( ! current_user_can( 'manage_affiliate_options' ) ) {
			return;
		}

		$customer = affiliate_wp()->customers->get_by( 'user_id', $user->ID );

		$username         = $affiliate_url = '';
		$linked_affiliate = false;

		if ( $customer ) {
			$lifetime_customer = affiliate_wp_lifetime_commissions()->lifetime_customers->get_by( 'affwp_customer_id', $customer->customer_id );
			if ( $lifetime_customer ) {
				$linked_affiliate = affwp_get_affiliate( $lifetime_customer->affiliate_id );

				if ( false !== $linked_affiliate ) {
					$user          = $linked_affiliate->get_user();
					$username      = false !== $user ? $user->user_nicename : '';
					$affiliate_url = affwp_admin_url( 'affiliates', array(
						'action'       => 'edit_affiliate',
						'affiliate_id' => $linked_affiliate->ID,
					) );
				}
			}
		}

		?>
		<h3><?php _e( 'Lifetime Commissions', 'affiliate-wp-lifetime-commissions' ); ?></h3>

		<table class="form-table">
			<tr>
				<th>
					<label for="lifetime-affiliate"><?php _e( 'Linked Affiliate', 'affiliate-wp-lifetime-commissions' ); ?></label>
				</th>
				<td>
					<span class="affwp-ajax-search-wrap">
						<input type="text" name="lifetime_affiliate" id="lifetime-affiliate" value="<?php echo esc_attr( $username ); ?>" class="affwp-user-search regular-text" data-affwp-status="active"/>
					</span>
					<p class="description"><?php _e( 'The affiliate linked to this customer.', 'affiliate-wp-lifetime-commissions' ) ?>
						<?php
						if ( false !== $linked_affiliate ): ?>
							<a href="<?php echo esc_url( $affiliate_url ) ?>"><?php _e( 'View Affiliate', 'affiliate-wp-lifetime-commissions' ) ?></a>
						<?php endif; ?>
					</p>
				</td>
			</tr>
		</table>
	<?php }

	/**
	 * Action to save user profile fields related to this plugin.
	 *
	 * @since 1.5
	 * @param int $user_id The User ID to save against
	 */
	public function save_profile_field( $user_id ) {

		$lifetime_user_name = isset( $_POST['lifetime_affiliate'] ) ? $_POST['lifetime_affiliate'] : '';
		$affiliate_user     = get_user_by( 'login', $lifetime_user_name );

		// Prevent affiliates from setting themselves as a lifetime customer.
		if ( $affiliate_user->ID === $user_id ) {
			return;
		}

		$customer = affiliate_wp()->customers->get_by( 'user_id', $user_id );

		// If the customer does not exist, create it.
		if ( ! $customer ) {
			$user = get_user_by( 'id', $user_id );

			$customer = affwp_get_customer( affwp_add_customer( array(
				'user_id' => $user_id,
				'email'   => $user->user_email,
			) ) );
		}

		$lifetime_customer = affiliate_wp_lifetime_commissions()->lifetime_customers->get_by( 'affwp_customer_id', $customer->customer_id );
		$affiliate         = '';

		if ( $affiliate_user ) {
			$affiliate = affiliate_wp()->affiliates->get_by( 'user_id', $affiliate_user->ID );
		}

		// If the affiliate was unset, and this user is currently a customer, delink the affiliate and bail.
		if ( $lifetime_customer && ! $affiliate ) {
			affiliate_wp_lifetime_commissions()->lifetime_customers->delete( $lifetime_customer->lifetime_customer_id );

			return;
		}

		// Add a new lifetime customer if the provided lifetime customer does not exist
		if ( ! $lifetime_customer ) {
			affiliate_wp_lifetime_commissions()->lifetime_customers->add( array(
				'affwp_customer_id' => $customer->customer_id,
				'affiliate_id'      => $affiliate->affiliate_id,
			) );

			// Otherwise, confirm the affiliate is actually a new affiliate before changing.
		} elseif ( $affiliate->affiliate_id !== $lifetime_customer->affiliate_id ) {
			affiliate_wp_lifetime_commissions()->lifetime_customers->update( $lifetime_customer->affwp_customer_id, array(
				'affiliate_id' => $affiliate->affiliate_id,
			) );
		}

	}

	/**
	 * Adds the settings tab.
	 *
	 * @since 1.4
	 *
	 * @param array $tabs Tabs array.
	 * @return array Modified tabs array.
	 */
	public function settings_tab( $tabs ) {
		$tabs['lifetime-commissions'] = __( 'Lifetime Commissions', 'affiliate-wp-lifetime-commissions' );

		return $tabs;
	}

	/**
	 * Add link icon to the referral id column to indicate a lifetime referral.
	 *
	 * @since 1.3
	 */
	function affwp_custom_referral_id_column( $value, $referral ) {

		$custom = maybe_unserialize( $referral->custom );

		if ( $custom && is_array( $custom ) && in_array( 'lifetime_referral', $custom ) || $custom == 'lifetime_referral' ) {
			$value = $value . ' <span class="dashicons dashicons-admin-links" aria-label="' . __( 'Lifetime Referral', 'affiliate-wp-lifetime-commissions' ) . '"></span>';
		}

		return $value;
	}

	/**
	 * Legacy settings registration method.
	 *
	 * @since 1.0
	 * @deprecated 1.4 Use register_settings() instead
	 *
	 * @param array $settings Optional. Global integrations settings. Default empty array.
	 * @return array Modified Integrations settings array.
	 */
	public function settings_integrations( $settings = array() ) {
		_deprecated_function( __METHOD__, '1.4', 'Use register_settings() instead' );

		$all_settings    = affiliate_wp()->settings->get_registered_settings();
		$legacy_settings = $this->register_settings( $all_settings['integrations'] );

		// Add the header.
		$header = array(
			'name' => __( 'Lifetime Commissions', 'affiliate-wp-lifetime-commissions' ),
			'type' => 'header',
		);

		$legacy_settings = array_merge( array( 'lifetime_commissions_header' => $header ), $legacy_settings['lifetime-commissions'] );

		return $legacy_settings;
	}

	/**
	 * Registers settings for the add-on.
	 *
	 * @since 1.4
	 *
	 * @param array $settings Optional. Global AffWP settings. Default empty array.
	 * @return array Modified AffWP settings array.
	*/
	public function register_settings( $settings = array() ) {

		$settings['lifetime-commissions'] = array(
			'lifetime_commissions' => array(
				'name' => __( 'Enable For All Affiliates?', 'affiliate-wp-lifetime-commissions' ),
				'desc' => __( 'Check this box to enable lifetime commissions for all affiliates.<br /><em>Leave unchecked to enable lifetime commissions on a per-affiliate basis only.</em>', 'affiliate-wp-lifetime-commissions' ),
				'type' => 'checkbox'
			),
			'lifetime_commissions_customers_access' => array(
				'name' => __( 'Lifetime Customers Access', 'affiliate-wp-lifetime-commissions' ),
				'desc' => __( 'Check this box to allow all affiliates to see their lifetime customers.', 'affiliate-wp-lifetime-commissions' ),
				'type' => 'checkbox',
			),
			'lifetime_commissions_hide_customer_emails' => array(
				'name' => __( 'Lifetime Customers Hide Emails', 'affiliate-wp-lifetime-commissions' ),
				'desc' => __( 'Hides lifetime customer emails from affiliates for compliance with GDPR or other privacy reasons.', 'affiliate-wp-lifetime-commisions' ),
				'type' => 'checkbox',
			),
			'lifetime_commissions_enable_lifetime_referral_rates' => array(
				'name' => __( 'Enable Lifetime Referral Rates', 'affiliate-wp-lifetime-commissions' ),
				'desc' => __( 'Check this box to enable lifetime referral rates.', 'affiliate-wp-lifetime-commissions' ),
				'type' => 'checkbox'
			),
			'lifetime_commissions_lifetime_referral_rate' => array(
				'name' => __( 'Lifetime Referral Rate', 'affiliate-wp-lifetime-commissions' ),
				'desc' => __( 'Referral rate used for lifetime referrals, for all affiliates. The lifetime referral rate will take priority unless it is set to 0, or a per-affiliate lifetime referral rate is set.', 'affiliate-wp-lifetime-commissions' ),
				'type' => 'number',
				'size' => 'small',
				'step' => '0.01',
				'std'  => affiliate_wp()->settings->get( 'referral_rate' )
			),
			'lifetime_commissions_lifetime_referral_rate_type' => array(
				'name'    => __( 'Lifetime Referral Rate Type', 'affiliate-wp-lifetime-commissions' ),
				'desc'    => __( 'Should lifetime referrals be based on a percentage or flat rate amounts?', 'affiliate-wp-lifetime-commissions' ),
				'type'    => 'select',
				'options' => array_merge( array( __( 'Site Default', 'affiliate-wp-lifetime-commissions' ) ), affwp_get_affiliate_rate_types() )
			),
			'lifetime_commissions_lifetime_length' => array(
				'name' => __( 'Lifetime Length', 'affiliate-wp-lifetime-commissions' ),
				'desc' => __( 'The number of days lifetime commissions will be generated from a referral for all affiliates. The lifetime length will always be used unless a per-affiliate lifetime length is set. Set to 0 for infinite.', 'affiliate-wp-lifetime-commissions' ),
				'type' => 'number',
				'size' => 'small',
				'step' => '1',
				'std'  => 0
			),
			'lifetime_commissions_link_customers_on_registration' => array(
				'name' => __( 'Link Customers To Affiliates On User Registration', 'affiliate-wp-lifetime-commissions' ),
				'desc' => __( 'Check this box if you would like to automatically link customers to affiliates on user registration.', 'affiliate-wp-lifetime-commissions' ),
				'type' => 'checkbox',
			),
			'lifetime_commissions_uninstall_on_delete' => array(
				'name' => __( 'Remove Data When Deleted?', 'affiliate-wp-lifetime-commissions' ),
				'desc' => __( 'Check this box if you would like Lifetime Commissions to completely remove all of its data when the plugin is deleted.', 'affiliate-wp-lifetime-commissions' ),
				'type' => 'checkbox'
			),
		);

		return $settings;
	}

	/**
	 * Add checkbox to edit affiliate page.
	 *
	 * @since  1.0
	 */
	public function affiliate_admin_edit( $affiliate ) {

		?>

		<table class="form-table">
			<tr><th scope="row"><label for="affwp_settings[lifetime_commissions_header]"><?php _e( 'Lifetime Commissions', 'affiliate-wp-lifetime-commissions' ); ?></label></th><td><hr></td></tr>
		</table>

		<?php
		// if all affiliate can earn lifetime commissions don't show this admin option
		$global_lc_enabled    = affiliate_wp()->settings->get( 'lifetime_commissions' );
		$affiliate_lc_enabled = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'affwp_lc_enabled', true );
		$lc_enabled           = ( $global_lc_enabled || ( ! $global_lc_enabled && $affiliate_lc_enabled ) );

		$lc_settings_url = affwp_admin_url( 'settings', array( 'tab' => 'lifetime-commissions' ) );

		if ( ! $global_lc_enabled ) :
			?>
			<table class="form-table">

				<tr class="form-row form-required">

					<th scope="row">
						<label for="lifetime-commissions"><?php _e( 'Enable Lifetime Commissions', 'affiliate-wp-lifetime-commissions' ); ?></label>
					</th>

					<td>
						<label for="lifetime-commissions">
							<input type="checkbox" name="lifetime_commissions" id="lifetime-commissions" value="1" <?php checked( $affiliate_lc_enabled, 1 ); ?> />
							<?php esc_html_e( 'Allow this affiliate to receive lifetime commissions.', 'affiliate-wp-lifetime-commissions' ); ?>
						</label>
					</td>

				</tr>

			</table>

		<?php
		endif;

		// If all affiliate can access their lifetime customers.
		$global_lc_customer_access = affiliate_wp()->settings->get( 'lifetime_commissions_customers_access', false );

		if ( ! $global_lc_customer_access ) :

			$checked = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'affwp_lc_customers_access', true );
			?>
			<table class="form-table">

				<tr class="form-row form-required">

					<th scope="row">
						<label for="lifetime-customers-access"><?php _e( 'Lifetime Customers Access', 'affiliate-wp-lifetime-commissions' ); ?></label>
					</th>

					<td>
						<label for="lifetime-customers-access">
							<input type="checkbox" name="lifetime_customers_access" id="lifetime-customers-access" value="1" <?php checked( $checked, 1 ); ?> />
							<?php esc_html_e( 'Allow affiliate to see their lifetime customers.', 'affiliate-wp-lifetime-commissions' ); ?>
						</label>
					</td>

				</tr>

			</table>

		<?php
		endif;

		$global_rate   = affiliate_wp()->settings->get( 'lifetime_commissions_lifetime_referral_rate', 20 );
		$lifetime_rate = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'affwp_lc_lifetime_referral_rate', true );

		// Lifetime referral rates must be enabled.
		if ( affiliate_wp()->settings->get( 'lifetime_commissions_enable_lifetime_referral_rates' ) ) : ?>
			<table class="form-table">

				<tr class="form-row">

					<th scope="row">
						<label for="lifetime-referral-rate"><?php _e( 'Lifetime Referral Rate', 'affiliate-wp-lifetime-commissions' ); ?></label>
					</th>

					<td>
						<input type="number" name="lifetime_referral_rate" id="lifetime-referral-rate" class="small-text" step="0.01" min="0" placeholder="<?php echo esc_attr( $global_rate ); ?>" value="<?php echo esc_attr( $lifetime_rate ); ?>" />
						<p class="description"><?php echo sprintf( __( 'The affiliate&#8217;s lifetime referral rate. This rate takes priority over the <a href="%s">Lifetime Referral Rate</a> set in the Lifetime Commissions settings tab.', 'affiliate-wp-lifetime-commissions' ), $lc_settings_url ); ?></p>

					</td>
				</tr>

				<?php $lifetime_referral_rate_type = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'affwp_lc_lifetime_referral_rate_type', true ); ?>

				<tr class="form-row">

					<th scope="row">
						<label for="lifetime-referral-rate-type"><?php _e( 'Lifetime Referral Rate Type', 'affiliate-wp' ); ?></label>
					</th>

					<td>
						<select name="lifetime_referral_rate_type" id="lifetime-referral-rate-type">
							<option value=""><?php _e( 'Lifetime Referral Rate Type Default', 'affiliate-wp' ); ?></option>
							<?php foreach( affwp_get_affiliate_rate_types() as $key => $type ) : ?>
								<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $lifetime_referral_rate_type, $key ); ?>><?php echo esc_html( $type ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php echo sprintf( __( 'The affiliate&#8217;s lifetime referral rate type. This rate type takes priority over the <a href="%s">Lifetime Referral Rate Type</a> set in the Lifetime Commissions settings tab.', 'affiliate-wp-lifetime-commissions' ), $lc_settings_url ); ?></p>
					</td>

				</tr>

			</table>

		<?php
        endif;

        $global_length   = affiliate_wp()->settings->get( 'lifetime_commissions_lifetime_length', 0 );
        $lifetime_length = affwp_get_affiliate_meta( $affiliate->affiliate_id, 'affwp_lc_lifetime_length', true );

        ?>

        <table class="form-table">

            <tr class="form-row">

                <th scope="row">
                    <label for="lifetime-referral-length"><?php _e( 'Lifetime Length', 'affiliate-wp-lifetime-commissions' ); ?></label>
                </th>

                <td>
                    <input type="number" class="small-text" name="lifetime_referral_length" id="lifetime-referral-length" placeholder="<?php echo esc_attr( $global_length ); ?>" value="<?php echo esc_attr( $lifetime_length ); ?>" />
                    <p class="description"><?php echo sprintf( __( 'The number of days this affiliate can earn lifetime commissions from a referral. This length takes priority over the <a href="%s">Lifetime Length</a> set in the Lifetime Commissions settings tab.', 'affiliate-wp-lifetime-commissions' ), $lc_settings_url ); ?></p>
                </td>
            </tr>

        </table>

		<table class="form-table">
			<tr class="form-row form-required">
				<th scope="row">
					<label for="lifetime-commissions"><?php _e( 'Lifetime Customers', 'affiliate-wp-lifetime-commissions' ); ?></label>
				</th>

				<td>
					<?php $linked_customers = affiliate_wp_lifetime_commissions()->integrations->get_customers_for_affiliate( $affiliate->affiliate_id ); ?>

					<p id="affwp-lc-linked-customers-desc" class="description">
						<?php
						if ( $linked_customers && $lc_enabled ) :
							_e( 'Customers linked to this affiliate.', 'affiliate-wp-lifetime-commissions' );
						else :
							_e( 'No customers are linked to this affiliate.', 'affiliate-wp-lifetime-commissions' );
						endif;
						?>
					</p>

					<ul class="affwp-lc-linked-customers">
						<?php
						if ( $linked_customers && $lc_enabled ) :
							foreach ( $linked_customers as $customer ) :

								if ( ! $customer ) {
									continue;
								}

								$customer_html = $this->get_customer_html( $customer->customer_id, $affiliate->ID );

								if ( false !== $customer_html ) {
									echo $customer_html;
								}

							endforeach;
						endif;
						?>
					</ul>
				</td>
			</tr>
		</table>

		<?php if ( $lc_enabled ) : ?>
			<table class="form-table">

				<tr class="form-row form-required">

					<th scope="row">
						<label for="lifetime-commissions"><?php _e( 'Add New Customer Email', 'affiliate-wp-lifetime-commissions' ); ?></label>
					</th>

					<td>
						<input class="regular-text" type="email" name="affwp_lc_email" id="affwp_lc_email" value="" placeholder="<?php esc_attr_e( 'Enter an email address', 'affiliate-wp-lifetime-commissions' ); ?>"/>
						<input type="hidden" name="affwp_lc_affiliate_id" id="affwp_lc_affiliate_id" value="<?php echo esc_attr( $affiliate->affiliate_id ); ?>">
						<input type="submit" name="affwp_lc_add_email" id="affwp_lc_add_email" class="button" value="<?php _e( 'Add Email', 'affiliate-wp-lifetime-commissions' ); ?>">
						<p class="description"><?php _e( 'Enter an email to link a new customer to this affiliate.', 'affiliate-wp-lifetime-commissions' ); ?></p>
					</td>

				</tr>

			</table>
		<?php
		endif;
	}

	/**
	 * Save lifetime commissions option in the affiliate's user meta table.
	 *
	 * @since  1.0
	 */
	public function update_affiliate( $data ) {

		if ( empty( $data['affiliate_id'] ) ) {
			return false;
		}

		if ( ! current_user_can( 'manage_affiliates' ) ) {
			return;
		}

		$lifetime_commissions = isset( $data['lifetime_commissions'] ) ? $data['lifetime_commissions'] : '';

		if ( $lifetime_commissions ) {
		    affwp_update_affiliate_meta( $data['affiliate_id'], 'affwp_lc_enabled', $lifetime_commissions );
		} else {
		    affwp_delete_affiliate_meta( $data['affiliate_id'], 'affwp_lc_enabled' );
		}

		// Lifetime customers access.
		$lifetime_customers_access = isset( $data['lifetime_customers_access'] ) ? $data['lifetime_customers_access'] : '';

		if ( $lifetime_customers_access ) {
			affwp_update_affiliate_meta( $data['affiliate_id'], 'affwp_lc_customers_access', $lifetime_customers_access );
		} else {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'affwp_lc_customers_access' );
		}

		// Lifetime referral rate.
		$lifetime_rate = isset( $data['lifetime_referral_rate'] ) ? $data['lifetime_referral_rate'] : '';

		if ( $lifetime_rate ) {
			affwp_update_affiliate_meta( $data['affiliate_id'], 'affwp_lc_lifetime_referral_rate', (float) abs( $lifetime_rate ) );
		} else {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'affwp_lc_lifetime_referral_rate' );
		}

		// Lifetime length.
		$lifetime_length = isset( $data['lifetime_referral_length'] ) && '' !== $data['lifetime_referral_length'] ? $data['lifetime_referral_length'] : null;

		if ( $lifetime_length !== null ) {
			affwp_update_affiliate_meta( $data['affiliate_id'], 'affwp_lc_lifetime_length', absint( $data['lifetime_referral_length'] ) );
		} else {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'affwp_lc_lifetime_length' );
		}

		// Lifetime referral rate type.
		$lifetime_rate_type = isset( $data['lifetime_referral_rate_type'] ) ? $data['lifetime_referral_rate_type'] : '';

		if ( $lifetime_rate_type ) {
			affwp_update_affiliate_meta( $data['affiliate_id'], 'affwp_lc_lifetime_referral_rate_type', $lifetime_rate_type );
		} else {
			affwp_delete_affiliate_meta( $data['affiliate_id'], 'affwp_lc_lifetime_referral_rate_type' );
		}

		if ( ! empty( $data['affwp_lc_email'] ) && is_email( $data['affwp_lc_email'] ) ) {

			$customer = affiliate_wp()->customers->get_by( 'email', $data['affwp_lc_email'] );

			if ( $customer ) {

				if ( ! $customer->user_id ) {

					$user = get_user_by( 'email', $customer->email );

					if ( $user ) {

						$args = array(
							'user_id'     => $user->ID,
							'customer_id' => $customer->customer_id,
						);

						$update_customer = affwp_update_customer( $args );

						if ( $update_customer ) {

							$linked_affiliate = affiliate_wp_lifetime_commissions()->lifetime_customers->get_by( 'affwp_customer_id', $customer->customer_id );

							if ( ! $linked_affiliate ) {

								$args = array(
									'affwp_customer_id' => $customer->ID,
									'affiliate_id'      => $data['affiliate_id'],
								);

								affiliate_wp_lifetime_commissions()->lifetime_customers->add( $args );
							}
						}
					}
				}

				$linked_affiliate = affiliate_wp_lifetime_commissions()->lifetime_customers->get_by( 'affwp_customer_id', $customer->customer_id );

				if ( ! $linked_affiliate ) {

					$args = array(
						'affwp_customer_id' => $customer->ID,
						'affiliate_id'      => $data['affiliate_id'],
					);

					affiliate_wp_lifetime_commissions()->lifetime_customers->add( $args );

				}
			} else {

				$args = array(
					'email'        => $data['affwp_lc_email'],
					'affiliate_id' => $data['affiliate_id'],
				);

				$user = get_user_by( 'email', $data['affwp_lc_email'] );

				if ( $user ) {
					$args['user_id']    = $user->ID;
					$args['first_name'] = $user->first_name;
					$args['last_name']  = $user->last_name;
				}

				$customer_id = affwp_add_customer( $args );

				if ( false !== $customer_id ) {

					$args = array(
						'affwp_customer_id' => $customer_id,
						'affiliate_id'      => $data['affiliate_id'],
					);

					affiliate_wp_lifetime_commissions()->lifetime_customers->add( $args );
				}
			}

		}

	}

	/**
	 * Delink a linked customer from an affiliate.
	 *
	 * @since 1.3
	 */
	public function delink_customer( $data ) {

		if ( empty( $data[ '_wpnonce' ] ) ) {
			return;
		}

		if( ! wp_verify_nonce( $data['_wpnonce'], 'affwp-lc-delink-customer-nonce' ) ) {
			wp_die( __( 'Nonce verification failed', 'affiliate-wp' ), __( 'Error', 'affiliate-wp-lifetime-commissions' ), array( 'response' => 403 ) );
		}

		$customer_id  = absint( $data['customer'] );
		$affiliate_id = absint( $data['affiliate_id'] );

		$lifetime_customer = affiliate_wp_lifetime_commissions()->lifetime_customers->get_by( 'affwp_customer_id', $customer_id );

		if ( $lifetime_customer && ( $affiliate_id === absint( $lifetime_customer->affiliate_id ) ) ) {
			affiliate_wp_lifetime_commissions()->lifetime_customers->delete( $lifetime_customer->lifetime_customer_id );
		}

		$message = urlencode( __( 'Customer unlinked successfully', 'affiliate-wp-lifetime-commissions' ) );

		wp_redirect( affwp_admin_url( 'affiliates', array( 'action' => 'edit_affiliate', 'affiliate_id' => $affiliate_id, 'affwp_notice' => 'affiliate_delinked', 'affwp_message' => $message ) ) ); exit;

	}

	/**
	 * Unlinks a lifetime customer from a deleted affiliate.
	 *
	 * @since 1.4.4
	 *
	 * @param int              $affiliate_id The affiliate ID.
	 * @param bool             $delete_data  Whether the user data was also flagged for deletion.
	 * @param \AffWP\Affiliate $affiliate    Affiliate object.
	 */
	public function delink_customer_affiliate_deleted( $affiliate_id ) {
		$lifetime_customer = affiliate_wp_lifetime_commissions()->lifetime_customers->get_by( 'affiliate_id', $affiliate_id );

		if ( $lifetime_customer ) {
			affiliate_wp_lifetime_commissions()->lifetime_customers->delete( $lifetime_customer->lifetime_customer_id );
		}
	}

	/**
	 * Admin scripts.
	 *
	 * @since 1.3
	 */
	public function admin_scripts() {

		if ( ! affwp_is_admin_page() ) {
			return;
		}

		// Use minified libraries if SCRIPT_DEBUG is set to false.
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// Register scripts.
		wp_register_script( 'affwplc-admin-scripts', AFFWP_LC_PLUGIN_URL . 'assets/js/admin-scripts' . $suffix . '.js', array(), AFFWP_LC_VERSION, false );

		wp_localize_script( 'affwplc-admin-scripts', 'affwp_lc', array(
			'linked_customers' => __( 'Customers linked to this affiliate.', 'affiliate-wp-lifetime-commissions' ),
		) );

		// Enqueue scripts.
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'edit_affiliate' ) {
			wp_enqueue_script( 'affwplc-admin-scripts' );
		}
	}

	/**
	 * Handles Ajax for adding a new customer email.
	 *
	 * @since 1.3
	 */
	public function add_customer_email() {

		if ( empty( $_REQUEST['customer_email'] ) || empty( $_REQUEST['affiliate_id'] ) ) {
			wp_die( -1 );
		}

		if ( ! current_user_can( 'manage_affiliates' ) ) {
			wp_die( -1 );
		}

		$customer_email = sanitize_text_field( $_REQUEST['customer_email'] );
		$affiliate_id   = sanitize_text_field( $_REQUEST['affiliate_id'] );

		if ( ! is_email( $customer_email ) ) {
			wp_die( -1 );
		}

		$customer = affiliate_wp()->customers->get_by( 'email', $customer_email );

		if ( $customer ) {

			if ( ! $customer->user_id ) {

				$user = get_user_by( 'email', $customer->email );

				if ( $user ) {

					$args = array(
						'user_id'     => $user->ID,
						'customer_id' => $customer->customer_id,
					);

					affwp_update_customer( $args );
				}

			}

			$linked_affiliate = affiliate_wp_lifetime_commissions()->lifetime_customers->get_by( 'affwp_customer_id', $customer->customer_id );

			if ( ! $linked_affiliate ) {

				$args = array(
					'affwp_customer_id' => $customer->customer_id,
					'affiliate_id'      => $affiliate_id,
				);

				$lifetime_customer = affiliate_wp_lifetime_commissions()->lifetime_customers->add( $args );

				if ( $lifetime_customer ) {

					$customer_html = $this->get_customer_html( $customer->customer_id, $affiliate_id );

					$response = array(
						'message'       => __( 'Customer has been linked to this affiliate.', 'affiliate-wp-lifetime-commissions' ),
						'customer_html' => $customer_html,
					);

					wp_send_json_success( $response );

				} else {

					$response = array(
						'message' => __( 'Unable to link customer to this affiliate.', 'affiliate-wp-lifetime-commissions' ),
					);

					wp_send_json_error( $response );

				}

			} else {

				$response = array(
					'message' => __( 'Customer is already linked to an affiliate.', 'affiliate-wp-lifetime-commissions' ),
				);

				wp_send_json_error( $response );

			}

		} else {

			$args = array(
				'email'        => $customer_email,
				'affiliate_id' => $affiliate_id,
			);

			$user = get_user_by( 'email', $customer_email );

			if ( $user ) {
				$args['user_id']    = $user->ID;
				$args['first_name'] = $user->first_name;
				$args['last_name']  = $user->last_name;
			}

			$customer_id = affwp_add_customer( $args );

			if ( $customer_id ) {

				$args = array(
					'affwp_customer_id' => $customer_id,
					'affiliate_id'      => $affiliate_id,
				);

				$lifetime_customer = affiliate_wp_lifetime_commissions()->lifetime_customers->add( $args );

				if ( $lifetime_customer ) {

					$customer_html = $this->get_customer_html( $customer_id, $affiliate_id );

					$response = array(
						'message'       => __( 'Customer has been linked to this affiliate.', 'affiliate-wp-lifetime-commissions' ),
						'customer_html' => $customer_html,
					);

					wp_send_json_success( $response );

				} else {

					$response = array(
						'message' => __( 'Unable to link customer to this affiliate.', 'affiliate-wp-lifetime-commissions' ),
					);

					wp_send_json_error( $response );

				}

			} else {

				$response = array(
					'message' => __( 'Unable to link customer to this affiliate.', 'affiliate-wp-lifetime-commissions' ),
				);

				wp_send_json_error( $response );

			}

		}

	}

	/**
	 * Get customer HTML for Ajax response.
	 *
	 * @since 1.3
	 */
	function get_customer_html( $customer_id, $affiliate_id ) {

		ob_start();

		$customer = affwp_get_customer( $customer_id );

		if ( ! $customer ) {
			return false;
		}

		echo '<li id="customer-' . esc_attr( $customer->customer_id ) . '">';

		$edit_user_link = get_edit_user_link( $customer->user_id );

		if ( $customer->user_id && $edit_user_link ) {

			/* translators: 1: Edit user link, 2: Customer Name, 3: Customer Email */
			echo sprintf( __( '<a href="%1$s">%2$s (%3$s)</a>' ), esc_url( $edit_user_link ), $customer->get_name(), $customer->email );

		} else {

			echo $customer->get_name() . ' (' . $customer->email . ')';

		}

		echo ' | ' . $this->delink_url( $affiliate_id, $customer_id );

		echo '</li>';

		return ob_get_clean();
	}

	/**
	 * Retrieves the displayed name and email combination for templates.
	 *
	 * @since 1.5
	 *
	 * @param $customer int|\AffWP\Customer The customer from which the name should be retrieved.
	 * @return string output of the name, and email address if both exist. If the customer has no name, just the email.
	 */
	private function get_display_name( $customer ) {
		$customer      = affwp_get_customer( $customer );
		$name_or_email = $customer->get_name();

		// If get_name returns the email address, just return the email address.
		if ( $customer->email === $name_or_email ) {
			return $name_or_email;

			// Otherwise, return the name and email.
		} else {
			return $name_or_email . ' (' . $customer->email . ')';
		}
	}

	/**
	 * Generates the HTML to create the delink URL
	 *
	 * @since 1.5
	 *
	 * @param $affiliate_id int The affiliate ID to delink
	 * @param $customer_id  int The customer ID to delink
	 * @return string The HTML output of the delink URL.
	 */
	private function delink_url( $affiliate_id, $customer_id ) {

		$unlink_url = wp_nonce_url( add_query_arg( array(
			'affwp_action' => 'lc_delink_customer',
			'affiliate_id' => $affiliate_id,
			'customer'     => $customer_id,
		) ), 'affwp-lc-delink-customer-nonce' );

		return '<a href="' . esc_url( $unlink_url ) . '">' . __( 'Unlink', 'affiliate-wp-lifetime-commissions' ) . '</a>';
	}

	/**
	 * Sanitizes referral rate.
	 * If empty, returns the global rate.
	 *
	 * @since 1.5
	 *
	 * @param mixed  $val Setting value.
	 * @param string $key Setting key.
	 * @return mixed The sanitized setting value.
	 */
	public function sanitize_referral_rate( $val, $key ) {
		if ( 'lifetime_commissions_lifetime_referral_rate' === $key && '' === $val ) {
			$val = affiliate_wp()->settings->get( 'referral_rate' );
		}

		return $val;
	}

}
new Affiliate_WP_Lifetime_Commissions_Admin;
