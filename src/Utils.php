<?php
/**
 * Utility methods for managing bylines
 *
 * @package Bylines
 */

namespace Bylines;

use Bylines\Objects\Byline;
use WP_Error;

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

	/**
	 * Convert co-authors to bylines on a post.
	 *
	 * Errors if the post already has bylines. To re-convert, remove bylines
	 * from the post.
	 *
	 * @param integer $post_id ID for the post to convert.
	 * @return object|WP_Error Result object if successful; WP_Error on error.
	 */
	public static function convert_post_coauthors( $post_id ) {
		if ( ! function_exists( 'get_coauthors' ) ) {
			return new WP_Error( 'bylines_missing_cap', __( 'Co-Authors Plus must be installed and active.', 'bylines' ) );
		}
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error( 'bylines_missing_post', "Invalid post: {$post_id}" );
		}
		$bylines = get_the_terms( $post_id, 'byline' );
		if ( $bylines && ! is_wp_error( $bylines ) ) {
			return new WP_Error( 'bylines_post_has_bylines', "Post {$post_id} already has bylines." );
		}
		$bylines = array();
		$result = new \stdClass;
		$result->created = 0;
		$result->existing = 0;
		$result->post_id = 0;
		foreach ( get_coauthors( $post_id ) as $coauthor ) {
			switch ( $coauthor->type ) {
				case 'wpuser':
					$byline = Byline::get_by_user_id( $coauthor->ID );
					if ( $byline ) {
						$bylines[] = $byline;
						$result->existing++;
					} else {
						$byline = Byline::create_from_user( $coauthor->ID );
						if ( is_wp_error( $byline ) ) {
							return $byline;
						}
						$bylines[] = $byline;
						$result->created++;
					}
					break;
			}
		}
		Utils::set_post_bylines( $post_id, $bylines );
		return $result;
	}

}
