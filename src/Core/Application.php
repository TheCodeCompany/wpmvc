<?php
/**
 * Framework application manager.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Core;

use WPMVC\Library\Config;
use WPMVC\Library\Route;
use WPMVC\Library\REST;
use WPMVC\Library\AdminAjax;

/**
 * An application.
 * This is the core utility which manages an application which uses this framework.
 * An application should be defined like so:
 * ```
 * $app = new Application(
 *  'example',
 *  dirname( __FILE__ ),
 *  [
 *    new MyController(),
 *    ...
 *  ]
 * );
 * ```
 */
class Application {

	/**
	 * The slug/name of the application.
	 *
	 * @var string
	 */
	protected string $name = '';

	/**
	 * The root directory of the application.
	 *
	 * @var string
	 */
	protected string $directory = '';

	/**
	 * All of the controllers defined in the application.
	 *
	 * @var array
	 */
	protected array $controllers = [];

	/**
	 * The config helper instance.
	 *
	 * @var Config
	 */
	protected ?Config $config = null;

	/**
	 * Constructor.
	 *
	 * @param string $name        The name/slug of the application.
	 * @param string $directory   The root directory of the application.
	 * @param array  $controllers All of the applications controllers.
	 */
	public function __construct( string $name, string $directory, array $controllers ) {

		$this->name        = $name;
		$this->directory   = $directory;
		$this->controllers = $controllers;

		$this->load_config();

		add_action( 'plugins_loaded', [ $this, 'setup_controllers' ] );
	}

	/**
	 * Set up the application controllers.
	 * This should be called on `plugins_loaded`
	 *
	 * @return void
	 */
	public function setup_controllers(): void {

		// Set helper instances.
		$route = new Route();
		$rest  = new REST();
		$ajax  = new AdminAjax();

		foreach ( $this->controllers as $index => $controller ) {

			$controller = apply_filters( 'wpmvc_pre_controller_set_instances', $controller );

			$controller->set_route_instance( $route );
			$controller->set_rest_instance( $rest );
			$controller->set_admin_ajax_instance( $ajax );

			$controller = apply_filters( 'wpmvc_post_controller_set_instances', $controller );
			$controller = apply_filters( 'wpmvc_pre_controller_set_config', $controller );

			$controller->set_config_instance( $this->config );

			$controller = apply_filters( 'wpmvc_post_controller_set_config', $controller );
			$controller = apply_filters( 'wpmvc_pre_controller_set_up', $controller );

			$controller->set_up();

			$controller = apply_filters( 'wpmvc_post_controller_set_up', $controller );

			// Write back so filter-replaced instances are persisted for callers.
			$this->controllers[ $index ] = $controller;
		}
	}

	/**
	 * Get the application configuration object.
	 *
	 * @return Config
	 */
	public function get_config(): Config {

		return $this->config;
	}

	/**
	 * Returns the root directory path of the application.
	 *
	 * @return string
	 */
	public function get_directory(): string {

		return $this->directory;
	}

	/**
	 * Returns the name/slug of the application.
	 *
	 * @return string
	 */
	public function get_name(): string {

		return $this->name;
	}

	/**
	 * Load the application config from file.
	 *
	 * @return void
	 */
	protected function load_config(): void {

		$this->config = new Config( $this );
		$this->config->autoload();
	}
}
