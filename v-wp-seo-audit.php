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
		// Each file gets its own version to ensure proper cache invalidation.
		$css_bootstrap_file = V_WPSA_PLUGIN_DIR . 'assets/css/bootstrap.min.css';
		$css_app_file       = V_WPSA_PLUGIN_DIR . 'assets/css/app.css';
		$js_bootstrap_file  = V_WPSA_PLUGIN_DIR . 'assets/js/bootstrap.bundle.min.js';
		$js_flot_file       = V_WPSA_PLUGIN_DIR . 'assets/js/jquery.flot.js';
		$js_flot_pie_file   = V_WPSA_PLUGIN_DIR . 'assets/js/jquery.flot.pie.js';
		$js_base_file       = V_WPSA_PLUGIN_DIR . 'assets/js/base.js';

		// Enqueue CSS files with individual file modification times.
		wp_enqueue_style(
			'v-wpsa-bootstrap',
			V_WPSA_PLUGIN_URL . 'assets/css/bootstrap.min.css',
			array(),
			file_exists( $css_bootstrap_file ) ? filemtime( $css_bootstrap_file ) : V_WPSA_VERSION
		);
		wp_enqueue_style(
			'v-wpsa-app',
			V_WPSA_PLUGIN_URL . 'assets/css/app.css',
			array( 'v-wpsa-bootstrap' ),
			file_exists( $css_app_file ) ? filemtime( $css_app_file ) : V_WPSA_VERSION
		);

		// Enqueue JS files with individual file modification times.
		wp_enqueue_script( 'jquery' ); // Use WordPress jQuery.
		wp_enqueue_script(
			'v-wpsa-bootstrap',
			V_WPSA_PLUGIN_URL . 'assets/js/bootstrap.bundle.min.js',
			array( 'jquery' ),
			file_exists( $js_bootstrap_file ) ? filemtime( $js_bootstrap_file ) : V_WPSA_VERSION,
			true
		);
		wp_enqueue_script(
			'v-wpsa-flot',
			V_WPSA_PLUGIN_URL . 'assets/js/jquery.flot.js',
			array( 'jquery' ),
			file_exists( $js_flot_file ) ? filemtime( $js_flot_file ) : V_WPSA_VERSION,
			true
		);
		wp_enqueue_script(
			'v-wpsa-flot-pie',
			V_WPSA_PLUGIN_URL . 'assets/js/jquery.flot.pie.js',
			array( 'jquery', 'v-wpsa-flot' ),
			file_exists( $js_flot_pie_file ) ? filemtime( $js_flot_pie_file ) : V_WPSA_VERSION,
			true
		);
		wp_enqueue_script(
			'v-wpsa-base',
			V_WPSA_PLUGIN_URL . 'assets/js/base.js',
			array( 'jquery' ),
			file_exists( $js_base_file ) ? filemtime( $js_base_file ) : V_WPSA_VERSION,
			true
		);

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
	// Ensure jQuery and our base script are enqueued for AJAX loading.
	// This must be done before the shortcode renders.
	wp_enqueue_script( 'jquery' );

	// Enqueue base.js if not already enqueued (contains helper functions).
	if ( ! wp_script_is( 'v-wpsa-base', 'enqueued' ) ) {
		$js_base_file = V_WPSA_PLUGIN_DIR . 'assets/js/base.js';
		wp_enqueue_script(
			'v-wpsa-base',
			V_WPSA_PLUGIN_URL . 'assets/js/base.js',
			array( 'jquery' ),
			file_exists( $js_base_file ) ? filemtime( $js_base_file ) : V_WPSA_VERSION,
			true
		);

		// Add global JavaScript variables if not already added.
		$base_url    = rtrim( V_WPSA_PLUGIN_URL, '/' );
		$global_data = array(
			'baseUrl' => $base_url,
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'v_wpsa_nonce' ),
		);
		$global_vars = 'var _global = ' . wp_json_encode( $global_data ) . ';';
		wp_add_inline_script( 'v-wpsa-base', $global_vars, 'before' );
	}

	// Create a fresh nonce for the container to support AJAX operations.
	$nonce = wp_create_nonce( 'v_wpsa_nonce' );

	// Generate unique ID for this shortcode instance to support multiple shortcodes per page.
	// Use uniqid() with more_entropy for better uniqueness and less predictability.
	$unique_id = 'v-wpsa-' . uniqid( '', true );

	// Get plugin configuration.
	$plugin_name = apply_filters( 'v_wpsa_plugin_name', 'SEO Audit by Vontainment' );
	$placeholder = apply_filters( 'v_wpsa_placeholder', 'example.com' );
	$base_url    = V_WPSA_PLUGIN_URL;

	// Render static content directly (no outer AJAX wrapper needed).
	// Only the widget part will load via AJAX to break caching.
	ob_start();
	?>
	<div id="<?php echo esc_attr( $unique_id ); ?>" class="v-wpsa-container" data-nonce="<?php echo esc_attr( $nonce ); ?>">
		<div class="jumbotron">
			<h1><?php echo esc_html( $plugin_name ); ?></h1>
			<p class="lead mb-4">
				<?php echo esc_html( $plugin_name ); ?> <?php esc_html_e( 'is a free SEO tool which provides you content analysis of the website.', 'v-wpsa' ); ?>
			</p>
			<form id="website-form">
				<div class="form-row">
					<div class="form-group col-md-6">
						<div class="input-group mb-3">
							<input type="text" name="Website[domain]" id="domain" class="form-control form-control-lg" placeholder="<?php echo esc_attr( $placeholder ); ?>">
							<div class="input-group-append">
								<button class="btn btn-primary" type="button" id="submit">
									<?php esc_html_e( 'Analyze', 'v-wpsa' ); ?>
								</button>
							</div>
						</div>

						<div class="alert alert-danger mb-0" id="errors" style="display: none"></div>

						<div class="clearfix"></div>

						<div id="progress-bar" class="progress" style="display: none">
							<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
						</div>
					</div>
				</div>
			</form>
		</div>

		<div class="row">
			<div class="col-md-6 mb-3">
				<h5 class="mb-3"><?php esc_html_e( 'Content analysis', 'v-wpsa' ); ?></h5>
				<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/content.png' ); ?>" alt="<?php esc_attr_e( 'Content analysis', 'v-wpsa' ); ?>" />
				<p>
					<?php esc_html_e( 'Get detailed analysis of your website content including headings, images, and text structure.', 'v-wpsa' ); ?>
				</p>
			</div>

			<div class="col-md-6 mb-3">
				<h5 class="mb-3"><?php esc_html_e( 'Meta Tags', 'v-wpsa' ); ?></h5>
				<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/tags.png' ); ?>" alt="<?php esc_attr_e( 'Meta Tags', 'v-wpsa' ); ?>" />
				<p>
					<?php esc_html_e( 'Analyze your meta tags including title, description, and Open Graph properties.', 'v-wpsa' ); ?>
				</p>
			</div>

			<div class="col-md-6 mb-3">
				<h5 class="mb-3"><?php esc_html_e( 'Link Extractor', 'v-wpsa' ); ?></h5>
				<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/link.png' ); ?>" alt="<?php esc_attr_e( 'Link Extractor', 'v-wpsa' ); ?>" />
				<p>
					<?php esc_html_e( 'Extract and analyze all internal and external links on your website.', 'v-wpsa' ); ?>
				</p>
			</div>

			<div class="col-md-6 mb-3">
				<h5 class="mb-3"><?php esc_html_e( 'Speed Test', 'v-wpsa' ); ?></h5>
				<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/speed.png' ); ?>" alt="<?php esc_attr_e( 'Speed Test', 'v-wpsa' ); ?>" />
				<p>
					<?php esc_html_e( 'Check your website speed and get recommendations for improvement.', 'v-wpsa' ); ?>
				</p>
			</div>

			<div class="col-md-6 mb-3">
				<h5 class="mb-3"><?php esc_html_e( 'Get Advice', 'v-wpsa' ); ?></h5>
				<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/advice.png' ); ?>" alt="<?php esc_attr_e( 'Get Advice', 'v-wpsa' ); ?>" />
				<p>
					<?php esc_html_e( 'Receive actionable recommendations to improve your SEO performance.', 'v-wpsa' ); ?>
				</p>
			</div>

			<div class="col-md-6 mb-3">
				<h5 class="mb-3"><?php esc_html_e( 'Website Review', 'v-wpsa' ); ?></h5>
				<img class="marketing-img float-left" src="<?php echo esc_url( $base_url . 'assets/img/review.png' ); ?>" alt="<?php esc_attr_e( 'Website Review', 'v-wpsa' ); ?>" />
				<p>
					<?php esc_html_e( 'Get a comprehensive review of your website SEO health and performance.', 'v-wpsa' ); ?>
				</p>
			</div>
		</div>

		<div id="v-wpsa-latest-reviews-container"></div>
		<script type="text/javascript">
			// Load latest reviews widget dynamically to prevent caching issues
			(function() {
				'use strict';
				// jQuery is enqueued by WordPress, so it should be available
				jQuery(document).ready(function($) {
					$.ajax({
						url: <?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>,
						type: 'POST',
						data: {
							action: 'v_wpsa_load_latest_reviews',
							nonce: <?php echo wp_json_encode( $nonce ); ?>,
							_cache_bust: new Date().getTime()
						},
						dataType: 'json',
						success: function(response) {
							if (response && response.success && response.data && response.data.html) {
								$('#v-wpsa-latest-reviews-container').html(response.data.html);
							}
						},
						error: function() {
							// Silent fail - widget is optional, no need to alert users
							console.log('v-wpsa: Failed to load latest reviews widget');
						}
					});
				});
			})();
		</script>
	</div>
	<?php
	return ob_get_clean();
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
