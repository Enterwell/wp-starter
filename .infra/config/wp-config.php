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
define( 'DB_NAME', getenv( 'WP_DB_NAME' ) ?: 'wp_starter' );

/** Database username */
define( 'DB_USER', getenv( 'WP_DB_USER' ) ?: 'root' );

/** Database password */
define( 'DB_PASSWORD', getenv( 'WP_DB_PASSWORD' ) ?: '' );

/** Database hostname */
define( 'DB_HOST', getenv( 'WP_DB_HOST' ) ?: 'localhost' );

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
define( 'AUTH_KEY', '+8;mVa-# `cZQ$ZKq{_]&Y^7f04c8)Ye.>:M[#di>Qqgs)QE6ncic^r8kh{ip^IS' );
define( 'SECURE_AUTH_KEY', 'dr:I|IWwVv+g!p|P[so|{~QSNn&j>@l>m-.3~!1kUYf=%yQ|rf7a4XHJ;^.+WLd+' );
define( 'LOGGED_IN_KEY', 'LKp#Vwg!kVv220qede%}VV)[ePbLl|#!~/w|6O~;22WYIbGB&0@B9CF8d8-ix=hm' );
define( 'NONCE_KEY', ';U=uyITfR(Sx^LP+jtSM4]JMr= Z3[6`$1-DTCkz`C60M`>w:j{g&%Uz32bkq*y}' );
define( 'AUTH_SALT', '+UDN6L=GBr/UE<_|?Gv(t&A8)C6?WH^8pV*0p+Q|d{VDJC */V#=Q|? %_)e2a!1' );
define( 'SECURE_AUTH_SALT', 'Bm>Eb#+8J0qr|Uhx~eMF_>onV!Imjxn7eY$WgKCr?+ }nl(F#qaidR{y<}$}STUk' );
define( 'LOGGED_IN_SALT', 'aZQxMU2libEu(;++`vZ.Cw^LQYfLr>?36j]B6^&[Sfc6$;B{7A9RQR/dOT`ya4)Z' );
define( 'NONCE_SALT', '>=D#(n22@7yt9?N%|BSj$8I$}v%Kz]e#%vTDVQP-u@rw^<+|bM*OOxB3Q|??Ga-|' );

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
define( 'WP_DEBUG', filter_var( getenv( 'WP_DEBUG' ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? false );

/* Add any custom values between this line and the "stop editing" line. */

define( 'WP_ENVIRONMENT_TYPE', getenv( 'WP_ENVIRONMENT_TYPE' ) ?: 'development' );

define( 'WP_DEBUG_DISPLAY', filter_var( getenv( 'WP_DEBUG_DISPLAY' ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? false );
define( 'WP_DEBUG_LOG', filter_var( getenv( 'WP_DEBUG_LOG' ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? false );

define( 'DISALLOW_FILE_EDIT', filter_var( getenv( 'EW_DISALLOW_FILE_EDIT' ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? true );
define( 'DISALLOW_FILE_MODS', filter_var( getenv( 'EW_DISALLOW_FILE_MODS' ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? true );
define( 'AUTOMATIC_UPDATER_DISABLED', filter_var( getenv( 'EW_AUTOMATIC_UPDATER_DISABLED' ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? true );
define( 'WP_AUTO_UPDATE_CORE', filter_var( getenv( 'WP_AUTO_UPDATE_CORE' ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? false );

define( 'WP_MEMORY_LIMIT', getenv( 'WP_MEMORY_LIMIT' ) ?: '128M' );
define( 'WP_MAX_MEMORY_LIMIT', getenv( 'WP_MAX_MEMORY_LIMIT' ) ?: '256M' );

define( 'FS_METHOD', getenv( 'EW_FS_METHOD' ) ?: 'ftpext' );

define( 'WP_CACHE', filter_var( getenv( 'WP_CACHE' ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? false );

define( 'FORCE_SSL_ADMIN', filter_var( getenv( 'EW_FORCE_SSL_ADMIN' ), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ) ?? true );
if ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && str_contains( $_SERVER['HTTP_X_FORWARDED_PROTO'], 'https' ) ) {
	$_SERVER['HTTPS'] = 'on';
}

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
