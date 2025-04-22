<?php
/**
 * CEV pro tools_tab 
 *
 * @class   cev_pro_admin_tools_tab
 * @package WooCommerce/Classes
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cev pro tools tab class.
 */
class Cev_Pro_Tools_Tab {

	/**
	 * Get the class instance
	 *
	 * @since  1.0.0
	 * @return customer-email-verification-pro
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	*/
	private static $instance;
	
	/**
	 * Initialize the main plugin function
	 * 
	 * @since  1.0.0
	*/
	public $my_account_id;
	public function __construct() {
		$this->init();
		$this->my_account_id = get_option( 'woocommerce_myaccount_page_id' );
	}
	
	/*
	 * init function
	 *
	 * @since  1.0
	*/
	public function init() {
		add_action( 'wp_ajax_bulk_email_verify_from_tools', array( $this, 'bulk_email_verify_from_tools') );
		add_action( 'wp_ajax_bulk_email_resend_verify_from_tools', array( $this, 'bulk_email_resend_verify_from_tools') );
		add_action( 'wp_ajax_bulk_approv_customer_from_tools', array( $this, 'bulk_approv_customer_from_tools') );
		add_action( 'wp_ajax_toggle_email_delete_from_tools', array( $this, 'toggle_email_delete_from_tools') );
		add_action( 'wp_ajax_delete_all_unverified_users', array( $this, 'delete_all_unverified_users') );
		add_action( 'cev_delete_unverified_users', array( $this, 'delete_all_unverified_users') );
		add_action( 'wp_ajax_nopriv_delete_all_unverified_users', array( $this, 'delete_all_unverified_users') );
		add_filter( 'cron_schedules', array( $this ,'schedule_cron_delete_unverify_user') );
	}
	
	/* 
	* Schedule cron
	*/
	public function schedule_cron_delete_unverify_user( $schedules ) {
		$schedules['cev_delete_user_cron_events'] = array(
			'interval' => 86400,
			'display' => __('Every day'),
		);
		return $schedules;
	}

	/* 
	* Bulk verify all unverified users
	*/
	public function bulk_email_verify_from_tools() {
		
		$user_query = new WP_User_Query(
			array(
				'number' => 1000,
				'meta_query'    => array(
					array(
						'key' => 'customer_email_verified',
						'value' => 'false',
						'compare' => 'NOT EXISTS'
					)
				)
			)
		);
	
		// Get the results from the query, returning the first user
		$users = $user_query->get_results();
		$count = 0;
		foreach ( $users as $user ) {
			update_user_meta( $user->ID, 'customer_email_verified', 'true' );
			$count++;
		}
		echo esc_html( $count );
		exit;
	}
	
	/* 
	* Bulk resend verification email to all unverified users
	*/
	public function bulk_email_resend_verify_from_tools() {
		
		$user_query = new WP_User_Query(
			array(
				'meta_query'    => array(
					array(
						'key' => 'customer_email_verified',
						'value' => 'false',
						'compare' => 'NOT EXISTS'
					)
				)
			)
		);
	
		// Get the results from the query, returning the first user
		$users = $user_query->get_results();
	 
		foreach ( $users as $user ) {
			$current_user = get_user_by( 'id', $user->ID );
			
			$this->user_id                         = $current_user->ID;
			$this->email_id                        = $current_user->user_email;
			$this->user_login                      = $current_user->user_login;
			$this->user_email                      = $current_user->user_email;
			cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id  = $current_user->ID;
			$this->is_user_created                 = true;
			$is_secret_code_present                = get_user_meta( $user->ID, 'customer_email_verification_code', true );
			
			if ( '' === $is_secret_code_present ) {
				$secret_code = md5( $user->ID . time() );
				update_user_meta( $user->ID, 'customer_email_verification_code', $secret_code );
			}
		}
	}
	
	/* 
	* Bulk approve all users
	*/
	public function bulk_approv_customer_from_tools() {
		
		$user_query = new WP_User_Query(
			array(
				array(
					'key' => 'customer_admin_approval_verified',
					'value' => 'false',
					'compare' => 'NOT EXISTS'
				)
			)
		);
	
		// Get the results from the query, returning the first user
		$users = $user_query->get_results();
		foreach ( $users as $user ) {
			update_user_meta( $user->ID, 'customer_admin_approval_verified', 'true' );
		}
	}
	
	/* 
	* Bulk delete all unverified users
	*/
	public function delete_all_unverified_users() {
		
		$cev_enable_tools = get_option('cev_enable_tools', 'false');
		if ( 'yes' != $cev_enable_tools ) {
			return;
		}
		$current_date = gmdate( 'Y-m-d h:i:s' );
		
		$user_query = new WP_User_Query(
			array(
				'meta_query'    => array(
					'relation'  => 'AND',
					array( 
						'key'     => 'cev_email_verification_pin',
						'compare' => 'EXISTS'
					),
					array(
						'key' => 'customer_email_verified',
						'value' => 'false',
						'compare' => 'NOT EXISTS'
					)
				)
			)
		);
		
		// Get the results from the query, returning the first user
		$users = $user_query->get_results();
		
		foreach ( $users as $user ) {
			$days = get_option('cev_change_texbox_value', 14);
			$udata = get_userdata( $user->ID );
			
			$now = time();
			$registered = strtotime($udata->user_registered);
			$datediff = $now - $registered;
						
			$differece = round ( $datediff / ( 60 * 60 * 24 ) );
			if ( $differece > $days ) {
				require_once(ABSPATH . 'wp-admin/includes/user.php');
				wp_delete_user( $user->ID );
			}
		}
	}
	
	/* 
	* Enable/Disable Automatically delete customers toggle 
	*/
	public function toggle_email_delete_from_tools() {
		
		check_ajax_referer( 'wc_cev_delete_user', 'wp_nonce' );
		
		$toggle_condition = isset( $_POST['toggle'] ) ? wc_clean( $_POST['toggle'] ) : '';
		
		if ( 'true' == $toggle_condition ) {
			$toggle = 'yes';
			if (!wp_next_scheduled('cev_delete_unverified_users')) {
				wp_schedule_event( time(), 'cev_delete_user_cron_events', 'cev_delete_unverified_users' );
			}
		} else {
			$toggle = 'no';
			wp_clear_scheduled_hook( 'cev_delete_unverified_users' );
		}
		update_option( 'cev_enable_tools', $toggle);
		$toggle_textbox = isset( $_POST['textbox'] ) ? wc_clean( $_POST['textbox'] ) : '';
		update_option( 'cev_change_texbox_value', $toggle_textbox );
	}
}
