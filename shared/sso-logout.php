<?php
// Central SSO logout proxy with extended debugging and error control
// Usage: https://buzzjuice.net/shared/sso-logout.php?sso_secret=<secret>&from=<platform>&sso_debug=1

declare(strict_types=1);

// --- Settings ---
$DEBUG_LOG = __DIR__ . '/sso-logout-debug.log'; // Change if desired

function sso_debug_log($msg, $ctx = []) {
    global $DEBUG_LOG;
    $ts = gmdate('Y-m-d H:i:s');
    $meta = [
        'ts' => $ts,
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? null,
        'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
        'cookies'     => $_COOKIE,
        'session_id'  => session_id(),
        'from'        => $_REQUEST['from'] ?? null,
    ];
    if ($ctx) $meta['ctx'] = $ctx;
    @file_put_contents($DEBUG_LOG, "[$ts] $msg | " . json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
}

// --- Error reporting helper ---
function sso_error($code, $msg, $ctx = []) {
    sso_debug_log("ERROR $code: $msg", $ctx);
    http_response_code($code);
    if (!empty($_GET['sso_debug'])) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "SSO-LOGOUT ERROR ($code): $msg\n";
        print_r($ctx);
    } else {
        echo $msg;
    }
    exit;
}

// --- Secret extraction and validation ---
function _sso_get_request_secret() {
    $secret = null;
    if (!empty($_REQUEST['sso_secret'])) $secret = $_REQUEST['sso_secret'];
    if (!$secret && !empty($_SERVER['HTTP_AUTHORIZATION'])) {
        if (preg_match('/Bearer\s+(.*)$/i', $_SERVER['HTTP_AUTHORIZATION'], $m)) $secret = trim($m[1]);
    }
    if (!$secret && in_array($_SERVER['CONTENT_TYPE'] ?? '', ['application/json', 'application/json; charset=utf-8'])) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (is_array($data) && !empty($data['sso_secret'])) $secret = $data['sso_secret'];
    }
    return $secret;
}

try {
    $baseDir = realpath(__DIR__ . '') ?: __DIR__;

    // --- Load env & helpers ---
    $helpers = __DIR__ . '/db_helpers.php';
    if (!file_exists($helpers)) {
        sso_error(500, "Missing shared/db_helpers.php");
    }
    require_once $helpers;

    // --- Validate SSO secret ---
    $provided = _sso_get_request_secret();
    if (!defined('BUZZ_SSO_SECRET') || !BUZZ_SSO_SECRET || !$provided || !hash_equals((string)BUZZ_SSO_SECRET, (string)$provided)) {
        sso_error(403, "Forbidden: Invalid or missing SSO secret", ['provided' => $provided]);
    }
    sso_debug_log("SSO secret validated", ['provided' => $provided]);

    // --- Bootstrap WordPress ---
    $wp_load = realpath(__DIR__ . '/../wp-load.php') ?: (__DIR__ . '/../wp-load.php');
    $wp_loaded = false;
    if (file_exists($wp_load)) {
        define('WP_USE_THEMES', false);
        require_once $wp_load;
        $wp_loaded = true;
        sso_debug_log("WordPress bootstrapped");
    } else {
        sso_debug_log("wp-load.php missing", ['wp_load' => $wp_load]);
    }

    // --- WP Logout ---
    $from_wp = !empty($_REQUEST['from_wp']);
    if (function_exists('wp_logout')) {
        if (!$from_wp) {
            sso_debug_log("Calling wp_logout()");
            @wp_logout();
        } else {
            sso_debug_log("Skip wp_logout (already from WP)");
        }
    } else {
        sso_debug_log("wp_logout not available");
    }

    // --- Defensive session cleanup ---
    if (function_exists('bz_remove_shadow_session')) {
        if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
        try {
            $sid = session_id();
            bz_remove_shadow_session($sid);
            sso_debug_log("Shadow session removed", ['sid' => $sid]);
        } catch (Throwable $e) {
            sso_debug_log("Shadow session removal threw", ['sid' => session_id(), 'err' => $e->getMessage()]);
        }
    } else {
        sso_debug_log("bz_remove_shadow_session unavailable");
    }

    // --- Expire SSO and platform cookies ---
    $cookie_results = [];
    $expiry = time() - 3600;
    $domain = '.buzzjuice.net';
    // buzz_sso
    if (PHP_VERSION_ID >= 70300) {
        $cookie_results['buzz_sso'] = setcookie('buzz_sso', '', ['expires'=>$expiry,'path'=>'/','domain'=>$domain,'secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
        $cookie_results['user_id']  = setcookie('user_id', '', ['expires'=>$expiry,'path'=>'/','domain'=>$domain,'secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
        $cookie_results['JWT']      = setcookie('JWT',     '', ['expires'=>$expiry,'path'=>'/','domain'=>$domain,'secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
    } else {
        $cookie_results['buzz_sso'] = setcookie('buzz_sso', '', $expiry, '/', $domain, true, true);
        $cookie_results['user_id']  = setcookie('user_id',  '', $expiry, '/', $domain, true, true);
        $cookie_results['JWT']      = setcookie('JWT',      '', $expiry, '/', $domain, true, true);
    }
    sso_debug_log("Cookies expired", $cookie_results);

    // --- Redirect to next platform in chain ---
    $origin = $_REQUEST['from'] ?? '';
    $chain = [
        'quickdate' => 'https://buzzjuice.net/streams/logout/?cabin=home&from=sso',
        'wowonder'  => 'https://buzzjuice.net/social/logout.php?cabin=home&from=sso',
        'wp'        => 'https://buzzjuice.net/streams/logout/?cabin=home&from=sso',
        ''          => 'https://buzzjuice.net/streams/logout/?cabin=home&from=sso', // default
    ];
    $redirect = $chain[$origin] ?? $chain[''];
    sso_debug_log("Redirecting to next logout", ['origin' => $origin, 'redirect' => $redirect]);

    // If debug, show end-state before redirect
    if (!empty($_GET['sso_debug'])) {
        header('Content-Type: text/plain; charset=utf-8');
        echo "BuzzJuice SSO Logout Debug\n";
        echo "Request origin: $origin\n";
        echo "Redirect: $redirect\n";
        echo "Cookies:\n"; print_r($_COOKIE);
        echo "Session:\n"; print_r($_SESSION);
        echo "Log file: $DEBUG_LOG\n";
        exit;
    }
    
    // ----------- Browser cache clearing headers -----------
    header('Expires: 0');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');

    header('Location: ' . $redirect);
    exit();

} catch (Throwable $e) {
    sso_error(500, "Unexpected exception", ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
}
?>