<?php
/**
 * Provides the Ajax helper class.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Library;

/**
 * Helper class for registering WordPress admin ajax endpoints.
 * Example:
 * $ajax = new AdminAjax();
 * $ajax->endpoint( 'my_ajax_action', [ $this, 'my_callback' ] );
 */
class AdminAjax {
	// TODO: make singleton.

	/**
	 * Register a new AJAX endpoint.
	 *
	 * @param string $action   The ajax action name.
	 * @param array  $callback The callback for the ajax hook.
	 */
	public function endpoint( $action, $callback ) {

		add_action( 'wp_ajax_' . $action, $callback );
		add_action( 'wp_ajax_nopriv_' . $action, $callback );
	}

	/**
	 * Returns the given parameter passed as part of the AJAX request.
	 *
	 * @param string $param   Parameter to get.
	 * @param mixed  $default Default value (default = '').
	 *
	 * @return mixed|string The value for the parameter or the default.
	 */
	public static function get_param( $param, $default = '' ) {

		$value = isset( $_POST[ $param ] ) ? $_POST[ $param ] : $default; // phpcs:ignore

		return $value;
	}

	/**
	 * Serves the given array as a JSON response and dies.
	 *
	 * @param array $response The response fields.
	 */
	public static function json_resp( array $response ) {

		header( 'Content-Type: application/json' );
		die( wp_json_encode( $response ) );
	}
}
