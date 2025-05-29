<?php
/*
Plugin Name: WoWonder Payment Gateway Bridge
Description: Sync WooCommerce with WoWonder for digital payments.
Version: 1.00.15
Author: Blue Crown R&D
Author URI: https://koware.org
*/

// Load environment variables
require_once __DIR__ . '/../blue-crown-wp/DotEnv.php';
try {
    $dotenv = new DotEnv(dirname(__DIR__, 4) . '/.env');
    $dotenv->load();
} catch (\Exception $e) {
    error_log('Failed to load .env file: ' . $e->getMessage());
    // Optionally, handle the error (e.g., fallback to default values or terminate execution)
}

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
    $buzzsocial_url = get_option('buzzsocial_url', 'http://127.0.0.1/buzzjuice.net/social/'); // Base WoWonder URL


    //error_log("✅ (wow-pgb_sync.php) Order ID:" . print_r($order, true)); // Log the order ID for debugging

    foreach ($order->get_items() as $item) {
        $product = wc_get_product($item->get_product_id());

        // Check if the product SKU starts with 'wow-pgb_'
        if (strpos($product->get_sku(), 'wow-pgb_') === 0) {

            // Get the product SKU
            $product_sku = $product->get_sku();

            // Get the wow_post_id from the webhook response (meta data)
            $wow_post_id = '';
            foreach ($order->get_meta_data() as $meta) {
                if ($meta->key === 'wow_post_id') {
                    $wow_post_id = $meta->value;
                    break;
                } elseif ($meta->key === 'qdw_membershipType') {
                    $wow_post_id = $meta->value;
                    break;
                }
            }

            //error_log("wow_post_id: " . print_r($wow_post_id, true)); // Log the retrieved WoWonder post ID for debugging
            
            $qdw_transaction_kind = '';
            foreach ($order->get_meta_data() as $meta) {
                if ($meta->key === 'qdw_transaction_kind') {
                    $qdw_transaction_kind = $meta->value;
                    break;
                }
            }

            if (empty($wow_post_id) && empty($qdw_membershipType) && empty($qdw_transaction_kind)) {
                error_log("❌ WoWonder Post ID and QDW Membership Type are missing from the webhook meta.");
                return;
            }

            // Handle specific product types
            if ($product_sku === 'wow-pgb_fund') {

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
            }
            
            if ($product_sku === 'wow-pgb_pro' || $product_sku === 'wow-pgb_pro_1' || $product_sku === 'wow-pgb_pro_2' || $product_sku === 'wow-pgb_pro_3' || $product_sku === 'wow-pgb_pro_4') {
                // Authenticate to WoWonder to obtain an access token
                $wowonder_api_url = $_ENV['WOWONDER_API_URL'];
                $server_key = $_ENV['WOWONDER_SERVER_KEY'];
                $wow_username = $_ENV['WOWONDER_ADMIN_USERNAME'];
                $wow_password = $_ENV['WOWONDER_ADMIN_PASSWORD'];
            
                // Step 1: Authenticate to WoWonder
                $access_token = retry_with_backoff(function () use ($wowonder_api_url, $server_key, $wow_username, $wow_password) {
                    return authenticate_to_wowonder($wowonder_api_url, $server_key, $wow_username, $wow_password);
                }, 5, 5, 80); // Retry up to 5 times with exponential backoff

                if (!$access_token) {
                    error_log("❌ Failed to authenticate to WoWonder after retries.");
                    return;
                }
            
                // Step 2: Get the user_id from the WooCommerce webhook meta

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
                    if (!empty($meta->key) && $meta->key === 'userid') {
                        $wow_user_id = $meta->value;
                        break;
                    } elseif (!empty($meta->key) && $meta->key === 'wow_user_id') {
                        $wow_user_id = $meta->value;
                        break;
                    }
                }
                if (empty($wow_user_id)) {
                    error_log("❌ WooCommerce User ID is missing.");
                    return;
                }
            
                // Step 3: Check the user's pro status in WoWonder
                $success = retry_with_backoff(function () use ($wowonder_api_url, $access_token, $server_key, $wow_user_id, $wow_post_id) {
                    $user_data = fetch_wowonder_user_data($wowonder_api_url, $access_token, $server_key, $wow_user_id);

                    if (!$user_data) {
                        return null; // Retry if user data is not fetched
                    }

                    // Check if the user's pro status matches
                    if (!empty($user_data['is_pro']) && $user_data['is_pro'] == 1 &&
                        (!empty($user_data['pro_type']) && $user_data['pro_type'] == $wow_post_id)) {
                        error_log("✅ (wow-pgb_sync.php) User's pro status verified successfully.");
                        return true; // Stop retrying if the condition is met
                    } else {
                        error_log("❌ User's pro status or pro type does not match.");
                        return null; // Retry if the condition is not met
                    }
                }, 10, 2, 300); // Retry up to 5 times with exponential backoff

                if (!$success) {
                    error_log("❌ Failed to verify user's pro status after retries.");
                } else {
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
                    //error_log("✅ Product Metadata:" . print_r($metadata, true)); // Fixed the logging statement
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

                    $subscription_response = retry_with_backoff(function () use ($woocommerce_api_url, $consumer_key, $consumer_secret, $subscription_data) {
                        return create_woocommerce_subscription(
                            $woocommerce_api_url, // Base WooCommerce API URL
                            $consumer_key,        // WooCommerce Consumer Key
                            $consumer_secret,     // WooCommerce Consumer Secret
                            $subscription_data    // Subscription data to send
                        );
                    }, 5, 5, 80); // Retry up to 5 times with exponential backoff

                    // Validate the response structure
                    if (empty($subscription_response)) {
                        log_error("❌ Invalid subscription response received after retries.");
                        return;
                    }

                    // Log success
                    $subscription_id = $subscription_response['id'] ?? null;
                    error_log("✅ Subscription created successfully with ID: $subscription_id");

                    // Additional logic for post-subscription actions
                    bluecrown_affiliatewp_post_checkout_verification($order_id); // Call the function to credit the affiliate

                    if ($order->get_meta_data('wow_order_id') === true) {
                        // Redirect to the upgraded page for WoWPGB-Pro
                        $redirect_url = sprintf("%s/upgraded", esc_url($wowonder_url));
                        wp_redirect($redirect_url);
                        exit();
                    } else {
                        // Redirect to the purchased page for other products
                        $redirect_url = sprintf("%s/ProSuccess?paymode=pro", esc_url($buzzsocial_url));
                        wp_redirect($redirect_url);
                        exit();
                    }
                }
            }
            
            if ($product_sku === 'wow-pgb_wallet') {

                    bluecrown_affiliatewp_post_checkout_verification($order_id);

                    if ($order->get_meta_data('wow_order_id') === true) {
                        // Redirect to the upgraded page for WoWPGB-Pro
                        $redirect_url = sprintf(
                            "%s/wallet/?nocache=%d",
                            esc_url($wowonder_url),
                            time() // Add a cache-busting parameter
                        );
                        wp_redirect($redirect_url);
                        exit();
                    } else {
                        // Redirect to the purchased page for other products
                        $redirect_url = sprintf("%s/ProSuccess", esc_url($buzzsocial_url));
                        wp_redirect($redirect_url);
                        exit();
                    }
                }
             

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

    // BuzzSocial URL
    add_settings_field(
        'buzzsocial_url',
        'BuzzSocial URL',
        function() {
            $buzzsocial_url = get_option('buzzsocial_url', '');
            echo '<input type="url" id="buzzsocial_url" name="buzzsocial_url" value="' . esc_attr($buzzsocial_url) . '" class="regular-text ltr">';
        },
        'general',
        'wowonder_settings_section'
    );

    register_setting('general', 'wowonder_url', 'esc_url');
    register_setting('general', 'woocommerce_api_url', 'esc_url');
    register_setting('general', 'woocommerce_consumer_key', 'sanitize_text_field');
    register_setting('general', 'woocommerce_consumer_secret', 'sanitize_text_field');
    register_setting('general', 'buzzsocial_url', 'esc_url');

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
    //error_log("✅ Full Product Metadata for Variation ID $variation_id: " . print_r($full_metadata, true));

    return [
        'subscription_period' => $subscription_period,
        'subscription_interval' => $subscription_interval,
        'full_metadata' => $full_metadata, // Include all metadata in the return value
    ];
}

/**
 * Utility function for making cURL requests.
 *
 * @param string $url The API endpoint URL.
 * @param string $method The HTTP method (GET, POST, PUT, DELETE).
 * @param array|null $data The data to send in the request body (for POST/PUT).
 * @param array $headers Optional headers for the request.
 * @param int $timeout Timeout in seconds for the request.
 * @return array The API response, HTTP code, and any errors.
 */
function make_curl_request($url, $method = 'GET', $data = null, $headers = [], $timeout = 30) {
    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        log_error("Invalid URL provided: $url");
        return ['response' => null, 'http_code' => 0, 'error' => 'Invalid URL'];
    }

    $curl = curl_init();
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYHOST => $_ENV['CURL_SSL_VERIFYHOST'] ?? 0, // Disable SSL verification for local testing
        CURLOPT_SSL_VERIFYPEER => $_ENV['CURL_SSL_VERIFYPEER'] ?? 0, // Disable SSL verification for local testing
    ];

    if (!empty($data)) {
        $options[CURLOPT_POSTFIELDS] = is_array($data) ? http_build_query($data) : $data;
    }

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);

    if ($error) {
        log_error("cURL Error: $error");
    }

    return ['response' => $response, 'http_code' => $http_code, 'error' => $error];
}

// Include the AffiliateWP core DB class
require_once __DIR__ . '/../affiliate-wp/includes/abstracts/class-db.php'; // Adjust the path if necessary

// Include the Affiliate_WP_Referrals_DB class
require_once __DIR__ . '/../affiliate-wp/includes/class-referrals-db.php';

function bluecrown_affiliatewp_post_checkout_verification($order_id) {
    if (!$order_id) return;

    // Retrieve WooCommerce order
    $order = wc_get_order($order_id);
    if (!$order) return;

    global $wpdb;

    // Step 1: Get customer_id from order metadata
    $customer_id = $order->get_customer_id() ?: get_current_user_id();
    if (!$customer_id) {
        error_log("❌ Unable to determine the customer ID.");
        return;
    }

    // Step 2: Fetch affwp_customer_id from wp_affiliate_wp_customers table
    $affwp_customer_id = $wpdb->get_var($wpdb->prepare(
        "SELECT customer_id FROM wp_affiliate_wp_customers WHERE user_id = %d",
        $customer_id
    ));

    if (!$affwp_customer_id || $affwp_customer_id <= 0) {
        error_log("❌ Valid AffiliateWP Customer ID not found for Customer ID $customer_id. Skipping record creation.");
        return;
    }

    // Step 3: Retrieve the referring affiliate ID
    $referring_affiliate_id = $wpdb->get_var($wpdb->prepare(
        "SELECT meta_value FROM wp_affiliate_wp_customermeta WHERE affwp_customer_id = %d",
        $affwp_customer_id
    ));

    if (!$referring_affiliate_id) {
        error_log("❌ Referring Affiliate ID not found for Order #$order_id.");
        return;
    }

    // Step 4: Check if the affiliate has already been credited
    $referrals_db = new Affiliate_WP_Referrals_DB(); // Instantiate the referrals database class
    $existing_referral = $referrals_db->get_by('reference', $order_id);

    if ($existing_referral) {
        error_log("✅ Affiliate ID $referring_affiliate_id has already been credited for Order #$order_id.");
        return;
    }

    // Step 5: Validate if the lifetime_customer record already exists
    $lifetime_customer_id = $wpdb->get_var($wpdb->prepare(
        "SELECT lifetime_customer_id FROM wp_affiliate_wp_lifetime_customers WHERE affwp_customer_id = %d AND affiliate_id = %d",
        $affwp_customer_id,
        $referring_affiliate_id
    ));

    if (!$lifetime_customer_id) {
        // Create a new lifetime_customer record
        $lifetime_customer_data = [
            'affwp_customer_id' => $affwp_customer_id,
            'affiliate_id' => $referring_affiliate_id,
            'date_created' => current_time('mysql'),
        ];

        $lifetime_customer_added = affiliate_wp_lifetime_commissions()->lifetime_customers->add($lifetime_customer_data);

        if (!$lifetime_customer_added) {
            error_log("❌ Failed to create Lifetime Customer record for affwp_customer_id $affwp_customer_id.");
            return;
        }
    }

    // Step 6: Calculate commission based on product or default rates
    $commission = 0;
    $products_meta = []; // Array to store product details for the referral

    foreach ($order->get_items() as $item) {
        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);
        $product_rate_type = get_post_meta($product_id, '_affwp_woocommerce_product_rate_type', true);
        $product_rate = get_post_meta($product_id, '_affwp_woocommerce_product_rate', true);

        $product_price = $item->get_total();
        $referral_amount = 0;

        if (is_numeric($product_rate) && $product_rate >= 0) {
            if ($product_rate_type === 'percentage') {
                $referral_amount = ($product_price * $product_rate / 100);
            } elseif ($product_rate_type === 'flat') {
                $referral_amount = $product_rate;
            }
        } else {
            $default_rate_type = get_option('affwp_settings')['referral_rate_type'];
            $default_rate = get_option('affwp_settings')['referral_rate'];

            if (is_numeric($default_rate) && $default_rate >= 0) {
                if ($default_rate_type === 'percentage') {
                    $referral_amount = ($product_price * $default_rate / 100);
                } elseif ($default_rate_type === 'flat') {
                    $referral_amount = $default_rate;
                }
            }
        }

        $commission += $referral_amount;

        // Add product details to the products_meta array
        $products_meta[] = [
            'name' => $product->get_name(),
            'id' => $product_id,
            'price' => $product_price,
            'referral_amount' => $referral_amount,
        ];
    }

    // Step 7: Add referral for the affiliate
    if ($commission > 0) {
        // Retrieve the parent ID (if applicable)
        $parent_id = $order->get_parent_id(); // WooCommerce provides this method to get the parent order ID

        // Add the lifetime_referral flag to the custom data if lifetime_customer_id exists
        $custom_data = [];

        if ($lifetime_customer_id) {
            $custom_data['lifetime_referral'] = true;
        }

        $referral_data = [
            'affiliate_id' => $referring_affiliate_id,
            'customer_id' => $affwp_customer_id,
            'parent_id' => $parent_id ?: 0, // Use 0 if no parent ID exists
            'description' => '',
            'status' => 'unpaid',
            'amount' => $commission,
            'currency' => $order->get_currency(),
            'context' => 'woocommerce',
            'campaign' => '', // Optional: Add campaign data if available
            'reference' => $order_id,
            'products' => serialize($products_meta), // Serialize product data
            'date' => current_time('mysql'),
            'custom' => maybe_serialize($custom_data), // Include the lifetime_referral flag
        ];
        
        // Generate the description
        foreach ($order->get_items() as $item) {
            $product = wc_get_product($item->get_product_id());
            $parent_name = $product->get_name(); // Parent product name
            $variation_name = $item->get_name(); // Variation product name
            $variation_id = $item->get_variation_id(); // Variation ID
    
            // Format the description
            $referral_data['description'] .= sprintf(
                '%s - %s (Variation ID %d), ',
                $parent_name,
                $variation_name,
                $variation_id
            );
        }
    
        // Remove trailing comma and space
        $referral_data['description'] = rtrim($referral_data['description'], ', ');

        $referral_id = $referrals_db->add($referral_data);

        if ($referral_id) {
            error_log("✅ Commission of $commission credited to Affiliate ID $referring_affiliate_id for Order #$order_id.");

            // Step 8: Update unpaid earnings for the affiliate
            $updated_unpaid_earnings = affwp_increase_affiliate_unpaid_earnings($referring_affiliate_id, $commission);
            if ($updated_unpaid_earnings !== false) {
                error_log("✅ Unpaid earnings updated for Affiliate ID $referring_affiliate_id. New Unpaid Earnings: $updated_unpaid_earnings.");
            } else {
                error_log("❌ Failed to update unpaid earnings for Affiliate ID $referring_affiliate_id.");
            }
        } else {
            error_log("❌ Failed to credit commission for Affiliate ID $referring_affiliate_id for Order #$order_id.");
        }
    }
}

/**
 * Authenticate to WoWonder and retrieve an access token.
 *
 * @param string $api_url The WoWonder API base URL.
 * @param string $server_key The server key for authentication.
 * @param string $username The WoWonder admin username.
 * @param string $password The WoWonder admin password.
 * @return string|null The access token or null on failure.
 */
function authenticate_to_wowonder($api_url, $server_key, $username, $password) {
    $url = "$api_url/auth";
    $data = [
        'server_key' => $server_key,
        'username' => $username,
        'password' => $password,
    ];
    $headers = ['Content-Type: application/x-www-form-urlencoded'];

    $response = make_curl_request($url, 'POST', $data, $headers);

    if ($response['http_code'] !== 200) {
        log_error("WoWonder Authentication Failed: HTTP Code {$response['http_code']}. Response: {$response['response']}");
        return null;
    }

    $auth_data = json_decode($response['response'], true);
    if (empty($auth_data['api_status']) || $auth_data['api_status'] != 200) {
        log_error("WoWonder Authentication Failed: " . print_r($auth_data, true));
        return null;
    }

    return $auth_data['access_token'] ?? null;
}

/**
 * Fetch user data from WoWonder.
 *
 * @param string $api_url The WoWonder API base URL.
 * @param string $access_token The access token for authentication.
 * @param string $server_key The server key for authentication.
 * @param string $user_id The WoWonder user ID.
 * @return array|null The user data or null on failure.
 */
function fetch_wowonder_user_data($api_url, $access_token, $server_key, $user_id) {
    $url = "$api_url/get-user-data?access_token=$access_token";
    $data = [
        'server_key' => $server_key,
        'user_id' => $user_id,
        'fetch' => 'user_data',
    ];
    $headers = ['Content-Type: application/x-www-form-urlencoded'];

    $response = make_curl_request($url, 'POST', $data, $headers);

    if ($response['http_code'] !== 200) {
        log_error("Failed to fetch user data from WoWonder: HTTP Code {$response['http_code']}. Response: {$response['response']}");
        return null;
    }

    $user_data = json_decode($response['response'], true);
    if (empty($user_data['api_status']) || $user_data['api_status'] != 200) {
        log_error("Failed to fetch user data from WoWonder: " . print_r($user_data, true));
        return null;
    }

    return $user_data['user_data'] ?? null;
}

/**
 * Create a subscription using the WooCommerce API.
 *
 * @param string $api_url The WooCommerce API base URL.
 * @param string $consumer_key The WooCommerce consumer key.
 * @param string $consumer_secret The WooCommerce consumer secret.
 * @param array $subscription_data The subscription data to send.
 * @return array|null The subscription response or null on failure.
 */
function create_woocommerce_subscription($api_url, $consumer_key, $consumer_secret, $subscription_data) {
    $url = "$api_url/subscriptions";
    $headers = [
        'Authorization: Basic ' . base64_encode("$consumer_key:$consumer_secret"),
        'Content-Type: application/json',
    ];

    // Make the cURL request
    $response = make_curl_request($url, 'POST', json_encode($subscription_data), $headers);

    // Check if the response contains a valid HTTP code
    if (empty($response) || !isset($response['http_code'])) {
        log_error("❌ cURL Error: Invalid response structure. Response: " . print_r($response, true));
        return null;
    }

    // Handle non-201 HTTP codes
    if ($response['http_code'] !== 201) {
        log_error("❌ Failed to create subscription. HTTP Code: {$response['http_code']}. Response: {$response['response']}");
        return null;
    }

    // Decode the JSON response
    $decoded_response = json_decode($response['response'], true);

    // Validate the decoded response
    if (empty($decoded_response) || !is_array($decoded_response)) {
        log_error("❌ Failed to decode subscription response. Raw Response: {$response['response']}");
        return null;
    }

    return $decoded_response;
}

/**
 * Retry logic with exponential backoff.
 *
 * @param callable $callback The function to retry.
 * @param int $max_retries The maximum number of retries.
 * @param int $base_delay The base delay in seconds.
 * @param int $max_delay The maximum delay in seconds.
 * @return mixed The result of the callback or null on failure.
 */
function retry_with_backoff($callback, $max_retries = 5, $base_delay = 5, $max_delay = 80) {
    $retry_count = 0;

    while ($retry_count < $max_retries) {
        $result = $callback();
        if ($result !== null) {
            return $result;
        }

        $retry_count++;
        $delay = min($base_delay * (2 ** $retry_count), $max_delay);
        sleep($delay);
    }

    return null;
}

/**
 * Utility function for logging errors.
 *
 * @param string $message The error message to log.
 */
function log_error($message) {
    if ($_ENV['ENABLE_LOGGING'] ?? true) {
        error_log("❌ $message");
    }
}
?>