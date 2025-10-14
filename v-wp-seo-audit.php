<?php
/**
 * File: v-wp-seo-audit.php
 *
 * @package V_WP_SEO_Audit
 */

/*
Plugin Name: V-WP-SEO-Audit
Description: WordPress SEO Audit plugin - Analyze your website's SEO performance
Version: 1.0.0
Author: djav1985
License: GPL2
Text Domain: v-wp-seo-audit
*/

if ( ! defined( 'ABSPATH' )) {
	exit; // Exit if accessed directly.
}

// Define plugin constants.
define( 'V_WP_SEO_AUDIT_VERSION', '1.0.0' );
define( 'V_WP_SEO_AUDIT_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'V_WP_SEO_AUDIT_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load organized includes files.
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-v-wp-seo-audit-db.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-yii-integration.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-validation.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-helpers.php';
require_once V_WP_SEO_AUDIT_PLUGIN_DIR . 'includes/class-ajax-handlers.php';

// Initialize Yii framework.
// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_error_reporting, WordPress.PHP.DevelopmentFunctions.error_log_error_reporting, WordPress.Security.PluginMenuSlug.Using error_reporting
error_reporting( E_ALL & ~( E_NOTICE | E_DEPRECATED | E_STRICT ) );

// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
if ( ! @ini_get( 'date.timezone' )) {
	// phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set
	date_default_timezone_set( 'UTC' );
}

mb_internal_encoding( 'UTF-8' );

// Global variable to store Yii application instance.
global $v_wp_seo_audit_app;
$v_wp_seo_audit_app = null;

/**
 * V_wp_seo_audit_configure_yii_app function.
 *
 * Wrapper function for backward compatibility.
 *
 * @param mixed $app Parameter.
 */
function v_wp_seo_audit_configure_yii_app( $app ) {
	V_WP_SEO_Audit_Yii_Integration::configure_yii_app( $app );
}

// Plugin initialization - only when needed (not on every page load).
/**
 * V_wp_seo_audit_init function.
 *
 * Wrapper function for backward compatibility.
 */
function v_wp_seo_audit_init() {
	V_WP_SEO_Audit_Yii_Integration::init();
}
add_action( 'wp', 'v_wp_seo_audit_init' ); // Use 'wp' instead of 'init' to have access to $post.

// Enqueue styles and scripts for front-end.
/**
 * V_wp_seo_audit_enqueue_assets function.
 */
function v_wp_seo_audit_enqueue_assets() {
	global $post, $v_wp_seo_audit_app;

	// Only load if shortcode is present on the page.
	if (is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'v_wp_seo_audit' )) {
		// Enqueue CSS files.
		wp_enqueue_style( 'v-wp-seo-audit-bootstrap', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/css/bootstrap.min.css', array(), V_WP_SEO_AUDIT_VERSION );
		wp_enqueue_style( 'v-wp-seo-audit-fontawesome', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/css/fontawesome.min.css', array(), V_WP_SEO_AUDIT_VERSION );
		wp_enqueue_style( 'v-wp-seo-audit-app', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/css/app.css', array( 'v-wp-seo-audit-bootstrap' ), V_WP_SEO_AUDIT_VERSION );

		// Enqueue JS files.
		wp_enqueue_script( 'jquery' ); // Use WordPress jQuery.
		wp_enqueue_script( 'v-wp-seo-audit-bootstrap', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/js/bootstrap.bundle.min.js', array( 'jquery' ), V_WP_SEO_AUDIT_VERSION, true );
		wp_enqueue_script( 'v-wp-seo-audit-flot', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/js/jquery.flot.js', array( 'jquery' ), V_WP_SEO_AUDIT_VERSION, true );
		wp_enqueue_script( 'v-wp-seo-audit-flot-pie', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/js/jquery.flot.pie.js', array( 'jquery', 'v-wp-seo-audit-flot' ), V_WP_SEO_AUDIT_VERSION, true );
		wp_enqueue_script( 'v-wp-seo-audit-base', V_WP_SEO_AUDIT_PLUGIN_URL . 'assets/js/base.js', array( 'jquery' ), V_WP_SEO_AUDIT_VERSION, true );

		// Add global JavaScript variables needed by the plugin.
		// Get the base URL from Yii app if initialized, otherwise use plugin URL.
		$base_url = rtrim( V_WP_SEO_AUDIT_PLUGIN_URL, '/' );

		// Inject global variables into the page.
		$global_vars = "var _global = { 
            baseUrl: '" . esc_js( $base_url ) . "',
            ajaxUrl: '" . esc_js( admin_url( 'admin-ajax.php' ) ) . "',
            nonce: '" . wp_create_nonce( 'v_wp_seo_audit_nonce' ) . "'
        };";
		wp_add_inline_script( 'v-wp-seo-audit-base', $global_vars, 'before' );
	}
}
add_action( 'wp_enqueue_scripts', 'v_wp_seo_audit_enqueue_assets' );

// Register shortcode.
/**
 * V_wp_seo_audit_shortcode function.
 *
 * @param mixed $atts Parameter.
 */
function v_wp_seo_audit_shortcode( $atts) {
	global $v_wp_seo_audit_app;

	if ( ! $v_wp_seo_audit_app) {
		return '<div class="v-wp-seo-audit-error"><p>Error: Application not initialized.</p></div>';
	}

	// Start output buffering to capture Yii output.
	ob_start();

	try {
		// Process the request through Yii.
		$v_wp_seo_audit_app->run();
		$content = ob_get_clean();

		// Create a fresh nonce for the container to support PDF downloads.
		$nonce = wp_create_nonce( 'v_wp_seo_audit_nonce' );

		// Wrap in container with data-nonce attribute.
		return '<div class="v-wp-seo-audit-container" data-nonce="' . esc_attr( $nonce ) . '">' . $content . '</div>';
	} catch (Exception $e) {
		ob_end_clean();
		return '<div class="v-wp-seo-audit-error"><p>Error: ' . esc_html( $e->getMessage() ) . '</p></div>';
	}
}
add_shortcode( 'v_wp_seo_audit', 'v_wp_seo_audit_shortcode' );

// Activation hook - create database tables.
/**
 * V_wp_seo_audit_activate function.
 */
function v_wp_seo_audit_activate() {
	global $wpdb;

	// Get the table prefix from WordPress.
	$table_prefix = $wpdb->prefix . 'ca_';

	// Set charset.
	$charset_collate = $wpdb->get_charset_collate();

	// SQL statements to create tables.
	$sql = array();

	// ca_cloud table.
	$sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}cloud` (
        `wid` int unsigned NOT NULL,
        `words` mediumtext NOT NULL,
        `matrix` mediumtext NOT NULL,
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";

	// ca_content table.
	$sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}content` (
        `wid` int unsigned NOT NULL,
        `headings` mediumtext NOT NULL,
        `total_img` int unsigned NOT NULL DEFAULT '0',
        `total_alt` int unsigned NOT NULL DEFAULT '0',
        `deprecated` mediumtext NOT NULL,
        `isset_headings` tinyint NOT NULL DEFAULT '0',
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";

	// ca_document table.
	$sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}document` (
        `wid` int unsigned NOT NULL,
        `doctype` text,
        `lang` varchar(255) DEFAULT NULL,
        `charset` varchar(255) DEFAULT NULL,
        `css` int unsigned NOT NULL DEFAULT '0',
        `js` int unsigned NOT NULL DEFAULT '0',
        `htmlratio` int unsigned NOT NULL DEFAULT '0',
        `favicon` text,
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";

	// ca_issetobject table.
	$sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}issetobject` (
        `wid` int unsigned NOT NULL,
        `flash` tinyint(1) DEFAULT '0',
        `iframe` tinyint(1) DEFAULT '0',
        `nestedtables` tinyint(1) DEFAULT '0',
        `inlinecss` tinyint(1) DEFAULT '0',
        `email` tinyint(1) DEFAULT '0',
        `viewport` tinyint(1) DEFAULT '0',
        `dublincore` tinyint(1) DEFAULT '0',
        `printable` tinyint(1) DEFAULT '0',
        `appleicons` tinyint(1) DEFAULT '0',
        `robotstxt` tinyint(1) DEFAULT '0',
        `gzip` tinyint(1) DEFAULT '0',
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";

	// ca_links table.
	$sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}links` (
        `wid` int unsigned NOT NULL,
        `links` mediumtext NOT NULL,
        `internal` int unsigned NOT NULL DEFAULT '0',
        `external_dofollow` int unsigned NOT NULL DEFAULT '0',
        `external_nofollow` int unsigned NOT NULL DEFAULT '0',
        `isset_underscore` tinyint(1) NOT NULL,
        `files_count` int unsigned NOT NULL DEFAULT '0',
        `friendly` tinyint(1) NOT NULL,
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";

	// ca_metatags table.
	$sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}metatags` (
        `wid` int unsigned NOT NULL,
        `title` mediumtext,
        `keyword` mediumtext,
        `description` mediumtext,
        `ogproperties` mediumtext,
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";

	// ca_misc table.
	$sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}misc` (
        `wid` int unsigned NOT NULL,
        `sitemap` mediumtext NOT NULL,
        `analytics` mediumtext NOT NULL,
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";

	// ca_pagespeed table.
	$sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}pagespeed` (
        `wid` int unsigned NOT NULL,
        `data` longtext NOT NULL,
        `lang_id` varchar(5) NOT NULL,
        PRIMARY KEY (`wid`,`lang_id`)
    ) ENGINE=InnoDB $charset_collate;";

	// ca_w3c table.
	$sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}w3c` (
        `wid` int unsigned NOT NULL,
        `validator` enum('html') NOT NULL,
        `valid` tinyint(1) NOT NULL DEFAULT '1',
        `errors` smallint unsigned NOT NULL DEFAULT '0',
        `warnings` smallint unsigned NOT NULL DEFAULT '0',
        PRIMARY KEY (`wid`)
    ) ENGINE=InnoDB $charset_collate;";

	// ca_website table (main table).
	$sql[] = "CREATE TABLE IF NOT EXISTS `{$table_prefix}website` (
        `id` int unsigned NOT NULL AUTO_INCREMENT,
        `domain` varchar(255) DEFAULT NULL,
        `idn` varchar(255) DEFAULT NULL,
        `final_url` mediumtext,
        `md5domain` varchar(32) DEFAULT NULL,
        `added` timestamp NULL DEFAULT NULL,
        `modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
        `score` tinyint unsigned DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `ix_md5domain` (`md5domain`),
        KEY `ix_rating` (`score`)
    ) ENGINE=InnoDB $charset_collate;";

	// Execute all SQL statements.
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	foreach ($sql as $query) {
		dbDelta( $query );
	}

	// Set plugin version option.
	add_option( 'v_wp_seo_audit_version', V_WP_SEO_AUDIT_VERSION );

	// Schedule daily cleanup cron job.
	if ( ! wp_next_scheduled( 'v_wp_seo_audit_daily_cleanup' )) {
		wp_schedule_event( time(), 'daily', 'v_wp_seo_audit_daily_cleanup' );
	}
}
register_activation_hook( __FILE__, 'v_wp_seo_audit_activate' );

// Deactivation hook (optional - for cleanup on deactivation).
/**
 * V_wp_seo_audit_deactivate function.
 */
function v_wp_seo_audit_deactivate() {
	// Clear scheduled cron job.
	$timestamp = wp_next_scheduled( 'v_wp_seo_audit_daily_cleanup' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'v_wp_seo_audit_daily_cleanup' );
	}
}
register_deactivation_hook( __FILE__, 'v_wp_seo_audit_deactivate' );

// WordPress Cron cleanup function.
/**
 * V_wp_seo_audit_cleanup function.
 *
 * Cleans up old PDF files, thumbnails, and database records.
 * Runs daily via WordPress cron.
 */
function v_wp_seo_audit_cleanup() {
	global $wpdb;

	// Get cache time from config (default 24 hours).
	$cache_time = 60 * 60 * 24; // 24 hours in seconds.

	// Calculate cutoff timestamp.
	$cutoff_date = gmdate( 'Y-m-d H:i:s', time() - $cache_time );

	// Get old website records from database.
	$table_name = $wpdb->prefix . 'ca_website';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$old_websites = $wpdb->get_results(
		$wpdb->prepare(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT domain FROM {$table_name} WHERE modified < %s",
			$cutoff_date
		),
		ARRAY_A
	);

	if ( ! empty( $old_websites )) {
		$plugin_dir = V_WP_SEO_AUDIT_PLUGIN_DIR;
		$pdf_dir    = $plugin_dir . 'pdf/';

		// Get upload directory for thumbnails.
		$upload_dir    = wp_upload_dir();
		$thumbnail_dir = $upload_dir['basedir'] . '/seo-audit/thumbnails/';

		foreach ( $old_websites as $website ) {
			$domain = $website['domain'];

			// Clean up PDF files.
			// PDFs are stored in: pdf/{lang}/{first_letter}/{domain}.pdf.
			$languages = array( 'en' ); // Default language support.

			foreach ( $languages as $lang ) {
				$first_letter = mb_substr( $domain, 0, 1 );
				$pdf_path     = $pdf_dir . $lang . '/' . $first_letter . '/' . $domain . '.pdf';

				if ( file_exists( $pdf_path ) ) {
					wp_delete_file( $pdf_path );
				}

				// Also check for pagespeed PDF.
				$pdf_path_ps = $pdf_dir . $lang . '/' . $first_letter . '/' . $domain . '_pagespeed.pdf';
				if ( file_exists( $pdf_path_ps ) ) {
					wp_delete_file( $pdf_path_ps );
				}
			}

			// Clean up thumbnails.
			$thumbnail_path = $thumbnail_dir . md5( $domain ) . '.jpg';
			if ( file_exists( $thumbnail_path ) ) {
				wp_delete_file( $thumbnail_path );
			}
		}

		// Delete old database records.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE FROM {$table_name} WHERE modified < %s",
				$cutoff_date
			)
		);

		// Clean up orphaned records in related tables.
		$related_tables = array( 'cloud', 'content', 'document', 'issetobject', 'links', 'metatags', 'misc', 'pagespeed', 'w3c' );
		foreach ( $related_tables as $table ) {
			$related_table_name = $wpdb->prefix . 'ca_' . $table;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				"DELETE FROM {$related_table_name} WHERE wid NOT IN (SELECT id FROM {$table_name})"
			);
		}
	}
}
add_action( 'v_wp_seo_audit_daily_cleanup', 'v_wp_seo_audit_cleanup' );

// Uninstall hook - remove plugin database tables.
/**
 * V_wp_seo_audit_uninstall function.
 */
function v_wp_seo_audit_uninstall() {
	global $wpdb;
	$table_prefix = $wpdb->prefix . 'ca_';
	$tables       = array(
		'cloud',
		'content',
		'document',
		'issetobject',
		'links',
		'metatags',
		'misc',
		'pagespeed',
		'w3c',
		'website',
	);
	foreach ($tables as $table) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
		$wpdb->query( "DROP TABLE IF EXISTS `{$table_prefix}{$table}`;" );
	}
	delete_option( 'v_wp_seo_audit_version' );
}
register_uninstall_hook( __FILE__, 'v_wp_seo_audit_uninstall' );

// WordPress-native domain validation functions.
/**
 * V_wp_seo_audit_validate_domain function.
 *
 * Wrapper function for backward compatibility.
 *
 * @param string $domain The domain to validate.
 * @return array Array with 'valid' boolean, 'domain', 'idn', 'ip', and 'errors' array.
 */
function v_wp_seo_audit_validate_domain( $domain ) {
	return V_WP_SEO_Audit_Validation::validate_domain( $domain );
}

/**
 * Sanitize domain input.
 *
 * Wrapper function for backward compatibility.
 *
 * @param string $domain The domain to sanitize.
 * @return string Sanitized domain.
 */
function v_wp_seo_audit_sanitize_domain( $domain ) {
	return V_WP_SEO_Audit_Validation::sanitize_domain( $domain );
}

/**
 * Encode IDN domain to punycode.
 *
 * Wrapper function for backward compatibility.
 *
 * @param string $domain The domain to encode.
 * @return string Punycode-encoded domain.
 */
function v_wp_seo_audit_encode_idn( $domain ) {
	return V_WP_SEO_Audit_Validation::encode_idn( $domain );
}

/**
 * Validate domain format.
 *
 * Wrapper function for backward compatibility.
 *
 * @param string $domain The domain to validate.
 * @return bool True if valid, false otherwise.
 */
function v_wp_seo_audit_is_valid_domain_format( $domain ) {
	return V_WP_SEO_Audit_Validation::is_valid_domain_format( $domain );
}

/**
 * Check if domain is banned.
 *
 * Wrapper function for backward compatibility.
 *
 * @param string $domain The domain to check.
 * @return string|false Error message if banned, false otherwise.
 */
function v_wp_seo_audit_check_banned_domain( $domain ) {
	return V_WP_SEO_Audit_Validation::check_banned_domain( $domain );
}

// Initialize AJAX handlers.
V_WP_SEO_Audit_Ajax_Handlers::init();

/**
 * WordPress-native function to delete PDF files for a domain.
 * Replaces Utils::deletePdf() with WordPress-native implementation.
 *
 * Wrapper function for backward compatibility.
 *
 * @param string $domain The domain name.
 * @return bool True on success.
 */
function v_wp_seo_audit_delete_pdf( $domain ) {
	return V_WP_SEO_Audit_Helpers::delete_pdf( $domain );
}

/**
 * WordPress-native function to get config file value.
 * Replaces Utils::getLocalConfigIfExists() with WordPress-native implementation.
 *
 * @param string $config_name The config file name (without extension).
 * @return mixed The config value or empty array on failure.
 */
function v_wp_seo_audit_get_config( $config_name ) {
	$config_dir   = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/';
	$config_local = $config_dir . $config_name . '_local.php';
	$config_prod  = $config_dir . $config_name . '.php';

	if ( file_exists( $config_local ) ) {
		return require $config_local;
	} elseif ( file_exists( $config_prod ) ) {
		return require $config_prod;
	}

	return array();
}

/**
 * WordPress-native website analysis function.
 * Replaces the removed CLI commands with inline analysis.
 *
 * @param string   $domain The domain to analyze (ASCII/punycode).
 * @param string   $idn The internationalized domain name (Unicode).
 * @param string   $ip The IP address of the domain.
 * @param int|null $wid Optional. Existing website ID for updates.
 * @return int|WP_Error Website ID on success, WP_Error on failure.
 */
function v_wp_seo_audit_analyze_website( $domain, $idn, $ip, $wid = null ) {
	global $wpdb;

	// Load required Yii vendor classes.
	// Note: We must load files directly before any class_exists() checks to avoid
	// triggering Yii's autoloader which will try to find the class in the wrong path.
	$helper_path = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/vendors/Webmaster/Utils/Helper.php';
	if ( file_exists( $helper_path ) ) {
		require_once $helper_path;
	}

	try {
		// Fetch website HTML.
		$url      = 'http://' . $domain;
		$response = wp_remote_get(
			$url,
			array(
				'timeout'     => 30,
				'user-agent'  => 'Mozilla/5.0 (compatible; V-WP-SEO-Audit/1.0; +http://yoursite.com)',
				'sslverify'   => false,
				'redirection' => 5,
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'fetch_failed', 'Could not fetch website: ' . $response->get_error_message() );
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return new WP_Error( 'fetch_failed', 'Website returned HTTP ' . $response_code );
		}

		$html = wp_remote_retrieve_body( $response );
		if ( empty( $html ) ) {
			return new WP_Error( 'empty_response', 'Website returned empty content' );
		}

		// Load analysis classes.
		$source_path     = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/vendors/Webmaster/Source/';
		$classes_to_load = array(
			'Content.php',
			'Document.php',
			'Links.php',
			'MetaTags.php',
			'Optimization.php',
			'SeoAnalyse.php',
			'Validation.php',
		);

		foreach ( $classes_to_load as $class_file ) {
			$class_path = $source_path . $class_file;
			if ( file_exists( $class_path ) ) {
				require_once $class_path;
			}
		}

		// Perform analysis.
		$table_prefix = $wpdb->prefix . 'ca_';
		$now          = current_time( 'mysql' );

		// Create or update website record.
		if ( $wid ) {
			// Update existing website.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$table_prefix . 'website',
				array(
					'domain'   => $domain,
					'idn'      => $idn,
					'ip'       => $ip,
					'modified' => $now,
					'score'    => 0, // Will be calculated later.
				),
				array( 'id' => $wid ),
				array( '%s', '%s', '%s', '%s', '%d' ),
				array( '%d' )
			);
		} else {
			// Insert new website.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->insert(
				$table_prefix . 'website',
				array(
					'domain'    => $domain,
					'idn'       => $idn,
					'ip'        => $ip,
					'md5domain' => md5( $domain ),
					'modified'  => $now,
					'score'     => 0,
				),
				array( '%s', '%s', '%s', '%s', '%s', '%d' )
			);
			$wid = $wpdb->insert_id;
		}

		if ( ! $wid ) {
			return new WP_Error( 'db_error', 'Failed to create website record' );
		}

		// Analyze content if classes are available.
		if ( class_exists( 'Content' ) ) {
			$content_analyzer = new Content( $html );
			$content_data     = array(
				'wid' => $wid,
			);

			// Check if record exists.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_prefix}content WHERE wid = %d", $wid ) );
			if ( $exists ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->update( $table_prefix . 'content', $content_data, array( 'wid' => $wid ) );
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->insert( $table_prefix . 'content', $content_data );
			}
		}

		// Analyze document structure.
		if ( class_exists( 'Document' ) ) {
			$doc_analyzer = new Document( $html );
			$doc_data     = array(
				'wid'     => $wid,
				'doctype' => method_exists( $doc_analyzer, 'getDoctype' ) ? substr( (string) $doc_analyzer->getDoctype(), 0, 255 ) : '',
			);

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_prefix}document WHERE wid = %d", $wid ) );
			if ( $exists ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->update( $table_prefix . 'document', $doc_data, array( 'wid' => $wid ) );
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->insert( $table_prefix . 'document', $doc_data );
			}
		}

		// Analyze links - Pass $idn as third parameter.
		if ( class_exists( 'Links' ) ) {
			$links_analyzer = new Links( $html, $domain, $idn );
			$links_data     = array(
				'wid'      => $wid,
				'internal' => method_exists( $links_analyzer, 'getInternalCount' ) ? $links_analyzer->getInternalCount() : 0,
				'external' => method_exists( $links_analyzer, 'getExternalDofollowCount' ) ? $links_analyzer->getExternalDofollowCount() : 0,
			);

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_prefix}links WHERE wid = %d", $wid ) );
			if ( $exists ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->update( $table_prefix . 'links', $links_data, array( 'wid' => $wid ) );
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->insert( $table_prefix . 'links', $links_data );
			}
		}

		// Analyze meta tags.
		if ( class_exists( 'MetaTags' ) ) {
			$meta_analyzer = new MetaTags( $html );
			$meta_data     = array(
				'wid'         => $wid,
				'title'       => method_exists( $meta_analyzer, 'getTitle' ) ? substr( $meta_analyzer->getTitle(), 0, 255 ) : '',
				'description' => method_exists( $meta_analyzer, 'getDescription' ) ? substr( $meta_analyzer->getDescription(), 0, 500 ) : '',
			);

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_prefix}metatags WHERE wid = %d", $wid ) );
			if ( $exists ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->update( $table_prefix . 'metatags', $meta_data, array( 'wid' => $wid ) );
			} else {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->insert( $table_prefix . 'metatags', $meta_data );
			}
		}

		// Store misc data.
		$misc_data = array(
			'wid'      => $wid,
			'loadtime' => 0, // Could be calculated from response time.
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_prefix}misc WHERE wid = %d", $wid ) );
		if ( $exists ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update( $table_prefix . 'misc', $misc_data, array( 'wid' => $wid ) );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->insert( $table_prefix . 'misc', $misc_data );
		}

		return $wid;

	} catch ( Exception $e ) {
		return new WP_Error( 'analysis_error', 'Analysis failed: ' . $e->getMessage() );
	}
}

