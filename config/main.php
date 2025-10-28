<?php
/**
 * File: main.php
 *
 * Description: Configuration loader and database setup.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Load configuration.
$cfg_main  = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$cfg_local = __DIR__ . DIRECTORY_SEPARATOR . 'config_local.php';
$params    = is_file( $cfg_local ) ? require $cfg_local : require $cfg_main;

// Ensure WordPress database constants are defined.
// This plugin only works as a WordPress plugin.

if ( ! defined( 'DB_NAME' ) || ! defined( 'DB_USER' ) || ! defined( 'DB_PASSWORD' ) || ! defined( 'DB_HOST' ) ) {
	wp_die( 'WordPress database constants are not defined. This plugin requires WordPress to be installed and configured.' );
}

if ( ! defined( 'DB_CHARSET' ) ) {
	define( 'DB_CHARSET', 'utf8mb4' );
}

// Ensure DB_PORT is defined (default to 3306 if not)
if ( ! defined( 'DB_PORT' ) ) {
	define( 'DB_PORT', '3306' );
}

// Get WordPress database table prefix.
global $wpdb;
$db_table_prefix = isset( $wpdb ) && isset( $wpdb->prefix ) ? $wpdb->prefix : 'wp_';

return array(
	'basePath'   => dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..',
	'name'       => $params['app.name'],
	'language'   => get_locale(),
	'timeZone'   => wp_timezone_string(),
	'preload'    => array(),


	// autoloading model and component classes.
	'import'     => array(
		'application.models.*',
		'application.components.*',
	),

	// application components.
	'components' => array(
		// Url Manager.
		'urlManager'      => array(
			'urlFormat'      => 'path',
			'showScriptName' => false,
			'class'          => 'application.components.UrlManager',
		),

		'db'              => array(
			// Use WordPress DB constants and table prefix.
			'connectionString'      => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . ( defined( 'DB_PORT' ) ? DB_PORT : '3306' ),
			'emulatePrepare'        => true,
			'username'              => DB_USER,
			'password'              => DB_PASSWORD,
			'charset'               => defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8mb4',
			'tablePrefix'           => $db_table_prefix . 'ca_',
			'schemaCachingDuration' => 60 * 60 * 24 * 30,
		),

		// Error handler - removed custom error view, WordPress will handle 404s.

		'securityManager' => array(
			'encryptionKey' => wp_salt( 'auth' ),
			'validationkey' => wp_salt( 'secure_auth' ),
		),
	),

	// App level params.
	'params'     => $params,
);
