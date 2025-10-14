<?php
/**
 * File: main.php
 *
 * @package V_WP_SEO_Audit
 */

// Load configuration.
$cfg_main  = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$cfg_local = __DIR__ . DIRECTORY_SEPARATOR . 'config_local.php';
$params    = is_file( $cfg_local ) ? require $cfg_local : require $cfg_main;

// Use WordPress DB constants if available (when running as WordPress plugin)
// Otherwise fall back to config file settings (for CLI or standalone usage)
if ( ! defined( 'DB_NAME' )) {
	define( 'DB_NAME', $params['db.dbname'] );
}
if ( ! defined( 'DB_USER' )) {
	define( 'DB_USER', $params['db.username'] );
}
if ( ! defined( 'DB_PASSWORD' )) {
	define( 'DB_PASSWORD', $params['db.password'] );
}
if ( ! defined( 'DB_HOST' )) {
	define( 'DB_HOST', $params['db.host'] );
}
if ( ! defined( 'DB_CHARSET' )) {
	define( 'DB_CHARSET', 'utf8mb4' );
}
if ( ! isset( $table_prefix )) {
	global $wpdb;
	if (isset( $wpdb ) && isset( $wpdb->prefix )) {
		$table_prefix = $wpdb->prefix;
	} else {
		$table_prefix = 'wp_';
	}
}

return array(
	'basePath'   => dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..',
	'name'       => $params['app.name'],
	'language'   => $params['app.default_language'],
	'timeZone'   => $params['app.timezone'],
	'preload'    => array( 'log' ),


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
			'showScriptName' => $params['url.show_script_name'],
			'class'          => 'application.components.UrlManager',
			'cacheID'        => 'cache',
		),

		// File Cache. ~/root/website_review/runtime/cache direcotry.
		'cache'           => array(
			'class' => 'CFileCache',
		),

		'db'              => array(
			// Use WordPress DB constants and table prefix.
			'connectionString'      => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . ( defined( 'DB_PORT' ) ? DB_PORT : '3306' ),
			'emulatePrepare'        => true,
			'username'              => DB_USER,
			'password'              => DB_PASSWORD,
			'charset'               => defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8mb4',
			'tablePrefix'           => 'wp_ca_',
			'schemaCachingDuration' => 60 * 60 * 24 * 30,
			'enableProfiling'       => defined( 'YII_DEBUG' ) ? YII_DEBUG : false,
			'enableParamLogging'    => defined( 'YII_DEBUG' ) ? YII_DEBUG : false,
		),

		// Error handler - removed custom error view, WordPress will handle 404s

		// Log errors into ~/root/website_review/runtime/application.log file.
		'log'             => array(
			'class'  => 'CLogRouter',
			'routes' => array(
				array(
					'class'  => 'CFileLogRoute',
					'levels' => 'error, warning',
					'except' => 'exception.CHttpException.*',
				),
				/*
				array(
					'class'=>'CWebLogRoute',
				),*/
			),
		),

		'securityManager' => array(
			'encryptionKey' => $params['app.encryption_key'],
			'validationkey' => $params['app.validation_key'],
		),
	),

	// App level params.
	'params'     => $params,
);
