<?php
/**
 * File: console.php
 *
 * @package V_WP_SEO_Audit
 */
return CMap::mergeArray(
	require dirname( __FILE__ ) . '/main.php',
	array(
		// console application components.
		'components' => array(
			'request' => array(
				'hostInfo'  => $params['app.host'],
				'baseUrl'   => rtrim( $params['app.base_url'], '/' ),
				'scriptUrl' => rtrim( $params['app.base_url'], '/' ) . '/index.php',
			),
		),
	)
);
