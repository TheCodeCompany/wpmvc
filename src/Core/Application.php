<?php
/**
 * Framework application manager.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Core;

use WPMVC\Library\Config;
use WPMVC\Library\ControllerSetup;
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
	protected $name = '';

	/**
	 * The root directory of the application.
	 *
	 * @var string
	 */
	protected $directory = '';

	/**
	 * All of the controllers defined in the application.
	 *
	 * @var array
	 */
	protected $controllers = [];

	/**
	 * The config helper instance.
	 *
	 * @var \WPMVC\Library\Config
	 */
	protected $config = null;

	/**
	 * Constructor.
	 *
	 * @param string $name        The name/slug of the application.
	 * @param string $directory   The root directory of the application.
	 * @param array  $controllers All of the applications controllers.
	 */
	public function __construct( $name, $directory, $controllers ) {

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
	public function setup_controllers() {

		// Set helper instances.
		$route = new Route();
		$rest  = new REST();
		$ajax  = new AdminAjax();
		foreach ( $this->controllers as $controller ) {

			/**
			 * Apply filters to the controller before we set instances.
			 */
			$controller = apply_filters( 'wpmvc_pre_controller_set_instances', $controller );

			$controller->set_route_instance( $route );
			$controller->set_rest_instance( $rest );
			$controller->set_admin_ajax_instance( $ajax ); // TODO: implement and test.

			/**
			 * Apply filters to the controller after we set instances.
			 */
			$controller = apply_filters( 'wpmvc_post_controller_set_instances', $controller );
		}

		// Set config.
		foreach ( $this->controllers as $controller ) {

			/**
			 * Apply filters to the controller before we set the config.
			 */
			$controller = apply_filters( 'wpmvc_pre_controller_set_config', $controller );

			$controller->set_config_instance( $this->config );

			/**
			 * Apply filters to the controller after we set the config.
			 */
			$controller = apply_filters( 'wpmvc_post_controller_set_config', $controller );
		}

		// Set up each controller.
		foreach ( $this->controllers as $controller ) {

			/**
			 * Apply filters to the controller before we set it up.
			 */
			$controller = apply_filters( 'wpmvc_pre_controller_set_up', $controller );

			$controller->set_up();

			/**
			 * Apply filters to the controller after we set it up.
			 */
			$controller = apply_filters( 'wpmvc_post_controller_set_up', $controller );
		}
	}

	/**
	 * Get the application configuration object.
	 *
	 * @return Config
	 */
	public function get_config() {

		return $this->config;
	}

	/**
	 * Returns the root directory path of the application.
	 *
	 * @return string
	 */
	public function get_directory() {

		return $this->directory;
	}

	/**
	 * Returns the name/slug of the application.
	 *
	 * @return string
	 */
	public function get_name() {

		return $this->name;
	}

	/**
	 * Load the application config from file.
	 *
	 * @return void
	 */
	protected function load_config() {

		$this->config = new Config( $this );
		$this->config->autoload();
	}
}
