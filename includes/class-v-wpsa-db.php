<?php
/**
 * Database operations class for v-wpsa plugin.
 * Provides WordPress-native database access methods to replace Yii's CActiveRecord and CDbCommand.
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class V_WPSA_DB
 *
 * WordPress-native database wrapper to replace Yii database operations.
 * Maintains the same table structure and schema while using $wpdb.
 */
class V_WPSA_DB {

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
	public static function analyze_website( $domain, $idn, $ip, $wid = null ) {
		global $wpdb;

		// Load required Yii vendor classes.
		// Note: We must load files directly before any class_exists() checks to avoid
		// triggering Yii's autoloader which will try to find the class in the wrong path.
		$helper_path = v_wpsa_PLUGIN_DIR . 'protected/vendors/Webmaster/Utils/Helper.php';
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
					'user-agent'  => 'Mozilla/5.0 (compatible; v-wpsa/1.0; +http://yoursite.com)',
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
			$source_path     = v_wpsa_PLUGIN_DIR . 'protected/vendors/Webmaster/Source/';
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
			// Use false parameter to prevent autoloader from triggering.
			if ( class_exists( 'Content', false ) ) {
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
			// Use false parameter to prevent autoloader from triggering.
			if ( class_exists( 'Document', false ) ) {
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
			// Use false parameter to prevent autoloader from triggering.
			if ( class_exists( 'Links', false ) ) {
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
			// Use false parameter to prevent autoloader from triggering.
			if ( class_exists( 'MetaTags', false ) ) {
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

	/**
	 * Get websites with pagination and ordering.
	 *
	 * @param array $args Query arguments.
	 *                    - 'order' (string): ORDER BY clause.
	 *                    - 'limit' (int): Number of results to return.
	 *                    - 'offset' (int): Offset for pagination.
	 *                    - 'columns' (array): Columns to select.
	 *
	 * @return array Array of website records.
	 */
	public function get_websites( $args = array() ) {
		$defaults = array(
			'order'   => 'added DESC',
			'limit'   => 10,
			'offset'  => 0,
			'columns' => array( '*' ),
		);

		$args = wp_parse_args( $args, $defaults );

		$table_name = $this->get_table_name( 'website' );
		$columns    = is_array( $args['columns'] ) ? implode( ', ', array_map( 'esc_sql', $args['columns'] ) ) : '*';
		$order      = esc_sql( $args['order'] );
		$limit      = absint( $args['limit'] );
		$offset     = absint( $args['offset'] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $this->wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			"SELECT {$columns} FROM {$table_name} ORDER BY {$order} LIMIT {$offset}, {$limit}",
			ARRAY_A
		);

		return $results ? $results : array();
	}

	/**
	 * Count total number of websites.
	 *
	 * @param array $where Optional where conditions.
	 *
	 * @return int Total count.
	 */
	public function count_websites( $where = array() ) {
		$table_name = $this->get_table_name( 'website' );

		if ( empty( $where ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$count = $this->wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT COUNT(*) FROM {$table_name}"
			);
		} else {
			$where_clause = $this->build_where_clause( $where );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$count = $this->wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}"
			);
		}

		return $count ? intval( $count ) : 0;
	}
}
