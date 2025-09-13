<?php
/**
 * Template Name: Chat Embed
 * @package BuddyBoss_Theme
 * Description: Custom template to embed Bp Better Messages chat and auto-maximize on load.
 */
get_header();
?>

<style>
    /* Optional: Hide header/footer when chat is maximized */
    .bpbm-fullscreen #masthead,
    .bpbm-fullscreen #colophon,
    .bpbm-fullscreen .site-footer,
    .bpbm-fullscreen .site-header {
        display: none !important;
    }

    /* Ensure chat stretches properly */
    .bpbm-fullscreen .site-content,
    .bpbm-fullscreen #primary {
        width: 100% !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* Optional: make the chat container fill the screen width */
    .chat-fullscreen-container {
        max-width: 100% !important;
        width: 100% !important;
        margin: 0 auto !important;
        padding: 0 !important;
    }

    .chat-fullscreen-container .bp-messages-wrap-main {
        min-height: 80vh; /* fallback if not maximized */
    }
</style>

<div id="primary" class="content-area chat-fullscreen-container">
    <main id="main" class="site-main">
        <?php
        while ( have_posts() ) :
            the_post();
            the_content(); // assumes [better_messages] shortcode is used
        endwhile;
        ?>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Delay execution slightly to allow DOM to fully render
    setTimeout(function () {
        const maximizeBtn = document.querySelector('.bpbm-maximize');
        const isAlreadyFullscreen = document.body.classList.contains('bpbm-fullscreen');

        if (maximizeBtn && !isAlreadyFullscreen) {
            maximizeBtn.click();
        }
    }, 500);
});
</script>

<?php get_footer(); ?>

