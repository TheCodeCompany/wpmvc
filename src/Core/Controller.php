<?php
/**
 * Base controller class which all controllers should extend.
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
 * Base controller class which all controllers should extend.
 * Controllers should be registered with the framework like so:
 * ```
 * $app = new Application(
 *  'example',
 *  [
 *    new MyController(),
 *    ...
 *  ]
 * );
 * ```
 */
abstract class Controller {

	/**
	 * The config helper instance.
	 * This is automatically set when the controller is booted.
	 *
	 * @var Config
	 */
	protected ?Config $config = null;

	/**
	 * Route helper instance.
	 * This is automatically set when the controller is booted.
	 *
	 * @var Route
	 */
	protected Route $route;

	/**
	 * REST helper instance.
	 * This is automatically set when the controller is booted.
	 *
	 * @var REST
	 */
	protected REST $rest;

	/**
	 * Admin AJAX helper instance.
	 * This is automatically set when the controller is booted.
	 *
	 * @var AdminAjax
	 */
	protected AdminAjax $admin_ajax;

	/**
	 * Called automatically at `plugins_loaded`.
	 * This must be overridden by child controllers.
	 *
	 * @return void
	 */
	abstract public function set_up(): void;

	/**
	 * Get the Config instance.
	 *
	 * @return Config
	 */
	public function get_config(): ?Config {
		return $this->config;
	}

	/**
	 * Get the Route instance.
	 *
	 * @return Route
	 */
	public function get_route(): Route {
		return $this->route;
	}

	/**
	 * Get the REST instance.
	 *
	 * @return REST
	 */
	public function get_rest(): REST {
		return $this->rest;
	}

	/**
	 * Get the AdminAjax instance.
	 *
	 * @return AdminAjax
	 */
	public function get_admin_ajax(): AdminAjax {
		return $this->admin_ajax;
	}

	/**
	 * Set the Config manager instance for the controller.
	 *
	 * @param Config $config Config manager instance the controller should use.
	 *
	 * @return void
	 */
	public function set_config_instance( Config $config ): void {

		$this->config = $config;
	}

	/**
	 * Set the Route object instance for the controller.
	 *
	 * @param Route $route Route helper instance the controller should use.
	 *
	 * @return void
	 */
	public function set_route_instance( Route $route ): void {

		$this->route = $route;
	}

	/**
	 * Set the REST object instance for the controller.
	 *
	 * @param REST $rest REST helper instance the controller should use.
	 *
	 * @return void
	 */
	public function set_rest_instance( REST $rest ): void {

		$this->rest = $rest;
	}

	/**
	 * Set the Admin Ajax object instance for the controller.
	 *
	 * @param AdminAjax $admin_ajax AdminAjax helper instance the controller should use.
	 *
	 * @return void
	 */
	public function set_admin_ajax_instance( AdminAjax $admin_ajax ): void {

		$this->admin_ajax = $admin_ajax;
	}
}
