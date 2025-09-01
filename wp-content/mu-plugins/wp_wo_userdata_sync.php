<?php
// Enhanced logging function
function log_wo_sync_debug($message) {
    $log_file = __DIR__ . '/sync_wo_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message\n", 3, $log_file);
}

require_once __DIR__ . '/../../shared/wwqd_bridge.php';

// HOOKS
add_action('profile_update', 'sync_wp_usermeta_to_wowonder', 10, 2);
add_action('edit_user_profile_update', 'sync_wp_usermeta_to_wowonder', 10, 2);
add_action('personal_options_update', 'sync_wp_usermeta_to_wowonder', 10, 2);
add_action('xprofile_data_after_save', 'sync_wp_xprofile_to_wowonder', 10, 1);

    // Field maps to sync
    $metadata = get_user_field_metadata();
    $wp_usermeta_fields = $metadata['private_secure_fields'];
    $wp_xprofile_fields = $metadata['public_open_fields'];

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
    
    $table_columns = get_wowonder_table_columns($conn, $table);
    $set = [];
    foreach ($fields as $field => $value) {
        
        if (!in_array($field, $table_columns)) {
            log_wo_sync_debug("[$label] Skipping unknown column: $field");
            continue;
        }
        
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

function get_wowonder_table_columns($conn, $table) {
    static $cache = [];
    if (isset($cache[$table])) return $cache[$table];

    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    if (!$result) {
        log_wo_sync_debug("[WoWonder] Failed to fetch columns for $table: " . $conn->error);
        return [];
    }

    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    $cache[$table] = $columns;
    return $columns;
}

function sync_wp_usermeta_to_wowonder($user_id) {
    global $wp_usermeta_fields;
    $wowonder_id = get_wowonder_id_for_wp_user($user_id);
    if (!$wowonder_id) return;

    $user = get_userdata($user_id);
    $data = ['username' => $user->user_login, 'email' => $user->user_email, 'password' => $user->user_pass]; // Synchronize password hash

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
        if ($val === '' || $val === null) continue;
    
        if ($field === 'avatar') {
            $val = trim($val);
            // If not a full URL, make it one
            if (!preg_match('/^https?:\/\//i', $val)) {
                $val = rtrim(home_url(), '/') . '/' . ltrim($val, '/');
            }
            // Ensure valid image extension
            if (!preg_match('/\.(jpg|jpeg|png|gif)(\?|$)/i', $val)) {
                $val .= '.png';
            }
        }
    
        $update_data[$field] = $val;
    }

//    log_wo_sync_debug("xProfile sync to WoWonder: WP ID $user_id => WoWonder ID $wowonder_id | Data: " . json_encode($update_data));
    do_wo_update(get_wowonder_db(), 'Wo_Users', 'user_id', $wowonder_id, $update_data, 'WoWonder');
}