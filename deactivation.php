<?php
/**
 * Deactivation script for V-WP-SEO-Audit plugin
 *
 * This file contains the deactivation hook function.
 *
 * @package V_WP_SEO_Audit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

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
