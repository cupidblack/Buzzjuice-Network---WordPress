<?php
/**
 * Integrations: Pushover Notifications add-on
 *
 * @package     AffiliateWP Affiliate Dashboard
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
 * Class for integrating the Pushover Notifications add-on.
 *
 * @since 1.0.0
 */
class Pushover_Notifications implements Interfaces\Integration {

	/**
	 * @inheritDoc
	 */
	public function init() {
		// Register pushover notifications checkbox.
		add_action( 'affwp_portal_controls_registry_init', array( $this, 'register_user_key_input' ) );
	}

	/**
	 * Registers integration user key input.
	 *
	 * @since 1.0.0
	 *
	 * @param Core\Controls_Registry $registry Controls registry.
	 */
	public function register_user_key_input( $registry ) {

		// Create user key text input control.
		$user_key_pushover_notifications_control = new Controls\Text_Input_Control( array(
			'id'      => 'affwp-pushover-notifications-user-key',
			'view_id' => 'settings',
			'section' => 'user-settings',
			'args'    => array(
				'label'        => __( 'Pushover user key', 'affiliatewp-affiliate-portal' ),
				'desc'         => array(
					'text'     => __( 'Receive referral notifications via Pushover', 'affiliatewp-affiliate-portal' ),
					'position' => 'before',
				),
				'get_callback' => function( $affiliate_id ) {
					$user_id     = affwp_get_affiliate_user_id( $affiliate_id );
					$current_key = get_user_meta( $user_id, '_affwp_pushover_user_key', true );

					if ( ! $current_key ) {
						$current_key = '';
					}

					return $current_key;
				},
				'save_callback' => function( $value, $affiliate_id ) {
					$value   = sanitize_text_field( $value );
					$user_id = affwp_get_affiliate_user_id( $affiliate_id );

					if ( ! $user_id ) {
						return;
					}

					update_user_meta( $user_id, '_affwp_pushover_user_key', $value );
				},
			),
		) );

		// Add control to section.
		$registry->add_control( $user_key_pushover_notifications_control );

	}


}
