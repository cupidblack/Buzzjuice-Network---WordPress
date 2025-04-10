<?php
/**
 * Woocommerce-direct-checkout-pro
 *
 * @package  WooCommerce Direct Checkout PRO
 * @since    1.0.0
 */

namespace QuadLayers\WCDC_PRO\Controllers;

use QuadLayers\WCDC_PRO\View\Frontend\Archives as Frontend_Archives;

/**
 * Archives
 *
 * @class Archives
 * @version 1.0.0
 */
class Archives {

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

		new Frontend_Archives();
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
