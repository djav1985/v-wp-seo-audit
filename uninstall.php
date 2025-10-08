<?php
/**
 * Uninstall script for V-WP-SEO-Audit plugin
 * 
 * This file is executed when the plugin is uninstalled from WordPress.
 * It removes all database tables and options created by the plugin.
 */

// Exit if uninstall not called from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Get the table prefix
$table_prefix = $wpdb->prefix . 'ca_';

// List of tables to drop
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

// Drop all tables
foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS `{$table}`");
}

// Delete plugin options
delete_option('v_wp_seo_audit_version');

// For multisite installations, delete options from all sites
if (is_multisite()) {
    $sites = get_sites();
    foreach ($sites as $site) {
        switch_to_blog($site->blog_id);
        delete_option('v_wp_seo_audit_version');
        restore_current_blog();
    }
}
