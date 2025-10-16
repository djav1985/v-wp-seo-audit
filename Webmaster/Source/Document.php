<?php
/**
 * File: Document.php
 *
 * Description: HTML document analysis.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
 */

class Document {
	private $html;
	// DOCTYPE Declarations. See: http://www.w3.org/QA/2002/04/valid-dtd-list.html
	private $doctypes = array(
		/* (X)HTML Doctype Declarations List */
		'HTML 4.01 Strict'           => array(
			'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"(?:[^>]*)>',
		),
		'HTML 4.01 Transitional'     => array(
			'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"(?:[^>]*)>',
		),
		'HTML 4.01 Frameset'         => array(
			'<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN"(?:[^>]*)>',
		),
		'XHTML 1.0 Strict'           => array(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"(?:[^>]*)>',
		),
		'XHTML 1.0 Transitional'     => array(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"(?:[^>]*)>',
		),
		'XHTML 1.0 Frameset'         => array(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"(?:[^>]*)>',
		),
		'XHTML 1.1 - DTD'            => array(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"(?:[^>]*)>',
		),
		'XHTML Basic 1.1'            => array(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.1//EN"(?:[^>]*)>',
		),
		/* HTML5 */
		'HTML 5'                     => array(
			'<!DOCTYPE HTML>',
		),
		/* MathML Doctype Declarations */
		'MathML 2.0 - DTD'           => array(
			'<!DOCTYPE math PUBLIC "-//W3C//DTD MathML 2.0//EN"(?:[^>]*)>',
		),
		'MathML 1.01 - DTD'          => array(
			'<!DOCTYPE math SYSTEM(?:[^>]*)>',
		),
		/* Compound documents doctype declarations */
		'XHTML + MathML + SVG - DTD' => array(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN"(?:[^>]*)>',
		),
		'XHTML + MathML + SVG Profile (XHTML as the host language) - DTD' => array(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN"(?:[^>]*)>',
		),
		'XHTML + MathML + SVG Profile (Using SVG as the host) - DTD' => array(
			'<!DOCTYPE svg:svg PUBLIC "-//W3C//DTD XHTML 1.1 plus MathML 2.0 plus SVG 1.1//EN"(?:[^>]*)>',
		),
		/* Optional doctype declarations */
		'SVG 1.1 Full - DTD'         => array(
			'<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN"(?:[^>]*)>',
		),
		'SVG 1.0 - DTD'              => array(
			'<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.0//EN"(?:[^>]*)>',
		),
		'SVG 1.1 Basic - DTD'        => array(
			'<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Basic//EN"(?:[^>]*)>',
		),
		'SVG 1.1 Tiny - DTD'         => array(
			'<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1 Tiny//EN"(?:[^>]*)>',
		),
		/* Historical doctype declarations */
		'HTML 2.0 - DTD'             => array(
			'<!DOCTYPE html PUBLIC "-//IETF//DTD HTML 2.0//EN">',
		),
		'HTML 3.2 - DTD'             => array(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 3.2 Final//EN">',
		),
		'XHTML Basic 1.0 - DTD'      => array(
			'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN"(?:[^>]*)>',
		),
	);

	private $deprectaedTags = array(
		'acronym',
		'applet',
		'basefont',
		'big',
		'center',
		'dir',
		'font',
		'frame',
		'frameset',
		'isindex',
		'noframes',
		's',
		'strike',
		'tt',
		'u',
	);

	public function __construct( $html ) {
		$this->html = $html;
	}

	public function getDoctype() {
		$pattern = '<!DOCTYPE[^>]*>';
		preg_match( "#{$pattern}#is", $this->html, $matches );
		if ( ! isset( $matches[0] ) ) {
			return false;
		}
		$type = preg_replace( '/\s+/', ' ', trim( $matches[0] ) );
		foreach ( $this->doctypes as $doctype => $patterns ) {
			foreach ( $patterns as $pattern ) {
				preg_match( "#{$pattern}#is", $type, $matches );
			}
			if ( $matches ) {
				return $doctype;
			}
		}
		return false;
	}

	public function isPrintable() {
		$pattern = "<link.*?media=('(print[^\']*?)'|\"(print[^\"]*?)\").*?>";
		return preg_match( "#{$pattern}#is", $this->html );
	}

	public function issetAppleIcon() {
		$pattern = "<link.*?rel=('(apple-touch-icon[^\']*?)'|\"(apple-touch-icon[^\"]*?)\").*?>";
		return preg_match( "#{$pattern}#is", $this->html );
	}

	public function getDeprecatedTags() {
		$deprecated = array();
		$pattern    = '<(' . implode( '|', $this->deprectaedTags ) . ')( [^>]*)?>';
		preg_match_all( "#{$pattern}#is", $this->html, $matches );
		foreach ( $matches[1] as $tag ) {
			if ( isset( $deprecated[ $tag ] ) ) {
				$deprecated[ $tag ]++;
			} else {
				$deprecated[ $tag ] = 1;
			}
		}
		return $deprecated;
	}

	public function getLanguageID() {
		$pattern = '<html[^>]+lang=[\'"]?(.*?)[\'"]?[\/\s>]';
		preg_match( "#{$pattern}#is", $this->html, $matches );
		if ( isset( $matches[1] ) ) {
			return trim( mb_substr( $matches[1], 0, 5 ) );
		}
		$pattern = '<meta[^>]+http-equiv=[\'"]?content-language[\'"]?[^>]+content=[\'"]?(.*?)[\'"]?[\/\s>]';
		preg_match( "#{$pattern}#is", $this->html, $matches );
		return isset( $matches[1] ) ? trim( mb_substr( $matches[1], 0, 5 ) ) : null;
	}

	public function getCssFilesCount() {
		$tagPattern = '<link[^>]*>';
		$cssPattern = '(?=.*\bstylesheet\b)(?=.*\bhref=("[^"]*"|\'[^\']*\')).*';
		$cnt        = 0;
		preg_match_all( "#{$tagPattern}#is", $this->html, $matches );
		if ( ! isset( $matches[0] ) ) {
			return $cnt;
		}
		foreach ( $matches[0] as $tag ) {
			if ( preg_match( "#{$cssPattern}#is", $tag ) ) {
				$cnt++;
			}
		}
		return $cnt;
	}


	public function getJsFilesCount() {
		$tagPattern = '<script[^>]*>';
		$jsPattern  = 'src=("[^"]*"|\'[^\']*\')';
		$cnt        = 0;
		preg_match_all( "#{$tagPattern}#is", $this->html, $matches );
		if ( ! isset( $matches[0] ) ) {
			return $cnt;
		}
		foreach ( $matches[0] as $tag ) {
			if ( preg_match( "#{$jsPattern}#is", $tag ) ) {
				$cnt++;
			}
		}
		return $cnt;
	}
}
