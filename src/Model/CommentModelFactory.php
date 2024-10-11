<?php
/**
 * Provides the CommentModelFactory class.
 *
 * @package WPMVC\Model
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

use function get_comments;

/**
 * A factory which produces `CommentModel` instances.
 * A `CommentModel` instance should not be instantiated directly. It should always go via one of the methods in this class.
 *
 * @package WPMVC\Model
 */
class CommentModelFactory extends WPModelFactory {

	/**
	 * Returns a `CommentModel` with the given ID.
	 *
	 * @param int $id ID of the comment to get.
	 *
	 * @return null|CommentModel
	 */
	public function get_by_id( $id ) {
		$commend = null;

		$wp_comment = get_comment( $id );
		if ( ! empty( $wp_comment ) ) {
			$comment = $this->wrap( $wp_comment );
		}

		return $comment;
	}

	/**
	 * Get comment model instances that match the arguments.
	 *
	 * @param array $args get_comments() arguments.
	 *
	 * @return array
	 */
	public function get_comments( $args ) {
		$comments = [];

		$query_args = $this->get_query_args( $args );

		$wp_comments = get_comments( $query_args );
		if ( ! empty( $wp_comments ) ) {
			$comments = $this->wrap_models( $wp_comments );
		}

		return $comments;
	}

	/**
	 * Get the query arguments. Mix the given arguments with the standard
	 * default arguments for the factory.
	 *
	 * @param array $query_args Specific query arguments to mix with the
	 *                          defaults.
	 *
	 * @return array
	 */
	protected function get_query_args( $query_args ) {
		$args = [];

		// There are no base arguments for this factory.
		$default_args = [];

		$args = array_merge( $default_args, $query_args );

		return $args;
	}

	/**
	 * Inserts a comment into the database.
	 *
	 * @param array $insert_comment_args The `wp_insert_comment()` args.
	 *
	 * @return CommentModel|null
	 */
	public function insert_comment( $insert_comment_args ) {
		$comment = null;

		// Insert the comment object.
		$id = wp_insert_comment( $insert_comment_args );

		if ( false !== $id ) {
			$comment = $this->get_by_id( $id );
		}

		return $comment;
	}

	/**
	 * Creates a new comment and sets meta data.
	 *
	 * @param array $insert_comment_args The `wp_insert_comment()` args.
	 * @param array $meta_fields         The meta fields to add. E.g. [ 'meta_key' => 'meta_value' ].
	 *
	 * @return CommentModel|null if failed.
	 */
	public function create_comment( $insert_comment_args, $meta_fields = [] ) {
		$comment = null;

		$comment = $this->insert_comment( $insert_comment_args );

		if ( ! empty( $comment ) ) {

			// Set comment meta data.
			if ( ! empty( $meta_fields ) ) {
				$this->set_comment_meta( $meta_fields );
			}
		}

		return $comment;
	}

	/**
	 * Update the comment.
	 * Uses `wp_update_comment`.
	 *
	 * @param CommentModel $comment      The comment being updated.
	 * @param array        $comment_args The `wp_update_comment()` arguments. ID is automatically added.
	 *
	 * @return CommentModel|null
	 */
	public function update_comment( $comment, $comment_args = [] ) {
		$updated_comment = null;

		if ( empty( $comment_args ) ) {
			$comment_args = get_object_vars( $comment->get_wp_comment() );
		}

		if ( is_array( $comment_args ) ) {

			// Ensure the ID is included in the update arguments.
			$comment_args['comment_ID'] = $comment->comment_ID;
		}

		$outcome = wp_update_comment( $comment_args );

		if ( 1 === $outcome ) {
			$this->get_by_id( $comment->comment_ID );
		}

		return $updated_comment;
	}

	/**
	 * Delete a comment.
	 *
	 * @param CommentModel $comment      The comment to delete.
	 * @param bool         $force_delete Whether to force delete the comment out of the DB.
	 *
	 * @return bool.
	 */
	public function delete_comment( $comment, $force_delete = false ) {
		return $this->delete_comment_by_id( $comment->ID, $force_delete );
	}

	/**
	 * Trash or delete a comment by ID.
	 *
	 * @param int  $comment_id   Comment ID.
	 * @param bool $force_delete Optional. Whether to bypass trash and force deletion.
	 *
	 * @return bool.
	 */
	public function delete_comment_by_id( $comment_id, $force_delete = false ) {
		return wp_delete_comment( $comment_id, $force_delete );
	}

	/**
	 * Set the meta data for the given comment.
	 *
	 * @param CommentModel $comment     The comment to set the meta for.
	 * @param array        $meta_fields The key/value meta data.
	 */
	public function set_comment_meta( $comment, $meta_fields ) {
		foreach ( $meta_fields as $key => $value ) {
			if ( empty( $value ) ) {
				$comment->delete_meta( $key );
			} elseif ( is_array( $value ) ) {
					$comment->delete_meta( $key );

					// Arrays are added as multiple, separate values.
				foreach ( $value as $val ) {
					$comment->add_meta( $key, $val );
				}
			} else {
				$comment->set_meta( $key, $value );
			}
		}
	}

	/**
	 * Wrap the given WP model instances.
	 *
	 * @param array $wp_models WordPress model instances.
	 *
	 * @return array
	 */
	public function wrap_models( $wp_models ) {
		$models = [];

		foreach ( $wp_models as $wp_model ) {
			$models[] = $this->wrap( $wp_model );
		}

		return $models;
	}

	/**
	 * Returns a `CommentModel` instance which wraps the given `WP_Comment`.
	 *
	 * @param \WP_Comment $wp_comment Comment to wrap.
	 *
	 * @return CommentModel
	 */
	public function wrap( $wp_comment ) {
		return new CommentModel( $wp_comment );
	}
}
