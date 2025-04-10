<?php
/*
Plugin Name: WoWonder Payment Gateway Bridge
Description: Sync WooCommerce with WoWonder for digital payments.
Version: 1.0014
Author: Blue Crown R&D
Author URI: https://koware.org
*/

// 🚀 Create Virtual WooCommerce Products on Plugin Activation
function create_wow_pgb_virtual_products() {
    $products = [
        'WoWPGB-Donate' => 'wow-pgb_donate',
        'WoWPGB-Pro' => 'wow-pgb_pro',
        'WoWPGB-Market' => 'wow-pgb_market',
        'WoWPGB-Wallet' => 'wow-pgb_wallet'
    ];

    $product_ids = get_option('wow_pgb_product_ids', []);

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
        update_option("wow_pgb_product_id_$sku", $product_ids[$sku]);
    }

    update_option('wow_pgb_product_ids', $product_ids);
}
register_activation_hook(__FILE__, 'create_wow_pgb_virtual_products');

// 🔹 Override Product Name & Price in WooCommerce Cart, Checkout, Emails, & Invoices
function override_woo_product_metadata($cart) {
    $product_ids = get_option('wow_pgb_product_ids', []);

    foreach ($cart->get_cart() as $cart_item) {
        $product = $cart_item['data'];
        if (isset($product_ids[$product->get_sku()])) {
            if (isset($cart_item['woo_dynamic_price']) && isset($cart_item['wow_currency_code'])) {
                global $WOOCS;
                $default_currency = get_option('woocommerce_currency');
                $converted_price = $WOOCS->convert_from_to_currency($cart_item['woo_dynamic_price'], $cart_item['wow_currency_code'], $default_currency);
                $product->set_price($converted_price);
            }
            if (isset($cart_item['woo_product_name'])) {
                $product->set_name(htmlspecialchars_decode($cart_item['woo_product_name'], ENT_QUOTES)); // Decode the product name
            }
            if (isset($cart_item['wow_currency_code'])) {
                update_post_meta($product->get_id(), '_currency', htmlspecialchars_decode($cart_item['wow_currency_code'], ENT_QUOTES)); // Update the currency code
            }
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'override_woo_product_metadata');

// 🔹 Set Dynamic Price & Name When Item is Added to Cart
function set_dynamic_price_and_title_cart_item_data($cart_item_data, $product_id) {
    $product = wc_get_product($product_id);

    if (strpos($product->get_sku(), 'wow-pgb_') === 0) {
        $cart_item_data['woo_dynamic_price'] = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
        $cart_item_data['woo_product_name'] = isset($_GET['product_name']) ? htmlspecialchars_decode(sanitize_text_field($_GET['product_name']), ENT_QUOTES) : 'Buzzjuice WoWonder Digital Payment'; // Decode the product name
        $cart_item_data['wow_currency_code'] = isset($_GET['wow_currency_code']) ? htmlspecialchars_decode(sanitize_text_field($_GET['wow_currency_code']), ENT_QUOTES) : ''; // Decode the currency code
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'set_dynamic_price_and_title_cart_item_data', 10, 2);

// 🔹 Modify Order Metadata at Checkout
// 🔹 Modify Order Metadata at Checkout to Store wow_order_id
function wow_pgb_modify_order_metadata($item, $cart_item_key, $values, $order) {
    if (isset($values['woo_product_name'])) {
        $item->set_name($values['woo_product_name']);
    }
    
    // Store wow_order_id in order meta
    if (isset($_GET['wow_order_id'])) {
        $order->update_meta_data('_wow_order_id', sanitize_text_field($_GET['wow_order_id']));
    }

    // Store wow_post_id in order meta
    if (isset($_GET['wow_post_id'])) {
        $order->update_meta_data('_wow_post_id', sanitize_text_field($_GET['wow_post_id']));
    }

}
add_action('woocommerce_checkout_create_order_line_item', 'wow_pgb_modify_order_metadata', 10, 4);



// 🔹 Redirect After Purchase to WoWonder
/*function wowonder_redirect_after_purchase($order_id) {
    $order = wc_get_order($order_id);
    $product_ids = get_option('wow_pgb_product_ids', []);
    $wowonder_url = get_option('wowonder_url', 'http://127.0.0.1/buzzjuice.net/streams/');

    foreach ($order->get_items() as $item) {
        $product = wc_get_product($item->get_product_id());

        if (strpos($product->get_sku(), 'wow-pgb_') === 0) {
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
*/


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