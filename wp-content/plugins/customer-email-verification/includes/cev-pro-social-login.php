<?php
/**
 * CEV pro social login 
 *
 * @class WC Customer Email Verification Social Login
 * @package WooCommerce/Classes
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

	/**
	* Cev Customer Email Verification Social Login Class.
	*/
class WC_Customer_Email_Verification_Social_Login {

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

	public function __construct() {
		$this->init();

	}
	
	/*
	* init function
	*
	* @since  1.0
	*/
	public function init() {

		$social_login = array( 'google','amazon','facebook','linkedin','paypal','twitter','vkontakte','yahoo','disqus' );	

		foreach ( $social_login as $value ) {
			add_filter( 'wc_social_login_' . $value . '_new_user_data', array( $this ,'wc_social_login_google_new_user_data_for_cev' ), 10, 2 );
		}

		add_action('woocommerce_created_customer', array( $this ,'woocommerce_created_customer_for_social_login'), 10, 3 );
		add_action('wc_social_login_before_user_login', array( $this ,'wc_social_login_before_user_login_for_social_login'), 10, 3 );
		add_action('nsl_login', array( $this ,'nsl_login_for_nextend_social_login'), 10, 2 );
	}
	
	/*
	* use of this function to add filter in social to auto verify user 
	*/
	public function wc_social_login_google_new_user_data_for_cev( $userdata, $profile ) {
		$userdata['cev_auto_verify'] = 1;
		return 	$userdata;
	}
	
	/*
	* social email to verify this user auto verify
	*/
	public function woocommerce_created_customer_for_social_login( $user_id, $userdata, $password_generated ) {
		if ( isset( $userdata[ 'cev_auto_verify' ] ) && 1 == $userdata[ 'cev_auto_verify' ] ) {
			update_user_meta( $user_id, 'customer_email_verified', 'true');
			
			/*if ( get_option('delay_new_account_email_customer') =='1' ) {				
				$this->enable_new_account_creation_email_pro( $user_id );
			}*/
		}
	}
	/*
	* before user login to auto verify user 
	*/
	
	public function wc_social_login_before_user_login_for_social_login( $user_id ) {
		update_user_meta( $user_id, 'customer_email_verified', 'true');	
	}
	/*
	* nextend social login to use this auto verify user 
	*/
	
	public function nsl_login_for_nextend_social_login( $user_id ) {
		update_user_meta( $user_id, 'customer_email_verified', 'true');	
	}
	
	/*
	* Disable sending customer New Account email if enable 'Delay new Account Email to After Email Verification' option
	*/
	/*public function disable_account_creation_email_pro( $email_class ) {	
		add_action('woocommerce_created_customer', array( $this ,'woocommerce_created_customer_for_social_login'), 10, 3 );
	}*/

	/* 
	* Trigger New Account email when customer register After customer verify email  
	*/
	public function enable_new_account_creation_email_pro( $user_id ) {
		$emails = WC()->mailer()->emails;
		$new_customer_data = get_userdata( $user_id );	
		$user_pass = $new_customer_data->user_pass;	
		$email = $emails['WC_Email_Customer_New_Account'];
		$email->trigger( $user_id, $user_pass, false );			
	}
}
