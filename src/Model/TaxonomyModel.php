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
#[\AllowDynamicProperties]
class TaxonomyModel extends WPModel {

	/**
	 * The WP_Taxonomy instance for the model.
	 *
	 * @var \WP_Taxonomy|false
	 */
	protected \WP_Taxonomy|false $taxonomy;

	/**
	 * TaxonomyModel constructor.
	 *
	 * @param null|\WP_Taxonomy|string $taxonomy The taxonomy instance.
	 */
	public function __construct( $taxonomy = null ) {

		if ( $taxonomy instanceof \WP_Taxonomy ) {
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
	public function __get( string $name ): mixed {
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
	public function __set( string $name, mixed $value ): void {
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
	public function get_wp_taxonomy(): \WP_Taxonomy|false {
		return $this->taxonomy;
	}
}
