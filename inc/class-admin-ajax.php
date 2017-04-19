<?php
/**
 * Admin ajax endpoints
 *
 * @package Bylines
 */

namespace Bylines;

/**
 * Admin ajax endpoints
 */
class Admin_Ajax {

	/**
	 * Handle a request to search available bylines
	 */
	public static function handle_bylines_search() {
		header( 'Content-Type: application/javascript' );

		// @todo capability check.
		$bylines = array();
		$term_args = array(
			'taxonomy'    => 'byline',
			'hide_empty'  => false,
		);
		if ( ! empty( $_GET['q'] ) ) {
			$term_args['search'] = sanitize_text_field( $_GET['q'] );
		}
		$terms = get_terms( $term_args );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$bylines[] = array(
					// Select2 specific.
					'id'            => (int) $term->term_id,
					'text'          => $term->name,
					// Bylines specific.
					'term'          => (int) $term->term_id,
					'display_name'  => $term->name,
				);
			}
		}
		$response = array(
			'results'    => $bylines,
		);
		echo wp_json_encode( $response );
		exit;
	}

}
