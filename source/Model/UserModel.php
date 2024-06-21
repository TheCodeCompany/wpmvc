<?php
/**
 * Provides the UserModel class.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * A model instance which wraps a `WP_User` instance.
 *
 * @property int    $ID
 * @property string $nickname
 * @property string $description
 * @property string $user_description
 * @property string $first_name
 * @property string $user_firstname
 * @property string $last_name
 * @property string $user_lastname
 * @property string $user_login
 * @property string $user_pass
 * @property string $user_nicename
 * @property string $user_email
 * @property string $user_url
 * @property string $user_registered
 * @property string $user_activation_key
 * @property string $user_status
 * @property int    $user_level
 * @property string $display_name
 * @property string $spam
 * @property string $deleted
 * @property string $locale
 *                         TODO document the user properties.
 */
class UserModel extends WPModel implements WPMeta {

	const FIELD_USER_LOGIN = 'user_login';
	const FIELD_USER_EMAIL = 'user_email';
	const META_FIRST_NAME  = 'first_name';
	const META_LAST_NAME   = 'last_name';

	/**
	 * The backing WP_User object.
	 *
	 * @var bool|\WP_User
	 */
	protected $user;

	/**
	 * UserModel constructor.
	 *
	 * @param \WP_User|int $user User object or ID. Use of ID is discouraged.
	 */
	public function __construct( $user = 0 ) {
		//assert( ! empty( $user ) );

		if ( 'object' === (string) gettype( $user ) ) {
			$this->user = $user;
		} else {
			$this->user = get_user_by( 'id', $user );
		}
	}

	/**
	 * Get underlying `WP_User` field value.
	 *
	 * @param string $name Field name/slug.
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		$value = null;

		if ( isset( $this->user->$name ) ) {
			$value = $this->user->$name;
		} elseif ( isset( $this->$name ) ) {
			$value = $this->$name;
		}

		return $value;
	}

	/**
	 * Set underlying `WP_User` field value.
	 *
	 * @param string $name  Field name/slug.
	 * @param mixed  $value New field value.
	 */
	public function __set( $name, $value ) {
		if ( isset( $this->user->$name ) ) {
			$this->user->$name = $value;
		} else {
			$this->$name = $value;
		}
	}

	/**
	 * Call underlying `WP_User` method.
	 *
	 * @param string $name Callable name.
	 * @param array  $args Callable arguments.
	 *
	 * @return mixed
	 */
	public function __call( $name, $args ) {
		$return = null;

		if ( method_exists( $this->user, $name ) ) {
			$return = call_user_func_array( [ $this->user, $name ], $args );
		} elseif ( method_exists( $this, $name ) ) {
			$return = call_user_func_array( [ $this, $name ], $args );
		}

		return $return;
	}

	/**
	 * Get the WP User object this model wraps.
	 *
	 * @return bool|int|\WP_User
	 */
	public function get_wp_user() {
		return $this->user;
	}

	/**
	 * Shortcut method for `wp_update_user()`.
	 *
	 * @param array $args The `wp_update_user()` arguments.  ID is automatically added.
	 *
	 * @return int|\WP_Error
	 * @deprecated Use a factory to update a model.
	 */
	public function update( $args ) {
		//assert( ! empty( $args ) );
		$args['ID'] = $this->user->ID;

		return wp_update_user( $args );

	}

	/**
	 * Returns the given meta field.
	 *
	 * @param string  $key    The meta key to get for the user.
	 * @param boolean $single Whether to get a single value, or array of all metas. Default `true`.
	 *
	 * @return mixed
	 */
	public function get_meta( $key = null, $single = true ) {
		return get_user_meta( $this->user->ID, $key, $single );
	}

	/**
	 * Sets the given meta field - update_user_meta().
	 *
	 * @param string $key   The meta key to get for the user.
	 * @param mixed  $value The value to set the meta field to.
	 *
	 * @return bool|int
	 */
	public function set_meta( $key, $value ) {
		//assert( ! empty( $key ) );

		return update_user_meta( $this->user->ID, $key, $value );
	}

	/**
	 * Adds the given meta field - add_user_meta().
	 *
	 * @param string $key    The meta key to get for the user.
	 * @param mixed  $value  The value to set the meta field to.
	 * @param bool   $unique Whether the value is unique or not.
	 *
	 * @return false|int
	 */
	public function add_meta( $key, $value, $unique = false ) {
		//assert( ! empty( $key ) );

		return add_user_meta( $this->user->ID, $key, $value, $unique );
	}

	/**
	 * Deletes the given meta field - delete_user_meta().
	 *
	 * @param string $key   The meta key to get for the user.
	 * @param string $value The value to set the meta field to.
	 *
	 * @return bool False for failure. True for success.
	 */
	public function delete_meta( $key, $value = '' ) {
		//assert( ! empty( $key ) );

		return delete_user_meta( $this->user->ID, $key, $value );
	}

	/**
	 * Calls the set_role function on the user object
	 *
	 * @param string $role Role to pass to the set_role call on $this->user.
	 */
	public function set_role( $role ) {
		$this->user->set_role( $role );
	}
}
