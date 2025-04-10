<?php
/**
 * Woocommerce-direct-checkout-pro
 *
 * @package  WooCommerce Direct Checkout PRO
 * @since    1.0.0
 */

namespace QuadLayers\WCDC_PRO\View\Frontend;

use QuadLayers\WCDC\Plugin as Free_Plugin;
use QuadLayers\WCDC_PRO\Plugin;

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
		add_filter( 'body_class', array( $this, 'add_class' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), -20 );
		add_action( 'wp_ajax_qlwcdc_add_to_cart_action', array( $this, 'add_to_cart_action' ) );
		add_action( 'wp_ajax_nopriv_qlwcdc_add_to_cart_action', array( $this, 'add_to_cart_action' ) );
		add_action( 'wp_ajax_qlwcdc_add_product_cart_ajax_message', array( $this, 'add_product_cart_ajax_message' ) );
		add_action( 'wp_ajax_nopriv_qlwcdc_add_product_cart_ajax_message', array( $this, 'add_product_cart_ajax_message' ) );
		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'add_to_cart_redirect' ), 10 );
		add_action( 'woocommerce_after_add_to_cart_button', array( $this, 'add_quick_purchase_button' ), -5 );
		add_action( 'woocommerce_before_single_product_summary', array( $this, 'add_product_default_attributes' ) );
		add_action( 'wp_loaded', array( $this, 'remove_redirect_url' ) );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'subscription_switch' ), 5, 3 );
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

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {

		global $post;
		if ( did_action( 'before_woocommerce_init' ) ) {
			if ( function_exists( 'is_product' ) && is_product() ) {
				if ( 'yes' === Free_Plugin::instance()->get_product_option( $post->ID, 'qlwcdc_add_product_ajax', 'no' ) ) {

					Plugin::instance()->register_scripts();

					// wp_enqueue_script('wc-add-to-cart');.

					// wp_enqueue_script('wc-add-to-cart-variation');.
				}
			}
		}
	}

	/**
	 * Remove redirect url
	 */
	public function remove_redirect_url() {

		if ( isset( $_REQUEST['action'] ) && ( 'qlwcdc_add_to_cart_action' == $_REQUEST['action'] ) ) {
			add_filter( 'woocommerce_add_to_cart_redirect', '__return_false' );
		}

		if ( isset( $_REQUEST['action'] ) && ( 'qlwcdc_add_product_cart_ajax_message' == $_REQUEST['action'] ) ) {
			remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );
		}
	}

	/**
	 * Add to cart action
	 */
	public function add_to_cart_action() {
		if ( isset( $_REQUEST['add-to-cart'] ) && 'yes' != Free_Plugin::instance()->get_product_option( wc_clean( wp_unslash( $_REQUEST['add-to-cart'] ) ), 'qlwcdc_add_product_ajax_alert', 'no' ) ) {
			wc_clear_notices();
		}
		\WC_AJAX::get_refreshed_fragments();
	}

	/**
	 * Add class
	 *
	 * @param array $classes Located.
	 */
	public function add_class( $classes ) {

		global $post;

		if ( function_exists( 'is_product' ) && is_product() ) {
			if ( 'no' != Free_Plugin::instance()->get_product_option( $post->ID, 'qlwcdc_add_product_ajax', 'no' ) ) {
				$classes[] = 'qlwcdc-product-ajax';
			}
			if ( 'no' != Free_Plugin::instance()->get_product_option( $post->ID, 'qlwcdc_add_product_ajax_alert', 'no' ) ) {
				$classes[] = 'qlwcdc-product-ajax-alert';
			}
		}
		return $classes;
	}

	/**
	 * Add product cart ajax message
	 */
	public function add_product_cart_ajax_message() {

		global $wp_query;

		if ( ! isset( $_REQUEST['add-to-cart'] ) ) {
			return;
		}

		/**
		 * Apply filters: woocommerce_add_to_cart_product_id.
		 *
		 * @since 1.0.0
		 */
		$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );
		$product_id = wp_get_post_parent_id( $product_id ) ? wp_get_post_parent_id( $product_id ) : $product_id;

		$args = array(
			'p'         => $product_id,
			'post_type' => 'any',
		);

		$wp_query = new \WP_Query( $args );

		ob_start();

		woocommerce_output_all_notices();

		$data = ob_get_clean();

		wp_send_json_success( $data );
	}

	/**
	 * Get quick purchase link
	 *
	 * @param int $product_id Product id.
	 */
	public function get_quick_purchase_link( $product_id = 0 ) {

		if ( 'checkout' === Free_Plugin::instance()->get_product_option( $product_id, 'qlwcdc_add_product_quick_purchase_to', 'checkout' ) ) {
			return wc_get_checkout_url();
		}

		return wc_get_cart_url();
	}

	/**
	 * Add to cart redirect
	 *
	 * @param string $url URL.
	 */
	public function add_to_cart_redirect( $url ) {

		if ( isset( $_GET['add-to-cart'], $_SERVER['REQUEST_URI'] ) && absint( $_GET['add-to-cart'] ) > 0 && strpos( home_url( wc_clean( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), $this->get_quick_purchase_link() ) !== false ) {
			return false;
		}

		return $url;
	}

	/**
	 * Add quick purchase button
	 */
	public function add_quick_purchase_button() {

		global $product;

		static $instance = 0;

		if ( ! $instance && 'yes' === Free_Plugin::instance()->get_product_option( $product->get_id(), 'qlwcdc_add_product_quick_purchase', 'no' ) && $product->get_type() != 'external' ) {
			?>
				<button type="submit" class="single_add_to_cart_button button qlwcdc_quick_purchase <?php echo esc_attr( Free_Plugin::instance()->get_product_option( $product->get_id(), 'qlwcdc_add_product_quick_purchase_type' ) ); ?> <?php echo esc_attr( Free_Plugin::instance()->get_product_option( $product->get_id(), 'qlwcdc_add_product_quick_purchase_class' ) ); ?>" data-href="<?php echo esc_url( $this->get_quick_purchase_link( $product->get_id() ) ); ?>"><?php esc_html_e( Free_Plugin::instance()->get_product_option( $product->get_id(), 'qlwcdc_add_product_quick_purchase_text', esc_html__( 'Purchase Now', 'woocommerce-direct-checkout-pro' ) ) ); // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText ?></button>
			<?php
			++$instance;
		}
	}

	/**
	 * Add product default attributes
	 */
	public function add_product_default_attributes() {

		global $product;

		if ( 'yes' !== get_option( 'qlwcdc_add_product_default_attributes' ) ) {
			return;
		}

		if ( ! method_exists( $product, 'get_default_attributes' ) ) {
			return;
		}

		if ( ! method_exists( $product, 'get_variation_attributes' ) ) {
			return;
		}

		$default_product_attributes = (array) $product->get_default_attributes();

		$product_attributes = (array) $product->get_variation_attributes();

		if ( count( array_keys( $default_product_attributes ) ) === count( array_keys( $product_attributes ) ) ) {
			return;
		}

		$new_default_attributes = array();

		if ( empty( $product_attributes ) ) {
			return;
		}

		foreach ( $product_attributes as $attribute_key => $attribute_options ) {

			$first_attribute_name = isset( array_values( $attribute_options )[0] ) ? array_values( $attribute_options )[0] : null;

			if ( ! $first_attribute_name ) {
				continue;
			}

			$new_default_attributes[ $attribute_key ] = $first_attribute_name;
		}

		if ( empty( $new_default_attributes ) ) {
			return;
		}

		update_post_meta( $product->get_id(), '_default_attributes', $new_default_attributes );
	}

	public function subscription_switch( $cart_item_data, $product_id, $variation_id ) {

		if ( isset( $_REQUEST['switch-subscription'] ) ) {
				$_GET['switch-subscription'] = $_REQUEST['switch-subscription'];
		}

		if ( isset( $_REQUEST['item'] ) ) {
			$_GET['item'] = $_REQUEST['item'];
		}

		return $cart_item_data;
	}
}
