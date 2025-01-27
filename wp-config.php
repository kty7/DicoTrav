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
define( 'DB_NAME', 'dicoTrav' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

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
define( 'AUTH_KEY',         '%h^[1ONB8_PV_]QvB.aXR&|($3Ss%GKIj*BVlf3XZ;%d!n%X5~_P}Mp:+m!~?WGa' );
define( 'SECURE_AUTH_KEY',  '5e7mIGZ;sH%(yVB,|8e6,f:@:A&c%w.V:^LKId}b :SPbCaz[N]8wP$BFcmjvor~' );
define( 'LOGGED_IN_KEY',    ')CY-%&YF_MU7qJWS;%dEj.:yQmFmO{1(GiM*Pv1L?D4#G:IT-ej2SUG|Xg{NJimn' );
define( 'NONCE_KEY',        'R;R|D?bqknHr4CV)Pmtxzsj>.;S&r[!?9ZVkSpW9Rxa;J7usBb|w{V$1I)EB4x-2' );
define( 'AUTH_SALT',        'KG#OAh9J*hu)+2e>xjqoGIvC59VGi)YT&;7k2VrW?cz] *Ztif8Ey1_f/3-dlLp?' );
define( 'SECURE_AUTH_SALT', '$G1.tU&$m8g]{oe3==o0n=SkF>-Wux1Ue$g77,oS]va&!c}<N<R3G.A(g &[;=oW' );
define( 'LOGGED_IN_SALT',   '<5B=lh%ihz8Xh.48gYOV-Kq[(>E6 ;@,.Hb-Tr%G@7osaDq it>hA~k;gCj=q-gE' );
define( 'NONCE_SALT',       '~UA{-`:Ve;NOG{Gt]R$?MFI1nCyJyd]p#:<6?M&_E?Z?8w|yOszM zlpZNUZ?[u=' );

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
