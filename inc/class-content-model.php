<?php
/**
 * Declaration of the content model
 *
 * @package Bylines
 */

namespace Bylines;

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
		return $post_types_with_authors;
	}

}
