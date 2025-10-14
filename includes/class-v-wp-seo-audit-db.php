<?php
/**
 * Database operations class for V-WP-SEO-Audit plugin.
 * Provides WordPress-native database access methods to replace Yii's CActiveRecord and CDbCommand.
 *
 * @package V_WP_SEO_Audit
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class V_WP_SEO_Audit_DB
 *
 * WordPress-native database wrapper to replace Yii database operations.
 * Maintains the same table structure and schema while using $wpdb.
 */
class V_WP_SEO_Audit_DB {

	/**
	 * WordPress database object.
	 *
	 * @var wpdb
	 */
	protected $wpdb;

	/**
	 * Table prefix for plugin tables.
	 *
	 * @var string
	 */
	protected $table_prefix;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->wpdb         = $wpdb;
		$this->table_prefix = $wpdb->prefix . 'ca_';
	}

	/**
	 * Get full table name with prefix.
	 *
	 * @param string $table_name Table name without prefix.
	 * @return string Full table name with prefix.
	 */
	public function get_table_name( $table_name ) {
		return $this->table_prefix . $table_name;
	}

	/**
	 * Get a single row from a table by website ID.
	 *
	 * @param string $table Table name (without prefix).
	 * @param int    $wid Website ID.
	 * @return array|null Row data or null if not found.
	 */
	public function get_by_wid( $table, $wid ) {
		$table_name = $this->get_table_name( $table );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$table_name} WHERE wid = %d",
				$wid
			),
			ARRAY_A
		);
	}

	/**
	 * Get website by MD5 domain hash.
	 *
	 * @param string $md5_domain MD5 hash of the domain.
	 * @param array  $fields Optional. Fields to select. Default all fields.
	 * @return array|null Website data or null if not found.
	 */
	public function get_website_by_md5( $md5_domain, $fields = array( '*' ) ) {
		$table_name = $this->get_table_name( 'website' );
		$select     = is_array( $fields ) ? implode( ', ', array_map( 'esc_sql', $fields ) ) : '*';
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $this->wpdb->get_row(
			$this->wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT {$select} FROM {$table_name} WHERE md5domain = %s",
				$md5_domain
			),
			ARRAY_A
		);
	}

	/**
	 * Get website by domain.
	 *
	 * @param string $domain Domain name.
	 * @param array  $fields Optional. Fields to select. Default all fields.
	 * @return array|null Website data or null if not found.
	 */
	public function get_website_by_domain( $domain, $fields = array( '*' ) ) {
		return $this->get_website_by_md5( md5( $domain ), $fields );
	}

	/**
	 * Delete website and all related records.
	 *
	 * @param int $website_id Website ID.
	 * @return bool True on success, false on failure.
	 */
	public function delete_website( $website_id ) {
		$tables = array(
			'website',
			'w3c',
			'pagespeed',
			'misc',
			'metatags',
			'links',
			'issetobject',
			'document',
			'content',
			'cloud',
		);

		foreach ( $tables as $table ) {
			$table_name = $this->get_table_name( $table );
			$where      = ( 'website' === $table ) ? array( 'id' => $website_id ) : array( 'wid' => $website_id );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result = $this->wpdb->delete( $table_name, $where, array( '%d' ) );
			if ( false === $result ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Insert or update pagespeed data.
	 *
	 * @param int    $wid Website ID.
	 * @param string $data JSON data.
	 * @param string $lang_id Language ID.
	 * @return bool True on success, false on failure.
	 */
	public function upsert_pagespeed( $wid, $data, $lang_id ) {
		$table_name = $this->get_table_name( 'pagespeed' );

		// Check if record exists.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$exists = $this->wpdb->get_var(
			$this->wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT COUNT(*) FROM {$table_name} WHERE wid = %d AND lang_id = %s",
				$wid,
				$lang_id
			)
		);

		if ( $exists ) {
			// Update existing record.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return false !== $this->wpdb->update(
				$table_name,
				array( 'data' => $data ),
				array(
					'wid'     => $wid,
					'lang_id' => $lang_id,
				),
				array( '%s' ),
				array( '%d', '%s' )
			);
		} else {
			// Insert new record.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return false !== $this->wpdb->insert(
				$table_name,
				array(
					'wid'     => $wid,
					'data'    => $data,
					'lang_id' => $lang_id,
				),
				array( '%d', '%s', '%s' )
			);
		}
	}

	/**
	 * Get pagespeed data.
	 *
	 * @param int    $wid Website ID.
	 * @param string $lang_id Language ID.
	 * @return string|null JSON data or null if not found.
	 */
	public function get_pagespeed_data( $wid, $lang_id ) {
		$table_name = $this->get_table_name( 'pagespeed' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $this->wpdb->get_var(
			$this->wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT data FROM {$table_name} WHERE wid = %d AND lang_id = %s",
				$wid,
				$lang_id
			)
		);
	}

	/**
	 * Get all data for website report.
	 * Returns all related table data for a given website ID.
	 *
	 * @param int $wid Website ID.
	 * @return array Array of table data indexed by table name.
	 */
	public function get_website_report_data( $wid ) {
		$data   = array();
		$tables = array( 'cloud', 'content', 'document', 'issetobject', 'links', 'metatags', 'w3c', 'misc' );

		foreach ( $tables as $table ) {
			$result         = $this->get_by_wid( $table, $wid );
			$data[ $table ] = $result ? $result : array();
		}

		return $data;
	}

	/**
	 * Get website count (for statistics).
	 *
	 * @return int Total number of websites.
	 */
	public function get_website_count() {
		$table_name = $this->get_table_name( 'website' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return (int) $this->wpdb->get_var(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT COUNT(*) FROM {$table_name}"
		);
	}

	/**
	 * Execute a custom query.
	 * Use sparingly - prefer specific methods when possible.
	 *
	 * @param string $query SQL query (must be properly prepared).
	 * @return mixed Query result.
	 */
	public function query( $query ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $this->wpdb->query( $query );
	}

	/**
	 * Get single value from database.
	 *
	 * @param string $table Table name (without prefix).
	 * @param string $column Column name.
	 * @param array  $where Where conditions as key-value pairs.
	 * @return mixed|null Single value or null if not found.
	 */
	public function get_var( $table, $column, $where = array() ) {
		$table_name = $this->get_table_name( $table );
		$column     = esc_sql( $column );

		if ( empty( $where ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			return $this->wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT {$column} FROM {$table_name} LIMIT 1"
			);
		}

		$where_clause = $this->build_where_clause( $where );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $this->wpdb->get_var(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT {$column} FROM {$table_name} WHERE {$where_clause}"
		);
	}

	/**
	 * Build WHERE clause from array of conditions.
	 *
	 * @param array $where Where conditions as key-value pairs.
	 * @return string WHERE clause (without WHERE keyword).
	 */
	protected function build_where_clause( $where ) {
		$conditions = array();
		foreach ( $where as $key => $value ) {
			$key = esc_sql( $key );
			if ( is_int( $value ) ) {
				$conditions[] = $key . ' = ' . intval( $value );
			} else {
				$conditions[] = $key . ' = ' . $this->wpdb->prepare( '%s', $value );
			}
		}
		return implode( ' AND ', $conditions );
	}
}
