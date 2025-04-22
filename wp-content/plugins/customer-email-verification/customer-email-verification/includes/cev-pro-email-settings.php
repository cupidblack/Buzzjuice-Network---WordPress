<?php
/**
 * CEV pro admin 
 *
 * @class   cev_pro_admin_email_settings
 * @package WooCommerce/Classes
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cev pro admin email settings class.
 */
class Customer_Email_Verification_Email_Settings {

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
	public $my_account;
	public function __construct() {
		$this->init();
		$this->my_account = get_option( 'woocommerce_myaccount_page_id' ); 
	}
	
	/*
	 * init function
	 *
	 * @since  1.0
	*/
	public function init() {
		
		//adding hooks
		$is_checkout = isset( $_GET['wc-ajax'] ) && ( 'checkout' == $_GET['wc-ajax'] || 'complete_order' == $_GET['wc-ajax'] ) ? true : false;
		if ( get_option('delay_new_account_email_customer') == '1' && !$is_checkout ) {
			add_action( 'woocommerce_email', array( $this,'disable_account_creation_email_pro' ) );
			add_filter( 'woocommerce_mail_callback_params', array( $this,'woocommerce_mail_callback_params' ), 10, 2 );
			add_action( 'cev_new_email_enable', array( $this,'enable_new_account_creation_email_pro' ) );
		}				
		//  starts
		//
		add_action( 'woocommerce_checkout_process', array( $this, 'register_account_notification_on_checkout' ) );
		//
		add_action( 'woocommerce_checkout_update_customer', array( $this, 'auto_varify_user_on_checkout' ), 10, 2 );
		// end
			
		add_action( 'template_redirect', array( $this, 'disable_checkout_page_for_logged_in_user') );		
		add_action( 'wp_footer', array( $this, 'disable_checkout_page_for_guest_user') );
		add_action( 'wp_ajax_checkout_page_send_verification_code', array( $this, 'checkout_page_send_verification_code') );
		add_action( 'wp_ajax_nopriv_checkout_page_send_verification_code', array( $this, 'checkout_page_send_verification_code') );		
		add_action( 'wp_ajax_nopriv_resend_verification_code_guest_user', array( $this, 'checkout_page_send_verification_code') );
		add_action( 'wp_ajax_resend_verification_code_guest_user', array( $this, 'checkout_page_send_verification_code') );
		add_action( 'wp_ajax_checkout_page_verify_code', array( $this, 'checkout_page_verify_code') );
		add_action( 'wp_ajax_nopriv_checkout_page_verify_code', array( $this, 'checkout_page_verify_code') );
		add_action( 'wp', array( $this, 'authenticate_guest_user_by_email_link' ) );	
		add_action( 'woocommerce_after_checkout_validation', array( $this,'after_checkout_validation' ), 10, 2);	
		
		add_action( 'woocommerce_save_account_details_errors', array( $this, 'validate_email_on_change_from_edit_acccount' ), 10, 2 );
		add_action( 'woocommerce_edit_account_form_start', array( $this, 'add_email_verification_field_edit_account') );
		add_action( 'wp_footer', array( $this, 'set_cev_temp_email_in_my_account' ) );
		add_action( 'wp_ajax_verify_email_on_edit_account', array( $this, 'verify_email_on_edit_account') );
		add_action( 'wp_ajax_resend_verification_code_on_edit_account', array( $this, 'resend_verification_code_on_edit_account'), 10, 1 );
		
		add_action( 'wp', array( $this, 'verify_change_user_by_email_by_email' ) );
		
		add_action( 'wp_ajax_send_email_on_chekout_page', array( $this, 'send_email_on_chekout_page'), 10 , 1);
		add_action( 'wp_ajax_nopriv_send_email_on_chekout_page', array( $this, 'send_email_on_chekout_page'), 10, 1);			
		
		add_action( 'wp_footer', array( $this, 'cev_display_inline_verification_on_checkout' ), 100 );	
		add_action( 'woocommerce_review_order_before_submit', array( $this, 'woocommerce_review_order_before_submit' ) );
		add_action('wp_ajax_custom_email_verification', array( $this, 'custom_email_verification'));
		add_action('wp_ajax_nopriv_custom_email_verification', array( $this, 'custom_email_verification'));
	}
	

	public function custom_email_verification() {
		if ( is_user_logged_in() ) { 
			echo json_encode( array( 'success' => 'login_user' ));
			die();	
			return;
		}
		// Retrieve the email value from the AJAX request.
		check_ajax_referer( 'wc_cev_email_guest_user', 'wp_nonce' );
		$email = isset( $_POST['email'] ) ? wc_clean( $_POST['email'] ) : '';
	
		$verification_data = array(
			'email' => $email,
			'verified' => false,
		);
		WC()->session->set( 'cev_user_verified_data', json_encode($verification_data) );
		$verified = $this->check_email_verify( $email );
		
		if ( true == $verified ) {
			$verification_data = array(
				'email' => $email,
				'verified' => true,
			);	

			//echo '<pre>';print_r( $verification_data );echo '</pre>';exit;

			if ( ! WC()->session->has_session() ) {
				WC()->session->set_customer_session_cookie( true );
			}

			WC()->session->set( 'cev_user_verified_data', json_encode($verification_data) );			
			WC()->customer->set_billing_email( $email );
			echo json_encode( array( 'success' => 'alredy_verify' ));
			die();	
		} else { 
		$result = $this->send_verification_email_to_guest_user( $email );
		
			$cev_user_verified_data_raw = WC()->session->get( 'cev_user_verified_data' );
			$cev_user_verification_data = ! is_null( $cev_user_verified_data_raw ) ? json_decode( $cev_user_verified_data_raw ) : null;
			$cev_redirect_limit_resend = get_option('cev_redirect_limit_resend', 1);
			//echo $cev_redirect_limit_resend; 
			if ( isset( $cev_user_verification_data->cev_guest_user_resend_times ) ) {
				$cev_guest_user_resend_times = $cev_user_verification_data->cev_guest_user_resend_times;
			} else {
				$cev_guest_user_resend_times = 0;
			}
			//echo  $cev_guest_user_resend_times;
			if ( $cev_guest_user_resend_times >= $cev_redirect_limit_resend+1 ) {
				$cev_resend_limit_reached = 'true';
			} else { 
				$cev_resend_limit_reached = 'false';
			}
			//print_r($result);
			if ( $result ) {			
				echo json_encode( array( 'success' => 'true', 'cev_resend_limit_reached' => $cev_resend_limit_reached ));
				die();
			} else {
				echo json_encode( array( 'success' => 'false', 'cev_resend_limit_reached' => $cev_resend_limit_reached ));
				die();	
			}
			
		
		}
	
		// Always exit to prevent extra output.
		wp_die();
	
	}
	public function register_account_notification_on_checkout() {
		// separte verification removed
		remove_action( 'woocommerce_created_customer_notification', array( cev_pro()->email, 'new_user_registration_from_registration_form' ), 10, 3 );		
	}	
	
	public function auto_varify_user_on_checkout( $customer, $data ) {
		update_user_meta( $customer->get_id(), 'customer_email_verified', 'true' );
	}		
	
	/*
	* Disable sending customer New Account email if enable 'Delay new Account Email to After Email Verification' option
	*/
	public function disable_account_creation_email_pro( $email_class ) {
		remove_action( 'woocommerce_created_customer_notification', array( $email_class, 'customer_new_account' ), 10, 3 );	
	}
	
	/* 
	* Trigger New Account email when customer register After customer verify email  
	*/
	public function enable_new_account_creation_email_pro( $user_id ) {
		
		$emails = WC()->mailer()->emails;
		$email = $emails['WC_Email_Customer_New_Account'];
		
		$new_customer_data = get_userdata( $user_id );		
		$user_pass = isset( $new_customer_data->user_pass ) ? $new_customer_data->user_pass : '';		
		$password_generated = false;

		$generate_password = get_option( 'woocommerce_registration_generate_password', 'no' );

		if ( 'yes' == $generate_password ) {
			$user_pass           = wp_generate_password();
			$password_generated = true;
			
			wp_set_password($user_pass, $user_id);		
			
			wp_set_current_user( $user_id, $new_customer_data->user_login );
			wp_set_auth_cookie( $user_id );		
		}

		$email->trigger( $user_id, $user_pass, $password_generated );			
	}

	public function woocommerce_mail_callback_params( $params, $email ) {				
		if ( is_a( $email, 'WC_Email_Customer_New_Account' ) ) {
			if ( is_user_logged_in() ) {
				$cev_enable_email_verification = get_option('cev_enable_email_verification', 1);
				if ( !cev_pro()->admin->is_admin_user( get_current_user_id() )  && !cev_pro()->admin->is_verification_skip_for_user( get_current_user_id() ) && 1 == $cev_enable_email_verification ) {
					$verified = get_user_meta( get_current_user_id(), 'customer_email_verified', true );
					$cev_email_verification_pin = get_user_meta( get_current_user_id(), 'cev_email_verification_pin', true );
					if ( !empty($cev_email_verification_pin) ) {
						if ( 'true' !== $verified ) {
							$params['0'] = '';
							return $params;
						} elseif ( 'true' == $verified ) {
							return $params;
						}
					}
				}				
			} else {
				$user = get_user_by( 'email', $params['0'] );
				if ( $user ) {
					$user_id = $user->ID;
					$verified = get_user_meta( $user_id, 'customer_email_verified', true );
					$cev_email_verification_pin = get_user_meta( $user_id, 'cev_email_verification_pin', true );
					if ( !empty($cev_email_verification_pin) ) {
						if ( 'true' !== $verified ) {
							$params['0'] = ''; 
							return $params;
						} elseif ( 'true' == $verified ) {
							return $params;
						}
					}
				}
			}
			
		}
		return $params;
	}
	
	/* 
	* Block cart and checkout page
	*/   
	public function disable_checkout_page_for_logged_in_user() {

		if ( is_checkout() && is_wc_endpoint_url('order-received') ) {      
			return;
		}
		if ( !is_user_logged_in() ) { 
			return;
		}
		if ( !is_checkout() && !is_cart() ) {
			return;             
		}
		
		$cev_enable_email_verification = get_option('cev_enable_email_verification', 1);
		//echo get_option('cev_enable_email_verification_checkout');    
		
		if ( 1 != $cev_enable_email_verification  ) {
			return;
		}
		
		$verified = get_user_meta( get_current_user_id(), 'customer_email_verified', true );

		if ( 'true' == $verified ) {
			return;
		}

		$cev_enable_email_verification_free_orders = get_option('cev_enable_email_verification_free_orders');
		$order_subtotal = WC()->cart->total;
		
		$redirect_url = wc_get_account_endpoint_url( 'email-verification' );
		$email_verification_url = rtrim(wc_get_account_endpoint_url( 'email-verification' ), '/');
		global $wp;                                                                                         
			
		if ( !cev_pro()->admin->is_admin_user( get_current_user_id() )  && !cev_pro()->admin->is_verification_skip_for_user( get_current_user_id() ) && ( ( get_option('cev_enable_email_verification_checkout') == '1' && is_checkout() ) || ( get_option('cev_enable_email_verification_cart_page') == '1' && get_option('cev_enable_email_verification_checkout') == '1' && is_cart() ) ) ) {
			
			$cev_email_verification_pin = get_user_meta( get_current_user_id(), 'cev_email_verification_pin', true );
			$need_verification = false;
			
			if ( empty( $cev_email_verification_pin ) ) {
				$need_verification = true;
			} elseif ( $order_subtotal > 0 && 1 == $cev_enable_email_verification_free_orders ) {
				$need_verification = false;
			} else {
				$need_verification = true;
			}
			if ( $need_verification ) {
				if (home_url( $wp->request ) != $email_verification_url) {
					wp_safe_redirect( $redirect_url );
						exit;
				}
			}
		}
	}
	
	/* 
	* Block cart and checkout page
	*/	 
	public function disable_checkout_page_for_guest_user() {       
		
		$cev_enable_email_verification_checkout = get_option( 'cev_enable_email_verification_checkout', 1 );
		$cev_inline_email_verification_checkout = get_option('cev_verification_checkout_dropdown_option');
		
		if ( is_user_logged_in() ) {
			return;
		}
		
		if ( !is_checkout() && !is_cart() ) {
			return;
		}
		
		if ( 1 != $cev_enable_email_verification_checkout ) {
			return;
		}
		
		if ( 2 == $cev_inline_email_verification_checkout  ) {
			return;
		}
		
		$cev_enable_email_verification_free_orders  = get_option('cev_enable_email_verification_free_orders');		
		$cev_skip_verification_for_selected_roles = get_option('cev_skip_verification_for_selected_roles');
		$order_subtotal = WC()->cart->total;		
		
		if ( ( 1 == get_option( 'cev_enable_email_verification_checkout' ) && is_checkout() ) || ( 1 == get_option('cev_enable_email_verification_cart_page') && '1' == get_option('cev_enable_email_verification_checkout') && is_cart() ) ) {
			
			$cev_user_verified_data_raw = WC()->session->get('cev_user_verified_data');
			$cev_user_verified_data = !is_null($cev_user_verified_data_raw) ? json_decode($cev_user_verified_data_raw) : null;
			$customer_email = WC()->customer->get_billing_email();						
			
			$need_verification = false;
			
			if ( isset($cev_user_verified_data->email) && $cev_user_verified_data->email == $customer_email && 1 == $cev_user_verified_data->verified ) {
				$need_verification = false;
			} elseif ( $order_subtotal > 0 && 1 == $cev_enable_email_verification_free_orders ) {
				$need_verification = false;
			} else { 
				$need_verification = true;
			}  
			
			if ( $need_verification ) {				
				
				$CEV_Customizer_Options = new CEV_Customizer_Options();
				$cev_button_color_widget_header =  get_option('cev_button_color_widget_header', '#2296f3');
				$cev_button_text_color_widget_header =  get_option('cev_button_text_color_widget_header', '#ffffff');
				$cev_button_text_size_widget_header =  get_option('cev_button_text_size_widget_header', '14');

				$cev_verification_overlay_color = get_option('cev_verification_popup_overlay_background_color', $CEV_Customizer_Options->defaults['cev_verification_popup_overlay_background_color']);
				$cev_widget_header_image_width = get_option('cev_widget_header_image_width', $CEV_Customizer_Options->defaults['cev_widget_header_image_width']); ?>
				
				<style>
				.cev-authorization-grid__visual{
					background: <?php esc_html__( cev_pro()->hex2rgba($cev_verification_overlay_color, '0.7') ); ?>;	
				}		
				</style>
				<?php 
				require_once( 'views/verify_checkout_guest_user.php' );
			}
		}
		
	}
	
	/* 
	* Send verification code to guest user on checkout page
	*/
	public function checkout_page_send_verification_code() {			
		
		check_ajax_referer( 'wc_cev_email_guest_user', 'wp_nonce' );
		
		$email = isset( $_POST['email'] ) ? wc_clean( $_POST['email'] ) : '';
		$verified = $this->check_email_verify( $email );
		
		if ( true == $verified ) {
			$verification_data = array(
				'email' => $email,
				'verified' => true,
			);	

			//echo '<pre>';print_r( $verification_data );echo '</pre>';exit;

			if ( ! WC()->session->has_session() ) {
				WC()->session->set_customer_session_cookie( true );
			}

			WC()->session->set( 'cev_user_verified_data', json_encode($verification_data) );			
			WC()->customer->set_billing_email( $email );
			echo json_encode( array( 'success' => 'alredy_verify' ));
			die();	
		} else { 
			$result = $this->send_verification_email_to_guest_user( $email );
		
			$cev_user_verified_data_raw =  WC()->session->get( 'cev_user_verification_data' );
			$cev_user_verification_data = ! is_null( $cev_user_verified_data_raw ) ? json_decode( $cev_user_verified_data_raw ) : null;
			$cev_redirect_limit_resend = get_option('cev_redirect_limit_resend', 1);
			//echo $cev_redirect_limit_resend; 
			if ( isset( $cev_user_verification_data->cev_guest_user_resend_times ) ) {
				$cev_guest_user_resend_times = $cev_user_verification_data->cev_guest_user_resend_times;
			} else {
				$cev_guest_user_resend_times = 0;
			}
			//echo  $cev_guest_user_resend_times;
			if ( $cev_guest_user_resend_times >= $cev_redirect_limit_resend+1 ) {
				$cev_resend_limit_reached = 'true';
			} else { 
				$cev_resend_limit_reached = 'false';
			}
			//print_r($result);
			if ( $result ) {			
				echo json_encode( array( 'success' => 'true', 'cev_resend_limit_reached' => $cev_resend_limit_reached ));
				die();
			} else {
				echo json_encode( array( 'success' => 'false', 'cev_resend_limit_reached' => $cev_resend_limit_reached ));
				die();	
			}
		}
				
	}		
	
	/* 
	* resend verification email to guest user from Cart or Checkout page
	*/
	public function send_verification_email_to_guest_user( $recipient ) {
		
		$CEV_Customizer_Options = new CEV_Customizer_Options();
		$verification_pin = cev_pro()->WC_customer_email_verification_email_Common->generate_verification_pin();
		$expire_time =  get_option('cev_verification_code_expiration', 'never');
		$cev_redirect_limit_resend = get_option('cev_redirect_limit_resend', 1);
		$cev_user_verification_data_raw = WC()->session->get( 'cev_user_verification_data' );
		$cev_user_verification_data = ! is_null( $cev_user_verification_data_raw ) ? json_decode( $cev_user_verification_data_raw ) : null;
		if ( isset( $cev_user_verification_data->cev_guest_user_resend_times ) ) {
			$cev_guest_user_resend_times = $cev_user_verification_data->cev_guest_user_resend_times;
		} else {
			$cev_guest_user_resend_times = 0;
		}
		if ( $cev_guest_user_resend_times >= $cev_redirect_limit_resend+1 ) {
			return false;
		} else {
			$cev_guest_user_resend_times = ++$cev_guest_user_resend_times;
		}
		if ( empty($expire_time) ) {
			$expire_time = 'never';
		}
		$secret_code = md5( $recipient . time() );
		$verification_data = array(
			'email' => $recipient,
			'pin' => base64_encode( $verification_pin ),
			'secret_code' => $secret_code,
			'startdate' => time(),
			'enddate' => time() + ( int ) $expire_time,
			'cev_guest_user_resend_times' => $cev_guest_user_resend_times
		);
		WC()->session->set( 'cev_user_verification_data', json_encode($verification_data) );
		$result = false;
		$email_subject = get_option('cev_verification_email_subject_che', $CEV_Customizer_Options->defaults['cev_verification_email_subject_che']);
		$email_subject = $this->maybe_parse_merge_tags( $email_subject );
		$email_heading = get_option('cev_verification_email_heading_che', $CEV_Customizer_Options->defaults['cev_verification_email_heading_che']);
		add_filter( 'wp_kses_allowed_html', array( cev_pro()->WC_customer_email_verification_email_Common, 'my_allowed_tags' ) );
		add_filter( 'safe_style_css', array( cev_pro()->WC_customer_email_verification_email_Common, 'safe_style_css_callback' ), 10, 1 );
		$mailer = WC()->mailer();
		$content = get_option('cev_verification_email_body_che', $CEV_Customizer_Options->defaults['cev_verification_email_body_che']);
		$content = $this->maybe_parse_merge_tags( $content );
		$footer_content = get_option('cev_new_verification_Footer_content_che');
		$email_content = '';
		$local_template = get_stylesheet_directory() . '/woocommerce/emails/cev-email-verification.php';
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
		$email->id = 'CEV_Guset_User_Verification';
		$email_body = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $email_content ) ) );
		$email_body = apply_filters( 'wc_cev_decode_html_content', $email_body );
		add_filter( 'wp_mail_from', array( cev_pro()->WC_customer_email_verification_email_Common, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( cev_pro()->WC_customer_email_verification_email_Common, 'get_from_name' ) );
		$result = wp_mail( $recipient, $email_subject, $email_body, $email->get_headers() );
		return $result;
	}
	
	/* css display inline block */
	
	public function my_allowed_tags( $tags ) {
		$tags['style'] = array( 'type' => true, );
		return $tags;
	}
	/* css display inline block */
	
	public function safe_style_css_callback( $styles ) {
		 $styles[] = 'display';
		return $styles;
	}
	
	/*
	 * Function for verify pin code
	*/ 
	public function checkout_page_verify_code() {
		
		check_ajax_referer( 'checkout-verify-code', 'security' );
		
		$post_pin = isset( $_POST['pin'] ) ? wc_clean( $_POST['pin'] ) : '';				
		// echo $post_pin. "sdffffff";
		if ( '' == $post_pin ) {
			echo json_encode( array( 'success' => 'false' ));
			die();
		}
		
		$cev_user_verification_data_raw = WC()->session->get( 'cev_user_verification_data' );
		$cev_user_verification_data = ! is_null( $cev_user_verification_data_raw ) ? json_decode( $cev_user_verification_data_raw ) : null;		
		// print_r($cev_user_verification_data);
		if ( empty( $cev_user_verification_data ) ) {
			//echo $post_pin. "sdffffff";
			echo json_encode( array( 'success' => 'false' ));
			die();
		}
		
		$session_pin = base64_decode( $cev_user_verification_data->pin );
		$session_email = $cev_user_verification_data->email;
		// echo $session_pin. "==". $post_pin; exit;
		
		if ( ( $session_pin == $post_pin ) ) {			
			$verified_data = array(
				'email' => $session_email,
				'verified' => true,
			);	
			
			WC()->customer->set_billing_email( $session_email );
			WC()->session->set( 'cev_user_verified_data', json_encode($verified_data) );
			$user = get_user_by( 'email', $session_email );	
			if ( $user ) {
				$user_id = $user->ID;
				update_user_meta( $user_id, 'customer_email_verified', 'true' );
			}
			echo json_encode( array( 'success' => 'true' ));
			die();	
		} else {
			echo json_encode( array( 'success' => 'false' ));
			die();
		}		
	}
	
	public function authenticate_guest_user_by_email_link() {
		if ( isset( $_GET['cusomer_email_verify'] ) && '' !== $_GET['cusomer_email_verify'] ) {
			$cusomer_email_verify = wc_clean( $_GET['cusomer_email_verify'] );
			$user_meta = explode( '@', base64_decode( $cusomer_email_verify ) ); 
			$email_secret_code = $user_meta[0];
			$cev_user_verification_data_raw = WC()->session->get( 'cev_user_verification_data' );
			$cev_user_verification_data = ! is_null( $cev_user_verification_data_raw ) ? json_decode( $cev_user_verification_data_raw ) : null;
			
			if ( !empty($cev_user_verification_data) ) {
				$secret_code = $cev_user_verification_data->secret_code;
				$email = $cev_user_verification_data->email;
				
				if ( $secret_code === $email_secret_code ) {
					$verified_data = array(
						'email' => $email,
						'verified' => true,
					);				
					WC()->customer->set_billing_email( $email );
					WC()->session->set( 'cev_user_verified_data', json_encode($verified_data) );					
				}
			}								
		}
	}
	
	/*
	 * Function for check email verification on checkout process
	*/
	public function after_checkout_validation( $fields, $errors ) {
		$cev_enable_email_verification_free_orders  = get_option('cev_enable_email_verification_free_orders');
		$order_subtotal = WC()->cart->subtotal;
		$need_inline_verification = false;
		if ( ( 1 != $cev_enable_email_verification_free_orders ) ) {
			$need_inline_verification = true;
		} elseif ( 0 == $order_subtotal && 1 == $cev_enable_email_verification_free_orders ) {
			$need_inline_verification = true;
		}
		if ( '1' == get_option( 'cev_enable_email_verification_checkout' ) && $need_inline_verification ) {
			$cev_user_verification_data_raw = WC()->session->get( 'cev_user_verification_data' );
			$cev_user_verified_data = ! is_null( $cev_user_verification_data_raw ) ? json_decode( $cev_user_verification_data_raw ) : null;
			if ( !is_user_logged_in() ) {
				if ( !isset($cev_user_verified_data->email) || $cev_user_verified_data->email != $fields[ 'billing_email' ] || 1 != $cev_user_verified_data->verified ) {
					$message = __( 'Please verify your email address.', 'customer-email-verification' );
					$errors->add( 'validation', $message );
					echo '<input type="hidden" name="validation_error" value="1">';
					?>
						
					<?php
					
				}
			}
		}
	}
	 
	/**
	 * Maybe try and parse content to found the xlwuev merge tags
	 * And converts them to the standard wp shortcode way
	 * So that it can be used as do_shortcode in future
	 *
	 * @param string $content
	 *
	 * @return mixed|string
	 */
	public  function maybe_parse_merge_tags( $content = '' ) {
		$get_all      = self::get_all_tags();
		$get_all_tags = wp_list_pluck( $get_all, 'tag' );

		//iterating over all the merge tags
		if ( $get_all_tags && is_array( $get_all_tags ) && count( $get_all_tags ) > 0 ) {
			foreach ( $get_all_tags as $tag ) {
				$matches = array();
				$re      = sprintf( '/\{{%s(.*?)\}}/', $tag );
				$str     = $content;

				//trying to find match w.r.t current tag
				preg_match_all( $re, $str, $matches );

				//if match found
				if ( $matches && is_array( $matches ) && count( $matches ) > 0 ) {

					//iterate over the found matches
					foreach ( $matches[0] as $exact_match ) {

						//preserve old match
						$old_match        = $exact_match;
						$single           = str_replace( '{{', '', $old_match );
						$single           = str_replace( '}}', '', $single );
						$get_parsed_value = call_user_func( array( __CLASS__, $single ) );
						$content          = str_replace( $old_match, $get_parsed_value, $content );
					}
				}
			}
		}
		if ( $get_all_tags && is_array( $get_all_tags ) && count( $get_all_tags ) > 0 ) {
			foreach ( $get_all_tags as $tag ) {
				$matches = array();
				$re      = sprintf( '/\{%s(.*?)\}/', $tag );
				$str     = $content;

				//trying to find match w.r.t current tag
				preg_match_all( $re, $str, $matches );

				//if match found
				if ( $matches && is_array( $matches ) && count( $matches ) > 0 ) {

					//iterate over the found matches
					foreach ( $matches[0] as $exact_match ) {

						//preserve old match
						$old_match        = $exact_match;
						$single           = str_replace( '{', '', $old_match );
						$single           = str_replace( '}', '', $single );
						$get_parsed_value = call_user_func( array( __CLASS__, $single ) );
						$content          = str_replace( $old_match, $get_parsed_value, $content );
					}
				}
			}
		}
		return $content;
	}
	
	/*
	 * Mergetag callback for showing sitename.
	 */

	public  function get_all_tags() {
		$tags = array(
			array(
				'name' => __( 'Email Verification Link', 'customer-email-verification' ),
				'tag'  => 'cev_user_verification_link',
			),			
			array(
				'name' => __( 'Verification Pin', 'customer-email-verification' ),
				'tag'  => 'cev_user_verification_pin',
			),
			array(
				'name' => __( 'Site Title', 'customer-email-verification' ),
				'tag'  => 'site_title',
			),
		);
		return $tags;
	}
	
	/*
	* Return Email Verification link from this variable {cev_user_verification_link}
	*/
	public function cev_user_verification_link() {
		$cev_user_verification_data_raw = WC()->session->get( 'cev_user_verification_data' );
		$cev_user_verification_data = ! is_null( $cev_user_verification_data_raw ) ? json_decode( $cev_user_verification_data_raw ) : null;
		$cev_verification_selection = get_option('cev_verification_selection');	
		$secret = $cev_user_verification_data->secret_code;
		$email = $cev_user_verification_data->email;		
		$create_link = $secret . '@' . $email;
		$hyperlink   = add_query_arg( array(
			'cusomer_email_verify' => base64_encode( $create_link ),
		), get_the_permalink( get_option( 'woocommerce_checkout_page_id' ) ) );	
			
		$style = 'text-decoration:  none ';	
		$style = cev_pro()->WC_customer_email_verification_email_Common->cev_user_verification_link_style( $style );
		if ( 'button' == $cev_verification_selection ) {
			$link = '<p><a style="' . $style . '" href="' . $hyperlink . '">' . get_option( 'cev_new_acoount_button_text', __( 'Verify Your Email', 'customer-email-verification' )) . '</a></p>';
		} else {
			$link = '<p><a style="' . $style . '" href="' . $hyperlink . '">' . get_option( 'cev_new_acoount_link_text', __( 'Verify Your Email', 'customer-email-verification' )) . '</a></p>';
		}
		
		return $link;
	}
	
	
	/*
	* Return Email Verification pin from this variable {cev_user_verification_pin}
	*/
	public function cev_user_verification_pin() {	
		$cev_user_verification_data_raw = WC()->session->get( 'cev_user_verification_data' );
		$cev_user_verification_data = ! is_null( $cev_user_verification_data_raw ) ? json_decode( $cev_user_verification_data_raw ) : null;
		$pin = base64_decode($cev_user_verification_data->pin);		
				
		return '<span>' . $pin . '</span>';
	}
	
	/*
	* Return Site Title from this variable {site_title}
	*/
	public static function site_title() {
		return get_bloginfo( 'name' );
	}

	/*
	* Check if user change email so send verification email on new email and add a validation error
	*/
	public function validate_email_on_change_from_edit_acccount( $errors, $user ) {
		$current_user = wp_get_current_user();
		$old_mail = $current_user->user_email;
		$new_mail = $user->user_email;				
		
		if ( $old_mail !=  $new_mail ) {
			
			update_user_meta( get_current_user_id(), 'cev_temp_email', $new_mail);
			cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id  = get_current_user_id();
			$this->code_mail_sender_edit_account( $new_mail );
			$errors->add( 'validation', 'We have sent a verification email, please verify your email address.' );
		}
				
	}
	
	/*
	* Add Verify email address field in Edit Account page
	*/
	public function add_email_verification_field_edit_account() {
		$cev_temp_email = get_user_meta( get_current_user_id(), 'cev_temp_email', true);
		
		if ( null != $cev_temp_email ) {
			
			woocommerce_form_field(
				'my_account_email_verification',
				array(
					'type'        => 'text',
					'placeholder' =>  __( '4-digits code', 'customer-email-verification' ),
					'required'    => true,
					'label'       => __('Verify Your Email Address', 'customer-email-verification')					
				)	
			);
			
			$resend_limit_reached = cev_pro()->WC_customer_email_verification_email_Common->cev_resend_email_limit( false, get_current_user_id() );
			$cev_user_resend_times = get_user_meta( get_current_user_id(), 'cev_user_resend_times', true);
			$cev_redirect_limit_resend = get_option('cev_redirect_limit_resend', 1);
			?>
			<span class="cev-pin-verification__failure_code " style="display:none;"><?php esc_html_e( 'Invalid PIN Code', 'customer-email-verification' ); ?></span>
			<p>
				<button type="button" wp_nonce="<?php esc_html_e( wp_create_nonce( 'wc_cev_email_edit_user_verify' ) ); ?>" class="woocommerce-Button button verify_account_email_my_account" name="verify_account_email" value="<?php esc_attr_e( 'Verify', 'customer-email-verification' ); ?>"><?php esc_html_e( 'Verify', 'customer-email-verification' ); ?>
				</button>
				<a href="javaScript:void(0);" class="a_cev_resend resend_verification_code_my_account limit_reched <?php echo $resend_limit_reached ? 'cev_disibale_values' : '' ; ?>" name="resend_verification_code" value="<?php esc_attr_e( 'Resend verification code', 'customer-email-verification' ); ?>"><?php esc_html_e( 'Resend verification code', 'customer-email-verification' ); ?> <span class="dashicons dashicons-yes show_cev_resend_yes" style="display:none;"></span></a>		
			</p>
		<?php
		}
	}
	
	/*
	* Set temp email in my account edit profile
	*/
	public function set_cev_temp_email_in_my_account() {
		global $wp;
		$request = explode( '/', $wp->request );
		if ( ( 'edit-account' == end($request) ) && is_account_page() ) { 
			$cev_temp_email = get_user_meta( get_current_user_id(), 'cev_temp_email', true);
			if ( null != $cev_temp_email ) {
				?>
				<style>
				a.a_cev_resend {
					vertical-align: super;
				}
				span.dashicons.dashicons-yes.show_cev_resend_yes {
					vertical-align: middle;
				}
				.cev_disibale_values {
					pointer-events: none;
					cursor: not-allowed;
					opacity: 0.5;
				}
				</style>
				<script>
					jQuery( document ).ready(function() {
						jQuery('#account_email').val('<?php echo wp_kses_post( $cev_temp_email ); ?>');
					});
				</script>
				<?php
			}	
		}		
	}
	
	/**
	 * Verify email verification on edit account
	 */	 
	public function verify_email_on_edit_account() {
		
		check_ajax_referer( 'wc_cev_email_edit_user_verify', 'wp_nonce' );
		
		$cev_email_verification_pin = get_user_meta( get_current_user_id(), 'cev_email_verification_pin', true );			
		
		$current_time = time();
		$expire_time = $cev_email_verification_pin['enddate'];
		
		$cev_verification_code_expiration = get_option('cev_verification_code_expiration', 'never');
		
		if ( 'never' != $cev_verification_code_expiration ) {
			if ( $current_time > $expire_time ) {											
				echo json_encode( array('success' => 'false') );
				die();		
			}
		}
		
		$cev_pin = isset( $_POST['pin'] ) ? wc_clean( $_POST['pin'] ) : '';	
		
		if ( $cev_email_verification_pin['pin'] == $cev_pin ) {			
			
			$cev_temp_email = get_user_meta( get_current_user_id(), 'cev_temp_email', true);
			$args = array(
				'ID'         => get_current_user_id(),
				'user_email' => $cev_temp_email
				
			);
			wp_update_user( $args );
			update_user_meta( get_current_user_id(), 'cev_user_resend_times', 0 );
			delete_user_meta( get_current_user_id(), 'cev_temp_email');			
			
			$verification_success_message = get_option('cev_verification_success_message', __('Your email is verified!', 'customer-email-verification'));		
			wc_add_notice( $verification_success_message, 'notice' );						
				
			echo json_encode( array('success' => 'true') );
			die();			
		} else {			
			echo json_encode( array('success' => 'false') );
			die();			
		}			
	}
	
	/**
	 * Resend email verification email 
	 */	
	public function resend_verification_code_on_edit_account() {
		
		$cev_temp_email = get_user_meta( get_current_user_id(), 'cev_temp_email', true);
		$cev_user_resend_times = get_user_meta( get_current_user_id(), 'cev_user_resend_times', true);				 
		$cev_redirect_limit_resend = get_option('cev_redirect_limit_resend', 1);
		
		if ( null == $cev_user_resend_times ) {
			$cev_user_resend_times=0;				
		}
		if ( $cev_user_resend_times >= $cev_redirect_limit_resend ) {
			$resend_limit_reached = true;
			echo json_encode( array( 'success' => 'false', 'cev_resend_limit_reached' => $resend_limit_reached ));
			die();	
		} else {
			cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id  = get_current_user_id();
			$this->code_mail_sender_edit_account( $cev_temp_email );
			update_user_meta( get_current_user_id(), 'cev_user_resend_times', ( int ) $cev_user_resend_times+1 );		
			echo json_encode( array( 'success' => 'true' ));
			die();		
		}				
	}
	
	/**
	 *  Code email sender edit account  
	 */
	public function code_mail_sender_edit_account( $recipient ) {
		
		$CEV_Customizer_Options = new CEV_Customizer_Options();
		
		$cev_pin = cev_pro()->WC_customer_email_verification_email_Common->generate_verification_pin();		
		
		$expire_time =  get_option('cev_verification_code_expiration', 'never');
		
		if ( empty($expire_time) ) {
			$expire_time = 'never';
		}
		
		$verification_data = array(
			'pin' => $cev_pin, 
			'startdate' => time(),
			'enddate' => time() + ( int ) $expire_time,
		);		

		update_user_meta( get_current_user_id(), 'cev_email_verification_pin', $verification_data );
		
		$secret_code = md5( get_current_user_id() . time() );
		update_user_meta( get_current_user_id(), 'customer_email_verification_code', $secret_code );
		
		$email_link_edit_account = $this->cev_user_verification_link_on_email_change( $secret_code );
		
		$result        = false;
		
		$email_subject = get_option('cev_verification_email_subject', $CEV_Customizer_Options->defaults['cev_verification_email_subject']);
		$email_subject = apply_filters( 'cev_edit_email_subject_content', $email_subject);
		$email_subject = $this->maybe_parse_merge_tags( $email_subject );
		
		$email_heading = __( 'You recently changed the email address {site_title}', 'customer-email-verification' );			
		$email_heading = apply_filters( 'cev_edit_email_heading_content', $email_heading);
		$email_heading = $this->maybe_parse_merge_tags( $email_heading );	
		// echo $email_subject.'------'.$email_heading;
		/* translators: %1$s: Replace with Recipent, %2$s: Replace with verification pin,  %3$s: Replace with verification link  */	
		$content = sprintf( __( '<p> Please confirm <strong>%1$s</strong> as your new email address. The change will not take effect until you verify this email address.</p><p> Your verification code: <strong>%2$s</strong> </p> <p> Or, verify your account by clicking on the verification link: <strong>%3$s</strong> </p>' , 'customer-email-verification' ), $recipient, $cev_pin, $email_link_edit_account );
		
		$content = apply_filters( 'cev_edit_email_content', $content, $recipient, $cev_pin, $email_link_edit_account );
		
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
		
		$mailer = WC()->mailer();
		// create a new email
		$email = new WC_Email();
		
		
		$email->id = 'CEV_Registration_Verification';
		$email_body = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $email_content ) ) );
		$email_body = apply_filters( 'wc_cev_decode_html_content', $email_body );
		
		add_filter( 'wp_mail_from', array( cev_pro()->WC_customer_email_verification_email_Common, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( cev_pro()->WC_customer_email_verification_email_Common, 'get_from_name' ) );
				
		wp_mail( $recipient, $email_subject, $email_body, $email->get_headers() );		
	}
	
	public function cev_user_verification_link_on_email_change( $secret_code ) {
		
		$CEV_Customizer_Options = new CEV_Customizer_Options();
		$cev_new_email_link_color = get_option('cev_new_email_link_color', $CEV_Customizer_Options->defaults['cev_new_email_link_color']) ;
		$cev_header_button_size_pro = get_option('cev_button_text_font_size', $CEV_Customizer_Options->defaults['cev_button_text_font_size']) ;
		$cev_verification_selection = get_option('cev_verification_selection');
		
		//$secret      = get_user_meta( get_current_user_id(), 'customer_email_verification_code', true );
		$create_link = $secret_code . '@' . get_current_user_id();
		
		$hyperlink   = add_query_arg( array(
			'cusomer_email_change' => base64_encode( $create_link ),
		), wc_customer_edit_account_url() );				
		
		$style = 'text-decoration:  none;  color: ' . $cev_new_email_link_color . '; font-size: ' . $cev_header_button_size_pro . 'px';
		$style = cev_pro()->WC_customer_email_verification_email_Common->cev_user_verification_link_style( $style );
		
		if ( 'button' == $cev_verification_selection ) {
			$link = '<p><a style="' . $style . '" href="' . $hyperlink . '">' . get_option( 'cev_new_acoount_button_text', __( 'Verify your Email', 'customer-email-verification' )) . '</a></p>';
		} else {
			$link = '<p><a style="' . $style . '" href="' . $hyperlink . '">' . get_option( 'cev_new_acoount_button_text', __( 'Verify your Email', 'customer-email-verification' )) . '</a></p>';
		}
		
		return $link;
	}
	
	public function verify_change_user_by_email_by_email() {
		if ( isset( $_GET['cusomer_email_change'] ) && '' !== $_GET['cusomer_email_change'] ) { // WPCS: input var ok, CSRF ok.
			
			$cusomer_email_change = wc_clean( $_GET['cusomer_email_change'] );
			$user_meta = explode( '@', base64_decode( $cusomer_email_change ) ); // WPCS: input var ok, CSRF ok.			

			$verified_code = get_user_meta( (int) $user_meta[1], 'customer_email_verification_code', true );
			
			if ( ! empty( $verified_code ) && $verified_code === $user_meta[0] ) {
				
				$cev_email_link_expired = cev_pro()->email->cev_email_link_expired( false, (int) $user_meta[1] );
				
				if ( $cev_email_link_expired ) {
					$verification_failed_message = get_option('cev_verification_success_message', 'Your email verification link is expired.');
					wc_add_notice( $verification_failed_message, 'notice' );
				} else {
					$cev_temp_email = get_user_meta( (int) $user_meta[1], 'cev_temp_email', true);
					if ( '' != $cev_temp_email ) {
						$args = array(
							'ID'         => (int) $user_meta[1],
							'user_email' => $cev_temp_email					
						);
						wp_update_user( $args );
						update_user_meta( (int) $user_meta[1] , 'cev_user_resend_times', 0 );
						delete_user_meta( (int) $user_meta[1] , 'cev_temp_email');							

						$verification_success_message = get_option('cev_verification_success_message', 'Your email is verified!');		
						wc_add_notice( $verification_success_message, 'notice' );
					}					
				}
			}
		}
	}
	
	
	/*
	* checkout page email send 
	*/
	public function send_email_on_chekout_page() {
		
		check_ajax_referer( 'checkout-send-verification-email', 'security' );
		
		$email = isset( $_POST['email'] ) ? wc_clean( $_POST['email'] ) : '';
		if ( is_plugin_active( 'checkout-for-woocommerce/checkout-for-woocommerce.php' ) ) {
			$checkoutWC = true;
		} else {
			$checkoutWC = false;
		}
		$verified = $this->check_email_verify( $email );
		if ( true == $verified ) {
			$verification_data = array(
				'email' => $email,
				'verified' => true,
			);	
			//echo '<pre>';print_r( $verification_data );echo '</pre>';exit;
			WC()->session->set( 'cev_user_verified_data', json_encode($verification_data) );
			WC()->customer->set_billing_email( $email );
			echo json_encode( array( 'success' => 'alredy_verify', 'checkoutWC' => $checkoutWC  ));
			die();	
		} else {
			$cev_user_verification_data_raw = WC()->session->get( 'cev_user_verification_data' );
			$cev_user_verification_data = ! is_null( $cev_user_verification_data_raw ) ? json_decode( $cev_user_verification_data_raw ) : null;
			$cev_user_verified_data_raw =  WC()->session->get( 'cev_user_verified_data' );
			$cev_user_verified_data = ! is_null( $cev_user_verified_data_raw ) ? json_decode( $cev_user_verified_data_raw ) : null;

			if ( isset( $cev_user_verified_data->email ) ) {
				$verified_email = $cev_user_verified_data->email;
				if ( $verified_email == $email && true == $cev_user_verified_data->verified ) {
					echo json_encode( array( 'mail_sent' => true, 'verify' => true, 'checkoutWC' => $checkoutWC ) );
					die();
				}
			}
		
			if ( isset( $cev_user_verification_data->email ) ) {
			$session_email = $cev_user_verification_data->email;
				if ( $session_email == $email ) {
					echo json_encode( array( 'mail_sent' => true, 'verify' => false, 'checkoutWC' => $checkoutWC ) );
					die();
				}
			}
		
			WC()->customer->set_billing_email( $email );
			$this->send_verification_email_to_guest_user( $email );	

			echo json_encode( array( 'mail_sent' => false, 'verify' => false, 'checkoutWC' => $checkoutWC ));
			die();	
		}
				
	}

	public function cev_display_inline_verification_on_checkout() {
		
		$cev_enable_email_verification_checkout = get_option( 'cev_enable_email_verification_checkout', 1 );
		$cev_inline_email_verification_checkout = get_option( 'cev_verification_checkout_dropdown_option' );
		$cev_enable_email_verification_free_orders  = get_option('cev_enable_email_verification_free_orders');
		$order_subtotal = WC()->cart->subtotal;
		
		if ( is_user_logged_in() ) {
			return;
		}
		
		if ( !is_checkout() && !is_cart() ) {
			return;
		}
		
		if ( 1 != $cev_enable_email_verification_checkout ) {
			return;
		}
		
		if ( 1 == $cev_inline_email_verification_checkout  ) {
			return;
		}
		
		if ( $order_subtotal > 0 && 1 == $cev_enable_email_verification_free_orders ) {
			return;
		}
		
		$billing_email = WC()->customer->get_billing_email();
		
		$cev_user_verification_data_raw = WC()->session->get( 'cev_user_verification_data' );
		$cev_user_verification_data = ! is_null( $cev_user_verification_data_raw ) ? json_decode( $cev_user_verification_data_raw ) : null;
		$cev_user_verified_data_raw =  WC()->session->get( 'cev_user_verified_data' );
		$cev_user_verified_data = ! is_null( $cev_user_verified_data_raw ) ? json_decode( $cev_user_verified_data_raw ) : null;
		
		if ( isset( $cev_user_verified_data->email ) ) {
			$verified_email = $cev_user_verified_data->email;
			if ( $verified_email == $billing_email && true == $cev_user_verified_data->verified ) {
				return;
			}
		}
		
		$cev_redirect_limit_resend = get_option('cev_redirect_limit_resend', 1);						
		
		if ( isset($cev_user_verification_data->cev_guest_user_resend_times) ) {
			$cev_guest_user_resend_times = $cev_user_verification_data->cev_guest_user_resend_times;
		} else {
			$cev_guest_user_resend_times = 0;
		}
		
		if ( $cev_guest_user_resend_times >= $cev_redirect_limit_resend+1 ) {
			$cev_resend_limit_reached = 'true';
		} else {
			$cev_resend_limit_reached = 'false';
		}
			
		if ( isset( $cev_user_verification_data->email ) ) {
			$session_email = $cev_user_verification_data->email;
			if ( $session_email == $billing_email ) {
				$resend_class = ( 'true' == $cev_resend_limit_reached ) ? 'a_cev_resend_checkout' : 'resend_verification_code_inline_chekout_user';
				$wp_nonce = wp_create_nonce( 'wc_cev_email_guest_user' );
				if ( !is_plugin_active( 'checkout-for-woocommerce/checkout-for-woocommerce.php' ) ) {
					?>
					<script>
					jQuery(document).ready(function() {
						setTimeout(function() {
							var resend_class = '<?php esc_html_e( $resend_class ); ?>';
							var wp_nonce = '<?php esc_html_e( $wp_nonce ); ?>';
							var email_verification_code_label = "<?php esc_html_e( 'Email verification code', 'customer-email-verification' ); ?>";
							var verification_code_error_msg = "<?php esc_html_e( 'Verification code does not match', 'customer-email-verification' ); ?>";
							var verify_button_text = "<?php esc_html_e( 'Verify', 'customer-email-verification' ); ?>";
							var resend_verification_label = "<?php esc_html_e( 'Resend verification code', 'customer-email-verification' ); ?>";
							jQuery( "#billing_email_field" ).after( "<div class='cev_pro_append'><label class='jquery_color_css'> "+email_verification_code_label+"</label> <input type='text' class='input-text cev_pro_chekout_input' name='cev_billing_email' style= 'margin-bottom: 0;'></input><span class='cev_verification__failure_code_checkout' style='display:none; color:red'>"+verification_code_error_msg+"</span> <div class='cev_pro_chekout_button'> <button type='button' class='woocommerce-Button button verify_account_email_chekout_page' name='verify_account_email' value='verify'>"+verify_button_text+"</button> <a href='javaScript:void(0);' class='"+resend_class+"' name='resend_verification_code' wp_nonce='"+wp_nonce+"'>"+resend_verification_label+"</a></div></div>" );
						}, 500);
						jQuery(".cev_inline_code_sent").remove();
					});
					</script>
					<?php 
				} else {
					?>
					<script>
					jQuery(document).ready(function() {
						setTimeout(function() {
							var resend_class = '<?php esc_html_e( $resend_class ); ?>';
							var wp_nonce = '<?php esc_html_e( $wp_nonce ); ?>';
							var email_verification_code_label = "<?php esc_html_e( 'Email verification code', 'customer-email-verification' ); ?>";
							var verification_code_error_msg = "<?php esc_html_e( 'Verification code does not match', 'customer-email-verification' ); ?>";
							var verify_button_text = "<?php esc_html_e( 'Verify', 'customer-email-verification' ); ?>";
							var resend_verification_label = "<?php esc_html_e( 'Resend verification code', 'customer-email-verification' ); ?>";
							jQuery( "#billing_email_field" ).parent('.cfw-input-wrap-row').after( "<div class='row cfw-input-wrap-row'><div class='col-lg-12'><div class='cfw-input-wrap cfw-text-input'><label class=''>"+email_verification_code_label+"</label> <input type='text' class='input-text garlic-auto-save cev_pro_chekout_input' name='cev_billing_email' placeholder='"+email_verification_code_label+"' aria-describedby='parsley-id-5' data-parsley-trigger='keyup change focusout'></input><span class='cev_verification__failure_code_checkout' style='display:none; color:red'>"+verification_code_error_msg+"</span> <div class='cev_pro_chekout_button'> <button type='button' class='woocommerce-Button button verify_account_email_chekout_page' name='verify_account_email' value='verify'>"+verify_button_text+"</button> <a href='javaScript:void(0);' class='"+resend_class+"' name='resend_verification_code' wp_nonce='"+wp_nonce+"'>"+resend_verification_label+"</a></div></div></div></div>" );
						}, 500);
						jQuery(".cev_inline_code_sent").remove();
					});
					</script>				
					<?php			
				}				
			}
		}
		?>
		<script>
		if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_BACK_FORWARD) {
			location.reload();
		}
		</script>
		<?php	
	}

	public function woocommerce_review_order_before_submit() {
		
		$cev_enable_email_verification_checkout = get_option( 'cev_enable_email_verification_checkout', 1 );
		$cev_inline_email_verification_checkout = get_option( 'cev_verification_checkout_dropdown_option' );
		$cev_enable_email_verification_free_orders  = get_option('cev_enable_email_verification_free_orders');
		$order_subtotal = WC()->cart->subtotal;
		
		if ( is_user_logged_in() ) {
			return;
		}
		
		if ( !is_checkout() && !is_cart() ) {
			return;
		}
		
		if ( 1 != $cev_enable_email_verification_checkout ) {
			return;
		}
		
		if ( 1 == $cev_inline_email_verification_checkout  ) {
			return;
		}
		
		if ( $order_subtotal > 0 && 1 == $cev_enable_email_verification_free_orders ) {
			return;
		}
		
		if ( isset( $_GET['cusomer_email_verify'] ) && '' !== $_GET['cusomer_email_verify'] ) {
			$cusomer_email_verify = wc_clean( $_GET['cusomer_email_verify'] );
			$user_meta = explode( '@', base64_decode( $cusomer_email_verify ) ); 
			$email_secret_code = $user_meta[0];
			$cev_user_verified_data_raw =  WC()->session->get( 'cev_user_verified_data' );
			$cev_user_verified_data = ! is_null( $cev_user_verified_data_raw ) ? json_decode( $cev_user_verified_data_raw ) : null;		
			
			if ( isset( $cev_user_verified_data->email ) ) {
				if ( isset( $cev_user_verified_data->verified ) && true == $cev_user_verified_data->verified ) {
					?>
					<script>
						var email_address_verified = "<?php esc_html_e( 'Email address verified', 'customer-email-verification' ); ?>";
						jQuery("<small class= 'cev-hide-desc' style='color: green;'>"+email_address_verified+"</small>").insertAfter('#billing_email');
						jQuery("#billing_email").css("margin","0");					
					</script>
					<?php		
				}
			}						
		}	
	}
	/*
	 * Checck email Verify or nor
	*/
	/*
	 * Checck email Verify or nor
	*/
	public function check_email_verify( $email ) {		
		$verification = false;
		$user = get_user_by( 'email', $email );	
		$orders = wc_get_orders( [
			'billing_email' => $email,
			'limit'         => 1, // We only need to check if at least one order exists
		] );	
		if ( $user ) {
			$user_id = $user->ID;
			
			$verified = get_user_meta( $user_id, 'customer_email_verified', true );
			
			
			if ( 'true' == $verified ) {
				$verification = true;
			} else {
				$verification = false;
			}
		
		} elseif (! empty( $orders ) ) {
			$verification = true;
		} else {
			$verification = false;
		}	
		return $verification;
	}
}
