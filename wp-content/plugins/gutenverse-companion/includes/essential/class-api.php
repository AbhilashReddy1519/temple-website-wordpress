<?php
/**
 * API
 *
 * @author Jegstudio
 * @since 1.0.0
 * @package gutenverse-companion
 */

namespace Gutenverse_Companion\Essential;

use WP_REST_Response;

/**
 * Class API
 *
 * @package gutenverse-companion
 */
class Api {
	/**
	 * Api constructor.
	 */
	public function __construct() {

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register Jeg Theme Essence APIs
	 */
	public function register_routes() {
		register_rest_route(
			'gutenverse-essence/v1',
			'/gutenverse-essence-proxy',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'essence_proxy' ),
				'permission_callback' => '__return_true',
			)
		);
	}
	/**
	 * Essence Proxy
	 */
	public function essence_proxy( $request ) {
		$url      = gutenverse_esc_data( $request->get_param( 'url' ) );
		$body     = gutenverse_esc_data( (array) $request->get_param( 'body' ), 'array' );
		$method   = gutenverse_esc_data( $request->get_param( 'method' ) );
		$response = wp_remote_request(
			$url,
			array(
				'method'  => strtoupper( $method ),
				'body'    => $body ? json_encode( $body ) : $body,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response(
				array(
					'active' => false,
					'status' => 'failed',
				),
				400
			);
		}

		$external_api_body = wp_remote_retrieve_body( $response );
		return rest_ensure_response( json_decode( $external_api_body ) );
	}
}
