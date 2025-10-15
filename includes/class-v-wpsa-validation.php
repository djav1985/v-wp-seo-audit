<?php
/**
 * Domain Validation Class
 *
 * WordPress-native domain validation functions.
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class V_WPSA_Validation
 */
class V_WPSA_Validation {

	/**
	 * Validate domain using WordPress patterns.
	 *
	 * @param string $domain The domain to validate.
	 * @return array Array with 'valid' boolean, 'domain', 'idn', 'ip', and 'errors' array.
	 */
	public static function validate_domain( $domain ) {
		$errors = array();
		$result = array(
			'valid'  => false,
			'domain' => '',
			'idn'    => '',
			'ip'     => '',
			'errors' => array(),
		);

		// Sanitize and trim domain.
		$domain = self::sanitize_domain( $domain );

		if ( empty( $domain ) ) {
			$errors[]         = __( 'Please enter a domain name', 'v-wpsa' );
			$result['errors'] = $errors;
			return $result;
		}

		// Store IDN (unicode) version before punycode encoding.
		$idn = $domain;

		// Convert IDN to punycode if needed.
		$domain = self::encode_idn( $domain );

		// Validate domain format.
		if ( ! self::is_valid_domain_format( $domain ) ) {
			$errors[]         = __( 'Invalid domain format. Please enter a valid domain name (e.g., example.com)', 'v-wpsa' );
			$result['errors'] = $errors;
			return $result;
		}

		// Check banned websites.
		$banned_error = self::check_banned_domain( $idn );
		if ( $banned_error ) {
			$errors[]         = $banned_error;
			$result['errors'] = $errors;
			return $result;
		}

		// Check if domain is reachable.
		$ip   = gethostbyname( $domain );
		$long = ip2long( $ip );
		if ( -1 === $long || false === $long ) {
			/* translators: %s: domain name */
			$errors[]         = sprintf( __( 'Could not reach host: %s', 'v-wpsa' ), $domain );
			$result['errors'] = $errors;
			return $result;
		}

		// All validation passed.
		$result['valid']  = true;
		$result['domain'] = $domain;
		$result['idn']    = $idn;
		$result['ip']     = $ip;

		return $result;
	}

	/**
	 * Sanitize domain input.
	 *
	 * @param string $domain The domain to sanitize.
	 * @return string Sanitized domain.
	 */
	public static function sanitize_domain( $domain ) {
		// Remove http:// or https:// prefix.
		$domain = preg_replace( '#^https?://#i', '', $domain );

		// Remove www. prefix.
		$domain = preg_replace( '#^www\.#i', '', $domain );

		// Remove trailing slash.
		$domain = rtrim( $domain, '/' );

		// Remove any path after domain.
		$domain = preg_replace( '#/.*$#', '', $domain );

		// Trim whitespace.
		$domain = trim( $domain );

		return $domain;
	}

	/**
	 * Encode IDN (Internationalized Domain Name) to punycode.
	 *
	 * @param string $domain The domain to encode.
	 * @return string Encoded domain.
	 */
	public static function encode_idn( $domain ) {
		// Check if IDN class is available.
		if ( class_exists( 'IDN' ) ) {
			$idn = new IDN();
			return $idn->encode( $domain );
		}

		// Fallback: use PHP's idn_to_ascii if available.
		if ( function_exists( 'idn_to_ascii' ) ) {
			$encoded = idn_to_ascii( $domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46 );
			return $encoded ? $encoded : $domain;
		}

		// No encoding available, return as-is.
		return $domain;
	}

	/**
	 * Check if domain has valid format.
	 *
	 * @param string $domain The domain to check.
	 * @return bool True if valid, false otherwise.
	 */
	public static function is_valid_domain_format( $domain ) {
		// Domain regex: alphanumeric and hyphens, with dots separating parts.
		// Each part can be 1-62 characters.
		$pattern = '/^[a-z\d-]{1,62}\.[a-z\d-]{1,62}(\.[a-z\d-]{1,62})*$/i';
		return (bool) preg_match( $pattern, $domain );
	}

	/**
	 * Check if domain is in banned list.
	 *
	 * @param string $domain The domain to check.
	 * @return string|false Error message if banned, false otherwise.
	 */
	public static function check_banned_domain( $domain ) {
		$restriction_file = v_wpsa_PLUGIN_DIR . 'config/domain_restriction.php';

		if ( ! file_exists( $restriction_file ) ) {
			return false;
		}

		$banned_patterns = include $restriction_file;

		if ( ! is_array( $banned_patterns ) ) {
			return false;
		}

		foreach ( $banned_patterns as $pattern ) {
			if ( preg_match( "#{$pattern}#i", $domain ) ) {
				return __( 'Error Code 103: This domain is not allowed', 'v-wpsa' );
			}
		}

		return false;
	}
}
