<?php
/**
 * Integrations: Store Credit add-on
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Integrations
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace AffiliateWP_Affiliate_Portal\Integrations;

use AffiliateWP_Affiliate_Portal\Core;
use AffiliateWP_Affiliate_Portal\Core\Components\Controls;
use AffiliateWP_Affiliate_Portal\Core\Interfaces;

/**
 * Class for integrating the Store Credit add-on.
 *
 * @since 1.0.0
 */
class Store_Credit implements Interfaces\Integration {

	/**
	 * Signifies whether Store Credit is globally enabled.
	 *
	 * @since 1.0.9
	 * @var   bool
	 */
	protected $enabled_global = false;

	/**
	 * Signifies whether the affiliate-level opt-in for payout switching is enabled.
	 *
	 * @since 1.0.9
	 * @var   bool
	 */
	protected $switch_payout_enabled = false;


	/**
	 * Signifies whether Store Credit is enabled for all affiliates.
	 *
	 * @since 1.0.9
	 * @var   bool
	 */
	protected $all_affiliates_enabled = false;

	/**
	 * @inheritDoc
	 */
	public function init() {
		$this->enabled_global         = (bool) affiliate_wp()->settings->get( 'store-credit', false );
		$this->all_affiliates_enabled = (bool) affiliate_wp()->settings->get( 'store-credit-all-affiliates', false );
		$this->switch_payout_enabled  = (bool) affiliate_wp()->settings->get( 'store-credit-change-payment-method', false );

		// Register store credit payout checkbox.
		add_action( 'affwp_portal_controls_registry_init', array( $this, 'register_payout_checkbox' ) );

		// Register available store credit on stats.
		add_action( 'affwp_portal_controls_registry_init', array( $this, 'register_available_credit_control' ) );
	}

	/**
	 * Registers Store Credit Payout checkbox.
	 *
	 * @since 1.0.0
	 *
	 * @param Core\Controls_Registry $registry Controls registry.
	 */
	public function register_payout_checkbox( $registry ) {
		$affiliate_id = affwp_get_affiliate_id();

		// Create checkbox control.
		$store_credit_checkbox = new Controls\Checkbox_Control( array(
			'id'                  => 'affwp-store-credit-payout',
			'view_id'             => 'settings',
			'section'             => 'user-settings',
			'priority'            => 6,
			'permission_callback' => array( $this, 'maybe_render_checkbox_setting' ),
			'atts'                => array(
				'name'  => 'store_credit_payout',
				'class' => array(
					'form-checkbox',
					'h-4',
					'w-4',
					'text-indigo-600',
					'transition',
					'duration-150',
					'ease-in-out',
				),
			),
			'args'                => array(
				'label'         => __( 'Enable payout via store credit', 'affiliatewp-affiliate-portal' ),
				'desc'          => __( 'Receive your payouts in store credit.', 'affiliatewp-affiliate-portal' ),
				'label_class'   => array(
					'font-medium',
					'text-gray-700',
				),
				'get_callback'  => function ( $affiliate_id ) {
					$enabled = affwp_get_affiliate_meta( $affiliate_id, 'store_credit_enabled', true );

					return $enabled ? 'on' : 'off';
				},
				'save_callback' => function ( $data, $affiliate_id ) {
					if ( true === $data ) {
						$result = affwp_update_affiliate_meta( $affiliate_id, 'store_credit_enabled', true );
					} else {
						$result = affwp_delete_affiliate_meta( $affiliate_id, 'store_credit_enabled' );
					}

					return $result;
				},
			),
		) );

		// Add control to section.
		$registry->add_control( $store_credit_checkbox );
	}

	/**
	 * Determines whether to render the Store Credit checkbox in the affiliate settings.
	 *
	 * @since 1.0.9
	 *
	 * @param Controls\Checkbox_Control $control      Control object.
	 * @param int                       $affiliate_id Affiliate ID.
	 * @return bool Whether to render the control.
	 */
	public function maybe_render_checkbox_setting( $control, $affiliate_id ) {
		$render = false;

		if ( true === $this->enabled_global && true === $this->switch_payout_enabled ) {
			$render = true;
		}

		return $render;
	}

	/**
	 * Registers Available Store Credit on Stats.
	 *
	 * @since 1.0.0
	 *
	 * @param Core\Controls_Registry $registry Controls registry.
	 */
	public function register_available_credit_control( $registry ) {

		// Create available store credit control.
		$available_store_credit_control = new Controls\Card_Control( array(
			'id'                  => 'affiliatewp-available-store-credit',
			'view_id'             => 'stats',
			'section'             => 'stats',
			'parent'              => 'referrals_card_group',
			'permission_callback' => array( $this, 'maybe_render_stats_card' ),
			'args'                => array(
				'title'    => __( 'Available store credit', 'affiliatewp-affiliate-portal' ),
				'data_key' => 'available_store_credit',
				'data'     => function ( $data_key, $affiliate_id ) {
					// Get Store Credit balance.
					$balance = affwp_store_credit_balance( array( 'affiliate_id' => $affiliate_id ) );
					return $balance;
				},
			),
		) );

		// Add control to section.
		$registry->add_control( $available_store_credit_control );
	}

	/**
	 * Determines whether to display the 'Available store credit' stats card control.
	 *
	 * @since 1.0.9
	 *
	 * @param Controls\Card_Control $control      Card control object.
	 * @param int                   $affiliate_id Affiliate ID.
	 * @return bool Whether to render the stats card.
	 */
	public function maybe_render_stats_card( $control, $affiliate_id ) {
		$render = false;

		if ( true === $this->enabled_global
			&& ( affwp_get_affiliate_meta( $affiliate_id, 'store_credit_enabled', true ) || true === $this->all_affiliates_enabled )
		) {
			$render = true;
		}

		return $render;
	}

}
