<?php
require_once __DIR__ . '/../../data/db_helpers.php'; // $ww_db_conn
require_once __DIR__ . '/../../data/sync_messages/sync_helpers.php';

function wp_wo_sync_messages($from_wp, $to_wp) {
    $ww_db_conn = get_wowonder_db(); 
    $wpdb = get_wp_db_conn();

    $from_ww = wp_to_ww($from_wp, $ww_db_conn);
    $to_ww = wp_to_ww($to_wp, $ww_db_conn);
    if (!$from_ww || !$to_ww) {
        log_sync_event("Mapping failed: WP $from_wp or $to_wp");
        return;
    }

    $meta = SYNC_META_PATH . "last_synced_wp-ww_{$from_wp}_{$to_wp}.txt";
    $last_id = get_last_synced_id($meta);

    $msgs = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM wp_bp_messages_messages WHERE sender_id = %d AND id > %d ORDER BY id ASC", $from_wp, $last_id
    ));

    foreach ($msgs as $msg) {
        $content = mysqli_real_escape_string($ww_db_conn, $msg->message);
        $timestamp = strtotime($msg->date_sent);

        mysqli_query($ww_db_conn, "INSERT INTO Wo_Messages (from_id, to_id, text, time, seen, deleted_one, deleted_two) 
            VALUES ($from_ww, $to_ww, '$content', $timestamp, 0, 0, 0)");

        set_last_synced_id($meta, $msg->id);
    }
}
?>