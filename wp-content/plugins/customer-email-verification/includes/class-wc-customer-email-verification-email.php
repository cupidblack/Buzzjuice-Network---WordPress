<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Customer_Email_Verification_Email_Pro {
	
	public $is_user_already_verified = false;
	public $is_new_user_email_sent = false;
	private $user_id;
	public $my_account;
	/**
	 * Initialize the main plugin function
	*/
	public function __construct() {

		$this->my_account = get_option( 'woocommerce_myaccount_page_id' );

		if ( '' === $this->my_account ) {
			$this->my_account = get_option( 'page_on_front' );
		}
	}
	
	/**
	 * Instance of this class.
	 *
	 * @var object Class Instance
	 */
	private static $instance;
	
	/**
	 * Get the class instance
	 *
	 * @return WC_Advanced_Shipment_Tracking_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	/*
	* init from parent mail class
	*/
	public function init() {

		add_shortcode( 'customer_email_verification_code', array( $this, 'customer_email_verification_code' ) );
		add_action( 'woocommerce_created_customer_notification', array( $this, 'new_user_registration_from_registration_form' ), 10, 3 );
		add_action( 'eael/login-register/after-insert-user', array( $this, 'new_user_registration_from_essential_addons_elementor' ), 10, 2 );
		add_action( 'wp', array( $this, 'authenticate_user_by_email' ) );
		add_filter( 'woocommerce_registration_redirect', array( $this, 'redirect_user_after_registration' ) );	
		add_filter( 'zorem_tracking_data', array( $this, 'get_settings_data' ) );
		add_filter( 'wcalr_register_user_successful', array( $this, 'wcalr_register_user_successful_fun' ) );	
		add_action( 'wp', array( $this, 'show_cev_notification_message_after_register' ) );
		add_action( 'wp', array( $this, 'cev_resend_verification_email' ) );
		add_action( 'wp', array( $this, 'check_user_and_redirect_to_endpoint' ) );
		add_action( 'wp_ajax_nopriv_cev_verify_user_email_with_pin', array( $this, 'cev_verify_user_email_with_pin_fun') );
		add_action( 'wp_ajax_cev_verify_user_email_with_pin', array( $this, 'cev_verify_user_email_with_pin_fun') );
		add_action( 'user_register', array( $this, 'cev_verify_user_email_on_registration_checkout'), 10, 1 );
		add_action( 'password_reset', array( $this, 'cev_verify_user_password_reset_to_verify'), 10, 2 );
		add_filter( 'cev_verification_popup_message', array( $this, 'cev_verification_popup_message_callback'), 10, 2 );
		
		if ( is_plugin_active( 'affiliate-for-woocommerce/affiliate-for-woocommerce.php' ) ) {
			add_action( 'user_register', array( $this, 'affiliate_user_register'), 10, 2 );
		}
		
	}

	/*
	 * Check Affiliate User
	*/
	public function affiliate_user_register( $user_id, $userdata ) {
		if ( is_checkout() ) {
			return; // Exit the function if we are on the checkout page
		}
		check_ajax_referer( 'afwc-register-affiliate', 'security' );
		$user = get_user_by( 'id', $user_id );
		
		if ( !$user ) {
			return;					
		}

		$action = isset( $_POST[ 'action' ] ) ? wc_clean( $_POST[ 'action' ] ) : '';
		if ( 'afwc_register_user' == $action ) {			
			$this->new_user_registration( $user_id );
		}
	}

	/*
	 * get settings data
	*/
	public function get_settings_data( $data ) { 
	
		$data['settings'] =  array(
			'meta_key' => 'cev_pro_settings',
			'settings_data' => array (
				'cev_enable_email_verification' => array(
					'label' => 'Enable Signup Verification',
					'value' => get_option('cev_enable_email_verification', 1)
				),
				'cev_enable_email_verification_checkout' => array(
					'label' => 'Enable Checkout Verification',
					'value' => get_option('cev_enable_email_verification_checkout', 1)
				),
				'cev_enable_email_verification_free_orders' => array(
					'label' => 'Require checkout verification only for free orders',
					'value' => get_option('cev_enable_email_verification_free_orders', 0)
				),
				'cev_verification_type' => array(
					'label' => 'Verification type',
					'value' => get_option('cev_verification_type', 'otp')
				),
				'cev_verification_code_length' => array(
					'label' => 'OTP length',
					'value' => get_option('cev_verification_code_length', '1')
				),
				'cev_verification_code_expiration' => array(
					'label' => 'OTP expiration',
					'value' => get_option('cev_verification_code_expiration', 'never')
				),
				'cev_redirect_limit_resend' => array(
					'label' => 'Verification email resend limit',
					'value' => get_option('cev_redirect_limit_resend', '1')
				),
				'cev_resend_limit_message' => array(
					'label' => 'Resend limit message',
					'value' => get_option('cev_resend_limit_message', 'Too many attempts, please contact us for further assistance')
				),
				'cev_verification_success_message' => array(
					'label' => 'Email verification success message',
					'value' => get_option('cev_verification_success_message', 'Your email is verified!')
				),
				'cev_skip_verification_for_selected_roles' => array(
					'label' => 'Skip email verification for the selected user roles',
					'value' => get_option('cev_skip_verification_for_selected_roles', [])
				),
				'cev_enable_login_authentication' => array(
					'label' => 'Enable Login Authentication',
					'value' => get_option('cev_enable_login_authentication', 1)
				),
				'enable_email_otp_for_account' => array(
					'label' => 'Require OTP verification for unrecognized login',
					'value' => get_option('enable_email_otp_for_account', 1)
				),
				'enable_email_auth_for_new_device' => array(
					'label' => 'Login from a new device',
					'value' => get_option('enable_email_auth_for_new_device', 1)
				),
				'enable_email_auth_for_new_location' => array(
					'label' => 'Login from a new location',
					'value' => get_option('enable_email_auth_for_new_location', 1)
				),
				'enable_email_auth_for_login_time' => array(
					'label' => 'Last login more then',
					'value' => get_option('enable_email_auth_for_login_time', 1)
				),
				'cev_last_login_more_then_time' => array(
					'label' => 'Last Login more then Time',
					'value' => get_option('cev_last_login_more_then_time', '15')
				),

			),
		);  

	
		return $data;
	}
	
	

	/**
	 * This function is executed when a new user is made from the woocommerce registration form in the myaccount page.
	 * Its hooked into 'woocommerce_registration_auth_new_customer' filter.
	 *
	 * @param $customer
	 * @param $user_id
	 *
	 * @return mixed
	 */	 
	public function new_user_registration_from_registration_form( $user_id, $new_customer_data = array(), $password_generated = false ) {
		$this->new_user_registration( $user_id );
	}

	public function new_user_registration_from_essential_addons_elementor( $user_id, $user_data ) {
		$this->new_user_registration( $user_id );
	}
	
	public function cev_verify_user_email_on_registration_checkout( $user_id ) {
		
		$woocommerce_process_checkout_nonce = isset( $_REQUEST['woocommerce-process-checkout-nonce'] ) ? wc_clean( $_REQUEST['woocommerce-process-checkout-nonce'] ) : '';
		$_wpnonce = isset( $_REQUEST['_wpnonce'] ) ? wc_clean( $_REQUEST['_wpnonce'] ) : '';
		
		$nonce_value = wc_get_var( $woocommerce_process_checkout_nonce, wc_get_var( $_wpnonce, '' ) );
		
		if ( wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) {		
			if ( isset($_POST['createaccount']) && '1' == $_POST['createaccount'] ) {
				update_user_meta( $user_id, 'customer_email_verified', 'true' );
			}
		}
	}
	
	/*	
	 *
	 * reset password user verify user auto verify
	 */	
	public function cev_verify_user_password_reset_to_verify( $user, $new_pass ) {
		update_user_meta( $user->ID, 'customer_email_verified', 'true' );
	}
	
	/*
	 * This function gets executed from different places when ever a new user is registered or resend verifcation email is sent.
	 */
	public function new_user_registration( $user_id ) {
		
		// $user_role = get_userdata( $user_id );
		$current_user = get_user_by( 'id', $user_id );
		// $verified = get_user_meta( $user_id, 'customer_email_verified', true );
		if ( !is_plugin_active( 'affiliate-for-woocommerce/affiliate-for-woocommerce.php' ) ) {
			update_user_meta( (int) $user_id, 'customer_email_verified', 'true' );
		} else {
			$cev_enable_email_verification = get_option('cev_enable_email_verification', 1);
			if ( !cev_pro()->admin->is_admin_user( $user_id )  && !cev_pro()->admin->is_verification_skip_for_user( $user_id ) && 1 ==$cev_enable_email_verification && 'true' != $verified ) {
				
				$current_user = get_user_by( 'id', $user_id );
				$this->user_id                         = $current_user->ID;
				$this->email_id                        = $current_user->user_email;
				$this->user_login                      = $current_user->user_login;
				$this->user_email                      = $current_user->user_email;
				cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id  = $current_user->ID;
				cev_pro()->WC_customer_email_verification_email_Common->wuev_myaccount_page_id = $this->my_account;
				$this->is_user_created                 = true;
				$is_secret_code_present                = get_user_meta( $this->user_id, 'customer_email_verification_code', true );
		
				if ( '' === $is_secret_code_present ) {
					$secret_code = md5( $this->user_id . time() );
					update_user_meta( $user_id, 'customer_email_verification_code', $secret_code );
				}
				
				$verification_data = array(
					'pin' => '', 
					'startdate' => time(),
					'enddate' => time() + ( int ) $expire_time,
				);		
		
				update_user_meta( $user_id, 'cev_email_verification_pin', $verification_data );
				
				$this->is_new_user_email_sent = true;
			} else {
				update_user_meta( (int) $user_id, 'customer_email_verified', 'true' );
			}
		}
		
	}
	
	/**
	 * This function generates the verification link from the shortocde [customer_email_verification_code] and returns the link.
	 *
	 * @return string
	 */
	public function customer_email_verification_code() {
		$secret      = get_user_meta( $this->user_id, 'customer_email_verification_code', true );
		$create_link = $secret . '@' . $this->user_id;
		$hyperlink   = add_query_arg( array(
			'cusomer_email_verify' => base64_encode( $create_link ),
		), get_the_permalink( $this->my_account ) );
		$link  = '<a href="' . $hyperlink . '">"Email verification link"</a>';

		return $link;
	}
	
	/*
	 * This function verifies the user when the user clicks on the verification link in its email.
	 * If automatic login setting is enabled in plugin setting screen, then the user is forced loggedin.
	 */
	public function authenticate_user_by_email() {
		
		if ( isset( $_GET['cusomer_email_verify'] ) && '' !== $_GET['cusomer_email_verify'] ) { // WPCS: input var ok, CSRF ok.
			
			$cusomer_email_verify = wc_clean( $_GET['cusomer_email_verify'] );
			$user_meta = explode( '@', base64_decode( $cusomer_email_verify ) ); // WPCS: input var ok, CSRF ok.
			$verified_code = get_user_meta( (int) $user_meta[1], 'customer_email_verification_code', true );
			
			if ( 'true' === get_user_meta( (int) $user_meta[1], 'customer_email_verified', true ) ) {				
				$this->is_user_already_verified = true;
			} else if ( ! empty( $verified_code ) && $verified_code === $user_meta[0] ) {
				
				$cev_email_link_expired = $this->cev_email_link_expired( false, (int) $user_meta[1] );
				
				if ( $cev_email_link_expired ) {
					$verification_failed_message = get_option('cev_verification_success_message', 'Your email verification link is expired.');
					wc_add_notice( $verification_failed_message, 'notice' );
				} else {
					cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id = (int) $user_meta[1];
					$allow_automatic_login = 1;
					update_user_meta( (int) $user_meta[1], 'customer_email_verified', 'true' );
					update_user_meta( (int) $user_meta[1], 'cev_user_resend_times', 0 );					
					$verification_success_message = get_option('cev_verification_success_message', 'Your email is verified!');		
					wc_add_notice( $verification_success_message, 'notice' );
					do_action( 'cev_new_email_enable', (int) $user_meta[1] );
				}
			}
		}
	}
	
	/*
	 * This function is executed just after a new user is made from woocommerce registration form in myaccount page.
	 * Its hooked into 'woocommerce_registration_redirect' filter.
	 * If restrict user setting is enabled from the plugin settings screen, then this function will logs out the user.
	 */
	public function redirect_user_after_registration( $redirect ) {
		if ( true === $this->is_new_user_email_sent  ) {
			$cev_enter_account_after_registration = get_option('cev_enter_account_after_registration', 0);
			if ( 1 == $cev_enter_account_after_registration ) {								
				WC()->session->set( 'first_login', 1 );
			}
		}
		return $redirect;
	}
	
	public function wcalr_register_user_successful_fun() {
		if ( true === $this->is_new_user_email_sent  ) {
			$cev_enter_account_after_registration = get_option('cev_enter_account_after_registration', 0);
			if ( 1 == $cev_enter_account_after_registration ) {								
				WC()->session->set( 'first_login', 1 );
			}
		}
	}
	
	public function show_cev_notification_message_after_register() {
		if ( isset( $_GET['cev'] ) && '' !== $_GET['cev'] ) { // WPCS: input var ok, CSRF ok.
			$registration_message = get_option('cev_verification_message', 'We sent you a verification email. Check and verify your account.');
			wc_add_notice( $registration_message, 'notice' );
		}
		if ( isset( $_GET['cevsm'] ) && '' !== $_GET['cevsm'] ) { // WPCS: input var ok, CSRF ok.
			$cevsm = wc_clean( $_GET['cevsm'] );
			cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id = base64_decode( $cevsm ); // WPCS: input var ok, CSRF ok.
			if ( false === WC()->session->has_session() ) {
				WC()->session->set_customer_session_cookie( true );
			}
			$message = get_option('cev_resend_verification_email_message', 'You need to verify your account before login. {{cev_resend_email_link}}');
			$message = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $message );
			if ( false === wc_has_notice( $message, 'notice' ) ) {
				wc_add_notice( $message, 'notice' );
			}
		}
	}
	
	/**
	 * This function sends a new verification email to user if the user clicks on 'resend verification email' link.
	 * If the email is already verified then it redirects to my-account page
	 */
	public function cev_resend_verification_email() {
		if ( isset( $_GET['cev_redirect_limit_resend'] ) && '' !== $_GET['cev_redirect_limit_resend'] ) { // WPCS: input var ok, CSRF ok.
			
			$cev_redirect_limit_resend = wc_clean( $_GET['cev_redirect_limit_resend'] );
			$user_id = base64_decode( $cev_redirect_limit_resend ); // WPCS: input var ok, CSRF ok.

			if ( false === WC()->session->has_session() ) {
				WC()->session->set_customer_session_cookie( true );
			}

			$verified = get_user_meta( $user_id, 'customer_email_verified', true );

			if ( 'true' === $verified ) {
				$verified_message = get_option('cev_verified_user_message', 'Your email is already verified');
				wc_add_notice( $verified_message, 'notice' );
			} else {
				cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id                  = $user_id;
				cev_pro()->WC_customer_email_verification_email_Common->wuev_myaccount_page_id        = $this->my_account;
				
				$current_user = get_user_by( 'id', $user_id );
				
				$resend_limit_reached = cev_pro()->WC_customer_email_verification_email_Common->cev_resend_email_limit( false, $user_id );
				
				if ( $resend_limit_reached ) {
					return;
				}
				$user_resend_times = get_user_meta( $user_id, 'cev_user_resend_times', true );
				if ( null == $user_resend_times ) {
					$user_resend_times=0;
				}
				update_user_meta( $user_id, 'cev_user_resend_times', ( int ) $user_resend_times+1 );
				
				cev_pro()->WC_customer_email_verification_email_Common->code_mail_sender( $current_user->user_email );
				//$this->new_user_registration( $user_id );
				$message = get_option('cev_resend_verification_email_message', 'A new verification link is sent. Check email. {{cev_resend_email_link}}');
				$message = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $message );
				wc_add_notice( $message, 'notice' );
			}
		}
	}

	public function check_user_and_redirect_to_endpoint() {
				
		if ( !is_account_page() ) {
			return;
		}
		if ( is_user_logged_in() ) {
			$user = get_user_by( 'id', get_current_user_id() );
			
			$user_id = $user->ID;
			
			$first_login = WC()->session->get( 'first_login', 0 );
			
			if ( 1 == $first_login ) {
				return;	
			}
			if ( !$user ) {
				return;					
			}
			
			$cev_enable_email_verification = get_option('cev_enable_email_verification', 1);
			$cev_redirect_after_successfull_verification = get_option('cev_redirect_after_successfull_verification', $this->my_account);
			$redirect_url = wc_get_account_endpoint_url( 'email-verification' );
			$redirect_url_my_account = wc_get_account_endpoint_url( 'dashboard' );
			$logout_url = wc_get_account_endpoint_url( 'customer-logout' );	
			$logout_url = strtok($logout_url, '?');
			$logout_url = rtrim(strtok($logout_url, '?'), '/');
			$email_verification_url = rtrim(wc_get_account_endpoint_url( 'email-verification' ), '/');
			
			global $wp;
			$current_slug = add_query_arg( array(), $wp->request );
			
			if ( home_url( $wp->request ) == $logout_url ) {
				return;															
			}
			if ( !cev_pro()->admin->is_admin_user( $user_id )  && !cev_pro()->admin->is_verification_skip_for_user( $user_id ) && 1 == $cev_enable_email_verification ) {
				$verified = get_user_meta( get_current_user_id(), 'customer_email_verified', true );
				$cev_email_verification_pin = get_user_meta( get_current_user_id(), 'cev_email_verification_pin', true );
					
				if ( !empty($cev_email_verification_pin) ) {
					if ( 'true' !== $verified ) {
						if ( rtrim( home_url( $wp->request ), '/' ) != $email_verification_url ) {
							wp_safe_redirect( $redirect_url );
							exit;
						}
					} elseif ( 'true' == $verified ) {
						if ( home_url( $wp->request ) == $email_verification_url ) {
							wp_safe_redirect( $redirect_url_my_account );
							exit;
						}
					}				
				}
			}
		}
	}
		
	public function cev_verify_user_email_with_pin_fun() {
		check_admin_referer( 'cev_verify_user_email_with_pin', 'cev_verify_user_email_with_pin' );
		
		$cev_email_link_expired = $this->cev_email_link_expired( false, get_current_user_id() );
		
		if ( $cev_email_link_expired ) {
			$verification_message_expire = get_option('cev_verification_success_message', 'failed');
			wc_add_notice( $verification_message_expire, 'notice' );
			echo json_encode( array('success' => 'false') );
			die();	
		}
		
		$cev_email_verification_pin = get_user_meta( get_current_user_id(), 'cev_email_verification_pin', true );
		
		
		$cev_pin = isset( $_POST['otp_value'] ) ? wc_clean( $_POST['otp_value'] ) : '';
		
		if ( $cev_email_verification_pin['pin'] == $cev_pin ) {
			$my_account = cev_pro()->my_account;
			$redirect_page_id = get_option('cev_redirect_page_after_varification', $my_account);
			
			update_user_meta( get_current_user_id(), 'customer_email_verified', 'true' );
			update_user_meta( get_current_user_id(), 'cev_user_resend_times', 0 );
							
			$verification_success_message = get_option('cev_verification_success_message', 'Your Email is verified!');		
			wc_add_notice( $verification_success_message, 'notice' );
			
			do_action( 'cev_new_email_enable', get_current_user_id() );
				
			echo json_encode( array('success' => 'true','url' => get_permalink($redirect_page_id)) );
			die();
		} else {
			echo json_encode( array('success' => 'false') );
			die();
		}
		exit;
	}
	
	/**
	 * Cev Email Link Expired 
	 */	
	public function cev_email_link_expired( $exprired, $user_id ) {
			
		$cev_email_verification_pin = get_user_meta( $user_id, 'cev_email_verification_pin', true );
		// Check if $cev_email_verification_pin is empty
		if ( empty( $cev_email_verification_pin ) ) {
			return $exprired;
		}
		$current_time = time();
		$expire_time = $cev_email_verification_pin['enddate'];
		
		$cev_verification_code_expiration = get_option('cev_verification_code_expiration', 'never');
		
		if ( 'never' != $cev_verification_code_expiration ) {
			if ( $current_time > $expire_time ) {
				return true;	
			}
		}
		return $exprired;
	}
	
	/**
	 * Return Email verification widget message
	 * 
	 * @since  1.0.0
	*/
	public function cev_verification_popup_message_callback( $message, $email ) {
		$message_text = get_option('cev_verification_message', $message);
		$message_text = str_replace('{customer_email}', $email, $message_text);
		$message_text = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $message_text );
		if ( '' != $message_text ) {
			return $message_text;
		}
		return $message;
	}
}
