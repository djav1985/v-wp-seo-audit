<?php
/**
 * Report Generator Class
 * WordPress-native report generation without Yii controllers.
 * Replaces functionality from WebsitestatController.
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class V_WPSA_Report_Generator
 */
class V_WPSA_Report_Generator {

	/**
	 * Generate HTML report for a domain.
	 *
	 * @param string $domain Domain to generate report for.
	 * @return string HTML content.
	 * @throws Exception If report cannot be generated.
	 */
	public static function generate_html_report( $domain ) {
		// Check if WP-native generation is enabled via feature flag.
		$use_native = get_option( 'v_wpsa_use_native_generator', false );

		if ( $use_native ) {
			// Use WordPress-native data collection.
			return self::generate_html_report_native( $domain );
		}

		// Legacy Yii-based generation (fallback).
		return self::generate_html_report_legacy( $domain );
	}

	/**
	 * Generate HTML report using WordPress-native data collection.
	 *
	 * @param string $domain Domain to generate report for.
	 * @return string HTML content.
	 * @throws Exception If report cannot be generated.
	 */
	private static function generate_html_report_native( $domain ) {
		// Use WordPress-native database class for data collection.
		$db = new V_WPSA_DB();

		// Get full report data.
		$data = $db->get_website_report_full_data( $domain );

		if ( ! $data ) {
			throw new Exception( 'Website not found: ' . $domain );
		}

		// Render using WordPress template.
		return self::render_template( 'report.php', $data );
	}

	/**
	 * Generate HTML report using legacy Yii controller (fallback).
	 *
	 * @param string $domain Domain to generate report for.
	 * @return string HTML content.
	 * @throws Exception If report cannot be generated.
	 */
	private static function generate_html_report_legacy( $domain ) {
		global $v_wpsa_app;

		if ( null === $v_wpsa_app ) {
			throw new Exception( 'Yii application not initialized' );
		}

		// Set domain in GET for compatibility with controller logic.
		$_GET['domain'] = $domain;

		// Import and instantiate controller to collect data.
		Yii::import( 'application.controllers.WebsitestatController' );
		$controller = new WebsitestatController( 'websitestat' );
		$controller->init();

		// Get all the data from controller protected properties.
		$data = self::extract_controller_data( $controller );

		// Render using WordPress template.
		return self::render_template( 'report.php', $data );
	}

	/**
	 * Generate PDF report for a domain.
	 *
	 * @param string $domain Domain to generate PDF for.
	 * @return array Array with 'file' => path to PDF file, 'filename' => suggested filename.
	 * @throws Exception If PDF cannot be generated.
	 */
	public static function generate_pdf_report( $domain ) {
		// Check if WP-native generation is enabled via feature flag.
		$use_native = get_option( 'v_wpsa_use_native_generator', false );

		if ( $use_native ) {
			// Use WordPress-native PDF generation.
			return self::generate_pdf_report_native( $domain );
		}

		// Legacy Yii-based generation (fallback).
		return self::generate_pdf_report_legacy( $domain );
	}

	/**
	 * Generate PDF report using WordPress-native methods.
	 *
	 * @param string $domain Domain to generate PDF for.
	 * @return array Array with 'file' => path to PDF file, 'filename' => suggested filename.
	 * @throws Exception If PDF cannot be generated.
	 */
	private static function generate_pdf_report_native( $domain ) {
		// Use WordPress-native database class for data collection.
		$db = new V_WPSA_DB();

		// Get full report data.
		$data = $db->get_website_report_full_data( $domain );

		if ( ! $data ) {
			throw new Exception( 'Website not found: ' . $domain );
		}

		// Ensure thumbnail is a URL string for the PDF template.
		if ( isset( $data['thumbnail'] ) && is_array( $data['thumbnail'] ) ) {
			if ( isset( $data['thumbnail']['thumb'] ) && ! empty( $data['thumbnail']['thumb'] ) ) {
				$data['thumbnail'] = $data['thumbnail']['thumb'];
			} elseif ( isset( $data['thumbnail']['url'] ) && ! empty( $data['thumbnail']['url'] ) ) {
				// Fallback: construct a thum.io URL when no cached thumb is present.
				$data['thumbnail'] = 'https://image.thum.io/get/maxAge/350/width/350/https://' . $data['thumbnail']['url'];
			} else {
				$data['thumbnail'] = '';
			}
		}

		// Render PDF template to HTML.
		$html = self::render_template( 'pdf.php', $data );

		// Generate PDF file path.
		if ( ! class_exists( 'Utils' ) ) {
			require_once v_wpsa_PLUGIN_DIR . 'protected/components/Utils.php';
		}
		$pdf_file = Utils::createPdfFolder( $domain );

		// Create PDF using TCPDF directly (WordPress-native).
		self::create_pdf_from_html_native( $html, $pdf_file, $data['website']['idn'] );

		// Ensure PDF was created.
		if ( ! file_exists( $pdf_file ) ) {
			throw new Exception( 'Failed to create PDF file' );
		}

		return array(
			'file'     => $pdf_file,
			'filename' => $data['website']['idn'] . '.pdf',
		);
	}

	/**
	 * Generate PDF report using legacy Yii methods (fallback).
	 *
	 * @param string $domain Domain to generate PDF for.
	 * @return array Array with 'file' => path to PDF file, 'filename' => suggested filename.
	 * @throws Exception If PDF cannot be generated.
	 */
	private static function generate_pdf_report_legacy( $domain ) {
		global $v_wpsa_app;

		if ( null === $v_wpsa_app ) {
			throw new Exception( 'Yii application not initialized' );
		}

		// Set domain in GET for compatibility.
		$_GET['domain'] = $domain;

		// Import and instantiate controller.
		Yii::import( 'application.controllers.WebsitestatController' );
		$controller = new WebsitestatController( 'websitestat' );
		$controller->init();

		// Get data from controller.
		$data = self::extract_controller_data( $controller );

		// Ensure thumbnail is a URL string for the PDF template.
		if ( isset( $data['thumbnail'] ) && is_array( $data['thumbnail'] ) ) {
			if ( isset( $data['thumbnail']['thumb'] ) && ! empty( $data['thumbnail']['thumb'] ) ) {
				$data['thumbnail'] = $data['thumbnail']['thumb'];
			} elseif ( isset( $data['thumbnail']['url'] ) && ! empty( $data['thumbnail']['url'] ) ) {
				// Fallback: construct a thum.io URL when no cached thumb is present.
				$data['thumbnail'] = 'https://image.thum.io/get/maxAge/350/width/350/https://' . $data['thumbnail']['url'];
			} else {
				$data['thumbnail'] = '';
			}
		}

		// Render PDF template to HTML.
		$html = self::render_template( 'pdf.php', $data );

		// Generate PDF file.
		$filename = $domain;
		$pdf_file = Utils::createPdfFolder( $filename );

		// Use Yii PDF generation but only save the file to disk (do not stream it).
		// The AJAX handler will send the file to the client.
		$controller->createPdfFromHtml( $html, $pdf_file, $data['website']['idn'], false );

		// Ensure PDF was created.
		if ( ! file_exists( $pdf_file ) ) {
			throw new Exception( 'Failed to create PDF file' );
		}

		return array(
			'file'     => $pdf_file,
			'filename' => $data['website']['idn'] . '.pdf',
		);
	}

	/**
	 * Create PDF from HTML using TCPDF directly (WordPress-native).
	 *
	 * @param string $html HTML content.
	 * @param string $pdf_file Path to save PDF file.
	 * @param string $title PDF title.
	 * @throws Exception If PDF cannot be created.
	 */
	private static function create_pdf_from_html_native( $html, $pdf_file, $title ) {
		// Load TCPDF library directly.
		$tcpdf_path = v_wpsa_PLUGIN_DIR . 'protected/extensions/tcpdf/tcpdf/tcpdf.php';
		if ( ! file_exists( $tcpdf_path ) ) {
			throw new Exception( 'TCPDF library not found' );
		}

		// Define K_PATH_CACHE for TCPDF if not already defined.
		if ( ! defined( 'K_PATH_CACHE' ) ) {
			$upload_dir = wp_upload_dir();
			$cache_dir  = $upload_dir['basedir'] . '/seo-audit/cache/';
			if ( ! file_exists( $cache_dir ) ) {
				wp_mkdir_p( $cache_dir );
			}
			define( 'K_PATH_CACHE', $cache_dir );
		}

		require_once $tcpdf_path;

		// Create new PDF document.
		$pdf = new TCPDF( 'P', 'cm', 'A4', true, 'UTF-8' );

		// Set document information.
		$pdf->SetCreator( 'WordPress SEO Audit Plugin' );
		$pdf->SetAuthor( 'Website Review Tool' );
		$pdf->SetTitle( 'Website review ' . $title );
		$pdf->SetSubject( 'Website review ' . $title );

		// Remove default header/footer.
		$pdf->setPrintHeader( false );
		$pdf->setPrintFooter( false );

		// Add a page.
		$pdf->AddPage();

		// Set font.
		$pdf->SetFont( 'dejavusans', '', 10, '', false );

		// Write HTML content.
		// Suppress warnings (e.g., for remote images that may fail to load).
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@$pdf->writeHTML( $html, true, false, true, false, '' );

		// Save PDF to disk. Convert PHP warnings (e.g., fopen failures) into exceptions.
		$prev_handler = set_error_handler(
			function ( $errno, $errstr, $errfile, $errline ) {
				throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
			}
		);

		try {
			$pdf->Output( $pdf_file, 'F' );
		} finally {
			// Restore previous error handler even if Output() threw.
			if ( null !== $prev_handler ) {
				set_error_handler( $prev_handler );
			} else {
				restore_error_handler();
			}
		}

		// Ensure the file was written successfully.
		if ( ! file_exists( $pdf_file ) ) {
			throw new Exception( 'PDF engine failed to create file' );
		}
	}

	/**
	 * Extract data from controller using reflection.
	 *
	 * @param object $controller WebsitestatController instance.
	 * @return array Data array for template.
	 */
	private static function extract_controller_data( $controller ) {
		// Use reflection to access protected properties.
		$reflection = new ReflectionClass( $controller );

		$data = array();

		// List of properties to extract.
		$properties = array( 'website', 'cloud', 'content', 'document', 'isseter', 'links', 'meta', 'w3c', 'generated', 'diff', 'thumbnail', 'misc' );

		foreach ( $properties as $prop_name ) {
			if ( $reflection->hasProperty( $prop_name ) ) {
				$property = $reflection->getProperty( $prop_name );
				$property->setAccessible( true );
				$data[ $prop_name ] = $property->getValue( $controller );
			}
		}

		// Add calculated values.
		$data['over_max']     = 6;
		$data['linkcount']    = isset( $data['links']['links'] ) ? count( $data['links']['links'] ) : 0;
		$data['rateprovider'] = new RateProvider();
		$data['updUrl']       = V_WPSA_Config::get( 'param.instant_redirect' ) ? '#update_form' : '#';

		return $data;
	}

	/**
	 * Render a template file with data.
	 *
	 * @param string $template Template filename (e.g., 'report.php').
	 * @param array  $data Data to pass to template.
	 * @return string Rendered HTML.
	 * @throws Exception If template not found.
	 */
	private static function render_template( $template, $data ) {
		$template_path = v_wpsa_PLUGIN_DIR . 'templates/' . $template;

		if ( ! file_exists( $template_path ) ) {
			throw new Exception( 'Template not found: ' . $template );
		}

		// Extract data array into variables for the template.
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $data, EXTR_SKIP );

		// Start output buffering.
		ob_start();

		// Include template file.
		include $template_path;

		// Get contents and clean buffer.
		$output = ob_get_clean();

		return $output;
	}
}
