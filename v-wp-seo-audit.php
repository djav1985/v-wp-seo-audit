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

// Global variable to store Yii application instance
global $v_wp_seo_audit_app;
$v_wp_seo_audit_app = null;

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
        
        // Configure request component to use WordPress plugin URL
        if ($v_wp_seo_audit_app->hasComponent('request')) {
            $request = $v_wp_seo_audit_app->getRequest();
            // Set base URL to plugin URL (without trailing slash)
            $request->setBaseUrl(rtrim(V_WP_SEO_AUDIT_PLUGIN_URL, '/'));
        }
    }
}
add_action('init', 'v_wp_seo_audit_init');

// Enqueue styles and scripts for front-end
function v_wp_seo_audit_enqueue_assets() {
    global $post;
    
    // Only load if shortcode is present on the page
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'v_wp_seo_audit')) {
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
}
add_action('wp_enqueue_scripts', 'v_wp_seo_audit_enqueue_assets');

// Register shortcode
function v_wp_seo_audit_shortcode($atts) {
    global $v_wp_seo_audit_app;
    
    if (!$v_wp_seo_audit_app) {
        return '<div class="v-wp-seo-audit-error"><p>Error: Application not initialized.</p></div>';
    }
    
    // Start output buffering to capture Yii output
    ob_start();
    
    try {
        // Process the request through Yii
        $v_wp_seo_audit_app->run();
        $content = ob_get_clean();
        
        // Wrap in container
        return '<div class="v-wp-seo-audit-container">' . $content . '</div>';
    } catch (Exception $e) {
        ob_end_clean();
        return '<div class="v-wp-seo-audit-error"><p>Error: ' . esc_html($e->getMessage()) . '</p></div>';
    }
}
add_shortcode('v_wp_seo_audit', 'v_wp_seo_audit_shortcode');
