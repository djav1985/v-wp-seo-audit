<?php
class Favicon {

	private $html;
	private $favicon;
	private $domain;

	public function __construct($html, $domain) {
		// Try HTTPS first, fallback to HTTP.
		$this->favicon = 'https://' . $domain . '/favicon.ico';
		$this->domain = $domain;
		$this->html = $html;
	}

	public function getFavicon() {
		$favicon = null;
		if(!$favicon = $this->getFromHtmlHead()) {
			$favicon = $this->getFromHeaders();
		}
		return $favicon;
	}

	private function getFromHtmlHead() {
		$matches = array();
		// Search for <link rel="icon" type="image/png" href="http://example.com/icon.png" />
		preg_match('#<link[^>]*rel=["\'](.*?icon.*?)["\'][^>]*href=["\']([^"\']+)["\'][^>]*>#i', $this->html, $matches);
		if (count($matches) > 2) {
			return trim($matches[2]);
		}
		// Order of attributes could be swapped around: <link type="image/png" href="http://example.com/icon.png" rel="icon" />
		preg_match('#<link[^>]*href=["\']([^"\']+)["\'][^>]*rel=["\'](.*?icon.*?)["\'][^>]*>#i', $this->html, $matches);
		if (count($matches) > 1) {
			return trim($matches[1]);
		}
		// No match
		return null;
	}

	private function getFromHeaders() {
		// Try HTTPS first.
		$https_favicon = 'https://' . $this->domain . '/favicon.ico';
		$headers = @get_headers($https_favicon, true);
		
		if($headers && $this->isValidFaviconResponse($headers)) {
			return $https_favicon;
		}
		
		// Fallback to HTTP.
		$http_favicon = 'http://' . $this->domain . '/favicon.ico';
		$headers = @get_headers($http_favicon, true);
		
		if($headers && $this->isValidFaviconResponse($headers)) {
			return $http_favicon;
		}
		
		// No favicon found.
		return null;
	}
	
	private function isValidFaviconResponse($headers) {
		if(!$headers) {
			return false;
		}
		
		$moved = "301|302|303|307|308";
		
		// If all is ok
		if(strpos($headers[0], "200") !== false) {
			return true;
		}
		
		// If favicon moved, follow the Location header
		if(preg_match("#({$moved})#i", $headers[0])) {
			// Check if Location header exists
			if(isset($headers['Location'])) {
				// Location could be an array of redirects
				$location = is_array($headers['Location']) ? end($headers['Location']) : $headers['Location'];
				// We found a redirect location, consider this valid
				return !empty($location);
			}
		}
		
		return false;
	}
}