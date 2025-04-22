<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CEV_Customizer_Options {

	/**
	 * Get the class instance
	 *
	 * @since  1.0
	 * @return CEV_Customizer_Options
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
	public $defaults;
	public function __construct() {
		$this->defaults = $this->cev_generate_defaults();
		$this->init();
	}

	/*
	 * init function
	 *
	 * @since  1.0
	*/
	public function init() {
		// Custom Hooks for everyone
		add_filter( 'cev_customizer_email_options', array( $this, 'cev_customizer_panel_options' ), 10, 2 );
		add_filter( 'cev_customizer_email_options', array( $this, 'cev_customizer_popup_style_options' ), 10, 2 );
		add_filter( 'cev_customizer_email_options', array( $this, 'cev_customizer_popup_content_options' ), 10, 2 );
		add_filter( 'cev_customizer_email_options', array( $this, 'cev_customizer_email_style_options' ), 20, 2 );
		add_filter( 'cev_customizer_email_options', array( $this, 'cev_customizer_email_content_options' ), 30, 2 );				
	}

	/**
	 * Code for initialize default value for customizer
	*/	
	public function cev_generate_defaults() {
		$cev_default = array(		
			'cev_widget_content_width_style' => '650',
			'cev_content_align_style' => 'left',
			'cev_widget_content_padding_style' => '30',
			'cev_verification_content_background_color'	=> '#fafafa',
			'cev_verification_content_border_color' => '#e0e0e0',
			'cev_verification_content_font_color' => '#333333',
			'cev_verification_image_content' => cev_pro()->plugin_dir_url() . 'assets/css/images/email-verification.png',
			'cev_email_content_widget_header_image_width'   => '80',
			'cev_header_content_font_size' => '18',
			'cev_new_acoount_button_text'	=> __( 'Verify Your Email', 'customer-email-verification' ),
			'cev_button_text_font_size' => '14',
			'cev_button_padding_size' => '15',
			'cev_new_email_link_color' => '#0052ff',
			'cev_email_verification_button_size' => 'normal',
			'cev_content_widget_type' => 'registration',
			'cev_verification_email_heading' => __( 'Verify Your Email Address', 'customer-email-verification' ),
			'cev_verification_email_heading_che' => __( 'Verify Your Email Address', 'customer-email-verification' ),			
			'cev_verification_email_subject' =>  __( 'Please Verify Your Email Address on {site_title}', 'customer-email-verification' ),
			'cev_verification_email_subject_che' =>  __( 'Please Verify Your Email Address on {site_title}', 'customer-email-verification' ),		
			'cev_verification_email_body' => __( 'Thank you for signing up for {site_title}, to activate your account, we need to verify your email address.<p>Your verification code: <strong>{cev_user_verification_pin}</strong></p>', 'customer-email-verification' ),
			'cev_verification_email_body_che' => __( 'To complete your order on {site_title}, please confirm your email address. This ensures we have the right email in case we need to contact you.<p>Your verification code: {cev_user_verification_pin}</p><p>Or, verify your account clicking on the button below:</p>{cev_user_verification_link}', 'customer-email-verification' ),	
			'cev_new_email_button_color' => '#03a9f4',
			'cev_new_email_button_color_text' => '#ffffff',
			'cev_verification_selection' => 'button',
			'cev_header_button_size_pro'	=> '14px',
			'cev_new_verification_Footer_content' => '',
			'cev_new_verification_Footer_content_che' => '',
			'cev_verification_image'	=> cev_pro()->plugin_dir_url() . 'assets/css/images/email-verification.svg',
			'cev_widget_header_image_width'   => '80',
			'cev_button_text_header_font_size' => '18',
			'cev_button_color_widget_header' => '#2296f3',
			'cev_button_text_color_widget_header'	=> '#ffffff',
			'cev_button_text_size_widget_header' => '14',
			'cev_verification_popup_background_color'	=> '#f5f5f5',
			'cev_verification_popup_overlay_background_color' => '#ffffff',
			'cev_widget_content_width' => '440',
			'cev_content_align' => 'Center',
			'cev_button_padding_size_widget_header' => '15',
			'cev_widget_content_padding' => '30',
			'sample_toggle_switch_cev' => '0',
			'cev_verification_hide_otp_section' => '0',
			'cev_verification_widget_hide_otp_section' => '0',
			'cev_login_hide_otp_section' => '0',
			'cev_popup_button_size' => 'normal',
			'cev_widget_type' => 'popup_registration',
			'cev_verification_header' => __( 'Verify its you.', 'customer-email-verification' ),
			'cev_verification_message'	=> __('We sent verification code to  {customer_email}. To verify your email address, please check your inbox and enter the code below.', 'customer-email-verification'),
			'cev_verification_widget_footer'  =>__("Didn't receive an email? <strong>{cev_resend_verification}</strong>", 'customer-email-verification'),
			'cev_login_auth_header' => __( 'Verify its you.', 'customer-email-verification' ),
			'cev_login_auth_message'	=> __('We sent verification code to  {customer_email}. To verify your email address, please check your inbox and enter the code below.', 'customer-email-verification'),
			'cev_login_auth_widget_footer'  =>__("Didn't receive an email? <strong>{cev_resend_verification}</strong>", 'customer-email-verification'),
			'cev_login_auth_button_text' => __('Verify Code', 'customer-email-verification'),
			'cev_header_text_checkout'	=> __( 'Verify its you.', 'customer-email-verification' ),
			'cev_verification_widget_message_checkout'	=> __( 'Please verify your email address to proceed to checkout.', 'customer-email-verification' ),
			'cev_verification_widget_message_footer_checkout_pro'	=> __( 'Already have an account?', 'customer-email-verification') . '<a href="/my-account">' . __(' Login now', 'customer-email-verification') . '</a>',
			'cev_verification_header_button_text' => __('Verify Code', 'customer-email-verification'),
			'cev_verification_header_send_verify_button_text' => __('Verify Your Email', 'customer-email-verification'),
			'cev_verification_header_verify_button_text' => __('Verify Code', 'customer-email-verification'),
			'cev_verification_login_auth_email_subject' => __('New sign-in from {login_browser} on {login_device}', 'customer-email-verification'),
			'cev_verification_login_auth_email_heading' => __('New sign-in from {login_browser} on {login_device}', 'customer-email-verification'),
			'cev_verification_login_auth_email_content' => __("Hi {cev_display_name},

There was a new login to your {site_title} account from {login_browser} on {login_device}
			
We wanted to make sure it was you. Please check the details below:
			
<strong>Device:</strong> {login_browser}, {login_device}
<strong>Date:</strong> {login_time}
<strong>IP:</strong> {login_ip}

If you don't recognize this activity, click the button below to change your password right away.

{cev_change_pw_btn}

Thanks", 'customer-email-verification'),
			'cev_login_auth_footer_content' => '',
			'cev_verification_login_otp_email_subject' => __('New sign-in from {login_browser} on {login_device}', 'customer-email-verification'),
			'cev_verification_login_otp_email_heading' => __('New sign-in from {login_browser} on {login_device}', 'customer-email-verification'),
			'cev_verification_login_otp_email_content' => __("Hi {cev_display_name},

There was a new login to your {site_title} account from {login_browser} on {login_device}

We wanted to make sure it was you. Please verify your account using this OTP: <strong>{login_otp}</strong>

If you don't recognize this activity, click the button below to change your password right away.

{cev_change_pw_btn}

Thanks", 'customer-email-verification'),
			'cev_login_otp_footer_content' => '',
		);
		return $cev_default;        
	}

	public function cev_customizer_panel_options( $settings, $preview ) {
		
		$settings['cev_verification_design_panel'] = array(
			'title'	=> esc_html__( 'Design', 'customer-email-verification' ),
			'type'	=> 'panel',
			'preview' => 'popup_registration',
		);
		
		$settings['cev_verification_popup_style'] = array(
			'title'	=> esc_html__( 'Popup Style', 'customer-email-verification' ),
			'type'	=> 'sub-panel',
			'parent'	=> 'cev_verification_design_panel',
			'preview' => 'popup_registration',
		);
	
		$settings['cev_verificaion_popup_message'] = array(
			'title'	=> esc_html__( 'Popup Content', 'customer-email-verification' ),
			'type'	=> 'panel',
			'preview' => 'popup_registration',
		);
		
		$settings['cev_email_style_section'] = array(
			'title'	=> esc_html__( 'Email Style', 'customer-email-verification' ),
			'type'	=> 'sub-panel',
			'parent'	=> 'cev_verification_design_panel',
			'preview' => 'email_registration',
		);
	
		$settings['cev_email_content'] = array(
			'title'	=> esc_html__( 'Email Content', 'customer-email-verification' ),
			'type'	=> 'panel',
			'preview' => 'email_registration',
		);
		
		return $settings;
	}

	public function cev_customizer_popup_style_options( $settings, $preview ) {
		
		$font_size_array_cev[ '' ] = __( 'Select', 'customer-email-verification' );
		for ( $i = 10; $i <= 30; $i++ ) {
			$font_size_array_cev[ $i ] = $i . 'px';
		}
	
		//Overlay Background Color
		$settings['cev_verification_popup_overlay_background_color'] = array(
			'title'    => esc_html__( 'Overlay Background Color', 'customer-email-verification' ),
			'parent'	=> 'cev_verification_popup_style',
			'type'     => 'color',
			'default'  => $this->get_option_val( 'cev_verification_popup_overlay_background_color' ),
			'show'     => true,
			'option_name' => 'cev_verification_popup_overlay_background_color',
			'option_type' => 'key',
			'refresh'   => true,
		);
	
		//Widget Background Color
		$settings['cev_verification_popup_background_color'] = array(
			'title'    => esc_html__( 'Widget Background Color', 'customer-email-verification' ),
			'parent'	=> 'cev_verification_popup_style',
			'type'     => 'color',
			'default'  => $this->get_option_val( 'cev_verification_popup_background_color' ),
			'show'     => true,
			'option_name' => 'cev_verification_popup_background_color',
			'option_type' => 'key',
			'refresh'   => true,
		);
	
		//Content align
		$settings['cev_content_align'] = array(
			'title'    => esc_html__( 'Content Align', 'customer-email-verification' ),
			'parent'	=> 'cev_verification_popup_style',
			'type'     => 'select',
			'default'  => $this->get_option_val( 'cev_content_align' ),
			'options'  => array(
				'center' => __( 'Center', 'customer-email-verification' ),
				'left' => __( 'Left', 'customer-email-verification' ),
			),
			'show'     => true,
			'option_name' => 'cev_content_align',
			'option_type' => 'key',
			'refresh'   => true,
		);
	
		// Content width
		$settings['cev_widget_content_width'] = array(
			'title'    => esc_html__( 'Content Width', 'customer-email-verification' ),
			'parent'	=> 'cev_verification_popup_style',
			'type'     => 'range',
			'default'  => $this->get_option_val( 'cev_widget_content_width' ),
			'show'     => true,
			'option_name' => 'cev_widget_content_width',
			'option_type' => 'key',
			'min' => '300',
			'max' => '600',
			'unit'=> 'px',
			'refresh'   => true,
		);
	
		// Content Padding
		$settings['cev_widget_content_padding'] = array(
			'title'    => esc_html__( 'Content Padding', 'customer-email-verification' ),
			'parent'	=> 'cev_verification_popup_style',
			'type'     => 'range',
			'default'  => $this->get_option_val( 'cev_widget_content_padding' ),
			'show'     => true,
			'option_name' => 'cev_widget_content_padding',
			'option_type' => 'key',
			'min' => '10',
			'max' => '100',
			'unit'=> 'px',
			'refresh'   => true,
		);   
		
		//Popup Header
		$settings['cev_verification_widget_style_Popup_Header'] = array(
			'title'    => esc_html__( 'Popup Header', 'customer-email-verification' ),
			'parent'    => 'cev_verification_popup_style',
			'type'     => 'title',
			'show'     => true,                
		);
	
		//Header Image
		$settings['cev_verification_image'] = array(
			'title'    => esc_html__( 'Header Image', 'customer-email-verification' ),
			'parent'	=> 'cev_verification_popup_style',
			'type'     => 'media',
			'show'     => true,
			'option_type' => 'key',
			'desc'     => '',
			'option_name' => 'cev_verification_image',
			'default'	=> $this->get_option_val( 'cev_verification_image' ),
			'refresh'   => true,
		);
	
		// Image Width
		$settings['cev_widget_header_image_width'] = array(
			'title'    => esc_html__( 'Image Width', 'customer-email-verification' ),
			'parent'	=> 'cev_verification_popup_style',
			'type'     => 'range',
			'default'  => $this->get_option_val( 'cev_widget_header_image_width' ),
			'show'     => true,
			'option_name' => 'cev_widget_header_image_width',
			'option_type' => 'key',
			'min' => '25',
			'max' => '250',
			'unit'=> 'px',
			'refresh'   => true,
		);
		
		//Header font size
		$settings['cev_button_text_header_font_size'] = array(
			'title'    => esc_html__( 'Header Font Size', 'customer-email-verification' ),
			'parent'	=> 'cev_verification_popup_style',
			'type'     => 'select',
			'default'  => $this->get_option_val( 'cev_button_text_header_font_size' ),
			'options'  => $font_size_array_cev,
			'show'     => true,
			'option_name' => 'cev_button_text_header_font_size',
			'option_type' => 'key',				
			'refresh'   => true,
		);
		return $settings;
	}
	
	public function cev_customizer_popup_content_options( $settings, $preview ) {
	
		//Popup Type
		$settings['cev_widget_type'] = array(
			'title'    => esc_html__( 'Popup Type', 'customer-email-verification' ),
			'parent'   => 'cev_verificaion_popup_message',
			'type'     => 'select',
			'default'  => $this->get_option_val( 'cev_widget_type' ),
			'options'  => array(
							'popup_registration' => __( 'Registration', 'customer-email-verification' ),
							'popup_login_auth' => __( 'Login Authentication', 'customer-email-verification' ),	
							'popup_checkout' => __( 'Checkout', 'customer-email-verification' ),									
						),
			'show'     => true,
			'option_name' => 'cev_widget_type',
			'option_type' => 'key',
			'previewType' => true,
		);
	
		//Header text
		$settings['cev_verification_header'] = array(
			'title'    => esc_html__( 'Header Text', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $this->get_option_val( 'cev_verification_header' ),
			'placeholder' => esc_html__( 'Header Text', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_verification_header',
			'option_type' => 'key',
			'class'		  => 'popup_registration_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Message
		$settings['cev_verification_message'] = array(
			'title'    => esc_html__( 'Message', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $this->get_option_val( 'cev_verification_message' ),
			'placeholder' => esc_html__( 'Message', 'customer-email-verification' ),
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_verification_message',
			'option_type' => 'key',
			'class'		  => 'popup_registration_sub_menu all_status_submenu',
			'refresh'   => true,
		);
		//Footer content
		$settings['cev_verification_widget_footer'] = array(
			'title'    => esc_html__( 'Footer Content', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $this->get_option_val( 'cev_verification_widget_footer' ),
			'placeholder' => esc_html__( 'Footer Content', 'customer-email-verification' ),
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_verification_widget_footer',
			'option_type' => 'key',
			'class'		  => 'popup_registration_sub_menu all_status_submenu',
			'refresh'   => true,
		);
		//Header text
		$settings['cev_login_auth_header'] = array(
			'title'    => esc_html__( 'Header Text', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $this->get_option_val( 'cev_login_auth_header' ),
			'placeholder' => esc_html__( 'Header Text', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_login_auth_header',
			'option_type' => 'key',
			'class'		  => 'popup_login_auth_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Message
		$settings['cev_login_auth_message'] = array(
			'title'    => esc_html__( 'Message', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $this->get_option_val( 'cev_login_auth_message' ),
			'placeholder' => esc_html__( 'Message', 'customer-email-verification' ),
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_login_auth_message',
			'option_type' => 'key',
			'class'		  => 'popup_login_auth_sub_menu all_status_submenu',
			'refresh'   => true,
		);
		//Footer content
		$settings['cev_login_auth_widget_footer'] = array(
			'title'    => esc_html__( 'Footer Content', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $this->get_option_val( 'cev_login_auth_widget_footer' ),
			'placeholder' => esc_html__( 'Footer Content', 'customer-email-verification' ),
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_login_auth_widget_footer',
			'option_type' => 'key',
			'class'		  => 'popup_login_auth_sub_menu all_status_submenu',
			'refresh'   => true,
		);
		//Checkout Header text
		$settings['cev_header_text_checkout'] = array(
			'title'    => esc_html__( 'Header Text', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $this->get_option_val( 'cev_header_text_checkout' ),
			'placeholder' => esc_html__( 'Header Text', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_header_text_checkout',
			'option_type' => 'key',
			'class'		  => 'popup_checkout_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Checkout Message
		$settings['cev_verification_widget_message_checkout'] = array(
			'title'    => esc_html__( 'Widget Content', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $this->get_option_val( 'cev_verification_widget_message_checkout' ),
			'placeholder' => esc_html__( 'Widget Content', 'customer-email-verification' ),
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_verification_widget_message_checkout',
			'option_type' => 'key',
			'class'		  => 'popup_checkout_sub_menu all_status_submenu',
			'refresh'   => true,
		);
		
		//Checkout Footer content
		$settings['cev_verification_widget_message_footer_checkout_pro'] = array(
			'title'    => esc_html__( 'Footer Content', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $this->get_option_val( 'cev_verification_widget_message_footer_checkout_pro' ),
			'placeholder' => esc_html__( 'Footer Content', 'customer-email-verification' ),
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_verification_widget_message_footer_checkout_pro',
			'option_type' => 'key',
			'class'		  => 'popup_checkout_sub_menu all_status_submenu',
			'refresh'   => true,
		);
		$available_variables = '<code>You can use HTML tags : &lt;a&gt;, &lt;strong&gt;, &lt;i&gt; and placeholders:{site_title}<br>{customer_email}<br>{cev_resend_verification}</code>';

		// Check if the preview is for 'popup_registration' and remove {customer_email}<br> if true
		if ( isset( $_GET['preview'] ) && 'popup_registration' === $_GET['preview'] ) {
			$available_variables = '<code>You can use HTML tags : &lt;a&gt;, &lt;strong&gt;, &lt;i&gt; and placeholders:{site_title}<br>{cev_resend_verification}</code>';
		}
		//Available variables
		$settings['cev_widzet_code_block'] = array(
			'title'    => esc_html__( 'Available Variables', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $available_variables,			
			'type'     => 'codeinfo',
			'show'     => true,
		);
	
		//Verification Your Email Button Text
		$settings['cev_verification_header_send_verify_button_text'] = array(
			'title'    => esc_html__( 'Verification Your Email Button Text', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $this->get_option_val( 'cev_verification_header_send_verify_button_text' ),
			'placeholder' => esc_html__( 'Verification Your Email Button Text', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_verification_header_send_verify_button_text',
			'option_type' => 'key',
			'class'		=> 'popup_checkout_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Verification Button Text
		$settings['cev_verification_header_verify_button_text'] = array(
			'title'    => esc_html__( 'Verification Button Text', 'customer-email-verification' ),
			'parent'	=> 'cev_verificaion_popup_message',
			'default'  => $this->get_option_val( 'cev_verification_header_verify_button_text' ),
			'placeholder' => esc_html__( 'Verification Button Text', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_verification_header_verify_button_text',
			'option_type' => 'key',
			'class'		=> 'popup_checkout_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		return $settings;
	}

	public function cev_customizer_email_style_options( $settings, $preview ) {
		
		$font_size_array_cev[ '' ] = __( 'Select', 'customer-email-verification' );
		for ( $i = 10; $i <= 30; $i++ ) {
			$font_size_array_cev[ $i ] = $i . 'px';
		}
		
		// Content width
		$settings['cev_widget_content_width_style'] = array(
			'title'    => esc_html__( 'Content width', 'customer-email-verification' ),
			'parent'	=> 'cev_email_style_section',
			'type'     => 'range',
			'default'  => $this->get_option_val( 'cev_widget_content_width_style' ),
			'show'     => true,
			'option_name' => 'cev_widget_content_width_style',
			'option_type' => 'key',
			'min' => '400',
			'max' => '1000',
			'unit'=> 'px',
			'refresh'   => true,
		);
		
		// Content align
		$settings['cev_content_align_style'] = array(
			'title'    => esc_html__( 'Content align', 'customer-email-verification' ),
			'parent'	=> 'cev_email_style_section',
			'type'     => 'select',
			'default'  => $this->get_option_val( 'cev_content_align_style' ),
			'options'  => array(
							'center' => __( 'Center', 'customer-email-verification' ),
							'left' => __( 'Left', 'customer-email-verification' ),
						),
			'show'     => true,
			'option_name' => 'cev_content_align_style',
			'option_type' => 'key',				
			'refresh'   => true,
		);
	
		// Content padding
		$settings['cev_widget_content_padding_style'] = array(
			'title'    => esc_html__( 'Content Padding', 'customer-email-verification' ),
			'parent'	=> 'cev_email_style_section',
			'type'     => 'range',
			'default'  => $this->get_option_val( 'cev_widget_content_padding_style' ),
			'show'     => true,
			'option_name' => 'cev_widget_content_padding_style',
			'option_type' => 'key',
			'min' => '10',
			'max' => '100',
			'unit'=> 'px',
			'refresh'   => true,
		);
	
		//email content background color
		$settings['cev_verification_content_background_color'] = array(
			'title'    => esc_html__( 'Background Color', 'customer-email-verification' ),
			'parent'	=> 'cev_email_style_section',
			'type'     => 'color',
			'default'  => $this->get_option_val( 'cev_verification_content_background_color' ),
			'show'     => true,
			'option_name' => 'cev_verification_content_background_color',
			'option_type' => 'key',
			'refresh'   => true,
		);
	
		//email content Border Color 
		$settings['cev_verification_content_border_color'] = array(
			'title'    => esc_html__( 'Border Color', 'customer-email-verification' ),
			'parent'	=> 'cev_email_style_section',
			'type'     => 'color',
			'default'  => $this->get_option_val( 'cev_verification_content_border_color' ),
			'show'     => true,
			'option_name' => 'cev_verification_content_border_color',
			'option_type' => 'key',
			'refresh'   => true,
		);
	
		//email content Font Color 
		$settings['cev_verification_content_font_color'] = array(
			'title'    => esc_html__( 'Font Color', 'customer-email-verification' ),
			'parent'	=> 'cev_email_style_section',
			'type'     => 'color',
			'default'  => $this->get_option_val( 'cev_verification_content_font_color' ),
			'show'     => true,
			'option_name' => 'cev_verification_content_font_color',
			'option_type' => 'key',
			'refresh'   => true,
		);
	
		//content Header
		$settings['cev_verification_widget_style_content_Header'] = array(
			'title'    => esc_html__( 'Widget Header', 'customer-email-verification' ),
			'parent'    => 'cev_email_style_section',
			'type'     => 'title',
			'show'     => true,                
		);
	
		//Display email image/thumbnail
		$settings['cev_verification_image_content'] = array(
			'title'    => esc_html__( 'Header image', 'customer-email-verification' ),
			'parent'	=> 'cev_email_style_section',
			'type'     => 'media',
			'show'     => true,
			'option_type' => 'key',
			'desc'     => '',
			'option_name' => 'cev_verification_image_content',
			'default'	=> $this->get_option_val( 'cev_verification_image_content' ),
			'refresh'   => true,
		);
	
		//email content image  width
		$settings['cev_email_content_widget_header_image_width'] = array(
			'title'    => esc_html__( 'Image Width', 'customer-email-verification' ),
			'parent'	=> 'cev_email_style_section',
			'type'     => 'range',
			'default'  => $this->get_option_val( 'cev_email_content_widget_header_image_width' ),
			'show'     => true,
			'option_name' => 'cev_email_content_widget_header_image_width',
			'option_type' => 'key',
			'min' => '50',
			'max' => '300',
			'unit'=> 'px',
			'refresh'   => true,
		);
	
		//Header content font size
		$settings['cev_header_content_font_size'] = array(
			'title'    => esc_html__( 'Header font size', 'customer-email-verification' ),
			'parent'	=> 'cev_email_style_section',
			'type'     => 'select',
			'default'  => $this->get_option_val( 'cev_header_content_font_size' ),
			'options'  => $font_size_array_cev,
			'show'     => true,
			'option_name' => 'cev_header_content_font_size',
			'option_type' => 'key',
			'refresh'   => true,
		);
	
		return $settings;
	}

	public function cev_customizer_email_content_options( $settings, $preview ) {
		
		//verification selection
		$settings['email_type'] = array(
			'title'    => esc_html__( 'Email Type', 'customer-email-verification' ),
			'parent'   => 'cev_email_content',
			'type'     => 'select',
			'default'  => $this->get_option_val( 'cev_content_widget_type' ),
			'options'  => array(
							'email_registration' => __( 'Registration', 'customer-email-verification' ),
							'email_checkout' => __( 'Checkout', 'customer-email-verification' ),
							'email_login_otp' => __( 'New Login OTP', 'customer-email-verification' ),
							'email_login_auth' => __( 'New Login Authentication', 'customer-email-verification' ),			
						),
			'show'     => true,
			'option_name' => 'email_type',
			'option_type' => 'key',
			'previewType' => true,
		);
	
		//Email Subject reg
		$settings['cev_verification_email_subject'] = array(
			'title'    => esc_html__( 'Email Subject', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_email_subject' ),
			'placeholder' => esc_html__( 'Email Subject', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_verification_email_subject',
			'option_type' => 'key',
			'class'		=> 'email_registration_sub_menu all_status_submenu',
		);
	
		//Email Subject reg
		$settings['cev_verification_email_heading'] = array(
			'title'    => esc_html__( 'Email Heading', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_email_heading' ),
			'placeholder' => esc_html__( 'Email Heading', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_verification_email_heading',
			'option_type' => 'key',
			'class'		=> 'email_registration_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Email content reg
		$settings['cev_verification_email_body'] = array(
			'title'    => esc_html__( 'Verification Message', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_email_body' ),
			'placeholder' => esc_html__( 'Verification Message', 'customer-email-verification' ),
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_verification_email_body',
			'option_type' => 'key',
			'class'		=> 'email_registration_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Email Subject che
		$settings['cev_verification_email_subject_che'] = array(
			'title'    => esc_html__( 'Email Subject', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_email_subject_che' ),
			'placeholder' => esc_html__( 'Email Subject', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_verification_email_subject_che',
			'option_type' => 'key',
			'class'		=> 'email_checkout_sub_menu all_status_submenu',
		);
	
		//Email Heading che
		$settings['cev_verification_email_heading_che'] = array(
			'title'    => esc_html__( 'Email Heading', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_email_heading_che' ),
			'placeholder' => esc_html__( 'Email Heading', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_verification_email_heading_che',
			'option_type' => 'key',
			'class'		=> 'email_checkout_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Email content reg
		$settings['cev_verification_email_body_che'] = array(
			'title'    => esc_html__( 'Verification Message', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_email_body_che' ),
			'placeholder' => esc_html__( 'Verification Message', 'customer-email-verification' ),
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_verification_email_body_che',
			'option_type' => 'key',
			'class'		=> 'email_checkout_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Email Subject che
		$settings['cev_verification_login_otp_email_subject'] = array(
			'title'    => esc_html__( 'Email Subject', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_login_otp_email_subject' ),
			'placeholder' => esc_html__( 'Email Subject', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_verification_login_otp_email_subject',
			'option_type' => 'key',
			'class'		=> 'email_login_otp_sub_menu all_status_submenu',
		);
	
		//Email Heading che
		$settings['cev_verification_login_otp_email_heading'] = array(
			'title'    => esc_html__( 'Email Heading', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_login_otp_email_heading' ),
			'placeholder' => esc_html__( 'Email Heading', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_verification_login_otp_email_heading',
			'option_type' => 'key',
			'class'		=> 'email_login_otp_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Email content reg
		$settings['cev_verification_login_otp_email_content'] = array(
			'title'    => esc_html__( 'Email Content', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_login_otp_email_content' ),
			'placeholder' => esc_html__( 'Email Content', 'customer-email-verification' ),
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_verification_login_otp_email_content',
			'option_type' => 'key',
			'class'		=> 'email_login_otp_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Email Subject che
		$settings['cev_verification_login_auth_email_subject'] = array(
			'title'    => esc_html__( 'Email Subject', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_login_auth_email_subject' ),
			'placeholder' => esc_html__( 'Email Subject', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_verification_login_auth_email_subject',
			'option_type' => 'key',
			'class'		=> 'email_login_auth_sub_menu all_status_submenu',
		);
	
		//Email Heading che
		$settings['cev_verification_login_auth_email_heading'] = array(
			'title'    => esc_html__( 'Email Heading', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_login_auth_email_heading' ),
			'placeholder' => esc_html__( 'Email Heading', 'customer-email-verification' ),
			'type'     => 'text',
			'show'     => true,				
			'option_name' => 'cev_verification_login_auth_email_heading',
			'option_type' => 'key',
			'class'		=> 'email_login_auth_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Email content reg
		$settings['cev_verification_login_auth_email_content'] = array(
			'title'    => esc_html__( 'Email Content', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_verification_login_auth_email_content' ),
			'placeholder' => esc_html__( 'Email Content', 'customer-email-verification' ),
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_verification_login_auth_email_content',
			'option_type' => 'key',
			'class'		=> 'email_login_auth_sub_menu all_status_submenu',
			'refresh'   => true,
		);
	
		//Available variables
		$settings['cev_email_code_block'] = array(
			'title'    => esc_html__( 'Available Variables', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => '<code>You can use HTML tags : &lt;a&gt;, &lt;strong&gt;, &lt;i&gt;	 and placeholders:{site_title}<br>{cev_user_verification_pin}<br>{cev_display_name}<br>{cev_change_pw_btn}<br>{login_browser}<br>{login_device}<br>{login_time}<br>{login_ip}</code>','You can use HTML tag : <strong>, <i>',				
			'type'     => 'codeinfo',
			'show'     => true,				
		);
	
		//Footer content
		$settings['cev_verification_footer_content'] = array(
			'title'    => esc_html__( 'Footer Content', 'customer-email-verification' ),
			'parent'    => 'cev_email_content',
			'type'     => 'title',
			'show'     => true,                
		);
	
		//Addition footer content reg
		$settings['cev_new_verification_Footer_content'] = array(
			'title'    => esc_html__( 'Addition Footer Content', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_new_verification_Footer_content' ),
			'placeholder' => '',
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_new_verification_Footer_content',
			'option_type' => 'key',
			'refresh'   => true,
			'class'		=> 'email_registration_sub_menu all_status_submenu',
		);
	
		//Addition footer content reg
		$settings['cev_new_verification_Footer_content_che'] = array(
			'title'    => esc_html__( 'Addition Footer Content', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_new_verification_Footer_content_che' ),
			'placeholder' => '',
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_new_verification_Footer_content_che',
			'option_type' => 'key',
			'refresh'   => true,
			'class'		=> 'email_checkout_sub_menu all_status_submenu',
		);
	
		//Addition footer content reg
		$settings['cev_login_otp_footer_content'] = array(
			'title'    => esc_html__( 'Addition Footer Content', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_login_otp_footer_content' ),
			'placeholder' => '',
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_login_otp_footer_content',
			'option_type' => 'key',
			'refresh'   => true,
			'class'		=> 'email_login_otp_sub_menu all_status_submenu',
		);
	
		//Addition footer content reg
		$settings['cev_login_auth_footer_content'] = array(
			'title'    => esc_html__( 'Addition Footer Content', 'customer-email-verification' ),
			'parent'	=> 'cev_email_content',
			'default'  => $this->get_option_val( 'cev_login_auth_footer_content' ),
			'placeholder' => '',
			'type'     => 'textarea',
			'show'     => true,				
			'option_name' => 'cev_login_auth_footer_content',
			'option_type' => 'key',
			'refresh'   => true,
			'class'		=> 'email_login_auth_sub_menu all_status_submenu',
		);
	
		return $settings;
	}

	public function get_option_val( $key ) {

		$value = get_option( $key, $this->defaults[ $key ] );
		if ( '' == $value ) {
			$value = $this->defaults[ $key ];
		}
		return $value;      
	}
}
