<?php
/**
 * Provides the GenericPostFactory class.
 *
 * @package WPMVC\Model
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * Factory for `GenericPostModel`. Serves as the base factory for all
 * post-based models. A `Model` instance should not be instantiated directly,
 * instead it should be instantiated via a factory.
 *
 * @package WPMVC\Model
 */
class GenericPostModelFactory extends WPModelFactory {

	// Return output types.
	const OUTPUT_WP_ID         = 'post_id'; // Post ID.
	const OUTPUT_WP_MODEL      = 'wp_model'; // WP_Post instance.
	const OUTPUT_MODEL_WRAPPER = 'model_wrapper'; // GenericPostModel family instance.
	const OUTPUT_DEFAULT       = self::OUTPUT_MODEL_WRAPPER;

	/**
	 * Gets the wrapped post for the given post identifier.
	 * Supported identifiers: 'id', 'slug',
	 *
	 * @param mixed  $identifier      The identifier value.
	 * @param string $identifier_type The type of identifier to get the post by.
	 *
	 * @return GenericPostModel|null
	 */
	public function get_by( $identifier, $identifier_type = 'id' ) {
		$post = null;

		// Get post by identifier.
		if ( 'id' === $identifier_type ) {
			$post = $this->get_by_id( $identifier );
		} elseif ( 'slug' === $identifier_type ) {
			$post = $this->get_by_slug( $identifier );
		}

		return $post;
	}

	/**
	 * Gets the wrapped post for the given post ID if it exists and is valid.
	 *
	 * @param mixed $id ID of the post to retrieve and wrap.
	 *
	 * @return GenericPostModel|null GenericPost instance or null if post doesn't
	 * exist or doesn't match the post type for this factory.
	 */
	public function get_by_id( $id ) {
		$post = null;

		// Get the WP_Post object for the given ID.
		$wp_post = get_post( $id );

		// Check if post is valid. Post has to exist and match the post type of
		// the factory class. If the factory's class is the default 'any' then
		// do not worry about checking the post type.
		if ( ! empty( $wp_post ) && ( GenericPostModel::POST_TYPE_ANY === $this->get_post_type() || $wp_post->post_type === $this->get_post_type() ) ) {

			// Post is valid, wrap it.
			$post = $this->wrap( $wp_post );
		}

		return $post;
	}

	/**
	 * Returns a `GenericPost` with the given slug.
	 *
	 * @param string $slug post_name or 'slug' to query for.
	 *
	 * @return null|GenericPostModel
	 */
	public function get_by_slug( $slug ) {
		$post = null;

		$args = array(
			'name'           => $slug,
			'posts_per_page' => 1,
		);

		$results = $this->get_posts( $args );
		if ( ! empty( $results ) ) {
			$post = $results[0]; // Retrieve first result.
		}

		return $post;
	}

	/**
	 * Returns an array of posts which match the given search. Return type can
	 * be controlled with $output argument
	 *
	 * @param array  $args   WP Query arguments.
	 * @param string $output Output type as per `convert_posts_to_output`.
	 *
	 * @return array Array of data in the format specified in $output argument
	 */
	public function get_posts( $args, $output = self::OUTPUT_DEFAULT ) {
		$posts = array();

		$default_args = array(
			GenericPostModel::FIELD_POST_STATUS => GenericPostModel::POST_STATUS_ANY,
		);

		$args = array_merge( $default_args, $args );

		$query_args = $this->get_query_args( $args );

		$query    = new \WP_Query( $query_args );
		$wp_posts = $query->posts;

		$posts = $this->convert_models_to_output( $wp_posts, $output );

		return $posts;
	}

	/**
	 * Returns a count of all posts that match with the arguments.
	 *
	 * @param array $args WP Query arguments.
	 *
	 * @return int
	 */
	public function get_found_posts( $args ) {
		$found_posts = 0;

		$query_args = $this->get_query_args( $args );

		$query       = new \WP_Query( $query_args );
		$found_posts = $query->found_posts;

		return $found_posts;
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
		$args = array();

		$default_args = array();

		// Restrict results by factory post type.
		$post_type = $this->get_post_type();
		if ( ! empty( $post_type ) ) {
			$default_args[ GenericPostModel::FIELD_POST_TYPE ] = $post_type;
		}

		$args = array_merge( $default_args, $query_args );

		return $args;
	}

	/**
	 * Converts the array of WP Post objects into the desired output.
	 *
	 * @param array  $posts  WP_Post instances.
	 * @param string $output Output type to convert to.
	 *
	 * @return array Array of converted posts.
	 */
	public function convert_models_to_output( $posts, $output = self::OUTPUT_DEFAULT ) {
		$converted_posts = array();

		if ( self::OUTPUT_WP_MODEL === $output ) {
			$converted_posts = $posts;
		} else {
			foreach ( $posts as $post ) {
				$converted_posts[] = $this->convert_model_to_output( $post, $output );
			}
		}

		return $converted_posts;
	}

	/**
	 * Converts the WP Post object into the desired output.
	 *
	 * @param \WP_Post $post   The post object to convert.
	 * @param string   $output Output type for return value.
	 *
	 * @return GenericPostModel|null post in the desired output.
	 */
	public function convert_model_to_output( $post, $output = self::OUTPUT_DEFAULT ) {
		$converted_post = null;

		if ( self::OUTPUT_MODEL_WRAPPER === $output ) {
			$converted_post = $this->wrap( $post );
		} elseif ( self::OUTPUT_WP_ID === $output ) {
			$converted_post = $post->ID;
		} elseif ( self::OUTPUT_WP_MODEL === $output ) {
			$converted_post = $post;
		}

		return $converted_post;
	}

	/**
	 * Create a new post in WordPress, setting additional data.
	 *
	 * @param array $insert_post_args The `wp_insert_post()` args.
	 * @param array $meta_fields      The meta fields to add. array( 'meta_key' => 'meta_value' ).
	 * @param array $taxonomy_terms   The taxonomy terms to add. array( 'taxonomy_name' => [ 1, 2, 3 ] ).
	 *
	 * @return GenericPostModel|\WP_Error
	 */
	public function create_post( $insert_post_args = array(), $meta_fields = array(), $taxonomy_terms = array() ) {
		$post = null;

		$post = $this->insert_post( $insert_post_args );
		if ( ! is_wp_error( $post ) ) {

			// Set post meta data.
			if ( ! empty( $post ) ) {

				// Set post meta data.
				if ( ! empty( $meta_fields ) ) {
					$this->set_post_meta( $post, $meta_fields );
				}

				// Set post taxonomy associations.
				// TODO test. Need to remove all connected terms first, start from scratch.
				foreach ( $taxonomy_terms as $taxonomy_name => $term_identifiers ) {
					$post->set_terms( $term_identifiers, $taxonomy_name );
				}
			}
		}

		return $post;
	}

	/**
	 * Insert or update a post.
	 *
	 * @param array $insert_post_args Arguments for `wp_insert_post`.
	 * @param bool  $wp_error         Whether to return WP_Error on failure. Default true.
	 *
	 * @return \WP_Error|GenericPostModel|null
	 */
	public function insert_post( $insert_post_args, $wp_error = true ) {
		$post = null;

		$default_args = array(
			GenericPostModel::FIELD_POST_STATUS => GenericPostModel::POST_STATUS_PUBLISH,
		);

		// If the factory works with a specific post type, include it in the
		// base arguments.
		$post_type = $this->get_post_type();
		if ( GenericPostModel::POST_TYPE_ANY !== $post_type ) {
			$default_args[ GenericPostModel::FIELD_POST_TYPE ] = $post_type;
		}

		$insert_post_args = array_merge( $default_args, $insert_post_args );

		$id = wp_insert_post( $insert_post_args, $wp_error );

		if ( ! is_wp_error( $id ) ) {
			$post = $this->get_by_id( $id );
		} elseif ( $wp_error ) {

			// Return the error object.
			$post = $id;
		}

		return $post;
	}

	/**
	 * Update a post with new post data.
	 * Uses `wp_update_post()`.
	 *
	 * @param GenericPostModel $post             The post to update.
	 * @param array            $update_post_args Specify exactly what data to update.
	 * @param array            $meta_fields      The meta fields to add. array( 'meta_key' => 'meta_value' ).
	 * @param array            $taxonomy_terms   The taxonomy terms to add. array( 'taxonomy_name' => [ 1, 2, 3 ] ).
	 * @param bool             $wp_error         Optional. Allow return of WP_Error on failure. Default true.
	 *                                           int|WP_Error The value 0 or WP_Error on failure. The post ID on success.
	 *
	 * @return \WP_Error|GenericPostModel|null The null or WP_Error on failure. The post model instance on success.
	 */
	public function update_post( $post, $update_post_args = array(), $meta_fields = array(), $taxonomy_terms = array(), $wp_error = true ) {
		$updated_post = null;

		if ( empty( $update_post_args ) ) {
			$update_post_args = $post->get_wp_post();
		}

		if ( is_array( $update_post_args ) ) {

			// Ensure the ID is included in the update arguments.
			$update_post_args[ GenericPostModel::FIELD_ID ] = $post->ID;
		}

		$id = wp_update_post( $update_post_args, $wp_error );

		if ( ! is_wp_error( $id ) ) {
			$updated_post = $this->get_by_id( $id );

			// Set post meta data.
			if ( ! empty( $meta_fields ) ) {
				$this->set_post_meta( $updated_post, $meta_fields );
			}

			// Set post taxonomy associations.
			// TODO test. Need to remove all connected terms first, start from scratch.
			foreach ( $taxonomy_terms as $taxonomy_name => $term_identifiers ) {
				$updated_post->set_terms( $term_identifiers, $taxonomy_name );
			}
		} elseif ( $wp_error ) {

			// Return the error object.
			$updated_post = $id;
		}

		return $updated_post;
	}

	/**
	 * Trash or delete a post.
	 *
	 * @param GenericPostModel|\WP_Post $post         Post to delete.
	 * @param boolean                   $force_delete Optional. Whether to bypass trash and force deletion.
	 *
	 * @return array|bool|false|\WP_Post|null Post data on success, false or null on failure.
	 */
	public function delete_post( $post, $force_delete = false ) {
		return $this->delete_post_by_id( $post->ID, $force_delete );
	}

	/**
	 * Trash or delete a post or page by ID.
	 *
	 * @param int  $post_id      Post ID.
	 * @param bool $force_delete Optional. Whether to bypass trash and force deletion.
	 *
	 * @return array|bool|false|\WP_Post|null Post data on success, false or null on failure.
	 */
	public function delete_post_by_id( $post_id, $force_delete = false ) {
		return wp_delete_post( $post_id, $force_delete );
	}

	/**
	 * Set the meta data for the given post.
	 *
	 * @param GenericPostModel $post        The post to set the meta for.
	 * @param array            $meta_fields The key/value meta data.
	 */
	public function set_post_meta( $post, $meta_fields ) {
		foreach ( $meta_fields as $key => $value ) {
			if ( empty( $value ) ) {
				$post->delete_meta( $key );
			} else {
				if ( is_array( $value ) ) {
					$post->delete_meta( $key );

					// Arrays will be added as multiple, separate values.
					foreach ( $value as $val ) {
						$post->add_meta( $key, $val );
					}
				} else {
					$post->set_meta( $key, $value );
				}
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
		$models = array();

		foreach ( $wp_models as $wp_model ) {
			$models[] = $this->wrap( $wp_model );
		}

		return $models;
	}

	/**
	 * Gets post type of the custom post type associated with this model factory
	 *
	 * @return string
	 */
	public function get_post_type() {
		return GenericPostModel::POST_TYPE_ANY;
	}

	/**
	 * Wraps the given WP_Post in the post class used by this factory. This
	 * method should be overwritten if extending this class. Assumes post is
	 * valid.
	 *
	 * @param \WP_Post|object $post The post instance to wrap.
	 *
	 * @return GenericPostModel
	 */
	public function wrap( $post ) {
		return new GenericPostModel( $post );
	}
}
