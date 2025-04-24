<?php

/**
 * Class Affiliate_WP_Lifetime_Commissions_Upgrades
 *
 * @since  1.4.2
 */
class Affiliate_WP_Lifetime_Commissions_Upgrades {

	/**
	 * Signals whether the upgrade was successful.
	 *
	 * @access public
	 * @var    bool
	 */
	private $upgraded = false;

	/**
	 * AffiliateWP - Lifetime Commissions version.
	 *
	 * @access private
	 * @since  1.4.2
	 * @var    string
	 */
	private $version;

	/**
	 * Sets up the Upgrades class instance.
	 *
	 * @access public
	 *
	 * @param string $version The AffiliateWP Lifetime Commissions version.
	 */
	public function __construct( $version ) {

		$this->version = $version;

		add_action( 'admin_init', array( $this, 'init' ), -9999 );

		add_action( 'admin_init', array( $this, 'register_batch_process' ) );

		add_action( 'admin_notices', array( $this, 'upgrade_notices' ) );

	}

	/**
	 * Initializes upgrade routines for the current version of Affiliate Area Tabs.
	 *
	 * @since 1.4.2
	 */
	public function init() {

		$this->version = get_option( 'affwp_lc_version' );

		// Delete affwp_lc_migrate_customers option so that the upgrade notice can be displayed again.
		if ( version_compare( $this->version, '1.4.1', '=' ) && get_option( 'affwp_lc_migrate_customers' ) ) {
			delete_option( 'affwp_lc_migrate_customers' );
		}

		// Inconsistency between current and saved version.
		if ( version_compare( $this->version, AFFWP_LC_VERSION, '<>' ) ) {
			$this->upgraded = true;
		}

		// If upgrades have occurred.
		if ( true === $this->upgraded ) {
			update_option( 'affwp_lc_version_upgraded_from', $this->version );
			update_option( 'affwp_lc_version', AFFWP_LC_VERSION );
		}

	}

	/**
	 * Register batch process
	 *
	 * @since 1.3
	 */
	public function register_batch_process() {

		if ( true === version_compare( AFFILIATEWP_VERSION, '2.0', '>=' ) ) {

			affiliate_wp()->utils->batch->register_process( 'migrate-lc-meta', array(
				'class' => 'AffWP\Utils\Batch_Process\Migrate_Lifetime_Commissions_Meta',
				'file'  => AFFWP_LC_PLUGIN_DIR . 'includes/class-batch-migrate-meta.php'
			) );

			/**
			 * Register batch process to migrate lifetime customers to the lifetime customers table from the customer meta.
			 *
			 * @since 1.4.1
			 */
			affiliate_wp()->utils->batch->register_process( 'migrate-lc-customers', array(
				'class' => 'AffWP\Utils\Batch_Process\Migrate_Lifetime_Commissions_Customers',
				'file'  => AFFWP_LC_PLUGIN_DIR . 'includes/class-batch-migrate-lifetime-customers.php'
			) );

		}
	}

	/**
	 * Displays upgrade notices.
	 *
	 * @since 1.3
	 */
	public function upgrade_notices() {

		if ( true === version_compare( AFFILIATEWP_VERSION, '2.0', '>=' ) && false === get_option( 'affwp_lc_migrate_meta' ) ) :

			// Enqueue admin JS for the batch processor.
			affwp_enqueue_admin_js();
			?>
			<div class="notice notice-info is-dismissible">
				<p><?php _e( 'Your database needs to be upgraded following the latest AffiliateWP - Lifetime Commissions update. Depending on the size of your database, this upgrade could take some time.', 'affiliate-wp-lifetime-commissions' ); ?></p>
				<form method="post" class="affwp-batch-form" data-batch_id="migrate-lc-meta"
				      data-nonce="<?php echo esc_attr( wp_create_nonce( 'migrate-lc-meta_step_nonce' ) ); ?>">
					<p>
						<?php submit_button( __( 'Upgrade Database', 'affiliate-wp-lifetime-commissions' ), 'secondary', 'v13-migrate-affiliates-lc-meta', false ); ?>
					</p>
				</form>
			</div>
		<?php
		endif;

		/**
		 * Upgrade notice to migrate lifetime customers to the lifetime customers table from the customer meta.
		 *
		 * @since 1.4.1
		 */
		if ( true === version_compare( AFFILIATEWP_VERSION, '2.0', '>=' ) && get_option( 'affwp_lc_migrate_meta' ) && false === get_option( 'affwp_lc_migrate_customers' ) ) :

			// Enqueue admin JS for the batch processor.
			affwp_enqueue_admin_js();
			?>
			<div class="notice notice-info is-dismissible">
				<p><?php _e( 'Your database needs to be upgraded following the latest AffiliateWP - Lifetime Commissions update. Depending on the size of your database, this upgrade could take some time.', 'affiliate-wp-lifetime-commissions' ); ?></p>
				<form method="post" class="affwp-batch-form" data-batch_id="migrate-lc-customers"
				      data-nonce="<?php echo esc_attr( wp_create_nonce( 'migrate-lc-customers_step_nonce' ) ); ?>">
					<p>
						<?php submit_button( __( 'Upgrade Database', 'affiliate-wp-lifetime-commissions' ), 'secondary', 'v141-migrate-affiliates-lc-customers', false ); ?>
					</p>
				</form>
			</div>
		<?php
		endif;
	}

}
