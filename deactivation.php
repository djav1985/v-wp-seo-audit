<?php
/**
 * Deactivation script for v-wpsa plugin
 *
 * This file contains the deactivation hook function.
 *
 * @package v_wpsa
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
