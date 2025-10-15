<?php
/**
 * Yii Framework Integration Class
 *
 * Handles all Yii framework initialization and configuration.
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class V_WPSA_Yii_Integration
 */
class V_WPSA_Yii_Integration {

	/**
	 * Configure Yii autoloader with WordPress-friendly settings.
	 *
	 * Prevents Yii's autoloader from interfering with WordPress core classes
	 * and other non-Yii classes.
	 */
	public static function configure_yii_autoloader() {
		// Add a filter to prevent Yii from trying to autoload WordPress classes.
		// Yii's autoloader filters allow us to skip certain class names.
		YiiBase::$autoloaderFilters[] = function( $className ) {
			// Skip WordPress core classes (they start with WP_ or have WordPress patterns).
			if ( strpos( $className, 'WP_' ) === 0 ) {
				return true; // Skip this class (return true to skip Yii autoloader).
			}

			// Skip WordPress filesystem classes.
			if ( strpos( $className, 'WP_Filesystem' ) === 0 ) {
				return true;
			}

			// Skip vendor classes that should be manually loaded.
			$skip_classes = array( 'RateProvider', 'Utils' );
			if ( in_array( $className, $skip_classes, true ) ) {
				return true;
			}

			// Allow Yii to process other classes.
			return false;
		};
	}

	/**
	 * Configure Yii application with WordPress-friendly settings.
	 *
	 * @param mixed $app Yii application instance.
	 */
	public static function configure_yii_app( $app ) {
		if ( ! $app ) {
			return;
		}

		if ( $app->hasComponent( 'request' ) ) {
			$request      = $app->getRequest();
			$plugin_parts = wp_parse_url( rtrim( v_wpsa_PLUGIN_URL, '/' ) );
			if ( ! is_array( $plugin_parts ) ) {
				$plugin_parts = array();
			}

			$host_info = '';
			if ( ! empty( $plugin_parts['scheme'] ) && ! empty( $plugin_parts['host'] ) ) {
				$host_info = $plugin_parts['scheme'] . '://' . $plugin_parts['host'];
				if ( ! empty( $plugin_parts['port'] ) ) {
					$host_info .= ':' . $plugin_parts['port'];
				}
			} else {
				$site_parts = wp_parse_url( get_site_url() );
				if ( ! is_array( $site_parts ) ) {
					$site_parts = array();
				}
				if ( ! empty( $site_parts['scheme'] ) && ! empty( $site_parts['host'] ) ) {
					$host_info = $site_parts['scheme'] . '://' . $site_parts['host'];
					if ( ! empty( $site_parts['port'] ) ) {
						$host_info .= ':' . $site_parts['port'];
					}
				}
			}

			if ( $host_info ) {
				$request->setHostInfo( $host_info );
			}

			$path = '';
			if ( ! empty( $plugin_parts['path'] ) ) {
				$path = '/' . ltrim( $plugin_parts['path'], '/' );
			}

			$path = rtrim( $path, '/' );

			$request->setBaseUrl( $path );
			$request->setScriptUrl( ( $path ? $path : '' ) . '/index.php' );
		}

		if ( $app->hasComponent( 'urlManager' ) ) {
			$urlManager                 = $app->getUrlManager();
			$urlManager->urlFormat      = 'get';
			$urlManager->showScriptName = true;
		}
	}

	/**
	 * Initialize Yii framework when shortcode is present.
	 */
	public static function init() {
		global $post, $v_wpsa_app;

		if ( null !== $v_wpsa_app ) {
			return;
		}

		// Check if we need to initialize (shortcode present or admin area).
		$should_init = false;
		if ( is_admin() ) {
			$should_init = false; // Don't init in admin to avoid conflicts.
		} elseif ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'v_wpsa' ) ) {
			$should_init = true;
		}

		if ( ! $should_init ) {
			return;
		}

		// Initialize Yii framework.
		$yii    = v_wpsa_PLUGIN_DIR . 'framework/yii.php';
		$config = v_wpsa_PLUGIN_DIR . 'protected/config/main.php';

		if ( file_exists( $yii ) && file_exists( $config ) ) {
			require_once $yii;

			// Configure Yii autoloader to skip WordPress and other non-Yii classes.
			// This prevents Yii from trying to load WordPress core classes.
			self::configure_yii_autoloader();

			// Create Yii application but don't run it yet.
			$v_wpsa_app = Yii::createWebApplication( $config );

			// Set timezone from config.
			if ( isset( $v_wpsa_app->params['app.timezone'] ) ) {
				$v_wpsa_app->setTimeZone( $v_wpsa_app->params['app.timezone'] );
			}

			// Configure Yii app for WordPress environment.
			self::configure_yii_app( $v_wpsa_app );
		}
	}
}
