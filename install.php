<?php
/**
 * Installation and activation script for v-wpsa plugin
 *
 * This file contains the activation, deactivation, and cleanup hooks.
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Activation hook - create database tables.
/**
 * V_wpsa_activate function.
 */
function v_wpsa_activate() {
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
        `images_missing_alt` mediumtext NOT NULL,
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
        `messages` mediumtext NOT NULL,
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
        `score_breakdown` longtext NULL,
        PRIMARY KEY (`id`),
        KEY `ix_md5domain` (`md5domain`),
        KEY `ix_rating` (`score`)
    ) ENGINE=InnoDB $charset_collate;";

	// Execute all SQL statements.
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	foreach ( $sql as $query ) {
		dbDelta( $query );
	}

	// Set plugin version option.
	add_option( 'v_wpsa_version', V_WPSA_VERSION );

	// Run database upgrades.
	v_wpsa_upgrade_database();

	// Schedule daily cleanup cron job.
	if ( ! wp_next_scheduled( 'v_wpsa_daily_cleanup' ) ) {
		wp_schedule_event( time(), 'daily', 'v_wpsa_daily_cleanup' );
	}
}

/**
 * Upgrade database schema.
 * Adds missing columns to existing tables.
 */
function v_wpsa_upgrade_database() {
	global $wpdb;

	$table_prefix    = $wpdb->prefix . 'ca_';
	$charset_collate = $wpdb->get_charset_collate();

	// Check if images_missing_alt column exists in ca_content table.
	$content_table = $table_prefix . 'content';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$column_exists = $wpdb->get_results( "SHOW COLUMNS FROM {$content_table} LIKE 'images_missing_alt'" );

	if ( empty( $column_exists ) ) {
		// Add images_missing_alt column.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( "ALTER TABLE {$content_table} ADD COLUMN `images_missing_alt` mediumtext NOT NULL" );
	}

        // Check if messages column exists in ca_w3c table.
        $w3c_table = $table_prefix . 'w3c';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$column_exists = $wpdb->get_results( "SHOW COLUMNS FROM {$w3c_table} LIKE 'messages'" );

	if ( empty( $column_exists ) ) {
		// Add messages column.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $wpdb->query( "ALTER TABLE {$w3c_table} ADD COLUMN `messages` mediumtext NOT NULL" );
        }

        // Ensure score_breakdown column exists on website table for score summaries.
        $website_table = $table_prefix . 'website';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $column_exists = $wpdb->get_results( "SHOW COLUMNS FROM {$website_table} LIKE 'score_breakdown'" );

        if ( empty( $column_exists ) ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $wpdb->query( "ALTER TABLE {$website_table} ADD COLUMN `score_breakdown` longtext NULL" );
        }
}

// WordPress Cron cleanup function.
/**
 * V_wpsa_cleanup function.
 *
 * Cleans up old PDF files, thumbnails, and database records.
 * Keeps only the last 12 reports and removes older ones.
 * Runs daily via WordPress cron.
 */
function v_wpsa_cleanup() {
	global $wpdb;

	// Get the maximum number of reports to keep (default 12).
	$max_reports = apply_filters( 'v_wpsa_max_reports', 12 );

	// Get total count of website records.
	$table_name = $wpdb->prefix . 'ca_website';
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$total_count = $wpdb->get_var(
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		"SELECT COUNT(*) FROM {$table_name}"
	);

	// If we have more than the maximum allowed reports, delete the oldest ones.
	if ( $total_count > $max_reports ) {
		// Get domains of old reports that should be deleted.
		// Keep the newest $max_reports and delete the rest.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$old_websites = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT domain, id FROM {$table_name} ORDER BY modified DESC LIMIT %d, 999999",
				$max_reports
			),
			ARRAY_A
		);

		if ( ! empty( $old_websites ) ) {
			// Get upload directory for PDFs and thumbnails.
			$upload_dir    = wp_upload_dir();
			$pdf_dir       = $upload_dir['basedir'] . '/seo-audit/pdf/';
			$thumbnail_dir = $upload_dir['basedir'] . '/seo-audit/thumbnails/';

			// Collect IDs to delete.
			$ids_to_delete = array();

			foreach ( $old_websites as $website ) {
				$domain          = $website['domain'];
				$ids_to_delete[] = $website['id'];

				// Clean up PDF files.
				// PDFs are stored in: seo-audit/pdf/{lang}/{first_letter}/{domain}.pdf.
				$languages = array( 'en' ); // Default language support.

				// Also remove simplified PDF files located directly under seo-audit/pdf/.
				$simple_pdf_path = $pdf_dir . $domain . '.pdf';
				if ( file_exists( $simple_pdf_path ) ) {
					wp_delete_file( $simple_pdf_path );
				}
				$simple_pdf_path_ps = $pdf_dir . $domain . '_pagespeed.pdf';
				if ( file_exists( $simple_pdf_path_ps ) ) {
					wp_delete_file( $simple_pdf_path_ps );
				}

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

			// Delete old database records by IDs.
			if ( ! empty( $ids_to_delete ) ) {
				$ids_placeholder = implode( ',', array_fill( 0, count( $ids_to_delete ), '%d' ) );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query(
					$wpdb->prepare(
						// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
						"DELETE FROM {$table_name} WHERE id IN ({$ids_placeholder})",
						$ids_to_delete
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
	}
}
