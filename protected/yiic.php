<?php
/**
 * File: yiic.php
 *
 * @package V_WP_SEO_Audit
 */

mb_internal_encoding( 'UTF-8' );
// phpcs:disable WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting
error_reporting( E_ALL & ~( E_NOTICE | E_DEPRECATED | E_STRICT ) );
// phpcs:enable WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting

$yiic   = dirname( __FILE__ ) . '/../framework/yiic.php';
$config = dirname( __FILE__ ) . '/config/console.php';

require_once $yiic;
