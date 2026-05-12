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

	/**
	 * Register a new AJAX endpoint.
	 *
	 * @param string   $action    The ajax action name.
	 * @param callable $callback  The callback for the ajax hook.
	 * @param bool     $auth_only When true, only the wp_ajax_ hook is registered so the
	 *                            endpoint is restricted to logged-in users. Default false
	 *                            registers both authenticated and unauthenticated hooks.
	 *
	 * @return void
	 */
	public function endpoint( string $action, callable $callback, bool $auth_only = false ): void {

		add_action( 'wp_ajax_' . $action, $callback );

		if ( ! $auth_only ) {
			add_action( 'wp_ajax_nopriv_' . $action, $callback );
		}
	}

	/**
	 * Returns the given parameter passed as part of the AJAX request.
	 *
	 * @param string $param   Parameter to get.
	 * @param mixed  $default Default value (default = '').
	 *
	 * @return mixed The value for the parameter or the default.
	 */
	public static function get_param( string $param, mixed $default = '' ): mixed {

		return $_POST[ $param ] ?? $default; // phpcs:ignore WordPress.Security.NonceVerification,WordPress.Security.ValidatedSanitizedInput
	}

	/**
	 * Verify a nonce sent with an AJAX request.
	 * Call this at the top of any AJAX callback that modifies data.
	 *
	 * @param string $action    The nonce action string used when the nonce was created.
	 * @param string $nonce_key The POST/GET key holding the nonce value. Default '_wpnonce'.
	 *
	 * @return void Calls wp_die() with a 403 response on failure.
	 */
	public static function verify_nonce( string $action, string $nonce_key = '_wpnonce' ): void {

		$nonce = isset( $_REQUEST[ $nonce_key ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $nonce_key ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die( esc_html__( 'Security check failed.', 'wpmvc' ), 403 );
		}
	}

	/**
	 * Serves the given array as a JSON response and dies.
	 *
	 * @param array $response The response fields.
	 *
	 * @return never
	 */
	public static function json_resp( array $response ): never {

		header( 'Content-Type: application/json' );
		die( wp_json_encode( $response ) );
	}
}
