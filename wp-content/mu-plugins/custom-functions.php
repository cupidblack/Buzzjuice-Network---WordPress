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
    wp_safe_redirect('https://buzzjuice.net/streams/logout/?cabin=home');
    exit(); // Ensure no further code execution after redirection
}
add_action('wp_logout', 'redirect_after_wp_logout');








?>