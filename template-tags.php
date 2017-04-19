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
	$terms = wp_get_object_terms( $post->ID, 'byline', array(
		'orderby'    => 'term_order',
	) );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return array();
	}
	$bylines = array();
	foreach ( $terms as $term ) {
		$bylines[] = Byline::get_by_term_id( $term->term_id );
	}
	return $bylines;
}
