<?php
/**
 * Provides the UserModelFactory class.
 *
 * @package WPMVC\Model
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * A factory which produces `UserModel` instances.  A `UserModel` instance
 * should not be instantiated directly.  It should always go via one of the
 * methods in this class.
 *
 * @package WPMVC\Model
 */
class UserModelFactory extends WPModelFactory {

	// Return output types.
	const OUTPUT_WP_ID         = 'user_id'; // User ID.
	const OUTPUT_WP_MODEL      = 'wp_model'; // WP_User instance.
	const OUTPUT_MODEL_WRAPPER = 'model_wrapper'; // UserModel family instance.
	const OUTPUT_DEFAULT       = self::OUTPUT_MODEL_WRAPPER;

	/**
	 * Get the user model with the given ID.
	 *
	 * @param string|int $id The ID of the user to get.
	 *
	 * @return null|UserModel
	 */
	public function get_by_id( $id ) {
		$user = null;

		$wp_user = get_user_by( 'id', $id );
		if ( ! empty( $wp_user ) ) {
			$user = $this->wrap( $wp_user );
		}

		return $user;
	}

	/**
	 * Get the `UserModel` for the current logged in user.
	 *
	 * @return UserModel|null
	 */
	public function get_current() {
		return $this->get_by_id( get_current_user_id() );
	}

	/**
	 * Get a user by a provided field type
	 *
	 * @param string $field The field to retrieve the user with. id | ID | slug | email | login.
	 * @param string $value A value for $field. A user ID, slug, email address, or login name.
	 *
	 * @return null|UserModel The users object.
	 */
	public function get_user_by( $field, $value ) {
		return $this->wrap( get_user_by( $field, $value ) );
	}

	/**
	 * Get users from the database.
	 *
	 * @param array  $args   WP_User_Query args.
	 * @param string $output Return format.
	 *
	 * @return array
	 */
	public function get_users( $args, $output = self::OUTPUT_DEFAULT ) {
		$users = [];

		$query_args = $this->get_query_args( $args );

		$query    = new \WP_User_Query( $query_args );
		$wp_users = $query->get_results();

		$users = $this->convert_models_to_output( $wp_users, $output );

		return $users;
	}

	/**
	 * Get users by multiple IDs.
	 *
	 * @param array  $user_ids      User IDs to query by.
	 * @param array  $optional_args Optional arguments for the query.
	 * @param string $output        Return output format.
	 *
	 * @return array
	 */
	public function get_users_by_ids( $user_ids, $optional_args = [], $output = self::OUTPUT_DEFAULT ) {
		$users = [];

		$base_args = [
			'include' => $user_ids,
		];

		$args  = array_merge( $base_args, $optional_args );
		$users = $this->get_users( $args, $output );

		return $users;
	}

	/**
	 * Get the count or total number of users that match the given arguments.
	 *
	 * @param array $args The search arguments for WP_User_Query.
	 *
	 * @return int
	 */
	public function get_total( $args ) {
		$total = 0;

		$query_args = $this->get_query_args( $args );

		$query = new \WP_User_Query( $query_args );
		$total = $query->get_total();

		return $total;
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

		// Restrict results by role if this factory works with a specific
		// role.
		$factory_role = $this->get_user_role();
		if ( ! empty( $factory_role ) ) {
			$default_args['role'] = $factory_role;
		}

		$args = array_merge( $default_args, $query_args );

		return $args;
	}

	/**
	 * Converts the array of WP_User objects into the desired output.
	 *
	 * @param array  $users  WP_User instances.
	 * @param string $output Output type to convert to.
	 *
	 * @return array Array of converted posts.
	 */
	public function convert_models_to_output( $users, $output = self::OUTPUT_DEFAULT ) {
		$converted_users = [];

		if ( self::OUTPUT_WP_MODEL === $output ) {
			$converted_users = $users;
		} else {
			foreach ( $users as $user ) {
				$converted_users[] = $this->convert_model_to_output( $user, $output );
			}
		}

		return $converted_users;
	}

	/**
	 * Converts the WP User object into the desired output.
	 *
	 * @param \WP_Post $user   The user object to convert.
	 * @param string   $output Output type for return value.
	 *
	 * @return UserModel|null post in the desired output.
	 */
	public function convert_model_to_output( $user, $output = self::OUTPUT_DEFAULT ) {
		$converted_user = null;

		if ( self::OUTPUT_MODEL_WRAPPER === $output ) {
			$converted_user = $this->wrap( $user );
		} elseif ( self::OUTPUT_WP_ID === $output ) {
			$converted_user = $user->ID;
		} elseif ( self::OUTPUT_WP_MODEL === $output ) {
			$converted_user = $user;
		}

		return $converted_user;
	}

	/**
	 * Insert a user into the database.
	 *
	 * @param array $user_data   User data to set.
	 * @param array $meta_fields Optional. User meta data to set.
	 *
	 * @return int|\WP_Error|UserModel|null
	 */
	public function insert_user( $user_data, $meta_fields = [] ) {
		$user = null;

		$id = wp_insert_user( $user_data );

		if ( ! is_wp_error( $id ) ) {
			$user = $this->get_by_id( $id );

			if ( ! empty( $user ) ) {

				// Set user meta data.
				if ( ! empty( $meta_fields ) ) {
					$this->set_user_meta( $user, $meta_fields );
				}
			}
		} else {

			// Return the error object.
			$user = $id;
		}

		return $user;
	}

	/**
	 * A simpler way of inserting a user into the database.
	 *
	 * @param string $username    The user's username.
	 * @param string $password    The user's password.
	 * @param string $email       Optional. The user's email. Default empty.
	 * @param array  $meta_fields Optional. User meta data to set.
	 *
	 * @return int|\WP_Error|UserModel|null
	 */
	public function create_user( $username, $password, $email = '', $meta_fields = [] ) {
		$user = null;

		$id = wp_create_user( $username, $password, $email );

		if ( ! is_wp_error( $id ) ) {

			$user = $this->get_by_id( $id );

			if ( ! empty( $user ) ) {

				// Set user meta data.
				if ( ! empty( $meta_fields ) ) {
					$this->set_user_meta( $user, $meta_fields );
				}
			}
		} else {

			// Return the error object.
			$user = $id;
		}

		return $user;
	}

	/**
	 * Update a user in the database.
	 *
	 * @param UserModel      $user      The user model to update.
	 * @param \WP_User|array $user_data The user data to update.
	 *
	 * @return int|\WP_Error|UserModel|null
	 */
	public function update_user( $user, $user_data = [] ) {
		$updated_user = null;

		if ( empty( $user_data ) ) {
			$user_data = $user->get_wp_user();
		}

		if ( is_array( $user_data ) ) {

			// Ensure the ID is included in the update arguments.
			$user_data['ID'] = $user->ID;
		}

		$id = wp_update_user( $user_data );

		if ( ! is_wp_error( $id ) ) {
			$updated_user = $this->get_by_id( $id );
		} else {

			// Return the error object.
			$updated_user = $id;
		}

		return $updated_user;
	}

	/**
	 * Set the meta data for the given user.
	 *
	 * @param UserModel $user        The user to set the meta for.
	 * @param array     $meta_fields The key/value meta data.
	 */
	public function set_user_meta( $user, $meta_fields ) {
		foreach ( $meta_fields as $key => $value ) {
			if ( empty( $value ) ) {
				$user->delete_meta( $key );
			} else {
				if ( is_array( $value ) ) {
					$user->delete_meta( $key );

					// Arrays will be added as multiple, separate values.
					foreach ( $value as $val ) {
						$user->add_meta( $key, $val );
					}
				} else {
					$user->set_meta( $key, $value );
				}
			}
		}
	}

	/**
	 * Deletes the given user.
	 *
	 * @param UserModel|\WP_User $user     The user model to delete.
	 * @param int                $reassign Optional. Reassign posts and links to new User ID.
	 *
	 * @return bool True when finished.
	 */
	public function delete_user( $user, $reassign = null ) {
		return $this->delete_user_by_id( $user->ID, $reassign );
	}

	/**
	 * Deletes the user identified by ID.
	 *
	 * @param int $user_id  The ID of the user to delete.
	 * @param int $reassign Optional. Reassign posts and links to new User ID.
	 *
	 * @return bool True when finished.
	 */
	public function delete_user_by_id( $user_id, $reassign = null ) {
		return wp_delete_user( $user_id, $reassign );
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
	 * The role the factory works with. If empty, no specific role is used.
	 *
	 * @return string
	 */
	public function get_user_role() {
		return '';
	}

	/**
	 * Wrap a WP_User as a user model instance.
	 *
	 * @param \WP_User $user The WP_User to wrap.
	 *
	 * @return UserModel
	 */
	public function wrap( $user ) {
		return new UserModel( $user );
	}
}
