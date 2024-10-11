<?php
/**
 * A view which displays one or more `Model`s.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Core;

/**
 * The base view class which all views should extend.
 */
abstract class View {

	/**
	 * Parameters to pass to the template.
	 *
	 * @var array
	 */
	protected $params;

	/**
	 * Set a parameter that will be passed to the template.
	 *
	 * @param string $name The name/slug of the parameter.
	 * @param mixed  $value The value of the parameter.
	 * @return void
	 */
	public function set_param( $name, $value ) {
		$this->params[ $name ] = $value;
	}

	/**
	 * Get the value of the given parameter.
	 *
	 * @param string $name The name/slug of the parameter.
	 * @return mixed
	 */
	public function get_param( $name ) {
		return $this->params[ $name ];
	}
}
