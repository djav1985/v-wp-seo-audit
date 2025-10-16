<?php
/**
 * File: config.php
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

return array(
	'app.name'                   => 'SEO Audit By V - v2.0',
	'app.timezone'               => 'America/New_york',
	'app.default_language'       => 'en',
	'app.languages'              => array(
		'en' => 'English',
	),

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
