<?php
/**
 * Provides the WPMeta class.
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * WordPress object meta interface. Models which implement this interface are
 * able to manage meta data in WordPress.
 */
interface WPMeta {

	/**
	 * Returns the given meta field.
	 *
	 * @param string $key The meta key to get for the object, null to return all
	 * meta fields. Default `null`.
	 * @param bool   $single Whether to get a single value, or array of all
	 * meta. Default `true`.
	 */
	public function get_meta( $key = null, $single = true );

	/**
	 * Sets the given meta field.
	 *
	 * @param string $key The meta key to get for the object.
	 * @param mixed  $value The value to set the meta field to.
	 * @return int|bool Meta ID if the key didn't exist, true on successful
	 * update, false on failure.
	 */
	public function set_meta( $key, $value );

	/**
	 * Adds the given meta field.
	 *
	 * @param string $key The meta key to get for the object.
	 * @param mixed  $value The value to set the meta field to.
	 * @return int|bool Meta ID on success, false on failure.
	 */
	public function add_meta( $key, $value );

	/**
	 * Deletes the given meta field.
	 *
	 * @param string $key The meta key to get for the object.
	 * @param string $value The value to set the meta field to.
	 * @return bool False for failure. True for success.
	 */
	public function delete_meta( $key, $value = '' );

}
