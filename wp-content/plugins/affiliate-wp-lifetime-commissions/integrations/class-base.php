<?php

class Affiliate_WP_Lifetime_Commissions_Base {

	public $context;
	public $email;
	public $customer;

	public function __construct() {

		// filter the affiliate ID
		add_filter( 'affwp_get_referring_affiliate_id', array( $this, 'get_affiliate_id' ), 99, 3 );

		// Create a customer record when user accounts are registered
		add_action( 'user_register', array( $this, 'create_customer_on_register' ), 10 );

		// Create a lifetime customer record when a referral is created.
		add_action( 'affwp_insert_referral', array( $this, 'create_lifetime_customer' ) );

		// Save the customer old email when their user profile is updated.
		add_action( 'profile_update', array( $this, 'update_customer_email' ), 10, 2 );

		// Don't save new customer record if the customer email belongs to an existing customer.
		add_filter( 'affwp_pre_insert_customer_data', array( $this, 'validate_new_customer_email' ) );

		// Delete the lifetime customer data if a customer is deleted.
		add_action( 'affwp_post_delete_customer', array( $this, 'delete_lifetime_customer_data' ) );

		$this->init();

	}

	/**
	 * Gets things started
	 *
	 * This is for integration-specific initiations and declarations
	 *
	 * @access  public
	 * @since   1.0
	 * @return  void
	 */
	public function init() {}


	/***********************************************************************
	 * The following methods are typically overwritten by each integration *
	 **********************************************************************/


	/**
	 * Retrieve the email address of a customer from the reference (order, payment, etc)
	 *
	 * @access  public
	 * @since   2.0
	 * @return  string
	 */
	public function get_email( $reference = 0 ) {
		return is_user_logged_in() ? wp_get_current_user()->user_email : '';
	}



	/*************************************************************************
	 * The following methods should typically not be changed by integrations *
	 *************************************************************************/

	public function get_affiliate_id( $affiliate_id, $reference, $context ) {

		$referral = affiliate_wp()->referrals->get_by_with_context( 'reference', $reference, $context );

		if ( $referral && 'draft' === $referral->status ) {
			return $affiliate_id;
		}

		if ( $this->context !== $context ) {
			return $affiliate_id;
		}

		if ( ! $this->is_lifetime_customer( $reference ) ) {
			return $affiliate_id;
		}

		$lc_affiliate_id = $this->get_lifetime_affiliate_id();

		if ( ! $this->is_lifetime_length_valid( $lc_affiliate_id ) ) {

			affiliate_wp()->utils->log( 'Referral failed to be created. Lifetime limit reached.' );

			return $affiliate_id;
		}

		if ( ! $this->can_receive_lifetime_commissions( $lc_affiliate_id ) ) {
			return $affiliate_id;
		}

		$this->filter_affiliate_rates();

		return $lc_affiliate_id;

	}

	public function filter_affiliate_rates() {

		// filter the affiliate rate
		add_filter( 'affwp_get_affiliate_rate', array( $this, 'set_lifetime_rate' ), 10, 4 );

		// set affiliate rate type
		add_filter( 'affwp_get_affiliate_rate_type', array( $this, 'set_lifetime_rate_type' ), 10, 2 );

		// set a flag for lifetime referrals
		add_filter( 'affwp_insert_pending_referral', array( $this, 'set_lifetime_referral_flag' ), 10, 8 );

	}

	/**
	 * Checks if the customer is a lifetime customer.
	 *
	 * @since 1.3
	 *
	 * @param int $reference Referral reference.
	 *
	 * @return bool True if customer is a lifetime customer, false otherwise.
	 */
	public function is_lifetime_customer( $reference = 0 ) {

		$ret         = false;
		$this->email = $this->get_email( $reference );

		if ( $this->email ) {
			$this->customer = $this->get_customer_by_email( $this->email );

			// Checks and save the new email for a logged in customer who is a lifetime customer for an affiliate.
			if ( ! $this->customer && is_user_logged_in() ) {

				$customer = affwp_get_customer( wp_get_current_user()->user_email );

				if ( $customer ) {
					$this->customer = $customer;

					// Save new email for the logged in customer to the customer meta.
					$this->maybe_add_email_to_customer( $customer->ID, $this->email );
				}
			}

			if ( $this->customer ) {

				$linked_affiliate = affiliate_wp_lifetime_commissions()->lifetime_customers->get_by(
					'affwp_customer_id',
					$this->customer->customer_id
				);

				if ( $linked_affiliate ) {
					$ret = true;
				}
			}
		}

		return apply_filters( 'affwp_lc_is_lifetime_customer', $ret, $this->email, $reference );
	}

	/**
	 * Get the lifetime affiliate ID for the customer.
	 *
	 * @since 1.4.1
	 *
	 * @return int|false Lifetime affiliate ID for the customer, false otherwise.
	 */
	public function get_lifetime_affiliate_id() {

		$lifetime_affiliate_id = affiliate_wp_lifetime_commissions()->lifetime_customers->get_column_by(
			'affiliate_id',
			'affwp_customer_id',
			$this->customer->ID
		);

		if ( ! $lifetime_affiliate_id ) {

			$customer = $this->get_customer_by_email( $this->email );

			if ( $customer instanceof AffWP\Customer ) {

				$lifetime_affiliate_id = affiliate_wp_lifetime_commissions()->lifetime_customers->get_column_by(
					'affiliate_id',
					'affwp_customer_id',
					$customer->customer_id
				);
			}
		}

		if ( ! $lifetime_affiliate_id && is_user_logged_in() ) {

			$customer = affwp_get_customer( wp_get_current_user()->user_email );

			if ( $customer ) {

				$lifetime_affiliate_id = affiliate_wp_lifetime_commissions()->lifetime_customers->get_column_by(
					'affiliate_id',
					'affwp_customer_id',
					$customer->ID
				);

			}
		}

		return $lifetime_affiliate_id;

	}

	/**
	 * Set lifetime referrals rate type
	 *
	 * @since 1.2
	 */
	public function set_lifetime_rate_type( $type, $affiliate_id ) {

		// Lifetime referral rates must be enabled.
		if ( ! $this->has_lifetime_referral_rates() ) {
			return $type;
		}

		// per-affiliate lifetime referral rate type
		$lifetime_affiliate_rate_type = $this->get_affiliate_lifetime_referral_rate_type( $affiliate_id );

		// lifetime referral rate type from Affiliates -> Settings -> Integrations
		$lifetime_referral_rate_type = affiliate_wp()->settings->get( 'lifetime_commissions_lifetime_referral_rate_type' );

		if ( $lifetime_affiliate_rate_type ) {
			$type = $lifetime_affiliate_rate_type;
		} elseif ( $lifetime_referral_rate_type ) {
			$type = $lifetime_referral_rate_type;
		}

		return $type;

	}

	/**
	 * Set a flag for lifetime referrals
	 *
	 * @since 1.2.1
	 */
	public function set_lifetime_referral_flag( $args, $amount, $reference, $description, $affiliate_id, $visit_id, $data, $context ) {

		// create our array of data
		$data = array( 'lifetime_referral' => true );

		// custom data already exists
		if ( $args['custom'] ) {
			// unserialize it so we can add our value to the array
			$args['custom'] = maybe_unserialize( $args['custom'] );

			// merge the two arrays together
			$data = array_merge( $data, $args['custom'] );
		}

		// serialize the $data array
		$args['custom'] = maybe_serialize( $data );

		return $args;

	}

	/**
	 * Determine whether lifetime referral rates are enabled
	 *
	 * @since 1.2
	 */
	public function has_lifetime_referral_rates() {

		$enabled = affiliate_wp()->settings->get( 'lifetime_commissions_enable_lifetime_referral_rates' );

		if ( $enabled ) {
			return true;
		}

		return false;

	}

	/**
	 * Get the affiliate's lifetime rate.
	 *
	 * @since 1.2
	 *
	 * @param $affiliate_id
	 *
	 * @return float
	 */
	public function get_lifetime_rate( $affiliate_id ) {

		// lifetime referral rates must be enabled
		if ( ! $this->has_lifetime_referral_rates() ) {
			return false;
		}

		// get global lifetime rate
		$rate = affiliate_wp()->settings->get( 'lifetime_commissions_lifetime_referral_rate' );

		// get per affiliate rate
		$lifetime_affiliate_rate = $this->get_affiliate_lifetime_referral_rate( $affiliate_id );

		if ( is_numeric( $lifetime_affiliate_rate ) ) {
			$rate = $lifetime_affiliate_rate;
		}

		$type = affwp_get_affiliate_rate_type( $affiliate_id );

		if ( $type === 'percentage' ) {
			$rate /= 100;
		}

		/**
		 * Filter the lifetime rate for an affiliate.
		 *
		 * This could be used in the future to provide a per affiliate lifetime rate.
		 *
		 * @since 1.2
		 *
		 * @param float $rate
		 * @param int   $affiliate_id
		 */
		$rate = apply_filters( 'affwp_lc_lifetime_referral_rate', $rate, $affiliate_id );

		return affwp_sanitize_referral_rate( $rate );
	}

	/**
	 * Get the lifetime referral rate for an affiliate
	 *
	 * @since 1.2
	 */
	public function get_affiliate_lifetime_referral_rate( $affiliate_id = 0 ) {

		// get per affiliate rate
		$rate = affwp_get_affiliate_meta( $affiliate_id, 'affwp_lc_lifetime_referral_rate', true );

		if ( is_numeric( $rate ) ) {
			return $rate;
		}

		return false;

	}

	/**
	 * Get the lifetime referral rate type for an affiliate
	 *
	 * @since 1.2
	 */
	public function get_affiliate_lifetime_referral_rate_type( $affiliate_id = 0 ) {

		// get per affiliate rate type
		$rate_type = affwp_get_affiliate_meta( $affiliate_id, 'affwp_lc_lifetime_referral_rate_type', true );

		if ( $rate_type ) {
			return $rate_type;
		}

		return false;

	}

	/**
	 * Get the lifetime length for an affiliate
	 *
	 * @since 1.3
	 */
	public function get_affiliate_lifetime_length( $affiliate_id = 0 ) {

		// get per affiliate lifetime length
		$length = affwp_get_affiliate_meta( $affiliate_id, 'affwp_lc_lifetime_length', true );

		if ( $length !== '' ) {
			return $length;
		}

		return false;

	}

	/**
	 * Change the affiliate's rate if lifetime commissions rate is set for the affiliate
	 *
	 * @since 1.2
	 */
	public function set_lifetime_rate( $rate, $affiliate_id, $type, $reference ) {

		$lifetime_rate = $this->get_lifetime_rate( $affiliate_id );

		// has lifetime rate
		if ( is_numeric( $lifetime_rate ) ) {
			$rate = $lifetime_rate;
		}

		return $rate;
	}

	/**
	 * Can the affiliate receive lifetime commissions?
	 *
	 * @since  1.0
	 * @todo add to affiliate meta table and provide backwards compatibility
	 */
	public function can_receive_lifetime_commissions( $affiliate_id = 0 ) {

		// get global setting
		$global_lifetime_commissions_enabled = affiliate_wp()->settings->get( 'lifetime_commissions' );

		// all affiliates can earn lifetime commissions
		if ( $global_lifetime_commissions_enabled ) {
			return true;
		}

		return (bool) affwp_get_affiliate_meta( $affiliate_id, 'affwp_lc_enabled', true );

	}

	/**
	 * Get an affiliate's ID from a customer's email address
	 *
	 * @since  1.0
	 *
	 * @param  string $email The customer's email address.
	 * @return int|false Affiliate ID, false otherwise.
	 */
	public function get_affiliate_id_from_customer_email( $email = '' ) {

		if ( ! $email ) {
			return false;
		}

		$lifetime_affiliate_id = false;
		$customer              = $this->get_customer_by_email( $email );

		if ( $customer instanceof AffWP\Customer ) {

			$lifetime_affiliate_id = affiliate_wp_lifetime_commissions()->lifetime_customers->get_column_by(
				'affiliate_id',
				'affwp_customer_id',
				$customer->ID
			);
		}

		return $lifetime_affiliate_id;
	}

	/**
	 * Creates a customer record if referred when user account is registered
	 *
	 * @since 1.2.1
	 * @param int $user_id
	 */
	public function create_customer_on_register( $user_id ) {

		// Check if a new customer can be automatically linked to an affiliate on registration.
		if ( ! affiliate_wp()->settings->get( 'lifetime_commissions_link_customers_on_registration' ) ) {
			return;
		}

		// get referring affiliate ID
		$referring_affiliate_id = affiliate_wp()->tracking->get_affiliate_id();

		if ( $referring_affiliate_id ) {

			$user = get_userdata( $user_id );

			// store the affiliate ID with the user.
			$customer = $this->get_customer_by_email( $user->user_email );

			if ( ! $customer ) {

				$args = array(
					'affiliate_id' => $referring_affiliate_id,
					'first_name'   => $user->first_name,
					'last_name'    => $user->last_name,
					'user_id'      => $user->ID,
					'email'        => $user->user_email,
					'ip'           => affiliate_wp()->tracking->get_ip()
				);

				$customer_id = affwp_add_customer( $args );

				/**
				 * Create lifetime customer record.
				 *
				 * @since 1.4.1
				 */
				if ( $customer_id ) {

					$args = array(
						'affwp_customer_id' => $customer_id,
						'affiliate_id'      => $referring_affiliate_id,
					);

					affiliate_wp_lifetime_commissions()->lifetime_customers->add( $args );
				}
			}

		}

	}

	/**
	 * Get all customers for an affiliate
	 *
	 * @since 1.3
	 *
	 * @param int $affiliate_id The affiliate ID.
	 * @return array An array of customer IDs.
	 */
	public function get_customers_for_affiliate( $affiliate_id ) {

		$customers = array();

		$args = array(
			'affiliate_id' => $affiliate_id,
			'fields'       => 'affwp_customer_id',
			'number'       => -1,
		);

		$lifetime_customers = affiliate_wp_lifetime_commissions()->lifetime_customers->get_lifetime_customers( $args );

		if ( ! empty( $lifetime_customers ) ) {

			foreach ( $lifetime_customers as $customer_id ) {
				$customers[] = affwp_get_customer( $customer_id );
			}
		}

		return $customers;

	}

	/**
	 * Get the lifetime commission expiration length
	 *
	 * @since  1.3
	 * @param  int $affiliate_id The affiliate ID
	 * @return int $days The number of days lifetime commissions can be created
	 */
	public function get_lifetime_length( $affiliate_id = 0 ) {

		$length = affiliate_wp()->settings->get( 'lifetime_commissions_lifetime_length', 0 );

		// get per affiliate lifetime length
		$lifetime_affiliate_length = $this->get_affiliate_lifetime_length( $affiliate_id );

		if ( $lifetime_affiliate_length !== false ) {
			$length = $lifetime_affiliate_length;
		}

		/**
		 * Filter the lifetime commissions length for an affiliate.
		 *
		 * This could be used in the future to provide a per affiliate lifetime expiration length.
		 *
		 * @since 1.3
		 *
		 * @param int $length The number of days lifetime commissions can be created
		 * @param int $affiliate_id The affiliate ID
		 */
		return apply_filters( 'affwp_lc_expiration_length', $length, $affiliate_id );

	}

	/**
	 * Check if the lifetime commissions length hasn't been reached
	 *
	 * @since  1.3
	 * @param  int $affiliate_id The affiliate ID
	 * @return bool
	 */
	public function is_lifetime_length_valid( $affiliate_id = 0 ) {

		$length = $this->get_lifetime_length( $affiliate_id );

		if ( $length !== '' ) {

			if ( $length == 0 ) {
				$lifetime_commissions_length = '1970-01-01 00:00:00';
			} else {
				$lifetime_commissions_length = date( 'Y-m-d H:i:s', strtotime( '-' . $length . ' days' ) );
			}

			$registration_date = date( 'Y-m-d H:i:s', strtotime( $this->customer->date_created ) );

			if ( $registration_date < $lifetime_commissions_length ) {

				return false;

			}

		}

		return true;

	}

	/**
	 * Creates a lifetime customer record when a referral is created.
	 *
	 * @since  1.4.1
	 * @param  int $referral_id The referral ID.
	 * @return int|false Lifetime customer ID if successfully created, false otherwise.
	 */
	public function create_lifetime_customer( $referral_id ) {

		$referral = affwp_get_referral( $referral_id );

		if ( $referral ) {

			$global_lc_enabled    = affiliate_wp()->settings->get( 'lifetime_commissions' );
			$affiliate_lc_enabled = affwp_get_affiliate_meta( $referral->affiliate_id, 'affwp_lc_enabled', true );
			$lc_enabled           = ( $global_lc_enabled || ( ! $global_lc_enabled && $affiliate_lc_enabled ) );

			if ( $lc_enabled ) {

				$lifetime_customer = affiliate_wp_lifetime_commissions()->lifetime_customers->get_by( 'affwp_customer_id', $referral->customer_id );

				if ( ! $lifetime_customer ) {

					$args = array(
						'affwp_customer_id' => $referral->customer_id,
						'affiliate_id'      => $referral->affiliate_id,
					);

					return affiliate_wp_lifetime_commissions()->lifetime_customers->add( $args );
				}
			}
		}

		return false;
	}

	/**
	 * Save the customer old email when their user profile is updated.
	 *
	 * @since  1.4.1
	 *
	 * @param int     $user_id       User ID.
	 * @param WP_User $old_user_data Old user's data.
	 * @return void.
	 */
	public function update_customer_email( $user_id, $old_user_data ) {

		$customer = affiliate_wp()->customers->get_by( 'user_id', $user_id );

		if ( $customer ) {
			$user = get_user_by( 'id', $user_id );

			if ( $customer->email !== $user->user_email ) {

				// Save old customer email in the customer meta.
				$this->maybe_add_email_to_customer( $customer->customer_id, $old_user_data->data->user_email );
			}
		}

	}

	/**
	 * Maybe add old customer email to the customer meta if it doesn't exist.
	 *
	 * @since 1.4.1
	 *
	 * @param int    $customer_id Customer ID.
	 * @param string $user_email  User email.
	 *
	 * @return void.
	 */
	public function maybe_add_email_to_customer( $customer_id = 0, $user_email = '' ) {

		$customer = affwp_get_customer( $customer_id );

		if ( $customer && $user_email !== $customer->email ) {

			if ( ! in_array( $user_email, $this->get_customer_emails( $customer_id ) ) ) {
				affwp_add_customer_meta( $customer_id, 'affwp_lc_customer_email', $user_email );
			}

		}

	}

	/**
	 * Get array of customer's old email addresses
	 *
	 * @since  1.4.1
	 *
	 * @param int $customer_id Customer ID.
	 * @return array|false old customer emails, otherwise false.
	 */
	public function get_customer_emails( $customer_id = 0 ) {

		if ( ! $customer_id ) {
			return false;
		}

		$emails = affwp_get_customer_meta( $customer_id, 'affwp_lc_customer_email' );

		return (array) $emails;
	}

	/**
	 * Checks if the new customer email is not currently linked to an existing customer.
	 * This will prevent a new customer from being created if the customer email is linked to an existing customer.
	 *
	 * @since  1.4.1
	 *
	 * @param array $customer_data New customer data.
	 * @return false|array New customer data if customer email doesn't belong to an existing customer, false otherwise.
	 */
	public function validate_new_customer_email( $customer_data ) {

		$customer = $this->get_customer_by_email( $customer_data['email'] );

		if ( $customer instanceof AffWP\Customer ) {
			return false;
		}

		return $customer_data;
	}

	/**
	 * Delete all data for a lifetime customer if a customer is deleted.
	 *
	 * @since  1.4.1
	 *
	 * @param int $customer_id Deleted customer ID.
	 * @return void.
	 */
	public function delete_lifetime_customer_data( $customer_id ) {

		affwp_delete_customer_meta( $customer_id, 'affwp_lc_customer_email' );

		$lifetime_customer = affiliate_wp_lifetime_commissions()->lifetime_customers->get_by( 'affwp_customer_id', $customer_id );

		if ( $lifetime_customer ) {
			affiliate_wp_lifetime_commissions()->lifetime_customers->delete( $lifetime_customer->lifetime_customer_id );
		}
	}

	/**
	 * Retrieves the customer meta row from the provided lifetime customer email address.
	 *
	 * @since 1.4.3
	 *
	 * @param string $email The email address to lookup
	 * @return AffWP\Customer|false The customer, if found. Otherwise, false.
	 */
	public function get_customer_by_email( $email ) {
		$customer = affwp_get_customer( $email );

		if ( false === $customer && method_exists( affiliate_wp()->customer_meta, 'get_meta_by_value' ) ) {
			$meta = affiliate_wp()->customer_meta->get_meta_by_value( 'affwp_lc_customer_email', $email );

			if ( false !== $meta ) {
				$customer = affwp_get_customer( $meta->affwp_customer_id );
			}
		}

		return $customer;
	}
}
