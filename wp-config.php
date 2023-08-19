<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'teachswell' );

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
define( 'AUTH_KEY',         'T]=(V{G#jVrarq]*1XOJUUhC15dI@YU|Q!Ic~G|)F~qOx?e7r5,gGU:kqEw{{@pX' );
define( 'SECURE_AUTH_KEY',  'Jw_<9nQD-&M0oIsE-323X?ge4Gz<gDbA;%8RHKs 18vjz.Hck{|rn$1fMA3u8N&_' );
define( 'LOGGED_IN_KEY',    '_1DnsaZ/)-?#)6wS^msGT.aR1+hv!R5<j9s[;JlP`rOKd)#>Z*H+UEc~HX^1P<)&' );
define( 'NONCE_KEY',        'MO]SLF(mhZ=zQ&pD_F~-.rP1e6B8wOb`TR3RqIHO:k0`zA9%bYyRP[>$CB^gM%4q' );
define( 'AUTH_SALT',        'tw2.(ULlOqQD6=6A:*F7RQ~neM!o^q});m~{l48Y!af!L-kJ3&hf2h5Vi8g/~B^~' );
define( 'SECURE_AUTH_SALT', 'qs& 43^:$f`F[[HJC`jvs<Jh03AAeNzr1q&Q~/Hy_`kl]bBF,gEqOX?QyF>!T}{6' );
define( 'LOGGED_IN_SALT',   'uegt9!)wO@X`J?XHHt#0q YR=M2|{h[.ob/_>PY0A ?(xK6~s$y+gUdp$B>}{0Ew' );
define( 'NONCE_SALT',       'y+Ql{RUt?zr]0T1gvB3{}_k U}tjz>]5Z^qeY*M)L&CAJa*xKg>La0LtlN&k8:)7' );

/**#@-*/

/**
 * WordPress database table prefix.
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
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
