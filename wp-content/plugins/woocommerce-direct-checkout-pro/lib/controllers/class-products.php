<?php
/**
 * Woocommerce-direct-checkout-pro
 *
 * @package  WooCommerce Direct Checkout PRO
 * @since    1.0.0
 */

namespace QuadLayers\WCDC_PRO\Controllers;

use QuadLayers\WCDC\Controller\Products as Free_Products;
use QuadLayers\WCDC_PRO\View\Frontend\Products as Frontend_Products;

/**
 * Products
 *
 * @class Products
 * @version 1.0.0
 */
class Products {

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

		new Frontend_Products();

		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_options' ) );
	}


	/**
	 * Construct
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Save product options
	 *
	 * @param int $product_id Tabs.
	 */
	public function save_product_options( $product_id ) {

		if ( isset( $_POST['woocommerce_meta_nonce'] ) || wp_verify_nonce( wc_clean( wp_unslash( $_POST['woocommerce_meta_nonce'] ) ), 'woocommerce_save_data' ) ) {

			Free_Products::instance()->add_product_fields();

			if ( wc_get_product( $product_id ) ) {

				$product = wc_get_product( $product_id );

				$product_fields = Free_Products::instance()->product_fields;

				if ( $product_fields && is_array( $product_fields ) ) {

					foreach ( $product_fields as $field ) {

						if ( isset( $field['id'] ) && isset( $_POST[ $field['id'] ] ) ) {

							$value = esc_attr( trim( stripslashes( wc_clean( wp_unslash( $_POST[ $field['id'] ] ) ) ) ) );

							if ( get_option( $field['id'], true ) != $value ) {
								$product->update_meta_data( $field['id'], $value );
							} else {
								$product->delete_meta_data( $field['id'] );
							}
						}
					}
				}

				$product->save();
			}
		}
	}
}
