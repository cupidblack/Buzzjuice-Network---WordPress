<?php
/**
 * Plugin Name: Blue Crown Platform
 * Description: Automatically assigns a nickname during registration based on email alias.
 * Version: 1.0
 * Author: Blue Crown R&D
 */

//*********************************************************************
//************REGISTRATION EMAIL USERNAME SYNC - START ****************
//*********************************************************************

// Enqueue the JavaScript file
function blue_crown_enqueue_scripts() {
    wp_enqueue_script('live-nickname', plugin_dir_url(__FILE__) . 'assets/js/live-nickname.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'blue_crown_enqueue_scripts');

// Add inline script to pre-fill nickname if email is already present
function blue_crown_pre_fill_nickname() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var emailField = $('#signup_email');
            var nicknameField = $('#field_3');

            // Pre-fill nickname if email field is already filled
            if (emailField.val()) {
                var emailAlias = emailField.val().split('@')[0];
                nicknameField.val(emailAlias);
            }
        });
    </script>
    <?php
}
add_action('wp_footer', 'blue_crown_pre_fill_nickname');

//*********************************************************************
//************REGISTRATION EMAIL USERNAME SYNC - STOP ****************
//*********************************************************************

// Activation hook to apply user roles
function blue_crown_activate_plugin() {
    require_once plugin_dir_path(__FILE__) . 'includes/user-roles.php';
}
register_activation_hook(__FILE__, 'blue_crown_activate_plugin');

/* RESET ADMIN ROLE 
function reset_admin_role() {
    $role = get_role('administrator');
    if ($role) {
        $role->add_cap('manage_options');
    }
}
add_action('init', 'reset_admin_role');


add_action('admin_init', function() {
    if (is_admin() && current_user_can('manage_options')) {
        echo '<pre>';
        print_r(wp_get_current_user());
        echo '</pre>';
        exit;
    }
});

*/