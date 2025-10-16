<?php
class RateProvider {
	/**
	 * Get W3C score and advice for given error/warning counts (no eval in template).
	 */
	public function getW3cScoreAdvice($errors, $warnings) {
		$rates = $this->rates['w3c'];
		foreach ($rates as $condition => $score) {
			// Replace $errors and $warnings in condition string
			$cond = str_replace(['$errors', '$warnings'], [$errors, $warnings], $condition);
			if (eval("return {$cond};")) {
				return array($score['score'], $score['advice']);
			}
		}
		return array(0, _RATE_ERROR);
	}
	/**
	 * Get the numeric score for an array-based category for a given value, without incrementing total score.
	 */
	public function getCompareArrayScore($index, $value) {
		$rates = $this->rates[$index];
		foreach ($rates as $condition => $score) {
			$eval = "return {$condition};";
			if (eval($eval)) {
				return $score['score'];
			}
		}
		return 0;
	}
	private $rates = array();
	private $score = 0;

	public function __construct() {
		$this->rates = include dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'rates.php';
	}

	public function addCompare( $index, $condition ) {
		$score = $this->rates[ $index ];
		if ( $condition ) {
			$this->score += $score;
			return _RATE_OK;
		} else {
			return _RATE_ERROR;
		}
	}

	public function addCompareArray( $index, $value ) {
		$rates = $this->rates[ $index ];
		foreach ( $rates as $condition => $score ) {
			$eval = "return {$condition};";
			if ( eval( $eval ) ) {
				$this->score += $score['score'];
				return $score['advice'];
			}
		}
	}

	public function addCompareMatrix( $matrix ) {
		$rate = $this->rates['wordConsistency'];
		foreach ( $matrix as $tags ) {
			foreach ( $tags as $tag => $consists ) {
				if ( $consists ) {
					$this->score += $rate[ $tag ];
				}
			}
		}
	}

	public function getRates() {
		return $this->rates;
	}

	public function getScore() {
		return $this->score;
	}
}
