<?php
// Enhanced logging function
function log_wo_sync_debug($message) {
    $log_file = __DIR__ . '/sync_wo_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message\n", 3, $log_file);
}

require_once __DIR__ . '/../../data/DotEnv.php';
// Load the .env file
try {
    $dotenv = new DotEnv(dirname(__DIR__, 4) . '/.env');
    $dotenv->load();

define('WOWONDER_DB_HOST', getenv('WOWONDER_DB_HOST'));
define('WOWONDER_DB_USER', getenv('WOWONDER_DB_USER'));
define('WOWONDER_DB_PASS', getenv('WOWONDER_DB_PASS'));
define('WOWONDER_DB_NAME', getenv('WOWONDER_DB_NAME'));

} catch (Exception $e) {
    die('Error - Failed to load environment: ' . $e->getMessage());
}

// HOOKS
add_action('profile_update', 'sync_wp_usermeta_to_wowonder', 10, 2);
add_action('edit_user_profile_update', 'sync_wp_usermeta_to_wowonder', 10, 2);
add_action('personal_options_update', 'sync_wp_usermeta_to_wowonder', 10, 2);
add_action('xprofile_data_after_save', 'sync_wp_xprofile_to_wowonder', 10, 1);

    // Field maps to sync
    $metadata = get_user_field_metadata();
    $wp_usermeta_fields = $metadata['private_secure_fields'];
    $wp_xprofile_fields = $metadata['public_open_fields'];

function get_wo_db_connection($host, $user, $pass, $db, $label) {
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_errno) {
        log_wo_sync_debug("[$label] DB connection failed: " . $conn->connect_error);
        return false;
    }

    $conn->set_charset('utf8mb4');
//    log_wo_sync_debug("[$label] DB connection successful to '$db' as user '$user'");
    return $conn;
}

function get_wowonder_db() {
    static $conn = null;
    if (!$conn) $conn = get_wo_db_connection(WOWONDER_DB_HOST, WOWONDER_DB_USER, WOWONDER_DB_PASS, WOWONDER_DB_NAME, 'WoWonder');
    return $conn;
}

function get_wowonder_id_for_wp_user($wp_user_id) {
    $id = function_exists('xprofile_get_field_data') ? xprofile_get_field_data('wo_user_id', $wp_user_id) : '';
    if (!$id) $id = get_user_meta($wp_user_id, 'wo_user_id', true);
//    log_wo_sync_debug("Resolved WoWonder ID for WP user $wp_user_id: $id");
    return $id ? (int)$id : false;
}

function do_wo_update($conn, $table, $id_field, $id, $fields, $label) {
    if (!$conn || !$id || empty($fields)) {
        log_wo_sync_debug("[$label] Update skipped: invalid parameters.");
        return;
    }

    $set = [];
    foreach ($fields as $field => $value) {
        $escaped = $conn->real_escape_string($value);
        $set[] = "`$field` = '$escaped'";
    }

    $sql = "UPDATE $table SET " . implode(',', $set) . " WHERE $id_field = $id";
//    log_wo_sync_debug("[$label] Executing SQL: $sql");

    $success = $conn->query($sql);
    if (!$success) {
        log_wo_sync_debug("[$label] Update failed: " . $conn->error);
    } else {
//        log_wo_sync_debug("[$label] Update successful for $table ID $id");
    }
}

function sync_wp_usermeta_to_wowonder($user_id) {
    global $wp_usermeta_fields;
    $wowonder_id = get_wowonder_id_for_wp_user($user_id);
    if (!$wowonder_id) return;

    $user = get_userdata($user_id);
    $data = ['username' => $user->user_login, 'email' => $user->user_email];
    foreach ($wp_usermeta_fields as $field) {
        $val = get_user_meta($user_id, $field, true);
        if ($val !== '' && $val !== null) $data[$field] = $val;
    }
//    log_wo_sync_debug("Syncing to WoWonder: WP ID $user_id => WoWonder ID $wowonder_id | Data: " . json_encode($data));
    do_wo_update(get_wowonder_db(), 'Wo_Users', 'user_id', $wowonder_id, $data, 'WoWonder');
}

function sync_wp_xprofile_to_wowonder($data) {
    global $wp_xprofile_fields;
    $user_id = is_object($data) ? $data->user_id : $data;
    $wowonder_id = get_wowonder_id_for_wp_user($user_id);
    if (!$wowonder_id) return;

    $update_data = [];
    foreach ($wp_xprofile_fields as $field) {
        $val = xprofile_get_field_data($field, $user_id);
        if ($val !== '' && $val !== null) $update_data[$field] = $val;
    }
//    log_wo_sync_debug("xProfile sync to WoWonder: WP ID $user_id => WoWonder ID $wowonder_id | Data: " . json_encode($update_data));
    do_wo_update(get_wowonder_db(), 'Wo_Users', 'user_id', $wowonder_id, $update_data, 'WoWonder');
}