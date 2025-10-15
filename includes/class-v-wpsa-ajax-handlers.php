<?php
/**
 * AJAX Handlers Class
 *
 * Handles all WordPress AJAX endpoints for the plugin.
 *
 * @package v_wpsa
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

		// PagePeeker proxy handler (legacy).
		add_action( 'wp_ajax_v_wpsa_pagepeeker', array( __CLASS__, 'pagepeeker_proxy' ) );
		add_action( 'wp_ajax_nopriv_v_wpsa_pagepeeker', array( __CLASS__, 'pagepeeker_proxy' ) );

		// PDF download handler.
		add_action( 'wp_ajax_v_wpsa_download_pdf', array( __CLASS__, 'download_pdf' ) );
		add_action( 'wp_ajax_nopriv_v_wpsa_download_pdf', array( __CLASS__, 'download_pdf' ) );
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
	 */
	public static function generate_report() {
		// Verify nonce for security.
		check_ajax_referer( 'v_wpsa_nonce', 'nonce' );

		global $v_wpsa_app;

		// Convert warnings/notices to exceptions during this request so they can be returned
		// as structured JSON errors instead of causing HTTP 500.
		$prev_error_handler = set_error_handler(
			function( $errno, $errstr, $errfile, $errline ) {
				if ( ! ( error_reporting() & $errno ) ) {
					  // Respect @ operator: do not convert silently suppressed errors.
					  return false;
				}
				throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
			}
		);

		// Initialize Yii if not already initialized.
		if ( null === $v_wpsa_app ) {
			$yii    = v_wpsa_PLUGIN_DIR . 'framework/yii.php';
			$config = v_wpsa_PLUGIN_DIR . 'protected/config/main.php';

			if ( file_exists( $yii ) && file_exists( $config ) ) {
				require_once $yii;

				// Configure Yii autoloader to skip WordPress classes.
				V_WPSA_Yii_Integration::configure_yii_autoloader();

				$v_wpsa_app = Yii::createWebApplication( $config );

				if ( isset( $v_wpsa_app->params['app.timezone'] ) ) {
					$v_wpsa_app->setTimeZone( $v_wpsa_app->params['app.timezone'] );
				}
			} else {
				wp_send_json_error( array( 'message' => 'Application not initialized' ) );
				return;
			}
		}

		V_WPSA_Yii_Integration::configure_yii_app( $v_wpsa_app );

		// Get domain from request.
		$domain = isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : '';

		if ( empty( $domain ) ) {
			wp_send_json_error( array( 'message' => 'Domain is required' ) );
			return;
		}

		// Create and validate the model to trigger analysis if needed.
		// The WebsiteForm::validate() will automatically call tryToAnalyse()
		// which will create/update the website record in the database.
		$model         = new WebsiteForm();
		$model->domain = $domain;

		if ( ! $model->validate() ) {
			// Validation failed (domain invalid, unreachable, or analysis error).
			$errors         = $model->getErrors();
			$error_messages = array();
			foreach ( $errors as $field => $field_errors ) {
				foreach ( $field_errors as $error ) {
					$error_messages[] = $error;
				}
			}
			wp_send_json_error( array( 'message' => implode( '<br>', $error_messages ) ) );
			return;
		}

		try {
			// Generate report using WordPress-native template system.
			$content = V_WPSA_Report_Generator::generate_html_report( $model->domain );

			// Also provide a fresh nonce in case the frontend lost the original one
			// (for example when HTML is injected via AJAX into pages without the inline script).
			$response_data = array(
				'html'  => $content,
				'nonce' => wp_create_nonce( 'v_wpsa_nonce' ),
			);

			// Return the HTML content and the helper nonce.
			wp_send_json_success( $response_data );
		} catch ( Throwable $t ) {
			// Log and return JSON error for the client.
			error_log( sprintf( 'v-wpsa: unhandled throwable during PDF download for %s: %s in %s on line %d', $domain, $t->getMessage(), $t->getFile(), $t->getLine() ) );
			if ( function_exists( 'Yii' ) ) {
				Yii::log( $t->getMessage(), CLogger::LEVEL_ERROR );
			}
			// Restore previous error handler if set.
			if ( isset( $prev_error_handler ) && null !== $prev_error_handler ) {
				set_error_handler( $prev_error_handler );
			} else {
				restore_error_handler();
			}
			wp_send_json_error( array( 'message' => 'Internal error while generating PDF: ' . $t->getMessage() ) );
		} finally {
			// Ensure error handler is restored if the request completes normally.
			if ( isset( $prev_error_handler ) && null !== $prev_error_handler ) {
				set_error_handler( $prev_error_handler );
			} else {
				restore_error_handler();
			}
		}
	}

	/**
	 * AJAX handler for PagePeeker proxy (legacy).
	 */
	public static function pagepeeker_proxy() {
		// Verify nonce for security.
		check_ajax_referer( 'v_wpsa_nonce', 'nonce' );

		global $v_wpsa_app;

		// Initialize Yii if not already initialized.
		if ( null === $v_wpsa_app ) {
			$yii    = v_wpsa_PLUGIN_DIR . 'framework/yii.php';
			$config = v_wpsa_PLUGIN_DIR . 'protected/config/main.php';

			if ( file_exists( $yii ) && file_exists( $config ) ) {
				require_once $yii;

				// Configure Yii autoloader to skip WordPress classes.
				V_WPSA_Yii_Integration::configure_yii_autoloader();

				$v_wpsa_app = Yii::createWebApplication( $config );

				if ( isset( $v_wpsa_app->params['app.timezone'] ) ) {
					$v_wpsa_app->setTimeZone( $v_wpsa_app->params['app.timezone'] );
				}
			} else {
				wp_send_json_error( array( 'message' => 'Application not initialized' ) );
				return;
			}
		}

		V_WPSA_Yii_Integration::configure_yii_app( $v_wpsa_app );

		// Check if thumbnail proxy is enabled (it's disabled by default).
		if ( ! isset( $v_wpsa_app->params['thumbnail.proxy'] ) || ! $v_wpsa_app->params['thumbnail.proxy'] ) {
			// Thumbnail proxy is disabled, use direct thum.io URLs instead.
			$url = isset( $_GET['url'] ) ? sanitize_text_field( wp_unslash( $_GET['url'] ) ) : '';
			if ( $url ) {
				// Return success with a message that thumbnails are served directly.
				wp_send_json_success( array( 'message' => 'Thumbnails are served directly from thum.io' ) );
			} else {
				wp_send_json_error( array( 'message' => 'Thumbnail proxy is not enabled' ) );
			}
			return;
		}

		// Legacy PagePeeker proxy code (not used with current thum.io implementation).
		wp_send_json_error( array( 'message' => 'PagePeeker proxy is deprecated' ) );
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

			// Debug logging.
			$upload_dir = function_exists( 'wp_upload_dir' ) ? wp_upload_dir() : array( 'basedir' => '' );
			error_log( sprintf( 'v-wpsa: download_pdf start for domain=%s uploads=%s memory_limit=%s', $domain, $upload_dir['basedir'], ini_get( 'memory_limit' ) ) );

			// Generate PDF using WordPress-native methods.
			$pdf_data = V_WPSA_Report_Generator::generate_pdf_report( $domain );

			error_log( sprintf( 'v-wpsa: generate_pdf_report returned: %s', var_export( $pdf_data, true ) ) );

			// Read the PDF file.
			if ( ! file_exists( $pdf_data['file'] ) ) {
				error_log( sprintf( 'v-wpsa: PDF file not found after generation: %s', $pdf_data['file'] ) );
				throw new Exception( 'PDF file not found' );
			}

			// Output the PDF with proper headers.
			header( 'Content-Type: application/pdf' );
			header( 'Content-Disposition: attachment; filename="' . $pdf_data['filename'] . '"' );
			header( 'Content-Length: ' . filesize( $pdf_data['file'] ) );
			header( 'Cache-Control: private, max-age=0, must-revalidate' );
			header( 'Pragma: public' );

			// Output file and exit.
			$prev_handler = set_error_handler(
				function ( $errno, $errstr, $errfile, $errline ) {
					throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
				}
			);
			$bytes        = false;
			try {
				$bytes = readfile( $pdf_data['file'] );
			} finally {
				if ( null !== $prev_handler ) {
					set_error_handler( $prev_handler );
				} else {
					restore_error_handler();
				}
			}
			if ( false === $bytes ) {
				error_log( sprintf( 'v-wpsa: readfile failed for %s', $pdf_data['file'] ) );
				throw new Exception( 'Unable to read PDF file' );
			}
			exit;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
}
