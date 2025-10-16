<?php
/**
 * File: deactivation.php
 *
 * Description: Deactivation hooks for the plugin.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
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
