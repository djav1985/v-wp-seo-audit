<?php
/**
 * Report Generator Class
 *
 * WordPress-native report generation.
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
		// Use WordPress-native database class for data collection.
		$db = new V_WPSA_DB();

		// Get full report data.
		$data = $db->get_full_report_data( $domain );

		if ( ! $data ) {
			throw new Exception( 'Website not found: ' . $domain );
		}

		// Render using WordPress template.
		// The template will call addCompare* methods which calculate the score.
		$html = self::render_template( 'report.php', $data );

		// Persist calculated score after template rendering.
		if ( isset( $data['website']['id'] ) && isset( $data['rateprovider'] ) && is_object( $data['rateprovider'] ) ) {
			try {
				if ( method_exists( $data['rateprovider'], 'getScore' ) ) {
					$score = (int) $data['rateprovider']->getScore();
					$db    = new V_WPSA_DB();
					$db->set_website_score( $data['website']['id'], $score );
					// Update the data array so the template shows the right score.
					$data['website']['score'] = $score;
				}
			} catch ( Exception $e ) {
				// Don't break rendering on score persistence failure; just continue.
				error_log( 'v-wpsa: Failed to persist score: ' . $e->getMessage() );
			}
		}

		return $html;
	}

	/**
	 * Generate PDF report for a domain.
	 *
	 * @param string $domain Domain to generate PDF for.
	 * @return array Array with 'file' => path to PDF file, 'filename' => suggested filename.
	 * @throws Exception If PDF cannot be generated.
	 */
	public static function generate_pdf_report( $domain ) {
		// Use WordPress-native database class for data collection.
		$db = new V_WPSA_DB();

		// Get full report data.
		$data = $db->get_full_report_data( $domain );

		if ( ! $data ) {
			throw new Exception( 'Website not found: ' . $domain );
		}

		// Generate PDF file path.
		$pdf_file = V_WPSA_Utils::create_pdf_folder( $domain );

		// Check if PDF already exists and is fresh (within cache time).
		$cache_time = apply_filters( 'v_wpsa_cache_time', DAY_IN_SECONDS );
		if ( file_exists( $pdf_file ) ) {
			$file_time = filemtime( $pdf_file );
			if ( ( time() - $file_time ) < $cache_time ) {
				// PDF exists and is fresh, return cached version.
				return array(
					'file'     => $pdf_file,
					'filename' => $data['website']['idn'] . '.pdf',
					'cached'   => true,
				);
			}
		}

		// PDF doesn't exist or is stale, generate new one.
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
		// The template will call addCompare* methods which calculate the score.
		$html = self::render_template( 'pdf.php', $data );

		// Persist calculated score after template rendering.
		if ( isset( $data['website']['id'] ) && isset( $data['rateprovider'] ) && is_object( $data['rateprovider'] ) ) {
			try {
				if ( method_exists( $data['rateprovider'], 'getScore' ) ) {
					$score = (int) $data['rateprovider']->getScore();
					$db    = new V_WPSA_DB();
					$db->set_website_score( $data['website']['id'], $score );
				}
			} catch ( Exception $e ) {
				// Ignore persistence errors and continue PDF generation.
				error_log( 'v-wpsa: Failed to persist score: ' . $e->getMessage() );
			}
		}

		// Create PDF using TCPDF directly.
		self::create_pdf_from_html( $html, $pdf_file, $data['website']['idn'] );

		// Ensure PDF was created.
		if ( ! file_exists( $pdf_file ) ) {
			throw new Exception( 'Failed to create PDF file' );
		}

		return array(
			'file'     => $pdf_file,
			'filename' => $data['website']['idn'] . '.pdf',
			'cached'   => false,
		);
	}

	/**
	 * Create PDF from HTML using TCPDF directly.
	 *
	 * @param string $html HTML content.
	 * @param string $pdf_file Path to save PDF file.
	 * @param string $title PDF title.
	 * @throws Exception If PDF cannot be created.
	 */
	private static function create_pdf_from_html( $html, $pdf_file, $title ) {
		// Load TCPDF library directly.
		$tcpdf_path = v_wpsa_PLUGIN_DIR . 'tcpdf/tcpdf/tcpdf.php';
		if ( ! file_exists( $tcpdf_path ) ) {
			throw new Exception( 'TCPDF library not found' );
		}

		// Define K_PATH_CACHE for TCPDF if not already defined.
		// TCPDF requires a writable cache directory for image and font processing.
		// We store it in WordPress uploads directory under seo-audit/cache/.
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
