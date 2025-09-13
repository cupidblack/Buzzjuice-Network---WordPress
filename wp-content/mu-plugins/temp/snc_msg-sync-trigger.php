<?php
/*
Plugin Name: Bluecrown WP Message Sync
Description: Syncs outgoing messages from BP Better Messages or BuddyBoss Messaging to WoWonder via bridge, including media.
Version: 1.0.0
Author: pineapplebuzzjuice
*/

if (!defined('ABSPATH')) exit;

require_once '/home/koware/public_html/buzzjuice.net/data/db_helpers.php';
require_once '/home/koware/public_html/buzzjuice.net/data/sync_messages/sync_helpers.php';

// Helper: Send to bridge
function bluecrown_wp_send_to_wowonder_bridge($from_user_id, $to_user_id, $message, $media_url = null) {
    $bridge_url = site_url('/wp-content/mu-plugins/snc_msg-wp-ww.php');

    $payload = [
        'from_user_id' => $from_user_id,
        'to_user_id'   => $to_user_id,
        'message'      => $message,
    ];
    if ($media_url) $payload['media_url'] = $media_url;

    $args = [
        'method'      => 'POST',
        'body'        => $payload,
        'timeout'     => 15,
        'headers'     => ['Accept' => 'application/json'],
    ];

    $response = wp_remote_post($bridge_url, $args);
    if (is_wp_error($response)) {
        log_sync_event("Bridge WP→WW request error: " . $response->get_error_message());
        return false;
    }
    $code = wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    log_sync_event("Bridge WP→WW request complete: code=$code, body=$body");
    return ($code === 200);
}

// BP Better Messages: After message save
add_action('better_messages_message_after_save', function($message_obj) {
    // BP_Messages_Message object: $message_obj
    $from_user_id = $message_obj->sender_id;
    $recipients   = $message_obj->recipients; // array mapping user_id => true
    $content      = $message_obj->message;
    $media_url    = isset($message_obj->media_url) ? $message_obj->media_url : null;

    // Handle multiple recipients
    foreach (array_keys($recipients) as $to_user_id) {
        // Avoid self-message
        if ($from_user_id == $to_user_id) continue;
        bluecrown_wp_send_to_wowonder_bridge($from_user_id, $to_user_id, $content, $media_url);
    }
}, 10, 1);

// BuddyBoss (BuddyPress): After message sent
add_action('messages_message_sent', function($message_obj) {
    // BP_Messages_Message object: $message_obj
    $from_user_id = $message_obj->sender_id;
    $recipients   = $message_obj->recipients; // array of BP_Messages_Recipient objects
    $content      = $message_obj->message;
    $media_url    = isset($message_obj->media_url) ? $message_obj->media_url : null;

    // Recipients is array of objects, extract user_id
    foreach ($recipients as $recipient) {
        $to_user_id = is_object($recipient) && isset($recipient->user_id) ? $recipient->user_id : intval($recipient);
        if ($from_user_id == $to_user_id) continue;
        bluecrown_wp_send_to_wowonder_bridge($from_user_id, $to_user_id, $content, $media_url);
    }
}, 10, 1);

// REST API support: After REST message creation (BuddyBoss REST endpoint)
add_action('bp_rest_messages_create_item', function($response, $request, $message_obj, $thread_id) {
    if (!is_object($message_obj)) return;
    $from_user_id = $message_obj->sender_id;
    $recipients   = $message_obj->recipients;
    $content      = $message_obj->message;
    $media_url    = isset($message_obj->media_url) ? $message_obj->media_url : null;
    foreach ($recipients as $recipient) {
        $to_user_id = is_object($recipient) && isset($recipient->user_id) ? $recipient->user_id : intval($recipient);
        if ($from_user_id == $to_user_id) continue;
        bluecrown_wp_send_to_wowonder_bridge($from_user_id, $to_user_id, $content, $media_url);
    }
}, 10, 4);

// Optional: Logging function for debugging
if (!function_exists('log_sync_event')) {
    function log_sync_event($msg) {
        $logfile = WP_CONTENT_DIR . '/logs/sync.log';
        $logdir = dirname($logfile);
        if (!is_dir($logdir)) mkdir($logdir, 0777, true);
        file_put_contents($logfile, "[" . date('Y-m-d H:i:s') . "] $msg\n", FILE_APPEND);
    }
}

?>