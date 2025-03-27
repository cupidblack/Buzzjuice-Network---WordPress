<?php
/*
Plugin Name: WooCommerce PGB Sync
Description: Sync WooCommerce with WoWonder for digital payments.
Version: 1.0006
Author: Your Name
*/

// 🚀 Create Virtual WooCommerce Products on Plugin Activation
function create_woo_pgb_virtual_products() {
    $products = [
        'WooPGB-Donate' => 'woo-pgb_donate',
        'WooPGB-Pro' => 'woo-pgb_pro',
        'WooPGB-Market' => 'woo-pgb_market',
        'WooPGB-Wallet' => 'woo-pgb_wallet'
    ];

    $product_ids = get_option('woo_pgb_product_ids', []);

    foreach ($products as $name => $sku) {
        $existing_product_id = wc_get_product_id_by_sku($sku);

        if (!$existing_product_id) {
            $product = new WC_Product_Simple();
            $product->set_name($name);
            $product->set_status('publish');
            $product->set_catalog_visibility('hidden'); // Hide from catalog
            $product->set_price(0);
            $product->set_regular_price(0);
            $product->set_virtual(true);
            $product->set_downloadable(true);
            $product->set_sku($sku);
            $product->set_stock_status('instock');
            $product->set_sold_individually(true);
            $product->save();

            $product_ids[$sku] = $product->get_id();
        } else {
            $product_ids[$sku] = $existing_product_id;
        }
        update_option("woo_pgb_product_id_$sku", $product_ids[$sku]);
    }

    update_option('woo_pgb_product_ids', $product_ids);
}
register_activation_hook(__FILE__, 'create_woo_pgb_virtual_products');

// 🔹 Override Product Name & Price in WooCommerce Cart, Checkout, Emails, & Invoices
function override_woo_product_metadata($cart) {
    $product_ids = get_option('woo_pgb_product_ids', []);

    foreach ($cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if (isset($product_ids[$product->get_sku()])) {
            if (isset($cart_item['woo_dynamic_price'])) {
                $product->set_price($cart_item['woo_dynamic_price']);
            }
            if (isset($cart_item['woo_product_name'])) {
                $product->set_name(htmlspecialchars_decode($cart_item['woo_product_name'], ENT_QUOTES)); // Decode the product name
            }
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'override_woo_product_metadata');

// 🔹 Set Dynamic Price & Name When Item is Added to Cart
function set_dynamic_price_and_title_cart_item_data($cart_item_data, $product_id) {
    $product = wc_get_product($product_id);

    if (strpos($product->get_sku(), 'woo-pgb_') === 0) {
        $cart_item_data['woo_dynamic_price'] = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
        $cart_item_data['woo_product_name'] = isset($_GET['product_name']) ? htmlspecialchars_decode(sanitize_text_field($_GET['product_name']), ENT_QUOTES) : 'Buzzjuice WoWonder Digital Payment'; // Decode the product name
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'set_dynamic_price_and_title_cart_item_data', 10, 2);

// 🔹 Modify Order Metadata at Checkout
function woo_pgb_modify_order_metadata($item, $cart_item_key, $values, $order) {
    if (isset($values['woo_product_name'])) {
        $item->set_name($values['woo_product_name']);
    }
}
add_action('woocommerce_checkout_create_order_line_item', 'woo_pgb_modify_order_metadata', 10, 4);

// 🔹 Redirect After Purchase to WoWonder
function wowonder_redirect_after_purchase($order_id) {
    $order = wc_get_order($order_id);
    $product_ids = get_option('woo_pgb_product_ids', []);
    $wowonder_url = get_option('wowonder_url', 'http://127.0.0.1/buzzjuice.net/streams/');

    foreach ($order->get_items() as $item) {
        $product = wc_get_product($item->get_product_id());

        if (strpos($product->get_sku(), 'woo-pgb_') === 0) {
            $redirect_url = sprintf(
                "%s/wallet/?order_id=%d&amount=%s&user_id=%d&product_name=%s&product_price=%s&product_units=%s",
                esc_url($wowonder_url),
                $order_id,
                $order->get_total(),
                $order->get_user_id(),
                urlencode($item->get_name()),
                $item->get_total(),
                $item->get_quantity()
            );
            wp_redirect($redirect_url);
            exit();
        }
    }
}
add_action('woocommerce_thankyou', 'wowonder_redirect_after_purchase');

// 🔹 Add WoWonder Settings to WordPress General Settings
function wowonder_settings_init() {
    add_settings_section(
        'wowonder_settings_section',
        'WoWonder Settings',
        function() { echo '<p>Settings for WoWonder integration.</p>'; },
        'general'
    );

    add_settings_field(
        'wowonder_url',
        'WoWonder URL',
        function() {
            $wowonder_url = get_option('wowonder_url', '');
            echo '<input type="url" id="wowonder_url" name="wowonder_url" value="' . esc_attr($wowonder_url) . '" class="regular-text ltr">';
        },
        'general',
        'wowonder_settings_section'
    );

    register_setting('general', 'wowonder_url', 'esc_url');
}
add_action('admin_init', 'wowonder_settings_init');

function wowonder_settings_section_callback() {
    echo '<p>Settings for WoWonder integration.</p>';
}
?>