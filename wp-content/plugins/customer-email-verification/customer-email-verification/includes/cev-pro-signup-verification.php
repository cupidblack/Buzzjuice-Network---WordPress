<?php

if (!defined('ABSPATH')) {
	exit;
}

class CEV_Signup_Verification {
	/**
	 * Initialize the main plugin function
	 */
	public function __construct() {
		add_action('init', array($this, 'init'));
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
	 * @return CEV_Signup_Verification
	 */
	public static function get_instance() {
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Init function to add hooks
	 */
	public function init() {
		add_action('wp_enqueue_scripts', array($this, 'custom_enqueue_scripts'));
		add_action('woocommerce_before_customer_login_form', array($this, 'add_otp_popup_to_my_account'));
		add_action('wp_ajax_nopriv_check_email_exists', array($this, 'check_email_exists'));
		add_action('wp_ajax_verify_otp', array($this, 'verify_otp'));
		add_action('wp_ajax_nopriv_verify_otp', array($this, 'verify_otp'));
		add_action('wp_ajax_resend_otp', array($this, 'resend_otp'));
		add_action('wp_ajax_nopriv_resend_otp', array($this, 'resend_otp'));

	}
	
	public function custom_enqueue_scripts() {
		wp_enqueue_script('cev-signup-script', plugins_url('../assets/js/signup-script.js', __FILE__), array('jquery'), time(), true);
		wp_enqueue_style('cev-custom-style', plugins_url('../assets/css/signup-style.css', __FILE__), array(), time());
		
		wp_localize_script('cev-signup-script', 'cev_ajax', array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('verify_otp_nonce'), // Add nonce here
			'loaderImage' => plugins_url('assets/images/Eclipse.svg', dirname(__FILE__)), // Use dirname(__FILE__) to get the correct path
			'enableEmailVerification' => (bool) get_option('cev_enable_email_verification'),
			'password_setup_link_enabled' =>  get_option('woocommerce_registration_generate_password', 'no'),
			'cev_password_validation' => __( 'Password is required.', 'customer-email-verification' ),
			'cev_email_validation' => __( 'Email is required.', 'customer-email-verification' ),
			'cev_email_exists_validation' => __( 'An account with this email address already exists. Please use a different email or log in to your existing account.', 'customer-email-verification' ),
			'cev_valid_email_validation' => __( 'Enter a valid email address.', 'customer-email-verification' ),
			'cev_Validation_error_position_id' => apply_filters('change_validation_error_message_position', '#customer_login')
		));
		
	}
	/**
	 * Add OTP popup to My Account page
	 */
	public function add_otp_popup_to_my_account() {
		require_once cev_pro()->get_plugin_path() . '/includes/views/cev_signup_popup.php';			
	}

	public function resend_otp() {
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
		if (!$nonce || !wp_verify_nonce($nonce, 'verify_otp_nonce')) {
			wp_send_json_error(array('verified' => false, 'message' => __('Nonce verification failed.', 'customer-email-verification')));
		}
		$recipient = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
		$this->send_signup_verification_email( $recipient );
		wp_send_json_success(array('email' => $result));
		
	}
	public function check_email_exists() {
		// Verify nonce for security.
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
		if (!$nonce || !wp_verify_nonce($nonce, 'verify_otp_nonce')) {
			wp_send_json_error(array(
				'verified' => false,
				'message'  => __('Nonce verification failed.', 'customer-email-verification'),
			));
		}
		// Validate email input.
		$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
		if (!is_email($email)) {
			wp_send_json_error(array(
				'not_valid' => true,
				'message'   => __('Invalid email address.', 'customer-email-verification'),
			));
		}
		global $wpdb;
		// Check if email exists in the custom verification log table.
		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}cev_user_log WHERE email = %s", $email));
		if ($row && $row->verified) {
			wp_send_json_success(array(
				'already_verify' => true,
				'message'        => __('Email is already verified.', 'customer-email-verification'),
			));
		}
		// Check if email exists in WordPress users.
		if (email_exists($email)) {
			wp_send_json_success(array(
				'exists'  => true,
				'message' => __('An account with this email already exists.', 'customer-email-verification'),
			));
		}
		// Use WP_Error to handle registration errors.
		$errors = new WP_Error();
		$errors = apply_filters( 'woocommerce_registration_errors', $errors, '', $email );
		$errors = apply_filters( 'registration_errors', $errors, '', $email );
		if ( $errors->has_errors() ) {
			wp_send_json_success(array(
				'validation' => false,
				'message' => $errors->get_error_message(),
			));
		}
		// Send OTP email for verification.
		$email_sent = $this->send_signup_verification_email($email);
		if ($email_sent) {
			wp_send_json_success(array(
				'email'   => $email,
				'message' => __('Verification email has been sent.', 'customer-email-verification'),
			));
		} 
	}

	public function send_signup_verification_email( $recipient ) {

		$verification_pin =  cev_pro()->WC_customer_email_verification_email_Common->generate_verification_pin();
		$CEV_Customizer_Options = new CEV_Customizer_Options();
		$expire_time =  get_option('cev_verification_code_expiration', 'never');
		$secret_code = md5( $recipient . time() );
		
		if ( empty($expire_time) ) {
			$expire_time = 'never';
		}
		
		global $wpdb;
		$email_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}cev_user_log WHERE email = %s", $recipient));
		$current_time = current_time('mysql');
		if ($email_exists) {
			// Update the existing record
			$wpdb->update(
				"{$wpdb->prefix}cev_user_log",
				array(
					
					'pin' => $verification_pin,
					'verified' => false,
					'secret_code' => $secret_code, // add secret_code
					'last_updated' => $current_time, // add last_updated
					
				),
				array('email' => $recipient),
				array(
					'%s', // pin
					'%d', // verified
					'%s', // secret_code
					'%s', // last_updated
				),
				array('%s') // email
			);
		} else {
			// Insert a new record
			$wpdb->insert(
				"{$wpdb->prefix}cev_user_log",
				array(
					'email' => $recipient,
					'pin' => $verification_pin,
					'verified' => false,
					'secret_code' => $secret_code, // add secret_code
					'last_updated' => $current_time, // add last_updated
				),
				array(
					'%s', // email
					'%s', // pin
					'%d', // verified
					'%s', // secret_code
					'%s', // last_updated
				)
			);
		}
		cev_pro()->WC_customer_email_verification_email_Common->registerd_user_email  = $recipient;
		$result        = false;
		$email_subject = get_option('cev_verification_email_subject', $CEV_Customizer_Options->defaults['cev_verification_email_subject']);
		$email_subject = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_subject );
		$email_heading = get_option('cev_verification_email_heading', $CEV_Customizer_Options->defaults['cev_verification_email_heading']);		
		
		$mailer = WC()->mailer();
	
		$content = get_option('cev_verification_email_body', $CEV_Customizer_Options->defaults['cev_verification_email_body']);
		$content = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $content );
		$footer_content = get_option('cev_new_verification_Footer_content');
		
		$email_content = '';
		
		$local_template	= get_stylesheet_directory() . '/woocommerce/emails/cev-email-verification.php';
		if ( file_exists( $local_template ) && is_writable( $local_template )) {
			$email_content.= wc_get_template_html( 'emails/cev-email-verification.php', array(
				'email_heading' => $email_heading,
				'content' => $content,
				'footer_content' => $footer_content,
			), 'customer-email-verification/', get_stylesheet_directory() . '/woocommerce/' );
		} else {
			$email_content.= wc_get_template_html( 'emails/cev-email-verification.php', array( 
				'email_heading' => $email_heading,
				'content' => $content,
				'footer_content' => $footer_content,					
			), 'customer-email-verification/', cev_pro()->get_plugin_path() . '/templates/' );
		}
		
		// create a new email
		$email = new WC_Email();
		$email->id = 'CEV_Registration_Verification';
		$email_body = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $email_content ) ) );
		$email_body = apply_filters( 'wc_cev_decode_html_content', $email_body );
		
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
				
		$result = wp_mail( $recipient, $email_subject, $email_body, $email->get_headers() );
		wp_send_json_success(array('email' => $result));
	}

	/**
	 * Get the from address for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_address() {
		$from_address = apply_filters( 'woocommerce_email_from_address', get_option( 'woocommerce_email_from_address' ), $this );
		$from_address = apply_filters( 'cev_email_from_address', $from_address, $this );
		return sanitize_email( $from_address );
	}
	
	/**
	 * Get the from name for outgoing emails.
	 *
	 * @return string
	 */
	public function get_from_name() {
		$from_name = apply_filters( 'woocommerce_email_from_name', get_option( 'woocommerce_email_from_name' ), $this );
		$from_name = apply_filters( 'cev_email_from_name', $from_name, $this );
		return wp_specialchars_decode( esc_html( $from_name ), ENT_QUOTES );
	}

	public function verify_otp() {
		
		global $wpdb;
		// Verify nonce
		$nonce = isset($_POST['nonce']) ? sanitize_text_field($_POST['nonce']) : '';
		if (!$nonce || !wp_verify_nonce($nonce, 'verify_otp_nonce')) {
			wp_send_json_error(array('verified' => false, 'message' => __('Nonce verification failed.', 'customer-email-verification')));
		}
		if (!isset($_POST['otp'])) {
			wp_send_json_error(array('verified' => false));
		}
		$otp = isset($_POST['otp']) ? sanitize_text_field($_POST['otp']) : '';
		$email = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';

		// Assume the OTP is "123456" for demonstration purposes
		if ( '' == $otp ) {
			echo json_encode( array( 'success' => 'false' ));
			die();
		}
		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}cev_user_log WHERE email = %s AND pin = %s", $email, $otp));
		if ($row) {
			if ($row->verified) {
				wp_send_json_error(array('verified' => false, 'message' => __('Already verified.', 'customer-email-verification')));
			} else {
				$wpdb->delete(
					"{$wpdb->prefix}cev_user_log",
					array('id' => $row->id),
					array('%d')
				);
				wp_send_json_success(array('verified' => true, 'message' => __('Registration and verification successful', 'customer-email-verification'), 'redirect_url' =>  home_url() . '/my-account/'));
						
				
			}
		} else {
			wp_send_json_error(array('verified' => false, 'message' =>  __('The OTP you entered is incorrect. Please check your email and try again.', 'customer-email-verification')));
		}
	}
	public function generate_verification_pin() {
		
		$code_length = get_option('cev_verification_code_length', 4);
		
		if ( '1' == $code_length ) {
			$digits = 4;
		} else if ( '2' == $code_length ) {
			$digits = 6;
		} else {
			$digits = 9;
		}
		
		$i = 0; //counter
		$pin = ''; //our default pin is blank.
		while ( $i < $digits ) {
			//generate a random number between 0 and 9.
			$pin .= mt_rand(0, 9);
			$i++;
		}		
		return $pin;
	}	
}

// Initialize the class
CEV_Signup_Verification::get_instance()->init();
