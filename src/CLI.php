<?php
/**
 * Manage bylines.
 *
 * @package Bylines
 */

namespace Bylines;

use Bylines\Objects\Byline;
use WP_CLI;

/**
 * * Manage bylines.
 */
class CLI {

	/**
	 * Convert post authors to bylines.
	 *
	 * Generates a byline term for the post author (if one doesn't already exist)
	 * and assigns the term to the post.
	 *
	 * ## OPTIONS
	 *
	 * <post-id>...
	 * : One or more post ids to process.
	 *
	 * @subcommand convert-post-author
	 */
	public function convert_post_author( $args, $assoc_args ) {

		$successes = 0;
		$failures = 0;
		$total = count( $args );
		foreach ( $args as $post_id ) {
			$post = get_post( $post_id );
			if ( ! $post ) {
				WP_CLI::warning( "Invalid post: {$post_id}" );
				$failures++;
				continue;
			}
			$bylines = get_the_terms( $post_id, 'byline' );
			if ( $bylines && ! is_wp_error( $bylines ) ) {
				WP_CLI::warning( "Post {$post_id} already has bylines." );
				$failures++;
				continue;
			}

			if ( ! $post->post_author ) {
				WP_CLI::warning( "Post {$post_id} doesn't have an author." );
				$failures++;
				continue;
			}

			$byline = Byline::get_by_user_id( $post->post_author );
			if ( $byline ) {
				Utils::set_post_bylines( $post_id, array( $byline ) );
				WP_CLI::log( "Found existing byline and assigned to post {$post_id}." );
			} else {
				$byline = Byline::create_from_user( (int) $post->post_author );
				if ( is_wp_error( $byline ) ) {
					WP_CLI::warning( $byline->get_error_message() );
					$failures++;
					continue;
				}
				Utils::set_post_bylines( $post_id, array( $byline ) );
				WP_CLI::log( "Created byline and assigned to post {$post_id}." );
			}
			$successes++;
		} // End foreach().

		WP_CLI\Utils\report_batch_operation_results( 'post author', 'convert', $total, $successes, $failures );
	}

}
