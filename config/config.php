<?php
/**
 * File: config.php
 *
 * Description: Main configuration file.
 *
 * @package v_wpsa
 * @author Vontainment
 * @license MIT
 * @license URI https://opensource.org/licenses/MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	'app.name'                   => 'SEO Audit By V - v2.0',

	// PageSpeed Insights.
	'psi.categories'             => array( 'performance', 'accessibility', 'best-practices', 'seo', 'pwa' ),
	'psi.device'                 => 'desktop',
	'psi.run_instantly'          => true,
	'psi.show'                   => true,

	// Analyzer.
	'analyzer.tag_cloud'         => 10,
	'analyzer.consistency_count' => 5,

	// Other params.
	'param.index_website_count'  => 12,
	'param.bad_words_validation' => false,
	'param.addthis'              => '',
	'param.placeholder'          => 'google.com',

	// Template.
	'template.footer'            => '<p>Developed by <strong><a href="https://vontainment.com">Vontainment</a></strong></p>',
);
