<?php
/*
Plugin Name: Bluecrown WP Message Status & Actions Sync
Description: Syncs read/unread, reactions, pin/fav, deletes from BPBM/BuddyBoss to WoWonder.
Version: 1.0.0
Author: pineapplebuzzjuice
*/

if (!defined('ABSPATH')) exit;
require_once '/home/koware/public_html/buzzjuice.net/data/db_helpers.php';
require_once '/home/koware/public_html/buzzjuice.net/data/sync_messages/sync_helpers.php';

// Helper: Send status/action to WoWonder
function bluecrown_wp_status_to_ww($action, $data) {
    $ww_api_map = [
        'read'     => 'read_chats.php',
        'react'    => 'react_message.php',
        'pin'      => 'pin_message.php',
        'fav'      => 'fav_message.php',
        'delete'   => 'delete_message.php'
    ];
    if (!isset($ww_api_map[$action])) return false;
    $url = 'https://buzzjuice.net/api/v2/endpoints/' . $ww_api_map[$action];
    $args = [
        'method' => 'POST',
        'body'   => $data,
        'timeout'=> 15,
        'headers'=> ['Accept' => 'application/json']
    ];
    $resp = wp_remote_post($url, $args);
    if (is_wp_error($resp)) {
        log_sync_event("Status WP→WW $action error: " . $resp->get_error_message());
        return false;
    }
    log_sync_event("Status WP→WW $action complete: code=" . wp_remote_retrieve_response_code($resp));
    return true;
}

// Message ID mapping helper
function bp_to_ww_message_id($bp_message_id) {
    // Use mapping helper from sync_helpers.php
    return sync_map_get_ww($bp_message_id);
}

// Message read
add_action('better_messages_mark_message_read', function($message_id, $user_id) {
    $ww_db = get_wowonder_db();
    $ww_user_id = wp_to_ww($user_id, $ww_db);
    $ww_message_id = bp_to_ww_message_id($message_id);
    if ($ww_user_id && $ww_message_id) {
        bluecrown_wp_status_to_ww('read', [
            'user_id' => $ww_user_id,
            'message_id' => $ww_message_id
        ]);
    } else {
        log_sync_event("WP→WW read mapping failed: WP($user_id,$message_id) → WW($ww_user_id,$ww_message_id)");
    }
}, 10, 2);

// Message react
add_action('better_messages_message_reacted', function($message_id, $user_id, $reaction) {
    $ww_db = get_wowonder_db();
    $ww_user_id = wp_to_ww($user_id, $ww_db);
    $ww_message_id = bp_to_ww_message_id($message_id);
    if ($ww_user_id && $ww_message_id) {
        bluecrown_wp_status_to_ww('react', [
            'user_id' => $ww_user_id,
            'message_id' => $ww_message_id,
            'reaction' => $reaction
        ]);
    } else {
        log_sync_event("WP→WW react mapping failed: WP($user_id,$message_id) → WW($ww_user_id,$ww_message_id)");
    }
}, 10, 3);

// Message pin
add_action('better_messages_message_pinned', function($message_id, $user_id) {
    $ww_db = get_wowonder_db();
    $ww_user_id = wp_to_ww($user_id, $ww_db);
    $ww_message_id = bp_to_ww_message_id($message_id);
    if ($ww_user_id && $ww_message_id) {
        bluecrown_wp_status_to_ww('pin', [
            'user_id' => $ww_user_id,
            'message_id' => $ww_message_id
        ]);
    } else {
        log_sync_event("WP→WW pin mapping failed: WP($user_id,$message_id) → WW($ww_user_id,$ww_message_id)");
    }
}, 10, 2);

// Message fav
add_action('better_messages_message_faved', function($message_id, $user_id) {
    $ww_db = get_wowonder_db();
    $ww_user_id = wp_to_ww($user_id, $ww_db);
    $ww_message_id = bp_to_ww_message_id($message_id);
    if ($ww_user_id && $ww_message_id) {
        bluecrown_wp_status_to_ww('fav', [
            'user_id' => $ww_user_id,
            'message_id' => $ww_message_id
        ]);
    } else {
        log_sync_event("WP→WW fav mapping failed: WP($user_id,$message_id) → WW($ww_user_id,$ww_message_id)");
    }
}, 10, 2);

// Message delete
add_action('better_messages_message_deleted', function($message_id, $user_id) {
    $ww_db = get_wowonder_db();
    $ww_user_id = wp_to_ww($user_id, $ww_db);
    $ww_message_id = bp_to_ww_message_id($message_id);
    if ($ww_user_id && $ww_message_id) {
        bluecrown_wp_status_to_ww('delete', [
            'user_id' => $ww_user_id,
            'message_id' => $ww_message_id
        ]);
    } else {
        log_sync_event("WP→WW delete mapping failed: WP($user_id,$message_id) → WW($ww_user_id,$ww_message_id)");
    }
}, 10, 2);

?>