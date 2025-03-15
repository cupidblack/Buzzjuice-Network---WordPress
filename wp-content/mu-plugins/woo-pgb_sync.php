<?php
// Add product to WooCommerce programmatically
function create_virtual_product() {
    // Define the base SKU
    $base_sku = 'wowonder-digital-payment';

    // Check if a product with this SKU already exists
    $existing_product_id = wc_get_product_id_by_sku($base_sku);

    if ($existing_product_id) {
        // Use the existing product
        $product = wc_get_product($existing_product_id);
    } else {
        // Generate a unique SKU if the base SKU already exists
        $unique_sku = $base_sku . '-' . uniqid();

        $product = new WC_Product_Simple();
        $product->set_name('Buzzjuice WoWonder Digital Payment');
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden'); // Hide from catalog
        $product->set_price(0); // Dynamic price
        $product->set_regular_price(0); // Regular price mapping
        $product->set_sale_price(0); // Sale price mapping
        $product->set_virtual(true);
        $product->set_downloadable(true);
        $product->set_sku($unique_sku); // Set unique SKU
        $product->set_manage_stock(false); // Inventory management
        $product->set_stock_status('instock'); // Stock status
        $product->set_sold_individually(true); // Sold individually
        $product->set_download_limit(-1); // Download limit
        $product->set_download_expiry(-1); // Download expiry
        $product->save();
    }

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
?>