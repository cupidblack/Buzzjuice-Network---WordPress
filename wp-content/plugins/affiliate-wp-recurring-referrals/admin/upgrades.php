<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register batch process
 *
 * @since 1.7
 */
function affwp_rr_register_batch_process() {

	if ( true === version_compare( AFFILIATEWP_VERSION, '2.2.9', '>=' ) ) {

		affiliate_wp()->utils->batch->register_process( 'affwp-rr-migrate-parent-id', array(
			'class' => 'AffWP\Utils\Batch_Process\AffiliateWP_Recurring_Referrals_Migrate_Parent_ID',
			'file'  => AFFWP_RR_PLUGIN_DIR . 'admin/class-batch-migrate-parent-id.php'
		) );

	}
}
add_action( 'admin_init', 'affwp_rr_register_batch_process' );

/**
 * Displays upgrade notices.
 *
 * @since 1.7
 */
function affwp_rr_upgrade_notice() {

	if ( true === version_compare( AFFILIATEWP_VERSION, '2.2.9', '>=' ) && false === get_option( 'affwp_rr_migrate_parent_id' ) ) :

		// Enqueue admin JS for the batch processor.
		affwp_enqueue_admin_js();
		?>
		<div class="notice notice-info is-dismissible">
			<p><?php _e( 'Your database needs to be upgraded following the latest AffiliateWP - Recurring Referrals update. Depending on the size of your database, this upgrade could take some time.', 'affiliate-wp-recurring-referrals' ); ?></p>
			<form method="post" class="affwp-batch-form" data-batch_id="affwp-rr-migrate-parent-id"
			      data-nonce="<?php echo esc_attr( wp_create_nonce( 'affwp-rr-migrate-parent-id_step_nonce' ) ); ?>">
				<p>
					<?php submit_button( __( 'Upgrade Database', 'affiliate-wp-recurring-referrals' ), 'secondary', 'v17-affwp-rr-migrate-parent-ida', false ); ?>
				</p>
			</form>
		</div>
	<?php endif;
}
add_action( 'admin_notices', 'affwp_rr_upgrade_notice' );