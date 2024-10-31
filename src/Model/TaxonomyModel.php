<?php
/**
 * Provides the TaxonomyModel class.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * Factory for Taxonomies in WordPress.
 *
 * @package wpmvc
 */
class TaxonomyModel extends WPModel {

	/**
	 * The WP_Taxonomy instance for the model.
	 *
	 * @var \WP_Taxonomy
	 */
	protected $taxonomy;

	/**
	 * TaxonomyModel constructor.
	 *
	 * @param null|\WP_Taxonomy|string $taxonomy The taxonomy instance.
	 */
	public function __construct( $taxonomy = null ) {
		// assert( ! empty( $taxonomy ) );

		if ( 'object' === (string) gettype( $taxonomy ) ) {
			$this->taxonomy = $taxonomy;
		} else {
			$this->taxonomy = get_taxonomy( $taxonomy );
		}
	}

	/**
	 * Get underlying `WP_Taxonomy` field value.
	 *
	 * @param string $name Field name/slug.
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		$value = null;

		if ( property_exists( $this->taxonomy, $name ) ) {
			$value = $this->taxonomy->$name;
		} elseif ( property_exists( $this, $name ) ) {
			$value = $this->$name;
		}

		return $value;
	}

	/**
	 * Set underlying `WP_Taxonomy` field value.
	 *
	 * @param string $name  Field name/slug.
	 * @param mixed  $value New field value.
	 */
	public function __set( $name, $value ) {
		if ( property_exists( $this->taxonomy, $name ) ) {
			$this->taxonomy->$name = $value;
		} else {
			$this->$name = $value;
		}
	}

	/**
	 * Get the term object this instance wraps.
	 *
	 * @return bool|false|string|\WP_Taxonomy
	 */
	public function get_wp_taxonomy() {
		return $this->taxonomy;
	}
}
