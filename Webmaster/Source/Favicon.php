<?php
class Favicon {

	private $html;
	private $favicon;

	public function __construct($html, $domain) {
		$this -> favicon = 'http://'.$domain.'/favicon.ico';
		$this -> html = $html;
	}

	public function getFavicon() {
		$favicon = null;
		if(!$favicon = $this -> getFromHtmlHead()) {
			$favicon = $this -> getFromHeaders();
		}
		return $favicon;
	}

	private function getFromHtmlHead() {
		$matches = array();
		// Search for <link rel="icon" type="image/png" href="http://example.com/icon.png" />
		preg_match('#<link.*?rel=("|\').*icon("|\').*?href=("|\')(.*?)("|\')#i', $this -> html, $matches);
		if (count($matches) > 4) {
			return trim($matches[4]);
		}
		// Order of attributes could be swapped around: <link type="image/png" href="http://example.com/icon.png" rel="icon" />
		preg_match('#<link.*?href=("|\')(.*?)("|\').*?rel=("|\').*icon("|\')#i', $this -> html, $matches);
		if (count($matches) > 2) {
			return trim($matches[2]);
		}
		// No match
		return null;
	}

	private function getFromHeaders() {
		$headers = @get_headers($this->favicon, true);
        if(!$headers) {
            return null;
        }
		$moved = "301|302|303|307";
		// If all is ok
		if($headers && strpos($headers[0], "200") !== false)
			return $this->favicon;
		// If favicon moved
		else if(preg_match("#({$moved})#i", $headers[0])) {
			if(!isset($headers['Location']))
				return null;
			return is_array($headers['Location']) ? current($headers['Location']) : $headers['Location'];
		} else
        // No match
        return null;
	}
}