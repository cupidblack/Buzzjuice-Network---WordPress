<?php
/**
 * Templates: Payouts Service View
 *
 * This template is used to render the Payouts Service view.
 *
 * @package     AffiliateWP Affiliate Portal
 * @subpackage  Templates
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */

use function AffiliateWP_Affiliate_Portal\html;

$affiliate_id         = affwp_get_affiliate_id();
$payouts_service_meta = affwp_get_affiliate_meta( affwp_get_affiliate_id(), 'payouts_service_account', true );
?>

<?php

if ( affwp_is_payouts_service_enabled() ) : ?>

	<div class="my-5">
		<?php
		if ( isset( $payouts_service_meta['status'] ) && 'payout_method_added' === $payouts_service_meta['status'] ) {
			affiliatewp_affiliate_portal()->templates->get_template_part( 'payouts/payout-method' );
		} elseif ( isset( $payouts_service_meta['status'] ) && 'account_created' === $payouts_service_meta['status'] ) {
			affiliatewp_affiliate_portal()->templates->get_template_part( 'payouts/add-payout-method' );
		} else {
			affiliatewp_affiliate_portal()->templates->get_template_part( 'payouts/register' );
		}
		?>
	</div>

	<?php html()->section_divider(); ?>

<?php endif; ?>


