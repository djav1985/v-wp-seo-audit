<?php
/**
 * Website Thumbnail Class
 * WordPress-native version of WebsiteThumbnail from protected/components/WebsiteThumbnail.php
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class V_WPSA_Thumbnail
 *
 * Handles website thumbnail generation and caching.
 */
class V_WPSA_Thumbnail {
	/**
	 * Get the WordPress uploads directory path for thumbnails.
	 *
	 * @return string Thumbnail directory path.
	 */
	private static function get_upload_dir() {
		if ( function_exists( 'wp_upload_dir' ) ) {
			$upload_dir    = wp_upload_dir();
			$thumbnail_dir = $upload_dir['basedir'] . '/seo-audit/thumbnails';

			// Create directory if it doesn't exist.
			if ( ! file_exists( $thumbnail_dir ) ) {
				wp_mkdir_p( $thumbnail_dir );
			}

			return $thumbnail_dir;
		}

		// Fallback for non-WordPress environments.
		$thumbnail_dir = '/tmp/seo-audit/thumbnails';
		if ( ! file_exists( $thumbnail_dir ) ) {
			mkdir( $thumbnail_dir, 0755, true );
		}

		return $thumbnail_dir;
	}

	/**
	 * Get the WordPress uploads directory URL for thumbnails.
	 *
	 * @return string Thumbnail directory URL.
	 */
	private static function get_upload_url() {
		if ( function_exists( 'wp_upload_dir' ) ) {
			$upload_dir = wp_upload_dir();
			return $upload_dir['baseurl'] . '/seo-audit/thumbnails';
		}

		// Fallback for non-WordPress environments.
		return '/tmp/seo-audit/thumbnails';
	}

	/**
	 * Get cached thumbnail path for a domain.
	 *
	 * @param string $domain Domain name.
	 * @return string Thumbnail file path.
	 */
	private static function get_cached_thumbnail_path( $domain ) {
		$filename = md5( $domain ) . '.jpg';
		return self::get_upload_dir() . '/' . $filename;
	}

	/**
	 * Get cached thumbnail URL for a domain.
	 *
	 * @param string $domain Domain name.
	 * @return string Thumbnail URL.
	 */
	private static function get_cached_thumbnail_url( $domain ) {
		$filename = md5( $domain ) . '.jpg';
		return self::get_upload_url() . '/' . $filename;
	}

	/**
	 * Download and cache thumbnail from thum.io.
	 *
	 * @param string $domain Domain name.
	 * @param int    $width Thumbnail width.
	 * @return string|false Thumbnail URL or false on failure.
	 */
	private static function download_thumbnail( $domain, $width = 350 ) {
		$thumbnail_path = self::get_cached_thumbnail_path( $domain );

		// Check if cached thumbnail exists and is less than 7 days old.
		if ( file_exists( $thumbnail_path ) ) {
			$file_time      = filemtime( $thumbnail_path );
			$cache_duration = 7 * 24 * 60 * 60; // 7 days in seconds.

			if ( ( time() - $file_time ) < $cache_duration ) {
				return self::get_cached_thumbnail_url( $domain );
			}
		}

		// Generate thum.io URL.
		$thumbnail_url = "https://image.thum.io/get/maxAge/350/width/{$width}/https://{$domain}";

		// Download thumbnail using V_WPSA_Utils if available.
		if ( class_exists( 'V_WPSA_Utils' ) ) {
			$thumbnail_data = V_WPSA_Utils::curl( $thumbnail_url );
		} else {
			// Fallback to WordPress HTTP API.
			$response = wp_remote_get( $thumbnail_url );
			if ( is_wp_error( $response ) ) {
				return false;
			}
			$thumbnail_data = wp_remote_retrieve_body( $response );
		}

		if ( $thumbnail_data && strlen( $thumbnail_data ) > 0 ) {
			file_put_contents( $thumbnail_path, $thumbnail_data );
			return self::get_cached_thumbnail_url( $domain );
		}

		return false;
	}

	/**
	 * Delete cached thumbnail for a domain.
	 *
	 * @param string $domain Domain name.
	 * @return bool True on success, false otherwise.
	 */
	public static function delete_thumbnail( $domain ) {
		$thumbnail_path = self::get_cached_thumbnail_path( $domain );

		if ( file_exists( $thumbnail_path ) ) {
			unlink( $thumbnail_path );
			return true;
		}

		return false;
	}

	/**
	 * Get thumbnail data (URL) for display.
	 *
	 * @param array $params Parameters array with 'url' key.
	 * @param int   $num Optional index (not used).
	 * @return array Thumbnail data array.
	 * @throws InvalidArgumentException If url param not specified.
	 */
	public static function get_thumb_data( array $params = array(), $num = 0 ) {
		if ( ! isset( $params['url'] ) ) {
			throw new InvalidArgumentException( 'Url param is not specified' );
		}

		$domain = $params['url'];
		$width  = isset( $params['width'] ) ? $params['width'] : 350;

		// Try to get or create cached thumbnail.
		$thumbnail_url = self::download_thumbnail( $domain, $width );

		if ( ! $thumbnail_url ) {
			// Fallback to direct thum.io URL if download fails.
			$thumbnail_url = "https://image.thum.io/get/maxAge/350/width/{$width}/https://{$domain}";
		}

		return array(
			'thumb'  => $thumbnail_url,
			'url'    => $domain,
			'cached' => file_exists( self::get_cached_thumbnail_path( $domain ) ),
		);
	}

	/**
	 * Get OG image for social media sharing.
	 *
	 * @param array $params Parameters array with 'url' key.
	 * @return string Thumbnail URL.
	 * @throws InvalidArgumentException If url param not specified.
	 */
	public static function get_og_image( array $params = array() ) {
		if ( ! isset( $params['url'] ) ) {
			throw new InvalidArgumentException( 'Url param is not specified' );
		}

		$domain = $params['url'];
		$width  = isset( $params['width'] ) ? $params['width'] : 350;

		// Try to get cached thumbnail first.
		$thumbnail_path = self::get_cached_thumbnail_path( $domain );

		if ( file_exists( $thumbnail_path ) ) {
			return self::get_cached_thumbnail_url( $domain );
		}

		// Return thum.io URL as fallback.
		return "https://image.thum.io/get/maxAge/350/width/{$width}/https://{$domain}";
	}

	/**
	 * Generate thumbnail stack for multiple websites.
	 *
	 * @param array $websites Array of website data.
	 * @param array $params Parameters for thumbnail generation.
	 * @return array Thumbnail stack indexed by website ID.
	 */
	public static function thumbnail_stack( $websites, array $params = array() ) {
		$stack = array();

		foreach ( $websites as $website ) {
			$params['url']           = $website['domain'];
			$stack[ $website['id'] ] = self::get_thumb_data( $params );
		}

		return $stack;
	}

	// ========== Backward Compatibility Aliases ==========
	// These methods maintain camelCase names for backward compatibility.

	/**
	 * Backward compatibility alias for delete_thumbnail.
	 *
	 * @param string $domain Domain name.
	 * @return bool True on success, false otherwise.
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	 */
	public static function deleteThumbnail( $domain ) {
		return self::delete_thumbnail( $domain );
	}

	/**
	 * Backward compatibility alias for get_thumb_data.
	 *
	 * @param array $params Parameters array with 'url' key.
	 * @param int   $num Optional index (not used).
	 * @return array Thumbnail data array.
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	 */
	public static function getThumbData( array $params = array(), $num = 0 ) {
		return self::get_thumb_data( $params, $num );
	}

	/**
	 * Backward compatibility alias for get_og_image.
	 *
	 * @param array $params Parameters array with 'url' key.
	 * @return string Thumbnail URL.
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	 */
	public static function getOgImage( array $params = array() ) {
		return self::get_og_image( $params );
	}

	/**
	 * Backward compatibility alias for thumbnail_stack.
	 *
	 * @param array $websites Array of website data.
	 * @param array $params Parameters for thumbnail generation.
	 * @return array Thumbnail stack indexed by website ID.
	 *
	 * phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
	 */
	public static function thumbnailStack( $websites, array $params = array() ) {
		return self::thumbnail_stack( $websites, $params );
	}
	// phpcs:enable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid

// Backward compatibility: create alias for old class name.
if ( ! class_exists( 'WebsiteThumbnail' ) ) {
	class_alias( 'V_WPSA_Thumbnail', 'WebsiteThumbnail' );
}
