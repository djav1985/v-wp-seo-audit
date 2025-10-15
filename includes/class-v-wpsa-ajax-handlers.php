<?php
/**
 * AJAX Handlers Class
 *
 * Handles all WordPress AJAX endpoints for the plugin.
 *
 * @package V_WP_SEO_Audit
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
		add_action( 'wp_ajax_v_wp_seo_audit_validate', array( __CLASS__, 'validate_domain' ) );
		add_action( 'wp_ajax_nopriv_v_wp_seo_audit_validate', array( __CLASS__, 'validate_domain' ) );

		// Report generation handler.
		add_action( 'wp_ajax_v_wp_seo_audit_generate_report', array( __CLASS__, 'generate_report' ) );
		add_action( 'wp_ajax_nopriv_v_wp_seo_audit_generate_report', array( __CLASS__, 'generate_report' ) );

		// PagePeeker proxy handler (legacy).
		add_action( 'wp_ajax_v_wp_seo_audit_pagepeeker', array( __CLASS__, 'pagepeeker_proxy' ) );
		add_action( 'wp_ajax_nopriv_v_wp_seo_audit_pagepeeker', array( __CLASS__, 'pagepeeker_proxy' ) );

		// PDF download handler.
		add_action( 'wp_ajax_v_wp_seo_audit_download_pdf', array( __CLASS__, 'download_pdf' ) );
		add_action( 'wp_ajax_nopriv_v_wp_seo_audit_download_pdf', array( __CLASS__, 'download_pdf' ) );
	}

	/**
	 * AJAX handler for domain validation.
	 */
	public static function validate_domain() {
		// Verify nonce for security.
		check_ajax_referer( 'v_wp_seo_audit_nonce', 'nonce' );

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
		check_ajax_referer( 'v_wp_seo_audit_nonce', 'nonce' );

		global $v_wp_seo_audit_app;

		// Initialize Yii if not already initialized.
		if ( null === $v_wp_seo_audit_app ) {
			$yii    = V_WP_SEO_AUDIT_PLUGIN_DIR . 'framework/yii.php';
			$config = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/main.php';

			if ( file_exists( $yii ) && file_exists( $config ) ) {
				require_once $yii;
				$v_wp_seo_audit_app = Yii::createWebApplication( $config );

				if ( isset( $v_wp_seo_audit_app->params['app.timezone'] ) ) {
					$v_wp_seo_audit_app->setTimeZone( $v_wp_seo_audit_app->params['app.timezone'] );
				}
			} else {
				wp_send_json_error( array( 'message' => 'Application not initialized' ) );
				return;
			}
		}

		V_WPSA_Yii_Integration::configure_yii_app( $v_wp_seo_audit_app );

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
				'nonce' => wp_create_nonce( 'v_wp_seo_audit_nonce' ),
			);

			// Return the HTML content and the helper nonce.
			wp_send_json_success( $response_data );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	/**
	 * AJAX handler for PagePeeker proxy (legacy).
	 */
	public static function pagepeeker_proxy() {
		// Verify nonce for security.
		check_ajax_referer( 'v_wp_seo_audit_nonce', 'nonce' );

		global $v_wp_seo_audit_app;

		// Initialize Yii if not already initialized.
		if ( null === $v_wp_seo_audit_app ) {
			$yii    = V_WP_SEO_AUDIT_PLUGIN_DIR . 'framework/yii.php';
			$config = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/main.php';

			if ( file_exists( $yii ) && file_exists( $config ) ) {
				require_once $yii;
				$v_wp_seo_audit_app = Yii::createWebApplication( $config );

				if ( isset( $v_wp_seo_audit_app->params['app.timezone'] ) ) {
					$v_wp_seo_audit_app->setTimeZone( $v_wp_seo_audit_app->params['app.timezone'] );
				}
			} else {
				wp_send_json_error( array( 'message' => 'Application not initialized' ) );
				return;
			}
		}

		V_WPSA_Yii_Integration::configure_yii_app( $v_wp_seo_audit_app );

		// Check if thumbnail proxy is enabled (it's disabled by default).
		if ( ! isset( $v_wp_seo_audit_app->params['thumbnail.proxy'] ) || ! $v_wp_seo_audit_app->params['thumbnail.proxy'] ) {
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
	 */
	public static function download_pdf() {
		// Verify nonce for security.
		check_ajax_referer( 'v_wp_seo_audit_nonce', 'nonce' );

		global $v_wp_seo_audit_app;

		// Initialize Yii if not already initialized.
		if ( null === $v_wp_seo_audit_app ) {
			$yii    = V_WP_SEO_AUDIT_PLUGIN_DIR . 'framework/yii.php';
			$config = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/main.php';

			if ( file_exists( $yii ) && file_exists( $config ) ) {
				require_once $yii;
				$v_wp_seo_audit_app = Yii::createWebApplication( $config );

				if ( isset( $v_wp_seo_audit_app->params['app.timezone'] ) ) {
					$v_wp_seo_audit_app->setTimeZone( $v_wp_seo_audit_app->params['app.timezone'] );
				}
			} else {
				wp_send_json_error( array( 'message' => 'Application not initialized' ) );
				return;
			}
		}

		V_WPSA_Yii_Integration::configure_yii_app( $v_wp_seo_audit_app );

		// Get domain from request.
		$domain = isset( $_POST['domain'] ) ? sanitize_text_field( wp_unslash( $_POST['domain'] ) ) : '';

		if ( empty( $domain ) ) {
			wp_send_json_error( array( 'message' => 'Domain is required' ) );
			return;
		}

		try {
			// Generate PDF using WordPress-native template system.
			$pdf_data = V_WPSA_Report_Generator::generate_pdf_report( $domain );

			// Read the PDF file.
			if ( ! file_exists( $pdf_data['file'] ) ) {
				throw new Exception( 'PDF file not found' );
			}

			// Output the PDF with proper headers.
			header( 'Content-Type: application/pdf' );
			header( 'Content-Disposition: attachment; filename="' . $pdf_data['filename'] . '"' );
			header( 'Content-Length: ' . filesize( $pdf_data['file'] ) );
			header( 'Cache-Control: private, max-age=0, must-revalidate' );
			header( 'Pragma: public' );

			// Output file and exit.
			readfile( $pdf_data['file'] );
			exit;
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}
}
