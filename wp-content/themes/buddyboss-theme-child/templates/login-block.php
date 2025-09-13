<?php
/**
 * Minimal, BuddyBoss/BuddyPress-compatible login block for activation page.
 * - Shake effect on error
 * - Show password toggle
 * - Placeholders instead of labels
 * - "Forgot password?" link
 * - "Sign in" left, "Create account" right
 */

// Don't show if already logged in
if ( is_user_logged_in() ) return;

// Get error state for shake
$shake = !empty($_GET['login']) && $_GET['login'] === 'failed';

// You can customize the redirect_to as needed
$redirect = home_url();
?>

<div id="custom-login-block" class="<?php echo $shake ? 'login-shake' : ''; ?>" style="max-width:400px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1em;">
        <span style="font-weight:bold;"><?php esc_html_e( 'Sign in', 'buddyboss' ); ?></span>
        <?php if ( get_option('users_can_register') ) : ?>
            <a href="<?php echo esc_url( wp_registration_url() ); ?>" style="text-align:right;"><?php esc_html_e( 'Create an Account', 'buddyboss' ); ?></a>
        <?php endif; ?>
    </div>
    <?php if ($shake): ?>
        <p class="login-error" style="color:#a00;text-align:center;"><?php esc_html_e('Incorrect username or password. Please try again.', 'buddyboss'); ?></p>
    <?php endif; ?>
    <form name="loginform" id="loginform-block" action="<?php echo esc_url( wp_login_url() ); ?>" method="post" novalidate autocomplete="off">
        <p>
            <input type="text" name="log" id="user_login" class="input" value="" size="20"
                   placeholder="<?php esc_attr_e( 'Email Address or Username', 'buddyboss' ); ?>" required autocomplete="username" autocapitalize="off" />
        </p>
        <p style="position:relative;">
            <input type="password" name="pwd" id="user_pass" class="input" value="" size="20"
                   placeholder="<?php esc_attr_e( 'Password', 'buddyboss' ); ?>" required autocomplete="current-password" spellcheck="false" />
            <button type="button" id="toggle-password" tabindex="-1" style="position:absolute;right:4px;top:2px;background:transparent;border:none;cursor:pointer;">
                <span id="toggle-password-icon" aria-label="<?php esc_attr_e('Show password', 'buddyboss'); ?>">👁️</span>
            </button>
        </p>
        <p style="display:flex;justify-content:space-between;align-items:center;">
            <label style="font-weight:normal;">
                <input name="rememberme" type="checkbox" id="rememberme" value="forever" />
                <?php esc_html_e( 'Remember Me', 'buddyboss' ); ?>
            </label>
            <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php esc_html_e( 'Forgot Password?', 'buddyboss' ); ?></a>
        </p>
        <p class="submit" style="margin-bottom:0;">
            <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary" value="<?php esc_attr_e( 'Log In', 'buddyboss' ); ?>" />
            <input type="hidden" name="redirect_to" value="<?php echo esc_url( $redirect ); ?>" />
        </p>
    </form>
    <p class="tos-link" style="font-size: 0.85em; text-align:center;">
        <?php esc_html_e( 'By logging in, you agree to our', 'buddyboss' ); ?>
        <a href="/terms-of-service"><?php esc_html_e( 'Terms of Service', 'buddyboss' ); ?></a>
    </p>
</div>

<style>
button#toggle-password {
    padding: 0px;
}
input#user_login, input#user_pass {
    min-width: 100%;
}
#custom-login-block.login-shake { animation: shake 0.35s linear 1; }
@keyframes shake {
    10%, 90% { transform: translateX(-2px); }
    20%, 80% { transform: translateX(4px); }
    30%, 50%, 70% { transform: translateX(-8px); }
    40%, 60% { transform: translateX(8px); }
}
#toggle-password { font-size: 1.1em; }
#toggle-password:focus { outline: 2px solid #0073aa; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var toggle = document.getElementById('toggle-password');
    var pwd = document.getElementById('user_pass');
    var icon = document.getElementById('toggle-password-icon');
    if (toggle && pwd) {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.textContent = '🙈';
            } else {
                pwd.type = 'password';
                icon.textContent = '👁️';
            }
        });
    }
    // Optional: autofocus on error
    <?php if ($shake): ?>
        setTimeout(function(){document.getElementById('user_login').focus();}, 300);
    <?php endif; ?>
});
</script>