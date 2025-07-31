<?php
require_once __DIR__ . '/../db_helpers.php';
require_once __DIR__ . '/sync_helpers.php';

function fix_incomplete_threads($wp_db_conn) {
    $q = "SELECT thread_id, COUNT(user_id) as total FROM wp_bp_messages_recipients 
          GROUP BY thread_id HAVING total < 2";
    $res = mysqli_query($wp_db_conn, $q);
    while ($row = mysqli_fetch_assoc($res)) {
        $thread_id = $row['thread_id'];

        // Find sender from messages table
        $result = mysqli_query($wp_db_conn, "SELECT sender_id FROM wp_bp_messages_messages WHERE thread_id = $thread_id ORDER BY id ASC LIMIT 1");
        $sender = mysqli_fetch_assoc($result)['sender_id'];

        // Find recipient (last non-sender from messages table)
        $r = mysqli_query($wp_db_conn, "SELECT sender_id FROM wp_bp_messages_messages WHERE thread_id = $thread_id ORDER BY id DESC LIMIT 1");
        $recipient = mysqli_fetch_assoc($r)['sender_id'];

        // Insert missing recipients if needed
        if ($sender) {
            mysqli_query($wp_db_conn, "INSERT IGNORE INTO wp_bp_messages_recipients (user_id, thread_id, unread_count, sender_only, is_deleted) 
                VALUES ($sender, $thread_id, 0, 1, 0)");
        }
        if ($recipient && $recipient != $sender) {
            mysqli_query($wp_db_conn, "INSERT IGNORE INTO wp_bp_messages_recipients (user_id, thread_id, unread_count, sender_only, is_deleted) 
                VALUES ($recipient, $thread_id, 1, 0, 0)");
        }

        log_sync_event("Fixed thread $thread_id by adding sender=$sender and recipient=$recipient");
    }
}

// Usage
$wp_db_conn = get_wp_db_conn();
fix_incomplete_threads($wp_db_conn);
?>