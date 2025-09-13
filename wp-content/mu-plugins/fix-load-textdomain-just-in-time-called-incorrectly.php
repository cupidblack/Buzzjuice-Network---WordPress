<?php
/**
 * BuddyBoss WP 6.7+ compatibility shim
 * Fixes "Translation loading triggered too early" for buddyboss domain.
 */

add_action( 'plugins_loaded', function() {
    if ( class_exists( 'BB_ReadyLaunch_Onboarding' ) ) {
        // Redefine the constructor via Reflection
        $onboarding = BB_ReadyLaunch_Onboarding::instance();

        if ( has_action( 'init', [ $onboarding, 'load_textdomain' ] ) === false ) {
            // Remove any early direct calls by replacing property
            $onboarding->loaded_textdomain = true;
        }
    }

    add_action( 'init', function() {
        load_plugin_textdomain(
            'buddyboss',
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/../buddyboss-platform/languages/'
        );
    }, 20 );
});

