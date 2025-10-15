<?php
/**
 * Helper Functions Class
 *
 * WordPress-native helper functions for the plugin.
 *
 * @package V_WP_SEO_Audit
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
		$pdf_base   = $upload_dir['basedir'] . '/seo-audit/pdf/';

		// Get available languages from config or use default.
		global $v_wpsa_app;
		$languages = array( 'en' ); // Default language.

		if ( null !== $v_wpsa_app && isset( $v_wpsa_app->params['app.languages'] ) ) {
			$languages = array_keys( $v_wpsa_app->params['app.languages'] );
		}

		// Delete PDF for each language.
		foreach ( $languages as $lang ) {
			$subfolder = mb_substr( $domain, 0, 1 );
			$pdf_path  = $pdf_base . $lang . '/' . $subfolder . '/' . $domain . '.pdf';

			if ( file_exists( $pdf_path ) ) {
				wp_delete_file( $pdf_path );
			}
		}

		// Also delete the cached thumbnail if the class is available.
		// Use false parameter to prevent autoloader from triggering.
		if ( class_exists( 'WebsiteThumbnail', false ) ) {
			WebsiteThumbnail::deleteThumbnail( $domain );
		}

		return true;
	}

	/**
	 * Get configuration value.
	 *
	 * Provides compatibility with old Yii config access pattern.
	 *
	 * @param string $config_name The configuration name to retrieve.
	 * @return mixed Configuration value or null if not found.
	 */
	public static function get_config( $config_name ) {
		// Map old Yii config names to WordPress equivalents.
		$config_map = array(
			'analyzer.cache_time'       => apply_filters( 'v_wpsa_cache_time', 86400 ), // 24 hours default.
			'param.rating_per_page'     => apply_filters( 'v_wpsa_rating_per_page', 12 ),
			'param.index_website_count' => apply_filters( 'v_wpsa_index_website_count', 30 ),
		);

		if ( isset( $config_map[ $config_name ] ) ) {
			return $config_map[ $config_name ];
		}

		// Try to get from global Yii app if still available (legacy fallback).
		global $v_wpsa_app;
		if ( $v_wpsa_app && isset( $v_wpsa_app->params[ $config_name ] ) ) {
			return $v_wpsa_app->params[ $config_name ];
		}

		return null;
	}

	/**
	 * Load configuration file.
	 * Replaces Utils::getLocalConfigIfExists() with WordPress-native implementation.
	 *
	 * @param string $config_name The config file name (without extension).
	 * @return mixed The config value or empty array on failure.
	 */
	public static function load_config_file( $config_name ) {
		$config_dir   = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/';
		$config_local = $config_dir . $config_name . '_local.php';
		$config_prod  = $config_dir . $config_name . '.php';

		if ( file_exists( $config_local ) ) {
			return require $config_local;
		} elseif ( file_exists( $config_prod ) ) {
			return require $config_prod;
		}

		return array();
	}

	/**
	 * Analyze website using WordPress-native approach.
	 *
	 * This function bridges between WordPress and the Yii-based analysis system.
	 *
	 * @param string $domain The domain to analyze.
	 * @param string $idn Internationalized domain name.
	 * @param string $ip IP address of the domain.
	 * @param int    $wid Optional website ID.
	 * @return array|false Analysis result or false on failure.
	 */
	public static function analyze_website( $domain, $idn, $ip, $wid = null ) {
		global $v_wpsa_app;

		// Ensure Yii is initialized for analysis.
		if ( null === $v_wpsa_app ) {
			$yii    = V_WP_SEO_AUDIT_PLUGIN_DIR . 'framework/yii.php';
			$config = V_WP_SEO_AUDIT_PLUGIN_DIR . 'protected/config/main.php';

			if ( file_exists( $yii ) && file_exists( $config ) ) {
				require_once $yii;
				$v_wpsa_app = Yii::createWebApplication( $config );

				if ( isset( $v_wpsa_app->params['app.timezone'] ) ) {
					$v_wpsa_app->setTimeZone( $v_wpsa_app->params['app.timezone'] );
				}

				V_WPSA_Yii_Integration::configure_yii_app( $v_wpsa_app );
			} else {
				return false;
			}
		}

		// Check if we need to analyze or use cached data.
		$cache_time = self::get_config( 'analyzer.cache_time' );

		try {
			// Use Yii's WebsitestatController to handle analysis.
			$controller = new WebsitestatController( 'websitestat' );

			// Check if website exists and is recent.
			$website = Website::model()->findByAttributes( array( 'domain' => $domain ) );

			if ( $website && $website->added > ( time() - $cache_time ) ) {
				// Use existing analysis.
				return array(
					'website' => $website,
					'cached'  => true,
				);
			}

			// Need new analysis.
			if ( $website ) {
				$wid = $website->id;
			}

			// Trigger new analysis (this will use Yii ParseController internally).
			$parse_controller = new ParseController( 'parse' );
			$result           = $parse_controller->actionWebsite( $domain, $idn, $ip, $wid );

			if ( $result ) {
				$website = Website::model()->findByAttributes( array( 'domain' => $domain ) );
				return array(
					'website' => $website,
					'cached'  => false,
				);
			}
		} catch ( Exception $e ) {
			// Optionally handle error here (e.g., do_action for logging), but do not log in production.
			return false;
		}

		return false;
	}
}
