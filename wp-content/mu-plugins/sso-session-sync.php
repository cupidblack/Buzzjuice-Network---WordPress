<?php
/**
 * Buzzjuice SSO session sync (WordPress side)
 * - Shared session cookie: BUZZSESS scoped to .buzzjuice.net
 * - Optional signed-cookie fallback: bz_sso (HMAC), controlled by BUZZ_SSO_SECRET
 * - Extensive error controls, debugging, and logging
 */

if (!defined('BUZZ_SESSION_NAME'))   define('BUZZ_SESSION_NAME', 'BUZZSESS');
if (!defined('BUZZ_SESSION_DOMAIN')) define('BUZZ_SESSION_DOMAIN', '.buzzjuice.net');
if (!defined('BUZZ_SESSION_TTL'))    define('BUZZ_SESSION_TTL', 900); // 15 minutes
if (!defined('BUZZ_SSO_COOKIE'))     define('BUZZ_SSO_COOKIE', 'bz_sso');
if (!defined('BUZZ_DEBUG_LOG'))      define('BUZZ_DEBUG_LOG', __DIR__ . '/buzz_sso_debug.log');

// Optional: set BUZZ_SSO_SECRET in wp-config.php for signed-cookie fallback
$__buzz_sso_secret = getenv('BUZZ_SSO_SECRET') ?: (defined('BUZZ_SSO_SECRET') ? BUZZ_SSO_SECRET : null);

// Logging helper
function bz_debug_log($msg, $extra = []) {
    $ts = date('Y-m-d H:i:s');
    $line = "[$ts] $msg";
    if (!empty($extra)) $line .= " | " . json_encode($extra, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    file_put_contents(BUZZ_DEBUG_LOG, $line."\n", FILE_APPEND);
}

// Helper for setting secure cookies with SameSite
function bz_set_cookie($name, $value, $expires = 0, $path = '/', $domain = BUZZ_SESSION_DOMAIN) {
    if (PHP_VERSION_ID >= 70300) {
        setcookie($name, $value, [
            'expires'  => $expires,
            'path'     => $path,
            'domain'   => $domain,
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    } else {
        setcookie($name, $value, $expires, $path . '; samesite=Lax', $domain, true, true);
    }
}
function bz_clear_cookie($name, $path = '/', $domain = BUZZ_SESSION_DOMAIN) {
    bz_set_cookie($name, '', time() - 3600, $path, $domain);
}
function _bz_b64url_encode($bin) {
    return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
}
function bz_sso_build_token_from_user($user_id, $login, $email, $secret) {
    $now = time();
    $payload = [
        'ver'        => 1,
        'wp_user_id' => (int) $user_id,
        'login'      => (string) $login,
        'email'      => (string) $email,
        'iat'        => $now,
        'exp'        => $now + BUZZ_SESSION_TTL,
    ];
    $json = wp_json_encode($payload);
    $sig  = hash_hmac('sha256', $json, $secret, true);
    return _bz_b64url_encode($json) . '.' . _bz_b64url_encode($sig);
}

// Session inspection utility for debugging
function bz_log_session_state($context) {
    bz_debug_log("Session dump at $context", [
        'session_id'  => session_id(),
        'session_name'=> session_name(),
        'cookie'      => $_COOKIE[BUZZ_SESSION_NAME] ?? null,
        'BUZZSESS?'   => isset($_COOKIE[BUZZ_SESSION_NAME]),
        'session_vars'=> $_SESSION,
        'server'      => [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
            'HTTPS' => $_SERVER['HTTPS'] ?? null
        ]
    ]);
}

// Start SSO session early
function buzzjuice_init_sso_session() {
    // If a session is already active but name is NOT BUZZSESS, destroy it and restart
    if (session_status() === PHP_SESSION_ACTIVE) {
        if (session_name() !== BUZZ_SESSION_NAME) {
            // Defensive: log
            bz_debug_log("Non-BUZZSESS session active during SSO. Destroying and restarting.", ['current_name'=>session_name(), 'expected'=>BUZZ_SESSION_NAME, 'sid'=>session_id()]);
            $_SESSION = [];
            @session_unset();
            @session_destroy();
        } else {
            // Already correct session, nothing to do
            bz_debug_log("BUZZSESS session already active. Skipping re-init.", ['sid'=>session_id()]);
            return;
        }
    }

    // Now safe to set ini and session_name/session_id
    @ini_set('session.name', BUZZ_SESSION_NAME);
    @ini_set('session.cookie_domain', BUZZ_SESSION_DOMAIN);
    @ini_set('session.cookie_samesite', 'Lax');
    @ini_set('session.cookie_secure', 1);
    @ini_set('session.cookie_httponly', 1);
    @ini_set('session.use_only_cookies', 1);
    @ini_set('session.use_strict_mode', 1);
    @ini_set('session.serialize_handler', 'php_serialize');

    if (!empty($_COOKIE[BUZZ_SESSION_NAME])) {
        $sid = preg_replace('/[^a-zA-Z0-9,-]/', '', $_COOKIE[BUZZ_SESSION_NAME]);
        bz_debug_log("buzzjuice_init_sso_session: Resuming session with SID", ['sid'=>$sid]);
        if ($sid) {
            session_id($sid);
        }
    }
    session_name(BUZZ_SESSION_NAME);
    @session_start();
    bz_log_session_state("init");
}
add_action('init', 'buzzjuice_init_sso_session', 1);

// On login, set session and optional fallback cookie
add_action('wp_login', function($user_login, $user) use ($__buzz_sso_secret) {
    buzzjuice_init_sso_session();

    $_SESSION['wp_user_id']     = (int) $user->ID;
    $_SESSION['wp_user_login']  = (string) $user->user_login;
    $_SESSION['wp_user_email']  = (string) $user->user_email;
    $_SESSION['wo_user_id']     = (int) get_user_meta($user->ID, 'wo_user_id', true);
    $_SESSION['qd_user_id']     = (int) get_user_meta($user->ID, 'qd_user_id', true);
    $_SESSION['wp_sso_iat']     = time();
    $_SESSION['wp_sso_exp']     = time() + BUZZ_SESSION_TTL;
    if (!empty($_SESSION['wo_user_id'])) {
        $_SESSION['user_id'] = (int) $_SESSION['wo_user_id'];
    } else {
        unset($_SESSION['user_id']);
    }

    $login_data = [
        'wp_user_id'     => $_SESSION['wp_user_id'],
        'wp_user_login'  => $_SESSION['wp_user_login'],
        'wp_user_email'  => $_SESSION['wp_user_email'],
        'wo_user_id'     => $_SESSION['wo_user_id'],
        'qd_user_id'     => $_SESSION['qd_user_id'],
        'wp_sso_iat'     => $_SESSION['wp_sso_iat'],
        'wp_sso_exp'     => $_SESSION['wp_sso_exp'],
        'session_id'     => session_id(),
    ];
    bz_debug_log("wp_login: Session set", $login_data);

    @session_regenerate_id(true);

    // Optional signed-cookie fallback
    if (!empty($__buzz_sso_secret)) {
        $token  = bz_sso_build_token_from_user($user->ID, $user->user_login, $user->user_email, $__buzz_sso_secret);
        $expiry = time() + BUZZ_SESSION_TTL;
        bz_set_cookie(BUZZ_SSO_COOKIE, $token, $expiry);
        bz_debug_log("wp_login: Signed SSO cookie set", [
            'cookie_name' => BUZZ_SSO_COOKIE,
            'expiry'      => $expiry,
            'token_part'  => substr($token, 0, 32) . '...'
        ]);
    }

    bz_log_session_state("wp_login");

    // *** THE CRITICAL LINE ***
    session_write_close();
}, 10, 2);

add_action('check_admin_referer', 'logout_without_confirm', 10, 2);
function logout_without_confirm($action, $result) {
    if ($action == "log-out" && !isset($_GET['_wpnonce'])) {
        $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : home_url('/');
        $location = str_replace('&amp;', '&', wp_logout_url($redirect_to));
        header("Location: " . $location);
        exit;
    }
}

// On logout, destroy session and cookies, log and clean up
add_action('wp_logout', function() {
    buzzjuice_init_sso_session();
    bz_debug_log("wp_logout: destroying session", ['sid'=>session_id()]);
    $_SESSION = [];
    @session_unset();
    @session_destroy();
    bz_clear_cookie(BUZZ_SESSION_NAME);
    bz_clear_cookie(BUZZ_SSO_COOKIE);
    bz_debug_log("wp_logout: cookies cleared", $_COOKIE);
    wp_safe_redirect('https://buzzjuice.net/streams/logout/?cabin=home');
//    exit;
}, 10);

// Utility: for admin/debug, dump session info (never expose publicly in prod!)
if (isset($_GET['bz_sso_debug']) && current_user_can('manage_options')) {
    header('Content-Type: text/plain');
    echo "BUZZSESS session info (WordPress)\n";
    print_r([
        'session_id'   => session_id(),
        'session_name' => session_name(),
        'session_vars' => $_SESSION,
        'cookies'      => $_COOKIE,
        'server'       => [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
        ],
    ]);
    exit;
}

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
// BuddyBoss redirect compatibility
add_action('plugins_loaded', function() {
    if (function_exists('bb_login_redirect')) {
        remove_filter('bp_login_redirect', 'bb_login_redirect', PHP_INT_MAX);
        remove_filter('login_redirect', 'bb_login_redirect', PHP_INT_MAX);
        add_filter('bp_login_redirect', 'bluecrown_bb_login_redirect', PHP_INT_MAX, 3);
        add_filter('login_redirect', 'bluecrown_bb_login_redirect', PHP_INT_MAX, 3);
    }
});
function bluecrown_bb_login_redirect($redirect_to, $request, $user) {
    if ($user && is_object($user) && is_a($user, 'WP_User')) {
        if (in_array('administrator', (array) $user->roles, true)) {
            return $redirect_to;
        }
        if (function_exists('bb_redirect_after_action')) {
            $redirect_to = bb_redirect_after_action($redirect_to, $user->ID, 'login');
        }
    }
    if (!empty($_REQUEST['redirect_to']) && is_string($_REQUEST['redirect_to'])) {
        $redirect_to = esc_url_raw(wp_unslash($_REQUEST['redirect_to']));
    } else {
        if (function_exists('bb_redirect_after_action')) {
            $redirect_to = bb_redirect_after_action($redirect_to, null, 'login');
        }
    }
    return $redirect_to;
}