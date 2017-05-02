<?php
/**
 * Admin ajax endpoints
 *
 * @package Bylines
 */

namespace Bylines;

use Bylines\Objects\Byline;

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
			'number'      => 20,
		);
		if ( ! empty( $_GET['q'] ) ) {
			$term_args['search'] = sanitize_text_field( $_GET['q'] );
		}
		if ( ! empty( $_GET['ignored'] ) ) {
			$term_args['exclude'] = array();
			foreach ( $_GET['ignored'] as $val ) {
				if ( is_numeric( $val ) ) {
					$term_args['exclude'][] = (int) $val;
				}
			}
		}
		$terms = get_terms( $term_args );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$byline = Byline::get_by_term_id( $term->term_id );
				$bylines[] = array(
					// Select2 specific.
					'id'            => (int) $term->term_id,
					'text'          => $term->name,
					// Bylines specific.
					'term'          => (int) $term->term_id,
					'display_name'  => $term->name,
					'user_id'       => $byline->user_id,
					'avatar_url'    => get_avatar_url( $byline->user_email, 32 ),
				);
			}
		}
		$user_args = array(
			'number' => 20,
		);
		if ( ! empty( $_GET['q'] ) ) {
			$user_args['search'] = sanitize_text_field( $_GET['q'] );
		}
		if ( ! empty( $_GET['ignored'] ) ) {
			$user_args['exclude'] = array();
			foreach ( $_GET['ignored'] as $val ) {
				if ( 'u' === $val[0] ) {
					$user_args['exclude'][] = (int) substr( $val, 1 );
				}
			}
		}
		$users = get_users( $user_args );
		foreach ( $users as $user ) {
			$bylines[] = array(
				// Select2 specific.
				'id'            => 'u' . $user->ID,
				'text'          => $user->display_name,
				// Bylines display specific.
				'term'          => 'u' . $user->ID,
				'display_name'  => $user->display_name,
				'user_id'       => $user->ID,
				'avatar_url'    => get_avatar_url( $user->user_email, 32 ),
			);
		}
		// Sort alphabetically by display name.
		usort( $bylines, function( $a, $b ) {
			return strcmp( $a['display_name'], $b['display_name'] );
		});
		$response = array(
			'results'    => $bylines,
		);
		echo wp_json_encode( $response );
		exit;
	}

}
