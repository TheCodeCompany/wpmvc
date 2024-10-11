<?php
/**
 * TaxonomyModelFactory
 *
 * @package WPMVC\Model
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * Factory for WordPress taxonomies.
 *
 * @package WPMVC\Model
 */
class TaxonomyModelFactory extends WPModelFactory {

	/**
	 * Gets the wrapped post for the given post ID if it exists and is valid.
	 *
	 * @param string $name Name of the taxonomy.
	 *
	 * @return TaxonomyModel|null
	 */
	public function get_by_name( $name ) {
		$taxonomy = null;

		// Get the WP_Taxonomy object with the given name..
		$wp_taxonomy = get_taxonomy( $name );

		if ( ! empty( $wp_taxonomy ) ) {
			$taxonomy = $this->wrap( $wp_taxonomy );
		}

		return $taxonomy;
	}

	/*
	 * TODO
	 * - Could implement register_taxonomy functionality.
	 * - Would also open up register_post_type functionality.
	 * -- This should be implemented in a separate module, outside of core MVC.
	 */

	public function wrap( $taxonomy ) {
		return new TaxonomyModel( $taxonomy );
	}
}
