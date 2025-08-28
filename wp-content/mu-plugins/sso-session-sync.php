<?php
/**
 * Buzzjuice SSO (WordPress side)
 * Server-side session canonicalization (PHPSESSID + php_serialize)
 * Updated: Aug 2025
 *
 * - Canonical SSO data lives in $_SESSION (not cookies)
 * - Uses default PHPSESSID with php_serialize (WoWonder & QuickDate compatible via shadow files)
 * - Signed buzz_sso cookie (HMAC) for cross-app hints only
 * - On login: destroy any existing sessions and start a fresh WP session,
 *            then canonicalize and export shadow session
 * - On logout: session_destroy() and remove shadow
 * - Exports shadow session file in php_serialize format for QuickDate/WoWonder via deterministic id
 *
 * Enhancements (Aug 2025):
 * - Also writes lightweight .ser and .json copies next to the sess_shadow file to improve
 *   cross-application pickup and defensive hydration by QuickDate.
 * - Removes the additional helper files on shadow removal.
 * - Adds extra debug logs and atomic write/rename semantics for the extra copies.
 *
 * See repository version — primary behavior unchanged: writes php_serialize payload into
 * sess_shadow_{shadow_id} with atomic write + .ser and .json copies.
 *
 * Important: QuickDate SessionStart reads the primary file (php_serialize) into
 * $_SESSION['buzz_sso_serialized'] and will only attempt unserialize() on that raw string.
 *
 * NOTE (2025-08): This variant issues a long-lived buzz_sso cookie (practically "never-expire")
 * by setting a very long expiration (10 years). The cookie still contains a signed payload
 * and is explicitly cleared on logout. Tokens accepted by other apps also check 'exp' inside
 * the signed JSON; we therefore set the token 'exp' to the same long expiry for the buzz_sso cookie.
 * Short-lived SSO password tokens (WPSSO.v1) used for the JS bridge still use BUZZ_SSO_TTL.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

require_once __DIR__ . '/../../shared/db_helpers.php';

/* Config */
if (!defined('BUZZ_SSO_COOKIE'))    define('BUZZ_SSO_COOKIE', 'buzz_sso');
if (!defined('BUZZ_SSO_TTL'))       define('BUZZ_SSO_TTL', 900);
if (!defined('BUZZ_SSO_DEBUG'))     define('BUZZ_SSO_DEBUG', true);
if (!defined('BUZZ_DEBUG_LOG'))     define('BUZZ_DEBUG_LOG', __DIR__ . '/wp_debug_buzz_sso.log');
if (!defined('BUZZ_COOKIE_DOMAIN')) define('BUZZ_COOKIE_DOMAIN', '.buzzjuice.net');
if (!defined('BUZZ_SSO_SHADOW_PATH')) define('BUZZ_SSO_SHADOW_PATH', rtrim(ABSPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'shared' . DIRECTORY_SEPARATOR . 'sso_sessions');

$__buzz_sso_secret = getenv('BUZZ_SSO_SECRET') ?: (defined('BUZZ_SSO_SECRET') ? BUZZ_SSO_SECRET : null);

/* ------------------------- Debug / Utilities --------------------------- */
function bz_debug_log($msg, $extra = []) {
    if (!defined('BUZZ_SSO_DEBUG') || !BUZZ_SSO_DEBUG) return;
    $ts = gmdate('Y-m-d H:i:s');
    $meta = [
        'session_name' => session_name(),
        'session_id'   => session_id(),
        'cookie_domain'=> BUZZ_COOKIE_DOMAIN,
    ];
    if ($extra) $meta['ctx'] = $extra;
    @file_put_contents(
        BUZZ_DEBUG_LOG,
        "[$ts] $msg | " . json_encode($meta, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . PHP_EOL,
        FILE_APPEND
    );
}

/* Long-lived expiry helper: practical "never-expire" lifetime (10 years) */
function bz_long_lived_expiry_seconds() {
    return 10 * 365 * 24 * 60 * 60; // 10 years
}

/* --------------------------- Shadow session helpers --------------------------- */
function bz_shadow_session_dir() {
    $dir = realpath(BUZZ_SSO_SHADOW_PATH) ?: BUZZ_SSO_SHADOW_PATH;
    if (!is_dir($dir)) {
        @mkdir($dir, 0750, true);
    }
    return $dir;
}

function bz_shadow_session_id($wp_sid = null) {
    static $persisted_shadow_id = null;
    if ($persisted_shadow_id) return $persisted_shadow_id;
    $wp_sid = $wp_sid ?: session_id();
    if (!$wp_sid) return null;
    $transient_key = 'buzz_shadow_sid_' . $wp_sid;
    $existing = get_transient($transient_key);
    if ($existing) {
        $persisted_shadow_id = $existing;
        return $persisted_shadow_id;
    }
    $new_shadow_id = 'shadow_' . $wp_sid;
    set_transient($transient_key, $new_shadow_id, DAY_IN_SECONDS);
    $persisted_shadow_id = $new_shadow_id;
    return $persisted_shadow_id;
}

function bz_shadow_session_path($derived_id = null) {
    $dir = bz_shadow_session_dir();
    $derived_id = $derived_id ?: bz_shadow_session_id();
    return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'sess_' . $derived_id;
}

function bz_write_shadow_session($wp_sid = null) {
    if (session_status() !== PHP_SESSION_ACTIVE) return false;
    $wp_sid = $wp_sid ?: session_id();
    if (!$wp_sid) return false;
    $shadow_id = bz_shadow_session_id($wp_sid);
    $path = bz_shadow_session_path($shadow_id);

    $shadow = [];
    $allow_keys = [
        'wp_user_id','wp_user_login','wp_user_email',
        'wo_user_id','qd_user_id','qd_ready','expected_user_id',
        'buzz_sso_last_sync','wp_php_session_id','wp_session_name'
    ];
    foreach ($allow_keys as $k) {
        if (array_key_exists($k, $_SESSION)) $shadow[$k] = $_SESSION[$k];
    }

    $payload = @serialize($shadow);
    if ($payload === false) {
        bz_debug_log('bz_write_shadow_session: serialize failed', ['shadow_id'=>$shadow_id]);
        return false;
    }

    $write_atomic = function($target_path, $contents) use ($shadow_id) {
        $tmp = $target_path . '.tmp';
        if (@file_put_contents($tmp, $contents, LOCK_EX) === false) {
            bz_debug_log('bz_write_shadow_session: write failed', ['path'=>$target_path, 'shadow_id'=>$shadow_id]);
            @unlink($tmp);
            return false;
        }
        @chmod($tmp, 0640);
        if (!@rename($tmp, $target_path)) {
            if (!@copy($tmp, $target_path) || !@unlink($tmp)) {
                bz_debug_log('bz_write_shadow_session: atomic rename/copy failed', ['tmp'=>$tmp,'target'=>$target_path,'shadow_id'=>$shadow_id]);
                @unlink($tmp);
                return false;
            }
        }
        @chmod($target_path, 0640);
        return true;
    };

    if (!$write_atomic($path, $payload)) {
        return false;
    }
    // convenience .ser copy
    $path_ser = $path . '.ser';
    if (!$write_atomic($path_ser, $payload)) {
        bz_debug_log('bz_write_shadow_session: failed to write .ser copy', ['path_ser'=>$path_ser,'shadow_id'=>$shadow_id]);
    }
    // json copy for debugging only
    $json_payload = @json_encode($shadow, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    if ($json_payload !== false) {
        $path_json = $path . '.json';
        if (!$write_atomic($path_json, $json_payload)) {
            bz_debug_log('bz_write_shadow_session: failed to write .json copy', ['path_json'=>$path_json,'shadow_id'=>$shadow_id]);
        }
    } else {
        bz_debug_log('bz_write_shadow_session: json_encode failed for shadow payload', ['shadow_id'=>$shadow_id]);
    }

    bz_debug_log('bz_write_shadow_session: shadow session written', [
        'path'=>$path,
        'path_ser'=>$path_ser,
        'path_json'=>isset($path_json) ? $path_json : null,
        'shadow_id'=>$shadow_id,
        'wp_sid'=>$wp_sid
    ]);
    return true;
}

function bz_remove_shadow_session($wp_sid = null) {
    $shadow_id = bz_shadow_session_id($wp_sid ?: session_id());
    $path = bz_shadow_session_path($shadow_id);

    $removed_any = false;
    if (is_file($path)) { @unlink($path); $removed_any = true; }
    $path_ser = $path . '.ser';
    if (is_file($path_ser)) { @unlink($path_ser); $removed_any = true; }
    $path_json = $path . '.json';
    if (is_file($path_json)) { @unlink($path_json); $removed_any = true; }

    if ($removed_any) {
        bz_debug_log('bz_remove_shadow_session: removed', ['shadow_id'=>$shadow_id, 'paths'=>[$path,$path_ser,$path_json]]);
        $transient_key = 'buzz_shadow_sid_' . ($wp_sid ?: session_id());
        delete_transient($transient_key);
        return true;
    }
    return false;
}

/* --------------------------- Session lifecycle hooks --------------------------- */
// On login: destroy any existing PHPSESSID/buzz_sso and start fresh, then canonicalize session and create new buzz_sso + shadow
add_action('wp_login', function ($user_login, \WP_User $user) {
    // Destroy any existing session + shadow + cookie; start fresh session
    if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
    $old_sid = session_id();
    bz_remove_shadow_session($old_sid);
    // Clear any existing buzz_sso cookie (we will issue a long-lived cookie below)
    setcookie(BUZZ_SSO_COOKIE, '', time() - 3600, '/', BUZZ_COOKIE_DOMAIN, true, true);
    $_SESSION = [];
    @session_unset();
    @session_destroy();

    @setcookie(session_name(), '', time() - 3600, '/', '', true, true);
    @setcookie(session_name(), '', time() - 3600, '/', BUZZ_COOKIE_DOMAIN, true, true);

    session_start();
    $new_sid = session_id();

    bz_debug_log('wp_login: destroyed old sessions', ['user'=>$user_login, 'id'=>$user->ID, 'old_sid'=>$old_sid, 'new_sid'=>$new_sid]);

    // Now sync canonical session from WP user and issue buzz_sso + shadow
    $_SESSION['wp_user_id']    = (int)$user->ID;
    $_SESSION['wp_user_login'] = (string)$user->user_login;
    $_SESSION['wp_user_email'] = (string)$user->user_email;
    $_SESSION['wo_user_id']    = (int)get_user_meta($user->ID, 'wo_user_id', true);
    $_SESSION['qd_user_id']    = (int)get_user_meta($user->ID, 'qd_user_id', true);
    $_SESSION['qd_ready']         = !empty($_SESSION['qd_user_id']);
    $_SESSION['expected_user_id'] = $_SESSION['qd_user_id'] ?: null;
    $_SESSION['buzz_sso_last_sync'] = time();
    $_SESSION['wp_php_session_id'] = session_id();
    $_SESSION['wp_session_name']   = session_name();

    // Export shadow session using stable id
    bz_write_shadow_session(session_id());

    // Issue buzz_sso cookie (long-lived)
    global $__buzz_sso_secret;
    if ($__buzz_sso_secret) {
        $now = time();
        $exp = $now + bz_long_lived_expiry_seconds();
        $payload = [
            'ver'           => 1,
            'wp_user_id'    => (int)$_SESSION['wp_user_id'],
            'wp_user_login' => (string)$_SESSION['wp_user_login'],
            'wp_user_email' => (string)$_SESSION['wp_user_email'],
            'wo_user_id'    => (int)$_SESSION['wo_user_id'],
            'qd_user_id'    => (int)$_SESSION['qd_user_id'],
            'cookie_domain' => BUZZ_COOKIE_DOMAIN,
            'session_name'  => session_name(),
            'session_id'    => session_id(),
            'handler'       => ini_get('session.serialize_handler'),
            'iat' => $now,
            'exp' => $exp,
        ];
        $json = wp_json_encode($payload);
        $sig  = hash_hmac('sha256', $json, (string)$__buzz_sso_secret, true);
        $token = rtrim(strtr(base64_encode($json), '+/', '-_'), '=') . '.' . rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');

        // Set cookie with long-lived expiry (10 years) — ensures cookie persists until explicit logout
        if (PHP_VERSION_ID >= 70300) {
            setcookie(BUZZ_SSO_COOKIE, $token, [
                'expires'  => $exp,
                'path'     => '/',
                'domain'   => BUZZ_COOKIE_DOMAIN,
                'secure'   => true,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        } else {
            setcookie(BUZZ_SSO_COOKIE, $token, $exp, '/', BUZZ_COOKIE_DOMAIN, true, true);
        }

        $_SESSION['buzz_sso_last'] = $payload;
        bz_debug_log('buzz_sso cookie issued (long-lived)', ['wp_sid'=>session_id(), 'shadow_sid'=>bz_shadow_session_id(session_id()), 'expires'=>date('c',$exp)]);
    }

    update_user_meta($user->ID, 'buzz_wp_php_sessid', session_id());

    bz_debug_log('wp_login processed', [
        'user'=>$user_login,
        'id'=>$user->ID,
        'wp_sid'=>session_id(),
        'shadow_sid'=>bz_shadow_session_id(session_id())
    ]);
}, 10, 2);

// Defensive fallback: if buzz_sso/shadow missing, regenerate without destroying WP session
add_action('init', function () {
    bz_shadow_session_dir(); // ensure dir exists
    $wp_sid = session_id();
    global $__buzz_sso_secret;
    if ($wp_sid) {
        // Defensive: fix shadow session id drift
        $shadow_id = bz_shadow_session_id($wp_sid);
        $transient_key = 'buzz_shadow_sid_' . $wp_sid;
        $stored = get_transient($transient_key);
        if ($stored !== $shadow_id) {
            set_transient($transient_key, $shadow_id, DAY_IN_SECONDS);
            bz_debug_log('init: fixed shadow session_id drift', ['wp_sid'=>$wp_sid, 'shadow_sid'=>$shadow_id]);
        }
        // If buzz_sso cookie is missing but session is valid, regenerate, don't destroy
        if ($__buzz_sso_secret && empty($_COOKIE[BUZZ_SSO_COOKIE])) {
            if (!empty($_SESSION['wp_user_id']) && !empty($_SESSION['wp_user_login']) && !empty($_SESSION['wp_user_email'])) {
                // Regenerate cookie with long-lived expiry, do not expire automatically
                $now = time();
                $exp = $now + bz_long_lived_expiry_seconds();
                $payload = [
                    'ver'           => 1,
                    'wp_user_id'    => (int)$_SESSION['wp_user_id'],
                    'wp_user_login' => (string)$_SESSION['wp_user_login'],
                    'wp_user_email' => (string)$_SESSION['wp_user_email'],
                    'wo_user_id'    => (int)($_SESSION['wo_user_id'] ?? 0),
                    'qd_user_id'    => (int)($_SESSION['qd_user_id'] ?? 0),
                    'cookie_domain' => BUZZ_COOKIE_DOMAIN,
                    'session_name'  => session_name(),
                    'session_id'    => session_id(),
                    'handler'       => ini_get('session.serialize_handler'),
                    'iat' => $now,
                    'exp' => $exp,
                ];
                $json = wp_json_encode($payload);
                $sig  = hash_hmac('sha256', $json, (string)$__buzz_sso_secret, true);
                $token = rtrim(strtr(base64_encode($json), '+/', '-_'), '=') . '.' . rtrim(strtr(base64_encode($sig), '+/', '-_'), '=');

                if (PHP_VERSION_ID >= 70300) {
                    setcookie(BUZZ_SSO_COOKIE, $token, [
                        'expires'  => $exp,
                        'path'     => '/',
                        'domain'   => BUZZ_COOKIE_DOMAIN,
                        'secure'   => true,
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);
                } else {
                    setcookie(BUZZ_SSO_COOKIE, $token, $exp, '/', BUZZ_COOKIE_DOMAIN, true, true);
                }

                $_SESSION['buzz_sso_last'] = $payload;
                bz_debug_log('init: buzz_sso cookie regenerated (long-lived)', ['wp_sid'=>$wp_sid, 'shadow_sid'=>$shadow_id, 'expires'=>date('c',$exp)]);
            }
        }
    }
}, 1);

// On logout: clear buzz_sso, remove shadow and destroy session
// Only clear SSO data and shadow on explicit logout
// On logout: clear buzz_sso, remove shadow and destroy session
// Only clear SSO data and shadow on explicit logout
add_action('wp_logout', function () {
    $wp_sid = session_id();
    bz_remove_shadow_session($wp_sid);
    // Expire buzz_sso cookie explicitly on logout
    if (PHP_VERSION_ID >= 70300) {
        setcookie(BUZZ_SSO_COOKIE, '', ['expires'=>time()-3600,'path'=>'/','domain'=>BUZZ_COOKIE_DOMAIN,'secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
    } else {
        setcookie(BUZZ_SSO_COOKIE, '', time()-3600, '/', BUZZ_COOKIE_DOMAIN, true, true);
    }
    $_SESSION = [];
    @session_unset();
    @session_destroy();
    $transient_key = 'buzz_shadow_sid_' . $wp_sid;
    delete_transient($transient_key);
    bz_debug_log('wp_logout: session destroyed and cookie cleared', ['wp_sid'=>$wp_sid, 'shadow_sid'=>bz_shadow_session_id($wp_sid)]);

    // Redirect to central shared logout proxy (authorised by BUZZ_SSO_SECRET)
    // Use BUZZ_SSO_SECRET if available (exposed earlier as $__buzz_sso_secret)
    global $__buzz_sso_secret;
    $secret = $__buzz_sso_secret ?: (defined('BUZZ_SSO_SECRET') ? BUZZ_SSO_SECRET : getenv('BUZZ_SSO_SECRET'));
    $url = 'https://buzzjuice.net/shared/sso-logout.php?sso_secret=' . rawurlencode((string)$secret) . '&from_wp=1';
    wp_safe_redirect($url);
    exit;
}, 10);

/* --------------------------- Admin debug endpoint --------------------------- */
add_action('init', function () use ($__buzz_sso_secret) {
    if (empty($_GET['bz_sso_debug'])) return;
    if (!current_user_can('manage_options')) return;
    header('Content-Type: text/plain; charset=utf-8');
    $token  = $_COOKIE[BUZZ_SSO_COOKIE] ?? null;
    $parsed = ($token && $__buzz_sso_secret) ? bz_sso_verify_token($token, $__buzz_sso_secret) : null;
    $info = [
        'session_name'   => session_name(),
        'session_id'     => session_id(),
        'shadow_id'      => bz_shadow_session_id(session_id()),
        'serialize_handler' => ini_get('session.serialize_handler'),
        'wp_user_id'     => $_SESSION['wp_user_id']    ?? null,
        'wp_user_login'  => $_SESSION['wp_user_login'] ?? null,
        'wp_user_email'  => $_SESSION['wp_user_email'] ?? null,
        'wo_user_id'     => $_SESSION['wo_user_id']    ?? null,
        'qd_user_id'     => $_SESSION['qd_user_id']    ?? null,
        'buzz_sso_raw'   => $token,
        'buzz_sso_parsed'=> $parsed,
        'cookie_domain'  => BUZZ_COOKIE_DOMAIN,
        'cookies'        => $_COOKIE,
        'session_vars'   => $_SESSION,
    ];
    bz_debug_log('Admin debug endpoint', $info);
    echo "BuzzJuice SSO — Admin Debug\n\n";
    print_r($info);
    exit;
}, 2);

/* ---------------- BuddyBoss redirect compatibility (unchanged) -- */
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

/* ---------------- Optional: logout without confirm -------------- */
add_action('check_admin_referer', 'logout_without_confirm', 10, 2);
function logout_without_confirm($action, $result) {
    if ($action === 'log-out' && !isset($_GET['_wpnonce'])) {
        $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : home_url('/');
        $location = str_replace('&amp;', '&', wp_logout_url($redirect_to));
        header('Location: ' . $location);
        exit;
    }
}
