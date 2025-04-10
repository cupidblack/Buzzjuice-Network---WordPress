<?php
/**
 * Woocommerce-direct-checkout-pro
 *
 * @package  WooCommerce Direct Checkout PRO
 * @since    1.0.0
 */

namespace QuadLayers\WCDC_PRO\View\Frontend;

use QuadLayers\WCDC_PRO\Plugin;

/**
 * General
 *
 * @class General
 * @version 1.0.0
 */
class General {

	/**
	 * The single instance of the class.
	 *
	 * @var WCDC_PRO
	 */
	protected static $instance;

	/**
	 * Construct
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), -10 );
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

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		if ( did_action( 'before_woocommerce_init' ) ) {
			Plugin::instance()->register_scripts();

			wp_enqueue_style( 'qlwcdc-pro' );

			wp_enqueue_script( 'qlwcdc-pro' );
		}
	}
}
