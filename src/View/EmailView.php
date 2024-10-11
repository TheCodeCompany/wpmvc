<?php
/**
 * Generic email view.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\View;

use \WPMVC\Core\View;
use \WPMVC\Library\Config;
use \WPMVC\Library\Templater;

/**
 * A generic email view.  Can be used like so:
 *  $email = new EmailView( $config, 'my-email' );
 *  $email->set_param( 'param1', 'Hello' );
 *  $email->set_param( 'param2', 'World' );
 *  // special parameters
 *  $email->set_param( 'subject', 'My subject line' );
 *  $email->set_param( 'cc', 'ccme@test.com' );
 *  $email->set_param( 'bcc', 'bccme@test.com' );
 *  $email->set_param( 'replyto', 'reply@test.com' );
 *  // send it
 *  $email->send( 'test@email.com' );
 * # Template Location
 * The fallback template directory in the application is `<app directory>/template/<app name>`.  The theme templates should
 * be under the `{theme}/<app name>` directory.
 */
class EmailView extends View {

	/**
	 * Application config object.
	 *
	 * @var Config
	 */
	protected $config;

	/**
	 * The email template file.
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * Email headers.
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Files to attach.
	 *
	 * @var array
	 */
	protected $attachments = [];

	/**
	 * Constructor.
	 *
	 * @param Config $config   App configuration object.
	 * @param string $template The email template file.
	 */
	public function __construct( Config $config, $template ) {

		$this->config   = $config;
		$this->template = $template;

		// Force html content type for emails.
		add_filter(
			'wp_mail_content_type',
			function () {
				return 'text/html';
			}
		);

		add_filter(
			'wp_mail_charset',
			function () {
				return 'UTF-8';
			}
		);

	}

	/**
	 * Attach the given file to the email.
	 *
	 * @param string $filename Absolute path of the file to attach.
	 */
	public function attach( $filename ) {

		//assert( file_exists( $filename ) );

		$this->attachments[] = $filename;

	}

	/**
	 * Generates the email and sends it to the given recipient.
	 *
	 * @param string $to The email recipient.
	 *
	 * @return boolean Whether the email was sent correctly.  NOTE does not mean that it was received properly.
	 */
	public function send( $to ) {
		$success = false;

		//assert( ! empty( $to ) );

		// Recurse if the to field is an array of email addresses.
		if ( is_array( $to ) ) {

			foreach ( $to as $recipient ) {
				$this->send( $recipient );
			}

			return $success;
		}

		// Build the email subject template.
		$subject_templater = new Templater(
			[
				'slug'   => $this->template . '-subject',
				'dir'    => $this->config->get_app_directory(),
				'params' => $this->params,
			]
		);

		$subject = $subject_templater->render( false );

		// Build the email content template.
		$content_templater = new Templater(
			[
				'slug'   => $this->template,
				'dir'    => $this->config->get_app_directory(),
				'params' => $this->params,
			]
		);

		$content_templater->subject = $subject;

		$content = $content_templater->render( false );

		// Now send the email.
		$success = wp_mail(
			$to,
			$this->shortcodes( $subject ),
			$this->shortcodes( $content ),
			$this->headers,
			$this->attachments
		);

		return $success;

	}

	/**
	 * Performs the shortcode substitution for the given content.
	 *
	 * @param string $content Content to filter.
	 *
	 * @return string
	 */
	protected function shortcodes( $content ) {

		//assert( ! empty( $content ) );

		// Perform shortcode replacement.
		foreach ( $this->params as $key => $value ) {

			if ( is_string( $value ) ) {

				$content = preg_replace(
					'{{{(| )' . $key . '( |)}}}',
					$value,
					$content
				);

			}
		}

		return $content;

	}

}
