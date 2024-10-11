<?php
/**
 * PageModelFactory.
 *
 * @package WPMVC\Model
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * Model factory for WordPress pages.
 *
 * @package WPMVC\Model
 */
class PageModelFactory extends GenericPostModelFactory {

	/**
	 * Get the model post type.
	 *
	 * @return string
	 */
	public function get_post_type() {
		return PageModel::POST_TYPE;
	}

	/**
	 * Wrap a post object to get a model instance.
	 *
	 * @param object|\WP_Post $post The WP_Post to wrap.
	 *
	 * @return GenericPostModel|PageModel
	 */
	public function wrap( $post ) {
		//assert( ! empty( $post ) );

		return new PageModel( $post );
	}
}
