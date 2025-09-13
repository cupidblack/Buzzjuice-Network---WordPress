<?php
// WordPress BPBM → WoWonder Message Bridge

require_once '/home/koware/public_html/buzzjuice.net/data/db_helpers.php';
require_once '/home/koware/public_html/buzzjuice.net/data/sync_messages/sync_helpers.php';

// ---- Handle preflight (OPTIONS) and restrict to POST ----
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    // Allow CORS preflight requests to succeed
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'error' => 'Method not allowed']);
    exit;
}
// ---------------------------------------------------------

$input = $_POST;
$wp_from_id = isset($input['from_user_id']) ? (int)$input['from_user_id'] : 0;
$wp_to_id   = isset($input['to_user_id']) ? (int)$input['to_user_id'] : 0;
$message    = isset($input['message']) ? trim($input['message']) : '';
$media_url  = isset($input['media_url']) ? trim($input['media_url']) : null;
$wp_msg_id  = isset($input['wp_msg_id']) ? (int)$input['wp_msg_id'] : 0; // Add this field to WP POST!

if (!$wp_from_id || !$wp_to_id || !$message || !$wp_msg_id) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'error' => 'Missing required fields']);
    exit;
}

$ww_db = get_wowonder_db();
$ww_from_id = wp_to_ww($wp_from_id, $ww_db);
$ww_to_id   = wp_to_ww($wp_to_id, $ww_db);

if (!$ww_from_id || !$ww_to_id) {
    log_sync_event("WP→WW user mapping failed: WP($wp_from_id,$wp_to_id) → WW($ww_from_id,$ww_to_id)");
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'error' => 'User mapping failed']);
    exit;
}

$ww_api_url = 'https://buzzjuice.net/api/v2/endpoints/send-message.php';
$post_data = [
    'user_id'         => $ww_to_id,
    'from_id'         => $ww_from_id,
    'message_hash_id' => md5($ww_from_id . $ww_to_id . time()),
    'text'            => $message
];
if ($media_url) $post_data['image_url'] = $media_url;

$ch = curl_init($ww_api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

$ww_msg_id = 0;
if ($response && $httpcode === 200) {
    $resp_arr = json_decode($response, true);
    if (isset($resp_arr['ww_msg_id'])) {
        $ww_msg_id = (int)$resp_arr['ww_msg_id'];
    }
}

if ($error || $httpcode !== 200 || !$ww_msg_id) {
    log_sync_event("WW REST failed: code=$httpcode; error=$error; resp=$response");
    http_response_code($httpcode ?: 500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'error' => 'WW REST failed']);
    exit;
}

// Save Message Mapping!
sync_map_save($ww_msg_id, $wp_msg_id);

log_sync_event("Synced WP($wp_from_id → $wp_to_id)/msg $wp_msg_id to WW($ww_from_id → $ww_to_id)/msg $ww_msg_id: $message");
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'code' => $httpcode, 'ww_msg_id' => $ww_msg_id]);
exit;
?>