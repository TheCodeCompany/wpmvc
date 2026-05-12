<?php
/**
 * Templating system.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Library;

/**
 * A simple pure PHP templating utility.
 * Simply executes a PHP file (the template) with a number of predefined variables.
 * <p>
 * The <code>Templater</code> can be used like so:
 * </p>
 * <pre>
 * $templater = new alyte\lib\Templater(array(
 *    'var' => 'value', ...
 *    # variables can be set at instatiation
 * ));
 * # the templater can also capture php output
 * # like so:
 * $templater->startVar('content');
 * ?&gt;
 *    &lt;p&gt; Some HTML Code!!&lt;/p&gt;
 * &lt?php
 * $templater->endVar();
 * # then apply a template file
 * $templater->serveTemplate('my/template/file');
 * </pre>
 * <p>
 * The template file is then just a simple PHP page, with the defined
 * variables.  Like so:
 * </p>
 * <pre>
 * ...
 * &ltbody&gt;
 *    &lt?php echo $content # content is a template variable ?&gt;
 * &lt/body&gt;
 * ...
 * </pre>
 * <p>
 * The class is loosely based on the function given in
 * <a href="http://www.bigsmoke.us/php-templates/functions">this</a> blog post.
 * </p>
 */
class Templater {

	/**
	 * Slug of the template file.
	 *
	 * @var string
	 */
	protected string $slug;

	/**
	 * Default template directory.
	 *
	 * @var string
	 */
	protected string $dir;

	/**
	 * System plugin directory
	 *
	 * @var string|null
	 */
	protected ?string $system_dir = null;

	/**
	 * Subdirectory the templates will live in
	 *
	 * @var string
	 */
	protected string $subdir;

	/**
	 * Template variables.
	 *
	 * @var array
	 */
	protected array $params = [];

	/**
	 * Constructor.
	 *
	 * @param array $args Template parameters.
	 *                    $slug The slug of the template file (i.e. the filename without the .php extensions).
	 *                    $var Bulk parameters to pass to the template.
	 */
	public function __construct( array $args ) {

		$this->slug   = $args['slug']   ?? '';
		$this->dir    = $args['dir']    ?? '';
		$this->subdir = $args['subdir'] ?? '';
		$this->params = $args['params'] ?? [];

		// Add in the template slug.
		$this->params['template_slug'] = $this->slug;
	}

	/**
	 * Set a parameter that will be passed to the template.
	 *
	 * @param string $name  The name/slug of the parameter.
	 * @param mixed  $value The value of the parameter.
	 *
	 * @return void
	 */
	public function set_param( string $name, mixed $value ): void {
		$this->params[ $name ] = $value;
	}

	/**
	 * Get the value of the given parameter.
	 *
	 * @param string $name The name/slug of the parameter.
	 *
	 * @return mixed
	 */
	public function get_param( string $name ): mixed {
		return $this->params[ $name ] ?? null;
	}

	/**
	 * Renders the template.
	 *
	 * @param bool $output Whether to output the rendered template, otherwise return it as a string.
	 *
	 * @return string|false|null String when $output is false; null when $output is true (outputs directly).
	 */
	public function render( bool $output = true ): string|false|null {

		if ( $output ) {

			$this->render_output();
			return null;

		} else {

			ob_start();
			$this->render_output();

			return ob_get_clean();
		}
	}

	/**
	 * Render the template and outputs the result.
	 *
	 * @return void
	 */
	protected function render_output(): void {

		$name  = $this->slug;
		$paths = [
			get_stylesheet_directory(),
			get_template_directory(),
			$this->dir . '/template',
		];

		// Loop through each of the paths checking if the file exists.
		foreach ( $paths as $path ) {

			// Handle whether a sub directory has been added.
			if ( ! empty( $this->subdir ) ) {
				$theme_view = "$path/$this->subdir/$name.php";
			} else {
				$theme_view = "$path/$name.php";
			}

			// Check if the file exists.
			if ( file_exists( $theme_view ) ) {
				$this->render_file( $theme_view );

				return;
			}
		}

		wp_die( esc_html( "Template does not exist; $name" ) );
	}

	/**
	 * Renders the template file.
	 *
	 * @param string $template_file The template file to render.
	 *
	 * @return void
	 */
	public function render_file( string $template_file ): void {

		// Extract the given params into the current scope.
		// NOTE we only do this for backward compat, this shouldn't actually be used in new builds.
		extract( $this->params ); // phpcs:ignore WordPress.PHP.DontExtract

		// Include and execute the template file.
		include $template_file;
	}
}
