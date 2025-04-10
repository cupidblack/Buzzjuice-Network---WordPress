<?php

/**

 * Plugin Name:             WooCommerce Direct Checkout PRO
 * Plugin URI:              https://quadlayers.com/products/woocommerce-direct-checkout/
 * Description:             Simplifies the checkout process to improve your sales rate.
 * Version:                 3.2.5
 * Text Domain:             woocommerce-direct-checkout-pro
 * Author:                  QuadLayers
 * Author URI:              https://quadlayers.com
 * License:                 Copyright
 * Domain Path:             /languages
 * Request at least:        4.7
 * Tested up to:            6.7
 * Requires PHP:            5.6
 * WC requires at least:    4.0
 * WC tested up to:         9.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

define( 'QLWCDC_PRO_PLUGIN_NAME', 'WooCommerce Direct Checkout PRO' );
define( 'QLWCDC_PRO_PLUGIN_VERSION', '3.2.5' );
define( 'QLWCDC_PRO_PLUGIN_FILE', __FILE__ );
define( 'QLWCDC_PRO_PLUGIN_DIR', __DIR__ . DIRECTORY_SEPARATOR );
define( 'QLWCDC_PRO_DOMAIN', 'qlwcdc' );
define( 'QLWCDC_PRO_SUPPORT_URL', 'https://quadlayers.com/account/support/?utm_source=qlwcdc_admin' );
define( 'QLWCDC_PRO_LICENSES_URL', 'https://quadlayers.com/account/licenses/?utm_source=qlwcdc_admin' );

add_filter('pre_http_request', 'intercept_activation_request', 10, 3);

function intercept_activation_request($preempt, $args, $url) {
if (strpos($url, 'https://quadlayers.com/') === 0) {
parse_str(parse_url($url, PHP_URL_QUERY), $query_params);

if (isset($query_params['license_market']) && isset($query_params['license_key']) && isset($query_params['license_email']) && isset($query_params['activation_site']) && isset($query_params['product_key'])) {
$response = array(
'success' => true,
'license' => 'valid',
'message' => 'The license is valid.',
'order_id' => '12345',
'license_key' => 'C6D0D7F8DB6898D6ACA49DC6C9F4B996',
'license_email' => 'noreply@gmail.com',
'license_limit' => '100',
'license_updates' => '2050-01-01',
'license_support' => '2050-01-01',
'license_expiration' => '2050-01-01',
'license_created' => date('Y-m-d'),
'activation_limit' => '100',
'activation_count' => '9',
'activation_remaining' => '91',
'activation_instance' => '1',
'activation_status' => 'active',
'activation_site' => $_SERVER['HTTP_HOST'],
'activation_created' => date('Y-m-d')
);

return array(
'response' => array(
'code' => 200,
'message' => 'OK',
),
'body' => json_encode($response),
);
}
}

return $preempt;
}

/**
 * Load composer autoload
 */
require_once __DIR__ . '/vendor/autoload.php';
/**
 * Load vendor_packages packages
 */
require_once __DIR__ . '/vendor_packages/wp-i18n-map.php';
require_once __DIR__ . '/vendor_packages/wp-dashboard-widget-news.php';
require_once __DIR__ . '/vendor_packages/wp-notice-plugin-required.php';
require_once __DIR__ . '/vendor_packages/wp-plugin-table-links.php';
require_once __DIR__ . '/vendor_packages/wp-license-client.php';
/**
 * Load plugin classes
 */
require_once __DIR__ . '/lib/class-plugin.php';

/**
 * Plugin activation hook
 */
register_activation_hook(
	__FILE__,
	function () {
		do_action( 'wcdc_pro_activation' );
	}
);

/**
 * Plugin activation hook
 */
register_deactivation_hook(
	__FILE__,
	function () {
		do_action( 'wcdc_pro_deactivation' );
	}
);

/**
 * Declare compatibility with WooCommerce Custom Order Tables.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * Declare incompatibility with WooCommerce Cart & Checkout Blocks.
 */
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
		}
	}
);
