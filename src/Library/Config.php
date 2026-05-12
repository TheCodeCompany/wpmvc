<?php
/**
 * Application configuration manager.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Library;

/**
 * Application configuration manager.
 * Use like so, in a controller, model or view:
 *  $my_config = $this->config->get( 'my_config' );
 *  echo $my_config['some_value'];
 *  // Shorthand:
 *  echo $this->config->$this->config->get( 'my_config', 'some_value' );
 * All of the application configuration is autoloaded from the /config/ and /config/local/ directories.
 */
class Config {

	/**
	 * The entire applications configuration.
	 * This contains all of the application configs, by config name/slug. Like so:
	 * [
	 *   'my_config' => [
	 *     'key' => 'value',
	 *   ],
	 *   ...
	 * ]
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * Application instance this config is for.
	 *
	 * @var \WPMVC\Core\Application
	 */
	protected $app;

	/**
	 * Creates a new config instance for the given application
	 *
	 * @param \WPMVC\Core\Application $app Application instance this config is for.
	 */
	public function __construct( \WPMVC\Core\Application $app ) {
		// TODO pass name and directory so we could use this is in a theme or something.

		$this->app = $app;
	}

	/**
	 * Returns the given configuration item.
	 *
	 * @param string $name    Configuration file slug/name.
	 * @param string $key     Options item in the configuration item in the config file.
	 * @param mixed  $default The default item/value if none is found.
	 *
	 * @return array|mixed|string
	 */
	public function get( $name, $key = '', $default = [] ) {

		$config_array = $this->config[ $name ] ?? null;

		if ( null === $config_array ) {
			return $default;
		}

		if ( ! empty( $key ) ) {
			return $config_array[ $key ] ?? $default;
		}

		return $config_array;
	}

	/**
	 * Get the name/slug of the application.
	 *
	 * @return string
	 */
	public function get_app_name() {

		return $this->app->get_name();
	}

	/**
	 * Get the root directory of the application.
	 *
	 * @return string
	 */
	public function get_app_directory() {

		return $this->app->get_directory();
	}

	/**
	 * Autoload the given application configuration from disk.
	 */
	public function autoload() {

		$app_config = [];
		$env_config = [];

		$dir = $this->app->get_directory();

		// Load the main configuration merging the default variables when required.
		$config_files = glob( "$dir/config/*.php" ) ?: [];
		foreach ( $config_files as $config_file ) {
			$config_name                = basename( $config_file, '.php' );
			$app_config[ $config_name ] = include $config_file;
		}

		// Select environment specific config files.
		$env_config_glob = "$dir/config/local/*.php";  // By default assume local dev.
		if ( defined( 'WP_ENV' ) ) {

			$env_name        = preg_replace( '/[^a-zA-Z0-9_-]/', '', WP_ENV );
			$env_config_glob = sprintf( '%s/config/%s/*.php', $dir, $env_name );

		}

		// Load each of the environment specific config files.
		$config_files = glob( $env_config_glob ) ?: [];
		foreach ( $config_files as $config_file ) {
			$config_name                = basename( $config_file, '.php' );
			$env_config[ $config_name ] = include $config_file;
		}

		// Merge the app configs with the environment specific overrides.
		$app_config_keys = array_keys( $app_config );
		$env_config_keys = array_keys( $env_config );
		$all_config_keys = array_merge( $app_config_keys, $env_config_keys );

		foreach ( $all_config_keys as $config_key ) {

			$app_config_value = $app_config[ $config_key ] ?? [];
			$env_config_value = $env_config[ $config_key ] ?? [];

			$this->config[ $config_key ] = array_replace_recursive( $app_config_value, $env_config_value );

		}
	}
}
