<?php
/**
 * File: class-v-wpsa-config.php
 *
 * Description: Configuration management class.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class V_WPSA_Config
 */
class V_WPSA_Config {

	/**
	 * Cached configuration array.
	 *
	 * @var array|null
	 */
	private static $config = null;

	/**
	 * Get configuration value.
	 *
	 * @param string $key Configuration key (e.g., 'app.name', 'psi.show').
	 * @param mixed  $default Default value if key not found.
	 * @return mixed Configuration value.
	 */
	public static function get( $key, $default = null ) {
		if ( null === self::$config ) {
			self::load_config();
		}

		return isset( self::$config[ $key ] ) ? self::$config[ $key ] : $default;
	}

	/**
	 * Get all configuration values.
	 *
	 * @return array All configuration values.
	 */
	public static function get_all() {
		if ( null === self::$config ) {
			self::load_config();
		}

		return self::$config;
	}

	/**
	 * Load configuration from config/config.php.
	 */
	private static function load_config() {
		$config_file = V_WPSA_PLUGIN_DIR . 'config/config.php';

		if ( file_exists( $config_file ) ) {
			self::$config = require $config_file;
		} else {
			// Fallback to default values if config file not found.
			self::$config = self::get_defaults();
		}

		// Allow filtering of config values.
		self::$config = apply_filters( 'v_wpsa_config', self::$config );
	}

	/**
	 * Get default configuration values.
	 *
	 * @return array Default configuration.
	 */
	private static function get_defaults() {
		return array(
			'app.name'                   => 'V WP SEO Audit',
			'app.languages'              => array( 'en' => 'English' ),
			'psi.categories'             => array( 'performance', 'accessibility', 'best-practices', 'seo', 'pwa' ),
			'psi.device'                 => 'desktop',
			'psi.run_instantly'          => true,
			'psi.show'                   => true,
			'analyzer.tag_cloud'         => 10,
			'analyzer.consistency_count' => 5,
			'param.index_website_count'  => 12,
			'param.bad_words_validation' => false,
			'param.addthis'              => '',
			'param.placeholder'          => 'example.com',
			'template.footer'            => '<p>Developed by <strong><a href="https://vontainment.com">Vontainment</a></strong></p>',
		);
	}

	/**
	 * Get base URL for the plugin.
	 *
	 * @param bool $absolute Whether to return absolute URL.
	 * @return string Base URL.
	 */
	public static function get_base_url( $absolute = false ) {
		if ( $absolute ) {
			return untrailingslashit( V_WPSA_PLUGIN_URL );
		}
		return untrailingslashit( str_replace( home_url(), '', V_WPSA_PLUGIN_URL ) );
	}
}
