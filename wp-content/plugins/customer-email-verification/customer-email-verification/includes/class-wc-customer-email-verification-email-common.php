<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Customer_Email_Verification_Email_Common_Pro {

	public $wuev_user_id = null;
	public $email_type = null;
	public $login_ip = null;
	public $login_time = null;
	public $login_browser = null;
	public $login_device = null;
	public $wuev_myaccount_page_id = null;
	public $registerd_user_email = null;
	
	
	/**
	 * Initialize the main plugin function
	*/
	public function __construct() {
				
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
	 * @return WC_customer_email_verification_email_Common
	*/
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
	
	public function init() {
		add_filter( 'wc_cev_decode_html_content', array( $this, 'wc_cev_decode_html_content' ), 1 );
		add_filter( 'verification_email_email_body', array( $this, 'content_do_shortcode' ) );	
					
	}

	public function code_mail_sender( $recipient ) {
		
		$verification_pin = $this->generate_verification_pin();
		$CEV_Customizer_Options = new CEV_Customizer_Options();
		
		$user_id = $this->wuev_user_id;
		
		$expire_time =  get_option('cev_verification_code_expiration', 'never');
		
		if ( empty($expire_time) ) {
			$expire_time = 'never';
		}
		
		$verification_data = array(
			'pin' => $verification_pin, 
			'startdate' => time(),
			'enddate' => time() + ( int ) $expire_time,
		);		

		update_user_meta( $user_id, 'cev_email_verification_pin', $verification_data );
		
		$result        = false;
		$email_subject = get_option('cev_verification_email_subject', $CEV_Customizer_Options->defaults['cev_verification_email_subject']);
		$email_subject = $this->maybe_parse_merge_tags( $email_subject );
		$email_heading = get_option('cev_verification_email_heading', $CEV_Customizer_Options->defaults['cev_verification_email_heading']);		
		
		$mailer = WC()->mailer();
	
		$content = get_option('cev_verification_email_body', $CEV_Customizer_Options->defaults['cev_verification_email_body']);
		$content = $this->maybe_parse_merge_tags( $content );
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

		return $result;
	}
	
	
	public function my_allowed_tags( $tags ) {
		$tags['style'] = array( 'type' => true, );
		return $tags;
	}
	
	public function safe_style_css_callback( $styles ) {		
		$styles[] = 'display';		
		return $styles;
	}

	public function content_do_shortcode( $content ) {
		return do_shortcode( $content );
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
	/*
	 * This function removes backslashes from the textfields and textareas of the plugin settings.
	 */
	public function wc_cev_decode_html_content( $content ) {
		if ( empty( $content ) ) {
			return '';
		}
		$content = preg_replace( '#<script(.*?)>(.*?)</script>#is', '', $content );

		return html_entity_decode( stripslashes( $content ) );
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
	public function maybe_parse_merge_tags( $content = '' ) {
		$get_all      = $this->get_all_tags();
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

	public function get_all_tags() {
		$tags = array(
			array(
				'name' => __( 'User login', 'customer-email-verification' ),
				'tag'  => 'cev_user_login',
			),
			array(
				'name' => __( 'User display name', 'customer-email-verification' ),
				'tag'  => 'cev_display_name',
			),
			array(
				'name' => __( 'User email', 'customer-email-verification' ),
				'tag'  => 'cev_user_email',
			),
			array(
				'name' => __( 'Email Verification Link', 'customer-email-verification' ),
				'tag'  => 'cev_user_verification_link',
			),	
			array(
				'name' => __( 'Verification Link', 'customer-email-verification' ),
				'tag'  => 'customer_email_verification_code',
			),
			array(
				'name' => __( 'Resend Confirmation Email', 'customer-email-verification' ),
				'tag'  => 'cev_resend_email_link',
			),
			array(
				'name' => __( 'Verification Pin', 'customer-email-verification' ),
				'tag'  => 'cev_user_verification_pin',
			),
			array(
				'name' => __( 'Site Title', 'customer-email-verification' ),
				'tag'  => 'site_title',
			),
			array(
				'name' => __( 'Try Again', 'customer-email-verification' ),
				'tag'  => 'cev_resend_verification',
			),
			array(
				'name' => __( 'Change Password', 'customer-email-verification' ),
				'tag'  => 'cev_change_pw_btn',
			),
			array(
				'name' => __( 'Login IP', 'customer-email-verification' ),
				'tag'  => 'login_ip',
			),
			array(
				'name' => __( 'Login Time', 'customer-email-verification' ),
				'tag'  => 'login_time',
			),
			array(
				'name' => __( 'Login Browser', 'customer-email-verification' ),
				'tag'  => 'login_browser',
			),
			array(
				'name' => __( 'Login Device', 'customer-email-verification' ),
				'tag'  => 'login_device',
			),
			array(
				'name' => __( 'Cancle Verification', 'customer-email-verification' ),
				'tag'  => 'cev_cancle_verification',
			),
		);

		return $tags;
	}
	
	public function cev_cancle_verification() {
		
		ob_start(); 
		?>
			<a href="<?php esc_html_e( wp_logout_url( home_url() ) ); ?>" class="" >Cancel Verification</a>
			<?php 
			$logout = ob_get_clean();
			return $logout;
		
	}

	public function customer_email_verification_code() {
		$secret = get_user_meta( $this->wuev_user_id, 'customer_email_verification_code', true );
		return $secret;
	}
	
	public function cev_user_login() {
		$user = get_userdata( $this->wuev_user_id );
		$user_login = $user->user_login ;
		return $user_login;
	}
	
	public function cev_user_email() {
		$user = get_userdata( $this->wuev_user_id );
		$user_email = $user->user_email ;
		return $user_email;
	}
	
	public function cev_display_name() {
		$user = get_userdata( $this->wuev_user_id );
		$display_name = $user->display_name;

		return $display_name;
	}
	
	public function cev_user_verification_link() {
		$CEV_Customizer_Options = new CEV_Customizer_Options();
		$cev_new_email_link_color = get_option('cev_new_email_link_color', $CEV_Customizer_Options->defaults['cev_new_email_link_color']) ;		
		$cev_verification_selection = get_option('cev_verification_selection');
			
		$cev_email_verification_button_size = get_option('cev_email_verification_button_size', $CEV_Customizer_Options->defaults['cev_email_verification_button_size']);
		$verification_button_font_size = ( 'large' == $cev_email_verification_button_size ) ? 16 : 14 ;		
		$user_email = $this->registerd_user_email;	
		global $wpdb;
		$email_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}cev_user_log WHERE email = %s", $user_email));
		if ($email_exists) {
			$secret = $wpdb->get_var($wpdb->prepare("SELECT secret_code FROM {$wpdb->prefix}cev_user_log WHERE email = %s", $user_email));
		} else {
			$secret  = md5( $user_email . time() );
		}
		// $secret      = get_user_meta( $this->wuev_user_id, 'customer_email_verification_code', true );
		$create_link = $secret . '@' . $user_email;
		$hyperlink   = add_query_arg( array(
			'cusomer_email_verify' => base64_encode( $create_link ),
		), get_the_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
		
		$style = 'text-decoration:  none;  color: ' . $cev_new_email_link_color . '; font-size: ' . $verification_button_font_size . 'px';
		$style = $this->cev_user_verification_link_style( $style );
		
		if ( 'button' == $cev_verification_selection ) {
			$link = '<p><a style="' . $style . '" href="' . $hyperlink . '">' . get_option( 'cev_new_acoount_button_text', __( 'Verify your Email', 'customer-email-verification' )) . '</a></p>';
		} else {
			$link = '<p><a style="' . $style . '" href="' . $hyperlink . '">' . get_option( 'cev_new_acoount_button_text', __( 'Verify your Email', 'customer-email-verification' )) . '</a></p>';
		}
		
		return $link;
	}
	
	/**
	 * Cev Email button style 
	 */	
	public function cev_user_verification_link_style( $style ) {		
		$CEV_Customizer_Options = new CEV_Customizer_Options();	
		
		$cev_verification_selection = get_option('cev_verification_selection', $CEV_Customizer_Options->defaults['cev_verification_selection']);
		$cev_new_email_button_color = get_option('cev_new_email_button_color', $CEV_Customizer_Options->defaults['cev_new_email_button_color']);
		$cev_new_email_button_color_text = get_option('cev_new_email_button_color_text', $CEV_Customizer_Options->defaults['cev_new_email_button_color_text']);		
				
		$cev_email_verification_button_size = get_option('cev_email_verification_button_size', $CEV_Customizer_Options->defaults['cev_email_verification_button_size']);
		$verification_button_font_size = ( 'large' == $cev_email_verification_button_size ) ? 16 : 14 ;
		$verification_button_padding = ( 'large' == $cev_email_verification_button_size ) ? '12px 20px' : '10px 15px' ;
		
		if ( 'button' == $cev_verification_selection ) {
			$style = 'color: ' . $cev_new_email_button_color_text . ';display:inline-block;font-weight: normal;text-decoration: none; background:' . $cev_new_email_button_color . '; padding: ' . $verification_button_padding . ';font-size: ' . $verification_button_font_size . 'px; border-radius: 3px ';
			return $style;
		} else {
			return $style;	
		}		
	}
	
	/*
	* return true/false if resend email reached
	*/
	public function cev_resend_email_limit( $resend_limit_reached, $user_id ) {
		
		$cev_redirect_limit_resend = get_option( 'cev_redirect_limit_resend', 1 );
		$user_resend_times = get_user_meta( $user_id, 'cev_user_resend_times', true );
		
		if ( null == $user_resend_times ) {
			$user_resend_times=0;
		}
		if ( $user_resend_times >= $cev_redirect_limit_resend ) {
			$resend_limit_reached = true;
		}				
		
		return $resend_limit_reached;
	}
	
	public function cev_resend_email_link() {
		$link = add_query_arg( array(
			'cev_redirect_limit_resend' => base64_encode( $this->wuev_user_id ),
		), get_the_permalink( $this->wuev_myaccount_page_id ) );
		$resend_confirmation_text = __( 'Resend Confirmation Email', 'customer-email-verification' );
		$cev_resend_link          = '<a href="' . $link . '">' . $resend_confirmation_text . '</a>';

		return $cev_resend_link;
	}
	
	public function cev_user_verification_pin() {	
		
		$user_email = $this->registerd_user_email;			
		
		global $wpdb;
		$email_exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}cev_user_log WHERE email = %s", $user_email));
		if ($email_exists) {
			$verification_pin = $wpdb->get_var($wpdb->prepare("SELECT pin FROM {$wpdb->prefix}cev_user_log WHERE email = %s", $user_email));
		} else {
			$user_id = $this->wuev_user_id;
			$cev_email_verification_pin = get_user_meta( $user_id, 'cev_email_verification_pin', true );
			if ( !empty($cev_email_verification_pin) ) {
				$verification_pin = $cev_email_verification_pin['pin'];
			} else {
				$verification_pin = $this->generate_verification_pin();
			}
				
		}
				
		return '<span>' . $verification_pin . '</span>';
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
	
	public static function site_title() {
		return get_bloginfo( 'name' );
	}
	
	public function cev_resend_verification() {	
		$resend_limit_reached = $this->cev_resend_email_limit( false, get_current_user_id() );
		if ( 'login-authentication' == WC()->query->get_current_endpoint() ) {
			$resend_email_link = add_query_arg( array('cev_resend_login_otp_email' => base64_encode( get_current_user_id() ),), get_the_permalink( $this->wuev_myaccount_page_id ) );
		} else {
			$resend_email_link = add_query_arg( array('cev_redirect_limit_resend' => base64_encode( get_current_user_id() ),), get_the_permalink( $this->wuev_myaccount_page_id ) );
		}
		
		//echo WC()->query->get_current_endpoint();exit;
		if ( is_user_logged_in() ) {
			ob_start(); 
			$class = ( $resend_limit_reached ) ? 'cev-try-again-disable' : '';	
			?>
			<a href="<?php echo esc_html( $resend_email_link ); ?>" class="cev-link-try-again <?php echo esc_html( $class ); ?>"><?php esc_html_e( 'Try Again', 'customer-email-verification' ); ?></a>
			<?php 
			$try_again_url = ob_get_clean();
			return $try_again_url;
		} else { 
			$class = ( $resend_limit_reached ) ? 'cev-try-again-disable' : '';	
			if ( is_account_page() ) {
				ob_start(); 
				?>
				<a href="#" class="cev-link-try-again send_again_link  <?php echo esc_html( $class ); ?>"><?php esc_html_e( 'Try Again', 'customer-email-verification' ); ?></a>
				<?php 
				$try_again_url = ob_get_clean();
				return $try_again_url;
			} else {
				ob_start(); 
				?>
				<a href="#" class="cev-link-try-again resend_verification_code_guest_user  <?php echo esc_html( $class ); ?>" data-nonce="<?php esc_html_e( wp_create_nonce( 'wc_cev_email_guest_user' ) ); ?>"><?php esc_html_e( 'Try Again', 'customer-email-verification' ); ?></a>
				<?php 
				$try_again_url = ob_get_clean();
				return $try_again_url;
			}
		}		
	}

	public function cev_change_pw_btn() {
		ob_start(); 
		$CEV_Customizer_Options = new CEV_Customizer_Options();
		$cev_new_email_link_color = get_option('cev_new_email_link_color', $CEV_Customizer_Options->defaults['cev_new_email_link_color']) ;
		$cev_email_verification_button_size = get_option('cev_email_verification_button_size', $CEV_Customizer_Options->defaults['cev_email_verification_button_size']);
		$verification_button_font_size = ( 'large' == $cev_email_verification_button_size ) ? 16 : 14 ;
		$style = 'text-decoration:  none;  color: ' . $cev_new_email_link_color . '; font-size: ' . $verification_button_font_size . 'px';
		$style = $this->cev_user_verification_link_style( $style );
		?>
		<a style='<?php esc_html_e( $style ); ?>' href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>"><?php echo esc_html__( 'Change Password', 'customer-email-verification' ); ?></a>
		<?php 
		$try_again_url = ob_get_clean();
		return $try_again_url;
	}

	public function login_ip() {
		$user_id = $this->wuev_user_id;
		$email_type = $this->email_type;
		if ( 'login_otp' == $email_type ) {
			return $this->login_ip;
		} else {
			$user_last_login_details = get_user_meta( $user_id, 'cev_last_login_detail' );
			if ( isset( $user_last_login_details['0']['last_login_ip'] ) ) {
				return $user_last_login_details['0']['last_login_ip'];
			} else {
				return '192.0.2.0';
			}
		}		
	}

	public function login_time() {
		$user_id = $this->wuev_user_id;			
		$email_type = $this->email_type;
		if ( 'login_otp' == $email_type ) {
			return $this->login_time;
		} else {
			$user_last_login_time = get_user_meta( $user_id, 'cev_last_login_time', true );
			if ( null == $user_last_login_time ) {
				return current_time('mysql');
			} else {
				return $user_last_login_time;
			}			
		}
	}

	public function login_browser() {
		$user_id = $this->wuev_user_id;	
		$email_type = $this->email_type;
		if ( 'login_otp' == $email_type ) {
			return $this->login_browser;
		} else {
			$user_last_login_details = get_user_meta( $user_id, 'cev_last_login_detail' );
			if ( isset( $user_last_login_details['0']['last_login_browser'] ) ) {
				return $user_last_login_details['0']['last_login_browser'];
			} else {
				return 'Chrome';
			}
		}
	}

	public function login_device() {
		$user_id = $this->wuev_user_id;	
		$email_type = $this->email_type;
		if ( 'login_otp' == $email_type ) {
			return $this->login_device;
		} else {
			$user_last_login_details = get_user_meta( $user_id, 'cev_last_login_detail' );
			if ( isset( $user_last_login_details['0']['last_login_device'] ) ) {
				return $user_last_login_details['0']['last_login_device'];
			} else {
				return 'Windows';
			}
			
		}
	}
}
