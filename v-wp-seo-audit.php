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

if ( ! defined( 'ABSPATH' ) ) {
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
register_activation_hook( __FILE__, 'v_wpsa_activate' );
register_deactivation_hook( __FILE__, 'v_wpsa_deactivate' );

// Register cleanup action.
add_action( 'v_wp_seo_audit_daily_cleanup', 'v_wpsa_cleanup' );

// Load organized includes files.
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-v-wpsa-config.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-v-wpsa-db.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-v-wpsa-yii-integration.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-v-wpsa-validation.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-v-wpsa-helpers.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-v-wpsa-report-generator.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-v-wpsa-ajax-handlers.php';

// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
if ( ! @ini_get( 'date.timezone' ) ) {
	// phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
	date_default_timezone_set( 'UTC' );
}

mb_internal_encoding( 'UTF-8' );

// Global variable to store Yii application instance.
global $v_wp_seo_audit_app;
$v_wp_seo_audit_app = null;

// NOTE: Yii initialization is NO LONGER done on page load.
// Yii is only initialized when needed by AJAX handlers (generate_report, download_pdf, pagepeeker_proxy).
// This prevents Yii from running on common page requests, improving performance and avoiding conflicts.

// Enqueue styles and scripts for front-end.
/**
 * V_wpsa_enqueue_assets function.
 */
function v_wpsa_enqueue_assets() {
	global $post;

	// Only load if shortcode is present on the page.
	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'v_wp_seo_audit' ) ) {
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
		// Use plugin URL directly (no Yii dependency).
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
add_action( 'wp_enqueue_scripts', 'v_wpsa_enqueue_assets' );

// Register shortcode.
/**
 * V_wpsa_shortcode function.
 *
 * Renders the plugin form WITHOUT loading Yii framework on page load.
 * Yii is only loaded via AJAX handlers when needed for report generation.
 *
 * @param mixed $atts Parameter.
 */
function v_wpsa_shortcode( $atts ) {
	// Load WordPress-native request form template.
	// This does NOT require Yii initialization.
	ob_start();

	$template_path = V_WP_SEO_AUDIT_PLUGIN_DIR . 'templates/request-form.php';
	if ( file_exists( $template_path ) ) {
		include $template_path;
	} else {
		echo '<div class="v-wp-seo-audit-error"><p>Error: Template not found.</p></div>';
	}

	$content = ob_get_clean();

	// Create a fresh nonce for the container to support AJAX operations.
	$nonce = wp_create_nonce( 'v_wp_seo_audit_nonce' );

	// Wrap in container with data-nonce attribute.
	return '<div class="v-wp-seo-audit-container" data-nonce="' . esc_attr( $nonce ) . '">' . $content . '</div>';
}
add_shortcode( 'v_wp_seo_audit', 'v_wpsa_shortcode' );

// Initialize AJAX handlers.
V_WPSA_Ajax_Handlers::init();
