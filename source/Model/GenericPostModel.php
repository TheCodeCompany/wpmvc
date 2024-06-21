<?php
/**
 * Provides the GenericPostModel class.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * A model instance which wraps a `WP_Post` instance.
 * You should extend this class for each custom post type model instance.
 *
 * @property int    $ID                     The ID of the post
 * @property string $post_author            The post author's user ID (numeric string)
 * @property string $post_date              Format: 0000-00-00 00:00:00
 * @property string $post_date_gmt          Format: 0000-00-00 00:00:00
 * @property string $post_content           The full content of the post
 * @property string $post_title             The title of the post
 * @property string $post_excerpt           User-defined post excerpt
 * @property string $post_status            The post status.
 * @property string $comment_status         Returns: { open, closed }
 * @property string $ping_status            Returns: { open, closed }
 * @property string $post_password          Returns empty string if no password
 * @property string $post_name              The post's slug
 * @property string $to_ping                URLs queued to be pinged.
 * @property string $pinged                 URLs that have been pinged.
 * @property string $post_modified          Format: 0000-00-00 00:00:00
 * @property string $post_modified_gmt      Format: 0000-00-00 00:00:00
 * @property string $post_content_filtered  A utility DB field for post content.
 * @property int    $post_parent            Parent Post ID (default 0)
 * @property string $guid                   The post globally unique identifier.
 * @property string $menu_order             Order value as set through page-attribute when enabled (numeric string. Defaults to 0)
 * @property string $post_type              The post type.
 * @property string $post_mime_type         The post type.
 * @property string $comment_count          Number of comments on post (numeric string)
 * @package WPMVC\Model
 */
class GenericPostModel extends WPModel implements WPMeta, WPTaxonomyTerms {

	// Post Fields.
	const FIELD_ID                    = 'ID';
	const FIELD_POST_AUTHOR           = 'post_author';
	const FIELD_POST_DATE             = 'post_date';
	const FIELD_POST_DATE_GMT         = 'post_date_gmt';
	const FIELD_POST_CONTENT          = 'post_content';
	const FIELD_POST_TITLE            = 'post_title';
	const FIELD_POST_EXCERPT          = 'post_excerpt';
	const FIELD_POST_STATUS           = 'post_status';
	const FIELD_COMMENT_STATUS        = 'comment_status';
	const FIELD_PING_STATUS           = 'ping_status';
	const FIELD_POST_PASSWORD         = 'post_password';
	const FIELD_POST_NAME             = 'post_name';
	const FIELD_TO_PING               = 'to_ping';
	const FIELD_PINGED                = 'pinged';
	const FIELD_POST_MODIFIED         = 'post_modified';
	const FIELD_POST_MODIFIED_GMT     = 'post_modified_gmt';
	const FIELD_POST_CONTENT_FILTERED = 'post_content_filtered';
	const FIELD_POST_PARENT           = 'post_parent';
	const FIELD_GUID                  = 'guid';
	const FIELD_MENU_ORDER            = 'menu_order';
	const FIELD_POST_TYPE             = 'post_type';
	const FIELD_POST_MIME_TYPE        = 'post_mime_type';
	const FIELD_COMMENT_COUNT         = 'comment_count';

	// Post statuses.
	const POST_STATUS_PUBLISH    = 'publish';
	const POST_STATUS_FUTURE     = 'future';
	const POST_STATUS_DRAFT      = 'draft';
	const POST_STATUS_PENDING    = 'pending';
	const POST_STATUS_PRIVATE    = 'private';
	const POST_STATUS_TRASH      = 'trash';
	const POST_STATUS_AUTO_DRAFT = 'auto-draft';
	const POST_STATUS_INHERIT    = 'inherit';

	// The default post type in WordPress.
	const POST_TYPE_DEFAULT = 'post';

	// Catchall term for any post type in WordPress.
	const POST_TYPE_ANY = 'any';

	// Catchall term for any post status in WordPress.
	const POST_STATUS_ANY = 'any';

	/**
	 * The WordPress post instance. The foundation for instance data.
	 *
	 * @var \WP_Post
	 */
	protected $post;

	/**
	 * GenericPostModel constructor.
	 *
	 * @param \WP_Post|int $post `WP_Post` instance. Post ID is accepted but is discouraged.
	 */
	public function __construct( $post = null ) {

		// If an ID was passed, retrieved the post object.
		if ( 'object' !== (string) gettype( $post ) ) {
			$post = get_post( $post );
		}

		$this->post = $post;
	}

	/**
	 * Update the post in the database.
	 * Uses `wp_update_post()` under the hood.
	 *
	 * @param array $args The `wp_update_post()` arguments. ID is automatically added.
	 *
	 * @return int|\WP_Error
	 * @deprecated Use a model factory to update a post.
	 */
	public function update( $args ) {
		$outcome = 0;

		$args['ID'] = $this->post->ID;

		$outcome = wp_update_post( $args );

		return $outcome;
	}

	/**
	 * Delete this post.
	 * Uses `wp_delete_post()` under the hood.
	 *
	 * @param boolean $force_delete Whether to bypass trash and force deletion.
	 *
	 * @return boolean The post object (if it was deleted or moved to the trash successfully) or false (failure). If the post was moved to the trash, $post
	 *                 represents its new state; if it was deleted, $post represents its state before deletion.
	 * @deprecated Use a model factory to delete a post.
	 */
	public function delete_post( $force_delete = true ) {
		return wp_delete_post( $this->ID, $force_delete );
	}

	/**
	 * Get underlying `WP_Post` field value.
	 *
	 * @param string $name Field name/slug.
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		$value = null;

		if ( isset( $this->post->$name ) ) {
			$value = $this->post->$name;
		} elseif ( isset( $this->$name ) ) {
			$value = $this->$name;
		}

		return $value;
	}

	/**
	 * Set underlying `WP_Post` field value.
	 *
	 * @param string $name  Field name/slug.
	 * @param mixed  $value New field value.
	 */
	public function __set( $name, $value ) {
		if ( isset( $this->post->$name ) ) {
			$this->post->$name = $value;
		} else {
			$this->$name = $value;
		}
	}

	/**
	 * Get the WP Post object this instance wraps.
	 *
	 * @return array|int|\WP_Post|null
	 */
	public function get_wp_post() {
		return $this->post;
	}

	/**
	 * Returns the given meta field.
	 * Uses `get_post_meta()` under the hood.
	 *
	 * @param string  $key    The meta key to get for the post.
	 * @param boolean $single Whether to get a single value, or array of all metas. Default `true`.
	 *
	 * @return mixed
	 */
	public function get_meta( $key = null, $single = true ) {
		return get_post_meta( $this->post->ID, $key, $single );
	}

	/**
	 * Sets the given meta field.
	 * Uses `update_post_meta()` under the hood.
	 *
	 * @param string $key   The meta key to get for the post.
	 * @param mixed  $value The value to set the meta field to.
	 *
	 * @return bool|int
	 */
	public function set_meta( $key, $value ) {
		return update_post_meta( $this->post->ID, $key, $value );
	}

	/**
	 * Adds the given meta field.
	 * Uses `add_post_meta()` under the hood.
	 *
	 * @param string $key   The meta key to get for the post.
	 * @param mixed  $value The value to set the meta field to.
	 *
	 * @return false|int
	 */
	public function add_meta( $key, $value ) {
		return add_post_meta( $this->post->ID, $key, $value );
	}

	/**
	 * Deletes the given meta field.
	 * Uses `delete_post_meta()` under the hood.
	 *
	 * @param string $key   The meta key to get for the post.
	 * @param string $value The value to set the meta field to.
	 *
	 * @return bool `false` for failure, `true` for success.
	 */
	public function delete_meta( $key, $value = '' ) {
		return delete_post_meta( $this->post->ID, $key, $value );
	}

	/**
	 * Retrieve the terms for a post.
	 * Calls `wp_get_post_terms()` for this post.
	 * Refer to;
	 * https://codex.wordpress.org/Function_Reference/wp_get_post_terms.
	 *
	 * @param string $taxonomy The taxonomy for which to retrieve terms.
	 *                         Defaults to post_tag.
	 * @param array  $args     Args to pass to `wp_get_post_terms()`.
	 *
	 * @return array|\WP_Error Array of terms or WP_Error if taxonomy does not
	 * exist
	 */
	public function get_terms( $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT, $args = [] ) {
		return wp_get_post_terms( $this->post->ID, $taxonomy, $args );
	}

	/**
	 * Adds new terms to the post object.
	 *
	 * @param array   $terms    List of terms. Can be an array or a comma separated string. If you want to enter terms of a hierarchical taxonomy like
	 *                          categories, then use IDs. If you want to add non-hierarchical terms like tags, then use names.
	 * @param string  $taxonomy Possible values for example: 'category', 'post_tag', 'taxonomy slug'.
	 * @param boolean $append   If true, tags will be appended to the post. If false, tags will replace existing tags.
	 *
	 * @return array|boolean|\WP_Error|string Array of terms or WP_Error if any issues occurred while processing the request.
	 */
	public function set_terms( $terms, $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT, $append = false ) {
		return wp_set_post_terms( $this->ID, ( is_array( $terms ) ? $terms : [ $terms ] ), $taxonomy, $append );
	}

	/**
	 * Removes all of the terms attached to this post from the provided taxonomy.
	 *
	 * @param string $taxonomy The taxonomy to remove all of the terms for.
	 *
	 * @return array|boolean|\WP_Error|string Array of terms or WP_Error if any issues occurred while processing the request.
	 */
	public function remove_terms( $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT ) {
		return wp_set_post_terms( $this->ID, [], $taxonomy, false );
	}

	/**
	 * Removes a term from this post.
	 *
	 * @param integer $term_id  The ID for the term that needs to be removed.
	 * @param string  $taxonomy The taxonomy name.
	 *
	 * @return mixed True on success, false or WP_Error on failure.
	 */
	public function remove_term( $term_id, $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT ) {
		return wp_remove_object_terms( $this->ID, $term_id, $taxonomy );
	}
}
