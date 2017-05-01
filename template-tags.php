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

/**
 * Renders the bylines display names, without links to their posts.
 *
 * Equivalent to the_authors() template tag.
 */
function the_bylines() {
	echo bylines_render( get_bylines() );
}

/**
 * Display one or more bylines, according to arguments provided.
 *
 * @param array $bylines Set of bylines to display.
 * @param array $args    Arguments to affect display.
 */
function bylines_render( $bylines, $args = array() ) {
	if ( empty( $bylines ) ) {
		return '';
	}
	$defaults = array(
		'between'           => ', ',
		'between_last_two'  => __( ' and ', 'bylines' ),
		'between_last_many' => __( ', and ', 'bylines' ),
	);
	$args = array_merge( $defaults, $args );
	$total = count( $bylines );
	$current = 0;
	$output = '';
	foreach ( $bylines as $byline ) {
		$current++;
		if ( $current > 1 ) {
			if ( $current === $total ) {
				if ( 2 === $total ) {
					$output .= $args['between_last_two'];
				} else {
					$output .= $args['between_last_many'];
				}
			} elseif ( $total >= 2 ) {
				$output .= $args['between'];
			}
		}
		$output .= $byline->display_name;
	}
	return $output;
}
