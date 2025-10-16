<?php
class RateProvider {

        /**
         * Cached rates configuration.
         *
         * @var array
         */
        private $rates = array();

        /**
         * Running score accumulator for legacy templates.
         *
         * @var float
         */
        private $score = 0.0;

        /**
         * Retrieve the rating configuration.
         *
         * @return array
         */
        public static function getRatesConfig() {
                static $config = null;

                if ( null === $config ) {
                        $config = include dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'rates.php';
                }

                return $config;
        }

        /**
         * Constructor.
         */
        public function __construct() {
                $this->rates = self::getRatesConfig();
        }

        /**
         * Legacy boolean comparator. Delegates to evaluateCompare().
         *
         * @param string $index     Rate key.
         * @param mixed  $condition Value to evaluate.
         *
         * @return string Advice code.
         */
        public function addCompare( $index, $condition ) {
                $result = $this->evaluateCompare( $index, $condition );
                $this->score += $result['points'];

                return $result['advice'];
        }

        /**
         * Legacy ranged comparator. Delegates to evaluateCompareArray().
         *
         * @param string $index Rate key.
         * @param mixed  $value Value to evaluate.
         *
         * @return string Advice code.
         */
        public function addCompareArray( $index, $value ) {
                $result = $this->evaluateCompareArray( $index, $value );
                $this->score += $result['points'];

                return $result['advice'];
        }

        /**
         * Legacy matrix comparator. Delegates to evaluateCompareMatrix().
         *
         * @param array   $matrix Keyword matrix.
         * @param integer $limit  Optional limit of keywords to evaluate.
         *
         * @return array Result containing points and advice.
         */
        public function addCompareMatrix( $matrix, $limit = null ) {
                $result       = $this->evaluateCompareMatrix( $matrix, $limit );
                $this->score += $result['points'];

                return $result;
        }

        /**
         * Get W3C score and advice for given error/warning counts.
         *
         * @param int $errors   Number of HTML errors.
         * @param int $warnings Number of HTML warnings.
         *
         * @return array Array with points and advice.
         */
        public function getW3cScoreAdvice( $errors, $warnings ) {
                $result = $this->evaluateW3c( $errors, $warnings );

                return array( $result['points'], $result['advice'] );
        }

        /**
         * Get the numeric score for an array-based category for a given value, without incrementing total score.
         *
         * @param string $index Rate key.
         * @param mixed  $value Value to evaluate.
         *
         * @return float Earned points.
         */
        public function getCompareArrayScore( $index, $value ) {
                $result = $this->evaluateCompareArray( $index, $value );

                return $result['points'];
        }

        /**
         * Evaluate a boolean rate and return points/advice.
         *
         * @param string $index     Rate key.
         * @param mixed  $condition Truthy/falsey condition.
         *
         * @return array
         */
        public function evaluateCompare( $index, $condition ) {
                $rate = isset( $this->rates[ $index ] ) ? $this->rates[ $index ] : 0;

                if ( is_array( $rate ) ) {
                        return $this->evaluateCompareArray( $index, $condition );
                }

                $is_success = (bool) $condition;
                $points     = $is_success ? (float) $rate : 0.0;
                $advice     = $is_success ? _RATE_OK : _RATE_ERROR;

                return array(
                        'points' => $points,
                        'advice' => $advice,
                );
        }

        /**
         * Evaluate a ranged rate definition (title length, html ratio, etc.).
         *
         * @param string $index Rate key.
         * @param mixed  $value Value to evaluate.
         *
         * @return array
         */
        public function evaluateCompareArray( $index, $value ) {
                if ( ! isset( $this->rates[ $index ] ) || ! is_array( $this->rates[ $index ] ) ) {
                        return array(
                                'points' => 0.0,
                                'advice' => _RATE_ERROR,
                        );
                }

                foreach ( $this->rates[ $index ] as $condition => $score ) {
                        $eval = "return {$condition};";
                        if ( eval( $eval ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_eval
                                return array(
                                        'points' => isset( $score['score'] ) ? (float) $score['score'] : 0.0,
                                        'advice' => isset( $score['advice'] ) ? $score['advice'] : _RATE_ERROR,
                                );
                        }
                }

                return array(
                        'points' => 0.0,
                        'advice' => _RATE_ERROR,
                );
        }

        /**
         * Evaluate W3C category thresholds.
         *
         * @param int $errors   Error count.
         * @param int $warnings Warning count.
         *
         * @return array
         */
        public function evaluateW3c( $errors, $warnings ) {
                if ( ! isset( $this->rates['w3c'] ) || ! is_array( $this->rates['w3c'] ) ) {
                        return array(
                                'points' => 0.0,
                                'advice' => _RATE_ERROR,
                        );
                }

                foreach ( $this->rates['w3c'] as $condition => $score ) {
                        $cond = str_replace( array( '$errors', '$warnings' ), array( (int) $errors, (int) $warnings ), $condition );
                        if ( eval( "return {$cond};" ) ) { // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.runtime_eval
                                return array(
                                        'points' => isset( $score['score'] ) ? (float) $score['score'] : 0.0,
                                        'advice' => isset( $score['advice'] ) ? $score['advice'] : _RATE_ERROR,
                                );
                        }
                }

                return array(
                        'points' => 0.0,
                        'advice' => _RATE_ERROR,
                );
        }

        /**
         * Evaluate keyword consistency matrix.
         *
         * @param array        $matrix Matrix of keyword appearances.
         * @param int|null     $limit  Optional keyword limit.
         *
         * @return array
         */
        public function evaluateCompareMatrix( $matrix, $limit = null ) {
                if ( ! isset( $this->rates['wordConsistency'] ) || ! is_array( $this->rates['wordConsistency'] ) ) {
                        return array(
                                'points' => 0.0,
                                'advice' => _RATE_ERROR,
                        );
                }

                if ( null === $limit ) {
                        $limit = 0;
                } else {
                        $limit = function_exists( 'absint' ) ? absint( $limit ) : abs( (int) $limit );
                }
                $rate         = $this->rates['wordConsistency'];
                $words_count  = 0;
                $earned       = 0.0;
                $matrix       = is_array( $matrix ) ? $matrix : array();
                $tags         = array_keys( $rate );
                $per_word_sum = array_sum( $rate );

                foreach ( $matrix as $tags_map ) {
                        if ( $limit > 0 && $words_count >= $limit ) {
                                break;
                        }

                        if ( ! is_array( $tags_map ) ) {
                                $words_count++;
                                continue;
                        }

                        foreach ( $tags as $tag ) {
                                if ( ! empty( $tags_map[ $tag ] ) ) {
                                        $earned += (float) $rate[ $tag ];
                                }
                        }

                        $words_count++;
                }

                $considered   = ( $limit > 0 ) ? min( $limit, max( $words_count, 0 ) ) : max( $words_count, 0 );
                $max_possible = $per_word_sum * $considered;

                if ( $max_possible <= 0 ) {
                        $advice = _RATE_ERROR;
                } elseif ( $earned >= $max_possible ) {
                        $advice = _RATE_OK;
                } elseif ( $earned > 0 ) {
                        $advice = _RATE_WARNING;
                } else {
                        $advice = _RATE_ERROR;
                }

                return array(
                        'points' => $earned,
                        'advice' => $advice,
                );
        }

        /**
         * Accessor for rates configuration.
         *
         * @return array
         */
        public function getRates() {
                return $this->rates;
        }

        /**
         * Legacy accessor for accumulated score.
         *
         * @return float
         */
        public function getScore() {
                return $this->score;
        }
}
