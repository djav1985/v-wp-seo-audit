<?php
/**
 * Report generation class for V-WP-SEO-Audit plugin.
 * WordPress-native wrapper around Yii-based report generation.
 * This class provides an incremental migration path from Yii to WordPress native code.
 *
 * @package V_WP_SEO_Audit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class V_WP_SEO_Audit_Report
 *
 * WordPress-native report generation wrapper.
 * Provides methods to generate HTML and PDF reports.
 */
class V_WP_SEO_Audit_Report {

	/**
	 * Database helper instance.
	 *
	 * @var V_WP_SEO_Audit_DB
	 */
	protected $db;

	/**
	 * Website domain.
	 *
	 * @var string
	 */
	protected $domain;

	/**
	 * Website ID.
	 *
	 * @var int
	 */
	protected $website_id;

	/**
	 * Website data from database.
	 *
	 * @var array
	 */
	protected $website;

	/**
	 * Constructor.
	 *
	 * @param string $domain Website domain.
	 */
	public function __construct( $domain ) {
		$this->db     = new V_WP_SEO_Audit_DB();
		$this->domain = $domain;
	}

	/**
	 * Load website data from database.
	 *
	 * @return bool True on success, false if website not found.
	 */
	protected function load_website_data() {
		$this->website = $this->db->get_website_by_domain( $this->domain );
		
		if ( ! $this->website ) {
			return false;
		}

		$this->website_id = isset( $this->website['id'] ) ? (int) $this->website['id'] : 0;
		return true;
	}

	/**
	 * Check if website exists and is ready for report generation.
	 *
	 * @return array Result with 'exists' boolean and optional 'message'.
	 */
	public function check_website_status() {
		if ( ! $this->load_website_data() ) {
			return array(
				'exists'  => false,
				'message' => __( 'Website not found in database. Please run analysis first.', 'v-wp-seo-audit' ),
			);
		}

		// Check if data is fresh enough (within cache time).
		$modified    = isset( $this->website['modified'] ) ? $this->website['modified'] : '';
		$cache_time  = $this->get_cache_time();
		$modified_ts = strtotime( $modified );
		$is_fresh    = ( $modified_ts + $cache_time > time() );

		return array(
			'exists'   => true,
			'fresh'    => $is_fresh,
			'modified' => $modified,
		);
	}

	/**
	 * Get cache time from config or default.
	 *
	 * @return int Cache time in seconds.
	 */
	protected function get_cache_time() {
		// Default to 24 hours if not configured.
		$default_cache = 24 * 60 * 60;

		// Try to get from Yii config if available.
		global $v_wp_seo_audit_app;
		if ( $v_wp_seo_audit_app && isset( $v_wp_seo_audit_app->params['analyzer.cache_time'] ) ) {
			return (int) $v_wp_seo_audit_app->params['analyzer.cache_time'];
		}

		// Allow filtering via WordPress.
		return (int) apply_filters( 'v_wp_seo_audit_cache_time', $default_cache );
	}

	/**
	 * Generate HTML report.
	 * Currently delegates to Yii controller but provides WordPress-native wrapper.
	 *
	 * @return array Array with 'success' boolean, 'html' string, and optional 'error'.
	 */
	public function generate_html() {
		// Check if website exists.
		$status = $this->check_website_status();
		if ( ! $status['exists'] ) {
			return array(
				'success' => false,
				'error'   => $status['message'],
			);
		}

		// For now, delegate to existing Yii controller.
		// This allows incremental migration - we can replace this later.
		return $this->generate_html_via_yii();
	}

	/**
	 * Generate HTML report via Yii controller (legacy method).
	 * This will be replaced with WordPress-native implementation in future migration.
	 *
	 * @return array Array with 'success' boolean, 'html' string, and optional 'error'.
	 */
	protected function generate_html_via_yii() {
		global $v_wp_seo_audit_app;

		// Ensure Yii is initialized.
		if ( null === $v_wp_seo_audit_app ) {
			return array(
				'success' => false,
				'error'   => __( 'Application not initialized', 'v-wp-seo-audit' ),
			);
		}

		// Set domain in GET for controller.
		$_GET['domain'] = $this->domain;

		// Import controller.
		Yii::import( 'application.controllers.WebsitestatController' );

		// Capture output.
		ob_start();

		try {
			$controller = new WebsitestatController( 'websitestat' );
			$controller->init();

			$previous = Yii::app()->getController();
			Yii::app()->setController( $controller );

			try {
				$controller->actionGenerateHTML( $this->domain );
			} finally {
				Yii::app()->setController( $previous );
			}

			$html = ob_get_clean();

			return array(
				'success' => true,
				'html'    => $html,
			);
		} catch ( Exception $e ) {
			ob_end_clean();
			return array(
				'success' => false,
				'error'   => $e->getMessage(),
			);
		}
	}

	/**
	 * Generate PDF report.
	 *
	 * @return array Array with 'success' boolean, 'file' path, and optional 'error'.
	 */
	public function generate_pdf() {
		// Check if website exists.
		$status = $this->check_website_status();
		if ( ! $status['exists'] ) {
			return array(
				'success' => false,
				'error'   => $status['message'],
			);
		}

		// For now, delegate to existing Yii controller.
		return $this->generate_pdf_via_yii();
	}

	/**
	 * Generate PDF report via Yii controller (legacy method).
	 * This will be replaced with WordPress-native implementation in future migration.
	 *
	 * @return array Array with 'success' boolean, 'file' path, and optional 'error'.
	 */
	protected function generate_pdf_via_yii() {
		global $v_wp_seo_audit_app;

		// Ensure Yii is initialized.
		if ( null === $v_wp_seo_audit_app ) {
			return array(
				'success' => false,
				'error'   => __( 'Application not initialized', 'v-wp-seo-audit' ),
			);
		}

		// Load website data.
		if ( ! $this->load_website_data() ) {
			return array(
				'success' => false,
				'error'   => __( 'Website data not found', 'v-wp-seo-audit' ),
			);
		}

		// Set domain in GET for controller.
		$_GET['domain'] = $this->domain;

		// Import controller.
		Yii::import( 'application.controllers.WebsitestatController' );

		try {
			$controller = new WebsitestatController( 'websitestat' );
			$controller->init();

			$previous = Yii::app()->getController();
			Yii::app()->setController( $controller );

			try {
				// actionGeneratePDF calls Yii::app()->end() which exits the script.
				// We need to handle this differently.
				$controller->actionGeneratePDF( $this->domain );
			} finally {
				Yii::app()->setController( $previous );
			}

			// If we reach here, PDF was output successfully.
			return array( 'success' => true );
		} catch ( Exception $e ) {
			return array(
				'success' => false,
				'error'   => $e->getMessage(),
			);
		}
	}

	/**
	 * Get website data for display.
	 * Useful for templates and custom rendering.
	 *
	 * @return array|false Website data or false if not found.
	 */
	public function get_website_data() {
		if ( ! $this->load_website_data() ) {
			return false;
		}

		// Get all related data.
		$report_data = $this->db->get_website_report_data( $this->website_id );

		return array_merge(
			array( 'website' => $this->website ),
			$report_data
		);
	}
}
