<?php

// Load WordPress DB constants and $table_prefix
// Robustly locate wp-config.php for both web and CLI execution.
// Load DB constants and table prefix from wp-db-config.php for both CLI and web contexts.
$wp_db_config_path = dirname( __FILE__, 3 ) . DIRECTORY_SEPARATOR . 'wp-db-config.php';
if (  ! file_exists( $wp_db_config_path ) ) {
    die( 'Fatal error: wp-db-config.php not found at ' . $wp_db_config_path );
}
require_once $wp_db_config_path;
$cfg_main  = __DIR__ . DIRECTORY_SEPARATOR . 'config.php';
$cfg_local = __DIR__ . DIRECTORY_SEPARATOR . 'config_local.php';
$params    = is_file( $cfg_local ) ? require $cfg_local : require $cfg_main;

return array(
    'basePath'   => dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..',
    'name'       => $params['app.name'],
    'language'   => $params['app.default_language'],
    'timeZone'   => $params['app.timezone'],
    'preload'    => array( 'log' ),


    // autoloading model and component classes
    'import'     => array(
        'application.models.*',
        'application.components.*',
    ),

    // application components
    'components' => array(
        'user'            => array(
            'identityCookie' => array(
                'httpOnly' => true,
                'path'     => $params['app.base_url'],
                'secure'   => $params['cookie.secure'],
                'sameSite' => $params['cookie.same_site'],
            ),
        ),

        // Url Manager
        'urlManager'      => array(
            'urlFormat'      => 'path',
            'showScriptName' => $params['url.show_script_name'],
            'class'          => 'application.components.UrlManager',
            'cacheID'        => 'cache',
        ),

        // File Cache. ~/root/website_review/runtime/cache direcotry
        'cache'           => array(
            'class' => 'CFileCache',
        ),

      'db'                => array(
          // Use WordPress DB constants and table prefix
          'connectionString'      => 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . ( defined( 'DB_PORT' ) ? DB_PORT : '3306' ),
          'emulatePrepare'        => true,
          'username'              => DB_USER,
          'password'              => DB_PASSWORD,
          'charset'               => defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8mb4',
        'tablePrefix'             => 'wp_ca_',
          'schemaCachingDuration' => 60 * 60 * 24 * 30,
          'enableProfiling'       => defined( 'YII_DEBUG' ) ? YII_DEBUG : false,
          'enableParamLogging'    => defined( 'YII_DEBUG' ) ? YII_DEBUG : false,
      ),

		// Error handler
		'errorHandler'    => array(
			// ControllerID/ActionID custom page to handle errors
			'errorAction' => 'site/error',
		),

		// Log errors into ~/root/website_review/runtime/application.log file
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

		'session'         => array(
			'cookieParams' => array(
				'httponly' => true,
				'path'     => $params['app.base_url'],
				'secure'   => $params['cookie.secure'],
				'samesite' => $params['cookie.same_site'],
			),
		),

		'request'         => array(
			'enableCookieValidation' => $params['app.cookie_validation'],
			'csrfCookie'             => array(
				'httpOnly' => true,
				'path'     => $params['app.base_url'],
				'secure'   => $params['cookie.secure'],
				'sameSite' => $params['cookie.same_site'],
			),
		),
	),

	// App level params
	'params'     => $params,
);
