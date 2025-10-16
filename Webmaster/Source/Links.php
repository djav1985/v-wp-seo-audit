<?php
/**
 * File: Links.php
 *
 * Description: Link analysis and validation.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
 */

class Links {
	private $html;
	private $domain;
	private $links             = array();
	private $internal          = 0;
	private $external_dofollow = 0;
	private $external_nofollow = 0;
	private $files_count       = 0;
	private $isset_underscore  = false;
	private $friendly          = true;
	private $idn;

	// Format's of web pages.
	private $pageExt = array(
		'rna',
		'xml',
		'html',
		'htm',
		'xhtml',
		'xht',
		'mhtml',
		'mht',
		'maff',
		'asp',
		'aspx',
		'bml',
		'cfm',
		'cgi',
		'ihtml',
		'jsp',
		'las',
		'lasso',
		'lassoapp',
		'pl',
		'php',
		'php3',
		'phtml',
		'shtml',
		'stm',
		'atom',
		'xml',
		'eml',
		'jsonld',
		'json',
		'metalink',
		'met',
		'rss',
		'markdown',
	);

	public function __construct( $html, $domain, $idn ) {
		$this->html   = $html; // strip_tags($html, "<a>");
		$this->domain = $domain;
		$this->idn    = $idn;
		$this->links  = $this->setLinks();
	}

	public function setLinks() {
		$l       = array();
		$u_flag  = $f_flag = true;
		$pattern = "<a(?:[^>]*)href=(?:'([^']+)'|\"([^\"]+)\")(?:[^>]*)>([^<]*)?<\/a>";
		preg_match_all( "#{$pattern}#is", $this->html, $matches );
		$tags  = $matches[0];
		$links = $this->overlayArrays( $matches[1], $matches[2] );
		$names = $matches[3];
		$juice = array();

		// Remove duplicates
		$links = array_unique( $links );

		// Remove not links
		$this->removeNotLinks( $links );

		// Normalize link
		$this->normalizeLink( $links );
		// Get juice from links
		$juice = $this->getJuiceFromTag( $tags );

		foreach ( $links as $id => $link ) {
			$segment = @parse_url( $link );
			if ( $segment == false ) {
				continue;
			}
			$l[ $id ]['Link']  = htmlspecialchars_decode( $link );
			$type              = $this->getType( @$segment['host'] );
			$l[ $id ]['Type']  = $type;
			$l[ $id ]['Name']  = trim( $names[ $id ] );
			$l[ $id ]['Juice'] = $juice[ $id ];
			$this->calculateLinkType( $type, $juice[ $id ] );
			if ( isset( $segment['path'] ) ) {
				$this->isFile( $segment['path'] );
			}
			if ( $u_flag and ( $this->sameHost( $segment ) and $this->containUnderscore( $link ) ) ) {
				$this->isset_underscore = true;
				$u_falg                 = false;
			}
			if ( $f_flag and ( $this->sameHost( $segment ) and ! $this->isFriendlyUrl( $link ) ) ) {
				$this->friendly = false;
				$f_flag         = false;
			}
		}
		return $l;
	}

	public function calculateLinkType( $type, $juice ) {
		if ( $type == 'external' and $juice == 'dofollow' ) {
			$this->external_dofollow++;
		} elseif ( $type == 'external' and $juice == 'nofollow' ) {
			$this->external_nofollow++;
		} else {
			$this->internal++;
		}
	}

	public function getInternalCount() {
		return $this->internal;
	}

	public function getExternalDofollowCount() {
		return $this->external_dofollow;
	}

	public function getExternalNofollowCount() {
		return $this->external_nofollow;
	}

	public function getFilesCount() {
		return $this->files_count;
	}

	public function issetUnderscore() {
		return $this->isset_underscore;
	}

	public function getLinks() {
		return $this->links;
	}

	public function isAllLinksAreFriendly() {
		return $this->friendly;
	}


	private function isFriendlyUrl( $link ) {
		return ! preg_match( '#\&([^\=]*)\=#is', $link );
	}

	private function isFile( $path ) {
		$ext = pathinfo( $path, PATHINFO_EXTENSION );
		if ( ! empty( $ext ) && ! in_array( strtolower( $ext ), $this->pageExt ) ) {
			$this->files_count++;
		}
	}

	private function getType( $host ) {
		return mb_strpos( (string) $host, $this->domain ) !== false ? 'internal' : 'external';
	}

	private function getJuiceFromTag( $tags ) {
		$juice   = array();
		$pattern = "rel=(?:'([^']+)'|\"([^\"]+)\")";
		foreach ( $tags as $id => $tag ) {
			preg_match( "#{$pattern}#is", $tag, $rel );
			if ( isset( $rel[2] ) ) {
				$j            = strtolower( trim( $rel[2], '"' ) );
				$juice[ $id ] = $j == 'nofollow' ? 'nofollow' : 'dofollow';
			} else {
				$juice[ $id ] = 'dofollow';
			}
		}
		return $juice;
	}

	private function normalizeLink( &$links ) {
		foreach ( $links as $id => $link ) {
			$link    = trim( $link, '\/' );
			$segment = parse_url( $link );
			if ( ! isset( $segment['host'] ) ) {
				$links[ $id ] = $this->domain . '/' . $link;
			}
			if ( ! isset( $segment['scheme'] ) ) {
				$links[ $id ] = 'http://' . $links[ $id ];
			}
		}
	}

	private function removeNotLinks( &$links ) {
		$mask = 'mailto:|tel:|skype:';
		foreach ( $links as $id => $link ) {
			if ( preg_match( "#^($mask)#i", $link ) ) {
				unset( $links[ $id ] );
			}
		}
	}

	private function containUnderscore( $link ) {
		return ( mb_strpos( $link, '_' ) !== false );
	}

	private function overlayArrays( array $a, array $b ) {
		$c = array();
		foreach ( $a as $k => $v ) {
			$c[ $k ] = empty( $v ) ? $b[ $k ] : $v;
		}
		return $c;
	}

	private function sameHost( $segment ) {
		return isset( $segment['host'] ) and preg_match( '/^(www\.)?(' . preg_quote( $this->domain, '/' ) . '|' . preg_quote( $this->idn, '/' ) . ')/iu', $segment['host'] );
	}
}
