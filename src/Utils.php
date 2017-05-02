<?php
/**
 * Utility methods for managing bylines
 *
 * @package Bylines
 */

namespace Bylines;

/**
 * Utility methods for managing bylines
 */
class Utils {

	/**
	 * Set the bylines for a post
	 *
	 * @param integer $post_id ID for the post to modify.
	 * @param array   $bylines Bylines to set on the post.
	 */
	public static function set_post_bylines( $post_id, $bylines ) {
		$bylines = wp_list_pluck( $bylines, 'term_id' );
		wp_set_object_terms( $post_id, $bylines, 'byline' );
	}

}
