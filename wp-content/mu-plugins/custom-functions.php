<?php

add_action('check_admin_referer', 'logout_without_confirm', 10, 2);
function logout_without_confirm($action, $result) {
    if ($action == "log-out" && !isset($_GET['_wpnonce'])) {
        $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : home_url('/');
        $location = str_replace('&amp;', '&', wp_logout_url($redirect_to));
        header("Location: " . $location);
        exit;
    }
}




function redirect_after_wp_logout() {
    wp_safe_redirect('https://127.0.0.1/buzzjuice.net/streams/logout/?cabin=home');
    exit(); // Ensure no further code execution after redirection
}
add_action('wp_logout', 'redirect_after_wp_logout');




/*Blue Crown R&D: WordPress REST API*/
add_filter( 'rest_user_query', 'prefix_remove_has_published_posts_from_wp_api_user_query', 10, 2 );
/**
 * Removes `has_published_posts` from the query args so even users who have not
 * published content are returned by the request.
 *
 * @see https://developer.wordpress.org/reference/classes/wp_user_query/
 *
 * @param array           $prepared_args Array of arguments for WP_User_Query.
 * @param WP_REST_Request $request       The current request.
 *
 * @return array
 */
function prefix_remove_has_published_posts_from_wp_api_user_query( $prepared_args, $request ) {
	unset( $prepared_args['has_published_posts'] );

	return $prepared_args;
}

function expose_user_roles_in_rest($response, $user, $request) {
    $response->data['roles'] = $user->roles; // Add roles field
    return $response;
}
add_filter('rest_prepare_user', 'expose_user_roles_in_rest', 10, 3);

function add_email_to_rest_api($response, $user, $request) {
    if (!empty($user->user_email)) {
        $response->data['email'] = $user->user_email;
    }
    return $response;
}
add_filter('rest_prepare_user', 'add_email_to_rest_api', 10, 3);





// Add WoWonder username to WooCommerce order metadata
add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    if (isset($_GET['username'])) {
        update_post_meta($order_id, '_wowonder_username', sanitize_text_field($_GET['username']));
    }
});




// Disable Admin Features: WooCommerce can load additional scripts in the admin dashboard.
// add_filter('woocommerce_admin_disabled', '__return_true');





?>