<?php
/**
 * Plugin Name: V WP SEO Audit
 * Description: WordPress SEO Audit plugin - Analyze your website's SEO performance
 * Version: 1.0.0
 * Author: Vontainment
 * Author URI: https://vontainment.com
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: v-wpsa
 * Requires PHP: 8.0
 * WARNING: The v-wpsa standard uses 2 deprecated sniffs
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'V_WPSA_VERSION', '1.0.0' );
define( 'V_WPSA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'V_WPSA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load installation hooks.
require_once V_WPSA_PLUGIN_DIR . 'install.php';

// Load deactivation hooks.
require_once V_WPSA_PLUGIN_DIR . 'deactivation.php';

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, 'v_wpsa_activate' );
register_deactivation_hook( __FILE__, 'v_wpsa_deactivate' );

// Register cleanup action.
add_action( 'v_wpsa_daily_cleanup', 'v_wpsa_cleanup' );

// Load Composer autoloader for plugin classes.
require_once V_WPSA_PLUGIN_DIR . 'vendor/autoload.php';

// Load WordPress-native widget templates.
require_once V_WPSA_PLUGIN_DIR . 'templates/widgets.php';

mb_internal_encoding( 'UTF-8' );

// Enqueue styles and scripts for front-end.
/**
 * V_wpsa_enqueue_assets function.
 */
function v_wpsa_enqueue_assets() {
	global $post;

	// Only load if shortcode is present on the page.
	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'v_wpsa' ) ) {
		// Use file modification time for cache busting instead of static version.
		$base_js_file = V_WPSA_PLUGIN_DIR . 'assets/js/base.js';
		$version      = file_exists( $base_js_file ) ? filemtime( $base_js_file ) : V_WPSA_VERSION;

		// Enqueue CSS files.
		wp_enqueue_style( 'v-wpsa-bootstrap', V_WPSA_PLUGIN_URL . 'assets/css/bootstrap.min.css', array(), $version );
		wp_enqueue_style( 'v-wpsa-app', V_WPSA_PLUGIN_URL . 'assets/css/app.css', array( 'v-wpsa-bootstrap' ), $version );

		// Enqueue JS files.
		wp_enqueue_script( 'jquery' ); // Use WordPress jQuery.
		wp_enqueue_script( 'v-wpsa-bootstrap', V_WPSA_PLUGIN_URL . 'assets/js/bootstrap.bundle.min.js', array( 'jquery' ), $version, true );
		wp_enqueue_script( 'v-wpsa-flot', V_WPSA_PLUGIN_URL . 'assets/js/jquery.flot.js', array( 'jquery' ), $version, true );
		wp_enqueue_script( 'v-wpsa-flot-pie', V_WPSA_PLUGIN_URL . 'assets/js/jquery.flot.pie.js', array( 'jquery', 'v-wpsa-flot' ), $version, true );
		wp_enqueue_script( 'v-wpsa-base', V_WPSA_PLUGIN_URL . 'assets/js/base.js', array( 'jquery' ), $version, true );

		// Add global JavaScript variables needed by the plugin.
		$base_url = rtrim( V_WPSA_PLUGIN_URL, '/' );

		// Inject global variables into the page (using JSON encoding for safety).
		$global_data = array(
			'baseUrl' => $base_url,
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'v_wpsa_nonce' ),
		);
		$global_vars = 'var _global = ' . wp_json_encode( $global_data ) . ';';
		wp_add_inline_script( 'v-wpsa-base', $global_vars, 'before' );
	}
}
add_action( 'wp_enqueue_scripts', 'v_wpsa_enqueue_assets' );

// Register shortcode.
/**
 * V_wpsa_shortcode function.
 *
 * @param mixed $atts Parameter.
 */
function v_wpsa_shortcode( $atts ) {
	// Load WordPress-native request form template.
	ob_start();

	$template_path = V_WPSA_PLUGIN_DIR . 'templates/main.php';
	if ( file_exists( $template_path ) ) {
		include $template_path;
	} else {
		echo '<div class="v-wpsa-error"><p>Error: Template not found.</p></div>';
	}

	$content = ob_get_clean();

	// Create a fresh nonce for the container to support AJAX operations.
	$nonce = wp_create_nonce( 'v_wpsa_nonce' );

	// Wrap in container with data-nonce attribute.
	return '<div class="v-wpsa-container" data-nonce="' . esc_attr( $nonce ) . '">' . $content . '</div>';
}
add_shortcode( 'v_wpsa', 'v_wpsa_shortcode' );

// Initialize AJAX handlers.
V_WPSA_Ajax_Handlers::init();

/**
 * External generation function for AI integrations and function calling.
 *
 * This function provides a simple interface for generating SEO reports
 * that can be called from anywhere in WordPress, including AI chatbots
 * and external integrations.
 *
 * @param string $domain The domain to analyze.
 * @param bool   $report Whether to return full report data (true) or minimal data (false).
 * @return string|WP_Error JSON string with report data, or WP_Error on failure.
 *
 * phpcs:disable WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
 */
function V_WPSA_external_generation( $domain, $report = true ) {
	// Use the service layer to prepare the report.
	$result = V_WPSA_Report_Service::prepare_report( $domain, array( 'force' => false ) );

	// Handle errors.
	if ( is_wp_error( $result ) ) {
		return $result;
	}

	// If minimal data is requested (no full report).
	if ( ! $report ) {
		// Return only domain, score, PDF URL, and report URL as JSON.
		$minimal = array(
			'domain'     => $result['domain'],
			'score'      => $result['score'],
			'pdf_url'    => $result['pdf_url'],
			'report_url' => $result['report_url'],
		);
		return wp_json_encode( $minimal );
	}

	// Return full report as JSON string.
	return wp_json_encode( $result );
}
