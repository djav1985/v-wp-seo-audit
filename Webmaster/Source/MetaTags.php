<?php
class MetaTags {
	private $html;
	private $title;
	private $description;
	private $keywords;

	private $charsetSwap = array(
		'WIN1251'  => 'Windows-1251',
		'UTF8'     => 'UTF-8',
		'X-EUC-JP' => 'EUC-JP',
	);

	public function __construct( $html ) {
		$this->html = $html;
	}

	public function getTitle() {
		if ( $this->title === null ) {
			$pattern = '<title.*?>(.*?)</title>';
			preg_match( "#{$pattern}#is", $this->html, $matches );
			$this->title = isset( $matches[1] ) ? trim( $matches[1] ) : '';
		}
		return $this->title;
	}

	public function getDescription() {
		if ( $this->description === null ) {
			$this->description = $this->getMetaName( 'description' );
		}
		return $this->description;
	}

	public function getKeywords() {
		if ( $this->keywords === null ) {
			$this->keywords = $this->getMetaName( 'keywords' );
		}
		return $this->keywords;
	}

	public function getCharset() {
		$pattern = '<meta[^>]+charset=[\'"]?(.*?)[\'"]?[\/\s>]';
		preg_match( "#{$pattern}#is", $this->html, $matches );
		$charset = isset( $matches[1] ) ? mb_strtoupper( trim( $matches[1] ) ) : null;
		if ( empty( $charset ) ) {
			return null;
		}
		if ( strpos( $charset, ';' ) !== false ) {
			$parts   = explode( ';', $charset );
			$charset = isset( $parts[0] ) ? $parts[0] : $charset;
		}
		if ( isset( $this->charsetSwap[ $charset ] ) ) {
			$charset = $this->charsetSwap[ $charset ];
		}
		return $charset;
	}

	public function getViewPort() {
		return $this->getMetaName( 'viewport' );
	}

	public function getDublinCore() {
		return $this->getMetaName( "dc\\.[^'|^\"]+" );
	}

	public function getOgMetaProperties() {
		return $this->getMetaProperties( 'og' );
	}

	private function getMetaProperties( $type ) {
		$og      = array();
		$pattern = "<meta[^>]+property=(?:'$type:([^']+)'|\"$type:([^\"]+)\")[^>]+content=(?:'([^']+)'|\"([^\"]+)\")[^>]*>";
		preg_match_all( "#{$pattern}#is", $this->html, $matches );
		if ( ! isset( $matches[0] ) ) {
			return $og;
		}
		$properties = $this->overlayArrays( $matches[1], $matches[2] );
		$content    = $this->overlayArrays( $matches[3], $matches[4] );
		foreach ( $properties as $id => $property ) {
			$og[ $property ] = $content[ $id ];
		}
		return $og;
	}

	private function getMetaName( $name, $default = null ) {
		// $pattern = "<meta[^>]+name=('$name'|\"$name\")[^>]+content=('[^\']*'|\"[^\"]*\")[^>]*>";
		$tagPattern = "<meta[^>]+name=('$name'|\"$name\")([^>]+)?>";
		preg_match( "#{$tagPattern}#is", $this->html, $tagMatches );
		if ( ! isset( $tagMatches[0] ) ) {
			return $default;
		}
		$contentPattern = "<meta[^>]+content=('[^\']*'|\"[^\"]*\")([^>]+)?>";
		preg_match( "#{$contentPattern}#is", $tagMatches[0], $contentMatches );
		return isset( $contentMatches[1] ) ? trim( $contentMatches[1], "\"'" ) : $default;
	}

	private function overlayArrays( array $a, array $b ) {
		$c = array();
		foreach ( $a as $k => $v ) {
			$c[ $k ] = empty( $v ) ? $b[ $k ] : $v;
		}
		return $c;
	}
}
