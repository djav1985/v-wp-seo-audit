<?php
/**
 * Yii Application Entry Point for WordPress Plugin
 * This file is DEPRECATED and should not be accessed directly.
 * All functionality is now handled through WordPress AJAX handlers.
 * This file is kept for backward compatibility only.
 *
 * @package V_WP_SEO_Audit
 */

// Prevent direct access - this file should only be loaded via WordPress.
if ( ! defined( 'ABSPATH' ) ) {
	// Try to load WordPress if not already loaded.
	$wp_load_path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';
	if ( file_exists( $wp_load_path ) ) {
		require_once $wp_load_path;
	} else {
		die( 'WordPress not found. This plugin requires WordPress to function properly.' );

	}
}

// If accessed directly without proper route, show error message.
// phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
if ( ! isset( $_GET['r'] ) && ! isset( $_POST['r'] ) ) {
	// phpcs:enable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
	?>
	<!DOCTYPE html>
	<html>
	<head>
		<title>V-WP-SEO-Audit - Direct Access Not Allowed</title>
		<style>
			body { font-family: Arial, sans-serif; margin: 50px; }
			.notice { background: #fff3cd; border: 1px solid #ffc107; padding: 20px; border-radius: 5px; }
			h1 { color: #856404; }
			p { color: #856404; }
		</style>
	</head>
	<body>
		<div class="notice">
			<h1>Direct Access Not Allowed</h1>
			<p>This file should not be accessed directly. The V-WP-SEO-Audit plugin now uses WordPress AJAX handlers.</p>
			<p>Please use the plugin shortcode <code>[v_wpsa]</code> on a WordPress page instead.</p>
			<p><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Return to Homepage</a></p>
		</div>
	</body>
	</html>
	<?php

	exit;
}

// Set error reporting similar to main plugin.
// phpcs:disable WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting, WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set, WordPress.PHP.NoSilencedErrors.Discouraged
error_reporting( E_ALL & ~( E_NOTICE | E_DEPRECATED | E_STRICT ) );
if ( ! ini_get( 'date.timezone' ) ) {
	date_default_timezone_set( 'UTC' );
}
// phpcs:enable WordPress.PHP.DevelopmentFunctions.prevent_path_disclosure_error_reporting, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting, WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set, WordPress.PHP.NoSilencedErrors.Discouraged

mb_internal_encoding( 'UTF-8' );
// Check if WordPress is loaded.
if ( ! defined( 'ABSPATH' ) ) {
	// If not, we need to load WordPress.
	$wp_load_path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php';
	if ( file_exists( $wp_load_path ) ) {
		require_once $wp_load_path;
	} else {
		die( 'WordPress not found' );
	}
}

// Plugin constants.
if ( ! defined( 'V_WP_SEO_AUDIT_PLUGIN_DIR' ) ) {
	define( 'V_WP_SEO_AUDIT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'V_WP_SEO_AUDIT_PLUGIN_URL' ) ) {
	define( 'V_WP_SEO_AUDIT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Initialize Yii framework if not already loaded.
$yii    = V_WP_SEO_AUDIT_PLUGIN_DIR . 'framework/yii.php';
$config = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/main.php';
if ( ! file_exists( $yii ) || ! file_exists( $config ) ) {
	die( 'Yii framework not found' );
}

// Check if Yii is already loaded (avoid double loading).
if ( ! class_exists( 'Yii', false ) ) {
	require_once $yii;
}

// Check if app is already created.
global $v_wpsa_app;
if ( null === $v_wpsa_app ) {
	// Create Yii application.
	$v_wpsa_app = Yii::createWebApplication( $config );

	// Set timezone from config.
	if ( isset( $v_wpsa_app->params['app.timezone'] ) ) {
		$v_wpsa_app->setTimeZone( $v_wpsa_app->params['app.timezone'] );

	}

	// Configure request component to use WordPress plugin URL.
	if ( $v_wpsa_app->hasComponent( 'request' ) ) {
		$request = $v_wpsa_app->getRequest();
		// Set base URL to plugin's relative path (from site root).
		$plugin_relative_url = str_replace( get_site_url(), '', rtrim( V_WP_SEO_AUDIT_PLUGIN_URL, '/' ) );
		$request->setBaseUrl( $plugin_relative_url );
		// In WordPress, we need to use the index.php as script URL.
		$request->setScriptUrl( $plugin_relative_url . '/index.php' );
	}

	// Configure URL manager for WordPress context.
	if ( $v_wpsa_app->hasComponent( 'urlManager' ) ) {
		$url_manager = $v_wpsa_app->getUrlManager();
		// Force GET format in WordPress since we can't use pretty URLs.
		// phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		$url_manager->urlFormat      = 'get';
		$url_manager->showScriptName = true;
		// phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

	}
}

// Run the application.
$v_wpsa_app->run();

