<?php
/**
 * Helper Functions Class
 *
 * WordPress-native helper functions for the plugin.
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class V_WPSA_Helpers
 */
class V_WPSA_Helpers {

	/**
	 * Delete PDF file for a domain.
	 *
	 * @param string $domain The domain whose PDF should be deleted.
	 * @return bool True on success, false on failure.
	 */
	public static function delete_pdf( $domain ) {
		// Get WordPress upload directory.
		$upload_dir = wp_upload_dir();
		$pdf_base   = rtrim( $upload_dir['basedir'], '\/' ) . '/seo-audit/pdf/';

		// Primary (new) simplified PDF paths.
		$simple_paths = array(
			$pdf_base . $domain . '.pdf',
			$pdf_base . $domain . '_pagespeed.pdf',
		);

		foreach ( $simple_paths as $pdf_path ) {
			if ( file_exists( $pdf_path ) ) {
				wp_delete_file( $pdf_path );
			}
		}

		// Also attempt to remove legacy nested PDF layout (language/first-letter) for backward compatibility.
		// Default to English language for legacy paths.
		$languages = apply_filters( 'v_wpsa_languages', array( 'en' ) );

		foreach ( $languages as $lang ) {
			$subfolder    = mb_substr( $domain, 0, 1 );
			$legacy_base  = rtrim( $upload_dir['basedir'], '\/' ) . '/seo-audit/pdf/' . $lang . '/' . $subfolder . '/';
			$legacy_paths = array(
				$legacy_base . $domain . '.pdf',
				$legacy_base . $domain . '_pagespeed.pdf',
			);
			foreach ( $legacy_paths as $pdf_path ) {
				if ( file_exists( $pdf_path ) ) {
					wp_delete_file( $pdf_path );
				}
			}
		}

		// Also delete the cached thumbnail if the class is available.
		if ( class_exists( 'V_WPSA_Thumbnail' ) ) {
			V_WPSA_Thumbnail::delete_thumbnail( $domain );
		}

		return true;
	}

	/**
	 * Get configuration value.
	 *
	 * WordPress-native configuration access using filters.
	 *
	 * @param string $config_name The configuration name to retrieve.
	 * @return mixed Configuration value or null if not found.
	 */
	public static function get_config( $config_name ) {
		// Map config names to WordPress filter equivalents.
		$config_map = array(
			'analyzer.cache_time'       => apply_filters( 'v_wpsa_cache_time', 86400 ), // 24 hours default.
			'param.rating_per_page'     => apply_filters( 'v_wpsa_rating_per_page', 12 ),
			'param.index_website_count' => apply_filters( 'v_wpsa_index_website_count', 30 ),
		);

		if ( isset( $config_map[ $config_name ] ) ) {
			return $config_map[ $config_name ];
		}

		// Allow custom config via filter.
		return apply_filters( 'v_wpsa_config_' . $config_name, null );
	}

	/**
	 * Load configuration file.
	 * WordPress-native implementation for loading config files.
	 *
	 * @param string $config_name The config file name (without extension).
	 * @return mixed The config value or empty array on failure.
	 */
	public static function load_config_file( $config_name ) {
		// Try old directory first (moved files).
		$config_dir   = v_wpsa_PLUGIN_DIR . 'old/protected/config/';
		$config_local = $config_dir . $config_name . '_local.php';
		$config_prod  = $config_dir . $config_name . '.php';

		if ( file_exists( $config_local ) ) {
			return require $config_local;
		} elseif ( file_exists( $config_prod ) ) {
			return require $config_prod;
		}

		return array();
	}
}
