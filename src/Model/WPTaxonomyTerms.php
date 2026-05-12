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
	 * @return array|\WP_Error Array of terms or WP_Error if taxonomy does not exist.
	 */
	public function get_terms( string $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT, array $args = [] ): array|\WP_Error;

	/**
	 * Adds new terms to the object.
	 *
	 * @param array|string $terms    List of terms. Can be an array or a comma separated string.
	 * @param string       $taxonomy Possible values for example: 'category', 'post_tag', 'taxonomy slug'.
	 * @param bool         $append   If true, terms will be appended. If false, they will replace existing terms.
	 *
	 * @return array|bool|\WP_Error Array of term IDs or WP_Error if any issues occurred.
	 */
	public function set_terms( array|string $terms, string $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT, bool $append = false ): array|bool|\WP_Error;

	/**
	 * Removes all of the terms attached to this object from the provided taxonomy.
	 *
	 * @param string $taxonomy The taxonomy to remove all of the terms for.
	 *
	 * @return array|bool|\WP_Error Array of term IDs or WP_Error if any issues occurred.
	 */
	public function remove_terms( string $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT ): array|bool|\WP_Error;

	/**
	 * Removes a term from this object.
	 *
	 * @param int    $term_id  The ID for the term that needs to be removed.
	 * @param string $taxonomy The taxonomy name.
	 *
	 * @return bool|\WP_Error True on success, false or WP_Error on failure.
	 */
	public function remove_term( int $term_id, string $taxonomy = TaxonomyTermModel::TAXONOMY_NAME_DEFAULT ): bool|\WP_Error;
}
