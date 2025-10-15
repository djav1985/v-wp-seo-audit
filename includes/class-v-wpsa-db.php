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
	 * Get table columns for a given table name (without prefix).
	 * Uses a static cache to avoid repeated DESCRIBE queries.
	 *
	 * @param string $table Table name without plugin prefix.
	 * @return array List of column names.
	 */
	public function get_table_columns( $table ) {
		static $cache = array();
		$key          = $table;
		if ( isset( $cache[ $key ] ) ) {
			return $cache[ $key ];
		}
		$table_name = $this->get_table_name( $table );
		$cols       = array();
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$results = $this->wpdb->get_results( "DESCRIBE {$table_name}", ARRAY_A );
		if ( $results ) {
			foreach ( $results as $row ) {
				$cols[] = $row['Field'];
			}
		}
		$cache[ $key ] = $cols;
		return $cols;
	}

	/**
	 * Filter an associative array to only keys that exist as columns in a given table.
	 *
	 * @param string $table Table name without prefix.
	 * @param array  $data Associative array to filter.
	 * @return array Filtered associative array.
	 */
	public function filter_columns( $table, array $data ) {
		$cols = $this->get_table_columns( $table );
		if ( empty( $cols ) ) {
			return $data; // no schema info - return as-is
		}
		return array_intersect_key( $data, array_flip( $cols ) );
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
			$data[ $table ] = $result ? $this->decode_json_fields( $table, $result ) : array();
		}

		return $data;
	}

	/**
	 * Decode JSON-encoded fields in a database row based on table name.
	 *
	 * @param string $table Table name (without prefix).
	 * @param array  $row Database row as associative array.
	 * @return array Row with JSON fields decoded to arrays.
	 */
	protected function decode_json_fields( $table, $row ) {
		if ( ! is_array( $row ) ) {
			return $row;
		}

		// Define which fields contain JSON data for each table.
		$json_fields = array(
			'cloud'    => array( 'words', 'matrix' ),
			'content'  => array( 'headings', 'deprecated' ),
			'links'    => array( 'links' ),
			'metatags' => array( 'ogproperties' ),
			'misc'     => array( 'sitemap', 'analytics' ),
		);

		// If this table has JSON fields, decode them.
		if ( isset( $json_fields[ $table ] ) ) {
			foreach ( $json_fields[ $table ] as $field ) {
				if ( isset( $row[ $field ] ) ) {
					$row[ $field ] = $this->decode_json_field( $row[ $field ] );
				}
			}
		}

		return $row;
	}

	/**
	 * Decode a single JSON field value.
	 *
	 * @param mixed $value The field value (may be JSON string, serialized data, or already decoded).
	 * @return array Decoded array or empty array if decoding fails.
	 */
	protected function decode_json_field( $value ) {
		// Already an array.
		if ( is_array( $value ) ) {
			return $value;
		}

		// Null or empty.
		if ( null === $value || '' === $value ) {
			return array();
		}

		// Try JSON decode.
		if ( is_string( $value ) ) {
			$trimmed = trim( $value );
			$decoded = json_decode( $trimmed, true );
			if ( JSON_ERROR_NONE === json_last_error() && ( is_array( $decoded ) || is_object( $decoded ) ) ) {
				return (array) $decoded;
			}

			// Try PHP serialized string.
			if ( function_exists( 'maybe_unserialize' ) && maybe_unserialize( $trimmed ) !== $trimmed ) {
				$maybe = maybe_unserialize( $trimmed );
				if ( is_array( $maybe ) || is_object( $maybe ) ) {
					return (array) $maybe;
				}
			}
		}

		// Fallback to empty array.
		return array();
	}

	/**
	 * Get full data for website report including metadata.
	 * This replaces WebsitestatController::collectInfo().
	 *
	 * @param string $domain Domain name.
	 * @return array|null Full report data or null if website not found.
	 * @throws Exception If required classes are not available.
	 */
	public function get_full_report_data( $domain ) {
		// Get website record.
		$website = $this->get_website_by_domain( $domain );
		if ( ! $website ) {
			return null;
		}

		$wid = $website['id'];

		// Get all related table data.
		$report_data = $this->get_website_report_data( $wid );

		// Get thumbnail data.
		$thumbnail = array();

		// Ensure WebsiteThumbnail and Utils classes are available.
		if ( ! class_exists( 'Utils' ) ) {
			$utils_path = v_wpsa_PLUGIN_DIR . 'protected/components/Utils.php';
			if ( file_exists( $utils_path ) ) {
				require_once $utils_path;
			}
		}
		if ( ! class_exists( 'WebsiteThumbnail' ) ) {
			$thumbnail_path = v_wpsa_PLUGIN_DIR . 'protected/components/WebsiteThumbnail.php';
			if ( file_exists( $thumbnail_path ) ) {
				require_once $thumbnail_path;
			}
		}

		if ( class_exists( 'WebsiteThumbnail' ) ) {
			try {
				$thumbnail = WebsiteThumbnail::getThumbData(
					array(
						'url'  => $domain,
						'size' => 'l',
					)
				);
			} catch ( Exception $e ) {
				// Log error but continue.
				error_log( 'v-wpsa: thumbnail error: ' . $e->getMessage() );
				$thumbnail = array(
					'thumb' => 'https://image.thum.io/get/maxAge/350/width/350/https://' . $domain,
					'url'   => $domain,
				);
			}
		}

		// Calculate time difference for cache expiration.
		// Use 'modified' timestamp to match Yii controller behavior.
		$modified_timestamp = isset( $website['modified'] ) ? strtotime( $website['modified'] ) : (int) $website['added'];
		$diff               = time() - $modified_timestamp;
		$strtime            = '';

		// Calculate human-readable time difference.
		if ( $diff < 60 ) {
			$strtime = $diff . ' seconds ago';
		} elseif ( $diff < 3600 ) {
			$strtime = floor( $diff / 60 ) . ' minutes ago';
		} elseif ( $diff < 86400 ) {
			$strtime = floor( $diff / 3600 ) . ' hours ago';
		} else {
			$strtime = floor( $diff / 86400 ) . ' days ago';
		}

		// Build generated metadata matching WebsitestatController format.
		// Include all date components needed by report.php template.
		$generated = array(
			'time'    => $strtime,
			'seconds' => $diff,
			'A'       => gmdate( 'A', $modified_timestamp ),
			'Y'       => gmdate( 'Y', $modified_timestamp ),
			'M'       => gmdate( 'M', $modified_timestamp ),
			'd'       => gmdate( 'd', $modified_timestamp ),
			'H'       => gmdate( 'H', $modified_timestamp ),
			'i'       => gmdate( 'i', $modified_timestamp ),
		);

		// Prepare RateProvider instance.
		// Explicitly require the file to avoid Yii autoloader interference.
		$rateprovider      = null;
		$rateprovider_path = v_wpsa_PLUGIN_DIR . 'protected/vendors/Webmaster/Rates/RateProvider.php';
		if ( file_exists( $rateprovider_path ) ) {
			require_once $rateprovider_path;
			$rateprovider = new RateProvider();
		}

		// Calculate URL for update form.
		$upd_url = '#';
		if ( class_exists( 'V_WPSA_Config' ) ) {
			$upd_url = V_WPSA_Config::get( 'param.instant_redirect' ) ? '#update_form' : '#';
		}

		// Extract report sections (JSON fields already decoded in get_website_report_data).
		$cloud    = ! empty( $report_data['cloud'] ) ? $report_data['cloud'] : array();
		$content  = ! empty( $report_data['content'] ) ? $report_data['content'] : array();
		$document = ! empty( $report_data['document'] ) ? $report_data['document'] : array();
		$issetobj = ! empty( $report_data['issetobject'] ) ? $report_data['issetobject'] : array();
		$links    = ! empty( $report_data['links'] ) ? $report_data['links'] : array();
		$meta     = ! empty( $report_data['metatags'] ) ? $report_data['metatags'] : array();
		$w3c      = ! empty( $report_data['w3c'] ) ? $report_data['w3c'] : array();
		$misc     = ! empty( $report_data['misc'] ) ? $report_data['misc'] : array();

		// Assemble full data array matching WebsitestatController structure.
		$full_data = array(
			'website'      => $website,
			'cloud'        => $cloud,
			'content'      => $content,
			'document'     => $document,
			'isseter'      => $issetobj,
			'links'        => $links,
			'meta'         => $meta,
			'w3c'          => $w3c,
			'misc'         => $misc,
			'thumbnail'    => $thumbnail,
			'generated'    => $generated,
			'diff'         => $diff,
			'over_max'     => 6,
			'linkcount'    => isset( $links['links'] ) && is_array( $links['links'] ) ? count( $links['links'] ) : 0,
			'rateprovider' => $rateprovider,
			'updUrl'       => $upd_url,
		);

		// Ensure commonly accessed keys have safe default types to avoid type errors in templates.
		$full_data = $this->ensure_report_defaults( $full_data );

		return $full_data;
	}

	/**
	 * Ensure report data has expected keys and types so templates can safely use count() and array access.
	 *
	 * @param array $data Report data produced by get_full_report_data().
	 * @return array Normalized report data with defaults applied.
	 */
	protected function ensure_report_defaults( $data ) {
		// Content defaults.
		if ( empty( $data['content'] ) || ! is_array( $data['content'] ) ) {
			$data['content'] = array();
		}
		if ( ! isset( $data['content']['headings'] ) || ! is_array( $data['content']['headings'] ) ) {
			$data['content']['headings'] = array();
		}
		// Ensure each heading bucket is an array to safely count() later.
		foreach ( $data['content']['headings'] as $hkey => $hval ) {
			if ( ! is_array( $hval ) ) {
				$data['content']['headings'][ $hkey ] = array();
			}
		}
		if ( ! isset( $data['content']['total_img'] ) ) {
			$data['content']['total_img'] = 0;
		}
		if ( ! isset( $data['content']['total_alt'] ) ) {
			$data['content']['total_alt'] = 0;
		}
		if ( ! isset( $data['content']['deprecated'] ) || ! is_array( $data['content']['deprecated'] ) ) {
			$data['content']['deprecated'] = array();
		}
		if ( ! isset( $data['content']['isset_headings'] ) ) {
			$data['content']['isset_headings'] = 0;
		}

		// Document defaults.
		if ( empty( $data['document'] ) || ! is_array( $data['document'] ) ) {
			$data['document'] = array();
		}
		// Note: css and js are integer counts, not arrays.
		if ( ! isset( $data['document']['css'] ) ) {
			$data['document']['css'] = 0;
		}
		if ( ! isset( $data['document']['js'] ) ) {
			$data['document']['js'] = 0;
		}
		if ( ! isset( $data['document']['htmlratio'] ) ) {
			$data['document']['htmlratio'] = 0;
		}
		if ( ! isset( $data['document']['doctype'] ) ) {
			$data['document']['doctype'] = '';
		}
		if ( ! isset( $data['document']['lang'] ) ) {
			$data['document']['lang'] = '';
		}
		if ( ! isset( $data['document']['charset'] ) ) {
			$data['document']['charset'] = '';
		}
		if ( ! isset( $data['document']['favicon'] ) ) {
			$data['document']['favicon'] = '';
		}

		// Links defaults.
		if ( empty( $data['links'] ) || ! is_array( $data['links'] ) ) {
			$data['links'] = array();
		}
		if ( ! isset( $data['links']['links'] ) || ! is_array( $data['links']['links'] ) ) {
			$data['links']['links'] = array();
		}
		// Ensure each link entry is an array with expected keys.
		foreach ( $data['links']['links'] as $idx => $link ) {
			if ( ! is_array( $link ) ) {
				$data['links']['links'][ $idx ] = array(
					'Link'  => (string) $link,
					'Name'  => '',
					'Type'  => 'external',
					'Juice' => 'dofollow',
				);
			} else {
				// Ensure keys exist.
				$data['links']['links'][ $idx ] = array_merge(
					array(
						'Link'  => '',
						'Name'  => '',
						'Type'  => 'external',
						'Juice' => 'dofollow',
					),
					$link
				);
			}
		}
		if ( ! isset( $data['links']['internal'] ) ) {
			$data['links']['internal'] = 0;
		}
		if ( ! isset( $data['links']['external_dofollow'] ) ) {
			$data['links']['external_dofollow'] = 0;
		}
		if ( ! isset( $data['links']['external_nofollow'] ) ) {
			$data['links']['external_nofollow'] = 0;
		}
		if ( ! isset( $data['links']['friendly'] ) ) {
			$data['links']['friendly'] = 0;
		}
		if ( ! isset( $data['links']['isset_underscore'] ) ) {
			$data['links']['isset_underscore'] = 0;
		}

		// Meta defaults.
		if ( empty( $data['meta'] ) || ! is_array( $data['meta'] ) ) {
			$data['meta'] = array();
		}
		if ( ! isset( $data['meta']['ogproperties'] ) || ! is_array( $data['meta']['ogproperties'] ) ) {
			$data['meta']['ogproperties'] = array();
		}
		if ( ! isset( $data['meta']['title'] ) ) {
			$data['meta']['title'] = '';
		}
		if ( ! isset( $data['meta']['description'] ) ) {
			$data['meta']['description'] = '';
		}
		if ( ! isset( $data['meta']['keyword'] ) ) {
			$data['meta']['keyword'] = '';
		}

		// Cloud defaults.
		if ( empty( $data['cloud'] ) || ! is_array( $data['cloud'] ) ) {
			$data['cloud'] = array(
				'words'  => array(),
				'matrix' => array(),
			);
		}
		if ( ! isset( $data['cloud']['words'] ) || ! is_array( $data['cloud']['words'] ) ) {
			$data['cloud']['words'] = array();
		}
		if ( ! isset( $data['cloud']['matrix'] ) || ! is_array( $data['cloud']['matrix'] ) ) {
			$data['cloud']['matrix'] = array();
		}

		// Misc defaults.
		if ( ! isset( $data['misc'] ) || ! is_array( $data['misc'] ) ) {
			$data['misc'] = array();
		}
		if ( ! isset( $data['misc']['sitemap'] ) || ! is_array( $data['misc']['sitemap'] ) ) {
			$data['misc']['sitemap'] = array();
		}
		if ( ! isset( $data['misc']['analytics'] ) || ! is_array( $data['misc']['analytics'] ) ) {
			$data['misc']['analytics'] = array();
		}

		// Isseter defaults (boolean flags).
		if ( ! isset( $data['isseter'] ) || ! is_array( $data['isseter'] ) ) {
			$data['isseter'] = array();
		}
		$isseter_flags = array( 'flash', 'iframe', 'nestedtables', 'inlinecss', 'viewport', 'dublincore', 'appleicons', 'robotstxt', 'gzip' );
		foreach ( $isseter_flags as $flag ) {
			if ( ! isset( $data['isseter'][ $flag ] ) ) {
				$data['isseter'][ $flag ] = 0;
			}
		}

		// W3C defaults.
		if ( ! isset( $data['w3c'] ) || ! is_array( $data['w3c'] ) ) {
			$data['w3c'] = array();
		}
		if ( ! isset( $data['w3c']['valid'] ) ) {
			$data['w3c']['valid'] = 0;
		}
		if ( ! isset( $data['w3c']['errors'] ) ) {
			$data['w3c']['errors'] = 0;
		}
		if ( ! isset( $data['w3c']['warnings'] ) ) {
			$data['w3c']['warnings'] = 0;
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
	 * Normalize a report section which may be stored as an array, JSON string or serialized string.
	 *
	 * @param mixed $value The raw value from DB.
	 * @return array Normalized array representation (or empty array).
	 */
	protected function normalize_report_section( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}

		if ( null === $value || '' === $value ) {
			return array();
		}

		// If it's a JSON string, try decode.
		if ( is_string( $value ) ) {
			$trimmed = trim( $value );
			// Try JSON first.
			$decoded = json_decode( $trimmed, true );
			if ( JSON_ERROR_NONE === json_last_error() && ( is_array( $decoded ) || is_object( $decoded ) ) ) {
				return (array) $decoded;
			}

			// Try PHP serialized string via WordPress helper.
			if ( function_exists( 'maybe_unserialize' ) && maybe_unserialize( $trimmed ) !== $trimmed ) {
				$maybe = maybe_unserialize( $trimmed );
				if ( is_array( $maybe ) || is_object( $maybe ) ) {
					return (array) $maybe;
				}
			}

			// Nothing worked - return empty array.
			return array();
		}

		// Fallback: return empty array.
		return array();
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

		// Create an instance to access instance helpers (schema introspection, etc.).
		$db = new self();

		// Load required Yii vendor classes.
		// Note: We must load files directly before any class_exists() checks to avoid
		// triggering Yii's autoloader which will try to find the class in the wrong path.
		$helper_path = v_wpsa_PLUGIN_DIR . 'protected/vendors/Webmaster/Utils/Helper.php';
		if ( file_exists( $helper_path ) ) {
			require_once $helper_path;
		}

		try {
			// Fetch website HTML - try both HTTPS and HTTP.
			// Use a more realistic user-agent to avoid being blocked.
			$user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

			$request_args = array(
				'timeout'     => 30,
				'user-agent'  => $user_agent,
				'sslverify'   => false,
				'redirection' => 5,
				'headers'     => array(
					'Accept'                    => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
					'Accept-Language'           => 'en-US,en;q=0.5',
					'Accept-Encoding'           => 'gzip, deflate',
					'DNT'                       => '1',
					'Connection'                => 'keep-alive',
					'Upgrade-Insecure-Requests' => '1',
				),
			);

			// Try HTTPS first (more common nowadays).
			$url      = 'https://' . $domain;
			$response = wp_remote_get( $url, $request_args );

			// If HTTPS fails, try HTTP.
			if ( is_wp_error( $response ) ) {
				// Do not log here to avoid phpcs warnings. We silently fallback to HTTP.
				$url      = 'http://' . $domain;
				$response = wp_remote_get( $url, $request_args );
			}

			if ( is_wp_error( $response ) ) {
				return new WP_Error( 'fetch_failed', 'Could not fetch website: ' . $response->get_error_message() );
			}

			$response_code = wp_remote_retrieve_response_code( $response );

			// Accept any 2xx or 3xx response code (success or redirect).
			// Many websites use 301/302 redirects or return 206 (partial content).
			if ( $response_code < 200 || $response_code >= 400 ) {
				// For 4xx and 5xx errors, provide a more helpful message.
				if ( $response_code >= 400 && $response_code < 500 ) {
					return new WP_Error( 'fetch_failed', 'Website access denied (HTTP ' . $response_code . '). The website may be blocking automated requests or may require authentication.' );
				} else {
					return new WP_Error( 'fetch_failed', 'Website returned HTTP ' . $response_code );
				}
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
				// Prepare data and filter columns to match DB schema.
				$website_data = array(
					'domain'   => $domain,
					'idn'      => $idn,
					'ip'       => $ip,
					'modified' => $now,
					'score'    => 0,
				);
				$website_data = $db->filter_columns( 'website', $website_data );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->update( $table_prefix . 'website', $website_data, array( 'id' => $wid ) );
			} else {
				// Insert new website.
				$website_insert = array(
					'domain'    => $domain,
					'idn'       => $idn,
					'ip'        => $ip,
					'md5domain' => md5( $domain ),
					'modified'  => $now,
					'score'     => 0,
				);
				$website_insert = $db->filter_columns( 'website', $website_insert );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->insert( $table_prefix . 'website', $website_insert );
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
				// Filter content_data columns to match DB schema.
				$content_data = $db->filter_columns( 'content', $content_data );
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

				$doc_data = $db->filter_columns( 'document', $doc_data );
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

				$links_data = $db->filter_columns( 'links', $links_data );
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
					'title'       => method_exists( $meta_analyzer, 'getTitle' ) ? substr( (string) $meta_analyzer->getTitle(), 0, 255 ) : '',
					'description' => method_exists( $meta_analyzer, 'getDescription' ) ? substr( (string) $meta_analyzer->getDescription(), 0, 500 ) : '',
				);

				$meta_data = $db->filter_columns( 'metatags', $meta_data );
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

			$misc_data = $db->filter_columns( 'misc', $misc_data );
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

	/**
	 * Update website score in database.
	 *
	 * @param int $wid Website ID.
	 * @param int $score Score value (0-100).
	 * @return bool True on success, false on failure.
	 */
	public function set_website_score( $wid, $score ) {
		$table = 'website';
		$data  = array( 'score' => intval( $score ) );

		// If schema does not contain 'score' column, attempt to add it (best-effort).
		$cols = $this->get_table_columns( $table );
		if ( ! in_array( 'score', $cols, true ) ) {
			$table_name = $this->get_table_name( $table );
			// Try to add the column; ignore failures.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$alter_sql = "ALTER TABLE {$table_name} ADD COLUMN score INT(3) NOT NULL DEFAULT 0";
			@ $this->wpdb->query( $alter_sql );
			// Refresh cached columns.
			$cols = $this->get_table_columns( $table );
		}

		$data = $this->filter_columns( $table, $data );
		if ( empty( $data ) ) {
			return false;
		}
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return false !== $this->wpdb->update( $this->get_table_name( $table ), $data, array( 'id' => intval( $wid ) ) );
	}
}
