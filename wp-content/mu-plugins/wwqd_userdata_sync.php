<?php
/**
 * Unified WordPress to WoWonder and QuickDate user metadata sync
 *
 * This MU plugin automatically syncs WordPress user metadata and BuddyBoss xProfile data
 * to both WoWonder and QuickDate platforms whenever a user profile is updated.
 */

require_once __DIR__ . '/../../shared/wwqd_bridge.php';

// WordPress hooks for user metadata updates
add_action('profile_update', function($user_id) {
    sync_wp_user_to_platforms($user_id, 'metadata');
}, 10, 1);

add_action('edit_user_profile_update', function($user_id) {
    sync_wp_user_to_platforms($user_id, 'metadata');
}, 10, 1);

add_action('personal_options_update', function($user_id) {
    sync_wp_user_to_platforms($user_id, 'metadata');
}, 10, 1);

// BuddyBoss xProfile updates
add_action('xprofile_data_after_save', function($data) {
    $user_id = is_object($data) ? $data->user_id : $data;
    sync_wp_user_to_platforms($user_id, 'xprofile');
}, 10, 1);

// Avatar updates specifically for QuickDate
add_action('bp_core_avatar_uploaded', function($user_id) {
    $user = get_userdata($user_id);
    if (!$user) return;
    
    $qd_id = get_quickdate_id_by_email($user->user_email);
    if (!$qd_id) return;
    
    $avatar_url = function_exists('bp_core_fetch_avatar') ? bp_core_fetch_avatar([
        'item_id' => $user_id,
        'object'  => 'user', 
        'type'    => 'full',
        'html'    => false
    ]) : '';
    
    if ($avatar_url) {
        do_platform_update(
            get_qd_db_conn(),
            QD_USERS_TABLE,
            'id',
            $qd_id,
            ['avatar' => normalize_avatar_url($avatar_url)],
            'QuickDate'
        );
    }
}, 10, 1);