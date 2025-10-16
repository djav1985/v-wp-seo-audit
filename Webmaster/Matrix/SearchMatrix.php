<?php
/**
 * File: SearchMatrix.php
 *
 * Description: Search engine matrix analysis.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
 */

class SearchMatrix {
	private $words    = array();
	private $searchin = array();

	public function addWords( array $words ) {
		$this->words = $words;
	}

	public function addSearchInString( $key, $value ) {
		$this->addSearchIn( 'searchInString', $key, (string) $value );
	}

	public function addSearchInArrayRecursive( $key, array $value ) {
		$this->addSearchIn( 'searchInArrayRecursive', $key, $value );
	}

	private function addSearchIn( $callback, $key, $value ) {
		static $i;
		$this->searchin[ $i ]['callback'] = $callback;
		$this->searchin[ $i ]['key']      = $key;
		$this->searchin[ $i ]['value']    = $value;
		$i++;
	}

	private function searchInString( $haystack, $needle ) {
		return mb_stripos( $haystack, $needle ) !== false;
		// return preg_match("#\b{$needle}\b#ius", $haystack);
	}

	public function searchInArrayRecursive( $haystack, $needle ) {
		foreach ( $haystack as $value ) {
			if ( is_array( $value ) ) {
				if ( $this->searchInArrayRecursive( $value, $needle ) == true ) {
					return true;
				}
			} else {
				if ( $this->searchInString( $value, $needle ) == true ) {
					return true;
				}
			}
		}
		return false;
	}

	public function generate() {
		$matrix = array();
		foreach ( $this->words as $word ) {
			foreach ( $this->searchin as $provider ) {
				$func                                = $provider['callback'];
				$matrix[ $word ][ $provider['key'] ] = $this->$func( $provider['value'], $word );
			}
		}
		return $matrix;
	}

}
