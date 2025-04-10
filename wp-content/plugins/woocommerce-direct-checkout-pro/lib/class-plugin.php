<?php
/**
 * Woocommerce-direct-checkout-pro
 *
 * @package  WooCommerce Direct Checkout PRO
 * @since    1.0.0
 */

namespace QuadLayers\WCDC_PRO;

use QuadLayers\WCDC_PRO\Controllers\Archives;
use QuadLayers\WCDC_PRO\Controllers\Checkout;
use QuadLayers\WCDC_PRO\Controllers\General;
use QuadLayers\WCDC_PRO\Controllers\License;
use QuadLayers\WCDC_PRO\Controllers\Products;

/**
 * Plugin Main class
 *
 * @class Plugin
 * @version 1.0.0
 */
final class Plugin {

	/**
	 * The single instance of the class.
	 *
	 * @var WCDC_PRO
	 */
	protected static $instance;

	/**
	 * Construct
	 */
	private function __construct() {

		/**
		 * Load plugin textdomain.
		 */
		add_action( 'init', array( $this, 'load_textdomain' ) );

		add_action(
			'wcdc_init',
			function () {
				/**
				 * Remove premium CSS
				 */
				remove_action( 'admin_footer', array( 'QuadLayers\\WCDC\\Plugin', 'add_premium_css' ) );
				/**
				 * Remove premium menu
				 */
				remove_action( 'qlwcdc_sections_header', array( 'QuadLayers\\WCDC\\Controller\\Premium', 'add_header' ) );
				remove_action( 'admin_menu', array( 'QuadLayers\\WCDC\\Controller\\Premium', 'add_menu' ) );
				/**
				 * Load classes
				 */
				new General();
				new Archives();
				new Products();
				new Checkout();
				new License();
			}
		);
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-direct-checkout-pro', false, QLWCDC_PRO_PLUGIN_DIR . '/languages/' );
	}

	/**
	 * Register scripts
	 */
	public function register_scripts() {
		wp_register_style( 'qlwcdc-pro', plugins_url( '/assets/frontend/qlwcdc-pro' . self::instance()->is_min() . '.css', QLWCDC_PRO_PLUGIN_FILE ), array(), QLWCDC_PRO_PLUGIN_VERSION, 'all' );

		wp_register_script( 'qlwcdc-pro', plugins_url( '/assets/frontend/qlwcdc-pro' . self::instance()->is_min() . '.js', QLWCDC_PRO_PLUGIN_FILE ), array( 'jquery', 'wc-add-to-cart-variation' ), QLWCDC_PRO_PLUGIN_VERSION, false );

		wp_localize_script(
			'qlwcdc-pro',
			'qlwcdc',
			array(
				'nonce'   => wp_create_nonce( 'qlwcdc' ),
				'delay'   => 200,
				'timeout' => null,
			)
		);
	}

	/**
	 * Is min
	 */
	public function is_min() {
		if ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) {
			return '.min';
		}
	}

	/**
	 * Instance
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

add_action(
	'plugins_loaded',
	function () {
		Plugin::instance();
	}
);
