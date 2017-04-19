<?php
/**
 * Utility functions for use by themes.
 *
 * @package Bylines
 */

use Bylines\Objects\Byline;

/**
 * Get all bylines for a post.
 *
 * @param WP_Post|null $post Post to fetch bylines for. Defaults to global post.
 * @return array
 */
function get_bylines( $post = null ) {
	if ( is_null( $post ) ) {
		$post = get_post();
	} elseif ( is_int( $post ) ) {
		$post = get_post( $post );
	}
	if ( ! $post ) {
		return array();
	}
	$taxonomy = 'byline';
	$terms = get_object_term_cache( $post->ID, $taxonomy );
	if ( false === $terms ) {
		$terms = wp_get_object_terms( $post->ID, $taxonomy, array(
			'orderby' => 'term_order',
		) );
		if ( ! is_wp_error( $terms ) ) {
			$term_ids = wp_list_pluck( $terms, 'term_id' );
			wp_cache_add( $post->ID, $term_ids, $taxonomy . '_relationships' );
		}
	}

	/**
	 * Filters the list of terms attached to the given post.
	 *
	 * @see get_the_terms()
	 */
	$terms = apply_filters( 'get_the_terms', $terms, $post->ID, $taxonomy );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}
	$bylines = array();
	foreach ( $terms as $term ) {
		$bylines[] = Byline::get_by_term_id( $term->term_id );
	}
	return $bylines;
}
