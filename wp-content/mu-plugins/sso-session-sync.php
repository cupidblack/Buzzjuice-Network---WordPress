<?php
/**
 * Buzzjuice SSO (WordPress side)
 * UPDATED — Aug 2025
 *
 * Goals:
 * - Use default PHPSESSID for WP internals (no BUZZSESS namespace)
 * - Primary SSO method = signed cookie `buzz_sso`
 * - WoWonder and QuickDate read `buzz_sso` (signed JSON payload)
 * - Include wp_user_id, wp_user_login, wp_user_email, wo_user_id, qd_user_id
 * - Safe against tampering (HMAC)
 */
require_once __DIR__ . '/../../data/db_helpers.php';
/* -------------------------------------------------------------------------- */
/* Config */
/* -------------------------------------------------------------------------- */
if (!defined('BUZZ_SSO_COOKIE')) define('BUZZ_SSO_COOKIE', 'buzz_sso');
if (!defined('BUZZ_SSO_TTL'))    define('BUZZ_SSO_TTL', 900); // 15 minutes
if (!defined('BUZZ_SSO_DEBUG'))  define('BUZZ_SSO_DEBUG', false);
if (!defined('BUZZ_DEBUG_LOG'))  define('BUZZ_DEBUG_LOG', __DIR__ . '/buzz_sso_debug.log');
if (!defined('BUZZ_COOKIE_DOMAIN')) define('BUZZ_COOKIE_DOMAIN', '.buzzjuice.net');

// Secret shared between WP, WoWonder, QuickDate (set in wp-config.php or ENV)
$__buzz_sso_secret = getenv('BUZZ_SSO_SECRET') ?: (defined('BUZZ_SSO_SECRET') ? BUZZ_SSO_SECRET : null);

/* -------------------------------------------------------------------------- */
/* Utilities */
/* -------------------------------------------------------------------------- */
function bz_debug_log($msg, $extra = []) {
    if (!BUZZ_SSO_DEBUG) return;
    $ts = date('Y-m-d H:i:s');
    $line = "[$ts] $msg";
    if (!empty($extra)) $line .= ' | ' . json_encode($extra, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @file_put_contents(BUZZ_DEBUG_LOG, $line . "\n", FILE_APPEND);
}

function _bz_b64url_encode($bin) {
    return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
}
function _bz_b64url_decode($str) {
    return base64_decode(strtr($str, '-_', '+/'));
}

function bz_sso_build_token($data, $secret) {
    $json = wp_json_encode($data);
    $sig  = hash_hmac('sha256', $json, (string) $secret, true);
    return _bz_b64url_encode($json) . '.' . _bz_b64url_encode($sig);
}

function bz_sso_verify_token($token, $secret) {
    $parts = explode('.', $token, 2);
    if (count($parts) !== 2) return false;
    list($b64json, $b64sig) = $parts;
    $json = _bz_b64url_decode($b64json);
    $sig  = _bz_b64url_decode($b64sig);
    $calc = hash_hmac('sha256', $json, (string) $secret, true);
    if (!hash_equals($calc, $sig)) return false;
    $payload = json_decode($json, true);
    if (!is_array($payload)) return false;
    if (empty($payload['exp']) || time() > $payload['exp']) return false;
    return $payload;
}

/* -------------------------------------------------------------------------- */
/* Build and set buzz_sso cookie */
/* -------------------------------------------------------------------------- */
function bz_sso_set_cookie($user) {
    global $__buzz_sso_secret;
    if (!$__buzz_sso_secret) return;

    $now = time();
    $payload = [
        'ver'        => 1,
        'wp_user_id' => (int) $user->ID,
        'wp_user_login' => (string) $user->user_login,
        'wp_user_email' => (string) $user->user_email,
        'wo_user_id' => (int) get_user_meta($user->ID, 'wo_user_id', true),
        'qd_user_id' => (int) get_user_meta($user->ID, 'qd_user_id', true),
        'iat'        => $now,
        'exp'        => $now + BUZZ_SSO_TTL,
    ];
    $token = bz_sso_build_token($payload, $__buzz_sso_secret);

    setcookie(BUZZ_SSO_COOKIE, $token, [
        'expires'  => $payload['exp'],
        'path'     => '/',
        'domain'   => BUZZ_COOKIE_DOMAIN,
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    bz_debug_log('buzz_sso cookie set', $payload);
}

/* -------------------------------------------------------------------------- */
/* Clear buzz_sso cookie */
/* -------------------------------------------------------------------------- */
function bz_sso_clear_cookie() {
    setcookie(BUZZ_SSO_COOKIE, '', time() - 3600, '/', BUZZ_COOKIE_DOMAIN, true, true);
    bz_debug_log('buzz_sso cookie cleared');
}

/* -------------------------------------------------------------------------- */
/* Hooks */
/* -------------------------------------------------------------------------- */
add_action('wp_login', function($user_login, $user) {
    bz_sso_set_cookie($user);
}, 10, 2);

add_action('set_auth_cookie', function() {
    if (!is_user_logged_in()) return;
    $user = wp_get_current_user();
    bz_sso_set_cookie($user);
}, 10, 0);

add_action('wp_logout', function() {
    bz_sso_clear_cookie();
    wp_safe_redirect('https://buzzjuice.net/streams/logout/?cabin=home');
    exit;
}, 10);

/* -------------------------------------------------------------------------- */
/* Debug endpoint (admin only) */
/* -------------------------------------------------------------------------- */
add_action('init', function() use ($__buzz_sso_secret) {
    if (!isset($_GET['bz_sso_debug'])) return;
    if (!current_user_can('manage_options')) return;

    header('Content-Type: text/plain');
    $token = $_COOKIE[BUZZ_SSO_COOKIE] ?? null;
    $parsed = $token && $__buzz_sso_secret ? bz_sso_verify_token($token, $__buzz_sso_secret) : null;

    echo "buzz_sso cookie debug\n";
    print_r([
        'raw_cookie' => $token,
        'parsed'     => $parsed,
        'server'     => [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
        ],
    ]);
    exit;
}, 1);

/* -------------------------------------------------------------------------- */
/* BuddyBoss login redirect compatibility (unchanged) */
/* -------------------------------------------------------------------------- */
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

/* -------------------------------------------------------------------------- */
/* Optional: streamline logout link without confirm (unchanged) */
/* -------------------------------------------------------------------------- */
add_action('check_admin_referer', 'logout_without_confirm', 10, 2);
function logout_without_confirm($action, $result) {
    if ($action === 'log-out' && !isset($_GET['_wpnonce'])) {
        $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : home_url('/');
        $location = str_replace('&amp;', '&', wp_logout_url($redirect_to));
        header('Location: ' . $location);
        exit;
    }
}
