<?php
/**
 * Multi Tier Main Controller
 *
 * Handles main logic.
 *
 * @package     AffiliateWP
 * @subpackage  AffiliateWP\MTC
 * @since       1.0.0
 * @copyright   Copyright (c) 2024, Awesome Motive, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

namespace AffiliateWP\MTC;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles different Multi-Tier Commissions admin functionalities.
 *
 * @since 1.0
 */
class Controller {

	/**
	 * Construct.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		if ( ! affiliate_wp_mtc()->is_activated() ) {
			return; // Multi-Tier Commissions is disabled.
		}

		$this->hooks();
	}

	/**
	 * Set necessary hooks.
	 *
	 * @since 1.0.0
	 */
	private function hooks() {

		// Maybe deactivate if is not a pro license.
		add_action( 'plugins_loaded', array( $this, 'restrict_to_pro' ) );

		// Register the new referral type at the 'init' action to ensure translations are loaded.
		add_action( 'init', array( $this, 'register_new_referral_type' ) );

		// Make the connection between affiliates.
		add_action( 'affwp_register_user', array( $this, 'save_referrer_id_to_affiliate' ) );
		add_action( 'affwp_add_new_affiliate', array( $this, 'save_referrer_id_to_affiliate' ) );

		// Create the tiered commissions.
		add_action( 'affwp_insert_referral', array( $this, 'generate_commissions' ) );

		// Keep referrals in sync. Make sure it runs in a later priority.
		add_action( 'affwp_post_update_referral', array( $this, 'sync_tiered_referrals_with_parent' ), 100, 2 );

		/*
		 * Tier rate is priority over the Site Default Rate, but not a priority over individual or group rates.
		 * Must run in a priority lower than -9999, which is used by affwp_maybe_override_affiliate_group_rate()
		 * and affwp_maybe_override_affiliate_group_rate_type() functions.
		 */
		add_filter( 'affwp_get_affiliate_rate', array( $this, 'maybe_set_tier_based_referral_rate' ), -10000, 2 );
		add_filter( 'affwp_get_affiliate_rate_type', array( $this, 'maybe_set_tier_based_referral_rate_type' ), -10000, 2 );
		add_filter( 'affwp_get_affiliate_flat_rate_basis', array( $this, 'maybe_set_tier_based_referral_rate_basis' ), -10000, 2 );
	}

	/**
	 * Limit Multi-Tier Commissions features to Pro licenses.
	 *
	 * If MTC was previously activated and the site is not using a Pro license anymore,
	 * deactivates preventing to run.
	 *
	 * @since 1.0.0
	 */
	public function restrict_to_pro() {

		if (
			! (
				affiliate_wp_mtc()->is_activated() &&
				affwp_is_upgrade_required( 'pro' )
			)
		) {
			return; // Not activated or is using a Pro license already.
		}

		// We need to retrieve all settings.
		$settings = get_option( 'affwp_settings' );

		if ( 0 === $settings['multi_tier_commissions'] ) {
			return; // Already deactivated.
		}

		// Deactivate Multi-Tier Commissions.
		$settings['multi_tier_commissions'] = 0;

		// Update the settings.
		affiliate_wp()->settings->set( $settings, true );
	}

	/**
	 * Register the new referral type.
	 *
	 * @since 1.0.0
	 */
	public function register_new_referral_type() {

		affiliate_wp()->referrals->types_registry->register_type(
			'tiered_sale',
			array(
				'label' => __( 'Sale (Tiered)', 'affiliatewp-multi-tier-commissions' ),
			)
		);
	}

	/**
	 * Hook to save the referrer ID to a new affiliate registration.
	 *
	 * @since 1.0.0
	 *
	 * @param int $affiliate_id The affiliate ID.
	 */
	public function save_referrer_id_to_affiliate( int $affiliate_id = 0 ) {

		$referrer_id = affiliate_wp()->tracking->get_affiliate_id();

		if ( empty( $referrer_id ) ) {
			return; // It has not being referred by anyone.
		}

		$referrer = affwp_get_affiliate( $referrer_id );

		if ( 'active' !== $referrer->status ) {
			return; // Only active affiliates can recruit new affiliates to their networks.
		}

		try {
			affiliate_wp_mtc()->network->connect_to_referrer( $affiliate_id, $referrer_id );
		} catch ( \Exception $error ) {
			affiliate_wp()->utils->log( 'Error when trying to refer an affiliate.', $error->getMessage() );
		}
	}

	/**
	 * Overwrite the default referral rate.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $rate The referral rate.
	 * @param mixed $affiliate_id The Affiliate ID.
	 * @return mixed
	 */
	public function maybe_set_tier_based_referral_rate( $rate, $affiliate_id ) {

		$affiliate = affwp_get_affiliate( $affiliate_id );

		// Custom rate has priority over the tier rate.
		if (
			is_a( $affiliate, '\AffWP\Affiliate' ) &&
			! empty( $affiliate->rate ) &&
			! empty( $affiliate->rate_type )
		) {
			return 'percentage' === $affiliate->rate_type
				? floatval( $affiliate->rate ) / 100
				: $affiliate->rate;
		}

		$tiers = affiliate_wp_mtc()->network->get_tiers_rates();

		if ( ! empty( $tiers[0]['rate'] ) ) {
			return 'percentage' === $tiers[0]['rate_type']
				? floatval( $tiers[0]['rate'] ) / 100
				: $tiers[0]['rate'];
		}

		return $rate;
	}

	/**
	 * Overwrite the default referral rate type.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $type The referral rate type.
	 * @param mixed $affiliate_id The Affiliate ID.
	 * @return mixed
	 */
	public function maybe_set_tier_based_referral_rate_type( $type, $affiliate_id ) {

		$affiliate = affwp_get_affiliate( $affiliate_id );

		// Custom rate has priority over the tier rate.
		if (
			is_a( $affiliate, '\AffWP\Affiliate' ) &&
			! empty( $affiliate->rate ) &&
			! empty( $affiliate->rate_type )
		) {
			return $affiliate->rate_type;
		}

		$tiers = affiliate_wp_mtc()->network->get_tiers_rates();

		if ( ! empty( $tiers[0]['rate'] ) ) {
			return 'percentage' === $tiers[0]['rate_type']
				? 'percentage'
				: 'flat';
		}

		return $type;
	}

	/**
	 * Overwrite the default referral rate basis.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $type The referral rate basis.
	 * @param mixed $affiliate_id The Affiliate ID.
	 * @return mixed
	 */
	public function maybe_set_tier_based_referral_rate_basis( $type, $affiliate_id ) {

		$affiliate = affwp_get_affiliate( $affiliate_id );

		// Custom rate has priority over the tier rate.
		if (
			is_a( $affiliate, '\AffWP\Affiliate' ) &&
			! empty( $affiliate->rate ) &&
			! empty( $affiliate->rate_type )
		) {
			return $affiliate->flat_rate_basis();
		}

		$tiers = affiliate_wp_mtc()->network->get_tiers_rates();

		if ( ! empty( $tiers[0]['rate_type'] ) ) {
			return ltrim( $tiers[0]['rate_type'], 'flat_' );
		}

		return $type;
	}

	/**
	 * Generate commissions based on the tiers when a new referral is added.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Introduced rate_type commissions and now uses calculate_tiered_referral_commission()
	 *               to calculate the tier commissions.
	 *
	 * @param mixed $referral_id The referral ID.
	 */
	public function generate_commissions( $referral_id ) {

		$referral = affwp_get_referral( $referral_id );

		if ( ! is_a( $referral, '\AffWP\Referral' ) ) {

			// Log the error so it can be easily debugged.
			affiliate_wp()->utils->log( sprintf( 'Failed to generate tiered commissions for referral_id %s, is not a valid Referral object', $referral_id ) );

			return; // Nothing to do since it is not a valid Referral object.
		}

		if ( 'sale' !== $referral->type ) {
			return; // Needs to be from sale type only.
		}

		// Get the tiers from our settings.
		$tiers = affiliate_wp_mtc()->network->get_tiers_rates();

		// Prevent infinite loops, as we will be adding more referrals.
		remove_action( 'affwp_insert_referral', array( $this, 'generate_commissions' ) );

		$ancestors = affiliate_wp_mtc()->network->get_affiliate_ancestors(
			$referral->affiliate_id,
			min(
				// The max number of tiers, based on how many were configured.
				count( $tiers ),
				// The max number of tiers to interact up to the limit defined by the MAX_REFERRAL_TIERS constant.
				affiliate_wp_mtc()->get_max_tiers()
			) - 1 // Minus one, since the original referral counts as the first one.
		);

		foreach ( $ancestors as $tier => $affiliate_id ) {

			// We start to count from the second tier, since tier one is the original referral.
			$current_tier = $tier + 1;

			// Fix timestamp before saving.
			$timestamp = strtotime( $referral->date ) + affiliate_wp()->utils->wp_offset;

			// Create a new referral object.
			$tiered_referral = array_filter(
				wp_parse_args(
					// Overwrite some attributes.
					[
						'referral_id'  => '', // Make this empty, so it will be removed by array_filter.
						'affiliate_id' => $affiliate_id,
						'parent_id'    => $referral_id,
						'type'         => 'tiered_sale',
						'date'         => gmdate( 'Y-m-d H:i:s', $timestamp ),
						'amount'       => $this->calculate_tiered_referral_commission(
							$referral_id,
							$tiers[ $current_tier ]['rate'],
							$tiers[ $current_tier ]['rate_type']
						),
					],
					// Copy all public properties from the original referral object. Always return arrays.
					get_object_vars( $referral )
				)
			);

			// Add the referral and increase the current tier.
			$tiered_referral_id = \affiliate_wp()->referrals->add( $tiered_referral );

			if ( empty( $tiered_referral_id ) ) {

				affiliate_wp()->utils->log(
					sprintf(
						'Failed to create referral from parent ID %s',
						$referral_id
					)
				);

				continue; // Skip to next if you can't create the tiered referral.
			}

			// Save the tier number, we need to +1 since this needs to be the real number and not the tiers[index].
			affwp_update_referral_meta( $tiered_referral_id, 'tier', $current_tier + 1 );

			// Also the tier_rate used at the time of the referral creation is also saved for future references.
			affwp_update_referral_meta( $tiered_referral_id, 'tier_rate', $tiers[ $current_tier ]['rate'] );

			// Save the tier rate_type for future reference.
			affwp_update_referral_meta( $tiered_referral_id, 'tier_rate_type', $tiers[ $current_tier ]['rate_type'] );

			/**
			 * Trigger actions after inserting a tiered referral.
			 *
			 * @since 1.0.0
			 * @since 1.2.0 Added tier_rate type as parameter.
			 *
			 * @param int    $tiered_referral_id The tiered referral ID.
			 * @param array  $tiered_referral The tiered referral array.
			 * @param int    $tier The tier number.
			 * @param float  $tier_rate The tier rate.
			 * @param string $tier_rate_type The tier rate_type.
			 */
			do_action(
				'affiliatewp_inserted_tiered_referral',
				$tiered_referral_id,
				$tiered_referral,
				$current_tier + 1,
				$tiers[ $current_tier ]['rate'],
				$tiers[ $current_tier ]['rate_type']
			);
		}

		// Re-add the original action.
		add_action( 'affwp_insert_referral', array( $this, 'generate_commissions' ) );
	}

	/**
	 * Hook to ensure tiered referrals are always in sync with parent.
	 *
	 * @since 1.0.0
	 * @since 1.2.0 Introduced rate_type commissions and now uses calculate_tiered_referral_commission()
	 *               to calculate the tier commissions.
	 *
	 * @param mixed $data The data used to update the referral.
	 * @param int   $referral_id The referral ID.
	 */
	public function sync_tiered_referrals_with_parent( $data, int $referral_id ) {

		$updated_referral = affwp_get_referral( $referral_id );

		if ( 'tiered_sale' === $updated_referral->type ) {
			return; // Sync should be done in the direction parent -> tiered only.
		}

		// Prevent infinite loops, as we will be updating referrals.
		remove_action( 'affwp_post_update_referral', array( $this, 'sync_tiered_referrals_with_parent' ), 100, 2 );

		$tiered_referrals = affiliate_wp()->referrals->get_referrals(
			array(
				'parent_id' => $updated_referral->referral_id,
				'type'      => 'tiered_sale',
				'status'    => array( 'draft', 'pending', 'paid', 'unpaid', 'rejected' ),
			)
		);

		if ( empty( $tiered_referrals ) && ! is_array( $tiered_referrals ) ) {
			return; // No referrals to sync, or it was not generated by MTC addon.
		}

		foreach ( $tiered_referrals as $tiered_referral ) {

			$tier_rate      = affwp_get_referral_meta( $tiered_referral->referral_id, 'tier_rate', true );
			$tier_rate_type = affwp_get_referral_meta( $tiered_referral->referral_id, 'tier_rate_type', true );

			$result = affiliate_wp()->referrals->update_referral(
				$tiered_referral->referral_id,
				[
					'status' => $updated_referral->status,
					'type'   => 'tiered_sale', // Ensure this doesn't change when updating.
					'amount' => $this->calculate_tiered_referral_commission(
						$referral_id,
						$tier_rate,
						empty( $tier_rate_type )
							? 'percentage'
							: $tier_rate_type
					),
				]
			);

			// If fails, log the error.
			if ( empty( $result ) ) {
				affiliate_wp()->utils->log( sprintf( 'Failed to update tiered referral with ID %s', $referral_id ) );
			}
		}

		// Add the action back.
		add_action( 'affwp_post_update_referral', array( $this, 'sync_tiered_referrals_with_parent' ), 100, 2 );
	}

	/**
	 * Get the sale amount from a referral.
	 *
	 * @since Unknown
	 * @since 1.4.4 Updated to exclude taxes and/or shipping if those options are set
	 *              in AffiliateWP's settings.
	 *
	 * @param int $referral_id The referral ID.
	 */
	private function get_sale_amount_from_referral( int $referral_id ) : float {

		$referral = affwp_get_referral( $referral_id );

		$calculation_method = affiliate_wp()->settings->get( 'multi_tier_commissions_tier_calculation_method' );

		if ( empty( $calculation_method ) || 'tier_based_commission' === $calculation_method ) {
			return floatval( $referral->amount );
		}

		$exclude_taxes = (bool) affiliate_wp()->settings->get( 'exclude_tax', false );

		// WooCommerce.
		if ( 'woocommerce' === $referral->context ) {

			// Try and get the order from WooCommerce.
			$order = function_exists( 'wp_get_order' )
				? wc_get_order( intval( $referral->reference ) )
				: false; // Unable to talk to WooCommerce (unlikely).

			if (

				// If we got a WooCommerce order successfully.
				( false !== $order && is_object( $order ) ) &&

				// We'll need these to calculate the overall total.
				method_exists( $order, 'get_total_tax' ) &&
				method_exists( $order, 'get_total' )
			) {

				// Get the order total.
				$order_total = $order->get_total();

				// Exclude taxes if the option is set.
				$order_total = $exclude_taxes
					? $order_total - $order->get_total_tax()
					: $order_total; // Option is not set.

				// Exclude shipping if the option is set.
				$order_total = ( (bool) affiliate_wp()->settings->get( 'exclude_shipping', false ) )
					? $order_total - $order->get_shipping_total()
					: $order_total; // Option is not set.

				return floatval( $order_total );
			}
		}

		// Easy Digital Downloads.
		if ( 'edd' === $referral->context ) {

			// Try and get the order from EDD.
			$order = function_exists( 'edd_get_order' )
				? edd_get_order( intval( $referral->reference ) )
				: false;

			if (

				// Did we get a successful order.
				( false !== $order && is_object( $order ) ) &&

				// We will need this property.
				property_exists( $order, 'tax' )
			) {

				$order_total = floatval( $order->total );

				// Exclude taxes if the option is set.
				$order_total = $exclude_taxes
					? $order_total - floatval( $order->tax )
					: $order_total; // Option is not set.

				return floatval( $order_total );
			}
		}

		// All other Integrations (or order was deleted): Use the sale amount.
		$sale = affwp_get_sale( $referral_id );

		return empty( floatval( $sale->order_total ) )

			// Return a sale based commission (no sale total).
			? floatval( $referral->amount )

			// Sale total (may not exclude taxes/shipping, etc).
			: floatval( $sale->order_total );
	}

	/**
	 * Calculate the tier commission based on the base referral.
	 *
	 * @since 1.2.0
	 *
	 * @param int    $referral_id The base referral ID.
	 * @param mixed  $tier_rate The tier rate, it can be an integer, float or string.
	 * @param string $tier_rate_type The tier rate type, it can be either 'percentage' or 'flat_per_order' or 'flat_per_product'
	 *
	 * @return float The referral commission.
	 */
	private function calculate_tiered_referral_commission( int $referral_id, $tier_rate, string $tier_rate_type ) : float {

		if ( 'flat_per_order' === $tier_rate_type ) {
			return round( $tier_rate, affwp_get_decimal_count() );
		}

		$referral = affwp_get_referral( $referral_id );

		if ( 'flat_per_product' === $tier_rate_type ) {
			return round(
				(
					empty( $referral->products )
						? 1
						: count(
							maybe_unserialize( $referral->products )
						)
				) * $tier_rate,
				affwp_get_decimal_count()
			);
		}

		// Percentage commission.
		return round(
			$this->get_sale_amount_from_referral( $referral_id )  * ( floatval( $tier_rate ) / 100 ),
			affwp_get_decimal_count(),
		);
	}
}

new Controller();
