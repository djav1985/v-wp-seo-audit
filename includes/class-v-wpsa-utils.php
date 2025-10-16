<?php
/**
 * File: class-v-wpsa-utils.php
 *
 * Description: Utility functions for the plugin.
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
 * Class V_WPSA_Utils
 *
 * Provides utility functions for the plugin.
 */
class V_WPSA_Utils {

	/**
	 * Shuffle an associative array.
	 *
	 * @param array $list The array to shuffle.
	 * @return array The shuffled array.
	 */
	public static function shuffle_assoc( $list ) {
		$keys = array_keys( $list );
		shuffle( $keys );
		$random = array();
		foreach ( $keys as $key ) {
			$random[ $key ] = $list[ $key ];
		}

		return $random;
	}

	/**
	 * Calculate proportion as percentage.
	 *
	 * @param int $big The larger value.
	 * @param int $small The smaller value.
	 * @return float The percentage.
	 */
	public static function proportion( $big, $small ) {
		return $big > 0 ? round( $small * 100 / $big, 2 ) : 0;
	}

	/**
	 * Create nested directory structure.
	 *
	 * @param string $path The path to create.
	 * @return bool True on success, false on failure.
	 */
	public static function create_nested_dir( $path ) {
		$dir = pathinfo( $path, PATHINFO_DIRNAME );

		if ( is_dir( $dir ) ) {
			return true;
		}

		// Prefer WordPress helper if available (handles permissions and recursion).
		if ( function_exists( 'wp_mkdir_p' ) ) {
			return (bool) wp_mkdir_p( $dir );
		}

		// Fallback to PHP recursive mkdir with permission bits.
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Fallback directory creation with error suppression.
		$created = @mkdir( $dir, 0777, true );
		if ( $created ) {
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Fallback chmod with error suppression.
			@chmod( $dir, 0777 );
		}
		return (bool) $created;
	}

	/**
	 * Create PDF folder for a domain.
	 *
	 * @param string $domain The domain name.
	 * @return string The PDF file path.
	 * @throws Exception If the folder cannot be created.
	 */
	public static function create_pdf_folder( $domain ) {
		// Ensure the primary PDF storage location uses the simplified uploads path.
		$pdf = self::get_pdf_file( $domain );
		$dir = pathinfo( $pdf, PATHINFO_DIRNAME );

		if ( ! is_dir( $dir ) ) {
			if ( function_exists( 'wp_mkdir_p' ) ) {
				$ok = wp_mkdir_p( $dir );
			} else {
				$ok = self::create_nested_dir( $pdf );
			}

			if ( ! $ok ) {
				throw new Exception( 'Unable to create PDF directory: ' . $dir . '. Please ensure the uploads directory is writable (wp-content/uploads).' );
			}
		}

		return $pdf;
	}

	/**
	 * Delete PDF files for a domain.
	 *
	 * @param string $domain The domain name.
	 * @return bool True on success.
	 */
	public static function delete_pdf( $domain ) {
		// Delete simplified PDF files in WordPress uploads.
		if ( function_exists( 'wp_upload_dir' ) ) {
			$upload_dir = wp_upload_dir();
			$base       = rtrim( $upload_dir['basedir'], "\/'" ) . '/seo-audit/pdf/';
			$paths      = array(
				$base . $domain . '.pdf',
				$base . $domain . '_pagespeed.pdf',
			);
			foreach ( $paths as $pdf ) {
				if ( file_exists( $pdf ) ) {
					// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Suppress errors for optional file deletion.
					@unlink( $pdf );
				}
			}
		}

		// Also delete the cached thumbnail if the helper exists.
		if ( class_exists( 'V_WPSA_Thumbnail' ) ) {
			try {
				V_WPSA_Thumbnail::delete_thumbnail( $domain );
			} catch ( Exception $e ) {
				// Intentionally ignore errors deleting thumbnails.
				unset( $e );
			}
		}

		return true;
	}

	/**
	 * Get PDF file path for a domain.
	 *
	 * @param string $domain The domain name.
	 * @param string $lang The language code (optional, not used in WP-native version).
	 * @return string The PDF file path.
	 */
	public static function get_pdf_file( $domain, $lang = null ) {
		// Use WordPress upload directory.
		if ( function_exists( 'wp_upload_dir' ) ) {
			$upload_dir = wp_upload_dir();
			$root       = rtrim( $upload_dir['basedir'], '\/' ) . '/seo-audit/pdf';
			// Use simplified filename structure: wp-content/uploads/seo-audit/pdf/{domain}.pdf.
			$file = $root . '/' . $domain . '.pdf';
			return $file;
		}

		// Fallback for non-WordPress environments (should not happen).
		$file = '/tmp/seo-audit/pdf/' . $domain . '.pdf';
		return $file;
	}

	/**
	 * Get value from array with default fallback.
	 *
	 * @param array $a The array.
	 * @param mixed $k The key.
	 * @param mixed $d The default value.
	 * @return mixed The value or default.
	 */
	public static function v( array $a, $k, $d = null ) {
		return isset( $a[ $k ] ) ? $a[ $k ] : $d;
	}

	/**
	 * Crop domain name to specified length.
	 *
	 * Shortens long domain names by keeping the start and end with separator in middle.
	 * Example: thelonglongdomain.com -> thelong...ain.com
	 *
	 * @param string $domain The domain name.
	 * @param int    $length Maximum length.
	 * @param string $separator The separator string.
	 * @return string The cropped domain name.
	 */
	public static function crop_domain( $domain, $length = 24, $separator = '...' ) {
		if ( mb_strlen( $domain ) < $length ) {
			return $domain;
		}
		$sep_length    = mb_strlen( $separator );
		$back_len      = 6;
		$available_len = $length - $sep_length - $back_len;
		// phpcs:ignore Squiz.PHP.CommentedOutCode.Found -- This is an arithmetic example comment, not code.
		// 20-3-6=11.
		$first_part = mb_substr( $domain, 0, $available_len );
		$last_part  = mb_substr( $domain, -$back_len );
		return $first_part . $separator . $last_part;
	}

	/**
	 * Perform a cURL request.

	 *
	 * @param string $url The URL to request.
	 * @param array  $headers Optional headers.
	 * @param bool   $cookie Whether to use cookies.
	 * @return mixed The response.
	 */
	public static function curl( $url, array $headers = array(), $cookie = false ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init -- Legacy code, will be migrated to wp_remote_get().
		$ch = curl_init( $url );
		if ( $cookie ) {
			// Use WP uploads folder for cookies.
			if ( function_exists( 'wp_upload_dir' ) ) {
				$upload_dir = wp_upload_dir();
				$cookie_dir = rtrim( $upload_dir['basedir'], "\/'" ) . '/seo-audit/cookies/';
				if ( ! is_dir( $cookie_dir ) ) {
					// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Fallback directory creation.
					@mkdir( $cookie_dir, 0755, true );
				}
				$cookie = $cookie_dir . "cookie_{$cookie}.txt";
			} else {
				$cookie = false;
			}
		}
		$html = self::curl_exec( $ch, $headers, $cookie );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close -- Legacy code, will be migrated to wp_remote_get().
		curl_close( $ch );
		return $html;
	}

	/**
	 * Execute cURL request with options.
	 *
	 *
	 *     @param CurlHandle|resource $ch           The cURL handle (PHP 8+ uses CurlHandle, <8 uses resource).
	 *     @param array               $headers      Optional headers.
	 *     @param mixed               $cookie       Cookie file path or false.
	 *     @param int                 $maxredirect  Maximum redirects (by reference).
	 *     @return mixed The response.
	 */
	public static function curl_exec( $ch, $headers = array(), $cookie = false, &$maxredirect = null ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec -- Legacy code, will be migrated to wp_remote_get().
		return curl_exec( self::ch( $ch, $headers, $cookie, $maxredirect ) );
	}

	/**
	 * Configure cURL handle with options.
	 *
	 *
	 *     @param CurlHandle|resource $ch           The cURL handle (PHP 8+ uses CurlHandle, <8 uses resource).
	 *     @param array               $headers      Optional headers.
	 *     @param mixed               $cookie       Cookie file path or false.
	 *     @param int                 $maxredirect  Maximum redirects (by reference).
	 *     @return CurlHandle|resource The configured cURL handle.
	 */
	public static function ch( $ch, $headers = array(), $cookie = false, &$maxredirect = null ) {
		// phpcs:disable WordPress.WP.AlternativeFunctions -- Legacy code, will be migrated to wp_remote_get().
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 20 );

		if ( $cookie ) {
			curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookie );
			curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookie );
		}

		if ( ! empty( $headers ) ) {
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		}

		if ( isset( $headers['user_agent'] ) ) {
			$user_agent = $headers['user_agent'];
			unset( $headers['user_agent'] );
		} else {
			$user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		}

		curl_setopt( $ch, CURLOPT_USERAGENT, $user_agent );

		$mr = null === $maxredirect ? 5 : intval( $maxredirect );
		if ( '' === ini_get( 'open_basedir' ) && ( 'Off' === ini_get( 'safe_mode' ) || '' === ini_get( 'safe_mode' ) ) ) {
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, $mr > 0 );
			curl_setopt( $ch, CURLOPT_MAXREDIRS, $mr );
		} else {
			curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, false );
			$original_url = curl_getinfo( $ch, CURLINFO_EFFECTIVE_URL );
			$parsed       = wp_parse_url( $original_url );
			if ( ! $parsed ) {
				return false;
			}
			$scheme = isset( $parsed['scheme'] ) ? $parsed['scheme'] : '';
			$host   = isset( $parsed['host'] ) ? $parsed['host'] : '';

			if ( $mr > 0 ) {
				$newurl = $original_url;
				$rch    = curl_copy_handle( $ch );

				curl_setopt( $rch, CURLOPT_HEADER, true );
				curl_setopt( $rch, CURLOPT_NOBODY, true );
				curl_setopt( $rch, CURLOPT_FORBID_REUSE, false );
				curl_setopt( $rch, CURLOPT_RETURNTRANSFER, true );
				do {
					curl_setopt( $rch, CURLOPT_URL, $newurl );
					$header = curl_exec( $rch );
					if ( curl_errno( $rch ) ) {
						$code = 0;
					} else {
						$code = curl_getinfo( $rch, CURLINFO_HTTP_CODE );
						if ( in_array( $code, array( 301, 302, 307, 308 ), true ) ) {
							preg_match( '/Location:(.*?)\n/i', $header, $matches );
							$newurl = trim( array_pop( $matches ) );

							$parsed = wp_parse_url( $newurl );
							if ( ! $parsed ) {
								return false;
							}

							if ( ! isset( $parsed['scheme'] ) ) {
								$parsed['scheme'] = $scheme;
							} else {
								$scheme = $parsed['scheme'];
							}

							if ( ! isset( $parsed['host'] ) ) {
								$parsed['host'] = $host;
							} else {
								$host = $parsed['host'];
							}
							$newurl = self::unparse_http_url( $parsed );
						} else {
							$code = 0;
						}
					}
				} while ( $code && --$mr );
				curl_close( $rch );

				if ( ! $mr ) {
					if ( null === $maxredirect ) {
						return false;
					} else {
						$maxredirect = 0;
					}

					return false;
				}
				curl_setopt( $ch, CURLOPT_URL, $newurl );
			}
		}
		// phpcs:enable WordPress.WP.AlternativeFunctions
		return $ch;
	}

	/**
	 * Unparse URL array into URL string.
	 *
	 * @param array $parsed Parsed URL array.
	 * @return string|false URL string or false on error.
	 */
	public static function unparse_http_url( array $parsed ) {
		if ( ! isset( $parsed['host'] ) ) {
			return false;
		}
		// Use existing scheme if present, otherwise default to http:// (fallback only).
		$url = isset( $parsed['scheme'] ) ? $parsed['scheme'] . '://' : 'http://';
		if ( isset( $parsed['user'] ) ) {
			$url .= $parsed['user'];
			if ( isset( $parsed['pass'] ) ) {
				$url .= ':' . $parsed['pass'];
			}
			$url .= '@' . $parsed['host'];
		} else {
			$url .= $parsed['host'];
		}

		if ( isset( $parsed['port'] ) ) {
			$url .= ':' . $parsed['port'];
		}

		if ( isset( $parsed['path'] ) ) {
			$url .= $parsed['path'];
		}
		if ( isset( $parsed['query'] ) ) {
			$url .= '?' . $parsed['query'];
		}
		if ( isset( $parsed['fragment'] ) ) {
			$url .= '#' . $parsed['fragment'];
		}
		return $url;
	}

	/**
	 * Get final URL from curl info.
	 *
	 * @param array|false $curl_info Curl info array.
	 * @param string      $default Default URL.
	 * @return string Final URL.
	 */
	public static function curl_get_final_url( $curl_info, $default ) {
		if ( false === $curl_info ) {
			return $default;
		}
		if ( ! empty( $curl_info['redirect_url'] ) ) {
			return $curl_info['redirect_url'];
		}
		return self::v( $curl_info, 'url', $default );
	}

	/**
	 * Get scheme and host from URL.
	 *
	 * @param string $url URL to parse.
	 * @param string $default Default value if parsing fails.
	 * @return string Scheme and host or default.
	 */
	public static function url_get_scheme_host( $url, $default ) {
		$parsed = wp_parse_url( $url );
		if ( false === $parsed ) {
			return $default;
		}
		if ( ! isset( $parsed['scheme'], $parsed['host'] ) ) {
			return $default;
		}
		return $parsed['scheme'] . '://' . $parsed['host'] . '/';
	}

	/**
	 * Get headers from curl response.
	 *
	 * @param string $response Curl response.
	 * @return array Headers array.
	 */
	public static function get_headers_from_curl_response( $response ) {
		$headers     = array();
		$header_text = substr( $response, 0, strpos( $response, "\r\n\r\n" ) );
		foreach ( explode( "\r\n", $header_text ) as $i => $line ) {
			if ( 0 === $i ) {
				$headers['status']    = $line;
				$data                 = explode( ' ', $line );
				$headers['http_code'] = isset( $data[1] ) ? $data[1] : null;
			} else {
				list( $key, $value )           = explode( ': ', $line );
				$headers[ strtolower( $key ) ] = $value;
			}
		}
		return $headers;
	}

	/**
	 * Check if PSI (PageSpeed Insights) setting is active.
	 *
	 * @param string $k Key (device or categories).
	 * @param string $item Item to check.
	 * @return bool True if active, false otherwise.
	 */
	public static function is_psi_active( $k, $item ) {
		// In WordPress-native version, we'll use options or constants.
		// For now, return defaults matching original behavior.
		if ( 'device' === $k ) {
			return 'desktop' === $item; // Default to desktop.
		}
		if ( 'categories' === $k ) {
			// Default categories enabled.
			return in_array( $item, array( 'performance', 'accessibility', 'best-practices', 'seo' ), true );
		}
		return false;
	}

	/**
	 * Check if string starts with needle.
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle The string to search for.
	 * @return bool True if starts with, false otherwise.
	 */
	public static function starts_with( $haystack, $needle ) {
		return '' !== (string) $needle && 0 === strncmp( $haystack, $needle, strlen( $needle ) );
	}

	/**
	 * Get local config file if exists.
	 *
	 * @param string $config_name Config name.
	 * @return array Config array.
	 */
	public static function get_local_config_if_exists( $config_name ) {
		$dir        = dirname( dirname( __FILE__ ) ) . '/config';
		$conf_local = $dir . '/' . $config_name . '_local.php';
		$conf_prod  = $dir . '/' . $config_name . '.php';
		return file_exists( $conf_local ) ? require $conf_local : ( file_exists( $conf_prod ) ? require $conf_prod : array() );
	}

	/**
	 * HTML decode string.
	 *
	 * @param string $str String to decode.
	 * @return string Decoded string.
	 */
	public static function html_decode( $str ) {
		return html_entity_decode( (string) $str, ENT_QUOTES, 'UTF-8' );
	}

	/**
	 * Check if reCAPTCHA is enabled.
	 *
	 * @return bool Always false in WP-native version (reCAPTCHA not implemented yet).
	 */
	public static function is_recaptcha_enabled() {
		return false;
	}
}
