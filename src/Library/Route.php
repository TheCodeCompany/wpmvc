<?php
/**
 * Custom route builder.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Library;

/**
 * Utility to build custom routes within WordPress. Routes are defined as
 * regular expressions and pass defined arguments to the callback function.
 * Example of setting up a route: within your controller setup() method:
 *  $this->route->add( array(
 *      'regex'    => '^myroute/([0-9]+)/?$',
 *      'callback' => array( $this, 'my_route_callback' )
 *  ) );
 * The callback would then look like the following:
 * function my_route_callback( $id ) {
 *   // Do things here
 * }
 */
class Route {
	// TODO: make singleton.

	/**
	 * The registered routes.
	 *
	 * @var array
	 */
	protected $routes = [];

	/**
	 * Set up the routing system.
	 *
	 * @return void
	 */
	public function __construct() {

		// Register the setup class.
		add_filter( 'do_parse_request', [ $this, 'handle_routes' ], 1, 3 );
	}

	/**
	 * Registers a new route.
	 *
	 * @param array $args Route args.
	 *                    $regex string The regular expression to match the route path.  Can contain groups which are passed as
	 *                    params to the callback.
	 *                    $callback function Callback for when the route is triggered.
	 *
	 * @return void
	 */
	public function add( array $args ) {

		// Set defaults so stuff does not break.
		$args = array_merge(
			[
				'regex'    => '',
				'callback' => [],
				'title'    => '',
			],
			$args
		);

		// Add to the list of routes.
		if ( ! empty( $args['regex'] ) && ! empty( $args['callback'] ) ) {
			$this->routes[] = $args;
		}
	}

	/**
	 * Handles routing on 'do_parse_request'
	 *
	 * @param boolean $continue         Whether to continue processing the request.
	 * @param \WP     $wp               Current WordPress environment instance.
	 * @param mixed   $extra_query_vars Extra passed query variables.
	 *
	 * @return boolean
	 */
	public function handle_routes( $continue, $wp, $extra_query_vars ) {

		// Get the request path / URI.
		$request_path = isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : ''; // phpcs:ignore
		$request_path = trim( $request_path, '/' );
		$request_path = strtok( $request_path, '?' );

		// Make request path relative to the site path.
		// This is required for routes to work when WP is installed in a subdirectory.

		$site_url       = get_site_url();
		$site_url_parts = wp_parse_url( $site_url );
		$site_url_path  = isset( $site_url_parts['path'] ) ? $site_url_parts['path'] : '/';
		$site_url_path  = trim( $site_url_path, '/' );

		$request_path = preg_replace( '{^/?' . $site_url_path . '/?}', '', $request_path );

		// Regsiter each of the routes.
		foreach ( $this->routes as $route ) {
			$regex    = $route['regex'];
			$callback = $route['callback'];

			// Regex the request path.
			$matches = [];
			$match   = preg_match( '{' . $regex . '}', $request_path, $matches );

			// Dispatch route if a hit.
			if ( ! empty( $matches ) ) {

				// Initialsie the admin bar, so we have admin bar access on custom routes!
				if ( is_user_logged_in() ) {
					_wp_admin_bar_init();
				}

				$this->set_newrelic_transaction( $route );

				// Remove the first match, which is the entire matches string.
				// We just want the single matches, to pass to the route handler as parameters.
				array_shift( $matches );

				// Pass request along to the route handler.
				call_user_func_array( $callback, $matches );
				exit;

			}
		}

		// No matching route found, fallback to WordPress.
		return $continue;
	}

	/**
	 * Set the new Relic transaction name, so the route can be traced more effectively.
	 *
	 * @param array $route The route config array.
	 *
	 * @return void
	 */
	protected function set_newrelic_transaction( array $route ) {

		$transaction_name = '';

		// Pull the Class and method name from the route callback.
		$callback = $route['callback'];
		if ( is_array( $callback ) ) {  // Callback to class method.

			$class  = '';
			$object = array_shift( $callback );
			$method = array_shift( $callback );

			if ( is_string( $object ) ) { // Static class callback.
				$class = $object;
			} else {  // Stateful callback.
				$class = get_class( $object );
			}

			// Change all of the backslashes to forward slashes to match NewRelics standards.
			$class = str_replace( '\\', '/', $class );

			$transaction_name = "{$class}//{$method}";

		} elseif ( is_string( $callback ) ) {  // Direct callback to function.

			$transaction_name = $callback;

		}

		// Set the transaction name within New Relic.
		if ( ! empty( $transaction_name ) && extension_loaded( 'newrelic' ) ) { // Ensure New Relic agent is available.
			newrelic_name_transaction( $transaction_name );
		}
	}
}
