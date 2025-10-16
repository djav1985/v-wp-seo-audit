<?php
/**
 * File: class-v-wpsa-ajax-handlers.php
 *
 * Description: AJAX request handlers for domain validation and report generation.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class V_WPSA_Ajax_Handlers
 */
class V_WPSA_Ajax_Handlers {

	/**
	 * Register all AJAX handlers.
	 */
	public static function init() {
		// Domain validation handler.
		add_action( 'wp_ajax_v_wpsa_validate', array( __CLASS__, 'validate_domain' ) );
		add_action( 'wp_ajax_nopriv_v_wpsa_validate', array( __CLASS__, 'validate_domain' ) );

		// Report generation handler.
		add_action( 'wp_ajax_v_wpsa_generate_report', array( __CLASS__, 'generate_report' ) );
		add_action( 'wp_ajax_nopriv_v_wpsa_generate_report', array( __CLASS__, 'generate_report' ) );

		// PDF download handler.
		add_action( 'wp_ajax_v_wpsa_download_pdf', array( __CLASS__, 'download_pdf' ) );
		add_action( 'wp_ajax_nopriv_v_wpsa_download_pdf', array( __CLASS__, 'download_pdf' ) );

		// Delete report handler (admin only).
		add_action( 'wp_ajax_v_wpsa_delete_report', array( __CLASS__, 'delete_report' ) );
	}

	/**
	 * AJAX handler for domain validation.
	 */
	public static function validate_domain() {
		// Verify nonce for security.
		check_ajax_referer( 'v_wpsa_nonce', 'nonce' );

		// Get domain from request.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitization handled in V_WPSA_Validation.
		$domain = isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '';

		// Use WordPress-native validation.
		$validation = V_WPSA_Validation::validate_domain( $domain );

		if ( ! $validation['valid'] ) {
			wp_send_json_error( array( 'message' => implode( '<br>', $validation['errors'] ) ) );
		} else {
			// Domain is valid, return success with domain.
			wp_send_json_success( array( 'domain' => $validation['domain'] ) );
		}
	}

	/**
	 * AJAX handler for generating HTML report.
	 * WordPress-native implementation.
	 */
	public static function generate_report() {
		// Verify nonce for security.
		check_ajax_referer( 'v_wpsa_nonce', 'nonce' );

		// Get domain from request.
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitization handled in V_WPSA_Validation.
		$domain_raw = isset( $_POST['domain'] ) ? wp_unslash( $_POST['domain'] ) : '';

		if ( empty( $domain_raw ) ) {
			wp_send_json_error( array( 'message' => 'Domain is required' ) );
			return;
		}

		// Check if this is a forced update (from update button).
		$force_update = isset( $_POST['force'] ) && '1' === $_POST['force'];

		// Validate domain using WordPress-native validation.
		$validation = V_WPSA_Validation::validate_domain( $domain_raw );

		if ( ! $validation['valid'] ) {
			wp_send_json_error( array( 'message' => implode( '<br>', $validation['errors'] ) ) );
			return;
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

		if ( ! $website ) {
			// Website doesn't exist - needs analysis.
			$needs_analysis = true;
		} elseif ( $force_update ) {
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
		}

		// Perform analysis if needed.
		if ( $needs_analysis ) {
			$result = V_WPSA_DB::analyze_website( $domain, $idn, $ip, $wid );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
				return;
			}

			// Verify analysis created the record.
			$website_check = $db->get_website_by_domain( $domain, array( 'id' ) );
			if ( ! $website_check ) {
				wp_send_json_error( array( 'message' => 'Analysis failed: domain record not created. Please try again or check your domain input.' ) );
				return;
			}
		}

		try {
			// Generate report using WordPress-native template system.
			$content = V_WPSA_Report_Generator::generate_html_report( $domain );

			// Also provide a fresh nonce in case the frontend lost the original one.
			$response_data = array(
				'html'  => $content,
				'nonce' => wp_create_nonce( 'v_wpsa_nonce' ),
			);

			// Return the HTML content and the helper nonce.
			wp_send_json_success( $response_data );
		} catch ( Throwable $t ) {
			// Log and return JSON error for the client.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Production error logging for troubleshooting.
			error_log( sprintf( 'v-wpsa: unhandled throwable during report generation for %s: %s in %s on line %d', $domain, $t->getMessage(), $t->getFile(), $t->getLine() ) );
			wp_send_json_error( array( 'message' => 'Internal error while generating report: ' . $t->getMessage() ) );
		}
	}

	/**
	 * AJAX handler for PDF download.
	 *
	 * @throws Exception If PDF generation or file reading fails.
	 */
	public static function download_pdf() {
		// Verify nonce for security.
		check_ajax_referer( 'v_wpsa_nonce', 'nonce' );

		// Get domain from request.
		$domain = isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : '';

		if ( empty( $domain ) ) {
			wp_send_json_error( array( 'message' => 'Domain is required' ) );
			return;
		}

		try {
			// Increase memory and execution time for PDF generation which can be heavy.
			if ( function_exists( 'wp_raise_memory_limit' ) ) {
				wp_raise_memory_limit( 'admin' );
			}
			if ( function_exists( 'set_time_limit' ) ) {
				set_time_limit( 0 );
			}

			// Register shutdown function to capture fatal errors.
			$domain_for_shutdown = $domain;
			register_shutdown_function(
				function () use ( $domain_for_shutdown ) {
					$error = error_get_last();
					if ( $error && in_array( $error['type'], array( E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE ), true ) ) {
						$error_msg = sprintf( 'v-wpsa: fatal error during PDF generation for %s: %s in %s on line %d', $domain_for_shutdown, $error['message'], $error['file'], $error['line'] );
						// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Production error logging for fatal errors.
						error_log( $error_msg );
						// Try to return a JSON error to the AJAX client.
						if ( ! headers_sent() ) {
							while ( ob_get_level() ) {
								ob_end_clean();
							}
							header( 'Content-Type: application/json; charset=utf-8' );
							header( 'HTTP/1.1 200 OK' );
							echo wp_json_encode(
								array(
									'success' => false,
									'data'    => array( 'message' => 'Internal server error while generating PDF: ' . $error['message'] ),
								)
							);
							exit;
						}
					}
				}
			);

			// Generate PDF using WordPress-native methods.
			$pdf_data = V_WPSA_Report_Generator::generate_pdf_report( $domain );

			// Read the PDF file.
			if ( ! file_exists( $pdf_data['file'] ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Production error logging for file operations.
				error_log( sprintf( 'v-wpsa: PDF file not found after generation: %s', $pdf_data['file'] ) );
				throw new Exception( 'PDF file not found' );
			}

			// Clear any output buffers before sending PDF to prevent corruption.
			while ( ob_get_level() ) {
				ob_end_clean();
			}

			// Output the PDF with proper headers.
			header( 'Content-Type: application/pdf' );
			header( 'Content-Disposition: attachment; filename="' . $pdf_data['filename'] . '"' );
			header( 'Content-Length: ' . filesize( $pdf_data['file'] ) );
			header( 'Cache-Control: private, max-age=0, must-revalidate' );
			header( 'Pragma: public' );
			// Explicitly disable compression for PDF downloads to prevent corruption.
			if ( function_exists( 'apache_setenv' ) ) {
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv -- Necessary to prevent PDF corruption.
				@apache_setenv( 'no-gzip', '1' );
			}
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.IniSet.Risky -- Necessary to prevent PDF corruption.
			@ini_set( 'zlib.output_compression', 'Off' );

			// Output file and exit. Convert PHP warnings to exceptions for better error handling.
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Legitimate error handling pattern.
			$prev_handler = set_error_handler(
				function ( $errno, $errstr, $errfile, $errline ) {
					throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
				}
			);
			$bytes        = false;
			try {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_readfile -- Direct file read is appropriate for streaming PDFs.
				$bytes = readfile( $pdf_data['file'] );
			} finally {
				if ( null !== $prev_handler ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler -- Restoring previous handler.
					set_error_handler( $prev_handler );
				} else {
					restore_error_handler();
				}
			}
			if ( false === $bytes ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Production error logging for file operations.
				error_log( sprintf( 'v-wpsa: readfile failed for %s', $pdf_data['file'] ) );
				throw new Exception( 'Unable to read PDF file' );
			}
			exit;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * AJAX handler for deleting a report.
	 * Only available to users with manage_options capability.
	 */
	public static function delete_report() {
		// Verify nonce for security.
		check_ajax_referer( 'v_wpsa_nonce', 'nonce' );

		// Check if user has admin permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Unauthorized: Only administrators can delete reports.' ) );
			return;
		}

		// Get domain from request.
		$domain = isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : '';

		if ( empty( $domain ) ) {
			wp_send_json_error( array( 'message' => 'Domain is required' ) );
			return;
		}

		try {
			// Get website ID from database.
			$db      = new V_WPSA_DB();
			$website = $db->get_website_by_domain( $domain, array( 'id' ) );

			if ( ! $website ) {
				wp_send_json_error( array( 'message' => 'Website not found in database' ) );
				return;
			}

			// Delete the website record and all related data from database.
			$deleted = $db->delete_website( $website['id'] );

			if ( ! $deleted ) {
				wp_send_json_error( array( 'message' => 'Failed to delete website record from database' ) );
				return;
			}

			// Delete PDF files.
			V_WPSA_Helpers::delete_pdf( $domain );
			V_WPSA_Helpers::delete_pdf( $domain . '_pagespeed' );

			// Delete thumbnails.
			$upload_dir    = wp_upload_dir();
			$thumbnail_dir = rtrim( $upload_dir['basedir'], '\/' ) . '/seo-audit/thumbnails/';
			$thumbnail_ext = array( '.jpg', '.jpeg', '.png', '.gif' );

			foreach ( $thumbnail_ext as $ext ) {
				$thumbnail_file = $thumbnail_dir . $domain . $ext;
				if ( file_exists( $thumbnail_file ) ) {
					wp_delete_file( $thumbnail_file );
				}
			}

			wp_send_json_success(
				array(
					'message' => 'Report deleted successfully',
					'domain'  => $domain,
				)
			);
		} catch ( Exception $e ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Production error logging for troubleshooting.
			error_log( sprintf( 'v-wpsa: Error deleting report for %s: %s', $domain, $e->getMessage() ) );
			wp_send_json_error( array( 'message' => 'Error deleting report: ' . $e->getMessage() ) );
		}
	}
}
