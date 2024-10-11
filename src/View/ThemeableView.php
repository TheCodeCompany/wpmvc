<?php
/**
 * HTML view which can use templates in the theme.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\View;

use WPMVC\Core\View;
use WPMVC\Library\Config;
use WPMVC\Library\Templater;

/**
 * A HTML view which uses template files in the WP theme.  It is backed by the `Templater` class so it can also use a
 * fallback template defined in the application directory.
 * Parameters can be passed to the template by setting the view properties.  See example below.
 * ## Example
 * An example from a controller instance:
 *      $view = new ThemeableView(
 *          $this->config,
 *          'my-template'
 *      );
 *      $view->set_param( 'param1', 'Hello' );
 *      $view->set_param( 'param2', 'Hello' );
 *      $view->render( 'my-template' );
 * # Template Location
 * The fallback template directory in the application is `<app directory>/template/<app name>`.  The theme templates should
 * be under the `{theme}/<app name>` directory.
 */
class ThemeableView extends View {

	/**
	 * Application config object.
	 *
	 * @var object
	 */
	protected $config;

	/**
	 * Constructor.
	 *
	 * @param Config $config   Application configuration object.
	 * @param string $template Slug of the template to use.
	 */
	public function __construct( Config $config, $template ) {

		$this->config   = $config;
		$this->template = $template;
	}

	/**
	 * Renders the given template file.  By default, it will use the template in the theme.  If there is no template in
	 * the theme, it will look in the 'template' directory in the application directory.
	 *
	 * @param boolean $output Whether to output the content or return as string (default true).
	 */
	public function render( $output = true ) {

		// Build the template.
		$templater = new Templater(
			[
				'slug'   => $this->template,
				'dir'    => $this->config->get_app_directory(),
				'params' => $this->params,
			]
		);

		return $templater->render( $output );
	}
}
