<?php
// Add product to WooCommerce programmatically
function create_virtual_product() {
    $product = new WC_Product_Simple();
    $product->set_name('Buzzjuice WoWonder Digital Payment');
    $product->set_status('publish');
    $product->set_catalog_visibility('hidden'); // Hide from catalog
    $product->set_price(0); // Dynamic price
    $product->set_virtual(true);
    $product->set_downloadable(true);
    $product->save();

    update_option('wo_wonder_digital_product_id', $product->get_id());
}
add_action('init', 'create_virtual_product');

// Inject dynamic price for the virtual product
function inject_dynamic_price($cart_object) {
    foreach ($cart_object->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == get_option('wo_wonder_digital_product_id')) {
            $cart_item['data']->set_price($cart_item['woo_dynamic_price']);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'inject_dynamic_price');