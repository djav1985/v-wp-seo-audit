<?php
/**
 * Yii Application Entry Point for WordPress Plugin
 * This file handles AJAX and direct requests to the Yii application
 */

// Prevent direct access to this file for security
if (!isset($_GET['r']) && !isset($_POST['r'])) {
    die('Direct access not allowed');
}

// Set error reporting similar to main plugin
error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));

if (!@ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

mb_internal_encoding('UTF-8');

// Check if WordPress is loaded
if (!defined('ABSPATH')) {
    // If not, we need to load WordPress
    $wp_load_path = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        require_once($wp_load_path);
    } else {
        die('WordPress not found');
    }
}

// Plugin constants
if (!defined('V_WP_SEO_AUDIT_PLUGIN_DIR')) {
    define('V_WP_SEO_AUDIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('V_WP_SEO_AUDIT_PLUGIN_URL')) {
    define('V_WP_SEO_AUDIT_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Initialize Yii framework if not already loaded
$yii = V_WP_SEO_AUDIT_PLUGIN_DIR . 'framework/yii.php';
$config = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/main.php';

if (!file_exists($yii) || !file_exists($config)) {
    die('Yii framework not found');
}

// Check if Yii is already loaded (avoid double loading)
if (!class_exists('Yii', false)) {
    require_once($yii);
}

// Check if app is already created
global $v_wp_seo_audit_app;
if ($v_wp_seo_audit_app === null) {
    // Create Yii application
    $v_wp_seo_audit_app = Yii::createWebApplication($config);
    
    // Set timezone from config
    if (isset($v_wp_seo_audit_app->params['app.timezone'])) {
        $v_wp_seo_audit_app->setTimeZone($v_wp_seo_audit_app->params['app.timezone']);
    }
    
    // Configure request component to use WordPress plugin URL
    if ($v_wp_seo_audit_app->hasComponent('request')) {
        $request = $v_wp_seo_audit_app->getRequest();
        // Set base URL to plugin's relative path (from site root)
        $plugin_relative_url = str_replace(get_site_url(), '', rtrim(V_WP_SEO_AUDIT_PLUGIN_URL, '/'));
        $request->setBaseUrl($plugin_relative_url);
        // In WordPress, we need to use the index.php as script URL
        $request->setScriptUrl($plugin_relative_url . '/index.php');
    }
    
    // Configure URL manager for WordPress context
    if ($v_wp_seo_audit_app->hasComponent('urlManager')) {
        $urlManager = $v_wp_seo_audit_app->getUrlManager();
        // Force GET format in WordPress since we can't use pretty URLs
        $urlManager->urlFormat = 'get';
        $urlManager->showScriptName = true;
    }
}

// Run the application
$v_wp_seo_audit_app->run();
