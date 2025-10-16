<?php
/**
 * Score calculator for analyzer output.
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
		exit;
}

/**
 * Calculate score breakdowns using analyzer data and rate configuration.
 */
class V_WPSA_Score_Calculator {


		/**
		 * Rate provider instance.
		 *
		 * @var RateProvider|null
		 */
		protected $rate_provider;

		/**
		 * Rate configuration array.
		 *
		 * @var array
		 */
		protected $rates = array();

		/**
		 * Maximum number of keywords to consider for consistency.
		 *
		 * @var int
		 */
		protected $consistency_limit = 0;

		/**
		 * Constructor.
		 *
		 * @param RateProvider|null $rate_provider Optional pre-built rate provider.
		 * @param array             $rates         Optional rate configuration.
		 * @param int|null          $limit         Optional keyword consistency limit override.
		 */
	public function __construct( $rate_provider = null, $rates = null, $limit = null ) {
		if ( null === $rate_provider && class_exists( 'RateProvider' ) ) {
				$rate_provider = new RateProvider();
		}

			$this->rate_provider = ( $rate_provider instanceof RateProvider ) ? $rate_provider : null;

		if ( null !== $rates && is_array( $rates ) ) {
				$this->rates = $rates;
		} elseif ( $this->rate_provider ) {
				$this->rates = $this->rate_provider->getRates();
		}

		if ( null === $limit ) {
				$limit = (int) V_WPSA_Config::get( 'analyzer.consistency_count' );
		}

			$this->consistency_limit = max( 0, (int) $limit );
	}

		/**
		 * Calculate score breakdown using analyzer report data.
		 *
		 * @param array $report_data Report data from V_WPSA_DB::get_website_report_data().
		 *
		 * @return array
		 */
	public function calculate( array $report_data ) {
			$result = array(
				'total'      => 0.0,
				'categories' => array(),
			);

			if ( empty( $this->rates ) || ! $this->rate_provider ) {
					return $result;
			}

			$content     = $this->get_section( $report_data, 'content' );
			$document    = $this->get_section( $report_data, 'document' );
			$links       = $this->get_section( $report_data, 'links' );
			$meta        = $this->get_section( $report_data, 'metatags' );
			$issetobject = $this->get_section( $report_data, 'issetobject' );
			$misc        = $this->get_section( $report_data, 'misc' );
			$cloud       = $this->get_section( $report_data, 'cloud' );
			$w3c         = $this->get_section( $report_data, 'w3c' );

			foreach ( $this->rates as $key => $definition ) {
					$evaluation = array(
						'points' => 0.0,
						'advice' => _RATE_ERROR,
					);

					if ( 'wordConsistency' === $key ) {
							$matrix     = isset( $cloud['matrix'] ) ? $cloud['matrix'] : array();
							$evaluation = $this->rate_provider->evaluateCompareMatrix( $matrix, $this->consistency_limit );
					} elseif ( 'w3c' === $key ) {
							$errors     = isset( $w3c['errors'] ) ? (int) $w3c['errors'] : 0;
							$warnings   = isset( $w3c['warnings'] ) ? (int) $w3c['warnings'] : 0;
							$evaluation = $this->rate_provider->evaluateW3c( $errors, $warnings );
					} elseif ( is_array( $definition ) ) {
							$value      = $this->resolve_range_value( $key, $content, $document, $links, $meta );
							$evaluation = $this->rate_provider->evaluateCompareArray( $key, $value );
					} else {
							$condition  = $this->resolve_boolean_condition( $key, $content, $document, $links, $meta, $issetobject, $misc );
							$evaluation = $this->rate_provider->evaluateCompare( $key, $condition );
					}

					$result['categories'][ $key ] = array(
						'points' => $this->format_points( $evaluation['points'] ),
						'advice' => $evaluation['advice'],
					);

					$result['total'] += (float) $evaluation['points'];
			}

			$result['total'] = $this->format_points( min( 100, $result['total'] ) );

			return $result;
	}

		/**
		 * Normalize score precision.
		 *
		 * @param float $points Points value.
		 *
		 * @return float
		 */
	protected function format_points( $points ) {
			return round( (float) $points, 2 );
	}

		/**
		 * Retrieve a section from report data.
		 *
		 * @param array  $report_data Report data array.
		 * @param string $key         Section key.
		 *
		 * @return array
		 */
	protected function get_section( array $report_data, $key ) {
			return ( isset( $report_data[ $key ] ) && is_array( $report_data[ $key ] ) ) ? $report_data[ $key ] : array();
	}

		/**
		 * Resolve value for ranged categories.
		 *
		 * @param string $key      Rate key.
		 * @param array  $content  Content section.
		 * @param array  $document Document section.
		 * @param array  $links    Links section.
		 * @param array  $meta     Meta section.
		 *
		 * @return float
		 */
	protected function resolve_range_value( $key, array $content, array $document, array $links, array $meta ) {
		switch ( $key ) {
			case 'title':
				$title = isset( $meta['title'] ) ? $meta['title'] : '';
				$title = $this->decode_string( $title );
				return $this->safe_length( $title );
			case 'description':
					$description = isset( $meta['description'] ) ? $meta['description'] : '';
					$description = $this->decode_string( $description );
				return $this->safe_length( $description );
			case 'htmlratio':
				return isset( $document['htmlratio'] ) ? (float) $document['htmlratio'] : 0.0;
			case 'cssCount':
				return isset( $document['css'] ) ? (int) $document['css'] : 0;
			case 'jsCount':
				return isset( $document['js'] ) ? (int) $document['js'] : 0;
			default:
				return 0;
		}
	}

		/**
		 * Resolve boolean condition for single-value categories.
		 *
		 * @param string $key          Rate key.
		 * @param array  $content      Content section.
		 * @param array  $document     Document section.
		 * @param array  $links        Links section.
		 * @param array  $meta         Meta section.
		 * @param array  $issetobject  Issetobject section.
		 * @param array  $misc         Miscellaneous section.
		 *
		 * @return bool
		 */
	protected function resolve_boolean_condition( $key, array $content, array $document, array $links, array $meta, array $issetobject, array $misc ) {
		switch ( $key ) {
			case 'noFlash':
				return empty( $issetobject['flash'] );
			case 'noIframe':
				return empty( $issetobject['iframe'] );
			case 'issetHeadings':
				return ! empty( $content['isset_headings'] );
			case 'noNestedtables':
				return empty( $issetobject['nestedtables'] );
			case 'noInlineCSS':
				return empty( $issetobject['inlinecss'] );
			case 'issetFavicon':
				return ! empty( $document['favicon'] );
			case 'noEmail':
				return empty( $issetobject['email'] );
			case 'keywords':
				return ! empty( $meta['keyword'] );
			case 'imgHasAlt':
					$total_img = isset( $content['total_img'] ) ? (int) $content['total_img'] : 0;
					$total_alt = isset( $content['total_alt'] ) ? (int) $content['total_alt'] : 0;
				return $total_img === $total_alt;
			case 'isFriendlyUrl':
				return ! empty( $links['friendly'] );
			case 'noUnderScore':
				return empty( $links['isset_underscore'] );
			case 'issetInternalLinks':
				return ! empty( $links['internal'] );
			case 'hasRobotsTxt':
				return ! empty( $issetobject['robotstxt'] );
			case 'hasSitemap':
				return ! empty( $misc['sitemap'] );
			case 'hasGzip':
				return ! empty( $issetobject['gzip'] );
			case 'hasAnalytics':
				return ! empty( $misc['analytics'] );
			case 'charset':
				return ! empty( $document['charset'] );
			case 'viewport':
				return ! empty( $issetobject['viewport'] );
			case 'dublincore':
				return ! empty( $issetobject['dublincore'] );
			case 'ogmetaproperties':
				return ! empty( $meta['ogproperties'] );
			case 'doctype':
				return ! empty( $document['doctype'] );
			case 'isPrintable':
				return ! empty( $issetobject['printable'] );
			case 'issetAppleIcons':
				return ! empty( $issetobject['appleicons'] );
			case 'noDeprecated':
				return empty( $content['deprecated'] );
			case 'lang':
				return ! empty( $document['lang'] );
			default:
				return false;
		}
	}

		/**
		 * Decode HTML entities safely.
		 *
		 * @param string $value String value.
		 *
		 * @return string
		 */
	protected function decode_string( $value ) {
		if ( class_exists( 'V_WPSA_Utils' ) && method_exists( 'V_WPSA_Utils', 'html_decode' ) ) {
				return V_WPSA_Utils::html_decode( $value );
		}

			return html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
	}

		/**
		 * Multibyte-safe string length helper.
		 *
		 * @param string $value String value.
		 *
		 * @return int
		 */
	protected function safe_length( $value ) {
			return function_exists( 'mb_strlen' ) ? mb_strlen( (string) $value ) : strlen( (string) $value );
	}
}
