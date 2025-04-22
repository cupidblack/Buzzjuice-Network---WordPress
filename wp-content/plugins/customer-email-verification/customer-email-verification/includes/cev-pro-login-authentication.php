<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Customer_Email_Verification_Login_Authentication {
	
	/**
	* Initialize the main plugin function
	*/
	public function __construct() {
		$this->init();
	}
	
	/**
	* Instance of this class.
	*
	* @var object Class Instance
	*/
	private static $instance;

	private $_basic_browser = array (
		'Trident\/7.0' => 'Internet Explorer 11',
		'Beamrise' => 'Beamrise',
		'Opera' => 'Opera',
		'OPR' => 'Opera',
		'Shiira' => 'Shiira',
		'Chimera' => 'Chimera',
		'Phoenix' => 'Phoenix',
		'Firebird' => 'Firebird',
		'Camino' => 'Camino',
		'Netscape' => 'Netscape',
		'OmniWeb' => 'OmniWeb',
		'Konqueror' => 'Konqueror',
		'icab' => 'iCab',
		'Lynx' => 'Lynx',
		'Links' => 'Links',
		'hotjava' => 'HotJava',
		'amaya' => 'Amaya',
		'IBrowse' => 'IBrowse',
		'iTunes' => 'iTunes',
		'Silk' => 'Silk',
		'Dillo' => 'Dillo', 
		'Maxthon' => 'Maxthon',
		'Arora' => 'Arora',
		'Galeon' => 'Galeon',
		'Iceape' => 'Iceape',
		'Iceweasel' => 'Iceweasel',
		'Midori' => 'Midori',
		'QupZilla' => 'QupZilla',
		'Namoroka' => 'Namoroka',
		'NetSurf' => 'NetSurf',
		'BOLT' => 'BOLT',
		'EudoraWeb' => 'EudoraWeb',
		'shadowfox' => 'ShadowFox',
		'Swiftfox' => 'Swiftfox',
		'Uzbl' => 'Uzbl',
		'UCBrowser' => 'UCBrowser',
		'Kindle' => 'Kindle',
		'wOSBrowser' => 'wOSBrowser',
		 'Epiphany' => 'Epiphany', 
		'SeaMonkey' => 'SeaMonkey',
		'Avant Browser' => 'Avant Browser',
		'Firefox' => 'Firefox',
		'Chrome' => 'Google Chrome',
		'MSIE' => 'Internet Explorer',
		'Internet Explorer' => 'Internet Explorer',
		'Safari' => 'Safari',
		'Mozilla' => 'Mozilla'
	);
 
	private $_basic_platform = array(
		'windows' => 'Windows', 
		'iPad' => 'iPad', 
		'iPod' => 'iPod', 
		'iPhone' => 'iPhone', 
		'mac' => 'Apple', 
		'android' => 'Android', 
		'linux' => 'Linux',
		'Nokia' => 'Nokia',
		'BlackBerry' => 'BlackBerry',
		'FreeBSD' => 'FreeBSD',
		'OpenBSD' => 'OpenBSD',
		'NetBSD' => 'NetBSD',
		'UNIX' => 'UNIX',
		'DragonFly' => 'DragonFlyBSD',
		'OpenSolaris' => 'OpenSolaris',
		'SunOS' => 'SunOS', 
		'OS\/2' => 'OS/2',
		'BeOS' => 'BeOS',
		'win' => 'Windows',
		'Dillo' => 'Linux',
		'PalmOS' => 'PalmOS',
		'RebelMouse' => 'RebelMouse'   
	); 
	
	/**
	* Get the class instance
	*
	* @return WC_Customer_Email_Verification_Login_Authentication
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
		add_action( 'woocommerce_created_customer', array( $this, 'add_last_login_details_to_new_customers' ), 10, 3 );		
		add_action( 'wp_login', array( $this, 'cev_woocommerce_account_login_check' ), 10, 3 );		
		add_action( 'wp', array( $this, 'check_user_and_redirect_to_endpoint' ) );	
		add_action( 'wp', array( $this, 'cev_resend_login_otp_email' ), 1 );	
		add_action( 'wp_ajax_nopriv_cev_login_auth_with_otp', array( $this, 'cev_login_auth_with_otp_fun') );
		add_action( 'wp_ajax_cev_login_auth_with_otp', array( $this, 'cev_login_auth_with_otp_fun') );		
		add_filter( 'cev_login_auth_message', array( $this, 'cev_login_auth_message_callback'), 10, 2 );	
	}	

	public function add_last_login_details_to_new_customers( $user_id, $new_customer_data, $password_generated ) {
		
		/* Get login user details */
		$user_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wc_clean( $_SERVER['REMOTE_ADDR'] ) : '';
		$HTTP_USER_AGENT = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wc_clean( $_SERVER['HTTP_USER_AGENT'] ) : '';
		$login_details = $this->cev_parse_user_agent( $HTTP_USER_AGENT );
		
		
		$login_device = isset( $login_details['platform'] ) ? $login_details['platform'] : '';
		$login_browser = isset( $login_details['browser'] ) ? $login_details['browser'] : '';
		
		$login_details = json_decode(file_get_contents("http://ipinfo.io/{$user_ip}/json"));
		$login_postal = isset( $login_details->postal ) ? $login_details->postal : '';	

		$cev_login_detail = array(
			'last_login_device' => $login_device,
			'last_login_browser' => $login_browser,
			'last_login_ip' => $user_ip,
			'last_login_city' => $login_details->city,
			'last_login_region' => $login_details->region,
			'last_login_country' => $login_details->country,
			'last_login_postal' => $login_postal,
		);	

		/* Insert login user details in Usermeta */
		update_user_meta( $user_id, 'cev_last_login_detail', $cev_login_detail );
		update_user_meta( $user_id, 'cev_last_login_time', current_time('mysql') );
	}
	
	public function cev_woocommerce_account_login_check( $user_login ) {

		if ( !isset( $_REQUEST['woocommerce-login-nonce'] ) ) {
			return;
		}

		$user = get_user_by( 'login', $user_login );
		$user_id = $user->ID;
		$user_email = $user->user_email;
		$verified =  cev_pro()->cev_email_settings->check_email_verify($user_email);
		if ( false == $verified ) {
			return;
		}
		$cev_enable_login_authentication = get_option( 'cev_enable_login_authentication', 0 );
		$enable_email_otp_for_account = get_option( 'enable_email_otp_for_account', 1 );		

		/* Get login user details */
		$user_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wc_clean( $_SERVER['REMOTE_ADDR'] ) : '';
		$HTTP_USER_AGENT = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wc_clean( $_SERVER['HTTP_USER_AGENT'] ) : '';
		$login_details = $this->cev_parse_user_agent( $HTTP_USER_AGENT );
		//echo '<pre>';print_r($login_details);echo '</pre>';exit;

		$login_device = isset( $login_details['platform'] ) ? $login_details['platform'] : '';
		$login_browser = isset( $login_details['browser'] ) ? $login_details['browser'] : '';
		
		$login_details = json_decode(file_get_contents("http://ipinfo.io/{$user_ip}/json"));
		$login_postal = isset( $login_details->postal ) ? $login_details->postal : '';			
		
		
		$user_last_login_details = get_user_meta( $user_id, 'cev_last_login_detail' );
		$user_last_login_time = get_user_meta( $user_id, 'cev_last_login_time', true );

		$enable_email_auth_for_login_time = get_option( 'enable_email_auth_for_login_time', 1 );
		$cev_last_login_more_then_time = get_option( 'cev_last_login_more_then_time', 15 );

		$day_diff = $this->get_day_difference( gmdate( 'Y-m-d' ), $user_last_login_time );

		if ( $cev_enable_login_authentication && $enable_email_otp_for_account ) {			

			$send_login_otp = false;
			
			if ( $enable_email_auth_for_login_time && $day_diff > $cev_last_login_more_then_time ) {
				$send_login_otp = true;
			}

			$enable_email_auth_for_new_device = get_option( 'enable_email_auth_for_new_device', 1 );
			$user_last_login_device = isset( $user_last_login_details['0']['last_login_device'] ) ? $user_last_login_details['0']['last_login_device'] : '';						
			
			if ( $enable_email_auth_for_new_device && $login_device != $user_last_login_device && '' != $user_last_login_device ) {
				$send_login_otp = true;
			}

			$enable_email_auth_for_new_location = get_option( 'enable_email_auth_for_new_location', 1 );
			$user_last_login_postal = isset( $user_last_login_details['0']['last_login_postal'] ) ? $user_last_login_details['0']['last_login_postal'] : '';

			if ( $enable_email_auth_for_new_location && $login_postal != $user_last_login_postal && '' != $user_last_login_postal ) {
				$send_login_otp = true;
			}
			
			if ( $send_login_otp ) {				
				$login_otp = cev_pro()->WC_customer_email_verification_email_Common->generate_verification_pin();
				//setcookie( 'cev_login_otp', base64_encode( $login_otp ), time()+86400 );
				//setcookie( 'cev_login_otp_required', true, time()+86400 );
				update_user_meta( $user_id, 'cev_login_otp', $login_otp );
				update_user_meta( $user_id, 'cev_login_otp_required', true );			
				cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id  = $user_id;
				cev_pro()->WC_customer_email_verification_email_Common->email_type  = 'login_otp';
				cev_pro()->WC_customer_email_verification_email_Common->login_ip  = $user_ip;
				cev_pro()->WC_customer_email_verification_email_Common->login_time  = current_time('mysql');
				cev_pro()->WC_customer_email_verification_email_Common->login_browser  = $login_browser;
				cev_pro()->WC_customer_email_verification_email_Common->login_device  = $login_device;
				$this->send_login_otp_email( $user_email, $login_otp );
				//wp_logout();
			} else {
				$cev_login_detail = array(
					'last_login_device' => $login_device,
					'last_login_browser' => $login_browser,
					'last_login_ip' => $user_ip,
					'last_login_city' => $login_details->city,
					'last_login_region' => $login_details->region,
					'last_login_country' => $login_details->country,
					'last_login_postal' => $login_postal,
				);	
	
				/* Insert login user details in Usermeta */
				update_user_meta( $user_id, 'cev_last_login_detail', $cev_login_detail );
				update_user_meta( $user_id, 'cev_last_login_time', current_time('mysql') );
			}
		} else if ( $cev_enable_login_authentication ) {
			$send_login_authentication_email = false;

			if ( $enable_email_auth_for_login_time && $day_diff > $cev_last_login_more_then_time ) {
				$send_login_authentication_email = true;
			}

			$enable_email_auth_for_new_device = get_option( 'enable_email_auth_for_new_device', 1 );
			$user_last_login_device = isset( $user_last_login_details['0']['last_login_device'] ) ? $user_last_login_details['0']['last_login_device'] : '';						
			
			if ( $enable_email_auth_for_new_device && $login_device != $user_last_login_device && '' != $user_last_login_device ) {
				$send_login_authentication_email = true;
			}

			$enable_email_auth_for_new_location = get_option( 'enable_email_auth_for_new_location', 1 );
			$user_last_login_postal = isset( $user_last_login_details['0']['last_login_postal'] ) ? $user_last_login_details['0']['last_login_postal'] : '';

			if ( $enable_email_auth_for_new_location && $login_postal != $user_last_login_postal && '' != $user_last_login_postal ) {
				$send_login_authentication_email = true;
			}

			$cev_login_detail = array(
				'last_login_device' => $login_device,
				'last_login_browser' => $login_browser,
				'last_login_ip' => $user_ip,
				'last_login_city' => $login_details->city,
				'last_login_region' => $login_details->region,
				'last_login_country' => $login_details->country,
				'last_login_postal' => $login_postal,
			);	

			/* Insert login user details in Usermeta */
			update_user_meta( $user_id, 'cev_last_login_detail', $cev_login_detail );
			update_user_meta( $user_id, 'cev_last_login_time', current_time('mysql') );

			if ( $send_login_authentication_email ) {	
				cev_pro()->WC_customer_email_verification_email_Common->email_type  = 'login_authentication';
				cev_pro()->WC_customer_email_verification_email_Common->wuev_user_id  = $user_id;
				$this->send_login_authentication_email( $user_id, $user_email );
			} else {
				$cev_login_detail = array(
					'last_login_device' => $login_device,
					'last_login_browser' => $login_browser,
					'last_login_ip' => $user_ip,
					'last_login_city' => $login_details->city,
					'last_login_region' => $login_details->region,
					'last_login_country' => $login_details->country,
					'last_login_postal' => $login_postal,
				);	
	
				/* Insert login user details in Usermeta */
				update_user_meta( $user_id, 'cev_last_login_detail', $cev_login_detail );
				update_user_meta( $user_id, 'cev_last_login_time', current_time('mysql') );
			}			
		} else {
			$cev_login_detail = array(
				'last_login_device' => $login_device,
				'last_login_browser' => $login_browser,
				'last_login_ip' => $user_ip,
				'last_login_city' => $login_details->city,
				'last_login_region' => $login_details->region,
				'last_login_country' => $login_details->country,
				'last_login_postal' => $login_postal,
			);	

			/* Insert login user details in Usermeta */
			update_user_meta( $user_id, 'cev_last_login_detail', $cev_login_detail );
			update_user_meta( $user_id, 'cev_last_login_time', current_time('mysql') );
		}	
	}	

	public function get_day_difference( $current_date, $last_login_date ) {
		$date1 = date_create($last_login_date);
		$date2=date_create($current_date);
		$diff=date_diff( $date1, $date2 );
		return (int) $diff->format('%R%a');
	}

	public function send_login_otp_email( $recipient, $login_otp ) {
		
		$email_subject     = get_option('cev_verification_login_otp_email_subject', cev_pro()->customizer_options->defaults['cev_verification_login_otp_email_subject']);
		$email_subject 	   = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_subject );
		
		$email_heading     = get_option('cev_verification_login_otp_email_heading', cev_pro()->customizer_options->defaults['cev_verification_login_otp_email_heading']);
		$email_heading 	   = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_heading );

		$email_content = get_option('cev_verification_login_otp_email_content', cev_pro()->customizer_options->defaults['cev_verification_login_otp_email_content']);

		$mailer = WC()->mailer();
		
		// create a new email
		$email = new WC_Email();
		$email->id = 'CEV_Login_OTP';

		$email_content = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_content );
		$footer_content = get_option('cev_login_otp_footer_content', cev_pro()->customizer_options->defaults['cev_login_otp_footer_content']);

		$content = ob_start();					
		$local_template	= get_stylesheet_directory() . '/woocommerce/emails/cev-login-authentication.php';				
		if ( file_exists( $local_template ) && is_writable( $local_template )) {
			wc_get_template( 'emails/cev-login-authentication.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,				
				'footer_content' => $footer_content,				
				'login_otp' => $login_otp,
			), 'customer-email-verification/', get_stylesheet_directory() . '/woocommerce/' );
		} else {
			wc_get_template( 'emails/cev-login-authentication.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,	
				'footer_content' => $footer_content,						
				'login_otp' => $login_otp,
			), 'customer-email-verification/', cev_pro()->get_plugin_path() . '/templates/' );
		}
		$content = ob_get_clean();

		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $content ) ) );
		$message = apply_filters( 'wc_cev_decode_html_content', $message );			
		
		add_filter( 'wp_mail_from', array( cev_pro()->WC_customer_email_verification_email_Common, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( cev_pro()->WC_customer_email_verification_email_Common, 'get_from_name' ) );

		$result = wp_mail( $recipient, $email_subject, $message, $email->get_headers() );
	}

	public function send_login_authentication_email( $user_id, $recipient ) {
		
		$email_subject     = get_option('cev_verification_login_auth_email_subject', cev_pro()->customizer_options->defaults['cev_verification_login_auth_email_subject']);
		$email_subject 	   = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_subject );
		
		$email_heading     = get_option('cev_verification_login_auth_email_heading', cev_pro()->customizer_options->defaults['cev_verification_login_auth_email_heading']);
		$email_heading 	   = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_heading );

		$email_content = get_option('cev_verification_login_auth_email_content', cev_pro()->customizer_options->defaults['cev_verification_login_auth_email_content']);

		$mailer = WC()->mailer();
		
		// create a new email
		$email = new WC_Email();
		$email->id = 'CEV_Login_Authentication';

		$email_content = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $email_content );
		$footer_content = get_option('cev_login_auth_footer_content', cev_pro()->customizer_options->defaults['cev_login_auth_footer_content']);

		$content = ob_start();					
		$local_template	= get_stylesheet_directory() . '/woocommerce/emails/cev-login-authentication.php';				
		if ( file_exists( $local_template ) && is_writable( $local_template )) {
			wc_get_template( 'emails/cev-login-authentication.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,				
				'footer_content' => $footer_content,				
				'login_otp' => '',
			), 'customer-email-verification/', get_stylesheet_directory() . '/woocommerce/' );
		} else {
			wc_get_template( 'emails/cev-login-authentication.php', array( 
				'email_heading' => $email_heading,
				'content' => $email_content,	
				'footer_content' => $footer_content,						
				'login_otp' => '',
			), 'customer-email-verification/', cev_pro()->get_plugin_path() . '/templates/' );
		}
		$content = ob_get_clean();

		$message = apply_filters( 'woocommerce_mail_content', $email->style_inline( $mailer->wrap_message( $email_heading, $content ) ) );
		$message = apply_filters( 'wc_cev_decode_html_content', $message );			
		
		add_filter( 'wp_mail_from', array( cev_pro()->WC_customer_email_verification_email_Common, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( cev_pro()->WC_customer_email_verification_email_Common, 'get_from_name' ) );

		$result = wp_mail( $recipient, $email_subject, $message, $email->get_headers() );
	}	

	public function check_user_and_redirect_to_endpoint() {
				
		if ( !is_account_page() ) {
			return;
		}

		if ( is_user_logged_in() ) {
			$user = get_user_by( 'id', get_current_user_id() );
			
			$user_id = $user->ID;
						
			if ( !$user ) {
				return;					
			}
			
			$user_email = $user->user_email;	
			$verified =  cev_pro()->cev_email_settings->check_email_verify($user_email);			
			if ( false == $verified ) {
				return;
			}
			
			$cev_enable_login_authentication = get_option( 'cev_enable_login_authentication', 0 );
			$enable_email_otp_for_account = get_option( 'enable_email_otp_for_account', 1 );
						
			$redirect_url = wc_get_account_endpoint_url( 'login-authentication' );
			$redirect_url_my_account = wc_get_account_endpoint_url( 'dashboard' );
			$logout_url = wc_get_account_endpoint_url( 'customer-logout' );	
			$logout_url = strtok($logout_url, '?');
			$logout_url = rtrim(strtok($logout_url, '?'), '/');
			$login_authentication_url = rtrim(wc_get_account_endpoint_url( 'login-authentication' ), '/');
			
			global $wp;
			$current_slug = add_query_arg( array(), $wp->request );
			
			if ( home_url( $wp->request ) == $logout_url ) {
				return;															
			}
			//echo $user_id;
			$cev_login_otp_required = get_user_meta( get_current_user_id(), 'cev_login_otp_required', true );
			//print_r(cev_pro()->admin->is_admin_user( $user_id ));
			if ( !cev_pro()->admin->is_admin_user( $user_id ) && 1 == $cev_enable_login_authentication && 1 == $enable_email_otp_for_account && true == $cev_login_otp_required ) {
				
				$cev_login_otp = get_user_meta( get_current_user_id(), 'cev_login_otp', true );
				if ( !empty($cev_login_otp) ) {
					if ( rtrim( home_url( $wp->request ), '/' ) != $login_authentication_url ) {
						wp_safe_redirect( $redirect_url );
						exit;
					}			
				}
			}
		}
	}

	/**
	* Information content
	*/
	public function cev_login_authentication_endpoint_content() {
		
		$current_user = wp_get_current_user();
		$email = $current_user->user_email;				
		$cev_login_otp  = get_user_meta( get_current_user_id(), 'cev_login_otp', true );
		$cev_login_otp_required  = get_user_meta( get_current_user_id(), 'cev_login_otp_required', true );
		
		$CEV_Customizer_Options = new CEV_Customizer_Options();
		$cev_verification_overlay_color = get_option('cev_verification_popup_overlay_background_color',
		$CEV_Customizer_Options->defaults['cev_verification_popup_overlay_background_color']);
		
		if ( cev_pro()->admin->is_admin_user( get_current_user_id() ) ) {
			return;
		}
		
		if ( false === $cev_login_otp_required ) {
			return;
		}
		?>
		<style>
		.cev-authorization-grid__visual{
			background: <?php echo wp_kses_post( cev_pro()->hex2rgba($cev_verification_overlay_color, '0.7') ); ?>;	
		}		
		</style>
		<?php 	
		$cev_button_color_widget_header =  get_option('cev_button_color_widget_header', '#212121');
		$cev_button_text_color_widget_header =  get_option('cev_button_text_color_widget_header', '#ffffff');		
		$cev_widget_header_image_width =  get_option('cev_widget_header_image_width', '80');
		$cev_button_text_header_font_size = get_option('cev_button_text_header_font_size', '22');
		$sample_toggle_switch_cev = get_option('sample_toggle_switch_cev', $CEV_Customizer_Options->defaults['sample_toggle_switch_cev']  );		
		
		$verification_popup_button_size = get_option('cev_popup_button_size', $CEV_Customizer_Options->defaults['cev_popup_button_size']);
		$cev_button_text_size_widget_header = ( 'large' == $verification_popup_button_size ) ? 18 : 16 ;
		$button_padding = ( 'large' == $verification_popup_button_size ) ? '15px 25px' : '12px 20px' ;
		require_once cev_pro()->get_plugin_path() . '/includes/views/cev_login_authentication_popup_template.php';
	}

	public function cev_resend_login_otp_email() {
				
		if ( isset( $_GET['cev_resend_login_otp_email'] ) && '' !== $_GET['cev_resend_login_otp_email'] ) { // WPCS: input var ok, CSRF ok.		

			$cev_resend_login_otp_email = wc_clean( $_GET['cev_resend_login_otp_email'] );
			$user_id = base64_decode( $cev_resend_login_otp_email ); // WPCS: input var ok, CSRF ok.
			
			$user = get_user_by( 'id', $user_id );			
			$user_email = $user->user_email;

			if ( false === WC()->session->has_session() ) {
				WC()->session->set_customer_session_cookie( true );
			}

			$cev_login_otp  = get_user_meta( get_current_user_id(), 'cev_login_otp', true );
			$cev_login_otp_required  = get_user_meta( get_current_user_id(), 'cev_login_otp_required', true );

			if ( $cev_login_otp_required ) {
				$this->send_login_otp_email( $user_email, $cev_login_otp );				
				$message = __( 'A new login OTP email is sent.', 'customer-email-verification' );
				wc_add_notice( $message, 'notice' );
			}
		}
	}

	public function cev_login_auth_with_otp_fun() {
		
		$nonce = isset($_POST['cev_login_auth_with_otp']) ? sanitize_key($_POST['cev_login_auth_with_otp']) : '';

		if (!wp_verify_nonce($nonce, 'cev_login_auth_with_otp')) {
			wp_send_json_error(array('message' => 'Nonce verification failed.'));
			exit;
		}
		
		$cev_login_otp = get_user_meta( get_current_user_id(), 'cev_login_otp', true );
		
		$post_cev_login_otp = isset( $_POST['cev_login_otp'] ) ? wc_clean( $_POST['cev_login_otp'] ) : '';
		
		if ( $cev_login_otp == $post_cev_login_otp ) {
			$my_account = cev_pro()->my_account;
			$redirect_page_id = get_option('cev_redirect_page_after_varification', $my_account);
			
			delete_user_meta( get_current_user_id(), 'cev_login_otp' );
			delete_user_meta( get_current_user_id(), 'cev_login_otp_required' );

			$user_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wc_clean( $_SERVER['REMOTE_ADDR'] ) : '';
			$HTTP_USER_AGENT = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wc_clean( $_SERVER['HTTP_USER_AGENT'] ) : '';
			$login_details = $this->cev_parse_user_agent( $HTTP_USER_AGENT );
			
			$login_device = isset( $login_details['platform'] ) ? $login_details['platform'] : '';
			$login_browser = isset( $login_details['browser'] ) ? $login_details['browser'] : '';
			$login_details = json_decode(file_get_contents("http://ipinfo.io/{$user_ip}/json"));
			$login_postal = isset( $login_details->postal ) ? $login_details->postal : '';				
			
			$cev_login_detail = array(
				'last_login_device' => $login_device,
				'last_login_browser' => $login_browser,
				'last_login_ip' => $user_ip,
				'last_login_city' => $login_details->city,
				'last_login_region' => $login_details->region,
				'last_login_country' => $login_details->country,
				'last_login_postal' => $login_postal,
			);	

			/* Insert login user details in Usermeta */
			update_user_meta( get_current_user_id(), 'cev_last_login_detail', $cev_login_detail );
			update_user_meta( get_current_user_id(), 'cev_last_login_time', current_time('mysql') );
							
			$verification_success_message = get_option('cev_verification_success_message', 'Your Email is verified!');		
			wc_add_notice( $verification_success_message, 'notice' );
			
			echo json_encode( array('success' => 'true','url' => get_permalink($redirect_page_id)) );
			die();
		} else {
			echo json_encode( array('success' => 'false') );
			die();
		}
		exit;
	}

	/**
	 * Turn the user agent into something more readible and handle icons
	 *
	 * @param [string] $u_agent
	 * @return array
	 */
	public function cev_parse_user_agent( $u_agent = null ) {
		
		$platform = null;
		$browser  = null;
		$browsericon = null;
		$version  = '';
		$imgplatform = null;
		$imgbrowser  = null;
		$empty = array('platform' => $platform, 'browser' => $browser, 'version' => $version);
		
		if ( !$u_agent ) {
			return $empty;
		}

		foreach ( $this->_basic_browser as $pattern => $name ) {
			if ( preg_match( '/' . $pattern . '/i', $u_agent, $match)) {
				$browser = $name;
				 // finally get the correct version number
				$known = array('Version', $pattern, 'other');
				$pattern_version = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
				if ( !preg_match_all( $pattern_version, $u_agent, $matches ) ) {
					$aaa = true;
				}				
				// see how many we have
				$i = count($matches['browser']);
				if ( 1 != $i ) {
					//we will have two since we are not using 'other' argument yet
					//see if version is before or after the name
					if ( strripos( $u_agent, 'Version' ) < strripos( $u_agent, $pattern ) ) {
						$version = $matches['version'][0];
					} else {
						$version = $matches['version'][1];
					}
				} else {
					$version = $matches['version'][0];
				}
				break;
			}
		}

		foreach ( $this->_basic_platform as $key => $d_platform ) {
			if (stripos( $u_agent, $key ) !== false) {
				$platform = $d_platform;
				break;
			} 
		}

		return array( 'platform' => $platform, 'browser' => $browser, 'version' => $version );
	}
	
	/**
	 * Return Email verification widget message
	 * 
	 * @since  1.0.0
	*/
	public function cev_login_auth_message_callback( $message, $email ) {
		$message_text = get_option('cev_login_auth_message', $message);
		$message_text = str_replace('{customer_email}', $email, $message_text);
		if ( '' != $message_text ) {
			return $message_text;
		}
		return $message;
	}
}
