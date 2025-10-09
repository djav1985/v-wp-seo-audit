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

// Plugin initialization - only when needed (not on every page load)
function v_wp_seo_audit_init() {
    global $v_wp_seo_audit_app, $post;
    
    // Only initialize if not already initialized and shortcode is present
    if ($v_wp_seo_audit_app !== null) {
        return;
    }
    
    // Check if we need to initialize (shortcode present or admin area)
    $should_init = false;
    if (is_admin()) {
        $should_init = false; // Don't init in admin to avoid conflicts
    } elseif (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'v_wp_seo_audit')) {
        $should_init = true;
    }
    
    if (!$should_init) {
        return;
    }
    
    // Initialize Yii framework
    $yii = V_WP_SEO_AUDIT_PLUGIN_DIR . 'framework/yii.php';
    $config = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/main.php';
    
    if (file_exists($yii) && file_exists($config)) {
        require_once($yii);
        
        // Create Yii application but don't run it yet
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
}
add_action('wp', 'v_wp_seo_audit_init'); // Use 'wp' instead of 'init' to have access to $post

// Enqueue styles and scripts for front-end
function v_wp_seo_audit_enqueue_assets() {
    global $post, $v_wp_seo_audit_app;
    
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
        
        // Add global JavaScript variables needed by the plugin
        // Get the base URL from Yii app if initialized, otherwise use plugin URL
        $base_url = rtrim(V_WP_SEO_AUDIT_PLUGIN_URL, '/');
        
        // Inject global variables into the page
        $global_vars = "var _global = { 
            baseUrl: '" . esc_js($base_url) . "',
            ajaxUrl: '" . esc_js(admin_url('admin-ajax.php')) . "',
            nonce: '" . wp_create_nonce('v_wp_seo_audit_nonce') . "'
        };";
        wp_add_inline_script('v-wp-seo-audit-base', $global_vars, 'before');
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

// Activation hook - create database tables
function v_wp_seo_audit_activate() {
    global $wpdb;
    
    // Get the table prefix from WordPress
    $table_prefix = $wpdb->prefix . 'ca_';
    
    // Set charset
    $charset_collate = $wpdb->get_charset_collate();
    
    // SQL statements to create tables
    $sql = array();
    
    // ca_cloud table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}cloud` (
        `wid` int unsigned NOT NULL,
        `words` mediumtext NOT NULL,
        `matrix` mediumtext NOT NULL,
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";
    
    // ca_content table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}content` (
        `wid` int unsigned NOT NULL,
        `headings` mediumtext NOT NULL,
        `total_img` int unsigned NOT NULL DEFAULT '0',
        `total_alt` int unsigned NOT NULL DEFAULT '0',
        `deprecated` mediumtext NOT NULL,
        `isset_headings` tinyint NOT NULL DEFAULT '0',
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";
    
    // ca_document table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}document` (
        `wid` int unsigned NOT NULL,
        `doctype` text,
        `lang` varchar(255) DEFAULT NULL,
        `charset` varchar(255) DEFAULT NULL,
        `css` int unsigned NOT NULL DEFAULT '0',
        `js` int unsigned NOT NULL DEFAULT '0',
        `htmlratio` int unsigned NOT NULL DEFAULT '0',
        `favicon` text,
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";
    
    // ca_issetobject table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}issetobject` (
        `wid` int unsigned NOT NULL,
        `flash` tinyint(1) DEFAULT '0',
        `iframe` tinyint(1) DEFAULT '0',
        `nestedtables` tinyint(1) DEFAULT '0',
        `inlinecss` tinyint(1) DEFAULT '0',
        `email` tinyint(1) DEFAULT '0',
        `viewport` tinyint(1) DEFAULT '0',
        `dublincore` tinyint(1) DEFAULT '0',
        `printable` tinyint(1) DEFAULT '0',
        `appleicons` tinyint(1) DEFAULT '0',
        `robotstxt` tinyint(1) DEFAULT '0',
        `gzip` tinyint(1) DEFAULT '0',
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";
    
    // ca_links table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}links` (
        `wid` int unsigned NOT NULL,
        `links` mediumtext NOT NULL,
        `internal` int unsigned NOT NULL DEFAULT '0',
        `external_dofollow` int unsigned NOT NULL DEFAULT '0',
        `external_nofollow` int unsigned NOT NULL DEFAULT '0',
        `isset_underscore` tinyint(1) NOT NULL,
        `files_count` int unsigned NOT NULL DEFAULT '0',
        `friendly` tinyint(1) NOT NULL,
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";
    
    // ca_metatags table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}metatags` (
        `wid` int unsigned NOT NULL,
        `title` mediumtext,
        `keyword` mediumtext,
        `description` mediumtext,
        `ogproperties` mediumtext,
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";
    
    // ca_misc table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}misc` (
        `wid` int unsigned NOT NULL,
        `sitemap` mediumtext NOT NULL,
        `analytics` mediumtext NOT NULL,
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";
    
    // ca_pagespeed table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}pagespeed` (
        `wid` int unsigned NOT NULL,
        `data` longtext NOT NULL,
        `lang_id` varchar(5) NOT NULL,
        PRIMARY KEY (`wid`,`lang_id`)
    ) ENGINE=InnoDB $charset_collate;";
    
    // ca_w3c table
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}w3c` (
        `wid` int unsigned NOT NULL,
        `validator` enum('html') NOT NULL,
        `valid` tinyint(1) NOT NULL DEFAULT '1',
        `errors` smallint unsigned NOT NULL DEFAULT '0',
        `warnings` smallint unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";
    
    // ca_website table (main table)
    $sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}website` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `domain` varchar(255) DEFAULT NULL,
        `idn` varchar(255) DEFAULT NULL,
        `final_url` mediumtext,
        `md5domain` varchar(32) DEFAULT NULL,
        `added` timestamp NULL DEFAULT NULL,
        `modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `score` tinyint unsigned DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `ix_md5domain` (`md5domain`),
        KEY `ix_rating` (`score`)
    ) ENGINE=InnoDB $charset_collate;";
    
    // Execute all SQL statements
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($sql as $query) {
        dbDelta($query);
    }
    
    // Set plugin version option
    add_option('v_wp_seo_audit_version', V_WP_SEO_AUDIT_VERSION);
}
register_activation_hook(__FILE__, 'v_wp_seo_audit_activate');

// Deactivation hook (optional - for cleanup on deactivation)
function v_wp_seo_audit_deactivate() {
    // Add any cleanup code here if needed on deactivation
    // Note: This does NOT delete tables - use uninstall for that
}
register_deactivation_hook(__FILE__, 'v_wp_seo_audit_deactivate');

// Uninstall hook - remove plugin database tables
function v_wp_seo_audit_uninstall() {
    global $wpdb;
    $table_prefix = $wpdb->prefix . 'ca_';
    $tables = array(
        'cloud', 'content', 'document', 'issetobject', 'links', 'metatags', 'misc', 'pagespeed', 'w3c', 'website'
    );
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS `{$table_prefix}{$table}`;");
    }
    delete_option('v_wp_seo_audit_version');
}
register_uninstall_hook(__FILE__, 'v_wp_seo_audit_uninstall');

// WordPress AJAX handler for domain validation
function v_wp_seo_audit_ajax_validate_domain() {
    global $v_wp_seo_audit_app;
    
    // Initialize Yii if not already initialized
    if ($v_wp_seo_audit_app === null) {
        $yii = V_WP_SEO_AUDIT_PLUGIN_DIR . 'framework/yii.php';
        $config = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/main.php';
        
        if (file_exists($yii) && file_exists($config)) {
            require_once($yii);
            $v_wp_seo_audit_app = Yii::createWebApplication($config);
            
            if (isset($v_wp_seo_audit_app->params['app.timezone'])) {
                $v_wp_seo_audit_app->setTimeZone($v_wp_seo_audit_app->params['app.timezone']);
            }
        } else {
            wp_send_json_error(array('message' => 'Application not initialized'));
            return;
        }
    }
    
    // Get domain from request
    $domain = isset($_POST['domain']) ? sanitize_text_field($_POST['domain']) : '';
    
    if (empty($domain)) {
        wp_send_json_error(array('message' => 'Please enter a domain name'));
        return;
    }
    
    // Create and validate the model
    $model = new WebsiteForm();
    $model->domain = $domain;
    
    if (!$model->validate()) {
        $errors = $model->getErrors();
        $errorMessages = array();
        foreach ($errors as $field => $fieldErrors) {
            foreach ($fieldErrors as $error) {
                $errorMessages[] = $error;
            }
        }
        wp_send_json_error(array('message' => implode('<br>', $errorMessages)));
    } else {
        // Domain is valid, return success with domain
        wp_send_json_success(array('domain' => $model->domain));
    }
}
add_action('wp_ajax_v_wp_seo_audit_validate', 'v_wp_seo_audit_ajax_validate_domain');
add_action('wp_ajax_nopriv_v_wp_seo_audit_validate', 'v_wp_seo_audit_ajax_validate_domain');

// WordPress AJAX handler for generating HTML report
function v_wp_seo_audit_ajax_generate_report() {
    global $v_wp_seo_audit_app;
    
    // Initialize Yii if not already initialized
    if ($v_wp_seo_audit_app === null) {
        $yii = V_WP_SEO_AUDIT_PLUGIN_DIR . 'framework/yii.php';
        $config = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/main.php';
        
        if (file_exists($yii) && file_exists($config)) {
            require_once($yii);
            $v_wp_seo_audit_app = Yii::createWebApplication($config);
            
            if (isset($v_wp_seo_audit_app->params['app.timezone'])) {
                $v_wp_seo_audit_app->setTimeZone($v_wp_seo_audit_app->params['app.timezone']);
            }
        } else {
            wp_send_json_error(array('message' => 'Application not initialized'));
            return;
        }
    }
    
    // Get domain from request
    $domain = isset($_POST['domain']) ? sanitize_text_field($_POST['domain']) : '';
    
    if (empty($domain)) {
        wp_send_json_error(array('message' => 'Domain is required'));
        return;
    }
    
    // Set the domain in GET for the controller
    $_GET['domain'] = $domain;
    
    // Start output buffering to capture the controller output
    ob_start();
    
    try {
        // Create the controller and render the view
        $controller = new WebsitestatController('websitestat');
        $controller->actionGenerateHTML($domain);
        
        $content = ob_get_clean();
        
        // Return the HTML content
        wp_send_json_success(array('html' => $content));
    } catch (Exception $e) {
        ob_end_clean();
        wp_send_json_error(array('message' => $e->getMessage()));
    }
}
add_action('wp_ajax_v_wp_seo_audit_generate_report', 'v_wp_seo_audit_ajax_generate_report');
add_action('wp_ajax_nopriv_v_wp_seo_audit_generate_report', 'v_wp_seo_audit_ajax_generate_report');
