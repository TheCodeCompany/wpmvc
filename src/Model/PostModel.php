<?php
/**
 * PostModel.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * A model instance which wraps a `WP_Post` instance.  You should extend this
 * class for a CPT instance.
 *
 * @package wpmvc
 */
class PostModel extends GenericPostModel {

	const POST_TYPE = self::POST_TYPE_DEFAULT;
}
