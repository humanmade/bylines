<?php
/**
 * Modifications to the main query, and helper query methods
 *
 * @package Bylines
 */

namespace Bylines;

/**
 * Modifications to the main query, and helper query methods
 */
class Query {

	/**
	 * Modify the WHERE clause on author queries.
	 *
	 * @param string   $where Existing WHERE clause.
	 * @param WP_Query $query Query object.
	 */
	public static function filter_posts_where( $where, $query ) {
		global $wpdb;

		if ( ! $query->is_author() ) {
			return $where;
		}

		// @todo ensure this is a supported post type.
		$author_name = $query->get( 'author_name' );
		if ( ! $author_name ) {
			$author_id = $query->get( 'author' );
			$user = get_user_by( 'id', $author_id );
			if ( ! $author_id || ! $user ) {
				return $where;
			}
			$author_name = $user->user_nicename;
		}

		$terms = array();
		$term = get_term_by( 'slug', $author_name, 'byline' );
		if ( $term ) {
			$terms[] = $term;
		}

		// Shamelessly copied from CAP, because it'd be a shame to have to deal
		// with this twice.
		if ( stripos( $where, '.post_author = 0)' ) ) {
			$maybe_both = false;
		} else {
			$maybe_both = apply_filters( 'bylines_query_post_author', true );
		}

		$maybe_both_query = $maybe_both ? '$1 OR' : '';

		if ( ! empty( $terms ) ) {
			$terms_implode = '';
			$query->bylines_having_terms = '';
			foreach ( $terms as $term ) {
				$terms_implode .= '(' . $wpdb->term_taxonomy . '.taxonomy = "byline" AND ' . $wpdb->term_taxonomy . '.term_id = \'' . $term->term_id . '\') OR ';
				$query->bylines_having_terms .= ' ' . $wpdb->term_taxonomy . '.term_id = \'' . $term->term_id . '\' OR ';
			}
			$terms_implode = rtrim( $terms_implode, ' OR' );
			$query->bylines_having_terms = rtrim( $query->bylines_having_terms, ' OR' );
			$where = preg_replace( '/(\b(?:' . $wpdb->posts . '\.)?post_author\s*=\s*(\d+))/', '(' . $maybe_both_query . ' ' . $terms_implode . ')', $where );
		}

		return $where;
	}

	/**
	 * Modify the JOIN clause on author queries.
	 *
	 * @param string   $join Existing JOIN clause.
	 * @param WP_Query $query Query object.
	 */
	public static function filter_posts_join( $join, $query ) {
		global $wpdb;

		if ( ! $query->is_author() ) {
			return $join;
		}

		if ( empty( $query->bylines_having_terms ) ) {
			return $join;
		}

		// Check to see that JOIN hasn't already been added. Props michaelingp and nbaxley.
		$term_relationship_inner_join = " INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)";
		$term_relationship_left_join = " LEFT JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)";
		$term_taxonomy_join = " INNER JOIN {$wpdb->term_taxonomy} ON ( {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id )";

		// 4.6+ uses a LEFT JOIN for tax queries so we need to check for both.
		if ( false === strpos( $join, trim( $term_relationship_inner_join ) )
			&& false === strpos( $join, trim( $term_relationship_left_join ) ) ) {
			$join .= $term_relationship_left_join;
		}

		if ( false === strpos( $join, trim( $term_taxonomy_join ) ) ) {
			$join .= str_replace( 'INNER JOIN', 'LEFT JOIN', $term_taxonomy_join );
		}

		return $join;
	}

	/**
	 * Modify the GROUP BY clause on author queries.
	 *
	 * @param string   $groupby Existing GROUP BY clause.
	 * @param WP_Query $query Query object.
	 */
	public static function filter_posts_groupby( $groupby, $query ) {
		global $wpdb;

		if ( ! $query->is_author() ) {
			return $groupby;
		}

		if ( empty( $query->bylines_having_terms ) ) {
			return $groupby;
		}

		$having = 'MAX( IF ( ' . $wpdb->term_taxonomy . '.taxonomy = "byline", IF ( ' . $query->bylines_having_terms . ',2,1 ),0 ) ) <> 1 ';
		$groupby = $wpdb->posts . '.ID HAVING ' . $having;
		return $groupby;
	}

}
