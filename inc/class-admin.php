<?php
/**
 * Generic admin controller
 *
 * @package Bylines
 */

namespace Bylines;

use Bylines\Objects\Byline;

/**
 * Generic admin controller
 */
class Admin {

	/**
	 * Register callbacks for managing custom columns
	 */
	public static function action_admin_init() {
		foreach ( Content_Model::get_byline_supported_post_types() as $post_type ) {
			add_filter( "manage_{$post_type}_posts_columns", array( __CLASS__, 'filter_manage_posts_columns' ) );
			add_action( "manage_{$post_type}_posts_custom_column", array( __CLASS__, 'action_manage_posts_custom_column' ), 10, 2 );
		}
	}

	/**
	 * Filter post columns to include the Bylines column
	 *
	 * @param array $columns All post columns with their titles.
	 * @return array
	 */
	public static function filter_manage_posts_columns( $columns ) {
		$new_columns = array();
		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;
			if ( 'title' === $key ) {
				$new_columns['bylines'] = __( 'Bylines', 'bylines' );
			}

			if ( 'author' === $key ) {
				unset( $new_columns[ $key ] );
			}
		}
		return $new_columns;
	}

	/**
	 * Render the bylines for a post in the table
	 *
	 * @param string  $column  Name of the column.
	 * @param integer $post_id ID of the post being rendered.
	 */
	public static function action_manage_posts_custom_column( $column, $post_id ) {
		if ( 'bylines' !== $column ) {
			return;
		}
		$bylines = get_bylines( $post_id );
		$post_type = get_post_type( $post_id );
		$bylines_str = array();
		foreach ( $bylines as $byline ) {
			$args = array(
				'author_name' => $byline->slug,
			);
			if ( 'post' !== $post_type ) {
				$args['post_type'] = $post_type;
			}
			$url = add_query_arg( array_map( 'rawurlencode', $args ), admin_url( 'edit.php' ) );
			$bylines_str[] = '<a href="' . esc_url( $url ) . '">' . esc_html( $byline->display_name ) . '</a>';
		}
		echo implode( ', ', $bylines_str );
	}

}
