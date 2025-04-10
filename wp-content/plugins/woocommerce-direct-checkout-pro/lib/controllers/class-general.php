<?php
/**
 * Woocommerce-direct-checkout-pro
 *
 * @package  WooCommerce Direct Checkout PRO
 * @since    1.0.0
 */

namespace QuadLayers\WCDC_PRO\Controllers;

use QuadLayers\WCDC_PRO\View\Frontend\General as Frontend_General;

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

		new Frontend_General();
	}

	/**
	 * Instance
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
