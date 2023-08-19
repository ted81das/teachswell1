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
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
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
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',          '!-.rg3[s,RROyF5M&3RUM5-1_ekPJ5!7J^-5p}^&lAQcIHo@_OyZy5yfKkS8=h/P' );
define( 'SECURE_AUTH_KEY',   'ln0r?16Th[84W@2}0r[3??yG17M{/HiIqQN!NIAX~M_lJz7`OkXOJxy3K0S:;Gl6' );
define( 'LOGGED_IN_KEY',     'eJi$q,d-ij)[2;K#{~}v{jy-=9EsmU+Y+l8sV:j%L-8R;W5|U,~n4Xgj.54 &[Kg' );
define( 'NONCE_KEY',         'rsa|Ek}C{^2mrokB<#zg6>0Sq9o_(5Hj*vcWw=J7GS^?b*^7snPU1|8b@w; z{Gq' );
define( 'AUTH_SALT',         'D;USzhk.HCm8+Wo1%2S?:D!SSn8`9:%:?cI3D<5unpYQ=Sivz2y!rB}|wnUwv&eL' );
define( 'SECURE_AUTH_SALT',  'h$ls<;J`sf**)|,/sS#[8%BOb^A6kX>V>;m_PDMqKvztcc ~aSffJzVi38C9J8?U' );
define( 'LOGGED_IN_SALT',    'GtsQ<Xc=,ohCS,IalF2z;.rZaG&8H@-_D18V02YqRZwGj]}L@gN1![eTjR5v=8r#' );
define( 'NONCE_SALT',        'hCh7]BG|enGpMshpDwNM>S%|ZLT./;Yj=]<7k<>su.Decr:`Z&!SvifSffT(AZin' );
define( 'WP_CACHE_KEY_SALT', 'Lda;:9$~>W6`z:PMC-:NGI*ACn.F+7DJX+&evZ0rn?[X}Vy}Fp8Wpr|!62&RwN#m' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
