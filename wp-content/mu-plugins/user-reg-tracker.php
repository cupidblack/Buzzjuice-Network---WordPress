<?php
/**
 * Plugin Name: User Registration Tracker
 * Description: Tracks registration source, referrer, device info, UTM, geolocation, and time on page for new BuddyBoss/BuddyPress users. Writes data to usermeta and a log file. Adds admin column.
 */

// 1. Add hidden fields to registration form (BuddyBoss/BuddyPress)
add_action('bp_before_registration_submit_buttons', function () {
    ?>
    <input type="hidden" name="registration_referrer" value="<?php echo esc_url(wp_get_referer()); ?>">
    <input type="hidden" name="registration_source" value="<?php echo esc_attr($_GET['source'] ?? ''); ?>">
    <input type="hidden" name="registration_utm_source" value="<?php echo esc_attr($_GET['utm_source'] ?? ''); ?>">
    <input type="hidden" name="registration_utm_campaign" value="<?php echo esc_attr($_GET['utm_campaign'] ?? ''); ?>">
    <input type="hidden" name="registration_initial_referrer" id="registration_initial_referrer">
    <input type="hidden" name="registration_time_on_page" id="registration_time_on_page">
    <input type="hidden" name="registration_device_info" id="registration_device_info">
    <input type="hidden" name="registration_geo_location" id="registration_geo_location">
    <?php
});

// 2. Save values to usermeta + log to file
add_action('bp_core_signup_user', function ($user_id, $user_login, $user_password, $user_email, $usermeta) {
    $fields = [
        'registration_referrer',
        'registration_source',
        'registration_utm_source',
        'registration_utm_campaign',
        'registration_initial_referrer',
        'registration_time_on_page',
        'registration_device_info',
        'registration_geo_location',
    ];

    $log_data = [
        'timestamp' => date('Y-m-d H:i:s'),
        'user_id' => $user_id,
        'user_login' => $user_login,
        'user_email' => $user_email,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
    ];

    foreach ($fields as $field) {
        $value = $_POST[$field] ?? '';
        if (!empty($value)) {
            // NOTE: Use sanitize_textarea_field for device info and geo (to allow comma/pipe), otherwise sanitize_text_field
            $sanitized = ($field === 'registration_device_info' || $field === 'registration_geo_location')
                ? sanitize_textarea_field($value)
                : sanitize_text_field($value);
            update_user_meta($user_id, $field, $sanitized);
            $log_data[$field] = $sanitized;
        }
    }

    // Write to log file
    $log_file = WP_CONTENT_DIR . '/registration_tracking.log';
    // Prevent log file from being publicly accessible via .htaccess
    if (!file_exists($log_file)) {
        file_put_contents($log_file, "DO NOT SHARE. This file contains sensitive registration meta data.\n");
    }
    file_put_contents($log_file, json_encode($log_data) . PHP_EOL, FILE_APPEND | LOCK_EX);

}, 10, 5);

// 3. Inject JavaScript into the registration page footer
add_action('wp_footer', function () {
    if (!function_exists('bp_is_register_page') || !bp_is_register_page()) return;
    ?>
    <script>
        (function () {
            const startTime = Date.now();

            // Set initial referrer (landing page before registration, not just previous page)
            const initRef = document.getElementById('registration_initial_referrer');
            if (initRef && !initRef.value) {
                // Save in localStorage so it persists if user navigates then returns
                if (!localStorage.getItem('registration_initial_referrer')) {
                    localStorage.setItem('registration_initial_referrer', document.referrer || window.location.href);
                }
                initRef.value = localStorage.getItem('registration_initial_referrer');
            }

            // Device info
            const deviceInfo = [
                navigator.userAgent,
                navigator.platform,
                screen.width + 'x' + screen.height
            ].join(' | ');
            const devField = document.getElementById('registration_device_info');
            if (devField) devField.value = deviceInfo;

            // Time spent on page (set just before submit or unload)
            function setTimeOnPage() {
                const timeSpent = Math.round((Date.now() - startTime) / 1000);
                const timeField = document.getElementById('registration_time_on_page');
                if (timeField) timeField.value = timeSpent;
            }
            // On submit
            const regForm = document.querySelector('form#signup_form, form#registerform, form[name="signup_form"]');
            if (regForm) {
                regForm.addEventListener('submit', setTimeOnPage, {capture: true});
            }
            // Also set on unload as backup (may not always work for async forms)
            window.addEventListener('beforeunload', setTimeOnPage);

            // Geo
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    const coords = position.coords.latitude + ',' + position.coords.longitude;
                    const geoField = document.getElementById('registration_geo_location');
                    if (geoField) geoField.value = coords;
                });
            }
        })();
    </script>
    <?php
});

// 4. Add registration source as a column in the Users admin table
add_filter('manage_users_columns', function($columns) {
    $columns['registration_source'] = __('Reg Source', 'user-reg-tracker');
    return $columns;
});
add_filter('manage_users_custom_column', function($value, $column_name, $user_id) {
    if ($column_name === 'registration_source') {
        return esc_html(get_user_meta($user_id, 'registration_source', true));
    }
    return $value;
}, 10, 3);

// 5. (Optional) Hide the log file from direct web access (add .htaccess if possible)
add_action('init', function() {
    $htaccess_path = WP_CONTENT_DIR . '/.htaccess';
    $log_file_rule = "\n<Files \"registration_tracking.log\">\nOrder allow,deny\nDeny from all\n</Files>\n";
    if (is_writable(WP_CONTENT_DIR) && (!file_exists($htaccess_path) || strpos(file_get_contents($htaccess_path), 'registration_tracking.log') === false)) {
        file_put_contents($htaccess_path, $log_file_rule, FILE_APPEND);
    }
});