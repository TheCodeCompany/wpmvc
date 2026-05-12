<?php
/**
 * WPModelFactory
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

use WPMVC\Core\ModelFactory;

/**
 * Base factory classes for WordPress-based models
 *
 * @package wpmvc
 */
abstract class WPModelFactory extends ModelFactory {

	/**
	 * Wrap an array of WP model objects using this factory's wrap() method.
	 *
	 * @param array $wp_models WordPress model objects to wrap.
	 *
	 * @return array
	 */
	public function wrap_models( array $wp_models ): array {
		$models = [];

		foreach ( $wp_models as $wp_model ) {
			$models[] = $this->wrap( $wp_model );
		}

		return $models;
	}

	/**
	 * Apply an array of meta key/value pairs to a model that implements WPMeta.
	 * Empty values delete the key; array values are stored as multiple separate entries.
	 *
	 * @param WPMeta $model       The model to update meta for.
	 * @param array  $meta_fields Key/value meta data.
	 *
	 * @return void
	 */
	protected function apply_meta_fields( WPMeta $model, array $meta_fields ): void {
		foreach ( $meta_fields as $key => $value ) {
			if ( empty( $value ) ) {
				$model->delete_meta( $key );
			} elseif ( is_array( $value ) ) {
				$model->delete_meta( $key );
				foreach ( $value as $val ) {
					$model->add_meta( $key, $val );
				}
			} else {
				$model->set_meta( $key, $value );
			}
		}
	}

	/**
	 * Wrap a single model object. Must be overridden by child factories.
	 *
	 * @param mixed $model The model object to wrap.
	 *
	 * @return mixed
	 */
	abstract public function wrap( mixed $model ): mixed;
}
