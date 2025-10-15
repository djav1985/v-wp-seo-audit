<?php
/**
 * Report Generator Class
 * WordPress-native report generation without Yii controllers.
 * Replaces functionality from WebsitestatController.
 *
 * @package V_WP_SEO_Audit
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
		// Still need to use Yii for data collection temporarily.
		// This will be migrated in a future phase.
		global $v_wp_seo_audit_app;

		if ( null === $v_wp_seo_audit_app ) {
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
		// Still need to use Yii for data collection and PDF generation temporarily.
		global $v_wp_seo_audit_app;

		if ( null === $v_wp_seo_audit_app ) {
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

		// Render PDF template to HTML.
		$html = self::render_template( 'pdf.php', $data );

		// Generate PDF file.
		$filename = $domain;
		$pdf_file = Utils::createPdfFolder( $filename );

		// Use Yii PDF generation (will be replaced in future).
		$controller->createPdfFromHtml( $html, $pdf_file, $data['website']['idn'] );

		return array(
			'file'     => $pdf_file,
			'filename' => $data['website']['idn'] . '.pdf',
		);
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
		$template_path = V_WP_SEO_AUDIT_PLUGIN_DIR . 'templates/' . $template;

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
