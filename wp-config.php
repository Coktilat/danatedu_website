<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'danatedu_danat' );

/** MySQL database username */
define( 'DB_USER', 'danatedu_danat' );

/** MySQL database password */
define( 'DB_PASSWORD', 'CV=hY4WOj1.&' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '?O/|#;.p7[51y(zi?m/h1yQhK{MjfpR?Z.QW}c]2Wb}YKTv4-PP?gi Xw3I8+<dl' );
define( 'SECURE_AUTH_KEY',  'vfCEw^^$~}mm6.~&HtZlQB4t(,_`eNZXEsI>_=S3Ew!5Bm?p~N`%4Sr}GY&$Rkf<' );
define( 'LOGGED_IN_KEY',    'c<}3hl:;|f$|2]%;.8I6?wf{h8}#L+fg&=OGY&VoewmA|AN0!-O:=QU(bn&%W2!j' );
define( 'NONCE_KEY',        'Zv@BG38F$LNpuO^}TiZEJT3qT>7P-zLe]f=t/a-(n!@rDK E>eNW}[zn}*<EKIn#' );
define( 'AUTH_SALT',        'Q<ad-5}SF(_Oe^A8l*Wvs%pX=PR?TfkMXCixyf!kI]p`0u#-zRtPWnZ)k+4pU!)d' );
define( 'SECURE_AUTH_SALT', '8)h?!0]>9y?=Oj5kUn?G?S1]s2Ep%ebmDU?Y[IFjLiVdRbQ1D=]8Os.r9Y*yL&LH' );
define( 'LOGGED_IN_SALT',   'mu:imfvs|/]y(uNb_[l?*SE/<92fE_yl60s&Ta4Gh{jOn`>@`C(v-j#.#$jW1f+^' );
define( 'NONCE_SALT',       '9.I(t6u6r1r;g}BsUk.:4=t VXxjHkax^hH,@EJJWx:B09Q{nQq$?-+H:wK0w2)F' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
