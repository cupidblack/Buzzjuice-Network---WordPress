<?php
/**
 * Admin: Trustpilot
 *
 * @package     AffiliateWP
 * @subpackage  Admin
 * @copyright   Copyright (c) 2024, Awesome Motive, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.27.4
 * @author      Aubrey Portwood <aportwood@am.co>
 *
 * phpcs:disable PEAR.Functions.FunctionCallSignature.EmptyLine
 * phpcs:disable PEAR.Functions.FunctionCallSignature.FirstArgumentPosition
 */

namespace AffiliateWP\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \AffiliateWP\Notices\Admin_Notice;

/**
 * Trustpilot Reviews
 *
 * This class helps encourage users to leaves reviews for AffiliateWP
 * on Trustpilot.
 *
 * @since 2.27.4
 */
class Trustpilot {

	/**
	 * Construct
	 *
	 * @since 2.27.4
	 */
	public function __construct() {
		add_action( 'affiliatewp_admin_notices', [ $this, 'trustpilot_notice' ] );
	}

	/**
	 * Trustpilot Notice
	 *
	 * @since 2.27.4
	 */
	public function trustpilot_notice() : void {

		$referral_count = affiliate_wp()->referrals->get_referrals(
			[
				'number' => apply_filters( 'affwp_unlimited', -1, 'trustpilot_notification' ),
			],
			true
		);

		if ( $referral_count < 20 ) {
			return; // Don't even bother creating the notice object.
		}

		$current_user = wp_get_current_user();
		$user_name = '';

		// Try first name first.
		if ( ! empty( $current_user->first_name ) ) {
			$user_name = $current_user->first_name;
		}
		// Then try display name if it's not the same as username.
		elseif ( ! empty( $current_user->display_name ) && $current_user->display_name !== $current_user->user_login ) {
			$user_name = $current_user->display_name;
		}

		$referral_milestone = $referral_count >= 100
			? floor( $referral_count / 100 ) * 100  // Round to nearest 100 for larger numbers.
			: $referral_count;                      // Show exact number for 20-99.

		$trustpilot_url = 'https://affiliatewp.com/trustpilot-review/?utm_source=WordPress&utm_medium=admin-notice&utm_campaign=trustpilot-review';
		$base_message   = __( 'Congratulations%s on your milestone of <strong>%d referrals</strong>! Your experience matters—we\'d love to hear your review on <a href="%s" target="_blank">Trustpilot</a>.', 'affiliate-wp' );

		$message = ! empty( $user_name )
			? sprintf( $base_message, ' ' . esc_html( $user_name ), $referral_milestone, $trustpilot_url )
			: sprintf( $base_message, '', $referral_milestone, $trustpilot_url );

		new Admin_Notice(
			'trustpilot',
			sprintf(
				'%1$s <p>%2$s</p>',
				$message,
				sprintf(
					'<a href="%s" target="_blank" class="button button-primary">%s</a>',
					$trustpilot_url,
					__( 'Leave your Review', 'affiliate-wp' )
				)
			),
			'dismissible',
			'updated',
			'manage_affiliates',
			'affiliate-wp',
			[
				'storage_type' => 'user_meta',
			]
		);
	}
}
