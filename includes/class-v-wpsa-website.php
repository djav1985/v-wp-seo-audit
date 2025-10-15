<?php
/**
 * Website Model Class
 * WordPress-native version of Website model from protected/models/Website.php
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class V_WPSA_Website
 *
 * Handles website-related database operations.
 */
class V_WPSA_Website {

	/**
	 * Get table name for websites.
	 *
	 * @return string Table name with prefix.
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ca_website';
	}

	/**
	 * Get total count of websites.
	 *
	 * @return int Total website count.
	 */
	public static function get_total() {
		global $wpdb;
		$table = self::get_table_name();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$count = $wpdb->get_var( 'SELECT COUNT(*) FROM ' . esc_sql( $table ) );
		return (int) $count;
	}

	/**
	 * Remove website by domain using WordPress native database.
	 *
	 * @param string $domain Domain name.
	 * @return bool True on success, false on failure.
	 */
	public static function remove_by_domain( $domain ) {
		// Use WordPress native database class.
		if ( ! class_exists( 'V_WPSA_DB' ) ) {
			return false;
		}

		// Use IDN class for domain encoding if available.
		if ( class_exists( 'IDN' ) ) {
			$idn    = new IDN();
			$domain = $idn->encode( $domain );
		}

		$db = new V_WPSA_DB();

		// Get website by domain.
		$website = $db->get_website_by_domain( $domain );
		if ( ! $website ) {
			return false;
		}

		$website_id = $website['id'];

		// Delete website and all related records.
		return $db->delete_website( $website_id );
	}
}
