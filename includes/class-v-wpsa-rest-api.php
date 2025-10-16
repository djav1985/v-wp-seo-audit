<?php
/**
 * REST API Controller Class
 *
 * Handles REST API endpoints for internal domain analysis.
 *
 * @package v_wpsa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class V_WPSA_Rest_API
 */
class V_WPSA_Rest_API {

	/**
	 * REST API namespace.
	 *
	 * @var string
	 */
	const NAMESPACE = 'v-wpsa/v1';

	/**
	 * Register REST API routes.
	 */
	public static function register_routes() {
		register_rest_route(
			self::NAMESPACE,
			'/report',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( __CLASS__, 'get_report' ),
				'permission_callback' => array( __CLASS__, 'check_permission' ),
				'args'                => array(
					'domain' => array(
						'required'          => true,
						'type'              => 'string',
						'description'       => 'Domain to analyze',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'force'  => array(
						'required'          => false,
						'type'              => 'boolean',
						'default'           => false,
						'description'       => 'Force re-analysis even if cached data exists',
						'sanitize_callback' => 'rest_sanitize_boolean',
					),
				),
			)
		);
	}

	/**
	 * Permission callback for REST API endpoints.
	 * Requires manage_options capability (admin access).
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return bool True if user has permission, false otherwise.
	 */
	public static function check_permission( $request ) {
		// Allow filtering the required capability.
		$capability = apply_filters( 'v_wpsa_rest_api_capability', 'manage_options' );

		return current_user_can( $capability );
	}

	/**
	 * REST API endpoint handler for getting a domain report.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response|WP_Error Response object or error.
	 */
	public static function get_report( $request ) {
		$domain = $request->get_param( 'domain' );
		$force  = $request->get_param( 'force' );

		// Use the report service to prepare the report.
		$report = V_WPSA_Report_Service::prepare_report(
			$domain,
			array(
				'force' => $force,
			)
		);

		// Handle errors from the service.
		if ( is_wp_error( $report ) ) {
			return new WP_Error(
				$report->get_error_code(),
				$report->get_error_message(),
				array( 'status' => 400 )
			);
		}

		// Return the report data as JSON.
		return new WP_REST_Response( $report, 200 );
	}
}
