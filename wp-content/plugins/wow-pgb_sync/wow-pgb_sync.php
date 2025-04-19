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
        'WoWPGB-Fund' => 'wow-pgb_fund',
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

// 🔹 Redirect After Purchase to WoWonder
function wowonder_redirect_after_purchase($order_id) {
    $order = wc_get_order($order_id);
    $product_ids = get_option('wow_pgb_product_ids', []); // Retrieve product IDs for WoWPGB products
    $wowonder_url = get_option('wowonder_url', 'http://127.0.0.1/buzzjuice.net/streams/'); // Base WoWonder URL

    foreach ($order->get_items() as $item) {
        $product = wc_get_product($item->get_product_id());

        // Check if the product SKU starts with 'wow-pgb_'
        if (strpos($product->get_sku(), 'wow-pgb_') === 0) {
            // Get the product SKU
            $product_sku = $product->get_sku();

            // Handle specific product types
            if ($product_sku === 'wow-pgb_fund') {
                // Get the wow_post_id from the webhook response (meta data)
                $wow_post_id = '';
                foreach ($order->get_meta_data() as $meta) {
                    if ($meta->key === 'wow_post_id') {
                        $wow_post_id = $meta->value;
                        break;
                    }
                }

                // Redirect to the WoWonder fund page
                if (!empty($wow_post_id)) {
                    $redirect_url = sprintf(
                        "%s/show_fund/%s?nocache=%d",
                        esc_url($wowonder_url),
                        $wow_post_id,
                        time() // Add a cache-busting parameter
                    );
                    wp_redirect($redirect_url);
                    exit();
                }
            } elseif ($product_sku === 'wow-pgb_pro') {
                // Authenticate to WoWonder to obtain an access token
                $wowonder_api_url = 'http://127.0.0.1/buzzjuice.net/streams/api';
                $server_key = 'd2c99a2e27e91439e54bdfc48c143119'; // Replace with your actual server key
                $wow_username = 'drenkaby'; // Replace with your WoWonder admin username
                $wow_password = 'cupidblack'; // Replace with your WoWonder admin password
            
                // Step 1: Authenticate to WoWonder
                $auth_response = wp_safe_remote_post("$wowonder_api_url/auth", [
                    'timeout' => 10,
                    'body' => [
                        'server_key' => $server_key,
                        'username' => $wow_username,
                        'password' => $wow_password,
                    ],
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                ]);
            
                if (is_wp_error($auth_response)) {
                    error_log("❌ WoWonder Authentication Failed: " . $auth_response->get_error_message());
                    return;
                }
            
                $auth_data = json_decode(wp_remote_retrieve_body($auth_response), true);
                if (empty($auth_data['api_status']) || $auth_data['api_status'] != 200) {
                    error_log("❌ WoWonder Authentication Failed: " . print_r($auth_data, true));
                    return;
                }
            
                $access_token = $auth_data['access_token'];
            
                // Step 2: Get the user_id from the WooCommerce webhook meta
                $wow_post_id = '';
                foreach ($order->get_meta_data() as $meta) {
                    if ($meta->key === 'wow_post_id') {
                        $wow_post_id = $meta->value;
                        break;
                    }
                }
            
                if (empty($wow_post_id)) {
                    error_log("❌ WoWonder Post ID is missing from the webhook meta.");
                    return;
                }
            
                $user_id = ''; // Get the WooCommerce user ID
                foreach ($order->get_meta_data() as $meta) {
                    if ($meta->key === 'userid') {
                        $user_id = $meta->value;
                        break;
                    }
                }
                if (empty($user_id)) {
                    error_log("❌ WooCommerce User ID is missing.");
                    return;
                }
            
                // Step 3: Check the user's pro status in WoWonder
                $retry_count = 0;
                $max_retries = 3; // Retry up to 3 times
                $success = false;
            
                while ($retry_count < $max_retries) {
                    $get_user_response = wp_safe_remote_post("$wowonder_api_url/get-user-data?access_token=$access_token", [
                        'timeout' => 10,
                        'body' => [
                            'server_key' => $server_key,
                            'user_id' => $user_id,
                            'fetch' => 'user_data',
                        ],
                        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    ]);
            
                    if (is_wp_error($get_user_response)) {
                        error_log("❌ Failed to fetch user data from WoWonder: " . $get_user_response->get_error_message());
                        $retry_count++;
                        sleep(3); // Wait 3 seconds before retrying
                        continue;
                    }
            
                    $user_data = json_decode(wp_remote_retrieve_body($get_user_response), true);
                    if (empty($user_data['api_status']) || $user_data['api_status'] != 200) {
                        error_log("❌ Failed to fetch user data from WoWonder: " . print_r($user_data, true));
                        $retry_count++;
                        sleep(3); // Wait 3 seconds before retrying
                        continue;
                    } else {
                        error_log("✅ User data fetched successfully. User data: " . print_r($user_data, true));
                    }
            
                    // Check if the user's pro status matches
                    if (!empty($user_data['user_data']['is_pro']) && $user_data['user_data']['is_pro'] == 1 &&
                        !empty($user_data['user_data']['pro_type']) && $user_data['user_data']['pro_type'] == $wow_post_id) {
                        error_log("✅ User's pro status verified successfully. User data: " . print_r($user_data, true));
                        $success = true;
                        break;
                    } else {
                        error_log("❌ User's pro status or pro type does not match. User data: " . print_r($user_data, true));
                        $retry_count++;
                        sleep(3); // Wait 3 seconds before retrying
                        continue;
                    }
                }
            
                if (!$success) {
                    error_log("❌ Failed to verify user's pro status after $max_retries retries.");
                    return;
                }
            
                // Step 4: Redirect to the upgraded page for WoWPGB-Pro
                $redirect_url = sprintf(
                    "%s/upgraded",
                    esc_url($wowonder_url)
                );
                wp_redirect($redirect_url);
                exit();
            } elseif ($product_sku === 'wow-pgb_wallet') {
                // Redirect to the WoWonder wallet page
                $redirect_url = sprintf(
                    "%swallet/?nocache=%d",
                    esc_url($wowonder_url),
                    time() // Add a cache-busting parameter
                );
                wp_redirect($redirect_url);
                exit();
            } else {
                // Handle other product types (e.g., WoWPGB-Market, etc.)
                $redirect_url = sprintf(
                    "%s/purchased",
                    esc_url($wowonder_url)
                );
                wp_redirect($redirect_url);
                exit();
            }
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