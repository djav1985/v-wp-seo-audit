<?php
mb_internal_encoding("UTF-8");
error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));

$yiic=dirname(__FILE__).'/../framework/yiic.php';
$config=dirname(__FILE__).'/config/console.php';

require_once($yiic);
