<?php
/*
Mu-plugin: WoWonder → WordPress Incoming Message Sync
Description: Receives incoming messages from WoWonder and inserts them into BP Better Messages/BuddyBoss.
Author: pineapplebuzzjuice
Version: 1.0.0
*/

require_once '/home/koware/public_html/buzzjuice.net/data/db_helpers.php';
require_once '/home/koware/public_html/buzzjuice.net/data/sync_messages/sync_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'error' => 'Method not allowed']);
    exit;
}

$input = $_POST;
$ww_from_id = isset($input['from_user_id']) ? (int)$input['from_user_id'] : 0;
$ww_to_id   = isset($input['to_user_id'])   ? (int)$input['to_user_id']   : 0;
$message    = isset($input['message'])      ? trim($input['message'])     : '';
$media_url  = isset($input['media_url'])    ? trim($input['media_url'])   : null;
$ww_msg_id  = isset($input['ww_msg_id'])    ? (int)$input['ww_msg_id']   : 0; // Add this field to WoWonder POST!

if (!$ww_from_id || !$ww_to_id || !$message || !$ww_msg_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'error' => 'Missing required fields']);
    exit;
}

$wp_db = get_wp_db_conn();
$wp_from_id = ww_to_wp($ww_from_id, $wp_db);
$wp_to_id   = ww_to_wp($ww_to_id, $wp_db);

if (!$wp_from_id || !$wp_to_id) {
    log_sync_event("WW→WP user mapping failed: WW($ww_from_id,$ww_to_id) → WP($wp_from_id,$wp_to_id)");
    http_response_code(404);
    echo json_encode(['status' => 'error', 'error' => 'User mapping failed']);
    exit;
}

if (!function_exists('Better_Messages')) {
    log_sync_event("BP Better Messages not active.");
    http_response_code(501);
    echo json_encode(['status' => 'error', 'error' => 'BP Better Messages not active']);
    exit;
}

$sent = false;
$wp_msg_id = 0;
try {
    $args = [
        'sender_id'  => $wp_from_id,
        'recipients' => [$wp_to_id],
        'message'    => $message
    ];
    if ($media_url) $args['attachment'] = $media_url;

    $result = Better_Messages()->functions->send_message($args);
    if ($result && isset($result['id']) && $result['id'] > 0) {
        $sent = true;
        $wp_msg_id = $result['id'];
    }
} catch (Exception $e) {
    log_sync_event("Exception in WW→WP message insert: " . $e->getMessage());
}

if (!$sent || !$wp_msg_id) {
    log_sync_event("Failed to insert message WW($ww_from_id → $ww_to_id) / WP($wp_from_id → $wp_to_id)");
    http_response_code(500);
    echo json_encode(['status' => 'error', 'error' => 'Failed to insert message']);
    exit;
}

// Save Message Mapping!
sync_map_save($ww_msg_id, $wp_msg_id);

log_sync_event("Inserted incoming WW($ww_from_id → $ww_to_id)/msg $ww_msg_id as WP($wp_from_id → $wp_to_id)/msg $wp_msg_id: $message");
echo json_encode(['status' => 'success', 'wp_from_id' => $wp_from_id, 'wp_to_id' => $wp_to_id, 'wp_msg_id' => $wp_msg_id]);
exit;
?>