<?php
/**
 * Uninstall script for V-WP-SEO-Audit plugin
 *
 * This file is executed when the plugin is uninstalled from WordPress.
 * It removes all database tables and options created by the plugin.
 *
 * @package V_WP_SEO_Audit
 */

// Exit if uninstall not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;
// Get the table prefix.
// phpcs:disable WordPress.WP.GlobalVariablesOverride.Prohibited
$table_prefix = $wpdb->prefix . 'ca_';
// phpcs:enable WordPress.WP.GlobalVariablesOverride.Prohibited
// List of tables to drop.
$tables = array(
	$table_prefix . 'cloud',
	$table_prefix . 'content',
	$table_prefix . 'document',
	$table_prefix . 'issetobject',
	$table_prefix . 'links',
	$table_prefix . 'metatags',
	$table_prefix . 'misc',
	$table_prefix . 'pagespeed',
	$table_prefix . 'w3c',
	$table_prefix . 'website',
);
// Drop all tables.
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS `{$table}`" );
}
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

// Delete plugin options.
delete_option( 'v_wpsa_version' );
// For multisite installations, delete options from all sites.
if ( is_multisite() ) {
	$sites = get_sites();
	foreach ( $sites as $site ) {
		switch_to_blog( $site->blog_id );
		delete_option( 'v_wpsa_version' );
		restore_current_blog();

	}
}
