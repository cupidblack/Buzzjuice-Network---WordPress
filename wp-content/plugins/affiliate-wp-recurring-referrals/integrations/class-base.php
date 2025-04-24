<?php

abstract class Affiliate_WP_Recurring_Base {

	/**
	 * The context of the referral.
	 * This is usually the name of the AffiliateWP integration from which a referral was generated.
	 *
	 * @var string $context The referral context.
	 */
	public $context;

	/**
	 * The affiliate ID.
	 *
	 * @var integer $affiliate_id
	 */
	public $affiliate_id;

	/**
	 * Construct class.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Gets things started.
	 *
	 * @access public
	 * @since  1.0
	 * @return void
	 */
	public function init() {
	}

	/**
	 * Determines if a signup was referred.
	 *
	 * @access public
	 * @since  1.0
	 * @return bool
	 */
	public function was_signup_referred() {
		return false;
	}

	/**
	 * Inserts a pending referral for a subscription payment.
	 *
	 * @access public
	 * @since  1.0
	 * @return int|false  Referral ID if successfully added, false otherwise.
	 */
	public function insert_referral( $args = array() ) {

		/**
		 * Allow extensions to prevent recurring referrals from being created,
		 * as well as filter the referral arguments array itself.
		 *
		 * @param bool          True if recurring referrals should generate a referral, otherwise false.
		 *                      Defaults to true.
		 * @param array  $args  Referral arguments. Specify an array of referral data.
		 * @param object $this  Instance of the Affiliate_WP_Recurring_Base class.
		 * @since 1.6
		 */
		if ( ! (bool) apply_filters( 'affwp_recurring_create_referral', true, $args, $this ) ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because affwp_recurring_create_referral set to false.' );
			return false;
		}

		if ( function_exists( 'affwp_get_affiliate_meta' ) && affwp_get_affiliate_meta( $args['affiliate_id'], 'recurring_disabled', true ) ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because recurring_disabled meta flag set on affiliate.' );
			return false;
		}

		if ( affiliate_wp()->referrals->get_by( 'reference', $args['reference'], $this->context ) ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because a referral with the provided reference, ' . $args['reference'] . ', already exists.' );
			return false; // Referral already created for this reference
		}

		if ( ! affiliate_wp()->tracking->is_valid_affiliate( $args['affiliate_id'] ) ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because invalid affiliate ID provided, ' . $args['affiliate_id'] . ', is not valid.' );
			return false; // Affiliate is not valid
		}

		if ( 0 == $args['amount'] && affiliate_wp()->settings->get( 'ignore_zero_referrals' ) ) {
			affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because amount is zero and ignore_zero_referrals is enabled.' );
			return false; // Ignore a zero amount referral
		}

		$referral_args = array(
			'amount'       => $args['amount'],
			'reference'    => $args['reference'],
			'description'  => $args['description'],
			'affiliate_id' => $args['affiliate_id'],
			'context'      => $this->context,
			'custom'       => ! empty( $args['custom'] ) ? $args['custom'] : ''
		);

		// Save the parent referral id if AffiliateWP version is greater than 2.2.8.
		if ( version_compare( AFFILIATEWP_VERSION, '2.2.9', '>=' ) ) {
			$referral_args['parent_id'] = isset( $args['parent_id'] ) ? $args['parent_id'] : 0;
		}

		affiliate_wp()->utils->log( sprintf( 'Recurring Referrals: Arguments being sent to DB: ' . var_export( $referral_args, true ) ) );

		/**
		 * Defines the referral data to be inserted when creating a recurring referral.
		 * Specify an array of referral properties to use this filter.
		 *
		 * @param  array  $referral_args Referral object arguments array.
		 * @param  object $this          Instance of the Affiliate_WP_Recurring_Base class.
		 *
		 * @since  1.6
		 */
		$referral_id = affiliate_wp()->referrals->add( apply_filters( 'affwp_insert_pending_recurring_referral', $referral_args, $this ) );

		if ( $referral_id ) {

			affiliate_wp()->utils->log( sprintf( 'Recurring Referrals: Recurring referral #%d inserted successfully.', $referral_id ) );

		} else {

			affiliate_wp()->utils->log( sprintf( 'Recurring Referrals: Recurring referral could not be created due to an error. Args: ' . var_export( $referral_args, true ) ) );

		}

		return $referral_id;

	}

	/**
	 * Marks a referral as complete.
	 *
	 * @access public
	 * @since  1.0
	 * @return bool
	 */
	public function complete_referral( $referral_id = 0 ) {

		if ( empty( $referral_id ) ) {
			return false;
		}

		if ( affwp_set_referral_status( $referral_id, 'unpaid' ) ) {

			/**
			 * Fires when a recurring referral is marked complete.
			 *
			 * @param $referral_id The ID of the completed referral.
			 * @since 1.0
			 */
			do_action( 'affwp_complete_recurring_referral', $referral_id );

			affiliate_wp()->utils->log( sprintf( 'Recurring Referrals: Recurring referral #%d set to Unpaid successfully', $referral_id ) );

			return true;
		}

		return false;

	}

	/**
	 * Calculates the referral amount for a subscription payment.
	 *
	 * @access public
	 * @since  1.5.7
	 *
	 * @param string     $base_amount         Optional. Base amount to calculate the referral amount from.
	 *                                        Default empty.
	 * @param string|int $reference           Optional. Referral reference (usually the order ID). Default empty.
	 * @param int        $parent_id           Parent referral ID
	 * @param int        $product_id          Optional. Product ID. Default 0.
	 * @param int        $affiliate_id        Optional. Affiliate ID.
	 *
	 * @return float The calculated referral amount
	 */
	public function calc_referral_amount( $base_amount = '', $reference = '', $parent_id, $product_id = 0, $affiliate_id = 0 ) {

		$referral_amount = '';

		if ( empty( $this->affiliate_id ) ) {
			$this->affiliate_id = $affiliate_id;
		}

		if ( version_compare( AFFILIATEWP_VERSION, '2.2.9', '>=' ) && get_option( 'affwp_rr_migrate_parent_id' ) ) {

			// Check if the recurring referral limit hasn't been reached yet
			if ( $parent_id && $this->is_at_limit( $parent_id, $product_id ) ) {
				affiliate_wp()->utils->log( 'Recurring Referrals: Referral not created because recurring referral limit has been reached.' );
				return false; // Affiliate is not valid
			}

		}

		// Get rate in order of priority: Per Affiliate Per Product Recurring Rate -> Affiliate Recurring Rate -> Tiered Rate -> Product Recurring Rate -> Global Recurring Rate

		if ( ! empty( $product_id )  ) {

			// Get the global recurring rate, fallback to global rate.
			$rate = affiliate_wp()->settings->get( 'recurring_rate' );

			if ( is_numeric( $rate ) ) {

				$default_rate_type = affiliate_wp()->settings->get( 'rate_type', 'percentage' );

				$type = affiliate_wp()->settings->get( 'recurring_rate_type', $default_rate_type );

				if ( ! empty( $rate ) && 'flat' !== $type ) {
					$rate /= 100;
				}

			} else {

				$rate = affwp_get_affiliate_rate( $affiliate_id );

				$type = affwp_get_affiliate_rate_type( $affiliate_id );

			}

			// Get product-specific recurring rate.
			$get_recurring_product_rate = $this->get_recurring_product_rate( $product_id, $args = array( 'reference' => $reference, 'affiliate_id' => $affiliate_id ) );

			if ( is_numeric( $get_recurring_product_rate ) ) {

				$rate = $get_recurring_product_rate;

				$type = $this->get_recurring_product_rate_type( $product_id, $args = array( 'reference' => $args['reference'], 'affiliate_id' => $affiliate_id ) );

			}

			// Get the tiered rate if the Tiered Rates add-on is activated and Tiered Rates is enabled in Recurring Referrals.
			if ( class_exists( 'AffiliateWP_Tiered_Rates' ) && affiliate_wp()->settings->get('recurring_rate_tiered_rates_enabled' ) ) {

				$affiliate_rate = affiliate_wp()->affiliates->get_column( 'rate', $this->affiliate_id );

				// The tiered rate is used only when the per-affiliate referral rate is not set.
				if ( empty ( $affiliate_rate ) ) {

					$type = affwp_get_affiliate_meta( $this->affiliate_id, 'recurring_rate_type', true );

					if ( empty( $type ) ) {

						$default_rate_type = affiliate_wp()->settings->get( 'rate_type', 'percentage' );

						$type = affiliate_wp()->settings->get( 'recurring_rate_type', $default_rate_type );

					}

					$rate = affiliate_wp_tiers()->get_affiliate_rate( $rate, $this->affiliate_id, $type );

				}

			}

			// Get affiliate recurring rate.
			$affiliate_recurring_rate = affwp_get_affiliate_meta( $this->affiliate_id, 'recurring_rate', true );

			if ( is_numeric( $affiliate_recurring_rate ) ) {

				$rate = affwp_abs_number_round( $affiliate_recurring_rate );

				$type = affwp_get_affiliate_meta( $this->affiliate_id, 'recurring_rate_type', true );

				if ( empty( $type ) ) {

					$type = affiliate_wp()->settings->get( 'recurring_rate_type' );

					if ( empty( $type ) ) {

						$type = affwp_get_affiliate_rate_type( $this->affiliate_id );

					}

				}

				if ( is_numeric( $rate ) && 'flat' !== $type ) {
					$rate /= 100;
				}

			}

			// Get per affiliate per product recurring rate.
			$get_affiliate_recurring_product_rate = $this->get_affiliate_recurring_product_rate( $product_id, $args = array( 'reference' => $reference, 'affiliate_id' => $affiliate_id ) );

			if ( is_numeric( $get_affiliate_recurring_product_rate ) ) {

				$rate = $get_affiliate_recurring_product_rate;

				$type = $this->get_affiliate_recurring_product_rate_type( $product_id, $args = array( 'reference' => $args['reference'], 'affiliate_id' => $affiliate_id ) );

			}

		}  else {

			// The default rate set if no product id is passed.
			$rate = $this->get_referral_rate();
			$type = $this->get_referral_rate_type();

		}

		$decimals = affwp_get_decimal_count();

		$referral_amount = ( 'percentage' === $type ) ? round( $base_amount * $rate, $decimals ) : $rate;

		if ( $referral_amount < 0 ) {
			$referral_amount = 0;
		}

		return $referral_amount;
	}

	/**
	 * Retrieves the referral rate for a subscription payment.
	 *
	 * @access public
	 * @since  1.5
	 * @return float
	 */
	public function get_referral_rate() {

		$rate      = affwp_get_affiliate_meta( $this->affiliate_id, 'recurring_rate', true );
		$rate      = affwp_abs_number_round( $rate );
		$rate_type = $this->get_referral_rate_type();

		if ( ! empty( $rate ) && 'flat' !== $rate_type ) {
			$rate /= 100;
		}

		if ( empty( $rate ) ) {

			$rate = affiliate_wp()->settings->get( 'recurring_rate' );

			// I hate this duplication but meh
			if ( ! empty( $rate ) && 'flat' !== $rate_type ) {
				$rate /= 100;
			}

			if ( empty( $rate ) ) {

				$rate = affwp_get_affiliate_rate( $this->affiliate_id, false, $rate );

				if (
					! empty( $rate ) &&
					'flat' !== $rate_type &&
					'flat' === affwp_get_affiliate_rate_type( $this->affiliate_id )
				) {
					$rate /= 100;
				}
			}

			if ( class_exists( 'AffiliateWP_Tiered_Rates' ) && affiliate_wp()->settings->get( 'recurring_rate_tiered_rates_enabled' ) ) {

				$affiliate_rate = affiliate_wp()->affiliates->get_column( 'rate', $this->affiliate_id );

				// The tiered rate is used only when the per-affiliate referral rate is not set.
				if ( empty ( $affiliate_rate ) ) {

					$rate = affiliate_wp_tiers()->get_affiliate_rate( $rate, $this->affiliate_id, $rate_type );

				}

			}

		}

		/**
		 * Sets the recurring referral rate for an affiliate.
		 * To use, specify the affiliate ID and desired recurring rate for the affiliate.
		 *
		 * @param int     $rate          The recurring referral rate.
		 * @param int     $affiliate_id  The affiliate ID.
		 * @param string  $context       The context of the referral.
		 *
		 * @since 1.5
		 */
		return apply_filters( 'affwp_get_recurring_referral_rate', $rate, $this->affiliate_id, $this->context );

	}

	/**
	 * Retrieves the referral rate type for a subscription payment.
	 *
	 * @access public
	 * @since  1.6
	 * @return float
	 */
	public function get_referral_rate_type() {

		$rate_type = affwp_get_affiliate_meta( $this->affiliate_id, 'recurring_rate_type', true );

		if ( empty( $rate_type ) ) {

			$rate_type = affiliate_wp()->settings->get( 'recurring_rate_type' );

			if ( empty( $rate_type ) ) {

				$rate_type = affwp_get_affiliate_rate_type( $this->affiliate_id );

			}

			if ( class_exists( 'AffiliateWP_Tiered_Rates' ) && affiliate_wp()->settings->get( 'recurring_rate_tiered_rates_enabled' ) ) {

				$affiliate_rate = affiliate_wp()->affiliates->get_column( 'rate', $this->affiliate_id );

				// The tiered rate is used only when the per-affiliate referral rate is not set.
				// The tiered rate is calculated using the affiliate rate type. This will ensure that the recurring rate type is not passed.
				if ( empty ( $affiliate_rate ) ) {

					$rate_type = affwp_get_affiliate_rate_type( $this->affiliate_id );

				}

			}

		}
		/**
		 * Sets the recurring referral rate type for an affiliate.
		 * To use, specify the affiliate ID and recurring rate type (either flat or recurring).
		 * To see available affiliate rate types, use `affwp_get_affiliate_rate_types`.
		 *
		 * @param int     $rate_type     The recurring referral rate type.
		 * @param int     $affiliate_id  The affiliate ID.
		 * @param  string $context       The context of the referral.
		 *
		 * @since 1.6
		 */
		return apply_filters( 'affwp_get_recurring_referral_rate_type', $rate_type, $this->affiliate_id, $this->context );

	}

	/**
	 * Retrieves the recurring referral rate for a specific product.
	 *
	 * @access public
	 * @since  1.7
	 *
	 * @param int   $product_id Optional. Product ID. Default 0.
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

		$args = wp_parse_args( $args, array(
			'reference'    => '',
			'affiliate_id' => 0,
		) );

		$affiliate_id = isset( $args['affiliate_id'] ) ? $args['affiliate_id'] : $this->affiliate_id;

		$rate = get_post_meta( $product_id, '_affwp_' . $this->context . '_recurring_product_rate', true );
		$rate = affwp_abs_number_round( $rate );

		$type = $this->get_recurring_product_rate_type( $product_id, $args = array( 'reference' => $args['reference'], 'affiliate_id' => $affiliate_id ) );

		if ( is_numeric( $rate ) && 'flat' !== $type ) {
			$rate /= 100;
		}

		/**
		 * Filters the integration recurring product rate.
		 *
		 * @since 1.7
		 *
		 * @param float  $rate         Product-level recurring referral rate.
		 * @param int    $product_id   Product ID.
		 * @param array  $args         Arguments for retrieving the recurring product rate.
		 * @param int    $affiliate_id Affiliate ID.
		 * @param string $context      Order context.
		 */
		return apply_filters( 'affwp_get_recurring_product_rate', $rate, $product_id, $args, $affiliate_id, $this->context );
	}

	/**
	 * Retrieves the recurring referral rate type for a specific product.
	 *
	 * @access public
	 * @since  1.7
	 *
	 * @param int   $product_id Optional. Product ID. Default 0.
	 * @param array $args {
	 *      Optional. Arguments for getting the product recurring rate.
	 *
	 *      @type string|int $reference    Optional. Referral reference (usually the order ID). Default empty.
	 *      @type int        $affiliate_id Optional. Affiliate ID.
	 * }
	 *
	 * @return float The recurring referral product rate type.
	 */
	public function get_recurring_product_rate_type( $product_id = 0, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'reference'    => '',
			'affiliate_id' => 0,
		) );

		$affiliate_id = isset( $args['affiliate_id'] ) ? $args['affiliate_id'] : $this->affiliate_id;

		$type = get_post_meta( $product_id, '_affwp_' . $this->context . '_recurring_product_rate_type', true );

		if ( empty( $type ) ) {

			$type = affwp_get_affiliate_meta( $affiliate_id, 'recurring_rate_type', true );

			if ( empty ( $type ) ) {

				$type = affiliate_wp()->settings->get( 'recurring_rate_type' );

				if ( empty( $type ) ) {

					$type = affwp_get_affiliate_rate_type( $affiliate_id );

				}

			}

		}

		/**
		 * Filters the integration recurring product rate type.
		 *
		 * @since 1.7
		 *
		 * @param float  $type         Product-level recurring referral rate type.
		 * @param int    $product_id   Product ID.
		 * @param array  $args         Arguments for retrieving the recurring product rate type.
		 * @param int    $affiliate_id Affiliate ID.
		 * @param string $context      Order context.
		 */
		return apply_filters( 'affwp_get_recurring_product_rate_type', $type, $product_id, $args, $affiliate_id, $this->context );
	}

	/**
	 * Retrieves the per affiliate recurring referral rate for a specific product.
	 *
	 * @access public
	 * @since  1.7
	 *
	 * @param int   $product_id Optional. Product ID. Default 0.
	 * @param array $args {
	 *      Optional. Arguments for getting per affiliate recurring referral rate for a specific product.
	 *
	 *      @type string|int $reference    Optional. Referral reference (usually the order ID). Default empty.
	 *      @type int        $affiliate_id Optional. Affiliate ID.
	 * }
	 *
	 * @return float The per affiliate recurring referral rate for a specific product.
	 */
	public function get_affiliate_recurring_product_rate( $product_id = 0, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'reference'    => '',
			'affiliate_id' => 0,
		) );

		$affiliate_id = isset( $args['affiliate_id'] ) ? $args['affiliate_id'] : $this->affiliate_id;

		$rate = false;

		if ( $this->context ) {

			// Get the affiliate's recurring product rates.
			$products_rates = affiliate_wp_recurring()->get_affiliate_recurring_product_rates( $this->affiliate_id );

			$products_rates = isset( $products_rates[ $this->context ] ) ? $products_rates[ $this->context ] : false;

			if ( $products_rates ) {

				foreach ( $products_rates as $product_rate ) {
					// Product matches.
					if ( in_array( $product_id, $product_rate['products'] ) ) {

						$rate = $product_rate['rate'];

					}
				}
			}
		}

		$rate = affwp_abs_number_round( $rate );

		$type = $this->get_affiliate_recurring_product_rate_type( $product_id, $args = array( 'reference' => $args['reference'], 'affiliate_id' => $affiliate_id ) );

		if ( is_numeric( $rate ) && 'flat' !== $type ) {
			$rate /= 100;
		}

		/**
		 * Filters the integration per affiliate recurring referral rate for a specific product.
		 *
		 * @since 1.7
		 *
		 * @param float  $rate         Product-level recurring referral rate.
		 * @param int    $product_id   Product ID.
		 * @param array  $args         Arguments for retrieving the per affiliate recurring referral rate for a specific product.
		 * @param int    $affiliate_id Affiliate ID.
		 * @param string $context      Order context.
		 */
		return apply_filters( 'affwp_get_affiliate_recurring_product_rate', $rate, $product_id, $args, $affiliate_id, $this->context );
	}

	/**
	 * Retrieves the per affiliate recurring referral rate type for a specific product.
	 *
	 * @access public
	 * @since  1.7
	 *
	 * @param int   $product_id Optional. Product ID. Default 0.
	 * @param array $args {
	 *      Optional. Arguments for getting per affiliate recurring referral rate type for a specific product.
	 *
	 *      @type string|int $reference    Optional. Referral reference (usually the order ID). Default empty.
	 *      @type int        $affiliate_id Optional. Affiliate ID.
	 * }
	 *
	 * @return float The per affiliate recurring referral rate type for a specific product.
	 */
	public function get_affiliate_recurring_product_rate_type( $product_id = 0, $args = array() ) {

		$args = wp_parse_args( $args, array(
			'reference'    => '',
			'affiliate_id' => 0,
		) );

		$affiliate_id = isset( $args['affiliate_id'] ) ? $args['affiliate_id'] : $this->affiliate_id;

		$type = affiliate_wp()->settings->get( 'recurring_rate_type' );

		if ( $this->context ) {

			// Get the affiliate's recurring product rates.
			$products_rates = affiliate_wp_recurring()->get_affiliate_recurring_product_rates( $this->affiliate_id );

			$products_rates = isset( $products_rates[ $this->context ] ) ? $products_rates[ $this->context ] : false;

			if ( $products_rates ) {

				foreach ( $products_rates as $product_rate ) {
					// Product matches.
					if ( in_array( $product_id, $product_rate['products'] ) ) {

						$type = $product_rate['type'];

					}
				}
			}
		}

		/**
		 * Filters the integration per affiliate recurring referral rate type for a specific product.
		 *
		 * @since 1.7
		 *
		 * @param float  $type         Product-level recurring referral rate type.
		 * @param int    $product_id   Product ID.
		 * @param array  $args         Arguments for retrieving the per affiliate recurring referral rate type for a specific product.
		 * @param int    $affiliate_id Affiliate ID.
		 * @param string $context      Order context.
		 */
		return apply_filters( 'affwp_get_affiliate_recurring_product_rate_type', $type, $product_id, $args, $affiliate_id, $this->context );
	}

	/**
	 * Returns whether or not the recurring referrals limit has been reached globally, per affiliate or per product.
	 *
	 * @since  1.7
	 *
	 * @param  integer $parent_id  Parent Referral ID.
	 * @param  integer $product_id Optional. Product ID. Default 0.
	 *
	 * @return boolean             True if the the recurring referrals limit has been reached.
	 */
	public function is_at_limit( $parent_id = 0, $product_id = 0 ) {

		$limit_enabled = affiliate_wp()->settings->get( 'recurring_referral_limit_enabled' );

		if ( ! $limit_enabled ) {
			return false;
		}

		$limit = $this->get_referral_limit( $product_id );
		$limit = absint( $limit );

		$recurring_referrals_count = $this->get_recurring_referral_count( $parent_id );
		$recurring_referrals_count = absint( $recurring_referrals_count );

		if ( $recurring_referrals_count >= $limit && $limit != 0 ) {
			return true;
		}

		return false;

	}

	/**
	 * Retrieves the total number of recurring referrals for a parent referral.
	 *
	 * @since  1.7
	 *
	 * @param  integer $parent_id Parent Referral ID.
	 *
	 * @return int     $count     The total number of recurring referrals for a parent referral.
	 */
	public function get_recurring_referral_count( $parent_id ) {

		$count = affiliate_wp()->referrals->count( array(
			'parent_id' => $parent_id,
			'context'   => $this->context,
			'status'    => array( 'paid', 'unpaid' )
		) );

		return $count;
	}

	/**
	 * Retrieves either the product recurring referral limit, affiliate recurring referral limit, or the global recurring referral limit.
	 * Priority is given to the per-product recurring referral limit.
	 *
	 * If none is set for the product or the affiliate, the global recurring referral referral limit is used.
	 *
	 * @param  integer $product_id  Optional. Product ID. Default 0.
	 *
	 * @since  1.7
	 * @return mixed int|bool  $limit The recurring referral limit. Returns 0 by default if not enabled or set.
	 */
	public function get_referral_limit( $product_id = 0 ) {

		$limit = affiliate_wp()->settings->get( 'recurring_referral_limit', 0 );

		// Get affiliate recurring referrals limit, fallback to global recurring referral limit.
		$get_affiliate_recurring_referral_limit = $this->get_affiliate_recurring_referral_limit();

		if ( is_numeric( $get_affiliate_recurring_referral_limit ) ) {

			$limit = $get_affiliate_recurring_referral_limit;

		}

		// Get product-specific recurring referral limit, fallback to global recurring referral limit.
		$get_recurring_product_limit = $this->get_recurring_product_referral_limit( $product_id );

		if ( is_numeric( $get_recurring_product_limit ) ) {

			$limit = $get_recurring_product_limit;

		}

		/**
		 * Filters the recurring referral limit.
		 *
		 * @since 1.7
		 *
		 * @param int    $limit        The affiliate recurring referral limit.
		 * @param int    $affiliate_id The affiliate ID.
		 * @param string $context      The context of the referral.
		 */
		return apply_filters( 'affwp_get_recurring_referral_limit', $limit, $this->affiliate_id, $this->context );
	}

	/**
	 * Retrieves the affiliate recurring referral limit.
	 *
	 * @access public
	 * @since  1.7
	 * @return mixed int|bool $limit The recurring referral limit. Returns false by default if not enabled or set.
	 */
	public function get_affiliate_recurring_referral_limit() {

		// Get per affiliate recurring referrals limit.
		$limit = affwp_get_affiliate_meta( $this->affiliate_id, 'recurring_referral_limit', true );

		/**
		 * Filters the affiliate recurring referral limit.
		 *
		 * @since 1.7
		 *
		 * @param int    $limit        The affiliate recurring referral limit.
		 * @param int    $affiliate_id The affiliate ID.
		 * @param string $context      The context of the referral.
		 */
		return apply_filters( 'affwp_get_affiliate_recurring_referral_limit', $limit, $this->affiliate_id, $this->context );
	}

	/**
	 * Retrieves the recurring referral limit for a specific product.
	 *
	 * @since  1.7
	 *
	 * @param  integer $product_id  Optional. Product ID. Default 0.
	 *
	 * @return mixed int|bool $limit The product recurring referral limit. Returns false by default if not enabled or set.
	 */
	public function get_recurring_product_referral_limit( $product_id ) {

		$limit = get_post_meta( $product_id, '_affwp_' . $this->context . '_recurring_referrals_limit', true );

		/**
		 * Filters the product recurring referral limit.
		 *
		 * @since 1.7
		 *
		 * @param float  $limit        Product-level recurring referral limit.
		 * @param int    $product_id   Product ID.
		 * @param int    $affiliate_id Affiliate ID.
		 * @param string $context      Order context.
		 */
		return apply_filters( 'affwp_get_recurring_product_referral_limit', $limit, $product_id, $this->affiliate_id, $this->context );
	}

}