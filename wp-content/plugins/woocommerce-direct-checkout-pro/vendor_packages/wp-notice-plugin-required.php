<?php

namespace QuadLayers\WP_Notice_Plugin_Required;

if ( class_exists( 'QuadLayers\\WP_Notice_Plugin_Required\\Load' ) ) {
	new \QuadLayers\WP_Notice_Plugin_Required\Load(
		QLWCDC_PRO_PLUGIN_NAME,
		array(
			array(
				'slug' => 'woocommerce',
				'name' => 'WooCommerce',
			),
			array(
				'slug' => 'woocommerce-direct-checkout',
				'name' => 'WooCommerce Direct Checkout',
			),
		)
	);
}
