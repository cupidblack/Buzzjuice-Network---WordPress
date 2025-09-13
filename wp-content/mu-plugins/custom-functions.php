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
    			.login h1 a,
			.login .wp-login-logo a {
			max-height: 64px;
    }
    
    body.login.login-split-page #login h1 a {
    margin-left: auto;
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


// BuzzJuice Message Sync Triggers BuddyBoss → WoWonder message sync on conversation view.
add_action('bp_messages_screen_conversation', function() {
    $user_id = get_current_user_id();
    $recipient_id = bp_displayed_user_id();
    if ($user_id && $recipient_id && $user_id !== $recipient_id) {
        require_once WP_CONTENT_DIR . '/mu-plugins/wp_wo_messages_sync.php';
        if (function_exists('wp_wo_sync_messages')) {
            wp_wo_sync_messages($user_id, $recipient_id);
        }
    }
});



/**
 * @snippet       Disable "You cannot add another ___" Woo Error Message
 * @how-to        businessbloomer.com/woocommerce-customization
 * @author        Rodolfo Melogli, Business Bloomer
 * @compatible    WooCommerce 8
 * @community     https://businessbloomer.com/club/
 */
 
add_filter( 'woocommerce_add_to_cart_sold_individually_found_in_cart', 'bbloomer_no_message_if_already_found_in_cart' );
 
function bbloomer_no_message_if_already_found_in_cart( $found ) {
   if ( $found ) {
      throw new Exception();
   }
   return $found;
}



add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        // Use the absolute path to the main plugin file
        $plugin_file = WP_PLUGIN_DIR . '/learndash-woocommerce/learndash_woocommerce.php';
        if ( file_exists( $plugin_file ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                $plugin_file,
                true
            );
        }
    }
} );

/*
add_action('login_init', function () {
    if ( is_user_logged_in() ) {
        wp_safe_redirect( home_url('/') );
        exit;
    }
});*/