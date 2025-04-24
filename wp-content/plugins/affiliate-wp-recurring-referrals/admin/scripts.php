<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Scripts.
 *
 * @since 1.7
*/
function affwp_affiliate_recurring_rates_admin_enqueue_scripts() {

	if ( affwp_rr_is_affiliate_page() ) {

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		if ( wp_script_is( 'affwp-select2', 'registered' ) ) {
			wp_enqueue_script( 'affwp-select2' );
			wp_enqueue_style( 'affwp-select2' );
		} else {
			wp_enqueue_script( 'affwp-rr-js', AFFWP_RR_PLUGIN_URL . 'assets/js/select2' . $suffix . '.js', array( 'jquery' ), AFFWP_RR_VERSION );
			wp_enqueue_style( 'affwp-rr-css', AFFWP_RR_PLUGIN_URL . 'assets/css/select2' . $suffix . '.css', '', AFFWP_RR_VERSION, 'screen' );	
		}
	}

}
add_action( 'admin_enqueue_scripts', 'affwp_affiliate_recurring_rates_admin_enqueue_scripts' );

/**
 *  Determines whether the current admin page is either the edit or add affiliate admin page.
 *  
 *  @since 1.7
 *  @return bool True if either edit or new affiliate admin pages.
 */
function affwp_rr_is_affiliate_page() {

	if ( ! is_admin() || ! did_action( 'wp_loaded' ) ) {
		$ret = false;
	}
	
	if ( ! ( isset( $_GET['page'] ) && 'affiliate-wp-affiliates' != $_GET['page'] ) ) {
		$ret = false;
	}

	$action  = isset( $_GET['action'] ) ? $_GET['action'] : '';

	$actions = array(
		'edit_affiliate',
		'add_affiliate'
	);
		
	$ret = in_array( $action, $actions );
	
	return $ret;
}