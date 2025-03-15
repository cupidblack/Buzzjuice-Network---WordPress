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
        $product = new WC_Product_Simple();
        $product->set_name('Buzzjuice WoWonder Digital Payment');
        $product->set_status('publish');
        $product->set_catalog_visibility('hidden'); // Hide from catalog
        $product->set_price(0); // Dynamic price
        $product->set_regular_price(0); // Regular price mapping
        $product->set_sale_price(0); // Sale price mapping
        $product->set_virtual(true);
        $product->set_downloadable(true);
        $product->set_sku($base_sku); // Set base SKU
        $product->set_manage_stock(false); // Inventory management
        $product->set_stock_status('instock'); // Stock status
        $product->set_sold_individually(true); // Sold individually
        $product->set_download_limit(-1); // Download limit
        $product->set_download_expiry(-1); // Download expiry
        $product->save();
    }

    update_option('wo_wonder_digital_product_id', $product->get_id());
}

// Hook to run the create_virtual_product function only when the plugin is activated
register_activation_hook(__FILE__, 'create_virtual_product');

// Inject dynamic price for the virtual product
function inject_dynamic_price($cart_object) {
    foreach ($cart_object->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == get_option('wo_wonder_digital_product_id')) {
            $cart_item['data']->set_price($cart_item['woo_dynamic_price']);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'inject_dynamic_price');

// Redirect to WooCommerce Checkout
function redirect_to_woocommerce_checkout($order_id, $user_id, $amount, $product_name, $product_price, $product_units, $product_owner_id) {
    $woo_store_url = get_option('woo_store_url') ?? '';
    $product_id = get_option('wo_wonder_digital_product_id');
    
    if (empty($woo_store_url) || empty($product_id)) {
        error_log("WooCommerce Payment Init Error: Store URL or Product ID not set.");
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'WooCommerce store URL or Product ID is not configured.']);
        exit();
    }

    $redirect_url = sprintf(
        "%s/checkout/?add-to-cart=%d&quantity=%d&woo_order_id=%s&amount=%.2f&user_id=%s&product_name=%s&product_price=%.2f&product_units=%d&product_owner_id=%d",
        $woo_store_url,
        $product_id,
        $product_units, 
        urlencode($order_id),
        $amount,
        $user_id,
        $product_name,
        $product_price,
        $product_units,
        $product_owner_id
    );
    header("Location: $redirect_url");
    exit();
}

// Custom function to handle redirection for specific product
function wowonder_redirect_after_purchase($order_id) {
    $order = wc_get_order($order_id);
    $product_id = get_option('wo_wonder_digital_product_id');

    // Check if the order contains the 'Buzzjuice WoWonder Digital Payment' product
    foreach ($order->get_items() as $item) {
        if ($item->get_product_id() == $product_id) {
            // Get necessary parameters
            $amount = $order->get_total();
            $user_id = $order->get_user_id();
            $product_name = urlencode($item->get_name());
            $product_price = $item->get_total();
            $product_units = $item->get_quantity();
            $product_owner_id = ''; // Add logic to get product owner ID if necessary
            $woo_order_id = $order_id;

            // Construct WoWonder URL
            $wowonder_url = sprintf(
                "%s/checkout-success?woo_order_id=%s&amount=%.2f&user_id=%s&product_name=%s&product_price=%.2f&product_units=%d&product_owner_id=%d",
                get_option('wowonder_url'),
                $woo_order_id,
                $amount,
                $user_id,
                $product_name,
                $product_price,
                $product_units,
                $product_owner_id
            );

            // Redirect to WoWonder
            wp_redirect($wowonder_url);
            exit();
        }
    }
}
add_action('woocommerce_thankyou', 'wowonder_redirect_after_purchase');
?>