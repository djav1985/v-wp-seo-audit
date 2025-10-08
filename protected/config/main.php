<?php
$cfg_main = __DIR__.DIRECTORY_SEPARATOR."config.php";
$cfg_local = __DIR__.DIRECTORY_SEPARATOR."config_local.php";
$params = is_file($cfg_local) ? require $cfg_local : require $cfg_main;

return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>$params['app.name'],
    'language'=>$params['app.default_language'],
    'timeZone'=>$params['app.timezone'],
    'preload'=>array('log'),


	// autoloading model and component classes
	'import'=>array(
		'application.models.*',
		'application.components.*',
	),

	// application components
	'components'=>array(
        'user'=>array(
            'identityCookie'=>array(
                'httpOnly' => true,
                'path' => $params['app.base_url'],
                'secure'=> $params['cookie.secure'],
                'sameSite'=> $params['cookie.same_site'],
            ),
        ),

		// Url Manager
        'urlManager'=>array(
            'urlFormat'=>'path',
            'showScriptName' => $params['url.show_script_name'],
            'class'=>'application.components.UrlManager',
            'cacheID'=>'cache',
		),

		// File Cache. ~/root/website_review/runtime/cache direcotry
		'cache'=>array(
			'class'=>'CFileCache',
		),

        'db'=>array(
            // Mysql host: localhost and databse name catalog
            'connectionString' => "mysql:host={$params['db.host']};dbname={$params['db.dbname']};port={$params['db.port']}",
            // whether to turn on prepare emulation
            'emulatePrepare' => true,
            // db username
            'username' => $params['db.username'],
            // db password
            'password' => $params['db.password'],
            // default cahrset
            'charset' => 'utf8mb4',
            // table prefix
            'tablePrefix' => 'ca_',
            // cache time to reduce SHOW CREATE TABLE * queries
            'schemaCachingDuration' => 60 * 60 * 24 * 30,
            'enableProfiling'=> YII_DEBUG,
            'enableParamLogging' => YII_DEBUG,
        ),

		// Error handler
		'errorHandler'=>array(
			// ControllerID/ActionID custom page to handle errors
			'errorAction'=>'site/error',
		),

		// Log errors into ~/root/website_review/runtime/application.log file
		'log'=>array(
			'class'=>'CLogRouter',
			'routes'=>array(
				array(
					'class'=>'CFileLogRoute',
					'levels'=>'error, warning',
                    'except'=>'exception.CHttpException.*',
				),
                /*array(
                    'class'=>'CWebLogRoute',
                ),*/
			),
		),

        'securityManager' => array(
            'encryptionKey'=>$params['app.encryption_key'],
            'validationkey'=>$params['app.validation_key'],
        ),

        'session'=>array(
            'cookieParams'=>array(
                'httponly' => true,
                'path' => $params['app.base_url'],
                'secure'=> $params['cookie.secure'],
                'samesite'=> $params['cookie.same_site'],
            ),
        ),

        'request'=>array(
            'enableCookieValidation'=>$params['app.cookie_validation'],
            'csrfCookie' => array(
                'httpOnly' => true,
                'path' => $params['app.base_url'],
                'secure'=> $params['cookie.secure'],
                'sameSite'=> $params['cookie.same_site'],
            ),
        ),
	),

	// App level params
	'params'=>$params
);
