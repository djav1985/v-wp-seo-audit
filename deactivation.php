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
 * V_wpsa_deactivate function.
 */
function v_wpsa_deactivate() {
	// Clear scheduled cron job.
	$timestamp = wp_next_scheduled( 'v_wpsa_daily_cleanup' );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, 'v_wpsa_daily_cleanup' );
	}
}
