<?php
// Enhanced logging function
function log_qd_sync_debug($message) {
    $log_file = __DIR__ . '/sync_qd_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message\n", 3, $log_file);
}

require_once __DIR__ . '/../../shared/wwqd_bridge.php';

if (!defined('QD_DB_HOST')) define('QUICKDATE_DB_HOST', getenv('QUICKDATE_DB_HOST'));
if (!defined('QD_DB_USER')) define('QUICKDATE_DB_USER', getenv('QUICKDATE_DB_USER'));
if (!defined('QD_DB_PASS')) define('QUICKDATE_DB_PASS', getenv('QUICKDATE_DB_PASS'));
if (!defined('QD_DB_NAME')) define('QUICKDATE_DB_NAME', getenv('QUICKDATE_DB_NAME'));

// HOOKS
add_action('profile_update', 'sync_wp_usermeta_to_quickdate', 10, 2);
add_action('edit_user_profile_update', 'sync_wp_usermeta_to_quickdate', 10, 2);
add_action('personal_options_update', 'sync_wp_usermeta_to_quickdate', 10, 2);
add_action('xprofile_data_after_save', 'sync_wp_xprofile_to_quickdate', 10, 1);
add_action('bp_core_avatar_uploaded', 'update_avatar_in_quickdate');

    // Field maps to sync
    $metadata = get_user_field_metadata();
    $wp_usermeta_fields = $metadata['private_secure_fields'];
    $wp_xprofile_fields = $metadata['public_open_fields'];

function get_qd_db_connection($host, $user, $pass, $db, $label) {
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_errno) {
        log_qd_sync_debug("[$label] DB connection failed: " . $conn->connect_error);
        return false;
    }

    $conn->set_charset('utf8mb4');
//    log_qd_sync_debug("[$label] DB connection successful to '$db' as user '$user'");
    return $conn;
}

function get_quickdate_db() {
    static $conn = null;
    if (!$conn) $conn = get_qd_db_connection(QD_DB_HOST, QD_DB_USER, QD_DB_PASS, QD_DB_NAME, 'QuickDate');
    return $conn;
}

function get_quickdate_id_by_email($email) {
    $conn = get_quickdate_db();
    if (!$conn) {
        log_qd_sync_debug("[QuickDate] Failed to get DB connection in get_quickdate_id_by_email()");
        return false;
    }

    $email = $conn->real_escape_string($email);
    $query_str = "SELECT id FROM users WHERE email = '$email' LIMIT 1";
//    log_qd_sync_debug("[QuickDate] Executing query: $query_str");

    $query = $conn->query($query_str);

    if (!$query) {
        log_qd_sync_debug("[QuickDate] Query failed: " . $conn->error);
        return false;
    }

    if ($query->num_rows === 0) {
        log_qd_sync_debug("[QuickDate] No user found with email: $email");
        return false;
    }

    $result = $query->fetch_assoc();
    $id = (int) $result['id'];
//    log_qd_sync_debug("[QuickDate] Resolved user ID: $id for email: $email");
    return $id;
}

function do_qd_update($conn, $table, $id_field, $id, $fields, $label) {
    if (!$conn || !$id || empty($fields)) {
        log_qd_sync_debug("[$label] Update skipped: invalid parameters.");
        return;
    }

    $table_columns = get_table_columns($conn, $table);
    $set = [];

    foreach ($fields as $field => $value) {
        if (!in_array($field, $table_columns)) {
            log_qd_sync_debug("[$label] Skipping unknown column: $field");
            continue;
        }
        $escaped = $conn->real_escape_string($value);
        $set[] = "`$field` = '$escaped'";
    }

    if (empty($set)) {
        log_qd_sync_debug("[$label] No valid columns to update for $table ID $id");
        return;
    }

    $sql = "UPDATE $table SET " . implode(',', $set) . " WHERE $id_field = $id";
//    log_qd_sync_debug("[$label] Executing SQL: $sql");

    $success = $conn->query($sql);
    if (!$success) {
        log_qd_sync_debug("[$label] Update failed: " . $conn->error);
    } else {
//        log_qd_sync_debug("[$label] Update successful for $table ID $id");
    }
}


function get_table_columns($conn, $table) {
    static $cache = [];
    if (isset($cache[$table])) return $cache[$table];

    $result = $conn->query("SHOW COLUMNS FROM `$table`");
    if (!$result) {
        log_qd_sync_debug("[QuickDate] Failed to fetch columns for $table: " . $conn->error);
        return [];
    }

    $columns = [];
    while ($row = $result->fetch_assoc()) {
        $columns[] = $row['Field'];
    }

    $cache[$table] = $columns;
    return $columns;
}

function sync_wp_usermeta_to_quickdate($user_id) {
    global $wp_usermeta_fields;
    $user = get_userdata($user_id);
    $qd_id = get_quickdate_id_by_email($user->user_email);
    if (!$qd_id) return;

    $data = ['username' => $user->user_login, 'email' => $user->user_email];
    foreach ($wp_usermeta_fields as $field) {
        $val = get_user_meta($user_id, $field, true);
        if ($val !== '' && $val !== null) $data[$field] = $val;
    }
//    log_qd_sync_debug("Syncing to QuickDate: WP ID $user_id => QuickDate ID $qd_id | Data: " . json_encode($data));
    do_qd_update(get_quickdate_db(), 'users', 'id', $qd_id, $data, 'QuickDate');
}

function sync_wp_xprofile_to_quickdate($data) {
    global $wp_xprofile_fields;
    $user_id = is_object($data) ? $data->user_id : $data;
    $user = get_userdata($user_id);
    $qd_id = get_quickdate_id_by_email($user->user_email);
    if (!$qd_id) return;

    $update_data = [];
    foreach ($wp_xprofile_fields as $field) {
        $val = xprofile_get_field_data($field, $user_id);
        if ($val !== '' && $val !== null) $update_data[$field] = $val;
    }
//    log_qd_sync_debug("xProfile sync to QuickDate: WP ID $user_id => QuickDate ID $qd_id | Data: " . json_encode($update_data));
    do_qd_update(get_quickdate_db(), 'users', 'id', $qd_id, $update_data, 'QuickDate');
}

function update_avatar_in_quickdate($user_id) {
    $qd_id = get_quickdate_id_by_email(get_userdata($user_id)->user_email);
    if (!$qd_id) return;

    $avatar_url = bp_core_fetch_avatar([
        'item_id' => $user_id,
        'object'  => 'user',
        'type'    => 'full',
        'html'    => false
    ]);

//    log_qd_sync_debug("Updating avatar in QuickDate for WP user $user_id => QuickDate ID $qd_id | Avatar URL: $avatar_url");
    do_qd_update(get_quickdate_db(), 'users', 'id', $qd_id, ['avatar' => $avatar_url], 'QuickDate');
}

if (!function_exists('get_user_field_metadata')) {
    function get_user_field_metadata() {
        // Always cache metadata for repeated calls in a request
        static $metadata = null;
        if ($metadata === null) {
            $json = @file_get_contents('https://buzzjuice.net/data/user_field_metadata.json');
            $metadata = $json ? json_decode($json, true) : ['private_secure_fields'=>[], 'public_open_fields'=>[]];
        }
        return $metadata;
    }
}