<?php
/**
 * Provides the TaxonomyTermModel class.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * A model instance which wraps a `WP_Term` instance. You should extend this
 * class for a custom taxonomy instance.
 *
 * @property int    $term_id
 * @property string $name
 * @property string $slug
 * @property int    $term_group
 * @property int    $term_taxonomy_id
 * @property string $taxonomy
 * @property string $description
 * @property int    $parent
 * @property int    $count
 * @property string $filter
 * @package wpmvc
 */
#[\AllowDynamicProperties]
class TaxonomyTermModel extends WPModel implements WPMeta {

	const TAXONOMY_NAME_POST_TAG = 'post_tag';
	const TAXONOMY_NAME_DEFAULT  = self::TAXONOMY_NAME_POST_TAG;

	const FIELD_TERM_ID          = 'term_id';
	const FIELD_TERM_TAXONOMY_ID = 'term_taxonomy_id';

	/**
	 * The term associated with the model instance.
	 *
	 * @var \WP_Term|\WP_Error|null
	 */
	protected \WP_Term|\WP_Error|null $term;

	/**
	 * TaxonomyTermModel constructor.
	 *
	 * @param int|\WP_Term $term The term to wrap. Term ID is accepted but discouraged.
	 */
	public function __construct( $term = 0 ) {

		if ( $term instanceof \WP_Term ) {
			$this->term = $term;
		} else {
			$this->term = get_term( $term );
		}
	}

	/**
	 * Get underlying `WP_Term` field value.
	 *
	 * @param string $name Field name/slug.
	 *
	 * @return mixed
	 */
	public function __get( string $name ): mixed {
		$value = null;

		if ( isset( $this->term->$name ) ) {
			$value = $this->term->$name;
		} elseif ( property_exists( $this, $name ) ) {
			$value = $this->$name;
		}

		return $value;
	}

	/**
	 * Set underlying `WP_Term` field value.
	 *
	 * @param string $name  Field name/slug.
	 * @param mixed  $value New field value.
	 */
	public function __set( string $name, mixed $value ): void {
		if ( isset( $this->term->$name ) ) {
			$this->term->$name = $value;
		} else {
			$this->$name = $value;
		}
	}

	/**
	 * Get the term object this instance wraps.
	 *
	 * @return array|int|object|\WP_Error|\WP_Term|null
	 */
	public function get_wp_term(): \WP_Term|\WP_Error|null {
		return $this->term;
	}

	/**
	 * Shortcut method for `wp_update_term()`.
	 *
	 * @param array $args The `wp_update_term()` arguments.
	 *
	 * @return array|int|object|\WP_Error|\WP_Term|null
	 * @deprecated Use a factory to update a model.
	 */
	public function update( array $args ): array|\WP_Error {

		$term_id  = $this->term->term_id;
		$taxonomy = $this->term->taxonomy;

		return wp_update_term( $term_id, $taxonomy, $args );
	}

	/**
	 * Returns the given meta field - get_term_meta().
	 *
	 * @param string|null $key    The meta key to get for the term.
	 * @param bool        $single Whether to get a single value, or array of all metas. Default `true`.
	 *
	 * @return mixed
	 */
	public function get_meta( ?string $key = null, bool $single = true ): mixed {
		return get_term_meta( $this->term->term_id, $key, $single );
	}

	/**
	 * Sets the given meta field - update_term_meta().
	 *
	 * @param string $key   The meta key to set for the term.
	 * @param mixed  $value The value to set the meta field to.
	 *
	 * @return int|bool
	 */
	public function set_meta( string $key, mixed $value ): int|bool {
		return update_term_meta( $this->term->term_id, $key, $value );
	}

	/**
	 * Adds the given meta field - add_term_meta().
	 *
	 * @param string $key    The meta key to add for the term.
	 * @param mixed  $value  The value to set the meta field to.
	 * @param bool   $unique Whether the meta key should be unique. Default false.
	 *
	 * @return int|bool
	 */
	public function add_meta( string $key, mixed $value, bool $unique = false ): int|bool {
		return add_term_meta( $this->term->term_id, $key, $value, $unique );
	}

	/**
	 * Deletes the given meta field - delete_term_meta().
	 *
	 * @param string $key   The meta key to delete for the term.
	 * @param string $value Optionally limit deletion to entries with this value.
	 *
	 * @return bool False for failure. True for success.
	 */
	public function delete_meta( string $key, string $value = '' ): bool {
		return delete_term_meta( $this->term->term_id, $key, $value );
	}
}
