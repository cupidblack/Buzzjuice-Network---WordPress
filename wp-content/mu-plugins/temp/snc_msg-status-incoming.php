<?php
// Receives status/action updates from WoWonder and applies to BPBM

require_once '/home/koware/public_html/buzzjuice.net/data/db_helpers.php';
require_once '/home/koware/public_html/buzzjuice.net/data/sync_messages/sync_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); echo json_encode(['status' => 'error', 'error' => 'Method not allowed']); exit;
}

$input = $_POST;
$action     = isset($input['action']) ? $input['action'] : null;
$ww_user_id = isset($input['user_id']) ? (int)$input['user_id'] : 0;
$ww_msg_id  = isset($input['message_id']) ? (int)$input['message_id'] : 0;
$reaction   = isset($input['reaction']) ? $input['reaction'] : null;

if (!$action || !$ww_user_id || !$ww_msg_id) {
    http_response_code(400); echo json_encode(['status' => 'error', 'error' => 'Missing fields']); exit;
}

// Map to WP IDs
$wp_db = get_wp_db_conn();
$wp_user_id    = ww_to_wp($ww_user_id, $wp_db);

// Use mapping helper for fullmapping!
$wp_message_id = sync_map_get_wp($ww_msg_id);

if (!$wp_user_id || !$wp_message_id) {
    log_sync_event("WW→WP status mapping failed: WW($ww_user_id,$ww_msg_id) → WP($wp_user_id,$wp_message_id)");
    http_response_code(404); echo json_encode(['status' => 'error', 'error' => 'User/message mapping failed']); exit;
}

switch ($action) {
    case 'read':
        Better_Messages()->functions->mark_message_read($wp_message_id, $wp_user_id);
        break;
    case 'react':
        Better_Messages()->functions->react_message($wp_message_id, $wp_user_id, $reaction);
        break;
    case 'pin':
        Better_Messages()->functions->pin_message($wp_message_id, $wp_user_id);
        break;
    case 'fav':
        Better_Messages()->functions->fav_message($wp_message_id, $wp_user_id);
        break;
    case 'delete':
        Better_Messages()->functions->delete_message($wp_message_id, $wp_user_id);
        break;
    default:
        http_response_code(400); echo json_encode(['status' => 'error', 'error' => 'Unknown action']); exit;
}
log_sync_event("WW→WP $action sync: $ww_user_id/$ww_msg_id → $wp_user_id/$wp_message_id");
echo json_encode(['status' => 'success']);
exit;
?>