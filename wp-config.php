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
define( 'DB_NAME', 'roozbeh1' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         'UFVW|WjHr>{B%eqx)2[MHpIaP9Lu/Hh/lq>KUNOf2vj@N,a39dT*/(-Z6KEuWw8;' );
define( 'SECURE_AUTH_KEY',  'i|7p!=pRqe0oRK?*w7_Qe]~[)i*t*zr^Q !LY]BO,^BkZeX~U{Z/Tp$H[+*jrQ2o' );
define( 'LOGGED_IN_KEY',    '2;&>k0&`$~fZQ;m*>A2$t_4f)?(VXi9U]bygGm7G-JqzBtDa3=,jJ}CTN45`LY(i' );
define( 'NONCE_KEY',        '-Sn4bHN.2Y*iIm[W,-(K.36m>| {94P=2`q+(YT<n0Fh7?Ir(:&bPgqeb_a0Q:Zh' );
define( 'AUTH_SALT',        'o^.R)j]9=i#i+A7E:`*z+Ueg3[{6a]PBwg-B(+wb~[JA>lp9pR{|!UrtmncPaKl*' );
define( 'SECURE_AUTH_SALT', '0~OS<u4H/Q9PqR]L4Yb.Gva0kc_2H%f[|:MNh*08BSejJe3Hu8lgdd(t(s[=J}#(' );
define( 'LOGGED_IN_SALT',   ';Z~34ZJ*;{SgPdm5Z;5-L=pvd?0ZBf9Zzpg[J!O~6@#y43qR|VFH?TRZsi_p-I6I' );
define( 'NONCE_SALT',       'U^U^h@}Wkq8bRQUgl5|F%e7x2puf~1_e@C|p~QYC@qvIwFPQv*j}{t6X5JVHE:Z$' );

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
