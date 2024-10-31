<?php
/**
 * PageModel.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * Model class for WordPress pages. Pages are a built-in post type used by
 * WordPress for custom web pages.
 *
 * @package wpmvc
 */
class PageModel extends GenericPostModel {

	const POST_TYPE = 'page';
}
