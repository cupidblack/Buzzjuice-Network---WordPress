<?php
// Central SSO logout proxy
// Usage: https://buzzjuice.net/shared/sso-logout.php?sso_secret=<secret>&from=<platform>
// Accepts sso_secret via GET/POST or Authorization: Bearer <secret> header.

declare(strict_types=1);

$baseDir = realpath(__DIR__ . '') ?: __DIR__;

// Load env & helpers (db_helpers loads DotEnv and BUZZ_SSO_SECRET)
if (!file_exists(__DIR__ . '/db_helpers.php')) {
    header('HTTP/1.1 500 Internal Server Error');
    echo "Missing shared/db_helpers.php";
    exit;
}
require_once __DIR__ . '/db_helpers.php';

function _sso_get_request_secret() {
    $secret = null;
    if (!empty($_REQUEST['sso_secret'])) $secret = $_REQUEST['sso_secret'];
    if (!$secret && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) $secret = trim($m[1]);
    }
    // JSON body support
    if (!$secret && in_array($_SERVER['CONTENT_TYPE'] ?? '', ['application/json', 'application/json; charset=utf-8'])) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (is_array($data) && !empty($data['sso_secret'])) $secret = $data['sso_secret'];
    }
    return $secret;
}

$provided = _sso_get_request_secret();
if (!defined('BUZZ_SSO_SECRET') || !BUZZ_SSO_SECRET || !$provided || !hash_equals((string)BUZZ_SSO_SECRET, (string)$provided)) {
    // Secret missing/invalid
    header('HTTP/1.1 403 Forbidden');
    echo "Forbidden";
    exit;
}

// We have a valid request. Bootstrap WordPress so wp_logout() and mu-plugins are available.
// wp-load.php should be one level up from shared/
$wp_load = realpath(__DIR__ . '/../wp-load.php') ?: (__DIR__ . '/../wp-load.php');
if (file_exists($wp_load)) {
    // Prevent WP from sending extra output
    define('WP_USE_THEMES', false);
    require_once $wp_load;
}

// If wp_logout is available and WordPress user still logged in, call it to run WP logout hooks.
// This will invoke sso-session-sync.php's wp_logout hooked function (if present) to remove shadow, cookie etc.
if (function_exists('wp_logout')) {
    // call wp_logout() but avoid invoking twice if we detect a 'from_wp' flag
    if (empty($_REQUEST['from_wp'])) {
        @wp_logout();
    }
}

// Perform SSO cookie + shadow cleanup as defensive measure (mu-plugin functions available if WP loaded)
if (function_exists('bz_remove_shadow_session')) {
    // If session is active, try remove by current session id
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_start();
    }
    try {
        bz_remove_shadow_session(session_id());
    } catch (Throwable $e) {
        // ignore
    }
}

// Expire buzz_sso cookie for the shared domain
if (PHP_VERSION_ID >= 70300) {
    setcookie('buzz_sso', '', ['expires'=>time()-3600,'path'=>'/','domain'=>'.buzzjuice.net','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
} else {
    setcookie('buzz_sso', '', time()-3600, '/', '.buzzjuice.net', true, true);
}

// Also attempt to clear WoWonder/QuickDate client cookies on the shared domain
if (PHP_VERSION_ID >= 70300) {
    setcookie('user_id', '', ['expires'=>time()-3600,'path'=>'/','domain'=>'.buzzjuice.net','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
    setcookie('JWT', '', ['expires'=>time()-3600,'path'=>'/','domain'=>'.buzzjuice.net','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
} else {
    setcookie('user_id', '', time()-3600, '/', '.buzzjuice.net', true, true);
    setcookie('JWT', '', time()-3600, '/', '.buzzjuice.net', true, true);
}

// Redirect next to the WoWonder logout route which will in turn finish the platform chain
// Use absolute URL for clarity. Use "from" to indicate central.
$wo_logout = 'https://buzzjuice.net/streams/logout/?cabin=home&from=sso';
header('Location: ' . $wo_logout);
exit;