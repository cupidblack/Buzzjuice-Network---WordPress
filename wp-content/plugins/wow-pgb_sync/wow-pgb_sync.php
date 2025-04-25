<?php
/*
Plugin Name: WoWonder Payment Gateway Bridge
Description: Sync WooCommerce with WoWonder for digital payments.
Version: 1.00.15
Author: Blue Crown R&D
Author URI: https://koware.org
*/

// 🚀 Create Virtual WooCommerce Products on Plugin Activation
function create_wow_pgb_virtual_products() {
    $products = [
        'WoWPGB-Fund' => 'wow-pgb_fund',
        'WoWPGB-Market' => 'wow-pgb_market',
        'WoWPGB-Wallet' => 'wow-pgb_wallet'
    ];

    $product_ids = get_option('wow_pgb_product_ids', []);

    // Create simple products
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

    // Create variable product for WoWPGB-Pro
    $parent_sku = 'wow-pgb_pro';
    $parent_product_id = wc_get_product_id_by_sku($parent_sku);

    if (!$parent_product_id) {
        // Create parent variable product
        $parent_product = new WC_Product_Variable();
        $parent_product->set_name('WoWPGB-Pro');
        $parent_product->set_status('publish');
        $parent_product->set_catalog_visibility('hidden'); // Hide from catalog
        $parent_product->set_sku($parent_sku);
        $parent_product->set_stock_status('instock');
        $parent_product->save();

        $parent_product_id = $parent_product->get_id();
        $product_ids[$parent_sku] = $parent_product_id;
        update_option("wow_pgb_product_id_$parent_sku", $parent_product_id);
    }

    // Create variations for WoWPGB-Pro
    $variations = [
        'wow-pgb_pro_1' => 'Pro Package 1',
        'wow-pgb_pro_2' => 'Pro Package 2',
        'wow-pgb_pro_3' => 'Pro Package 3',
        'wow-pgb_pro_4' => 'Pro Package 4'
    ];

    foreach ($variations as $sku => $name) {
        $existing_variation_id = wc_get_product_id_by_sku($sku);

        if (!$existing_variation_id) {
            $variation = new WC_Product_Variation();
            $variation->set_parent_id($parent_product_id);
            $variation->set_name($name);
            $variation->set_status('publish');
            $variation->set_virtual(true);
            $variation->set_downloadable(true);
            $variation->set_price(0);
            $variation->set_regular_price(0);
            $variation->set_sku($sku);
            $variation->set_stock_status('instock');
            $variation->save();

            $product_ids[$sku] = $variation->get_id();
        } else {
            $product_ids[$sku] = $existing_variation_id;
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

    //error_log("✅ (wow-pgb_sync.php) Order ID:" . print_r($order, true)); // Log the order ID for debugging

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

                    bluecrown_affiliatewp_post_checkout_verification($order_id);

                    $redirect_url = sprintf(
                        "%s/show_fund/%s?nocache=%d",
                        esc_url($wowonder_url),
                        $wow_post_id,
                        time() // Add a cache-busting parameter
                    );
                    wp_redirect($redirect_url);
                    exit();
                }
            } elseif ($product_sku === 'wow-pgb_pro' || $product_sku === 'wow-pgb_pro_1' || $product_sku === 'wow-pgb_pro_2' || $product_sku === 'wow-pgb_pro_3' || $product_sku === 'wow-pgb_pro_4') {
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
            

                // Get the WooCommerce user ID from the order meta
                $user_email = $order->get_billing_email(); // Get the billing email address from the order

                if (empty($user_email)) {
                    error_log("❌ Billing email address is missing.");
                    return;
                }
                
                // Log the retrieved email for debugging
                //error_log("✅ Retrieved Billing Email: $user_email");
                
                // Get the corresponding user ID for the email address
                $user = get_user_by('email', $user_email);
                
                if (!$user) {
                    error_log("❌ No WordPress user found for the email address: $user_email");
                    return;
                }
                
                $user_id = $user->ID; // Retrieve the user ID
                
                // Log the retrieved user ID for debugging
                //error_log("✅ Retrieved WordPress User ID: $user_id");



                $wow_user_id = ''; // Get the WooCommerce user ID
                foreach ($order->get_meta_data() as $meta) {
                    if ($meta->key === 'userid') {
                        $wow_user_id = $meta->value;
                        break;
                    }
                }
                if (empty($wow_user_id)) {
                    error_log("❌ WooCommerce User ID is missing.");
                    return;
                }
            
                // Step 3: Check the user's pro status in WoWonder
                $retry_count = 0;
                $max_retries = 20; // Retry up to 50 times
                $success = false;


            
                while ($retry_count < $max_retries) {
                    $get_user_response = wp_safe_remote_post("$wowonder_api_url/get-user-data?access_token=$access_token", [
                        'timeout' => 10,
                        'body' => [
                            'server_key' => $server_key,
                            'user_id' => $wow_user_id,
                            'fetch' => 'user_data',
                        ],
                        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    ]);
            
                    if (is_wp_error($get_user_response)) {
                        error_log("❌ Failed to fetch user data from WoWonder: " . $get_user_response->get_error_message());
                        $retry_count++;
                        sleep(5); // Wait 5 seconds before retrying
                        continue;
                    }
            
                    $user_data = json_decode(wp_remote_retrieve_body($get_user_response), true);
                    if (empty($user_data['api_status']) || $user_data['api_status'] != 200) {
                        error_log("❌ Failed to fetch user data from WoWonder: " . print_r($user_data, true));
                        $retry_count++;
                        sleep(5); // Wait 5 seconds before retrying
                        continue;
                    } else {
                        //error_log("✅ User data fetched successfully. User data: " . print_r($user_data, true));
                    }
            
                    // Check if the user's pro status matches
                    if (!empty($user_data['user_data']['is_pro']) && $user_data['user_data']['is_pro'] == 1 &&
                        !empty($user_data['user_data']['pro_type']) && $user_data['user_data']['pro_type'] == $wow_post_id) {
                        error_log("✅ (wow-pgb_sync.php) User's pro status verified successfully.");
                        //error_log("✅ (wow-pgb_sync.php) User's pro status verified successfully. User data: " . print_r($user_data, true));
                        $success = true;
                        break;
                    } else {
                        error_log("❌ User's pro status or pro type does not match. Retry Count " . $retry_count);
                        //error_log("❌ User's pro status or pro type does not match. User data: " . print_r($user_data, true));
                        $retry_count++;
                        sleep(5); // Wait 5 seconds before retrying
                        continue;
                    }
                }

                if ($success) {
                    // Step 4: Activate WooCommerce Subscription
                    $woo_order_id = $order->get_id(); // Get WooCommerce order ID
                    $variation_id = null;
                
                    if (empty($woo_order_id)) {
                        error_log("❌ Missing WooCommerce order ID.");
                        return;
                    }
                
                    // Retrieve the variation_id from line_items
                    foreach ($order->get_items('line_item') as $line_item) {
                        $variation_id = $line_item->get_variation_id();
                        if (!empty($variation_id)) {
                            break; // Stop after finding the first variation ID
                        }
                    }
                    if (empty($variation_id)) {
                        error_log("❌ Missing variation ID for WooCommerce order.");
                        return;
                    }
                
                    // Step 1: Extract subscription metadata
                    $metadata = get_subscription_metadata($variation_id);
                    $subscription_period = $metadata['subscription_period'];
                    $subscription_interval = $metadata['subscription_interval'];

                    // Log the extracted subscription metadata for debugging
                    error_log("✅ Product Metadata:" . print_r($metadata, true)); // Fixed the logging statement
                    //error_log("✅ Subscription Period: $subscription_period");
                    //error_log("✅ Subscription Interval: $subscription_interval");

                    // Calculate the next payment date based on the subscription period and interval
                    $next_payment_date = date('Y-m-d H:i:s', strtotime("+$subscription_interval $subscription_period"));

                    // Log the calculated next payment date
                    //error_log("✅ Next Payment Date: $next_payment_date");

                    // Step 2: Prepare subscription data
                    $subscription_data = [
                        'parent_id' => $woo_order_id, // Set the WooCommerce order ID as the parent ID
                        'customer_id' => $order->get_customer_id(),
                        'line_items' => [],
                        'billing_period' => $subscription_period,
                        'billing_interval' => $subscription_interval,
                        'next_payment_date' => $next_payment_date, // Include the next payment date
                        'status' => 'active',
                    ];

                    foreach ($order->get_items('line_item') as $line_item) {
                        $subscription_data['line_items'][] = [
                            'product_id' => $line_item->get_product_id(),
                            'quantity' => $line_item->get_quantity(),
                        ];
                    }

                    // Step 3: Create subscription using WooCommerce API
                    $woocommerce_api_url = rtrim(get_option('woocommerce_api_url', ''), '/'); // Ensure the base URL is correct
                    $consumer_key = get_option('woocommerce_consumer_key', '');
                    $consumer_secret = get_option('woocommerce_consumer_secret', '');

                    if (empty($woocommerce_api_url) || empty($consumer_key) || empty($consumer_secret)) {
                        error_log("❌ Missing WooCommerce API credentials or URL.");
                        return;
                    }

                    $subscription_response = send_woocommerce_request(
                        $woocommerce_api_url . '/subscriptions', // Append the correct endpoint
                        'POST',
                        $subscription_data,
                        $consumer_key,
                        $consumer_secret
                    );

                    if ($subscription_response['http_code'] === 201) {
                        $subscription = json_decode($subscription_response['response'], true);
                        if (!empty($subscription)) {
                            $subscription_id = $subscription['id'] ?? null;
                            error_log("✅ Subscription created successfully with ID: $subscription_id");

                            bluecrown_affiliatewp_post_checkout_verification($order_id); // Call the function to credit the affiliate

                            // Redirect to the upgraded page for WoWPGB-Pro
                            $redirect_url = sprintf("%s/upgraded", esc_url($wowonder_url));
                            wp_redirect($redirect_url);
                            exit();
                        } else {
                            error_log("❌ Failed to decode subscription response.");
                        }
                    } else {
                        error_log("❌ Failed to create subscription. HTTP Code: {$subscription_response['http_code']}");
                        $error_details = json_decode($subscription_response['response'], true);
                        error_log("❌ Error Details: " . print_r($error_details, true));
                    }
                }
                
            } else { 
                    
                // Handle wallet product redirection
                if ($product_sku === 'wow-pgb_wallet') {

                    bluecrown_affiliatewp_post_checkout_verification($order_id);

                    $redirect_url = sprintf(
                        "%s/wallet/?nocache=%d",
                        esc_url($wowonder_url),
                        time() // Add a cache-busting parameter
                    );
                    wp_redirect($redirect_url);
                    exit();
                }
            } 
        } else { // Corrected the unmatched '}' issue
            
            if ($product_sku === 'wow-pgb_market') {

                bluecrown_affiliatewp_post_checkout_verification($order_id);
                // Handle Market Redirection
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

// 🔹 Add WoWonder and WooCommerce API Settings to WordPress General Settings
function wowonder_settings_init() {
    add_settings_section(
        'wowonder_settings_section',
        'WoWonder Settings',
        function() { echo '<p>Settings for WoWonder and WooCommerce integration.</p>'; },
        'general'
    );

    // WoWonder URL
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

    // WooCommerce API URL
    add_settings_field(
        'woocommerce_api_url',
        'WooCommerce API URL',
        function() {
            $woocommerce_api_url = get_option('woocommerce_api_url', '');
            echo '<input type="url" id="woocommerce_api_url" name="woocommerce_api_url" value="' . esc_attr($woocommerce_api_url) . '" class="regular-text ltr">';
        },
        'general',
        'wowonder_settings_section'
    );

    // WooCommerce Consumer Key
    add_settings_field(
        'woocommerce_consumer_key',
        'WooCommerce Consumer Key',
        function() {
            $consumer_key = get_option('woocommerce_consumer_key', '');
            echo '<input type="text" id="woocommerce_consumer_key" name="woocommerce_consumer_key" value="' . esc_attr($consumer_key) . '" class="regular-text">';
        },
        'general',
        'wowonder_settings_section'
    );

    // WooCommerce Consumer Secret
    add_settings_field(
        'woocommerce_consumer_secret',
        'WooCommerce Consumer Secret',
        function() {
            $consumer_secret = get_option('woocommerce_consumer_secret', '');
            echo '<input type="text" id="woocommerce_consumer_secret" name="woocommerce_consumer_secret" value="' . esc_attr($consumer_secret) . '" class="regular-text">';
        },
        'general',
        'wowonder_settings_section'
    );

    register_setting('general', 'wowonder_url', 'esc_url');
    register_setting('general', 'woocommerce_api_url', 'esc_url');
    register_setting('general', 'woocommerce_consumer_key', 'sanitize_text_field');
    register_setting('general', 'woocommerce_consumer_secret', 'sanitize_text_field');
}
add_action('admin_init', 'wowonder_settings_init');

/**
 * Retrieve subscription metadata and all product metadata for a given variation ID.
 *
 * @param int $variation_id The variation product ID.
 * @return array An array containing the subscription period, interval, and full metadata.
 */
function get_subscription_metadata($variation_id) {
    $subscription_period = 'month'; // Default value
    $subscription_interval = 1; // Default value
    $full_metadata = []; // To store all metadata

    // Retrieve the product object for the variation
    $product = wc_get_product($variation_id);
    if ($product) {
        // Loop through the product's metadata to find subscription-related keys
        foreach ($product->get_meta_data() as $meta) {
            $full_metadata[$meta->key] = $meta->value; // Store all metadata
            if ($meta->key === '_subscription_period') {
                $subscription_period = $meta->value;
            }
            if ($meta->key === '_subscription_period_interval') {
                $subscription_interval = (int) $meta->value;
            }
        }
    }

    // Log the full metadata for debugging
    error_log("✅ Full Product Metadata for Variation ID $variation_id: " . print_r($full_metadata, true));

    return [
        'subscription_period' => $subscription_period,
        'subscription_interval' => $subscription_interval,
        'full_metadata' => $full_metadata, // Include all metadata in the return value
    ];
}

/**
 * Utility function for sending API requests (WooCommerce and WordPress).
 *
 * @param string $url The API endpoint URL.
 * @param string $method The HTTP method (GET, POST, PUT, DELETE).
 * @param array|null $data The data to send in the request body (for POST/PUT).
 * @param string|null $auth_key The WooCommerce consumer key.
 * @param string|null $auth_secret The WooCommerce consumer secret.
 * @return array The API response, HTTP code, and any errors.
 */
function send_woocommerce_request($url, $method = 'GET', $data = null, $auth_key = null, $auth_secret = null) {
    // Validate the URL
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        error_log("❌ Invalid URL provided: $url");
        return ['response' => null, 'http_code' => 0, 'error' => 'Invalid URL'];
    }

    // Initialize cURL
    $curl = curl_init();
    $headers = [
        'Content-Type: application/json',
    ];

    // Add Authorization header if credentials are provided
    if ($auth_key && $auth_secret) {
        $headers[] = 'Authorization: Basic ' . base64_encode("$auth_key:$auth_secret");
    }

    // Set cURL options
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_SSL_VERIFYHOST => 0, // Disable SSL verification for local testing
        CURLOPT_SSL_VERIFYPEER => 0, // Disable SSL verification for local testing
    ];

    // Add POST/PUT data if provided
    if (!empty($data)) {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }

    curl_setopt_array($curl, $options);

    // Execute the request
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    // Logging for debugging
    //error_log("📡 API Request: $method $url");
    //error_log("📡 API HTTP Code: $http_code");
    if ($error) error_log("⚠️ cURL Error: $error");
    //error_log("📥 API Response: " . print_r($response, true));

    return ['response' => $response, 'http_code' => $http_code, 'error' => $error];
}

function bluecrown_affiliatewp_post_checkout_verification($order_id) {
    // 🔹 AffiliateWP Integration: Ensure affiliates are credited for API orders
    global $wpdb;

    // Step 1: Retrieve the user ID (customer ID) from the WooCommerce order
    $order = wc_get_order($order_id); // Ensure the order object is retrieved
    if (!$order) {
        error_log("❌ Invalid WooCommerce order ID: $order_id");
        return;
    }

    $user_id = $order->get_customer_id(); // Use get_customer_id() instead of get_user_id()
    if (!$user_id) {
        error_log("❌ No user ID (customer ID) found for order ID: $order_id");
        return;
    }

    // Step 2: Fetch affwp_customer_id from wp_affiliate_wp_customers table
    $affwp_customer_id = $wpdb->get_var($wpdb->prepare(
        "SELECT customer_id FROM wp_affiliate_wp_customers WHERE user_id = %d",
        $user_id
    ));

    if (!$affwp_customer_id) {
        error_log("❌ No affwp_customer_id found for user ID: $user_id");
        return;
    }

    // Step 3: Fetch affiliate_id from wp_affiliate_wp_customermeta table
    $affiliate_id = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM wp_affiliate_wp_customermeta WHERE affwp_customer_id = %d AND meta_key = 'affiliate_id'",
        $affwp_customer_id
    ));

    if (!$affiliate_id) {
        error_log("❌ No affiliate_id found for affwp_customer_id: $affwp_customer_id");
        return;
    }

    // Step 4: Check if the affiliate has already been credited for this order
    $referral_exists = affiliate_wp()->referrals->get_by('reference', $order_id, 'woocommerce');
    if ($referral_exists) {
        error_log("✅ Affiliate already credited for order ID: $order_id");
        return;
    }

    // Step 5: Create a referral for the affiliate
    $order_total = $order->get_total();
    $referral_args = [
        'reference'    => $order_id,
        'amount'       => $order_total,
        'description'  => "Order #{$order_id}",
        'affiliate_id' => $affiliate_id,
        'context'      => 'woocommerce',
        'status'       => 'unpaid', // Default status, can be changed to 'paid' if necessary
    ];

    $referral_id = affiliate_wp()->referrals->add($referral_args);

    if ($referral_id) {
        error_log("✅ Affiliate credited successfully. Referral ID: $referral_id");
    } else {
        error_log("❌ Failed to credit affiliate for order ID: $order_id");
    }
}
?>