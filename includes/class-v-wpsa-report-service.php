<?php
/**
 * Report Service Class
 *
 * Service layer for domain analysis and report generation.
 * Provides a unified interface for AJAX handlers, REST API, and direct function calls.
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class V_WPSA_Report_Service
 */
class V_WPSA_Report_Service {

	/**
	 * Prepare a complete report for a domain.
	 *
	 * This method replicates the validation/analysis cache checks from
	 * V_WPSA_Ajax_Handlers::generate_report(), delegates to V_WPSA_DB::analyze_website()
	 * when needed, generates HTML and PDF reports, and assembles a JSON-safe payload.
	 *
	 * @param string $domain_raw Raw domain input from user.
	 * @param array  $args Optional arguments.
	 *                     - 'force' (bool): Force re-analysis even if cached data exists.
	 * @return array|WP_Error Array with report data on success, WP_Error on failure.
	 */
	public static function prepare_report( $domain_raw, $args = array() ) {
		$defaults = array(
			'force' => false,
		);
		$args     = wp_parse_args( $args, $defaults );

		// Validate input.
		if ( empty( $domain_raw ) ) {
			return new WP_Error( 'empty_domain', 'Domain is required' );
		}

		// Validate domain using WordPress-native validation.
		$validation = V_WPSA_Validation::validate_domain( $domain_raw );

		if ( ! $validation['valid'] ) {
			return new WP_Error( 'invalid_domain', implode( ' ', $validation['errors'] ) );
		}

		// Extract validated domain data.
		$domain = $validation['domain'];
		$idn    = $validation['idn'];
		$ip     = $validation['ip'];

		// Check if website needs analysis or if cached data can be used.
		$db      = new V_WPSA_DB();
		$website = $db->get_website_by_domain( $domain, array( 'modified', 'id' ) );

		// Get cache time - default to 24 hours.
		$cache_time = apply_filters( 'v_wpsa_cache_time', DAY_IN_SECONDS );

		$needs_analysis = false;
		$wid            = null;
		$was_cached     = false;

		if ( ! $website ) {
			// Website doesn't exist - needs analysis.
			$needs_analysis = true;
		} elseif ( $args['force'] ) {
			// Force update requested - delete everything and re-analyze from scratch.
			$needs_analysis = true;
			$wid            = null; // Set to null to force creation of new record.

			// Delete the complete website record from database.
			$db->delete_website( $website['id'] );

			// Delete old PDFs and thumbnails when force updating.
			V_WPSA_Helpers::delete_pdf( $domain );
			V_WPSA_Helpers::delete_pdf( $domain . '_pagespeed' );
		} elseif ( strtotime( $website['modified'] ) + $cache_time <= time() ) {
			// Website exists but data is stale - needs re-analysis.
			$needs_analysis = true;
			$wid            = $website['id'];

			// Delete old PDFs when re-analyzing.
			V_WPSA_Helpers::delete_pdf( $domain );
			V_WPSA_Helpers::delete_pdf( $domain . '_pagespeed' );
		} else {
			// Data is cached and fresh.
			$was_cached = true;
		}

		// Perform analysis if needed.
		if ( $needs_analysis ) {
			$result = V_WPSA_DB::analyze_website( $domain, $idn, $ip, $wid );

			if ( is_wp_error( $result ) ) {
				return $result;
			}

			// Verify analysis created the record.
			$website_check = $db->get_website_by_domain( $domain, array( 'id' ) );
			if ( ! $website_check ) {
				return new WP_Error( 'analysis_failed', 'Analysis failed: domain record not created. Please try again or check your domain input.' );
			}
		}

		try {
			// Get full report data.
			$report_data = $db->get_full_report_data( $domain );

			if ( ! $report_data ) {
				return new WP_Error( 'report_data_not_found', 'Website not found: ' . $domain );
			}

			// Generate PDF to ensure it exists.
			$pdf_result = V_WPSA_Report_Generator::generate_pdf_report( $domain );

			// Construct PDF URL.
			$upload_dir = wp_upload_dir();
			$pdf_url    = $upload_dir['baseurl'] . '/seo-audit/pdf/' . $domain . '.pdf';

			// Prepare simplified JSON-safe payload.
			// Remove rateprovider object and include key report sections.
			$payload = array(
				'domain'  => $domain,
				'score'   => isset( $report_data['website']['score'] ) ? (int) $report_data['website']['score'] : 0,
				'pdf_url' => $pdf_url,
				'report'  => array(
					'website'   => isset( $report_data['website'] ) ? self::sanitize_website_data( $report_data['website'] ) : array(),
					'content'   => isset( $report_data['content'] ) ? $report_data['content'] : array(),
					'document'  => isset( $report_data['document'] ) ? $report_data['document'] : array(),
					'links'     => isset( $report_data['links'] ) ? $report_data['links'] : array(),
					'meta'      => isset( $report_data['meta'] ) ? $report_data['meta'] : array(),
					'w3c'       => isset( $report_data['w3c'] ) ? $report_data['w3c'] : array(),
					'cloud'     => isset( $report_data['cloud'] ) ? $report_data['cloud'] : array(),
					'misc'      => isset( $report_data['misc'] ) ? $report_data['misc'] : array(),
					'thumbnail' => isset( $report_data['thumbnail'] ) ? $report_data['thumbnail'] : array(),
				),
			);

			return $payload;
		} catch ( Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Production error logging for troubleshooting.
			error_log( sprintf( 'v-wpsa: error in prepare_report for %s: %s in %s on line %d', $domain, $e->getMessage(), $e->getFile(), $e->getLine() ) );
			return new WP_Error( 'report_generation_failed', 'Internal error while generating report: ' . $e->getMessage() );
		}
	}

	/**
	 * Sanitize website data for JSON output.
	 * Removes non-serializable objects like RateProvider.
	 *
	 * @param array $website Website data array.
	 * @return array Sanitized website data.
	 */
	private static function sanitize_website_data( $website ) {
		// Create a copy to avoid modifying original data.
		$sanitized = $website;

		// Remove non-serializable keys.
		$keys_to_remove = array( 'rateprovider', 'rateProvider' );
		foreach ( $keys_to_remove as $key ) {
			if ( isset( $sanitized[ $key ] ) ) {
				unset( $sanitized[ $key ] );
			}
		}

		return $sanitized;
	}
}
