<?php
// Requires both DB bridges loaded before use
define('SYNC_META_PATH', __DIR__ . '/logs/');

// WoWonder user_id → WordPress user_id
function ww_to_wp($ww_id, $wp_db_conn) {
    $ww_id = (int)$ww_id;
    $q = "SELECT user_id FROM wp_usermeta WHERE meta_key='wo_user_id' AND meta_value='$ww_id' LIMIT 1";
    $res = mysqli_query($wp_db_conn, $q);
    if ($row = mysqli_fetch_assoc($res)) return (int)$row['user_id'];
    return false;
}

// WordPress user_id → WoWonder user_id
function wp_to_ww($wp_id, $ww_db_conn) {
    $wp_id = (int)$wp_id;
    $q = "SELECT user_id FROM Wo_Users WHERE wp_user_id='$wp_id' LIMIT 1";
    $res = mysqli_query($ww_db_conn, $q);
    if ($row = mysqli_fetch_assoc($res)) return (int)$row['user_id'];
    return false;
}

// Meta helpers
function get_last_synced_id($file) {
    return file_exists($file) ? (int)trim(file_get_contents($file)) : 0;
}
function set_last_synced_id($file, $id) {
    file_put_contents($file, $id);
}

function log_sync_event($msg) {
    $logfile = SYNC_META_PATH . 'sync.log';
    $logdir = dirname($logfile);
    if (!is_dir($logdir)) {
        mkdir($logdir, 0777, true); // recursive, permissions
    }
    file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
}

// Thread mapping for WP
function get_thread_id($from, $to) {
    $file = SYNC_META_PATH . "threads_wp_{$from}_{$to}.txt";
    return file_exists($file) ? (int)file_get_contents($file) : 0;
}
function set_thread_id($from, $to, $thread_id) {
    $file = SYNC_META_PATH . "threads_wp_{$from}_{$to}.txt";
    file_put_contents($file, $thread_id);
}
?>