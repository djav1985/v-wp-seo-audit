<?php
/**
 * File: rates.php
 *
 * Description: Rate configuration and defaults.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
 */

defined( '_RATE_OK' ) or define( '_RATE_OK', 'success' );
defined( '_RATE_WARNING' ) or define( '_RATE_WARNING', 'warning' );
defined( '_RATE_ERROR' ) or define( '_RATE_ERROR', 'error' );
defined( '_RATE_OK_IDEAL' ) or define( '_RATE_OK_IDEAL', 'success ideal_ratio' );
defined( '_RATE_ERROR_LESSTHAN' ) or define( '_RATE_ERROR_LESSTHAN', 'error less_than' );
defined( '_RATE_ERROR_MORETHAN' ) or define( '_RATE_ERROR_MORETHAN', 'error more_than' );

defined( '_RATE_CSS_COUNT' ) or define( '_RATE_CSS_COUNT', 10 );
defined( '_RATE_JS_COUNT' ) or define( '_RATE_JS_COUNT', 15 );

defined( '_RATE_TITLE_BAD' ) or define( '_RATE_TITLE_BAD', 0 );
defined( '_RATE_TITLE_GOOD' ) or define( '_RATE_TITLE_GOOD', 10 );
defined( '_RATE_TITLE_BEST' ) or define( '_RATE_TITLE_BEST', 70 );

defined( '_RATE_DESC_BAD' ) or define( '_RATE_DESC_BAD', 0 );
defined( '_RATE_DESC_GOOD' ) or define( '_RATE_DESC_GOOD', 70 );
defined( '_RATE_DESC_BEST' ) or define( '_RATE_DESC_BEST', 160 );

defined( '_RATE_HRATIO_BAD' ) or define( '_RATE_HRATIO_BAD', 5 );
defined( '_RATE_HRATIO_GOOD' ) or define( '_RATE_HRATIO_GOOD', 40 );
defined( '_RATE_HRATIO_BEST' ) or define( '_RATE_HRATIO_BEST', 70 );

defined( '_RATE_W3C_ERR_OK' ) or define( '_RATE_W3C_ERR_OK', 0 );
defined( '_RATE_W3C_WARN_OK' ) or define( '_RATE_W3C_WARN_OK', 5 );
defined( '_RATE_W3C_ERR_WARN_LOW' ) or define( '_RATE_W3C_ERR_WARN_LOW', 10 );
defined( '_RATE_W3C_ERR_WARN_MED' ) or define( '_RATE_W3C_ERR_WARN_MED', 25 );
defined( '_RATE_W3C_ERR_WARN_HIGH' ) or define( '_RATE_W3C_ERR_WARN_HIGH', 50 );

/*
The Website Review is a dynamic grade on a 100-point scale.
This mean that the sum of shown bellow points can't be more than 100.

So, how points are added? Let's take a look on the first key=>value pair
'noFlash' => 2,
This mean, that if website do not have flash content then he will get +2 points to current score and etc.
===========================
Let's analyse pairs containing arrays. For example: 'title' => array(),
if the $title length == 0, then website receives 0 points,
if length > 0 and < 10 -> 2 points
and etc
===========================
At the bottom of this config file you will see 'wordConsistency' key.
'wordConsistency' => array(
	'keywords' => 0.5,
	'description' => 1,
	'title' => 1,
	'headings' => 1,
),
To calculate the total sum of this checkpoint you need to multiply each value by {N} and sum them.
Where {N} -> is 'analyzer.consistency_count' => value in main config
By default {N} equals 5, so
(0.5 * 5) + (1 * 5) + (1 * 5) + (1 * 5) = 17.5
17.5 - the maximum points, which website can be get at this checkpoint.

Advice. Be careful if you want to change the rates.
*/
return array(

	'noFlash'            => 2,
	'noIframe'           => 2,
	'issetHeadings'      => 4,
	'noNestedtables'     => 1,
	'noInlineCSS'        => 2,
	'issetFavicon'       => 2,
	'noEmail'            => 0,
	'keywords'           => 0,
	'imgHasAlt'          => 6,
	'isFriendlyUrl'      => 4,
	'noUnderScore'       => 4,
	'issetInternalLinks' => 4,
	'hasRobotsTxt'       => 2,
	'hasSitemap'         => 4,
	'hasGzip'            => 1,
	'hasAnalytics'       => 1,

	'title'              => array(
		'$value == _RATE_TITLE_BAD' => array(
			'score'  => 0,
			'advice' => _RATE_ERROR,
		),
		'$value > _RATE_TITLE_BAD and $value < _RATE_TITLE_GOOD' => array(
			'score'  => 3,
			'advice' => _RATE_WARNING,
		),
		'$value >= _RATE_TITLE_GOOD and $value <= _RATE_TITLE_BEST' => array(
			'score'  => 6,
			'advice' => _RATE_OK,
		),
		'$value > _RATE_TITLE_BEST' => array(
			'score'  => 1,
			'advice' => _RATE_WARNING,
		),
	),

	'description'        => array(
		'$value == _RATE_DESC_BAD' => array(
			'score'  => 0,
			'advice' => _RATE_ERROR,
		),
		'$value > _RATE_DESC_BAD and $value < _RATE_DESC_GOOD' => array(
			'score'  => 3,
			'advice' => _RATE_WARNING,
		),
		'$value >= _RATE_DESC_GOOD and $value <= _RATE_DESC_BEST' => array(
			'score'  => 6,
			'advice' => _RATE_OK,
		),
		'$value > _RATE_DESC_BEST' => array(
			'score'  => 1,
			'advice' => _RATE_WARNING,
		),
	),

	'charset'            => 2,
	'viewport'           => 2,
	'dublincore'         => 1,
	'ogmetaproperties'   => 3,

	'htmlratio'          => array(
		'$value < _RATE_HRATIO_BAD'  => array(
			'score'  => 0,
			'advice' => _RATE_ERROR_LESSTHAN,
		),
		'$value >= _RATE_HRATIO_BAD and $value < _RATE_HRATIO_GOOD' => array(
			'score'  => 3,
			'advice' => _RATE_OK,
		),
		'$value >= _RATE_HRATIO_GOOD and $value <= _RATE_HRATIO_BEST' => array(
			'score'  => 6,
			'advice' => _RATE_OK_IDEAL,
		),
		'$value > _RATE_HRATIO_BEST' => array(
			'score'  => 1,
			'advice' => _RATE_WARNING,
		),
	),

	'w3c'                => array(
		'$errors == _RATE_W3C_ERR_OK && $warnings < _RATE_W3C_WARN_OK' => array(
			'score'  => 6,
			'advice' => _RATE_OK,
		),
		'$errors + $warnings < _RATE_W3C_ERR_WARN_LOW'   => array(
			'score'  => 4,
			'advice' => _RATE_OK,
		),
		'$errors + $warnings >= _RATE_W3C_ERR_WARN_LOW && $errors + $warnings < _RATE_W3C_ERR_WARN_MED' => array(
			'score'  => 2,
			'advice' => _RATE_WARNING,
		),
		'$errors + $warnings >= _RATE_W3C_ERR_WARN_MED && $errors + $warnings < _RATE_W3C_ERR_WARN_HIGH' => array(
			'score'  => 1,
			'advice' => _RATE_WARNING,
		),
		'$errors + $warnings >= _RATE_W3C_ERR_WARN_HIGH' => array(
			'score'  => 0,
			'advice' => _RATE_ERROR,
		),
	),

	'doctype'            => 2,
	'isPrintable'        => 0,
	'issetAppleIcons'    => 1,
	'noDeprecated'       => 1,
	'lang'               => 2,

	'cssCount'           => array(
		'$value <= _RATE_CSS_COUNT' => array(
			'score'  => 4,
			'advice' => _RATE_OK,
		),
		'$value > _RATE_CSS_COUNT'  => array(
			'score'  => 0,
			'advice' => _RATE_ERROR,
		),
	),

	'jsCount'            => array(
		'$value <= _RATE_JS_COUNT' => array(
			'score'  => 4,
			'advice' => _RATE_OK,
		),
		'$value > _RATE_JS_COUNT'  => array(
			'score'  => 0,
			'advice' => _RATE_ERROR,
		),
	),

	'wordConsistency'    => array(
		'keywords'    => 0.5,
		'description' => 0.5,
		'title'       => 1,
		'headings'    => 1,
	),

);
