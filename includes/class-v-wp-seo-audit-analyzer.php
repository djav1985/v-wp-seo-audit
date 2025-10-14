<?php
/**
 * Domain analysis class for V-WP-SEO-Audit plugin.
 * WordPress-native implementation of domain analysis (replaces ParseCommand).
 * This class performs the actual SEO audit of websites.
 *
 * @package V_WP_SEO_Audit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class V_WP_SEO_Audit_Analyzer
 *
 * WordPress-native domain analyzer.
 * Replaces the Yii ParseCommand that was removed in Phase 1.
 */
class V_WP_SEO_Audit_Analyzer {

	/**
	 * Database helper instance.
	 *
	 * @var V_WP_SEO_Audit_DB
	 */
	protected $db;

	/**
	 * Website domain (ASCII encoded).
	 *
	 * @var string
	 */
	protected $domain;

	/**
	 * Internationalized Domain Name (IDN).
	 *
	 * @var string
	 */
	protected $idn;

	/**
	 * IP address of the domain.
	 *
	 * @var string
	 */
	protected $ip;

	/**
	 * Website ID (for updates).
	 *
	 * @var int|null
	 */
	protected $website_id;

	/**
	 * Whether this is an update operation.
	 *
	 * @var bool
	 */
	protected $is_update = false;

	/**
	 * Error messages.
	 *
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Constructor.
	 *
	 * @param string   $domain Domain name (ASCII encoded).
	 * @param string   $idn Internationalized domain name.
	 * @param string   $ip IP address.
	 * @param int|null $website_id Website ID for updates.
	 */
	public function __construct( $domain, $idn, $ip, $website_id = null ) {
		$this->db         = new V_WP_SEO_Audit_DB();
		$this->domain     = $domain;
		$this->idn        = $idn;
		$this->ip         = $ip;
		$this->website_id = $website_id;
		$this->is_update  = ! is_null( $website_id );
	}

	/**
	 * Run the analysis.
	 * This is the main entry point that triggers domain analysis.
	 *
	 * NOTE: The actual domain analysis logic from ParseCommand was removed in Phase 1
	 * cleanup but is still required for the plugin to function. This method attempts
	 * to use the Yii command runner if available, but gracefully handles its absence.
	 *
	 * TODO: Implement WordPress-native analysis logic to replace Yii ParseCommand.
	 * This would include:
	 * - HTTP request to fetch website content
	 * - HTML parsing for meta tags, links, content
	 * - W3C validation
	 * - SEO scoring calculation
	 * - Database storage of results
	 *
	 * @return bool True on success, false on failure.
	 */
	public function analyze() {
		// Try to use Yii command runner if available.
		if ( $this->analyze_via_yii() ) {
			return true;
		}

		// If Yii method fails due to missing commands directory,
		// provide a helpful error message.
		if ( empty( $this->errors ) ) {
			$this->errors[] = __(
				'Analysis functionality requires migration. The ParseCommand was removed but analysis logic needs to be reimplemented.',
				'v-wp-seo-audit'
			);
		}

		return false;
	}

	/**
	 * Analyze via Yii command runner (legacy method).
	 * This delegates to the existing Yii ParseCommand for now.
	 *
	 * @return bool True on success, false on failure.
	 */
	protected function analyze_via_yii() {
		global $v_wp_seo_audit_app;

		// Ensure Yii is initialized.
		if ( null === $v_wp_seo_audit_app ) {
			$this->errors[] = __( 'Yii application not initialized', 'v-wp-seo-audit' );
			return false;
		}

		// Build command arguments.
		if ( $this->is_update ) {
			$args = array(
				'yiic',
				'parse',
				'update',
				"--domain={$this->domain}",
				"--idn={$this->idn}",
				"--ip={$this->ip}",
				"--wid={$this->website_id}",
			);
		} else {
			$args = array(
				'yiic',
				'parse',
				'insert',
				"--domain={$this->domain}",
				"--idn={$this->idn}",
				"--ip={$this->ip}",
			);
		}

		try {
			// Get command path.
			$command_path = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'commands';

			// Check if commands directory exists.
			if ( ! is_dir( $command_path ) ) {
				$this->errors[] = __( 'Commands directory not found. Analysis functionality may have been removed during migration.', 'v-wp-seo-audit' );
				return false;
			}

			// Create new console command runner.
			$runner = new CConsoleCommandRunner();
			// Adding commands.
			$runner->addCommands( $command_path );
			// If something goes wrong return error.
			if ( $error = $runner->run( $args ) ) {
				$this->errors[] = sprintf( __( 'Error Code %d', 'v-wp-seo-audit' ), $error );
				return false;
			}

			// After analysis, check if DB record exists.
			$website_check = $this->db->get_website_by_domain( $this->domain, array( 'id' ) );
			if ( ! $website_check ) {
				$this->errors[] = __( 'Analysis failed: domain record not created. Please try again or check your domain input.', 'v-wp-seo-audit' );
				return false;
			}

			return true;
		} catch ( Exception $e ) {
			$this->errors[] = $e->getMessage();
			return false;
		}
	}

	/**
	 * Get error messages.
	 *
	 * @return array Array of error messages.
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Static factory method to create analyzer for insert operation.
	 *
	 * @param string $domain Domain name (ASCII encoded).
	 * @param string $idn Internationalized domain name.
	 * @param string $ip IP address.
	 * @return V_WP_SEO_Audit_Analyzer Analyzer instance.
	 */
	public static function for_insert( $domain, $idn, $ip ) {
		return new self( $domain, $idn, $ip, null );
	}

	/**
	 * Static factory method to create analyzer for update operation.
	 *
	 * @param string $domain Domain name (ASCII encoded).
	 * @param string $idn Internationalized domain name.
	 * @param string $ip IP address.
	 * @param int    $website_id Website ID.
	 * @return V_WP_SEO_Audit_Analyzer Analyzer instance.
	 */
	public static function for_update( $domain, $idn, $ip, $website_id ) {
		return new self( $domain, $idn, $ip, $website_id );
	}
}
