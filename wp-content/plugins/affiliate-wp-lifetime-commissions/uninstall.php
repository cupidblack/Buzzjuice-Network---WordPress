<?php
/**
 * Uninstall Lifetime Commissions
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( is_multisite()
     && ( ! defined( 'AFFILIATE_WP_NETWORK_WIDE' )
          || ( defined( 'AFFILIATE_WP_NETWORK_WIDE' ) && ! AFFILIATE_WP_NETWORK_WIDE )
     )
) {
	if ( true === version_compare( $GLOBALS['wp_version'], '4.6', '<' ) ) {
		$sites = wp_list_pluck( 'blog_id', wp_get_sites() );
	} else {
		$sites = get_sites( array( 'fields' => 'ids' ) );
	}

	// Loop through sites and remove Lifetime Commissions data.
	foreach ( $sites as $site_id ) {

		switch_to_blog( $site_id );

		$affwp_settings = get_option( 'affwp_settings', array() );

		$uninstall_on_delete = ! empty( $affwp_settings['lifetime_commissions_uninstall_on_delete'] ) ? $affwp_settings['lifetime_commissions_uninstall_on_delete'] : false;

		if ( $uninstall_on_delete ) {

			affwp_lc_uninstall_tables();

		}

		restore_current_blog();
	}

} else {

	$affwp_settings = get_option( 'affwp_settings', array() );

	$uninstall_on_delete = ! empty( $affwp_settings['lifetime_commissions_uninstall_on_delete'] ) ? $affwp_settings['lifetime_commissions_uninstall_on_delete'] : false;

	if ( $uninstall_on_delete ) {

		affwp_lc_uninstall_tables();

	}
}

/**
 * Uninstalls all database tables created by Lifetime Commissions.
 *
 * @since 1.4.1
 */
function affwp_lc_uninstall_tables() {

	global $wpdb;

	// Delete lifetime customers table.
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . 'affiliate_wp_lifetime_customers' );

	// Remove all affwp_lc options.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%affwp_lc_%'" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'affwp\_lc\_%';" );
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '%affiliate_wp_lifetime_customers%'" );

	// Deletes all user meta entries associated with Lifetime Commissions.
	$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'affwp\_lc\_%';" );

	// Deletes all customer meta entries associated with Lifetime Commissions.
	$customer_meta_table        = $wpdb->prefix . 'affiliate_wp_customermeta';
	$customer_meta_table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $customer_meta_table ) );

	if ( $customer_meta_table === $customer_meta_table_exists ) {
		$wpdb->query( "DELETE FROM $customer_meta_table WHERE meta_key LIKE 'affwp\_lc\_%';" );
	}

	// Deletes all affiliate meta entries associated with Lifetime Commissions.
	$affiliate_meta_table        = $wpdb->prefix . 'affiliate_wp_affiliatemeta';
	$affiliate_meta_table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $affiliate_meta_table ) );

	if ( $affiliate_meta_table === $affiliate_meta_table_exists ) {
		$wpdb->query( "DELETE FROM $affiliate_meta_table WHERE meta_key LIKE 'affwp\_lc\_%';" );
	}

}