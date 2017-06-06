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
 * @return array Array of Byline objects, a single WP_User object, or empty.
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
	$terms = wp_get_object_terms( $post->ID, $taxonomy, array(
		'orderby' => 'term_order',
	) );

	/**
	 * Filters the list of terms attached to the given post.
	 *
	 * @see get_the_terms()
	 */
	$terms = apply_filters( 'get_the_terms', $terms, $post->ID, $taxonomy );
	if ( $terms && ! is_wp_error( $terms ) ) {
		$bylines = array();
		foreach ( $terms as $term ) {
			$bylines[] = Byline::get_by_term_id( $term->term_id );
		}
		return $bylines;
	} elseif ( ! $terms ) {
		$user = get_user_by( 'id', $post->post_author );
		if ( $user ) {
			return array( $user );
		}
	}
	return array();
}

/**
 * Renders the bylines display names, without links to their posts.
 *
 * Equivalent to the_author() template tag.
 */
function the_bylines() {
	echo bylines_render( get_bylines(), function( $byline ) {
		return $byline->display_name;
	} );
}

/**
 * Renders the bylines display names, with links to their posts.
 *
 * Equivalent to the_author_posts_link() template tag.
 */
function the_bylines_posts_links() {
	echo bylines_render( get_bylines(), function( $byline ) {
		$link = is_a( $byline, 'WP_User' ) ? get_author_posts_url( $byline->ID ) : $byline->link;
		$args = array(
			'before_html' => '',
			'href' => $link,
			'rel' => 'author',
			// translators: Posts by a given author.
			'title' => sprintf( __( 'Posts by %1$s', 'bylines' ), apply_filters( 'the_author', $byline->display_name ) ),
			'class' => 'author url fn',
			'text' => apply_filters( 'the_author', $byline->display_name ),
			'after_html' => '',
		);
		/**
		 * Arguments for determining the display of bylines with posts links
		 *
		 * @param array  $args   Arguments determining the rendering of the byline.
		 * @param Byline $byline The byline to be rendered.
		 */
		$args = apply_filters( 'bylines_posts_links', $args, $byline );
		$single_link = sprintf(
			'<a href="%1$s" title="%2$s" class="%3$s" rel="%4$s">%5$s</a>',
			esc_url( $args['href'] ),
			esc_attr( $args['title'] ),
			esc_attr( $args['class'] ),
			esc_attr( $args['rel'] ),
			esc_html( $args['text'] )
		);
		return $args['before_html'] . $single_link . $args['after_html'];
	} );
}

/**
 * Renders the bylines display names, with their website link if it exists.
 *
 * Equivalent to the_author_link() template tag.
 */
function the_bylines_links() {
	echo bylines_render( get_bylines(), function( $byline ) {
		if ( $byline->user_url ) {
			return sprintf( '<a href="%s" title="%s" rel="external">%s</a>',
				esc_url( $byline->user_url ),
				// Translators: refers to the byline's website.
				esc_attr( sprintf( __( 'Visit %s&#8217;s website' ), $byline->display_name ) ),
				$byline->display_name
			);
		} else {
			return $byline->display_name;
		}
	} );
}

/**
 * Display one or more bylines, according to arguments provided.
 *
 * @param array    $bylines         Set of bylines to display.
 * @param callable $render_callback Callback to return rendered byline.
 * @param array    $args            Arguments to affect display.
 */
function bylines_render( $bylines, $render_callback, $args = array() ) {
	if ( empty( $bylines )
		|| empty( $render_callback )
		|| ! is_callable( $render_callback ) ) {
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
		$output .= $render_callback( $byline );
	}
	return $output;
}
