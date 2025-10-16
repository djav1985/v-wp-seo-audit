<?php
/**
 * Example Plugin: SEO Report Dashboard Widget
 *
 * This example demonstrates how to integrate with the v-wpsa plugin
 * to display SEO reports in a WordPress admin dashboard widget.
 *
 * Installation:
 * 1. Save this file as wp-content/plugins/seo-dashboard-widget.php
 * 2. Activate the plugin in WordPress admin
 * 3. The widget will appear on the dashboard
 *
 * @package SEO_Dashboard_Widget
 */

/**
 * Plugin Name: SEO Report Dashboard Widget
 * Plugin URI: https://example.com
 * Description: Display SEO audit reports in the WordPress dashboard using v-wpsa plugin
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add SEO Report widget to dashboard.
 */
function seo_dashboard_widget_register() {
	wp_add_dashboard_widget(
		'seo_report_widget',
		'SEO Audit Reports',
		'seo_dashboard_widget_display'
	);
}
add_action( 'wp_dashboard_setup', 'seo_dashboard_widget_register' );

/**
 * Display the SEO report widget content.
 */
function seo_dashboard_widget_display() {
	// Check if v-wpsa plugin is active
	if ( ! function_exists( 'v_wpsa_get_report_data' ) ) {
		echo '<p>The v-wpsa plugin is required for this widget to work.</p>';
		return;
	}

	// Handle form submission
	if ( isset( $_POST['seo_domain'] ) && check_admin_referer( 'seo_widget_nonce' ) ) {
		$domain = sanitize_text_field( wp_unslash( $_POST['seo_domain'] ) );
		$force  = isset( $_POST['seo_force'] );

		// Get report using the v-wpsa helper function
		$report = v_wpsa_get_report_data( $domain, array( 'force' => $force ) );

		if ( is_wp_error( $report ) ) {
			echo '<div class="notice notice-error inline"><p><strong>Error:</strong> ' . esc_html( $report->get_error_message() ) . '</p></div>';
		} else {
			seo_dashboard_widget_display_report( $report );
			return;
		}
	}

	// Display form
	?>
	<form method="post" style="margin-bottom: 20px;">
		<?php wp_nonce_field( 'seo_widget_nonce' ); ?>
		<p>
			<label for="seo_domain">Domain to analyze:</label><br>
			<input type="text" id="seo_domain" name="seo_domain" value="" class="widefat" placeholder="example.com">
		</p>
		<p>
			<label>
				<input type="checkbox" id="seo_force" name="seo_force" value="1">
				Force fresh analysis (ignore cache)
			</label>
		</p>
		<p>
			<button type="submit" class="button button-primary">Generate Report</button>
		</p>
	</form>

	<hr>

	<h4>Recent Reports</h4>
	<?php
	// Display recent reports from transient cache
	$recent = get_transient( 'seo_widget_recent_reports' );
	if ( $recent && is_array( $recent ) ) {
		echo '<ul>';
		foreach ( $recent as $item ) {
			printf(
				'<li><a href="#" onclick="document.getElementById(\'seo_domain\').value=\'%s\'; return false;">%s</a> - Score: %d/100 <small>(%s)</small></li>',
				esc_attr( $item['domain'] ),
				esc_html( $item['domain'] ),
				absint( $item['score'] ),
				esc_html( $item['time'] )
			);
		}
		echo '</ul>';
	} else {
		echo '<p><em>No recent reports.</em></p>';
	}
}

/**
 * Display a formatted SEO report.
 *
 * @param array $report Report data from v_wpsa_get_report_data().
 */
function seo_dashboard_widget_display_report( $report ) {
	?>
	<div class="seo-report-result" style="background: #f9f9f9; padding: 15px; border-radius: 5px;">
		<h3><?php echo esc_html( $report['domain'] ); ?></h3>
		
		<div style="margin: 15px 0;">
			<div style="display: inline-block; text-align: center; margin-right: 20px;">
				<div style="font-size: 48px; font-weight: bold; color: <?php echo seo_widget_get_score_color( $report['score'] ); ?>">
					<?php echo absint( $report['score'] ); ?>
				</div>
				<div style="color: #666;">Overall Score</div>
			</div>
			
			<div style="display: inline-block; vertical-align: top;">
				<p>
					<strong>Status:</strong> 
					<?php echo $report['cached'] ? '<span style="color: #888;">Cached</span>' : '<span style="color: #46b450;">Fresh Analysis</span>'; ?>
				</p>
				<p>
					<strong>Generated:</strong> 
					<?php echo esc_html( $report['generated']['time'] ); ?>
				</p>
				<p>
					<a href="<?php echo esc_url( $report['pdf_url'] ); ?>" target="_blank" class="button button-secondary">
						Download PDF Report
					</a>
				</p>
			</div>
		</div>

		<hr>

		<h4>Key Metrics</h4>
		<table class="widefat" style="margin-top: 10px;">
			<tbody>
				<?php if ( isset( $report['report']['meta'] ) ) : ?>
					<tr>
						<td><strong>Meta Title</strong></td>
						<td>
							<?php
							$title = $report['report']['meta']['title'];
							echo $title ? esc_html( $title ) : '<em style="color: #dc3232;">Not set</em>';
							?>
						</td>
					</tr>
					<tr>
						<td><strong>Meta Description</strong></td>
						<td>
							<?php
							$desc = $report['report']['meta']['description'];
							echo $desc ? esc_html( substr( $desc, 0, 100 ) ) . '...' : '<em style="color: #dc3232;">Not set</em>';
							?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ( isset( $report['report']['links'] ) ) : ?>
					<tr>
						<td><strong>Internal Links</strong></td>
						<td><?php echo absint( $report['report']['links']['internal'] ); ?></td>
					</tr>
					<tr>
						<td><strong>External Links</strong></td>
						<td>
							<?php
							$ext = absint( $report['report']['links']['external_dofollow'] ) +
								   absint( $report['report']['links']['external_nofollow'] );
							echo $ext;
							?>
						</td>
					</tr>
				<?php endif; ?>

				<?php if ( isset( $report['report']['content']['word_count'] ) ) : ?>
					<tr>
						<td><strong>Word Count</strong></td>
						<td><?php echo absint( $report['report']['content']['word_count'] ); ?></td>
					</tr>
				<?php endif; ?>

				<?php if ( isset( $report['report']['document']['lang'] ) ) : ?>
					<tr>
						<td><strong>Language</strong></td>
						<td><?php echo esc_html( $report['report']['document']['lang'] ); ?></td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<p style="margin-top: 15px;">
			<button type="button" class="button" onclick="location.reload();">Close Report</button>
		</p>
	</div>

	<style>
		.seo-report-result table.widefat td:first-child {
			width: 40%;
			font-weight: 600;
		}
	</style>
	<?php

	// Save to recent reports cache
	seo_widget_save_recent_report( $report );
}

/**
 * Get color based on score.
 *
 * @param int $score Score value (0-100).
 * @return string Hex color code.
 */
function seo_widget_get_score_color( $score ) {
	if ( $score >= 80 ) {
		return '#46b450'; // Green
	} elseif ( $score >= 60 ) {
		return '#ffb900'; // Yellow
	} else {
		return '#dc3232'; // Red
	}
}

/**
 * Save report to recent cache.
 *
 * @param array $report Report data.
 */
function seo_widget_save_recent_report( $report ) {
	$recent = get_transient( 'seo_widget_recent_reports' );
	if ( ! is_array( $recent ) ) {
		$recent = array();
	}

	// Add new report to the beginning
	array_unshift(
		$recent,
		array(
			'domain' => $report['domain'],
			'score'  => $report['score'],
			'time'   => $report['generated']['time'],
		)
	);

	// Keep only last 5 reports
	$recent = array_slice( $recent, 0, 5 );

	// Save for 24 hours
	set_transient( 'seo_widget_recent_reports', $recent, DAY_IN_SECONDS );
}

/**
 * Example: Scheduled batch report generation.
 * This shows how to use the API for automated reporting.
 */
function seo_widget_schedule_batch_reports() {
	if ( ! wp_next_scheduled( 'seo_widget_generate_batch' ) ) {
		wp_schedule_event( time(), 'daily', 'seo_widget_generate_batch' );
	}
}
add_action( 'init', 'seo_widget_schedule_batch_reports' );

/**
 * Generate reports for multiple domains in batch.
 */
function seo_widget_batch_report_generation() {
	// Skip if v-wpsa is not available
	if ( ! function_exists( 'v_wpsa_get_report_data' ) ) {
		return;
	}

	// Get domains from site options
	$domains = get_option( 'seo_widget_monitored_domains', array() );

	foreach ( $domains as $domain ) {
		// Force fresh analysis for scheduled reports
		$report = v_wpsa_get_report_data( $domain, array( 'force' => true ) );

		if ( ! is_wp_error( $report ) ) {
			// Store report data
			update_option( 'seo_widget_report_' . md5( $domain ), $report, false );

			// Send notification if score drops below threshold
			if ( $report['score'] < 60 ) {
				seo_widget_send_alert_email( $domain, $report['score'] );
			}
		}
	}
}
add_action( 'seo_widget_generate_batch', 'seo_widget_batch_report_generation' );

/**
 * Send alert email when score is low.
 *
 * @param string $domain Domain name.
 * @param int    $score  Current score.
 */
function seo_widget_send_alert_email( $domain, $score ) {
	$admin_email = get_option( 'admin_email' );
	$subject     = sprintf( '[SEO Alert] Low score for %s', $domain );
	$message     = sprintf(
		"The SEO score for %s has dropped to %d/100.\n\nPlease review the report and take action.\n\nView report: %s",
		$domain,
		$score,
		admin_url( 'index.php' )
	);

	wp_mail( $admin_email, $subject, $message );
}
