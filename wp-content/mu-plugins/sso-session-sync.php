<?php
function buzzjuice_init_sso_session() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return; // Already active
    }

    // -----------------------------
    // CONFIG
    // -----------------------------
    $session_name  = 'BUZZSESS';           // Shared cookie name for SSO
    $cookie_domain = '.buzzjuice.net';     // Shared domain for subdomains
    $new_format    = 'php_serialize';      // Preferred format going forward

    // -----------------------------
    // CONSISTENT SETTINGS
    // -----------------------------
    ini_set('session.name', $session_name);
    ini_set('session.cookie_domain', $cookie_domain);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);

    // -----------------------------
    // FUNCTION: Decode PHP-format sessions
    // -----------------------------
    $decode_php_format = function($data) {
        $result = [];
        $offset = 0;
        while ($offset < strlen($data)) {
            if (!strstr(substr($data, $offset), "|")) {
                return false;
            }
            $pos = strpos($data, "|", $offset);
            $varname = substr($data, $offset, $pos - $offset);
            $offset = $pos + 1;
            $data_value = @unserialize(substr($data, $offset));
            if ($data_value === false && serialize(false) !== substr($data, $offset, strlen(serialize(false)))) {
                return false; // unserialize failed
            }
            $result[$varname] = $data_value;
            $offset += strlen(serialize($data_value));
        }
        return $result;
    };

    // -----------------------------
    // START SESSION WITH DUAL FORMAT SUPPORT
    // -----------------------------
    $sid = !empty($_COOKIE[$session_name]) ? preg_replace('/[^a-zA-Z0-9,-]/', '', $_COOKIE[$session_name]) : null;
    $sess_file = $sid ? rtrim(ini_get('session.save_path'), '/\\') . "/sess_" . $sid : null;

    if ($sid && is_readable($sess_file)) {
        $raw_data = file_get_contents($sess_file);

        // Try PHP format first
        $decoded = $decode_php_format($raw_data);
        if ($decoded !== false) {
            ini_set('session.serialize_handler', $new_format);
            session_id($sid);
            session_start();
            $_SESSION = $decoded;
            session_write_close();
            session_start();
            return;
        }

        // Fallback to php_serialize
        ini_set('session.serialize_handler', 'php_serialize');
        session_id($sid);
        @session_start();
        return;
    }

    // If no cookie or no file, start fresh in new format
    ini_set('session.serialize_handler', $new_format);
    session_start();
}
add_action('init', 'buzzjuice_init_sso_session', 1);

// Login → set shared session keys
add_action('wp_login', function($user_login, $user) {
    buzzjuice_init_sso_session();

    $_SESSION['wp_user_id']    = (int) $user->ID;
    $_SESSION['wp_user_login'] = $user->user_login;
    $_SESSION['wo_user_id']    = (int) get_user_meta($user->ID, 'wo_user_id', true);
    $_SESSION['qd_user_id']    = (int) get_user_meta($user->ID, 'qd_user_id', true);

    // WoWonder compatibility
    $_SESSION['user_id'] = $_SESSION['wo_user_id'] ?: null;

    session_regenerate_id(true);
}, 10, 2);

// Logout → destroy shared session
add_action('wp_logout', function() {
    buzzjuice_init_sso_session();
    $_SESSION = [];
    session_destroy();
});




/**
 * Redirect after login.
 *
 * @since BuddyBoss 2.4.70
 *
 * @param string  $redirect_to The redirect destination URL.
 * @param string  $request     The requested redirect destination URL passed as a parameter.
 * @param WP_User $user        WP_User object if login was successful.
 *
 * @return mixed|string
 */
add_action( 'plugins_loaded', function() {
    // If BuddyBoss's redirect function exists
    if ( function_exists( 'bb_login_redirect' ) ) {
        // Remove BuddyBoss/BuddyPress login redirect logic
        remove_filter( 'bp_login_redirect', 'bb_login_redirect', PHP_INT_MAX );
        remove_filter( 'login_redirect', 'bb_login_redirect', PHP_INT_MAX );
        // Attach our function instead
        add_filter( 'bp_login_redirect', 'bluecrown_bb_login_redirect', PHP_INT_MAX, 3 );
        add_filter( 'login_redirect', 'bluecrown_bb_login_redirect', PHP_INT_MAX, 3 );
    }
});
function bluecrown_bb_login_redirect( $redirect_to, $request, $user ) {
    if ( $user && is_object( $user ) && is_a( $user, 'WP_User' ) ) {
        // Exclude admins.
        if ( in_array( 'administrator', $user->roles, true ) ) {
            return $redirect_to;
        }
        // Default redirect using our logic
        $redirect_to = bb_redirect_after_action( $redirect_to, $user->ID, 'login' );
    }
    // PRIORITY: redirect_to from the request
    if ( ! empty( $_REQUEST['redirect_to'] ) && is_string( $_REQUEST['redirect_to'] ) ) {
        $redirect_to = esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) );
    } else { $redirect_to = bb_redirect_after_action( $redirect_to, null, 'login' );}
    error_log( 'Login redirection URL: ' . $redirect_to );
    return $redirect_to;
}



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