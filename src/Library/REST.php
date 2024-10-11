<?php
/**
 * WP REST API builder.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Library;

/**
 * Helper class for registering WordPress REST API endpoints.
 * Example:
 *  // Do this in the setup() method of a class
 *  $this->rest->>endpoint(
 *      [
 *          'namespace' => $config->app( 'name' ), // This should be the app name
 *          'action'    => 'edit/(?P<id>\d+)',
 *          'method'    => \WP_REST_Server::READABLE,
 *          'callback'  => [ $this, 'edit_thing' ],
 *      ]
 * );
 * This will create an endpoint like this:
 *      /wp-json/app_name/v1/edit/<ID>
 * NOTE v1 is the default version and can be overriden by adding the 'version'
 * parameter in the REST::endpoint() call
 *  // Our callback method
 *  function edit_thing( \WP_REST_Request $request ) {
 *      $thing_id = $request['id'];
 *      $thing = $factory->get_post( $thing_id );
 *      // Do some things ...
 *      // Return response which is sent as JSON
 *      return array(
 *          'name' => $thing->get_meta( Thing::META_NAME ),
 *          ...
 *      );
 *  }
 * Remember to follow the basics of REST APIs, use HTTP methods properly:
 *  - GET    - for getting data
 *  - POST   - for editing objects
 *  - PUT    - for creating objects
 *  - DELETE - for deleting objects
 */
class REST {
	// TODO: make singleton.

	/**
	 * All of the defined endpoints.
	 *
	 * @var array
	 */
	protected $endpoints = [];

	/**
	 * The current REST request, if any.
	 *
	 * @var array
	 */
	protected $current_request = [];

	/**
	 * The current endpoint, if any.
	 *
	 * @var array
	 */
	protected $current_endpoint = [];

	/**
	 * Initialises the AJAX system if needed.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}

	/**
	 * Register a new REST endpoint.
	 *
	 * @param array $args Endpoint args.
	 *                    $namespace The namespace for the endpoint, this should be the app name slug/namespace.
	 *                    $action The REST endpoint name.
	 *                    $version The version of the REST API (default = v1).
	 *                    $method The HTTP method.
	 *                    $callback The callback for the ajax hook.
	 *                    $permission_callback The permissions callback for the ajax hook.
	 */
	public function endpoint( array $args ) {

		$default_args = [
			'namespace'           => '',
			'version'             => 'v1',
			'action'              => '',
			'method'              => 'GET',
			'callback'            => '',
			'permission_callback' => '',
			'args'                => [],
		];

		$args = array_merge( $default_args, $args );

		// Add to the list of endpoints to register.
		$route                     = $this->build_route( $args );
		$this->endpoints[ $route ] = $args;
	}

	/**
	 * Returns the WP REST request object for the current endpoint.
	 *
	 * @return mixed
	 */
	public function get_request() {
		return $this->current_request;
	}

	/**
	 * Registers the configured REST endpoints with WordPress
	 *
	 * @return void
	 */
	public function register_endpoints() {

		foreach ( $this->endpoints as $endpoint ) {
			extract( $endpoint ); // phpcs:ignore

			// Build the actual namespace.
			$namespace = "{$namespace}/{$version}";

			// Register the endpoint.
			register_rest_route(
				$namespace,
				$action,
				[
					'methods'             => $method,
					'callback'            => [ $this, 'handle_callback' ],
					'permission_callback' => [ $this, 'handle_perm_callback' ],
					'args'                => $args,
				]
			);

		}
	}

	/**
	 * Handles callback for permission on an endpoint.
	 *
	 * @param \WP_REST_Request $request The REST endpoint request.
	 *
	 * @return boolean
	 */
	public function handle_perm_callback( \WP_REST_Request $request ) {

		$allowed = true;  // Default, free for all.

		// Save current request.
		$this->current_request = $request;

		// Set current end point.
		$this->set_current_endpoint( $request );

		// Call user callback if exists.
		if ( ! empty( $this->current_endpoint ) ) {

			$callback = null;
			if ( isset( $this->current_endpoint['permission_callback'] ) ) {
				$callback = $this->current_endpoint['permission_callback'];
			}

			if ( ! empty( $callback ) ) {

				$allowed = call_user_func(
					$callback,
					$request,
					$request->get_params()
				);

			}
		}

		return $allowed;
	}

	/**
	 * Set the current endpoint data by parsing the request.
	 *
	 * @param \WP_REST_Request $request The current request.
	 *
	 * @return void
	 */
	protected function set_current_endpoint( \WP_REST_Request $request ) {
		$route = $request->get_route();

		foreach ( $this->endpoints as $endpoint ) {

			// Build the expected route to check against.
			$endpoint_route = '/' . $endpoint['namespace'] . '/' . $endpoint['version'] . '/' . $endpoint['action'];

			$match = preg_match( '@^' . $endpoint_route . '$@i', $route );

			if ( $match ) {
				$this->current_endpoint = $endpoint;
				break;
			}
		}
	}

	/**
	 * Handles a callback for an endpoint.
	 *
	 * @param \WP_REST_Request $request The current request.
	 *
	 * @return array
	 */
	public function handle_callback( \WP_REST_Request $request ) {

		$response = [];

		// Save current request.
		$this->current_request = $request;

		// Set current end point.
		$this->set_current_endpoint( $request );

		// Call user callback.
		if ( ! empty( $this->current_endpoint ) ) {

			$callback = $this->current_endpoint['callback'];

			if ( ! empty( $callback ) ) {

				$response = call_user_func(
					$callback,
					$request,
					$request->get_params()
				);

			}
		}

		return $response;
	}

	/**
	 * Builds the route structure for the given endpoint config
	 *
	 * @param array $endpoint The REST endpoint arguments.
	 *
	 * @return string
	 */
	private static function build_route( array $endpoint ) {
		$route = '';

		extract( $endpoint ); // phpcs:ignore

		$route = "/{$namespace}/{$version}/{$action}";

		return $route;
	}
}
