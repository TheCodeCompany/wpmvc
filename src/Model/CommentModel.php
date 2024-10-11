<?php
/**
 * Provides the CommentModel class.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * A model instance which wraps a `WP_Comment` instance.
 * You should extend this class for a comment model type. I.e. for comments attached to a specific CPT.
 */
class CommentModel extends WPModel implements WPMeta {

	/**
	 * Wrapped WP_Comment
	 *
	 * @var \WP_Comment
	 */
	protected $comment;

	/**
	 * CommentModel constructor.
	 *
	 * @param int|\WP_Comment $comment WP_Comment to wrap, or ID. ID is discouraged.
	 */
	public function __construct( $comment = 0 ) {

		if ( 'object' === (string) gettype( $comment ) ) {
			$this->comment = $comment;
		} else {
			$this->comment = get_comment( $comment );
		}
	}

	/**
	 * Update comment object.
	 * Uses `wp_update_comment()` under the hood.
	 *
	 * @param array $args The `wp_update_comment()` arguments. ID is automatically added.
	 *
	 * @return int
	 * @deprecated Use a factory to update a model.
	 */
	public function update( array $args ) {
		$outcome = 0;

		$args['ID'] = $this->comment->comment_ID;

		$outcome = wp_update_comment( $args );

		return $outcome;
	}

	/**
	 * Get underlying `WP_Comment` field value.
	 *
	 * @param string $name Field name/slug.
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		$value = null;

		if ( property_exists( $this->comment, $name ) ) {
			$value = $this->comment->$name;
		} else {
			$value = $this->$name;
		}

		return $value;
	}

	/**
	 * Set underlying `WP_Comment` field value.
	 *
	 * @param string $name  Field name/slug.
	 * @param mixed  $value New field value.
	 */
	public function __set( $name, $value ) {
		if ( property_exists( $this->comment, $name ) ) {
			$this->comment->$name = $value;
		} else {
			$this->$name = $value;
		}
	}

	/**
	 * Get the WP_Comment instance.
	 *
	 * @return array|int|mixed|\WP_Comment|null
	 */
	public function get_wp_comment() {
		return $this->comment;
	}

	/**
	 * Returns the given meta field.
	 * Uses `get_comment_meta()` under the hood.
	 *
	 * @param string  $key    The meta key to get for the comment.
	 * @param boolean $single Whether to get a single value, or array of all metas. Default `true`.
	 *
	 * @return mixed
	 */
	public function get_meta( $key = null, $single = true ) {
		$meta = get_comment_meta( $this->comment->comment_ID, $key, $single );

		return $meta;
	}

	/**
	 * Sets the given meta field.
	 * Uses `update_comment_meta()` under the hood.
	 *
	 * @param string $key   The meta key to get for the comment.
	 * @param mixed  $value The value to set the meta field to.
	 *
	 * @return bool|int
	 */
	public function set_meta( $key, $value ) {
		$outcome = update_comment_meta( $this->comment->comment_ID, $key, $value );

		return $outcome;
	}

	/**
	 * Adds the given meta field.
	 * Uses `add_comment_meta()` under the hood.
	 *
	 * @param string $key   The meta key to get for the comment.
	 * @param mixed  $value The value to set the meta field to.
	 *
	 * @return bool|int
	 */
	public function add_meta( $key, $value ) {
		$outcome = add_comment_meta( $this->comment->comment_ID, $key, $value );

		return $outcome;
	}

	/**
	 * Deletes the given meta field - delete_comment_meta().
	 *
	 * @param string $key   The meta key to get for the comment.
	 * @param string $value The value to set the meta field to.
	 *
	 * @return bool `false` for failure, `true` for success.
	 */
	public function delete_meta( $key, $value = '' ) {
		$outcome = delete_comment_meta( $this->comment->comment_ID, $key, $value );

		return $outcome;
	}
}
