<?php
/*
Plugin Name: V-WP-SEO-Audit
Description: WordPress SEO Audit plugin - Analyze your website's SEO performance
Version: 1.0.0
Author: djav1985
License: GPL2
Text Domain: v-wp-seo-audit
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('V_WP_SEO_AUDIT_VERSION', '1.0.0');
define('V_WP_SEO_AUDIT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('V_WP_SEO_AUDIT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Initialize Yii framework
error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));

if (!@ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

mb_internal_encoding('UTF-8');

// Plugin initialization
function v_wp_seo_audit_init() {
    // Initialize Yii framework
    $yii = V_WP_SEO_AUDIT_PLUGIN_DIR . 'framework/yii.php';
    $config = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/main.php';
    
    if (file_exists($yii) && file_exists($config)) {
        require_once($yii);
        
        // Create Yii application but don't run it yet
        global $v_wp_seo_audit_app;
        $v_wp_seo_audit_app = Yii::createWebApplication($config);
        
        // Set timezone from config
        if (isset($v_wp_seo_audit_app->params['app.timezone'])) {
            $v_wp_seo_audit_app->setTimeZone($v_wp_seo_audit_app->params['app.timezone']);
        }
    }
}
add_action('init', 'v_wp_seo_audit_init');

// Enqueue styles and scripts
function v_wp_seo_audit_enqueue_assets() {
    // Only load on plugin pages
    if (!isset($_GET['page']) || strpos($_GET['page'], 'v-wp-seo-audit') !== 0) {
        return;
    }
    
    // Enqueue CSS files
    wp_enqueue_style('v-wp-seo-audit-bootstrap', V_WP_SEO_AUDIT_PLUGIN_URL . 'css/bootstrap.min.css', array(), V_WP_SEO_AUDIT_VERSION);
    wp_enqueue_style('v-wp-seo-audit-fontawesome', V_WP_SEO_AUDIT_PLUGIN_URL . 'css/fontawesome.min.css', array(), V_WP_SEO_AUDIT_VERSION);
    wp_enqueue_style('v-wp-seo-audit-app', V_WP_SEO_AUDIT_PLUGIN_URL . 'css/app.css', array('v-wp-seo-audit-bootstrap'), V_WP_SEO_AUDIT_VERSION);
    
    // Enqueue JS files
    wp_enqueue_script('jquery'); // Use WordPress jQuery
    wp_enqueue_script('v-wp-seo-audit-bootstrap', V_WP_SEO_AUDIT_PLUGIN_URL . 'js/bootstrap.bundle.min.js', array('jquery'), V_WP_SEO_AUDIT_VERSION, true);
    wp_enqueue_script('v-wp-seo-audit-flot', V_WP_SEO_AUDIT_PLUGIN_URL . 'js/jquery.flot.js', array('jquery'), V_WP_SEO_AUDIT_VERSION, true);
    wp_enqueue_script('v-wp-seo-audit-flot-pie', V_WP_SEO_AUDIT_PLUGIN_URL . 'js/jquery.flot.pie.js', array('jquery', 'v-wp-seo-audit-flot'), V_WP_SEO_AUDIT_VERSION, true);
    wp_enqueue_script('v-wp-seo-audit-base', V_WP_SEO_AUDIT_PLUGIN_URL . 'js/base.js', array('jquery'), V_WP_SEO_AUDIT_VERSION, true);
}
add_action('admin_enqueue_scripts', 'v_wp_seo_audit_enqueue_assets');

// Add admin menu
function v_wp_seo_audit_admin_menu() {
    add_menu_page(
        'V-WP-SEO-Audit',
        'SEO Audit',
        'manage_options',
        'v-wp-seo-audit',
        'v_wp_seo_audit_admin_page',
        'dashicons-search',
        80
    );
}
add_action('admin_menu', 'v_wp_seo_audit_admin_menu');

// Render admin page
function v_wp_seo_audit_admin_page() {
    global $v_wp_seo_audit_app;
    
    if (!$v_wp_seo_audit_app) {
        echo '<div class="wrap"><h1>V-WP-SEO-Audit</h1><p>Error: Application not initialized.</p></div>';
        return;
    }
    
    // Run the Yii application to handle the request
    echo '<div class="wrap v-wp-seo-audit-container">';
    
    try {
        // Process the request through Yii
        $v_wp_seo_audit_app->run();
    } catch (Exception $e) {
        echo '<h1>V-WP-SEO-Audit</h1>';
        echo '<div class="error"><p>Error: ' . esc_html($e->getMessage()) . '</p></div>';
    }
    
    echo '</div>';
}
