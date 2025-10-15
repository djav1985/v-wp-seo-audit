<?php
class Image {
	private $html;
	private $domain;
	private $images = array();
	private $altCount = 0;

	public function __construct ($html) {
		$this -> html = $html;
		$this -> setImages ();
	}

	public function setImages () {
		$pattern = "<img[^>]+>";
		preg_match_all("#{$pattern}#is", $this->html, $matches);
		$this -> images = $matches[0];
		foreach($this->images as $image) {
			if($this->issetAltAttr($image))
				$this->altCount++;
		}
	}

	public function issetAltAttr($tag) {
		return preg_match("#alt=(?:'([^']+)'|\"([^\"]+)\")#is", $tag);
	}

	public function getTotal() {
		return count($this -> images);
	}

	public function getAltCount() {
		return $this -> altCount;
	}

}