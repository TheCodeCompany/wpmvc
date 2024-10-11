<?php
/**
 * PostFactory.
 *
 * @package WPMVC\Model
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * A factory which produces `PostModel` instances.  A `PostModel` instance
 * should not be instantiated directly.  It should always go via one of the
 * methods in this class.
 */
class PostModelFactory extends GenericPostModelFactory {

	/** Gets post type of the custom post type associated with this model factory */

	/**
	 * Get the model post type.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return PostModel::POST_TYPE;
	}

	/**
	 * Wrap a post object to get a model instance.
	 *
	 * @param object|\WP_Post $post The WP_Post to wrap.
	 *
	 * @return GenericPostModel|PostModel
	 */
	public function wrap( $post ) {
		// assert( ! empty( $post ) );

		return new PostModel( $post );
	}
}
