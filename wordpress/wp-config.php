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

// ** Database settings - Using MySQL for demo ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'demo_husfelag' );

/** Database username */
define( 'DB_USER', 'demo' );

/** Database password */
define( 'DB_PASSWORD', 'demo123' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 */
define('AUTH_KEY',         'Ag{9(F{D8oxnt-uRfN/m1L&N~mSy*b=@r)fY>a7StERXD]~cZyNdK;V(_[R9{r+r');
define('SECURE_AUTH_KEY',  '-#CT^x]6UHmf@$vc>7Gl;7M]e`L&;W+2k)0Vcz+fI}z/-gY>so3L%>N7m|-Al5*4');
define('LOGGED_IN_KEY',    'Ya-WOW)9@ReD?:|Uec|-,+_M,(5u*V|{XMVzf~R%oE +nX[<En[d4B/U^Aze9Fi!');
define('NONCE_KEY',        '8(?+[~&|G|g~GO^3Sbd^ecK}d)B|o<J1x:Z<TXWH9BlX*lR>EaPk;UTh:m`/={vS');
define('AUTH_SALT',        'I:^*<]=U)AEL-]g~LLzB_{6vJsG1!>J?7N-)gJDylo!!-OTTzN`@UjI6$ 8)giwp');
define('SECURE_AUTH_SALT', 'A5MS5IQxr)2VB8zz!H0:y_+6]>Ze(wvME=C}S[#TqD-CyLeKVGJlT^q@!A %2PAO');
define('LOGGED_IN_SALT',   '=uI@FWc(FL#RakOlM<OsQr>.}E=)=?zXT~1iP$?-sK-<u%}#9jPNhO5y m%BzgfR');
define('NONCE_SALT',       'D~NmFg,BZ3-X7,/GpUN.yUU+VaHkWR-7;@~ilLmVkOq{A/wNR0?_?Gl:lt F[k}^');

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
