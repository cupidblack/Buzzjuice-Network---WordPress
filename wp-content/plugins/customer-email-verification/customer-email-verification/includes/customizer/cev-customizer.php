<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEV_Customizer {
	
	private static $screen_id = 'cev_customizer';
	private static $screen_title = 'Email Verification';

	/**
	 * Get the class instance
	 *
	 * @since  1.0
	 * @return CEV_Customizer
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
	 * @since  1.0
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

		//adding hooks
		add_action( 'admin_menu', array( $this, 'register_woocommerce_menu' ), 99 );

		add_action('rest_api_init', array( $this, 'route_api_functions' ) );
						
		add_action('admin_enqueue_scripts', array( $this, 'customizer_enqueue_scripts' ) );

		add_action('admin_footer', array( $this, 'admin_footer_enqueue_scripts' ) );

		add_action( 'wp_ajax_' . self::$screen_id . '_email_preview', array( $this, 'get_preview_func' ) );
		//add_action( 'wp_ajax_send_' . self::$screen_id . '_test_email', array( $this, 'send_test_email_func' ) );

		// Custom Hooks for everyone		
		add_filter( self::$screen_id . '_preview_content', array( $this, 'cev_customizer_preview_content' ), 10, 1);
		
	}

	/*
	 * Admin Menu add function
	 *
	 * @since  2.4
	 * WC sub menu 
	*/
	public function register_woocommerce_menu() {
		add_menu_page( __( self::$screen_title, 'customer-email-verification' ), __( self::$screen_title, 'customer-email-verification' ), 'manage_options', self::$screen_id, array( $this, 'cev_settingsPage' ) );
	}
	
	/*
	 * Call Admin Menu data function
	 *
	 * @since  2.4
	 * WC sub menu 
	*/
	public function cev_settingsPage() {
		echo '<div id="root"></div>';
	}
	
	/*
	 * Add admin javascript
	 *
	 * @since  2.4
	 * WC sub menu 
	*/
	public function admin_footer_enqueue_scripts() {
		echo '<style type="text/css">#toplevel_page_' . esc_html( self::$screen_id ) . ' { display: none !important; }</style>';
	}

	/*
	* Add admin javascript
	*
	* @since 1.0
	*/	
	public function customizer_enqueue_scripts() {
		
		
		$page = isset( $_GET['page'] ) ? sanitize_text_field($_GET['page']) : '' ;
		
		// Add condition for css & js include for admin page  
		if ( self::$screen_id == $page ) {
			// Add the WP Media 
			wp_enqueue_media();
			
			wp_enqueue_script( self::$screen_id, plugin_dir_url(__FILE__) . 'dist/main.js', ['jquery', 'wp-util', 'wp-color-picker'], time(), true);
			wp_localize_script( self::$screen_id, self::$screen_id, array(
				'admin_email' => get_option('admin_email'),
				'translations' => array(
					esc_html__( 'Save', 'react-customizer-framework' ),
					esc_html__( 'You are customizing', 'react-customizer-framework' ),
					esc_html__( 'Customizing', 'react-customizer-framework' ),
					esc_html__( 'Send Test Email', 'react-customizer-framework' ),
					esc_html__( 'Send a test email', 'react-customizer-framework' ),
					esc_html__( 'Enter Email addresses (comma separated)', 'react-customizer-framework' ),
					esc_html__( 'Send', 'react-customizer-framework' ),
					esc_html__( 'Settings Successfully Saved.', 'react-customizer-framework' ),
					esc_html__( 'Please save the changes to send test email.', 'react-customizer-framework' )
				),
				'iframeUrl' => array(
					'email_registration'      => admin_url('admin-ajax.php?action=' . self::$screen_id . '_email_preview&preview=email_registration'),
					'email_checkout'      => admin_url('admin-ajax.php?action=' . self::$screen_id . '_email_preview&preview=email_checkout'),
					'email_login_otp'  => admin_url('admin-ajax.php?action=' . self::$screen_id . '_email_preview&preview=email_login_otp'),
					'email_login_auth'  => admin_url('admin-ajax.php?action=' . self::$screen_id . '_email_preview&preview=email_login_auth'),
					'popup_registration'  => admin_url('admin-ajax.php?action=' . self::$screen_id . '_email_preview&preview=popup_registration'),
					'popup_login_auth'  => admin_url('admin-ajax.php?action=' . self::$screen_id . '_email_preview&preview=popup_login_auth'),
					'popup_checkout'  => admin_url('admin-ajax.php?action=' . self::$screen_id . '_email_preview&preview=popup_checkout'),
				),
				'back_to_wordpress_link' => admin_url( 'admin.php?page=customer-email-verification-for-woocommerce' ),
				'nonce' => wp_create_nonce('ajax-nonce'),
				'main_title' => self::$screen_title,
				'send_test_email_btn' => false,
				'rest_nonce'    => wp_create_nonce('wp_rest'),
				'rest_base' => esc_url_raw( rest_url() ),
			));
		}
		
	}

	/*
	 * Customizer Routes API 
	*/
	public function route_api_functions() {

		register_rest_route( self::$screen_id, 'settings', array(
			'methods'  => 'GET',
			'callback' => [$this, 'return_json_sucess_settings_route_api'],
			'permission_callback' => '__return_true',
		));

		/*register_rest_route( self::$screen_id, 'preview', array(
			'methods'  => 'GET',
			'callback' => [$this, 'return_json_sucess_preview_route_api'],
			'permission_callback' => '__return_true',
		));*/

		register_rest_route( self::$screen_id, 'store/update', array(
			'methods'				=> 'POST',
			'callback'				=> [$this, 'update_store_settings'],
			'permission_callback'	=> '__return_true',
		));
	}

	/*
	 * Settings API 
	*/
	public function return_json_sucess_settings_route_api( $request ) {
		$preview = !empty($request->get_param('preview')) ? $request->get_param('preview') : '';
		return wp_send_json_success($this->customize_setting_options_func( $preview ));
	}

	public function customize_setting_options_func( $preview) {
		$settings = array();
		$settings = apply_filters(  self::$screen_id . '_email_options', $settings, $preview );		
		return $settings; 
	}
	
	public function get_preview_func() {
		$preview = isset($_GET['preview']) ? wc_clean( $_GET['preview'] ) : '';
		echo wp_kses_post( $this->get_preview_email( $preview ) );
		die();
	}

	/*
	* update a customizer settings
	*/
	public function update_store_settings( $request ) {

		$preview = !empty($request->get_param('preview')) ? $request->get_param('preview') : '';

		$data = $request->get_params() ? $request->get_params() : array();
				
		if ( ! empty( $data ) ) {

			//data to be saved
			
			$settings = $this->customize_setting_options_func( $preview );
			
			foreach ( $settings as $key => $val ) {
				
				if ( !isset($data[$key] ) || ( isset($val['show']) && true != $val['show'] ) ) {
					continue;
				}

				//check column exist
				if ( isset( $val['type'] ) && 'textarea' == $val['type'] && !isset( $val['option_key'] ) && isset($val['option_name']) ) {
					$option_data = isset($data[$key]) ? wp_kses_post( wp_unslash( $data[$key] ) ) : '';					
					update_option( $val['option_name'], $option_data );
				} elseif ( isset( $val['option_type'] ) && 'key' == $val['option_type'] ) {
					$data[$key] = isset($data[$key]) ? wc_clean( wp_unslash( $data[$key] ) ) : '';
					update_option( $key, $data[$key] );
				} elseif ( isset( $val['option_type'] ) && 'array' == $val['option_type'] ) {
					if ( isset( $val['option_key'] ) && isset( $val['option_name'] ) ) {
						$option_data = get_option( $val['option_name'], array() );
						if ( 'enabled' == $val['option_key'] ) {
							$option_data[$val['option_key']] = isset($data[$key]) && 1 == $data[$key] ? wc_clean( wp_unslash( 'yes' ) ) : wc_clean( wp_unslash( 'no' ) );
						} else {
							$option_data[$val['option_key']] = isset($data[$key]) ? wc_clean( wp_unslash( $data[$key] ) ) : '';
						}
						update_option( $val['option_name'], $option_data );
					} else if ( isset($val['option_name']) ) {
						$option_data = get_option( $val['option_name'], array() );
						$option_data[$key] = isset($data[$key]) ? wc_clean( wp_unslash( $data[$key] ) ) : '';
						update_option( $val['option_name'], $option_data );
					}
				}
			}
			
			echo json_encode( array('success' => true, 'preview' => $preview) );
			die();
	
		}

		echo json_encode( array('success' => false) );
		die();
	}

	/**
	 * Get the email content
	 *
	 */
	public function get_preview_email( $preview ) { 

		$content = apply_filters( self::$screen_id . '_preview_content' , $preview );

		$content .= '<style type="text/css">body{margin: 0;}</style>';

		add_filter( 'wp_kses_allowed_html', array( $this, 'allowed_css_tags' ) );
		add_filter( 'safe_style_css', array( $this, 'safe_style_css' ), 10, 1 );

		return wp_kses_post($content);
	}

	public function cev_customizer_preview_content( $preview ) {
		if ( 'email_checkout' == $preview ) {
			return $this->preview_checkout_email();
		} else if ( 'email_registration' == $preview ) {
			return $this->preview_account_email();
		} else if ( 'email_login_auth' == $preview ) {
			return $this->preview_login_auth_email();
		} else if ( 'email_login_otp' == $preview ) {
			return $this->preview_login_otp_email();
		} else if ( 'popup_registration' == $preview ) {
			return $this->preview_popup_registration();
		} else if ( 'popup_checkout' == $preview ) {
			return $this->preview_popup_checkout();
		} else if ( 'popup_login_auth' == $preview ) {
			return $this->preview_popup_login_auth();
		} else {
			return $this->preview_account_email();
		}
	}

	/**
	 * Code for preview of tracking info in email
	*/	
	public function preview_account_email() {				
		
		// Load WooCommerce emails.
		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();				
		cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id  = 1;				
		
		$email_heading     = get_option('cev_verification_email_heading', cev_pro()->customizer_options->defaults['cev_verification_email_heading']);
		$email_heading 	   = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_heading );		
		
		$email_content = get_option('cev_verification_email_body', cev_pro()->customizer_options->defaults['cev_verification_email_body']);
					
		$email = '';
				
		$mailer = WC()->mailer();			

		// create a new email
		$email = new WC_Email();
		$email->id = 'CEV_Registration_Verification';
			
		$email_content = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_content );
		$footer_content = get_option('cev_new_verification_Footer_content');
		
		$content = ob_start();					
		$local_template	= get_stylesheet_directory() . '/woocommerce/emails/cev-email-verification.php';				
		if ( file_exists( $local_template ) && is_writable( $local_template )) {
			wc_get_template( 'emails/cev-email-verification.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,				
				'footer_content' => $footer_content,
			), 'customer-email-verification/', get_stylesheet_directory() . '/woocommerce/' );
		} else {
			wc_get_template( 'emails/cev-email-verification.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,	
				'footer_content' => $footer_content,	
			), 'customer-email-verification/', cev_pro()->get_plugin_path() . '/templates/' );
		}
		$content = ob_get_clean();				
		
		//add_filter( 'wp_kses_allowed_html', array( cev_pro()->WC_customer_email_verification_email_Common, 'my_allowed_tags' ) );
		//add_filter( 'safe_style_css', array( cev_pro()->WC_customer_email_verification_email_Common, 'safe_style_css_callback' ), 10, 1 );
		
		// wrap the content with the email template and then add styles
		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $content ) ) );
		$message = apply_filters( 'wc_cev_decode_html_content', $message );		
		return wp_kses_post( $message );
	}

	/**
	 * Code for preview of tracking info in email
	*/	
	public function preview_login_auth_email() {				
		
		// Load WooCommerce emails.
		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();		
		
		$email_heading     = get_option('cev_verification_login_auth_email_heading', cev_pro()->customizer_options->defaults['cev_verification_login_auth_email_heading']);
		$email_heading 	   = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_heading );		
		
		$email_content = get_option('cev_verification_login_auth_email_content', cev_pro()->customizer_options->defaults['cev_verification_login_auth_email_content']);
		
		$user_id = 1;
		cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id  = $user_id;	
		$user_info = get_userdata( $user_id );
		$user_last_login_details = get_user_meta( $user_id, 'cev_last_login_detail' );
		$user_last_login_time = get_user_meta( $user_id, 'cev_last_login_time', true );
					
		$email = '';
				
		$mailer = WC()->mailer();			

		// create a new email
		$email = new WC_Email();
		$email->id = 'CEV_Login_Auth';
			
		$email_content = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_content );
		$footer_content = get_option('cev_login_auth_footer_content', cev_pro()->customizer_options->defaults['cev_login_auth_footer_content']);
		
		$content = ob_start();					
		$local_template	= get_stylesheet_directory() . '/woocommerce/emails/cev-login-authentication.php';				
		if ( file_exists( $local_template ) && is_writable( $local_template )) {
			wc_get_template( 'emails/cev-login-authentication.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,				
				'footer_content' => $footer_content,
				'user_info' => $user_info,
				'user_last_login_details' => $user_last_login_details,
				'user_last_login_time' => $user_last_login_time,
				'login_otp' => '',
			), 'customer-email-verification/', get_stylesheet_directory() . '/woocommerce/' );
		} else {
			wc_get_template( 'emails/cev-login-authentication.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,	
				'footer_content' => $footer_content,
				'user_info' => $user_info,
				'user_last_login_details' => $user_last_login_details,
				'user_last_login_time' => $user_last_login_time,	
				'login_otp' => '',
			), 'customer-email-verification/', cev_pro()->get_plugin_path() . '/templates/' );
		}
		$content = ob_get_clean();				
		
		//add_filter( 'wp_kses_allowed_html', array( cev_pro()->WC_customer_email_verification_email_Common, 'my_allowed_tags' ) );
		//add_filter( 'safe_style_css', array( cev_pro()->WC_customer_email_verification_email_Common, 'safe_style_css_callback' ), 10, 1 );
		
		// wrap the content with the email template and then add styles
		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $content ) ) );
		$message = apply_filters( 'wc_cev_decode_html_content', $message );		
		return wp_kses_post( $message );
	}

	public function preview_login_otp_email() {
		// Load WooCommerce emails.
		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();		
		
		$email_heading     = get_option('cev_verification_login_otp_email_heading', cev_pro()->customizer_options->defaults['cev_verification_login_otp_email_heading']);
		$email_heading 	   = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_heading );		
		
		$email_content = get_option('cev_verification_login_otp_email_content', cev_pro()->customizer_options->defaults['cev_verification_login_otp_email_content']);
		
		$user_id = 1;
		cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id  = $user_id;	
		$user_info = get_userdata( $user_id );
		$user_last_login_details = get_user_meta( $user_id, 'cev_last_login_detail' );
		$user_last_login_time = get_user_meta( $user_id, 'cev_last_login_time', true );
		$login_otp = cev_pro()->WC_customer_email_verification_email_Common->generate_verification_pin();

		$email = '';

		$mailer = WC()->mailer();			

		// create a new email
		$email = new WC_Email();
		$email->id = 'CEV_Login_Auth';

		$email_content = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_content );
		$footer_content = get_option('cev_login_otp_footer_content', cev_pro()->customizer_options->defaults['cev_login_otp_footer_content']);
		
		$content = ob_start();					
		$local_template	= get_stylesheet_directory() . '/woocommerce/emails/cev-login-authentication.php';				
		if ( file_exists( $local_template ) && is_writable( $local_template )) {
			wc_get_template( 'emails/cev-login-authentication.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,				
				'footer_content' => $footer_content,				
				'user_last_login_details' => $user_last_login_details,
				'user_last_login_time' => $user_last_login_time,
				'login_otp' => $login_otp,
			), 'customer-email-verification/', get_stylesheet_directory() . '/woocommerce/' );
		} else {
			wc_get_template( 'emails/cev-login-authentication.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,	
				'footer_content' => $footer_content,
				'user_info' => $user_info,
				'user_last_login_details' => $user_last_login_details,
				'user_last_login_time' => $user_last_login_time,	
				'login_otp' => $login_otp,
			), 'customer-email-verification/', cev_pro()->get_plugin_path() . '/templates/' );
		}
		$content = ob_get_clean();

		// wrap the content with the email template and then add styles
		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $content ) ) );
		$message = apply_filters( 'wc_cev_decode_html_content', $message );		
		return wp_kses_post( $message );
	}

	/**
	 * Code for preview of tracking info in email
	*/	
	public function preview_checkout_email() {				
		
		// Load WooCommerce emails.
		$wc_emails      = WC_Emails::instance();
		$emails         = $wc_emails->get_emails();					
		
		$email_heading = get_option('cev_verification_email_heading_che', cev_pro()->customizer_options->defaults['cev_verification_email_heading_che']);
		$email_heading = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_heading );		
		
		$email_content = get_option('cev_verification_email_body_che', cev_pro()->customizer_options->defaults['cev_verification_email_body_che']);
					
		$email = '';
				
		$mailer = WC()->mailer();			

		// create a new email
		$email = new WC_Email();
		$email->id = 'CEV_Guset_User_Verification';
			
		$email_content = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_content );
		$footer_content = get_option('cev_new_verification_Footer_content_che');
		
		$content = ob_start();					
		$local_template	= get_stylesheet_directory() . '/woocommerce/emails/cev-email-verification.php';				
		if ( file_exists( $local_template ) && is_writable( $local_template )) {
			wc_get_template( 'emails/cev-email-verification.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,
				'footer_content' => $footer_content,
			), 'customer-email-verification/', get_stylesheet_directory() . '/woocommerce/' );
		} else {
			wc_get_template( 'emails/cev-email-verification.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,
				'footer_content' => $footer_content,				
			), 'customer-email-verification/', cev_pro()->get_plugin_path() . '/templates/' );
		}
		$content = ob_get_clean();				
		
		//add_filter( 'wp_kses_allowed_html', array( cev_pro()->WC_customer_email_verification_email_Common, 'my_allowed_tags' ) );
		//add_filter( 'safe_style_css', array( cev_pro()->WC_customer_email_verification_email_Common, 'safe_style_css_callback' ), 10, 1 );
		
		// wrap the content with the email template and then add styles
		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $content ) ) );
		$message = apply_filters( 'wc_cev_decode_html_content', $message );		
		return wp_kses_post( $message );
	}

	public function preview_popup_registration() {		
		wp_head();
		wp_enqueue_style( 'cev_front_style' );		
		include 'preview/preview_cev_popup_page.php';
		get_footer();				
	}

	public function preview_popup_login_auth() {
		wp_head();
		wp_enqueue_style( 'cev_front_style' );		
		include 'preview/preview_cev_popup_login_auth.php';
		get_footer();				
	}

	public function preview_popup_checkout() {
		wp_head();
		wp_enqueue_style( 'cev_front_style' );
		wp_enqueue_script( 'cev-pro-front-js' );	
		include 'preview/verify_checkout_guest_user.php';
		get_footer();	
	}

	public function allowed_css_tags( $tags ) {
		$tags['style'] = array( 'type' => true, );
		return $tags;
	}
	
	public function safe_style_css( $styles ) {
		 $styles[] = 'display';
		return $styles;
	}
}
