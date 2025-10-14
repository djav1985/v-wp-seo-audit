<?php
/**
 * Public API functions for V-WP-SEO-Audit plugin.
 * These functions provide a WordPress-native interface for other plugins/themes.
 *
 * @package V_WP_SEO_Audit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get SEO audit report for a domain.
 * This is a convenience function that wraps the Report class.
 *
 * Usage:
 * ```php
 * $result = v_wp_seo_audit_get_report( 'example.com' );
 * if ( $result['success'] ) {
 *     echo $result['html'];
 * }
 * ```
 *
 * @param string $domain Domain name to get report for.
 * @return array Array with 'success', 'html' or 'error'.
 */
function v_wp_seo_audit_get_report( $domain ) {
	if ( empty( $domain ) ) {
		return array(
			'success' => false,
			'error'   => __( 'Domain is required', 'v-wp-seo-audit' ),
		);
	}

	// Ensure classes are loaded.
	if ( ! class_exists( 'V_WP_SEO_Audit_Report' ) ) {
		return array(
			'success' => false,
			'error'   => __( 'Report class not available', 'v-wp-seo-audit' ),
		);
	}

	$report = new V_WP_SEO_Audit_Report( $domain );
	return $report->generate_html();
}

/**
 * Check if a domain has been analyzed.
 *
 * Usage:
 * ```php
 * $status = v_wp_seo_audit_check_domain( 'example.com' );
 * if ( $status['exists'] && $status['fresh'] ) {
 *     // Domain has fresh data
 * }
 * ```
 *
 * @param string $domain Domain name to check.
 * @return array Array with 'exists', 'fresh', 'modified'.
 */
function v_wp_seo_audit_check_domain( $domain ) {
	if ( empty( $domain ) ) {
		return array(
			'exists'  => false,
			'message' => __( 'Domain is required', 'v-wp-seo-audit' ),
		);
	}

	// Ensure classes are loaded.
	if ( ! class_exists( 'V_WP_SEO_Audit_Report' ) ) {
		return array(
			'exists'  => false,
			'message' => __( 'Report class not available', 'v-wp-seo-audit' ),
		);
	}

	$report = new V_WP_SEO_Audit_Report( $domain );
	return $report->check_website_status();
}

/**
 * Get website data for a domain.
 * Returns all database records for the domain.
 *
 * Usage:
 * ```php
 * $data = v_wp_seo_audit_get_website_data( 'example.com' );
 * if ( $data ) {
 *     $score = $data['website']['score'];
 *     $meta = $data['metatags'];
 * }
 * ```
 *
 * @param string $domain Domain name.
 * @return array|false Website data or false if not found.
 */
function v_wp_seo_audit_get_website_data( $domain ) {
	if ( empty( $domain ) ) {
		return false;
	}

	// Ensure classes are loaded.
	if ( ! class_exists( 'V_WP_SEO_Audit_Report' ) ) {
		return false;
	}

	$report = new V_WP_SEO_Audit_Report( $domain );
	return $report->get_website_data();
}

/**
 * Analyze a domain (or update existing analysis).
 * This triggers the full SEO audit process.
 *
 * Note: Domain analysis requires the ParseCommand functionality which
 * was removed in Phase 1. This function will fail until that is restored
 * or reimplemented. Use v_wp_seo_audit_check_domain() first to see if
 * cached data is available.
 *
 * Usage:
 * ```php
 * $result = v_wp_seo_audit_analyze_domain( 'example.com' );
 * if ( $result['success'] ) {
 *     // Analysis complete
 * } else {
 *     echo $result['error'];
 * }
 * ```
 *
 * @param string $domain Domain name to analyze.
 * @param array  $args Optional arguments (ip, idn, force_update).
 * @return array Array with 'success' and 'error' or 'data'.
 */
function v_wp_seo_audit_analyze_domain( $domain, $args = array() ) {
	if ( empty( $domain ) ) {
		return array(
			'success' => false,
			'error'   => __( 'Domain is required', 'v-wp-seo-audit' ),
		);
	}

	// Sanitize domain.
	$validation = v_wp_seo_audit_validate_domain( $domain );
	if ( ! $validation['valid'] ) {
		return array(
			'success' => false,
			'error'   => implode( '<br>', $validation['errors'] ),
		);
	}

	$domain = $validation['domain'];

	// Get optional arguments.
	$idn = isset( $args['idn'] ) ? $args['idn'] : $domain;
	$ip  = isset( $args['ip'] ) ? $args['ip'] : gethostbyname( $domain );

	// Check if domain exists and needs update.
	if ( ! class_exists( 'V_WP_SEO_Audit_DB' ) ) {
		return array(
			'success' => false,
			'error'   => __( 'Database class not available', 'v-wp-seo-audit' ),
		);
	}

	$db      = new V_WP_SEO_Audit_DB();
	$website = $db->get_website_by_domain( $domain, array( 'id', 'modified' ) );

	$force_update = isset( $args['force_update'] ) && $args['force_update'];

	// Determine if update is needed.
	$needs_update = false;
	$website_id   = null;

	if ( $website ) {
		$website_id   = $website['id'];
		$modified     = strtotime( $website['modified'] );
		$cache_time   = 60 * 60 * 24; // 24 hours.
		$needs_update = $force_update || ( $modified + $cache_time < time() );
	}

	// If website exists and doesn't need update, return success.
	if ( $website && ! $needs_update ) {
		return array(
			'success' => true,
			'data'    => array(
				'message' => __( 'Domain data is up to date', 'v-wp-seo-audit' ),
				'cached'  => true,
			),
		);
	}

	// Create analyzer instance.
	if ( ! class_exists( 'V_WP_SEO_Audit_Analyzer' ) ) {
		return array(
			'success' => false,
			'error'   => __( 'Analyzer class not available', 'v-wp-seo-audit' ),
		);
	}

	if ( $website_id ) {
		$analyzer = V_WP_SEO_Audit_Analyzer::for_update( $domain, $idn, $ip, $website_id );
	} else {
		$analyzer = V_WP_SEO_Audit_Analyzer::for_insert( $domain, $idn, $ip );
	}

	// Run analysis.
	if ( $analyzer->analyze() ) {
		return array(
			'success' => true,
			'data'    => array(
				'message' => __( 'Domain analysis complete', 'v-wp-seo-audit' ),
				'cached'  => false,
			),
		);
	} else {
		return array(
			'success' => false,
			'error'   => implode( '<br>', $analyzer->get_errors() ),
		);
	}
}

/**
 * Delete domain data from database.
 * Removes all records for a domain.
 *
 * Usage:
 * ```php
 * if ( v_wp_seo_audit_delete_domain( 'example.com' ) ) {
 *     // Domain data deleted
 * }
 * ```
 *
 * @param string $domain Domain name.
 * @return bool True on success, false on failure.
 */
function v_wp_seo_audit_delete_domain( $domain ) {
	if ( empty( $domain ) ) {
		return false;
	}

	// Use the Website model's static method.
	if ( ! class_exists( 'Website' ) ) {
		return false;
	}

	return Website::removeByDomain( $domain );
}

/**
 * Get plugin version.
 *
 * @return string Plugin version.
 */
function v_wp_seo_audit_get_version() {
	return V_WP_SEO_AUDIT_VERSION;
}

/**
 * Check if Yii framework is initialized.
 *
 * @return bool True if Yii is available, false otherwise.
 */
function v_wp_seo_audit_is_yii_available() {
	global $v_wp_seo_audit_app;
	return null !== $v_wp_seo_audit_app;
}
