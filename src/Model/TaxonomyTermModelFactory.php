<?php
/**
 * Provides the TaxonomyTermModelFactory class.
 *
 * @package WPMVC\Model
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * Base factory for taxonomy term models.
 *
 * @package WPMVC\Model
 */
class TaxonomyTermModelFactory extends WPModelFactory {

	// Return output types.
	const OUTPUT_WP_ID         = 'term_id'; // Term ID.
	const OUTPUT_WP_MODEL      = 'wp_object'; // WP_Term instance.
	const OUTPUT_MODEL_WRAPPER = 'model_wrapper'; // TaxonomyTermModel family instance.
	const OUTPUT_ARRAY_A       = 'array_a'; // An associative array of terms.
	const OUTPUT_DEFAULT       = self::OUTPUT_MODEL_WRAPPER;

	/**
	 * Get a taxonomy term by ID.
	 *
	 * @param string|int $id The term ID.
	 *
	 * @return TaxonomyTermModel|null
	 */
	public function get_by_id( $id ) {
		$term = null;

		$wp_term = get_term( $id, $this->get_taxonomy() );

		if ( ! empty( $wp_term ) ) {
			$term = $this->wrap( $wp_term );
		}

		return $term;
	}

	/**
	 * Build the taxonomy term object by a provided field, value and taxonomy.
	 *
	 * @param string           $field    Either 'id', 'slug', 'name', or 'term_taxonomy_id'.
	 * @param string | integer $value    Search for this term value.
	 * @param string           $taxonomy Taxonomy Name category, post_tag, link_category, nav_menu or something custom.
	 *
	 * @return TaxonomyTermModel|false
	 */
	public function get_term_by( $field = 'id', $value = '', $taxonomy = '' ) {
		$term = false;

		if ( empty( $taxonomy ) ) {
			$taxonomy = $this->get_taxonomy();
		}

		// Get the term.
		$wp_term = get_term_by( $field, $value, $taxonomy );

		// Ensure that there is a term for the query provided.
		if ( ! empty( $wp_term ) ) {
			$term = $this->wrap( $wp_term );
		}

		return $term;
	}

	/**
	 * Get taxonomy terms based on the provided arguments.
	 *
	 * @param array $args `get_terms` arguments.
	 *
	 * @return array
	 */
	public function get_terms( $args = [], $output = self::OUTPUT_DEFAULT ) {
		$terms = [];

		// Define the default args.
		$default_args = [
			'hide_empty' => false,
		];

		// Merge the default options into the existing args.
		$args = array_merge( $default_args, $args );

		$query_args = $this->get_query_args( $args );

		// Get the terms.
		$wp_terms = get_terms( $query_args );

		if ( ! is_wp_error( $wp_terms ) ) {
			$terms = $this->convert_models_to_output( $wp_terms, $output );
		}

		// Return the results.
		return $terms;
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

		$default_args = [];

		// Restrict results by factory taxonomy.
		$taxonomy = $this->get_taxonomy();
		if ( ! empty( $taxonomy ) ) {
			$default_args['taxonomy'] = $taxonomy;
		}

		$args = array_merge( $default_args, $query_args );

		return $args;
	}

	/**
	 * Get the terms for the given post model object. Shortcut for WordPress
	 * `get_the_terms()` function that also wraps results before returning.
	 * Note that `get_the_terms()` pulls from the object term cache, no the
	 * database each time.
	 *
	 * @param int|\WP_Post $post_id Post ID or instance of WP_Post - note
	 *                              that an instance of GenericPost will not work, pass the ID instead.
	 * @param string       $output  Optional. Output format for results.
	 *
	 * @return array|\WP_Error Array of terms, empty array if none found (as
	 * opposed to false in `get_the_terms()`). WP_Error object if an error
	 * occurred.
	 */
	public function get_the_terms( $post_id, $output = self::OUTPUT_DEFAULT ) {
		$terms = [];

		$wp_terms = get_the_terms( $post_id, $this->get_taxonomy() );

		if ( false !== $wp_terms ) {
			$terms = $this->convert_models_to_output( $wp_terms, $output );
		}

		return $terms;
	}

	/**
	 * Converts the array of `WP_Term` objects into the desired output
	 *
	 * @param array  $terms  Array of WP_Terms to convert.
	 * @param string $output Output format for results.
	 *
	 * @return array
	 */
	public function convert_models_to_output( $terms, $output = self::OUTPUT_DEFAULT ) {
		$converted_terms = [];

		if ( 'term' === $output ) {
			$converted_terms = $terms;
		} elseif ( self::OUTPUT_ARRAY_A === $output ) {
			$parent_groups = [];

			// Group each term by their parent.
			foreach ( $terms as $term ) {
				$parent_groups[ $term->parent ][ $term->term_id ] = $term->name;
			}

			// Check that the top level parent group exists.
			if ( isset( $parent_groups[0] ) ) {
				$converted_terms = $parent_groups[0];

				// Adds the children to a given term group.
				$add_term_children = function ( &$converted_terms ) use ( $parent_groups, &$add_term_children ) {

					// Loop through all of the terms for this parent.
					foreach ( $converted_terms as $term_id => &$term_info ) {

						// Check if this term is a parent.
						if ( isset( $parent_groups[ $term_id ] ) ) {
							$term_info = array(
								'label'    => $term_info,
								'children' => $parent_groups[ $term_id ],
							);
							$add_term_children( $term_info['children'] );
						}
					}
				};

				// Load the top level terms.
				$add_term_children( $converted_terms );
			}
		} else {
			foreach ( $terms as $term ) {
				$converted_terms[] = $this->convert_model_to_output( $term, $output );
			}
		}

		return $converted_terms;
	}

	/**
	 * Convert the WP_Term object into the desired output.
	 *
	 * @param \WP_Term $term   The WP_Term to convert.
	 * @param string   $output Output format for results..
	 *
	 * @return TaxonomyTermModel|null|int
	 */
	public function convert_model_to_output( $term, $output = self::OUTPUT_DEFAULT ) {
		$converted_term = null;

		if ( self::OUTPUT_MODEL_WRAPPER === $output ) {
			$converted_term = $this->wrap( $term );
		} elseif ( self::OUTPUT_WP_ID === $output ) {
			$converted_term = $term->term_id;
		} elseif ( self::OUTPUT_WP_MODEL === $output ) {
			$converted_term = $term;
		} elseif ( self::OUTPUT_ARRAY_A === $output ) {
			$converted_term = $term;
		}

		return $converted_term;
	}

	/**
	 * Create a new term and set data.
	 *
	 * @param string $term_name   The term name to add or update.
	 * @param array  $term_args   Arguments for `wp_insert_term`.
	 * @param array  $meta_fields Meta fields to set.
	 *
	 * @return \WP_Error|TaxonomyTermModel|null
	 */
	public function create_term( $term_name, $term_args = [], $meta_fields = [] ) {
		$term = null;

		$term = $this->insert_term( $term_name, $term_args );
		if ( ! is_wp_error( $term ) ) {

			if ( ! empty( $term ) ) {

				// Set term meta data.
				if ( ! empty( $meta_fields ) ) {
					$this->set_term_meta( $term, $meta_fields );
				}
			}
		}

		return $term;
	}

	/**
	 * Add a new term to the database.
	 *
	 * @param string $term_name The term name to add or update.
	 * @param array  $args      Arguments for `wp_insert_term`.
	 *
	 * @return \WP_Error|TaxonomyTermModel|null
	 */
	public function insert_term( $term_name, $args = [] ) {
		$term = null;

		// Attempt to insert the new term.
		$wp_term = wp_insert_term( $term_name, $this->get_taxonomy(), $args );

		if ( ! is_wp_error( $wp_term ) ) {

			// Term successfully added, wrap it.
			$term = $this->wrap( $wp_term[ TaxonomyTermModel::FIELD_TERM_ID ] );
		} else {

			// Return the WP_Error.
			$term = $wp_term;
		}

		return $term;
	}

	/**
	 * Update a term with new data.
	 * Uses `wp_update_term`.
	 *
	 * @param TaxonomyTermModel $term             The term to update.
	 * @param array             $update_term_args Specify exactly what data to update.
	 * @param bool              $wp_error         Optional. Allow return of WP_Error on failure. Default true.
	 *
	 * @return \WP_Error|GenericPostModel|null The null or WP_Error on failure. The post model instance on success.
	 */
	public function update_term( $term, $update_term_args = [], $wp_error = true ) {
		$updated_term = null;

		if ( empty( $update_term_args ) ) {
			$update_term_args = get_object_vars( $term );
		}

		$term_id  = $term->term_id;
		$taxonomy = $term->taxonomy;

		$term_id_taxonomy_term_id = wp_update_term( $term_id, $taxonomy, $update_term_args );

		if ( ! is_wp_error( $term_id_taxonomy_term_id ) ) {

			$term_id = $term_id_taxonomy_term_id[ TaxonomyTermModel::FIELD_TERM_ID ];

			$updated_term = $this->get_by_id( $term_id );
		} elseif ( $wp_error ) {

			// Return the error object.
			$updated_term = $term_id_taxonomy_term_id;
		}

		return $updated_term;
	}

	/**
	 * Trash or delete a term.
	 *
	 * @param TaxonomyTermModel|\WP_Term $term Term to delete.
	 * @param array|string               $args Optional. Array of arguments to override the default term ID. Default empty array.
	 *
	 * @return array|bool|false|\WP_Post|null Post data on success, false or null on failure.
	 */
	public function delete_term( $term, $args = [] ) {
		return $this->delete_term_by_id( $term->term_id, $term->taxonomy, $args );
	}

	/**
	 * Trash or delete a term by ID and taxonomy.
	 *
	 * @param int          $term_id  Term ID.
	 * @param string       $taxonomy Term taxonomy.
	 * @param array|string $args     Optional. Array of arguments to override the default term ID. Default empty array.
	 *
	 * @return array|bool|false|\WP_Post|null Post data on success, false or null on failure.
	 */
	public function delete_term_by_id( $term_id, $taxonomy, $args = [] ) {
		return wp_delete_term( $term_id, $taxonomy, $args );
	}

	/**
	 * Set the meta data for the given term.
	 *
	 * @param TaxonomyTermModel $term        The term to set the meta for.
	 * @param array             $meta_fields The key/value meta data.
	 */
	public function set_term_meta( $term, $meta_fields ) {
		foreach ( $meta_fields as $key => $value ) {
			if ( empty( $value ) ) {
				$term->delete_meta( $key );
			} elseif ( is_array( $value ) ) {
					$term->delete_meta( $key );

					// Arrays will be added as multiple, separate values.
				foreach ( $value as $val ) {
					$term->add_meta( $key, $val );
				}
			} else {
				$term->set_meta( $key, $value );
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
	 * Get the taxonomy name the factory manages.
	 *
	 * @return string
	 */
	public function get_taxonomy() {
		return TaxonomyTermModel::TAXONOMY_NAME_DEFAULT;
	}

	/**
	 * Wrap a term to get a taxonomy term model.
	 *
	 * @param \WP_Term $term The term to wrap.
	 *
	 * @return TaxonomyTermModel
	 */
	public function wrap( $term ) {
		return new TaxonomyTermModel( $term );
	}
}
