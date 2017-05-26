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

		if ( empty( $_GET['nonce'] )
			|| ! wp_verify_nonce( $_GET['nonce'], 'bylines-search' ) ) {
			exit;
		}

		$search = ! empty( $_GET['q'] ) ? sanitize_text_field( $_GET['q'] ) : '';
		$ignored = ! empty( $_GET['ignored'] ) ? array_map( 'sanitize_text_field', $_GET['ignored'] ) : array();
		$bylines = self::get_possible_bylines_for_search( $search, $ignored );
		$response = array(
			'results'    => $bylines,
		);
		echo wp_json_encode( $response );
		exit;
	}

	/**
	 * Handle an ajax request to search available users
	 */
	public static function handle_users_search() {
		header( 'Content-Type: application/javascript' );

		if ( empty( $_GET['nonce'] )
			|| ! wp_verify_nonce( $_GET['nonce'], 'bylines-user-search' ) ) {
			exit;
		}

		$user_args = array(
			'number' => 20,
		);
		if ( ! empty( $_GET['q'] ) ) {
			$user_args['search'] = sanitize_text_field( $_GET['q'] );
		}
		$users = get_users( $user_args );
		$results = array();
		foreach ( $users as $user ) {
			$results[] = array(
				'id'            => $user->ID,
				'text'          => $user->display_name,
			);
		}
		$response = array(
			'results'    => $results,
		);
		echo wp_json_encode( $response );
		exit;
	}

	/**
	 * Handle a GET request to create a new byline from a user
	 */
	public static function handle_byline_create_from_user() {
		if ( empty( $_GET['nonce'] )
			|| empty( $_GET['user_id'] )
			|| ! wp_verify_nonce( $_GET['nonce'], 'byline_create_from_user' . $_GET['user_id'] ) ) {
			exit;
		}

		$user_id = (int) $_GET['user_id'];
		$byline = Byline::create_from_user( $user_id );
		if ( is_wp_error( $byline ) ) {
			wp_die( $byline->get_error_message() );
		}
		$link = get_edit_term_link( $byline->term_id, 'byline' );
		wp_safe_redirect( $link );
		exit;
	}

	/**
	 * Get the possible bylines for a given search query.
	 *
	 * @param string $search  Search query.
	 * @param array  $ignored Any bylines that should be ignored.
	 * @return array
	 */
	public static function get_possible_bylines_for_search( $search, $ignored = array() ) {
		$term_args = array(
			'taxonomy'    => 'byline',
			'hide_empty'  => false,
			'number'      => 20,
		);
		if ( ! empty( $search ) ) {
			$term_args['search'] = $search;
		}
		if ( ! empty( $ignored ) ) {
			$term_args['exclude'] = array();
			$ignored_users = array();
			foreach ( $ignored as $val ) {
				if ( is_numeric( $val ) ) {
					$term_args['exclude'][] = (int) $val;
					$user_id = get_term_meta( $val, 'user_id', true );
					if ( $user_id ) {
						$ignored_users[] = 'u' . $user_id;
					}
				}
			}
			$ignored = array_merge( $ignored, $ignored_users );
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
				if ( $byline->user_id ) {
					$ignored[] = 'u' . $byline->user_id;
				}
			}
		}
		$user_args = array(
			'number' => 20,
		);
		if ( ! empty( $search ) ) {
			$user_args['search'] = $search;
		}
		if ( ! empty( $ignored ) ) {
			$user_args['exclude'] = array();
			foreach ( $ignored as $val ) {
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
		return $bylines;
	}

}
