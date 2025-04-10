<?php
/**
 * Woocommerce-direct-checkout-pro
 *
 * @package  WooCommerce Direct Checkout PRO
 * @since    1.0.0
 */

namespace QuadLayers\WCDC_PRO\Controllers;

/**
 * License
 *
 * @class License
 * @version 1.0.0
 */
class License {

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
		add_action( 'qlwcdc_sections_header', array( $this, 'add_header' ) );
	}

	/**
	 * Add header
	 */
	public function add_header() {
		global $current_section, $qlwcdc_license_client;

		if ( ! isset( $qlwcdc_license_client->plugin ) ) {
			return;
		}

		$license_menu_url = $qlwcdc_license_client->plugin->get_menu_license_url();

		?>
			<li><a href="<?php echo esc_url( $license_menu_url ); ?>" class="<?php echo ( 'license' == $current_section ? 'current' : '' ); ?>"><?php esc_html_e( 'License', 'woocommerce-direct-checkout-pro' ); ?></a> | </li>
		<?php
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
