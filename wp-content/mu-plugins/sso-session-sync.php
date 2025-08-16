<?php
/**
 * Buzzjuice SSO session sync (WordPress side)
 * REVIEWED & UPDATED — Aug 2025
 *
 * Goals:
 * - Use only a dedicated session cookie (BUZZSESS)
 * - Ensure session.serialize_handler = php_serialize for WoWonder compatibility
 * - Convert legacy php handler sessions to php_serialize if needed
 * - On login, write key WordPress user identifiers into the session
 * - Optionally set a signed fallback cookie (bz_sso)
 * - Be safe: avoid session fixation, honor TTLs
 * - Be robust across dev/staging/prod domains
 */

/* -------------------------------------------------------------------------- */
/* Config */
/* -------------------------------------------------------------------------- */
if (!defined('BUZZ_SESSION_NAME')) define('BUZZ_SESSION_NAME', 'BUZZSESS');
if (!defined('BUZZ_SESSION_TTL')) define('BUZZ_SESSION_TTL', 900); // 15 minutes
if (!defined('BUZZ_SSO_COOKIE')) define('BUZZ_SSO_COOKIE', 'bz_sso');
if (!defined('BUZZ_SSO_DEBUG')) define('BUZZ_SSO_DEBUG', false);
if (!defined('BUZZ_DEBUG_LOG')) define('BUZZ_DEBUG_LOG', __DIR__ . '/buzz_sso_debug.log');
if (!defined('BUZZ_SESSION_DOMAIN')) define('BUZZ_SESSION_DOMAIN', '.buzzjuice.net'); // shared domain

// Optional: set BUZZ_SSO_SECRET in wp-config.php for signed-cookie fallback
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

function bz_sso_build_token_from_user($user_id, $login, $email, $secret) {
    $now = time();
    $payload = [
        'ver' => 1,
        'wp_user_id' => (int) $user_id,
        'login' => (string) $login,
        'email' => (string) $email,
        'iat' => $now,
        'exp' => $now + BUZZ_SESSION_TTL,
    ];
    $json = function_exists('wp_json_encode') ? wp_json_encode($payload) : json_encode($payload);
    $sig = hash_hmac('sha256', $json, (string) $secret, true);
    return _bz_b64url_encode($json) . '.' . _bz_b64url_encode($sig);
}

// Session inspection utility for debugging
function bz_log_session_state($context) {
    if (!BUZZ_SSO_DEBUG) return;
    bz_debug_log("Session dump @ $context", [
        'session_id' => session_id(),
        'session_name' => session_name(),
        'cookie_value' => $_COOKIE[session_name()] ?? null,
        'cookie_exists' => isset($_COOKIE[session_name()]),
        'session_vars' => isset($_SESSION) ? $_SESSION : null,
        'server' => [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
            'HTTPS' => $_SERVER['HTTPS'] ?? null,
        ],
    ]);
}

/** Compute consistent cookie params for session module */
function bz_session_cookie_params($ttl = null) {
    if ($ttl === null) $ttl = BUZZ_SESSION_TTL;
    return [
        'lifetime' => (int) $ttl,
        'path' => '/',
        'domain' => BUZZ_SESSION_DOMAIN,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

/* -------------------------------------------------------------------------- */
/* Session bootstrap with handler conversion for WoWonder compatibility */
/* -------------------------------------------------------------------------- */
function buzzjuice_init_sso_session() {
    // Skip non-web contexts
    if (php_sapi_name() === 'cli' || (defined('WP_CLI') && WP_CLI) || (defined('DOING_CRON') && DOING_CRON)) {
        return;
    }

    // Ensure we can still send headers
    if (headers_sent($file, $line)) {
        bz_debug_log('Headers already sent before session start', compact('file','line'));
        return;
    }

    // If a BUZZSESS session is already active but dirty, wipe it
    if (session_status() === PHP_SESSION_ACTIVE) {
        $current_name = session_name();
        $sid = session_id();
        $expected = BUZZ_SESSION_NAME;
    
        $should_destroy = false;
    
        // Case 1: Wrong session name
        if ($current_name !== $expected) {
            bz_debug_log('Non-BUZZSESS session active; destroying', [
                'current_name' => $current_name,
                'expected'     => $expected,
                'sid'          => $sid,
            ]);
            $should_destroy = true;
        }
    
        // Case 2: Correct session name but mismatched WordPress user ID
        elseif (!empty($_SESSION['wp_user_id']) && $_SESSION['wp_user_id'] !== get_current_user_id()) {
            bz_debug_log('BUZZSESS wp_user_id mismatch; destroying', [
                'session_wp_user_id' => $_SESSION['wp_user_id'],
                'current_wp_user_id' => get_current_user_id(),
                'sid'                => $sid,
            ]);
            $should_destroy = true;
        }
    
        if ($should_destroy) {
            $_SESSION = [];
            @session_unset();
            @session_destroy();
    
            if (isset($_COOKIE[$current_name])) {
                setcookie($current_name, '', time() - 3600, '/');
            }
        }
    }

    // --- Handler conversion: ensure WoWonder-compatible serialization ---
    $session_id = null;
    if (!empty($_COOKIE[BUZZ_SESSION_NAME])) {
        $session_id = preg_replace('/[^a-zA-Z0-9,-]/', '', $_COOKIE[BUZZ_SESSION_NAME]);
    }
    $sess_file = $session_id ? rtrim(ini_get('session.save_path'), '/\\') . "/sess_" . $session_id : null;
    $handler = ini_get('session.serialize_handler');

    if ($session_id && $sess_file && is_readable($sess_file) && $handler !== 'php_serialize') {
        bz_debug_log('Converting session file to php_serialize', ['sid' => $session_id, 'handler' => $handler]);
        $raw_data = file_get_contents($sess_file);

        $session_vars = [];
        $offset = 0;
        while ($offset < strlen($raw_data)) {
            if (!strstr(substr($raw_data, $offset), "|")) break;
            $pos = strpos($raw_data, "|", $offset);
            $varname = substr($raw_data, $offset, $pos - $offset);
            $offset = $pos + 1;
            $data_value = @unserialize(substr($raw_data, $offset));
            if ($data_value === false && serialize(false) !== substr($raw_data, $offset, strlen(serialize(false)))) break;
            $session_vars[$varname] = $data_value;
            $offset += strlen(serialize($data_value));
        }

        // Switch handler & re-save with php_serialize
        @ini_set('session.serialize_handler', 'php_serialize');
        @session_name(BUZZ_SESSION_NAME);
        @session_id($session_id);
        @session_start();
        foreach ($session_vars as $k => $v) $_SESSION[$k] = $v;
        @session_write_close();
    }

    // --- Normal session start (always php_serialize) ---
    @ini_set('session.serialize_handler', 'php_serialize');
    $params = bz_session_cookie_params();
    if (PHP_VERSION_ID >= 70300) {
        @session_set_cookie_params($params);
    } else {
        @ini_set('session.cookie_lifetime', (string) $params['lifetime']);
        @ini_set('session.cookie_secure', '1');
        @ini_set('session.cookie_httponly', '1');
        @ini_set('session.cookie_domain', BUZZ_SESSION_DOMAIN);
    }
    @ini_set('session.gc_maxlifetime', (string) max((int) $params['lifetime'], BUZZ_SESSION_TTL));
    @ini_set('session.use_only_cookies', '1');
    @ini_set('session.use_strict_mode', '1');

    if ($session_id) @session_id($session_id);
    @session_name(BUZZ_SESSION_NAME);

    if (@session_start() === false) {
        bz_debug_log('session_start failed');
        return;
    }

    bz_log_session_state('init');
}
add_action('init', 'buzzjuice_init_sso_session', 1);

/* -------------------------------------------------------------------------- */
/* On login: set session vars + optional signed fallback cookie */
/* -------------------------------------------------------------------------- */
add_action('wp_login', function($user_login, $user) use ($__buzz_sso_secret) {
    buzzjuice_init_sso_session(); // ensure session is started with php_serialize
    if (session_status() !== PHP_SESSION_ACTIVE) {
        bz_debug_log('wp_login: No active session; cannot set vars');
        return;
    }

    // Core identifiers
    $_SESSION['wp_user_id'] = (int) $user->ID;
    $_SESSION['wp_user_login'] = (string) $user->user_login;
    $_SESSION['wp_user_email'] = (string) $user->user_email;

    // Cross-app IDs (optional)
    $_SESSION['wo_user_id'] = (int) get_user_meta($user->ID, 'wo_user_id', true);
    $_SESSION['qd_user_id'] = (int) get_user_meta($user->ID, 'qd_user_id', true);

    // Mirror WoWonder-style user_id if present
    if (!empty($_SESSION['wo_user_id'])) {
        $_SESSION['user_id'] = (int) $_SESSION['wo_user_id'];
    } else {
        unset($_SESSION['user_id']);
    }

    // TTL
    $now = time();
    $_SESSION['wp_sso_iat'] = $now;
    $_SESSION['wp_sso_exp'] = $now + BUZZ_SESSION_TTL;

    bz_debug_log('wp_login: Session variables set', [
        'sid' => session_id(),
        'wp_user_id' => $_SESSION['wp_user_id'],
        'wo_user_id' => $_SESSION['wo_user_id'] ?? null,
        'qd_user_id' => $_SESSION['qd_user_id'] ?? null,
    ]);

    if (PHP_VERSION_ID >= 70300) @session_set_cookie_params(bz_session_cookie_params());
    @session_regenerate_id(true);

    // Optional signed-cookie fallback
    if (!empty($__buzz_sso_secret)) {
        $token = bz_sso_build_token_from_user($user->ID, $user->user_login, $user->user_email, $__buzz_sso_secret);
        $expiry = $now + BUZZ_SESSION_TTL;
        setcookie(BUZZ_SSO_COOKIE, $token, $expiry, '/', BUZZ_SESSION_DOMAIN, true, true);
        bz_debug_log('wp_login: Signed SSO cookie set', [
            'cookie' => BUZZ_SSO_COOKIE,
            'exp' => $expiry,
            'peek' => substr($token, 0, 32) . '…',
        ]);
    }

    bz_log_session_state('wp_login');
    @session_write_close();
}, 10, 2);

/* -------------------------------------------------------------------------- */
/* Logout: destroy session + clear cookies */
/* -------------------------------------------------------------------------- */
add_action('wp_logout', function() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        @session_name(BUZZ_SESSION_NAME);
        @ini_set('session.serialize_handler', 'php_serialize');
        @session_start();
    }
    bz_debug_log('wp_logout: destroying session', ['sid' => session_id()]);
    $_SESSION = [];
    @session_unset();
    @session_destroy();

    setcookie(BUZZ_SESSION_NAME, '', time() - 3600, '/', BUZZ_SESSION_DOMAIN);
    setcookie(BUZZ_SSO_COOKIE, '', time() - 3600, '/', BUZZ_SESSION_DOMAIN);

    bz_debug_log('wp_logout: cookies cleared', ['cookies' => array_keys($_COOKIE)]);
    wp_safe_redirect('https://buzzjuice.net/streams/logout/?cabin=home');
    exit;
}, 10);

/* -------------------------------------------------------------------------- */
/* Optional: admin/debug endpoint (DO NOT expose publicly in production) */
/* -------------------------------------------------------------------------- */
add_action('init', function() {
    if (!isset($_GET['bz_sso_debug'])) return;
    if (!current_user_can('manage_options')) return;
    header('Content-Type: text/plain');
    echo "BUZZSESS session info (WordPress)\n";
    print_r([
        'session_id' => session_id(),
        'session_name' => session_name(),
        'session_vars' => isset($_SESSION) ? $_SESSION : null,
        'cookies' => $_COOKIE,
        'server' => [
            'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? null,
            'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? null,
        ],
    ]);
    exit;
}, 1);

/* -------------------------------------------------------------------------- */
/* BuddyBoss login redirect compatibility */
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