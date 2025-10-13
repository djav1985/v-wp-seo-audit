<?php

class WebsiteThumbnail {
	/**
	 * Get the WordPress uploads directory path for thumbnails
	 */
	private static function getUploadDir() {
		if (function_exists( 'wp_upload_dir' )) {
			$upload_dir    = wp_upload_dir();
			$thumbnail_dir = $upload_dir['basedir'] . '/seo-audit/thumbnails';

			// Create directory if it doesn't exist.
			if ( ! file_exists( $thumbnail_dir )) {
				wp_mkdir_p( $thumbnail_dir );
			}

			return $thumbnail_dir;
		}

		// Fallback for non-WordPress environments.
		$root          = Yii::getPathofAlias( 'webroot' );
		$thumbnail_dir = $root . '/uploads/seo-audit/thumbnails';

		if ( ! file_exists( $thumbnail_dir )) {
			mkdir( $thumbnail_dir, 0755, true );
		}

		return $thumbnail_dir;
	}

	/**
	 * Get the WordPress uploads directory URL for thumbnails
	 */
	private static function getUploadUrl() {
		if (function_exists( 'wp_upload_dir' )) {
			$upload_dir = wp_upload_dir();
			return $upload_dir['baseurl'] . '/seo-audit/thumbnails';
		}

		// Fallback for non-WordPress environments.
		return Yii::app()->request->getBaseUrl( true ) . '/uploads/seo-audit/thumbnails';
	}

	/**
	 * Get cached thumbnail path for a domain
	 */
	private static function getCachedThumbnailPath( $domain) {
		$filename = md5( $domain ) . '.jpg';
		return self::getUploadDir() . '/' . $filename;
	}

	/**
	 * Get cached thumbnail URL for a domain
	 */
	private static function getCachedThumbnailUrl( $domain) {
		$filename = md5( $domain ) . '.jpg';
		return self::getUploadUrl() . '/' . $filename;
	}

	/**
	 * Download and cache thumbnail from thum.io
	 */
	private static function downloadThumbnail( $domain, $width = 350) {
		$thumbnail_path = self::getCachedThumbnailPath( $domain );

		// Check if cached thumbnail exists and is less than 7 days old.
		if (file_exists( $thumbnail_path )) {
			$file_time      = filemtime( $thumbnail_path );
			$cache_duration = 7 * 24 * 60 * 60; // 7 days in seconds

			if (( time() - $file_time ) < $cache_duration) {
				return self::getCachedThumbnailUrl( $domain );
			}
		}

		// Generate thum.io URL.
		$thumbnail_url = "https://image.thum.io/get/maxAge/350/width/{$width}/https://{$domain}";

		// Download thumbnail.
		$thumbnail_data = Utils::curl( $thumbnail_url );

		if ($thumbnail_data && strlen( $thumbnail_data ) > 0) {
			file_put_contents( $thumbnail_path, $thumbnail_data );
			return self::getCachedThumbnailUrl( $domain );
		}

		return false;
	}

	/**
	 * Delete cached thumbnail for a domain
	 */
	public static function deleteThumbnail( $domain) {
		$thumbnail_path = self::getCachedThumbnailPath( $domain );

		if (file_exists( $thumbnail_path )) {
			unlink( $thumbnail_path );
			return true;
		}

		return false;
	}

	/**
	 * Get thumbnail data (URL) for display
	 */
	public static function getThumbData( array $params = array(), $num = 0) {
		if ( ! isset( $params['url'] )) {
			throw new InvalidArgumentException( 'Url param is not specified' );
		}

		$domain = $params['url'];
		$width  = isset( $params['width'] ) ? $params['width'] : 350;

		// Try to get or create cached thumbnail.
		$thumbnail_url = self::downloadThumbnail( $domain, $width );

		if ( ! $thumbnail_url) {
			// Fallback to direct thum.io URL if download fails.
			$thumbnail_url = "https://image.thum.io/get/maxAge/350/width/{$width}/https://{$domain}";
		}

		return json_encode(
			array(
				'thumb'  => $thumbnail_url,
				'url'    => $domain,
				'cached' => file_exists( self::getCachedThumbnailPath( $domain ) ),
			)
		);
	}

	/**
	 * Get OG image for social media sharing
	 */
	public static function getOgImage( array $params = array()) {
		if ( ! isset( $params['url'] )) {
			throw new InvalidArgumentException( 'Url param is not specified' );
		}

		$domain = $params['url'];
		$width  = isset( $params['width'] ) ? $params['width'] : 350;

		// Try to get cached thumbnail first.
		$thumbnail_path = self::getCachedThumbnailPath( $domain );

		if (file_exists( $thumbnail_path )) {
			return self::getCachedThumbnailUrl( $domain );
		}

		// Return thum.io URL as fallback.
		return "https://image.thum.io/get/maxAge/350/width/{$width}/https://{$domain}";
	}

	/**
	 * Generate thumbnail stack for multiple websites
	 */
	public static function thumbnailStack( $websites, array $params = array()) {
		$stack = array();

		foreach ($websites as $website) {
			$params['url']           = $website['domain'];
			$stack[ $website['id'] ] = self::getThumbData( $params );
		}

		return $stack;
	}
}
