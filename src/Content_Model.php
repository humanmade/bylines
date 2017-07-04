<?php
/**
 * Declaration of the content model
 *
 * @package Bylines
 */

namespace Bylines;

use Bylines\Objects\Byline;

/**
 * Declaration of the content model
 */
class Content_Model {

	/**
	 * Register custom taxonomies, aka groupings of content
	 */
	public static function action_init_register_taxonomies() {
		$args = array(
			'labels'       => array(
				'name'                       => _x( 'Bylines', 'taxonomy general name', 'bylines' ),
				'singular_name'              => _x( 'Byline', 'taxonomy singular name', 'bylines' ),
				'search_items'               => __( 'Search bylines', 'bylines' ),
				'popular_items'              => __( 'Popular bylines', 'bylines' ),
				'all_items'                  => __( 'All bylines', 'bylines' ),
				'parent_item'                => __( 'Parent byline', 'bylines' ),
				'parent_item_colon'          => __( 'Parent byline:', 'bylines' ),
				'edit_item'                  => __( 'Edit byline', 'bylines' ),
				'update_item'                => __( 'Update byline', 'bylines' ),
				'add_new_item'               => __( 'New byline', 'bylines' ),
				'new_item_name'              => __( 'New byline', 'bylines' ),
				'separate_items_with_commas' => __( 'Separate bylines with commas', 'bylines' ),
				'add_or_remove_items'        => __( 'Add or remove bylines', 'bylines' ),
				'choose_from_most_used'      => __( 'Choose from the most used bylines', 'bylines' ),
				'not_found'                  => __( 'No bylines found.', 'bylines' ),
				'menu_name'                  => __( 'Bylines', 'bylines' ),
			),
			'public'       => false,
			'hierarchical' => false,
			'sort'         => true,
			'args'         => array(
				'orderby' => 'term_order',
			),
			'capabilities' => array(
				'manage_terms' => 'list_users',
				'edit_terms'   => 'list_users',
				'delete_terms' => 'list_users',
				'assign_terms' => 'edit_others_posts',
			),
			'show_ui'      => true,
			'show_in_quick_edit' => false,
			'meta_box_cb'  => false,
		);
		register_taxonomy( 'byline', null, $args );
	}

	/**
	 * Register taxonomies to objects after post types have been registered
	 */
	public static function action_init_late_register_taxonomy_for_object_type() {
		foreach ( self::get_byline_supported_post_types() as $post_type ) {
			register_taxonomy_for_object_type( 'byline', $post_type );
		}
	}

	/**
	 * Filter byline term links to look like author links
	 *
	 * @param string $link     Term link URL.
	 * @param object $term     Term object.
	 * @param string $taxonomy Taxonomy slug.
	 * @return string
	 */
	public static function filter_term_link( $link, $term, $taxonomy ) {
		global $wp_rewrite;

		if ( 'byline' !== $taxonomy ) {
			return $link;
		}
		$byline = Byline::get_by_term_id( $term->term_id );
		$author_nicename = $byline ? $byline->slug : '';
		$permastruct = $wp_rewrite->get_author_permastruct();
		if ( $permastruct ) {
			$link = str_replace( '%author%', $author_nicename, $permastruct );
			$link = home_url( user_trailingslashit( $link ) );
		} else {
			$link = add_query_arg( 'author_name', rawurlencode( $author_nicename ), home_url() );
		}
		return $link;
	}

	/**
	 * Store user id as a term meta key too, for faster querying
	 *
	 * @param mixed   $check      Whether or not the update should be short-circuited.
	 * @param integer $object_id  ID for the byline term object.
	 * @param string  $meta_key   Meta key being updated.
	 * @param string  $meta_value New meta value.
	 */
	public static function filter_update_term_metadata( $check, $object_id, $meta_key, $meta_value ) {
		if ( 'user_id' !== $meta_key ) {
			return $check;
		}
		$term = get_term_by( 'id', $object_id, 'byline' );
		if ( 'byline' !== $term->taxonomy ) {
			return $check;
		}
		$metas = get_term_meta( $object_id );
		foreach ( $metas as $key => $meta ) {
			if ( 0 === strpos( $key, 'user_id_' ) ) {
				delete_term_meta( $object_id, $key );
			}
		}
		if ( $meta_value ) {
			update_term_meta( $object_id, 'user_id_' . $meta_value, 'user_id' );
		}
		return $check;
	}

	/**
	 * Get the supported post types for bylines
	 */
	public static function get_byline_supported_post_types() {
		$post_types_with_authors = array_values( get_post_types() );
		foreach ( $post_types_with_authors as $key => $name ) {
			if ( ! post_type_supports( $name, 'author' )
				|| in_array( $name, array( 'revision', 'attachment' ), true ) ) {
				unset( $post_types_with_authors[ $key ] );
			}
		}
		/**
		 * Modify post types that use bylines.
		 *
		 * @param array $post_types_with_authors Post types that support authors.
		 */
		return apply_filters( 'bylines_post_types', $post_types_with_authors );
	}

}
