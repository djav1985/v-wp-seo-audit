<?php
class Image {
	private $html;
	private $domain;
	private $images           = array();
	private $altCount         = 0;
	private $imagesMissingAlt = array();

	public function __construct( $html ) {
		$this->html = $html;
		$this->setImages();
	}

	public function setImages() {
		$pattern = '<img[^>]+>';
		preg_match_all( "#{$pattern}#is", $this->html, $matches );
		$this->images = $matches[0];
		foreach ( $this->images as $image ) {
			if ( $this->issetAltAttr( $image ) ) {
				$this->altCount++;
			} else {
				// Extract src attribute for images missing alt
				$src = $this->extractSrc( $image );
				if ( $src ) {
					$this->imagesMissingAlt[] = $src;
				}
			}
		}
	}

	public function issetAltAttr( $tag ) {
		return preg_match( "#alt=(?:'([^']+)'|\"([^\"]+)\")#is", $tag );
	}

	private function extractSrc( $tag ) {
		if ( preg_match( "#src=(?:'([^']+)'|\"([^\"]+)\")#is", $tag, $matches ) ) {
			return ! empty( $matches[1] ) ? $matches[1] : ( ! empty( $matches[2] ) ? $matches[2] : '' );
		}
		return '';
	}

	public function getTotal() {
		return count( $this->images );
	}

	public function getAltCount() {
		return $this->altCount;
	}

	public function getImagesMissingAlt() {
		return $this->imagesMissingAlt;
	}

}
