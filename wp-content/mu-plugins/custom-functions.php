<?php
function default_sort_users( $args ) {
    if ( empty( $args['orderby'] ) ) {
        $args['orderby'] = 'user_registered';
        $args['order'] = 'desc'; 
    }
    return $args;
}

add_filter( 'users_list_table_query_args', 'default_sort_users' );



function the_dramatist_custom_login_css() {
    echo '<style type="text/css"> 
    
    .register-section-logo {
        display: inline-flex !important;
        justify-content: center !important;
    }
    
    .activate-section-logo {
        display: inline-flex;
        justify-content: center;
    }
    
    h1.wp-login-logo {
        justify-self: center;
    }
    
    .login.bb-login #login > h1 > a {
        height: 1;
    }
    
    </style>';
}
add_action('login_head', 'the_dramatist_custom_login_css');
add_action( 'login_enqueue_scripts', 'the_dramatist_custom_login_css', 10 );
add_action( 'admin_enqueue_scripts', 'the_dramatist_custom_login_css', 10 );



function overrule_webhook_disable_limit( $number ) {
    return 999999999999; //very high number hopefully you'll never reach.
}
add_filter( 'woocommerce_max_webhook_delivery_failures', 'overrule_webhook_disable_limit' );



/*Blue Crown R&D: WordPress REST API*/
/*add_filter( 'rest_user_query', 'prefix_remove_has_published_posts_from_wp_api_user_query', 10, 2 );*/
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
/*function prefix_remove_has_published_posts_from_wp_api_user_query( $prepared_args, $request ) {
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
*/



// Add WoWonder username to WooCommerce order metadata
/*add_action('woocommerce_checkout_update_order_meta', function ($order_id) {
    if (isset($_GET['username'])) {
        update_post_meta($order_id, '_wowonder_username', sanitize_text_field($_GET['username']));
    }
});
*/



// Disable Admin Features: WooCommerce can load additional scripts in the admin dashboard.
// add_filter('woocommerce_admin_disabled', '__return_true');