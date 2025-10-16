<?php
class Optimization {
	private $domain;

	private $robotsTxt = null;

	private $final_url;

	public function __construct( $domain, $final_url ) {
		$this->domain    = $domain;
		$this->final_url = $final_url;
	}

	public function getSitemap() {
		$robotsTxt = $this->getRobotsTxt();

		$pattern  = "/Sitemap: ([^\r\n]*)/is";
		$sitemaps = array();
		preg_match_all( $pattern, $robotsTxt, $matches );

		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $sitemap ) {
				$sitemaps[] = $sitemap;
			}
		} else {
			$urlMap        = array();
			$urlMap[]      = 'https://' . $this->domain . '/sitemap.xml';
			$urlMap[]      = 'http://' . $this->domain . '/sitemap.xml';
			$acceptedCodes = array(
				200,
				201,
				202,
				203,
				204,
				205,
				206,
				207,
				208,
				226,
				300,
				301,
				302,
				303,
				304,
				305,
				306,
				307,
				308,
			);
			foreach ( $urlMap as $url ) {
				$ch = curl_init( $url );
				curl_setopt( $ch, CURLOPT_HEADER, 1 );
				curl_setopt( $ch, CURLOPT_NOBODY, true );
				curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
				$ch = V_WPSA_Utils::ch( $ch );

				if ( false === $ch ) {
					continue;
				}
				curl_exec( $ch );
				if ( curl_errno( $ch ) ) {
					continue;
				}
				$i = (array) curl_getinfo( $ch );

				if ( isset( $i['http_code'] ) and in_array( (int) $i['http_code'], $acceptedCodes ) ) {
					$sitemaps[] = V_WPSA_Utils::v( $i, 'url', $url );
				}
			}
		}

		return array_unique( $sitemaps );
	}

	public function getRobotsTxt() {
		if ( $this->robotsTxt !== null ) {
			return $this->robotsTxt;
		}
		$url = 'http://' . $this->domain . '/robots.txt';

		$ch = V_WPSA_Utils::ch( curl_init( $url ) );
		if ( false === $ch ) {
			$this->robotsTxt = false;
			return $this->robotsTxt;
		}

		$response = curl_exec( $ch );
		if ( curl_errno( $ch ) ) {
			$this->robotsTxt = false;
			return $this->robotsTxt;
		}
		$info = (array) curl_getinfo( $ch );
		if ( ! isset( $info['http_code'] ) and ( $info['http_code'] != '200' ) ) {
			$this->robotsTxt = false;
		} else {
			$this->robotsTxt = $response;
		}
		return $this->robotsTxt;
	}

	public function hasRobotsTxt() {
		$r = $this->getRobotsTxt();
		return $r !== false;
	}

	public function hasGzipSupport() {
		// Try HEAD request first - it's faster.
		// Disable automatic decompression to detect encoding even if PHP doesn't support it.
		$ch = curl_init( $this->final_url );
		curl_setopt( $ch, CURLOPT_HEADER, 1 );
		curl_setopt( $ch, CURLOPT_NOBODY, true ); // HEAD request.
		curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'HEAD' );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_ENCODING, '' ); // Disable auto-decompression.
		$ch = V_WPSA_Utils::ch(
			$ch,
			array(
				'Accept-Encoding: gzip, deflate, br, zstd',
			)
		);

		if ( false === $ch ) {
			return false;
		}

		$response = (string) curl_exec( $ch );
		curl_close( $ch );

		// Check raw header text for Content-Encoding to detect compression
		// even when PHP doesn't support decoding it (e.g., Brotli, Zstandard).
		if ( preg_match( '/^Content-Encoding:\s*(gzip|deflate|br|zstd)/im', $response ) ) {
			return true;
		}

		// If HEAD request didn't show encoding, try GET request as fallback.
		// Some servers only send Content-Encoding with actual content.
		$ch = curl_init( $this->final_url );
		curl_setopt( $ch, CURLOPT_HEADER, 1 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_ENCODING, '' ); // Disable auto-decompression.
		$ch = V_WPSA_Utils::ch(
			$ch,
			array(
				'Accept-Encoding: gzip, deflate, br, zstd',
			)
		);

		if ( false === $ch ) {
			return false;
		}

		$response = (string) curl_exec( $ch );
		curl_close( $ch );

		// Check raw header text for Content-Encoding.
		if ( preg_match( '/^Content-Encoding:\s*(gzip|deflate|br|zstd)/im', $response ) ) {
			return true;
		}

		return false;
	}

}
