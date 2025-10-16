<?php
class SeoAnalyse {
	private $html;
	private $keywords = array();
	private $striped;

	public function __construct( $html ) {
		$this->html    = $html;
		$this->striped = Helper::stripTags( $html );
	}

	public function getHtmlRatio() {
		$text_len = mb_strlen( $this->striped );
		$html_len = mb_strlen( $this->html );
		if ( $html_len == 0 ) {
			return 0;
		}
		$ratio = $text_len * 100 / $html_len;
		return (float) sprintf( '%.02f', $ratio );
	}

}
