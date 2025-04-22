<?php
/**
 * Cev Admin Preview 
 *
 * @class WC_customer_email_verification_preview
 * @package WooCommerce/Classes
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_customer_email_verification_preview class.
 */
class WC_Customer_Email_Verification_Preview_Pro {

	/**
	 * Get the class instance
	 *
	 * @since  1.0.0
	 * @return customer-email-verification-for-woocommerce
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
	}
	
	/*
	 * init function
	 *
	 * @since  1.0
	*/
	public function init() {
		add_action( 'wp_enqueue_scripts', array( $this, 'cev_pro_front_styles' ));
		add_action( 'template_redirect', array( $this, 'preview_cev_page') );
		add_filter( 'cev_verification_popup_message', array( $this, 'cev_verification_popup_message_callback'), 10, 2 );
		add_filter( 'cev_verification_popup_image', array( $this, 'cev_verification_popup_image_callback') );
		add_filter( 'cev_resend_limit_message', array( $this, 'cev_resend_limit_message_callback') );
		
		/* guest user */
		add_action( 'wp_enqueue_scripts', array( $this, 'cev_pro_front_styles_guest_user' ));
		add_action( 'template_redirect', array( $this, 'preview_cev_page_guest_user') );
	}
	
	/**
	 * Include front js and css
	*/
	public function cev_pro_front_styles() {
		$action = ( isset( $_REQUEST['action'] ) ? wc_clean( $_REQUEST['action'] ) : '' );
		
		if ( 'preview_cev_verification_lightbox' == $action ) {
			wp_enqueue_style( 'cev_front_style' );
		}
	}
	/*
	* CEV Page preview
	*/
	public static function preview_cev_page() {
		$action = ( isset( $_REQUEST['action'] ) ? wc_clean( $_REQUEST['action'] ) : '' );
		if ( 'preview_cev_verification_lightbox' != $action ) {
			return;
		}
		wp_head();
		include 'customizer/views/preview_cev_popup_page.php';
		get_footer();
		exit;
	}
	
	/**
	 * Return Email verification widget message
	 * 
	 * @since  1.0.0
	*/
	public function cev_verification_popup_message_callback( $message, $email ) {
		$CEV_Customizer_Options = new CEV_Customizer_Options();
		$message_text = get_option('cev_verification_message', $CEV_Customizer_Options->defaults['cev_verification_message']);
		$message_text = str_replace('{customer_email}', $email, $message_text);
		// $message_text = str_replace('{site_title}', get_bloginfo( 'name' ), $message_text);
		$message_text = cev_pro()->WC_customer_email_verification_email_Common->maybe_parse_merge_tags( $message_text );
		
		if ( '' != $message_text ) {
			return $message_text;
		}
		return $message;
	}
	
	/**
	 * Return Email verification widget image
	 * 
	 * @since  1.0.0
	*/
	public function cev_verification_popup_image_callback( $image ) {
		$image_media = get_option('cev_verification_image', $image);
		if ( '' != $image_media ) {
			return $image_media;
		}
		return $image;
	}
	
	/**
	 * Return Email verification widget Resend Limit Message link
	 * 
	 * @since  1.0.0
	*/
	public function cev_resend_limit_message_callback( $limitmessage ) {
		$resend_limit_message = get_option('cev_resend_limit_message');
		if ( !empty( $resend_limit_message ) ) {
			return $resend_limit_message;
		}
		return $limitmessage;
	}
	
		/**
	 * Include front js and css
	*/
	public function cev_pro_front_styles_guest_user() {
					
		$action = ( isset( $_REQUEST['action']) ? wc_clean( $_REQUEST['action'] ) : '' );
		
		if ( 'guest_user_preview_cev_verification_lightbox' == $action ) {
			wp_enqueue_style( 'cev_front_style' );
			wp_enqueue_script( 'cev-pro-front-js' );
		}
	}
	/*
	* CEV Page preview
	*/
	public static function preview_cev_page_guest_user() {
		$action = ( isset( $_REQUEST['action'] ) ? wc_clean( $_REQUEST['action'] ) : '' );
		if ( 'guest_user_preview_cev_verification_lightbox' != $action ) {
			return;
		}
		
		wp_head();
		$CEV_Customizer_Options = new CEV_Customizer_Options();
		$cev_widget_header_image_width = get_option('cev_widget_header_image_width', $CEV_Customizer_Options->defaults['cev_widget_header_image_width']);
		include 'views/verify_checkout_guest_user.php';
		wp_footer();
		exit;
	}
}
