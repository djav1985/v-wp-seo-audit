<?php
/**
 * File: config.php
 *
 * @package V_WP_SEO_Audit
 */

return array(
	'app.name'                   => 'SEO Audit By V - v2.0',
	'app.timezone'               => 'America/New_york', // https://www.php.net/manual/en/timezones.php
	'app.host'                   => 'https://vontainment.com',
	'app.base_url'               => '/scripts/seo/',
	'app.default_language'       => 'en',
	'app.languages'              => array(
		'en' => 'English',
	),
	'app.encryption_key'         => 'KA1E3ewqsdx0A9OdsTip50182A8mYhFm',
	'app.validation_key'         => 'D5TgCD0BAZTDoP823daEEv3ZRue6ZAQR',
	'app.cookie_validation'      => false,
	'app.manage_key'             => 'then2now',

	// Url settings.
	'url.multi_language_links'   => false,
	'url.show_script_name'       => false,

	// Database settings.
	'db.host'                    => 'localhost',
	'db.dbname'                  => 'vontainment_review',
	'db.username'                => 'vontainment_review',
	'db.password'                => '7=Y?_%K[&-m;TYj)',
	'db.port'                    => 3306,

	// Cookie settings.
	'cookie.secure'              => false,
	'cookie.same_site'           => 'Lax',

	// Pagepeeker settings.
	'thumbnail.proxy'            => false,
	'pagepeeker.verify'          => "<a href='https://pagepeeker.com/' target='_blank'>Website Screenshots by PagePeeker</a>",
	'pagepeeker.api_key'         => 'd3ec9c309b',

	// PageSpeed Insights.
	'psi.categories'             => array( 'performance' | 'accessibility' | 'best-practices' | 'seo' | 'pwa' ), // Values: 'performance' | 'accessibility' | 'best-practices' | 'seo' | 'pwa'.
	'psi.device'                 => 'desktop' | 'mobile', // Values: 'desktop' | 'mobile'
	'psi.run_instantly'          => true, // Whether to run analysis instantly after user opens review page. Values: true | false
	'psi.show'                   => true, // Whether to show Pagespeed Insight Section

	// Analyzer.
	'analyzer.cache_time'        => 60 * 60 * 24, // Review cache time in seconds
	'analyzer.tag_cloud'         => 10, // Total words in a tag cloud
	'analyzer.consistency_count' => 5, // Use 5 most consistent words from tagCloud to generate consistency matrix.

	// Other params.
	'param.cookie_cache'         => 'application.runtime',
	'param.instant_redirect'     => false,
	'param.index_website_count'  => 12,
	'param.bad_words_validation' => false,
	'param.addthis'              => '',
	'param.placeholder'          => 'google.com',

	// Template.
	'template.footer'            => '<p>Developed by <strong><a href="https://vontainment.com">Vontainment</a></strong></p>',
);
