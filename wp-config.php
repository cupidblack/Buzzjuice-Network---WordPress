<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local_buzzjuice' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'AIs?L3MsbZI:k8k:;S<2,6)j>$fiCZ`lqg]S62_%4ixqfIj@na4<TLfJ[^oFqN]Z' );
define( 'SECURE_AUTH_KEY',  ':YqG>UU5.<Z%>dOkE$ndY!Fp:wq[O2d/XtMyUr^:r2l$Q2U91}T*RtB:,dNQ^yHh' );
define( 'LOGGED_IN_KEY',    'hWOCcC=[PaUD55b5)eq}l-);q!i}hwL2Z_{j1[c&g+!-w?S/eKi3G&=yJ>d~:kCO' );
define( 'NONCE_KEY',        'EA!R5_IE/vy/T+9wV7}h,wHWmhtg-Mv<)%MEa![:H$fcYWU$DesGRy[Cq)/Od>IN' );
define( 'AUTH_SALT',        'f|]5aKb95:KWaPq9~7r=LHu]Ud2F0B(u_g_+a<Z]96?p^`v?%,^*(ZRcX*jS&:.T' );
define( 'SECURE_AUTH_SALT', '@%;l(5>Nlhok2?*cnO&My3x4wyW(<}#d,qWNE4PSL7[^1@BG22nBXK3M#:T#NgQ.' );
define( 'LOGGED_IN_SALT',   'q!AkA[-.T5$;<or8ZoCbsp_<{dAtL~`llw<g@v|GE83HV7hK&Y-,[e,k0v7ji# `' );
define( 'NONCE_SALT',       '2S<l@M$H/vp&YmmU>dD&+&}=)wV,1,@C10zBMhy&]rI0c cQQ5XI-AI,.WA*s^(e' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);
/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
