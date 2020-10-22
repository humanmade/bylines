<?php
/**
 * Rest endpoints and functionality
 *
 * @package Bylines
 */

namespace Bylines;

use Bylines\Objects\Byline;

/**
 * Rest endpoints and functionality
 */
class Rest {

	/**
	 * Register meta
	 */
	public static function register_meta() {
		foreach ( Content_Model::get_byline_supported_post_types() as $post_type ) {
			register_meta(
				'post',
				'bylines',
				array(
					'object_subtype' => $post_type,
					'single'         => true,
					'type'           => 'array',
					'show_in_rest'   => array(
						'schema' => array(
							'type'  => 'array',
							'items' => array(
								'type' => 'string',
							),
						),
					),
				)
			);
		}
	}

	/**
	 * Instead of writing loads of custom logic for the rest api
	 * and the block editor, we "fake" a metadata that contains
	 * the bylines and then use that during the save to set the
	 * byline terms.
	 *
	 * @param mixed  $value     The value to return, either a single metadata value or an array
	 *                          of values depending on the value of `$single`. Default null.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key  Metadata key.
	 */
	public static function filter_meta( $value, $object_id, $meta_key ) {
		if ( 'bylines' !== $meta_key ) {
			return $value;
		}
		$byline_display_data = array();
		foreach ( get_bylines( $object_id ) as $byline ) {
			$display_name          = $byline->display_name;
			$term                  = is_a( $byline, 'WP_User' ) ? 'u' . $byline->ID : $byline->term_id;
			$byline_display_data[] = (string) $term;
		}
		return array( $byline_display_data );
	}

	/**
	 * Register the bylines rest api route.
	 */
	public static function register_route(): void {
		register_rest_route(
			'bylines/v1',
			'/bylines',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => function( $request ) {
						$bylines = Admin_Ajax::get_possible_bylines_for_search( (string) $request['s'] );
						return rest_ensure_response( $bylines );
					},
					'permission_callback' => function() {
						return current_user_can( get_taxonomy( 'byline' )->cap->assign_terms );
					},
					'args'                => array(
						's' => array(
							'description' => 'A search string.',
							'type'        => 'string',
						),
					),
				),
			)
		);
	}

	/**
	 * Save the bylines posted by the block editor.
	 * Deletes the meta data afterwards since it's only used as
	 * a transportation vessel.
	 */
	public static function save_bylines() {
		foreach ( Content_Model::get_byline_supported_post_types() as $post_type ) {
			add_action(
				"rest_after_insert_{$post_type}",
				function( $post, $request ) {
					global $wpdb;
					if ( ! isset( $request['meta']['bylines'] ) ) {
						$dirty_bylines = array( 'u' . $post->post_author );
					} else {
						$dirty_bylines = $request['meta']['bylines'];
					}
					$bylines = array();
					foreach ( $dirty_bylines as $dirty_byline ) {
						if ( is_numeric( $dirty_byline ) ) {
							$bylines[] = Byline::get_by_term_id( $dirty_byline );
						} elseif ( 'u' === $dirty_byline[0] ) {
							$user_id = (int) substr( $dirty_byline, 1 );
							$byline  = Byline::get_by_user_id( $user_id );
							if ( ! $byline ) {
								$byline = Byline::create_from_user( $user_id );
								if ( is_wp_error( $byline ) ) {
									continue;
								}
							}
							$bylines[] = $byline;
						}
					}
					Utils::set_post_bylines( $post->ID, $bylines );
					if ( empty( $bylines ) ) {
						$wpdb->update(
							$wpdb->posts,
							array(
								'post_author' => 0,
							),
							array(
								'ID' => $post->ID,
							)
						);
						clean_post_cache( $post->ID );
					}
					delete_post_meta( $post->ID, 'bylines' );
					return $post;
				},
				10,
				2
			);
		}
	}

	/**
	 * Remove the link from the rest response that the block editor
	 * uses to determine if the current user can change authors.
	 *
	 * @param WP_Rest_Response $response The rest response to filter.
	 */
	public static function remove_authors_dropdown( $response ) {
		$response->remove_link( 'wp:action-assign-author' );
		return $response;
	}

}
