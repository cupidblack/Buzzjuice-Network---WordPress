<?php
/**
 * Plugin Name: Force Email Activation for All Registrations
 * Description: Enforce BuddyBoss/BuddyPress activation for all registrations (API, frontend, etc.), block logins unless activated, and resend activation emails automatically.
 * Version: 1.0
 */

// 1. Custom REST endpoint for registration (forces BuddyBoss activation flow)
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/register', [
        'methods'  => 'POST',
        'callback' => 'custom_api_register_user',
        'permission_callback' => '__return_true', // Secure this in production!
    ]);
});

/**
 * Force all registrations (API or otherwise) to go through BuddyBoss/BuddyPress activation.
 */
function custom_api_register_user($request) {
    $params   = $request->get_json_params();
    $username = sanitize_user($params['username'] ?? '');
    $password = $params['password'] ?? '';
    $email    = sanitize_email($params['email'] ?? '');

    if (empty($username) || empty($password) || empty($email)) {
        return new WP_REST_Response(['error' => 'Missing required fields.'], 400);
    }

    if (!is_email($email)) {
        return new WP_REST_Response(['error' => 'Invalid email address.'], 400);
    }

    if (username_exists($username) || email_exists($email)) {
        return new WP_REST_Response(['error' => 'Username or email already exists.'], 409);
    }

    if (!function_exists('bp_core_signup_user')) {
        return new WP_REST_Response(['error' => 'BuddyBoss/BuddyPress not available.'], 500);
    }

    $meta = []; // Optional: pass additional metadata if needed
    $signup_result = bp_core_signup_user($username, $password, $email, $meta);

    if (is_wp_error($signup_result)) {
        return new WP_REST_Response(['error' => $signup_result->get_error_message()], 500);
    }

    return new WP_REST_Response([
        'success' => true,
        'message' => 'Account created. Please check your email to activate your account.'
    ], 200);
}

// 2. Prevent login for unactivated users and auto-resend activation email
add_filter('authenticate', function ($user, $username, $password) {
    if (is_wp_error($user) || empty($username)) return $user;

    if (!function_exists('BP_Signup::get')) return $user;
    $signups = BP_Signup::get(['user_login' => $username]);
    if (!empty($signups['signups']) && !$signups['signups'][0]->active) {
        // Resend activation email
        bp_core_signup_send_activation_key($signups['signups'][0]->user_email);

        return new WP_Error(
            'not_activated',
            __('Your account is not active. A new activation email has been sent.', 'force-activation')
        );
    }

    return $user;
}, 30, 3);

// 3. Delete unauthorized users created outside of BuddyBoss (e.g., via wp_create_user), unless in admin
/*
add_action('user_register', function ($user_id) {
    if (is_admin()) return; // Allow backend/admin user creation

    $user = get_userdata($user_id);
    if (!$user) return;

    // If user was created without bp_core_signup_user, delete them
    if (class_exists('BP_Signup')) {
        $signup = BP_Signup::get(['user_login' => $user->user_login]);
        if (empty($signup['signups'])) {
            wp_delete_user($user_id);
            error_log("[Activation Enforcement] Deleted unauthorized user registration: $user_id");
        }
    }
}, 20); 
*/

// 4. (Optional) Customize activation email, e.g. for logging or notification
add_filter('bp_core_signup_send_activation_key', function ($user_email) {
    // Hook for custom notification, logging, etc.
    return $user_email;
});
