<?php
/**
 * MemberPress integration for Lifetime Commissions
 *
 * @package AffiliateWP Lifetime Commissions
 * @category Integrations
 *
 * @since 1.4
 */

/**
 * Implements a MemberPress integration for Lifetime Commissions.
 *
 * @since 1.4
 *
 * @see \Affiliate_WP_Lifetime_Commissions_Base
 */
class Affiliate_WP_Lifetime_Commissions_MemberPress extends Affiliate_WP_Lifetime_Commissions_Base {

	/**
	 * Sets up the integration.
	 *
	 * @since 1.4
	 */
	public function init() {
		$this->context = 'memberpress';
	}

	/**
	 * Retrieves the email address of a customer from the MemberPress transaction.
	 *
	 * @since 1.4
	 *
	 * @param int $reference Optional. MemberPress transaction ID. Default 0.
	 * @return string|false The email address of the customer, false otherwise.
	 */
	public function get_email( $reference = 0 ) {

		$transaction = \MeprTransaction::get_one( $reference );

		if ( $transaction ) {
			$user = get_user_by( 'id', $transaction->user_id );

			if ( $user ) {
				return $user->user_email;
			}
		}

		return false;
	}

}

if ( class_exists( 'MeprAppCtrl' ) ) {
	new Affiliate_WP_Lifetime_Commissions_MemberPress;
}
