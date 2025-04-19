<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package BuddyBoss_Theme
 */

?>

<?php do_action( THEME_HOOK_PREFIX . 'end_content' ); ?>

</div><!-- .bb-grid -->
</div><!-- .container -->
</div><!-- #content -->

<?php do_action( THEME_HOOK_PREFIX . 'after_content' ); ?>

<?php do_action( THEME_HOOK_PREFIX . 'before_footer' ); ?>
<?php do_action( THEME_HOOK_PREFIX . 'footer' ); ?>
<?php do_action( THEME_HOOK_PREFIX . 'after_footer' ); ?>

</div><!-- #page -->

<?php do_action( THEME_HOOK_PREFIX . 'after_page' ); ?>

<?php wp_footer(); ?>

<!--BlueCrownR&D: Footer Menu -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="http://127.0.0.1/footer-menu/footer-menu.css">

<div class="bottom-footer-menu">
    <ul>
        <li><a href="/buzzjuice.net/streams/messages"><i class="fa fa-comments"></i> Chat</a></li>
        <li><a href="/buzzjuice.net/streams/directory/market"><i class="fa fa-th-list"></i> Catalog</a></li>
        <li><a href="/buzzjuice.net/streams"><i class="fa fa-image"></i> Streams</a></li>
        <li><a href="/buzzjuice.net"><i class="fa fa-home"></i> Cabin</a></li>
        <li><a href="https://koware.org/academy/"><i class="fa fa-leanpub"></i> Academy</a></li>
        <li><a href="/buzzjuice.net/social/login"><i class="fa fa-users"></i> Social</a></li>
        <li><a href="/buzzjuice.net/streams/setting"><i class="fa fa-tachometer"></i> Dashboard</a></li>
    </ul>
</div>

</body>
</html>
