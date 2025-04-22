<?php
/**
 * Plugin Name:	Customer Email Verification
 * Plugin URI:	http://woocommerce.com/products/customer-email-verification/
 * Description: The Customer Email Verification helps WooCommerce store owners to reduce registration and orders spam by requiring customers to verify their email address when they register an account or before they can checkout on your store.
 * Version: 2.7.5
 * Author: zorem
 * Author URI: https://www.zorem.com
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Text Domain: customer-email-verification
 * Domain Path: /lang/
 * Requires Plugins: woocommerce
 *
 * WC requires at least: 4.8
 * WC tested up to: 9.7.1
 * Woo: 8105872:4b9f6cbe025271aa7ae3c09e808651bf
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package zorem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Customer_Email_Verification_Pro {

	/**
	* Customer Email Verification Pro
	*
	* @var string
	*/
	public $version = '2.7.5';
	public $plugin_file;
	public $plugin_path;
	public $my_account;
	public $admin;
	public $preview;
	public $WC_customer_email_verification_email_Common;
	public $cev_email_settings;
	public $cev_pro_tools_tab;
	public $cev_pro_guest_user;
	public $cev_pro_login_authentication;
	public $email;
	public $email_login;
	public $customizer;
	public $customizer_options;
	public $signup;
	public $install;
	
	/**
	* Initialize the main plugin function
	*/
	public function __construct() {
		
		$this->plugin_file = __FILE__;
		
		// Add your templates to this array.
		if ( !defined( 'CUSTOMER_EMAIL_VERIFICATION_PATH' ) ) {
			define( 'CUSTOMER_EMAIL_VERIFICATION_PATH', $this->get_plugin_path() );
		}
		$this->my_account = get_option( 'woocommerce_myaccount_page_id' );
		
		if ( '' === $this->my_account ) {
			$this->my_account = get_option( 'page_on_front' );
		}
		
		if ( $this->is_wc_active() ) {
			//start adding hooks
			$this->includes();
			
			$this->init();
			
			$this->admin->init();
			
			$this->email->init();
			
			$this->preview->init();
			
		}
		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ) );
		// Load plugin textdomain
		add_action('init', array($this, 'load_pro_textdomain'));
	}
	
	/**
	* Check if WC is active
	*
	* @since  1.0.0
	*
	* @return bool
	*/
	private function is_wc_active() {
		
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$is_active = true;
		} else {
			$is_active = false;
		}

		// Do the WC active check
		if ( false === $is_active ) {
			
			add_action( 'admin_notices', array( $this, 'notice_activate_wc' ) );
		}		
		return $is_active;
	}
	
	/**
	* Display WC active notice
	*
	* @since  1.0.0
	*/
	public function notice_activate_wc() {
		?>
		<div class="error">
			<p>
			<?php
			/* translators: %s: search Customer Email Verification PRO plugin link */
			printf( esc_html__( 'Please install and activate %1$sWooCommerce%2$s plugin for the Customer Email Verification PRO to work.', 'customer-email-verification' ), '<a href="' . esc_url( admin_url( 'plugin-install.php?tab=search&s=WooCommerce&plugin-search-input=Search+Plugins' ) ) . '">', '</a>' ); 
			?>
			</p>
		</div>
		<?php
	}
	
	/**
	* Include plugin file.
	*
	* @since 1.0.0
	*/	
	public function includes() {

		require_once $this->get_plugin_path() . '/includes/cev-pro-installation.php';
		$this->install = CEV_Installation::get_instance(__FILE__);

		require_once $this->get_plugin_path() . '/includes/cev-pro-signup-verification.php';
		$this->signup = CEV_Signup_Verification::get_instance();

		require_once $this->get_plugin_path() . '/includes/class-wc-customer-email-verification-admin.php';
		$this->admin = WC_Customer_Email_Verification_Admin_Pro::get_instance();

		require_once $this->get_plugin_path() . '/includes/class-wc-customer-email-verification-email.php';
		$this->email = WC_Customer_Email_Verification_Email_Pro::get_instance();	
		
		require_once $this->get_plugin_path() . '/includes/class-wc-customer-email-verification-preview-front.php';
		$this->preview = WC_Customer_Email_Verification_Preview_Pro::get_instance();		
		
		require_once $this->get_plugin_path() . '/includes/class-wc-customer-email-verification-email-common.php';
		$this->WC_customer_email_verification_email_Common = WC_Customer_Email_Verification_Email_Common_Pro::get_instance();
		
		require_once $this->get_plugin_path() . '/includes/cev-pro-email-settings.php';
		$this->cev_email_settings = Customer_Email_Verification_Email_Settings::get_instance();
	
		require_once $this->get_plugin_path() . '/includes/cev-pro-tools-settings.php';
		$this->cev_pro_tools_tab = Cev_Pro_Tools_Tab::get_instance();
		
		require_once $this->get_plugin_path() . '/includes/cev-pro-social-login.php';
		$this->cev_pro_guest_user = WC_Customer_Email_Verification_Social_Login::get_instance();

		require_once $this->get_plugin_path() . '/includes/cev-pro-login-authentication.php';
		$this->cev_pro_login_authentication = WC_Customer_Email_Verification_Login_Authentication::get_instance();
		
	}
	
	/*
	* init when class loaded
	*/
	public function init() {
		
		//Custom Woocomerce menu
		add_action('admin_menu', array( $this->admin, 'register_woocommerce_menu' ), 99 );
		
		//load javascript in admin
		add_action( 'admin_enqueue_scripts', array( $this->admin, 'admin_styles' ), 4);
		
		add_filter( 'woocommerce_account_menu_items', array( $this, 'cev_account_menu_items' ), 10, 1 );
		
		add_action( 'init', array( $this, 'cev_add_my_account_endpoint' ) );
		
		add_action( 'woocommerce_account_email-verification_endpoint', array( $this, 'cev_email_verification_endpoint_content' ) );
		add_action( 'woocommerce_account_login-authentication_endpoint', array( $this->cev_pro_login_authentication, 'cev_login_authentication_endpoint_content' ) );
		
		add_filter( 'woocommerce_account_menu_items', array( $this, 'hide_cev_menu_my_account' ), 999 );
		
		add_filter( 'wcml_register_endpoints_query_vars', array( $this, 'register_endpoint_WPML' ), 10, 3 );
		
		add_filter( 'wcml_endpoint_permalink_filter', array( $this, 'endpoint_permalink_filter' ), 10, 2 ) ;

		add_filter( 'woocommerce_get_query_vars', array( $this, 'add_query_vars' ) );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'cev_enqueue_pro' ) );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'front_styles' ));
		//callback for add action link for plugin page	
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this , 'my_plugin_action_links' ));
	
		 // stanalone plugin to use
		register_activation_hook( __FILE__, array( $this,'on_activation_cev' ) );

		
	}

	/*
	* call on plugin activation
	* 
	* @since 2.4
	*/
	public function on_activation_cev() {
		deactivate_plugins( 'customer-email-verification-for-woocommerce/customer-email-verification-for-woocommerce.php' );
		deactivate_plugins( 'customer-email-verification-pro/customer-email-verification-pro.php' );
		set_transient( 'free_cev_plugin', 'notice', 3 );
		update_option( 'cev_pro_plugin_notice_ignore', 'true' );
	}
	
	/*
	* Add admin javascript
	*/	
	public function cev_enqueue_pro() {
		
		// Add condition for css & js include for admin page  
		if ( !isset( $_GET['page'] ) ) {
				return;
		}
		if ( 'customer-email-verification-for-woocommerce' != $_GET['page'] ) {
			return;
		}
			
		// Add tiptip js and css file		
		wp_enqueue_style( 'cev-pro-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', array(), time() );		
		wp_enqueue_script( 'cev_pro_admin_js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery', 'wp-util'), time(), true );			
		wp_localize_script( 'cev_pro_admin_js', 'cev_pro_admin_js', array() );
		// Your custom js file
		wp_enqueue_script( 'media-lib-uploader-js' );
		//Core media script
		wp_enqueue_media();
	}
	
	/**
	* Include front js and css
	*/
	public function front_styles() {	
		
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		
		wp_register_script( 'cev-underscore-js', cev_pro()->plugin_dir_url() . 'assets/js/underscore-min.js', array( 'jquery' ), time() );		
		wp_register_script( 'cev-front-js', cev_pro()->plugin_dir_url() . 'assets/js/front.js', array( 'jquery' ), time() );
		wp_register_script( 'cev-inline-front-js', cev_pro()->plugin_dir_url() . 'assets/js/inline-verification.js', array( 'jquery' ), time() );
		
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
				'wc_cev_email_guest_user' => wp_create_nonce( 'wc_cev_email_guest_user' ),
				'cev_verification_checkout_dropdown_option' => get_option('cev_verification_checkout_dropdown_option'),
				'cev_enable_email_verification_checkout' => get_option('cev_enable_email_verification_checkout'),
			)
		);
		
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
				'email_verified_msg' => __( 'Email address verified', 'customer-email-verification' ),
			)
		);
		
		wp_register_style( 'cev_front_style', cev_pro()->plugin_dir_url() . 'assets/css/front.css', array(), cev_pro()->version );
		wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI' . $suffix . '.js', array( 'jquery' ), '2.70', true );	
		
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
			
			$cev_enable_email_verification_checkout = get_option( 'cev_enable_email_verification_checkout', 1 );
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
	
	/*
	* load text domain
	*/
	public function load_pro_textdomain() {
		load_plugin_textdomain( 'customer-email-verification', false, plugin_dir_path( plugin_basename(__FILE__) ) . 'lang/' );
	}

	/*
	* include file on plugin load
	*/
	public function on_plugins_loaded() {
		
		add_filter( 'wp_kses_allowed_html', array( cev_pro()->WC_customer_email_verification_email_Common, 'my_allowed_tags' ) );
		add_filter( 'safe_style_css', array( cev_pro()->WC_customer_email_verification_email_Common, 'safe_style_css_callback' ), 10, 1 );
		
		// customizer
		require_once $this->get_plugin_path() . '/includes/customizer/cev-customizer.php';
		$this->customizer = CEV_Customizer::get_instance();

		// customizer
		require_once $this->get_plugin_path() . '/includes/customizer/cev-customizer-options.php';
		$this->customizer_options = CEV_Customizer_Options::get_instance();
			
	}
	
	/**
	* Gets the absolute plugin path without a trailing slash, e.g.
	* /path/to/wp-content/plugins/plugin-directory.
	*
	* @return string plugin path
	*/
	public function get_plugin_path() {
		if ( isset( $this->plugin_path ) ) {
			return $this->plugin_path;
		}

		$this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );

		return $this->plugin_path;
	}
	
	/**
	* Gets the absolute plugin url.
	*/	
	public function plugin_dir_url() {
		return plugin_dir_url( __FILE__ );
	}		
	
	/**
	* Account menu items
	*
	* @param arr $items
	*
	* @return arr
	*/
	public function cev_account_menu_items( $items ) {
		$items['email-verification'] = __( 'Sign up email verification', 'customer-email-verification' );
		$items['login-authentication'] = __( 'Login Authentication', 'customer-email-verification' );
		return $items;
	}
	
	/**
	* Hide menu account
	*/			
	public function hide_cev_menu_my_account( $items ) {
		unset($items['email-verification']);
		unset($items['login-authentication']);
		return $items;
	}
	
	/**
	* Add endpoint
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
	
	public function register_endpoint_WPML( $query_vars, $wc_vars, $obj ) {
		$query_vars['email-verification'] = $obj->get_endpoint_translation('email-verification', isset( $wc_vars['email-verification']) ? $wc_vars['email-verification'] : 'email-verification' );
		$query_vars['login-authentication'] = $obj->get_endpoint_translation('login-authentication', isset( $wc_vars['login-authentication']) ? $wc_vars['login-authentication'] : 'login-authentication' );
		return $query_vars;
	}
	
	public function endpoint_permalink_filter( $endpoint, $key ) {
		if ( 'email-verification' == $key ) { 
			return 'email-verification';
		}
		if ( 'login-authentication' == $key ) { 
			return 'login-authentication';
		}
		return $endpoint;
	}

	public function add_query_vars( $query_vars ) {
		$query_vars['email-verification'] = 'email-verification';
		$query_vars['login-authentication'] = 'login-authentication';
		return $query_vars;
	}
	
	
	/**
	* Information content
	*/
	public function cev_email_verification_endpoint_content() {
		
		$current_user = wp_get_current_user();
		$email = $current_user->user_email;				
		$verified  = get_user_meta( get_current_user_id(), 'customer_email_verified', true );
		
		$CEV_Customizer_Options = new CEV_Customizer_Options();
		$cev_verification_overlay_color = get_option('cev_verification_popup_overlay_background_color',
		$CEV_Customizer_Options->defaults['cev_verification_popup_overlay_background_color']);
		
		if ( $this->is_admin_user( get_current_user_id() ) || $this->is_verification_skip_for_user( get_current_user_id() ) ) {
			return;
		}
		
		if ( 'true' === $verified ) {
			return;
		}
		?>
		<style>
		.cev-authorization-grid__visual{
			background: <?php echo wp_kses_post( $this->hex2rgba($cev_verification_overlay_color, '0.7') ); ?>;	
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
		require_once $this->get_plugin_path() . '/includes/views/cev_admin_endpoint_popup_template.php';
	}	
	
	/* Convert hexdec color string to rgb(a) string */
	public function hex2rgba ( $color, $opacity = false ) {
	
		$default = 'rgba(116,194,225,0.7)';
	
		//Return default if no color provided
		if ( empty( $color ) ) {
			return $default; 
		}
		//Sanitize $color if "#" is provided 
		if ( '#' == $color[0] ) {
			$color = substr( $color, 1 );
		}
	
		//Check if color has 6 or 3 characters and get values
		if (strlen($color) == 6) {
			$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) == 3 ) {
			$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
			return $default;
		}
	
		//Convert hexadec to rgb
		$rgb =  array_map('hexdec', $hex);
	
		//Check if opacity is set(rgba or rgb)
		if ( $opacity ) {
			if ( abs($opacity) > 1 ) {
				$opacity = 1.0;
			}	
			$output = 'rgba(' . implode(',', $rgb) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode(',' , $rgb) . ')';
		}
	
		//Return rgb(a) color string
		return $output;
	}
	
	/**
	* Add plugin action links.
	*
	* Add a link to the settings page on the plugins.php page.
	*
	* @since 1.0.0
	*
	* @param  array  $links List of existing plugin action links.
	* @return array         List of modified plugin action links.
	*/
	public function my_plugin_action_links( $links ) {
		$links = array_merge( array(
			'<a href="' . esc_url( admin_url( '/admin.php?page=customer-email-verification-for-woocommerce' ) ) . '">' . esc_html__( 'Settings', 'woocommerce' ) . '</a>'
		), $links );
		
		return $links;
	}
	
	/**
	* Check if user is administrator
	*
	* @param int $user_id
	*
	* @return bool
	*/
	public function is_admin_user( $user_id ) {
		
		$user = get_user_by( 'id', $user_id );
		if ( !$user ) {
			return false;
		}
		$roles = $user->roles;
		
		if ( in_array( 'administrator', (array) $roles ) ) {
			return true;	
		}
		return false;
	}
	
	public function is_verification_skip_for_user( $user_id ) {
		
		$user = get_user_by( 'id', $user_id );
		if ( !$user ) {
			return false;
		}
		$roles = $user->roles;
		$cev_skip_verification_for_selected_roles = get_option('cev_skip_verification_for_selected_roles');		
		
		foreach ( ( array ) $cev_skip_verification_for_selected_roles as $role => $val ) {
			if ( in_array( $role, (array) $roles ) && 1 == $val ) {
				return true;
			}
		}
		return false;
	}
	
}

/**
* Returns an instance of customer_email_verification_pro.
*
* @since 1.0.0
* @version 1.0.0
*
* @return customer_email_verification_pro.
*/
function cev_pro() {
	static $instance;

	if ( ! isset( $instance ) ) {		
		$instance = new Customer_Email_Verification_Pro();
	}

	return $instance;
}

/**
* Register this class globally.
*
* Backward compatibility.
*/
cev_pro();
add_action( 'before_woocommerce_init', function() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
} );
