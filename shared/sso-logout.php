<?php
declare(strict_types=1);
// Central SSO logout proxy (client-side orchestrator).
// Usage:
//  - Browser orchestration: https://buzzjuice.net/shared/sso-logout.php?sso_secret=<secret>&from=<platform>&logged_out=1
//  - Server token request: POST https://buzzjuice.net/shared/sso-logout.php?request_token=1  with JSON { sso_secret, from }

if (!headers_sent()) {
    header('Expires: 0');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Pragma: no-cache');
}

$LOG = __DIR__ . '/sso-logout-debug.log';
function sso_log($msg, $ctx = []) {
    global $LOG;
    $meta = ['ts'=>gmdate('Y-m-d H:i:s'), 'remote'=>$_SERVER['REMOTE_ADDR'] ?? null, 'uri'=>$_SERVER['REQUEST_URI'] ?? null, 'request'=>$_REQUEST ?? null];
    if ($ctx) $meta['ctx'] = $ctx;
    @file_put_contents($LOG, "[$meta[ts]] $msg | " . json_encode($meta, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
}

/* Helpers */
function _b64url_encode($b) {
    return rtrim(strtr(base64_encode($b), '+/', '-_'), '=');
}
function _b64url_decode($s) {
    $p = strtr($s, '-_', '+/');
    $m = strlen($p) % 4; if ($m) $p .= str_repeat('=', 4 - $m);
    return base64_decode($p);
}
function _get_server_secret() {
    $s = getenv('BUZZ_SSO_SECRET') ?: (defined('BUZZ_SSO_SECRET') ? BUZZ_SSO_SECRET : null);
    return $s;
}

// Read raw JSON if present
$raw_body = '';
if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST','PUT','PATCH']) && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $raw_body = file_get_contents('php://input');
    $body_json = @json_decode($raw_body, true);
} else {
    $body_json = null;
}

try {
    // require optional helpers
    $helpers = __DIR__ . '/db_helpers.php';
    if (!file_exists($helpers)) {
        sso_log('Missing shared/db_helpers.php');
        http_response_code(500);
        echo "Server misconfiguration";
        exit;
    }
    require_once $helpers;

    $server_secret = _get_server_secret();
    if (!$server_secret) {
        sso_log('orchestrator: missing BUZZ_SSO_SECRET', []);
        http_response_code(500);
        echo json_encode(['error'=>'server_misconfiguration']);
        exit;
    }

    // --- Token issuance API (server-to-server) ---
    // Accept either POST JSON with request_token=1 in query, or GET?request_token=1 with JSON body
    $is_token_request = (!empty($_GET['request_token']) && $_GET['request_token'] == '1') || (!empty($body_json['request_token']) && $body_json['request_token'] == '1');
    if ($is_token_request) {
        // Get provided secret from JSON body or query param
        $provided = null;
        if (!empty($body_json['sso_secret'])) $provided = (string)$body_json['sso_secret'];
        if (!$provided && !empty($_REQUEST['sso_secret'])) $provided = (string)$_REQUEST['sso_secret'];

        if (!$provided || !hash_equals((string)$server_secret, (string)$provided)) {
            sso_log('token_request: forbidden invalid secret', ['provided_preview'=>substr($provided ?? '',0,8)]);
            http_response_code(403);
            echo json_encode(['ok'=>0,'error'=>'forbidden']);
            exit;
        }

        $from = !empty($body_json['from']) ? (string)$body_json['from'] : (!empty($_REQUEST['from']) ? (string)$_REQUEST['from'] : 'unknown');
        $now = time();
        $exp = $now + 120; // short-lived 2 minutes
        $payload = ['iat'=>$now, 'exp'=>$exp, 'from'=>$from, 'nonce'=>bin2hex(random_bytes(8))];
        $json = json_encode($payload);
        $sig  = hash_hmac('sha256', $json, (string)$server_secret, true);
        $token = _b64url_encode($json) . '.' . _b64url_encode($sig);
        $wp_logout_url = 'https://buzzjuice.net/wp-login.php?action=logout&sso_one_time=' . rawurlencode($token);

        sso_log('token_request: issued token', ['from'=>$from,'exp'=>$exp]);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok'=>1,'token'=>$token,'wp_logout_url'=>$wp_logout_url]);
        exit;
    }

    // --- Normal browser orchestrator page ---
    // Validate provided sso_secret (from query param)
    $provided = !empty($_REQUEST['sso_secret']) ? (string)$_REQUEST['sso_secret'] : null;
    if (!$provided || !hash_equals((string)$server_secret, (string)$provided)) {
        sso_log('Forbidden: invalid or missing SSO secret', ['provided_preview'=>substr($provided ?? '',0,8)]);
        http_response_code(403);
        echo "Forbidden";
        exit;
    }
    sso_log('SSO secret validated (orchestrator page)', ['from'=>$_REQUEST['from'] ?? null, 'logged_out'=> $_REQUEST['logged_out'] ?? null]);

    // Endpoints to call from the browser (background POST). Keep using sso_secret for legacy invalidation endpoints.
    $streams_invalidate = 'https://buzzjuice.net/streams/sources/logout.php';
    $social_invalidate  = 'https://buzzjuice.net/social/logout.php';

    // Prepare JS values
    $secret_js = json_encode((string)$provided); // embedded to allow the client POST invalidation (legacy)
    $home_js   = json_encode('https://buzzjuice.net/?logged_out=1');
    $end1_js   = json_encode($streams_invalidate);
    $end2_js   = json_encode($social_invalidate);
    $timeoutMs = 8000;

    // Emit HTML/JS with retry/backoff (2 attempts per platform) and final navigation to WP home
    echo '<!doctype html><html><head><meta charset="utf-8"><meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0"><meta http-equiv="Pragma" content="no-cache"><title>Signing out…</title></head><body>';
    echo '<script>(function(){';
    echo "var secret = {$secret_js};";
    echo "var home = {$home_js};";
    echo "var endpoints = [{$end1_js}, {$end2_js}];";
    echo "var timeoutMs = " . intval($timeoutMs) . ";";

    // Helper: sleep
    echo "function delay(ms){return new Promise(function(r){setTimeout(r,ms);});}";

    // postInvalidate with retries (2 attempts)
    echo "function postInvalidateWithRetry(url){return new Promise(function(resolve){var attempts=0;var maxAttempts=2;function attempt(){attempts++;var controller=('AbortController' in window)?new AbortController():null;var signal=controller?controller.signal:null;if(controller) setTimeout(function(){controller.abort();}, timeoutMs-100);fetch(url,{method:'POST',credentials:'include',headers:{'Content-Type':'application/json'},body:JSON.stringify({sso_secret:secret}) ,signal:signal}).then(function(resp){ if(!resp||!resp.ok) { if(attempts<maxAttempts){return delay(300).then(attempt);} return resolve({ok:false,status:resp?resp.status:0}); } resp.json().then(function(j){ resolve({ok:true,json:j}); }).catch(function(){ resolve({ok:true,json:null}); }); }).catch(function(err){ if(attempts<maxAttempts){ return delay(300).then(attempt);} resolve({ok:false,err:String(err)}); }); } attempt(); }); }";

    // clearClientAndGoHome
    echo "function clearClientAndGoHome(){try{(function(domain){try{var cookies=(document.cookie||'').split('; ');for(var i=0;i<cookies.length;i++){var n=cookies[i].split('=')[0];if(!n) continue;try{document.cookie=n+'=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/;domain='+domain+';';}catch(e){}try{document.cookie=n+'=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/;';}catch(e){}}}catch(e){} })('.buzzjuice.net'); if('caches' in window && caches.keys) caches.keys().then(function(names){names.forEach(function(n){try{caches.delete(n);}catch(_){} });}).catch(function(){}); if('serviceWorker' in navigator && navigator.serviceWorker.getRegistrations) navigator.serviceWorker.getRegistrations().then(function(rs){rs.forEach(function(r){try{r.unregister();}catch(_){} });}).catch(function(){}); try{ if(window.localStorage) localStorage.clear(); }catch(e){} try{ if(window.sessionStorage) sessionStorage.clear(); }catch(e){} try{ if(window.indexedDB && indexedDB.databases) indexedDB.databases().then(function(dbs){dbs.forEach(function(db){try{indexedDB.deleteDatabase(db.name);}catch(_){} });}).catch(function(){}); }catch(e){} }catch(e){} try{ window.location.replace(home);}catch(e){window.location.href=home;} setTimeout(function(){try{window.location.href=home;}catch(e){}},350); window.onpageshow=function(ev){if(ev&&ev.persisted){try{window.location.replace(home);}catch(e){}}}; }";

    // Execute parallel invalidations with global timeout
    echo "var ps = endpoints.map(function(ep){ return postInvalidateWithRetry(ep).catch(function(e){return {ok:false,err:String(e)};}); }); var globalTimeout = new Promise(function(res){setTimeout(res, timeoutMs);}); Promise.race([ Promise.all(ps), globalTimeout ]).then(function(){ clearClientAndGoHome(); }).catch(function(){ clearClientAndGoHome(); });";

    echo "})();</script>";
    echo '<h2>Signing out…</h2><p>If you are not redirected automatically, <a href="https://buzzjuice.net/">click here</a>.</p>';
    echo '</body></html>';
    exit;

} catch (Throwable $e) {
    sso_log('sso-logout unexpected exception', ['err'=>$e->getMessage(),'trace'=>$e->getTraceAsString()]);
    http_response_code(500);
    echo "Server error";
    exit;
}