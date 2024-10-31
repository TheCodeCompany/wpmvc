<?php
/**
 * WPTaxonomyTerms
 *
 * @package wpmvc
 */

// phpcs:disable WordPress.Files.FileName

namespace WPMVC\Model;

/**
 * Interface for taxonomy terms. Models which implement this interface are
 * able to manage term associations in WordPress.
 *
 * @package wpmvc
 */
interface WPTaxonomyTerms {

	/**
	 * Retrieve the terms for an object.
	 *
	 * @param string $taxonomy The taxonomy for which to retrieve terms.
	 *                         Defaults to post_tag.
	 * @param array  $args     Args to pass to `wp_get_post_terms()`.
	 *
	 * @return array|\WP_Error Array of terms or WP_Error if taxonomy does not
	 * exist
	 */
	public function get_terms( $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT, $args = [] );

	/**
	 * Adds new terms to the object.
	 *
	 * @param array   $terms    List of terms. Can be an array or a comma separated string. If you want to enter terms of a hierarchical taxonomy like
	 *                          categories, then use IDs. If you want to add non-hierarchical terms like tags, then use names.
	 * @param string  $taxonomy Possible values for example: 'category', 'post_tag', 'taxonomy slug'.
	 * @param boolean $append   If true, tags will be appended to the object. If false, tags will replace existing tags.
	 *
	 * @return array|boolean|\WP_Error|string Array of terms or WP_Error if any issues occurred while processing the request.
	 */
	public function set_terms( $terms, $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT, $append = false );

	/**
	 * Removes all of the terms attached to this object from the provided taxonomy.
	 *
	 * @param string $taxonomy The taxonomy to remove all of the terms for.
	 *
	 * @return array|boolean|\WP_Error|string Array of terms or WP_Error if any issues occurred while processing the request.
	 */
	public function remove_terms( $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT );

	/**
	 * Removes a term from this object.
	 *
	 * @param integer $term_id  The ID for the term that needs to be removed.
	 * @param string  $taxonomy The taxonomy name.
	 *
	 * @return mixed True on success, false or WP_Error on failure.
	 */
	public function remove_term( $term_id, $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT );
}
