<?php
// Step 1: On Login, set shared session
add_action('wp_login', function($user_login, $user) {
    ini_set('session.cookie_domain', '.buzzjuice.net');
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();

    //$wp_user_id = get_current_user_id();
    //$wo_user_id = get_user_meta($wp_user_id, 'wo_user_id', true);
    //$user_login = get_user_meta($wp_user_id, 'user_login', true);

    // WordPress user info
    $_SESSION['wp_user_id'] = $user->ID;
    $_SESSION['wp_user_login'] = $user->user_login;

    // If you have a WoWonder mapping, set it here
    $wo_user_id = get_user_meta($user->ID, 'wo_user_id', true);
    if ($wo_user_id) {
        $_SESSION['user_id'] = $wo_user_id;
    } else {
        $_SESSION['user_id'] = '';
    }

    session_regenerate_id(true); // Security
}, 10, 2);

// Step 2: On Logout, destroy session system-wide
add_action('wp_logout', function() {
    ini_set('session.cookie_domain', '.buzzjuice.net');
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION = [];
    session_destroy();
});



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
    wp_safe_redirect('https://buzzjuice.net/streams/logout/?cabin=home');
    exit(); // Ensure no further code execution after redirection
}
add_action('wp_logout', 'redirect_after_wp_logout');