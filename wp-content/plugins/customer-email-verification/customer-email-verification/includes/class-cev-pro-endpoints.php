<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class CEV_Endpoints
 * Handles the addition of custom endpoints, styles, and scripts.
 */
class CEV_Endpoints {

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
	* @return woo_customer_email_verification_Admin
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initializes hooks for frontend styles, menu items, and endpoints.
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'front_styles' ));
		add_filter( 'woocommerce_account_menu_items', array( $this, 'cev_account_menu_items' ), 10, 1 );
		add_action( 'init', array( $this, 'cev_add_my_account_endpoint' ) );
		add_action( 'woocommerce_account_email-verification_endpoint', array( $this, 'cev_email_verification_endpoint_content' ) );				
		add_filter( 'woocommerce_account_menu_items', array( $this, 'hide_cev_menu_my_account' ), 999 );		
		add_filter( 'wcml_register_endpoints_query_vars', array( $this, 'register_endpoint_WPML' ), 10, 3 );	
		add_filter( 'wcml_endpoint_permalink_filter', array( $this, 'endpoint_permalink_filter' ), 10, 2 ) ;
		add_filter( 'woocommerce_get_query_vars', array( $this, 'add_query_vars' ) );        
	}

	/**
	 * Enqueues frontend styles and scripts.
	 */
	public function front_styles() {	
		$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

		// Register scripts and styles.
		wp_register_script('cev-underscore-js', cev_pro()->plugin_dir_url() . 'assets/js/underscore-min.js', array('jquery'), cev_pro()->version, true);
		wp_register_script('cev-front-js', cev_pro()->plugin_dir_url() . 'assets/js/front.js', array('jquery'), cev_pro()->version, true);
		wp_register_script('cev-inline-front-js', cev_pro()->plugin_dir_url() . 'assets/js/inline-verification.js', array('jquery'), cev_pro()->version, true);
		wp_register_style('cev_front_style', cev_pro()->plugin_dir_url() . 'assets/css/front.css', array(), cev_pro()->version);
		wp_register_script('jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array('jquery'), '2.70', true);


		if ( is_plugin_active( 'checkout-for-woocommerce/checkout-for-woocommerce.php' ) ) {
			$checkoutWC = true;
		} else {
			$checkoutWC = false;
		}
		
		wp_localize_script(
			'cev-front-js',
			'cev_ajax_object',
			array(					
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'checkout_send_verification_email' => wp_create_nonce( 'checkout-send-verification-email' ),
				'checkout_verify_code' => wp_create_nonce( 'checkout-verify-code' ),
				'cev_login_auth_with_otp_nonce' => wp_create_nonce( 'cev_login_auth_with_otp' ),
				'wc_cev_email_guest_user' => wp_create_nonce( 'wc_cev_email_guest_user' ),
				'cev_verification_checkout_dropdown_option' => get_option('cev_verification_checkout_dropdown_option'),
			)
		);
		
		$email_verified_msg = get_option('cev_verification_success_message', '');

		if ( empty($email_verified_msg) ) {
			$email_verified_msg = __( 'Your email is verified!', 'customer-email-verification' );
		}
		$resend_limit_message = get_option('cev_resend_limit_message', '');

		if ( empty($resend_limit_message) ) {
			$resend_limit_message = __( 'Too many attempts, please contact us for further assistance', 'customer-email-verification' );
		}
		wp_localize_script(
			'cev-inline-front-js',
			'cev_ajax_object',
			array(					
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'checkout_send_verification_email' => wp_create_nonce( 'checkout-send-verification-email' ),
				'checkout_verify_code' => wp_create_nonce( 'checkout-verify-code' ),
				'wc_cev_email_guest_user' => wp_create_nonce( 'wc_cev_email_guest_user' ),
				'checkoutWC' => $checkoutWC,
				'verification_send_msg' => __( 'We sent a verification code to your email', 'customer-email-verification' ),
				'email_verification_code_label' => __( 'Email verification code', 'customer-email-verification' ),
				'verification_code_error_msg' => __( 'Verification code does not match', 'customer-email-verification' ),
				'verify_button_text' => __( 'Verify', 'customer-email-verification' ),
				'resend_verification_label' => __( 'Resend verification code', 'customer-email-verification' ),
				'email_verified_msg' => $email_verified_msg,
				'cev_resend_limit_message' => $resend_limit_message,

			)
		);
		
		global $wp;	
		$current_slug = add_query_arg( array(), $wp->request );
		$email_verification_url = rtrim(wc_get_account_endpoint_url( 'email-verification' ), '/');
		$login_authentication_url = rtrim(wc_get_account_endpoint_url( 'login-authentication' ), '/');
		
		if ( rtrim( home_url( $wp->request ), '/' ) == $email_verification_url || rtrim( home_url( $wp->request ), '/' ) == $login_authentication_url ) {	
			wp_enqueue_style( 'cev_front_style' );	
			wp_enqueue_script( 'cev-underscore-js' );				
			wp_enqueue_script( 'cev-front-js' );
			wp_enqueue_script( 'jquery-blockui' );			
		}	
	
		
		if ( is_checkout() || is_cart() ) {	
			wp_enqueue_style( 'cev_front_style' );			
			wp_enqueue_script( 'cev-underscore-js' );
			wp_enqueue_script( 'cev-front-js' );			
			wp_enqueue_script( 'jquery-blockui' );			
			
			$cev_enable_email_verification_checkout = get_option( 'cev_enable_email_verification_checkout', 0 );
			$cev_inline_email_verification_checkout = get_option('cev_verification_checkout_dropdown_option');
			$cev_enable_email_verification_free_orders  = get_option('cev_enable_email_verification_free_orders');
			$order_subtotal = WC()->cart->subtotal;
			
			$need_inline_verification = false;
			if ( ( $order_subtotal > 0 && 1 != $cev_enable_email_verification_free_orders ) ) {				
				$need_inline_verification = true;
			} elseif ( 0 == $order_subtotal && 1 == $cev_enable_email_verification_checkout && 2 == $cev_inline_email_verification_checkout ) {			
				$need_inline_verification = true;
			}
			
			if ( 1 == $cev_enable_email_verification_checkout && 2 == $cev_inline_email_verification_checkout && $need_inline_verification && !is_user_logged_in() ) {
				wp_enqueue_script( 'cev-inline-front-js' );
			}
			
		}
				
		global $wp;
		$request = explode( '/', $wp->request );
		if ( ( end($request) == 'edit-account' ) && is_account_page() ) {
			$cev_temp_email = get_user_meta( get_current_user_id(), 'cev_temp_email', true);
			if ( null != $cev_temp_email ) {
				wp_enqueue_script( 'cev-underscore-js' );
				wp_enqueue_script( 'cev-front-js' );
				wp_enqueue_style( 'dashicons' );	
			}
		}
	}

	/**
	 * Adds custom menu items to the WooCommerce My Account menu.
	 *
	 * @param array $items Existing menu items.
	 * @return array Modified menu items.
	 */
	public function cev_account_menu_items( $items ) {
		$items['email-verification'] = __( 'Sign up email verification', 'customer-email-verification' );
		$items['login-authentication'] = __( 'Login Authentication', 'customer-email-verification' );
		return $items;
	}
		
	/**
	 * Adds custom endpoints to WooCommerce My Account.
	 */
	public function cev_add_my_account_endpoint() {
		add_rewrite_endpoint( 'email-verification', EP_PAGES );
		add_rewrite_endpoint( 'login-authentication', EP_PAGES );		
		if (version_compare(get_option( 'cev_version' ), '1.7', '<') ) {
			global $wp_rewrite;
			$wp_rewrite->set_permalink_structure('/%postname%/');
			$wp_rewrite->flush_rules();
			//flush_rewrite_rules();
			update_option( 'cev_version', '1.7');				
		}
	}

	/**
	 * Displays content for the email verification endpoint.
	 */
	public function cev_email_verification_endpoint_content() {
		
		$current_user = wp_get_current_user();
		$email = $current_user->user_email;				
		$verified  = get_user_meta( get_current_user_id(), 'customer_email_verified', true );
		
		if ( cev_pro()->admin->is_admin_user( get_current_user_id() ) || cev_pro()->admin->is_verification_skip_for_user( get_current_user_id() ) ) {
			return;
		}
		
		if ( 'true' === $verified ) {
			return;
		}
		
		// Include the HTML file for the popup.
		$template_path = cev_pro()->get_plugin_path() . '/includes/views/email_verification_popup.php';

		if (file_exists($template_path)) {
			include $template_path;
		}
	}

	/**
	 * Hides custom menu items in the My Account menu.
	 *
	 * @param array $items Existing menu items.
	 * @return array Modified menu items.
	 */			
	public function hide_cev_menu_my_account( $items ) {
		unset($items['email-verification']);
		unset($items['login-authentication']);
		return $items;
	}

	/**
	 * Registers custom query vars for WPML compatibility.
	 *
	 * @param array $query_vars Existing query vars.
	 * @param array $wc_vars WooCommerce query vars.
	 * @param object $obj WPML object.
	 * @return array Modified query vars.
	 */
	public function register_endpoint_WPML( $query_vars, $wc_vars, $obj ) {
		$query_vars['email-verification'] = $obj->get_endpoint_translation('email-verification', isset( $wc_vars['email-verification']) ? $wc_vars['email-verification'] : 'email-verification' );
		$query_vars['login-authentication'] = $obj->get_endpoint_translation('login-authentication', isset( $wc_vars['login-authentication']) ? $wc_vars['login-authentication'] : 'login-authentication' );
		return $query_vars;
	}

	/**
	 * Modifies the permalink filter for custom endpoints.
	 *
	 * @param string $endpoint Endpoint slug.
	 * @param string $key Endpoint key.
	 * @return string Modified endpoint slug.
	 */
	public function endpoint_permalink_filter( $endpoint, $key ) {
		if ( 'email-verification' == $key ) { 
			return 'email-verification';
		}
		if ( 'login-authentication' == $key ) { 
			return 'login-authentication';
		}
		return $endpoint;
	}

	/**
	 * Adds custom query vars for WooCommerce endpoints.
	 *
	 * @param array $query_vars Existing query vars.
	 * @return array Modified query vars.
	 */
	public function add_query_vars( $query_vars ) {
		$query_vars['email-verification'] = 'email-verification';
		$query_vars['login-authentication'] = 'login-authentication';
		return $query_vars;
	}
}
