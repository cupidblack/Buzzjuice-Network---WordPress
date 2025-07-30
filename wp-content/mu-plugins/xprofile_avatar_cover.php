<?php
add_action('bp_core_avatar_uploaded', 'update_avatar_and_cover_xprofile_fields');
add_action('xprofile_data_after_save', 'update_avatar_and_cover_xprofile_fields', 20, 1);

function update_avatar_and_cover_xprofile_fields($data) {
    static $has_run = [];

    // Determine user ID
    $user_id = is_object($data) && isset($data->user_id) ? (int)$data->user_id : (is_numeric($data) ? (int)$data : 0);
    if (!$user_id || isset($has_run[$user_id])) {
        //error_log("⚠️ Skipped avatar/cover update for user $user_id (invalid or already run)");
        return;
    }
    $has_run[$user_id] = true;

    // Fetch avatar and cover URLs as-is
    $avatar_url = bp_core_fetch_avatar([
        'item_id' => $user_id,
        'object'  => 'user',
        'type'    => 'full',
        'html'    => false
    ]);

    $cover_url = bp_attachments_get_attachment('url', [
        'item_id'    => $user_id,
        'object_dir' => 'members',
        'type'       => 'cover-image'
    ]);

    // Log for debugging
    //error_log("🖼 Avatar URL for user $user_id: $avatar_url");
    //error_log("📸 Cover URL for user $user_id: $cover_url");

    if (function_exists('xprofile_set_field_data')) {
        $current_avatar = xprofile_get_field_data('avatar', $user_id);
        $current_cover  = xprofile_get_field_data('cover', $user_id);

        if (!empty($avatar_url) && $avatar_url !== $current_avatar) {
            xprofile_set_field_data('avatar', $user_id, $avatar_url);
            //error_log("✅ Updated xProfile 'avatar' for user $user_id.");
        }

        if (!empty($cover_url) && $cover_url !== $current_cover) {
            xprofile_set_field_data('cover', $user_id, $cover_url);
            //error_log("✅ Updated xProfile 'cover' for user $user_id.");
        }
    } else {
        error_log("❌ xprofile_set_field_data not available for user $user_id.");
    }
}



add_filter('bp_core_fetch_avatar_url', 'custom_bp_avatar_from_xprofile', 10, 2);
function custom_bp_avatar_from_xprofile($avatar_url, $params) {
    if (
        !isset($params['object']) ||
        $params['object'] !== 'user' ||
        empty($params['item_id'])
    ) {
        return $avatar_url;
    }

    $user_id  = (int) $params['item_id'];
    $site_url = rtrim(home_url(), '/'); // https://buzzjuice.net

    if (function_exists('xprofile_get_field_data')) {
        $custom_avatar = xprofile_get_field_data('avatar', $user_id);

        if (!empty($custom_avatar)) {
            $custom_avatar = trim($custom_avatar);

            // CASE 1: If URL contains repeated 'https://buzzjuice.net/streams/'
            $pattern = '#(https://buzzjuice\.net/streams/)+#';
            if (preg_match($pattern, $custom_avatar)) {
                $custom_avatar = preg_replace($pattern, '', $custom_avatar);
                $custom_avatar = '/../streams/' . ltrim($custom_avatar, '/');
            }

            // CASE 2: If it starts with 'upload/', prepend '/../streams/'
            elseif (strpos(ltrim($custom_avatar, '/'), 'upload/') === 0) {
                $custom_avatar = '/../streams/' . ltrim($custom_avatar, '/');
            }

            // CASE 3: If it's a relative path, prepend site + '/streams/'
           /* elseif (!preg_match('#^https?://#i', $custom_avatar)) {
                $custom_avatar = $site_url . '/streams/' . ltrim($custom_avatar, '/');
            }*/

            // Add cache-busting versioning
            $custom_avatar .= (strpos($custom_avatar, '?') === false ? '?' : '&') . 'v=' . time();

            return esc_url($custom_avatar);
        }
    }

    return $avatar_url;
}




add_filter('bp_core_fetch_avatar', 'custom_bp_avatar_html_override', 10, 2);
function custom_bp_avatar_html_override($html, $params) {
    if (
        !isset($params['object']) || $params['object'] !== 'user' ||
        empty($params['item_id'])
    ) {
        return $html;
    }

    $user_id = (int) $params['item_id'];

    if (function_exists('xprofile_get_field_data')) {
        $custom_avatar = xprofile_get_field_data('avatar', $user_id);

        if (!empty($custom_avatar)) {
            // Normalize URL to prevent multiple 'https://buzzjuice.net/streams/' repetitions
            $site_streams_url = 'https://buzzjuice.net/streams/';
            $stream_relative  = '/../streams/';

            if (strpos($custom_avatar, $site_streams_url) === 0) {
                // Remove all occurrences of the site_streams_url
                while (strpos($custom_avatar, $site_streams_url) !== false) {
                    $custom_avatar = str_replace($site_streams_url, '', $custom_avatar);
                }
                // Then prepend with /../streams/
                $custom_avatar = $stream_relative . $custom_avatar;
            }

            // If it starts with 'upload/', prepend /../streams/
            elseif (strpos($custom_avatar, 'upload/') === 0) {
                $custom_avatar = $stream_relative . $custom_avatar;
            }

            // Cache busting
            $custom_avatar .= (strpos($custom_avatar, '?') === false ? '?' : '&') . 'v=' . time();

            // Render image
            $width  = !empty($params['width'])  ? (int)$params['width']  : 150;
            $height = !empty($params['height']) ? (int)$params['height'] : 150;
            $class  = esc_attr($params['class'] ?? 'avatar');
            $alt    = esc_attr($params['alt'] ?? 'User avatar');
            $style  = ($params['type'] === 'full') ? 'style="object-fit: cover;"' : '';

            return '<img src="' . esc_attr($custom_avatar) . '" class="' . $class . '" width="' . $width . '" height="' . $height . '" alt="' . $alt . '" ' . $style . ' />';
        }
    }

    return $html;
}





// Hook into cover image fetch

// Put in your theme's functions.php or a custom plugin


add_filter('bp_attachments_pre_get_attachment', 'custom_bp_cover_from_xprofile', 10, 2);
function custom_bp_cover_from_xprofile($pre_value, $args) {
    if (
        empty($args['object_dir']) || 
        empty($args['item_id']) || 
        empty($args['type']) || 
        $args['object_dir'] !== 'members' || 
        $args['type'] !== 'cover-image'
    ) {
        return $pre_value;
    }

    $user_id = (int) $args['item_id'];

    if (!function_exists('xprofile_get_field_data')) {
        return $pre_value;
    }

    $custom_cover = xprofile_get_field_data('cover', $user_id);

    if (empty($custom_cover)) {
        return $pre_value;
    }

    // Clean slashes and remove spaces
    $custom_cover = trim($custom_cover);
    $custom_cover = ltrim($custom_cover, '/');

    // Step 1: Remove repeated domain prefixes (even nested ones)
    $patterns_to_strip = [
        'https://buzzjuice.net/streams/',
        'http://buzzjuice.net/streams/',
        'https://buzzjuice.net/streams',
        'http://buzzjuice.net/streams',
    ];

    foreach ($patterns_to_strip as $pattern) {
        while (stripos($custom_cover, $pattern) === 0) {
            $custom_cover = substr($custom_cover, strlen($pattern));
        }
    }

    // Step 2: Ensure it starts with 'upload'
    $custom_cover = ltrim($custom_cover, '/');
    if (stripos($custom_cover, 'upload/') !== 0) {
        return $pre_value; // skip malformed paths
    }

    // Step 3: Prepend with /../streams/
    $custom_cover = '/../streams/' . $custom_cover;

    // Step 4: Add cache-busting timestamp
    $custom_cover .= (strpos($custom_cover, '?') === false ? '?' : '&') . 'v=' . time();

    return esc_url_raw($custom_cover);
}






/*
function sanitize_streams_avatar_cover_urls() {
    if (!function_exists('bp_get_members') || !function_exists('xprofile_get_field_data') || !function_exists('xprofile_set_field_data')) {
        return;
    }

    $users = get_users(['fields' => ['ID']]);

    foreach ($users as $user) {
        $user_id = $user->ID;

        foreach (['avatar', 'cover'] as $field) {
            $original_url = xprofile_get_field_data($field, $user_id);

            if (empty($original_url) || !is_string($original_url)) {
                continue;
            }

            $cleaned = $original_url;

            // Step 1 & 5: Remove all instances of 'https://buzzjuice.net/streams/'
            $cleaned = str_replace('https://buzzjuice.net/streams/', '', $cleaned);

            // Step 2: If starts with 'upload/', prepend '/../streams/'
            if (strpos($cleaned, 'upload/') === 0) {
                $cleaned = '/../streams/' . $cleaned;
            }

            // Step 1 again: If it previously started with 'https://.../streams/.../streams/...'
            // Now remove any remaining repeated '/../streams/' and keep only one
            while (strpos($cleaned, '/../streams/../streams/') !== false) {
                $cleaned = str_replace('/../streams/../streams/', '/../streams/', $cleaned);
            }

            // Step 3: Ensure only one '/../streams/' at the beginning
            $cleaned = preg_replace('#^(\.\./streams/)+#', '/../streams/', $cleaned);

            // Step 4: Remove all &v= queries (keep only the first one if needed)
            // Keep up to first '&' and discard the rest
            $parts = explode('&v=', $cleaned);
            $cleaned = $parts[0]; // Keep the main URL only (before extra versions)

            // Re-save only if changed
            if ($cleaned !== $original_url) {
                xprofile_set_field_data($field, $user_id, $cleaned);
            }
        }
    }
}

add_action('admin_init', 'sanitize_streams_avatar_cover_urls'); */