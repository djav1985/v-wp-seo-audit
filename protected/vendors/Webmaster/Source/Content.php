<?php
class Content {
	private $html;

	public function __construct($html) {
		$this -> html = $html;
	}

	public function issetFlash() {
		$pattern = "<object[^>]*>(.*?)</object>";
		return preg_match("#{$pattern}#is", $this -> html);
	}

	public function issetIframe() {
		$pattern = "<iframe[^>]*>(.*?)</iframe>";
		return preg_match("#{$pattern}#is", $this -> html);
	}

	public function getHeadings() {
		// the list of all possible h tags
		$headings = array(
			'h1' => array(),
			'h2' => array(),
			'h3' => array(),
			'h4' => array(),
			'h5' => array(),
			'h6' => array(),
		);
		$pattern = "<(h[1-6]{1})(?:[^>]*)>(.*?)</h[1-6]{1}(?:[^>]*)>";
		preg_match_all("#{$pattern}#is", $this -> html, $matches);
		$sizes = isset($matches[1]) ? $matches[1] : array();
		foreach($sizes as $id => $size) {
			$headings[strtolower($size)][] = html_entity_decode(strip_tags(trim($matches[2][$id])), ENT_QUOTES, 'UTF-8');
		}
		return $headings;
	}

	public function issetNestedTables() {
		$pattern = "<(td|th)(?:[^>]*)>(.*?)<table(?:[^>]*)>(.*?)</table(?:[^>]*)>(.*?)</(td|th)(?:[^>]*)>";
		return preg_match("#{$pattern}#is", $this -> html);
	}

	public function issetEmail() {
		$pattern="(?:[a-z0-9!#$%&'*+=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])";
		preg_match('#<body>(.*)</body>#is', $this -> html, $matches);
		$content = isset($matches[1]) ? $matches[1] : $this->html;
		return preg_match("/{$pattern}/is", $content);
	}

	public function issetInlineCss() {
		$pattern = "<(.+)style=\"[^\"].+\"[^>]*>(.*?)<\/[^>]*>";
		return preg_match("#{$pattern}#is", $this -> html);
	}
}