<?php
/**
 * File: v-wp-seo-audit.php
 *
 * @package V_WP_SEO_Audit
 */

/*
Plugin Name: V-WP-SEO-Audit
Description: WordPress SEO Audit plugin - Analyze your website's SEO performance
Version: 1.0.0
Author: djav1985
License: GPL2
Text Domain: v-wp-seo-audit
*/

if ( ! defined( 'ABSPATH' )) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'V_WP_SEO_AUDIT_VERSION', '1.0.0' );
define( 'V_WP_SEO_AUDIT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'V_WP_SEO_AUDIT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load installation hooks.
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'install.php';

// Load deactivation hooks.
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'deactivation.php';

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, 'v_wp_seo_audit_activate' );
register_deactivation_hook( __FILE__, 'v_wp_seo_audit_deactivate' );

// Register cleanup action.
add_action( 'v_wp_seo_audit_daily_cleanup', 'v_wp_seo_audit_cleanup' );

// Load organized includes files.
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-v-wp-seo-audit-db.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-yii-integration.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-validation.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-helpers.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-ajax-handlers.php';

// Initialize Yii framework.
// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting, WordPress.PHP.DevelopmentFunctions.error_log_error_reporting, WordPress.Security.PluginMenuSlug.Using error_reporting
error_reporting( E_ALL & ~( E_NOTICE | E_DEPRECATED | E_STRICT ) );

// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
if ( ! @ini_get( 'date.timezone' )) {
	// phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
	date_default_timezone_set( 'UTC' );
}

mb_internal_encoding( 'UTF-8' );

// Global variable to store Yii application instance.
global $v_wp_seo_audit_app;
$v_wp_seo_audit_app = null;

// Plugin initialization - only when needed (not on every page load).
// Use 'wp' instead of 'init' to have access to $post.
add_action( 'wp', array( 'V_WP_SEO_Audit_Yii_Integration', 'init' ) );

// Enqueue styles and scripts for front-end.
/**
 * V_wp_seo_audit_enqueue_assets function.
 */
function v_wp_seo_audit_enqueue_assets() {
	global $post, $v_wp_seo_audit_app;

	// Only load if shortcode is present on the page.
	if (is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'v_wp_seo_audit' )) {
		// Enqueue CSS files.
		wp_enqueue_style( 'v-wp-seo-audit-bootstrap', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/css/bootstrap.min.css', array(), V_WP_SEO_AUDIT_VERSION );
		wp_enqueue_style( 'v-wp-seo-audit-fontawesome', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/css/fontawesome.min.css', array(), V_WP_SEO_AUDIT_VERSION );
		wp_enqueue_style( 'v-wp-seo-audit-app', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/css/app.css', array( 'v-wp-seo-audit-bootstrap' ), V_WP_SEO_AUDIT_VERSION );

		// Enqueue JS files.
		wp_enqueue_script( 'jquery' ); // Use WordPress jQuery.
		wp_enqueue_script( 'v-wp-seo-audit-bootstrap', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/js/bootstrap.bundle.min.js', array( 'jquery' ), V_WP_SEO_AUDIT_VERSION, true );
		wp_enqueue_script( 'v-wp-seo-audit-flot', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/js/jquery.flot.js', array( 'jquery' ), V_WP_SEO_AUDIT_VERSION, true );
		wp_enqueue_script( 'v-wp-seo-audit-flot-pie', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/js/jquery.flot.pie.js', array( 'jquery', 'v-wp-seo-audit-flot' ), V_WP_SEO_AUDIT_VERSION, true );
		wp_enqueue_script( 'v-wp-seo-audit-base', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/js/base.js', array( 'jquery' ), V_WP_SEO_AUDIT_VERSION, true );

		// Add global JavaScript variables needed by the plugin.
		// Get the base URL from Yii app if initialized, otherwise use plugin URL.
		$base_url = rtrim( V_WP_SEO_AUDIT_PLUGIN_URL, '/' );

		// Inject global variables into the page.
		$global_vars = "var _global = { 
            baseUrl: '" . esc_js( $base_url ) . "',
            ajaxUrl: '" . esc_js( admin_url( 'admin-ajax.php' ) ) . "',
            nonce: '" . wp_create_nonce( 'v_wp_seo_audit_nonce' ) . "'
        };";
		wp_add_inline_script( 'v-wp-seo-audit-base', $global_vars, 'before' );
	}
}
add_action( 'wp_enqueue_scripts', 'v_wp_seo_audit_enqueue_assets' );

// Register shortcode.
/**
 * V_wp_seo_audit_shortcode function.
 *
 * @param mixed $atts Parameter.
 */
function v_wp_seo_audit_shortcode( $atts) {
	global $v_wp_seo_audit_app;

	if ( ! $v_wp_seo_audit_app) {
		return '<div class="v-wp-seo-audit-error"><p>Error: Application not initialized.</p></div>';
	}

	// Start output buffering to capture Yii output.
	ob_start();

	try {
		// Process the request through Yii.
		$v_wp_seo_audit_app->run();
		$content = ob_get_clean();

		// Create a fresh nonce for the container to support PDF downloads.
		$nonce = wp_create_nonce( 'v_wp_seo_audit_nonce' );

		// Wrap in container with data-nonce attribute.
		return '<div class="v-wp-seo-audit-container" data-nonce="' . esc_attr( $nonce ) . '">' . $content . '</div>';
	} catch (Exception $e) {
		ob_end_clean();
		return '<div class="v-wp-seo-audit-error"><p>Error: ' . esc_html( $e->getMessage() ) . '</p></div>';
	}
}
add_shortcode( 'v_wp_seo_audit', 'v_wp_seo_audit_shortcode' );

// Initialize AJAX handlers.
V_WP_SEO_Audit_Ajax_Handlers::init();

/**
 * WordPress-native function to get config file value.
 * Wrapper for backward compatibility. Uses V_WP_SEO_Audit_Helpers::load_config_file().
 *
 * @param string $config_name The config file name (without extension).
 * @return mixed The config value or empty array on failure.
 */
function v_wp_seo_audit_get_config( $config_name ) {
	return V_WP_SEO_Audit_Helpers::load_config_file( $config_name );
}

/**
 * WordPress-native website analysis function.
 * Wrapper for backward compatibility. Uses V_WP_SEO_Audit_DB::analyze_website().
 *
 * @param string   $domain The domain to analyze (ASCII/punycode).
 * @param string   $idn The internationalized domain name (Unicode).
 * @param string   $ip The IP address of the domain.
 * @param int|null $wid Optional. Existing website ID for updates.
 * @return int|WP_Error Website ID on success, WP_Error on failure.
 */
function v_wp_seo_audit_analyze_website( $domain, $idn, $ip, $wid = null ) {
	return V_WP_SEO_Audit_DB::analyze_website( $domain, $idn, $ip, $wid );
}

