<?php
error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));

if (!@ini_get('date.timezone'))
  date_default_timezone_set('UTC');

mb_internal_encoding('UTF-8');

// change the following paths if necessary
$yii = dirname(__FILE__) . '/framework/yii.php';
$config = dirname(__FILE__) . '/protected/config/main.php';

// remove the following lines when in production mode
// defined('YII_DEBUG') or define('YII_DEBUG', true);

require_once($yii);
$app = Yii::createWebApplication($config);
$app->setTimeZone($app->params['app.timezone']);
$app->run();
