<?php
/**
 * Representation of an individual byline.
 *
 * @package Bylines
 */

namespace Bylines\Objects;

use WP_Error;

/**
 * Representation of an individual byline.
 */
class Byline {

	/**
	 * ID for the correlated term.
	 *
	 * @var integer
	 */
	private $term_id;

	/**
	 * Create a new byline object
	 *
	 * @param array $args Arguments with which to create the new object.
	 * @return Byline|WP_Error
	 */
	public static function create( $args ) {
		if ( empty( $args['slug'] ) ) {
			return new WP_Error( 'missing-slug', __( "'slug' is a required argument", 'bylines' ) );
		}
		if ( empty( $args['display_name'] ) ) {
			return new WP_Error( 'missing-display_name', __( "'display_name' is a required argument", 'bylines' ) );
		}
		$term = wp_insert_term( $args['display_name'], 'byline', array(
			'slug'     => $args['slug'],
		) );
		if ( is_wp_error( $term ) ) {
			return $term;
		}
		$byline = new Byline( $term['term_id'] );
		return $byline;
	}

	/**
	 * Create a new byline object from an existing WordPress user.
	 *
	 * @param WP_User|integer $user WordPress user to clone.
	 * @return Byline|WP_Error
	 */
	public static function create_from_user( $user ) {
		global $wpdb;
		if ( is_int( $user ) ) {
			$user = get_user_by( 'id', $user );
		}
		if ( ! is_a( $user, 'WP_User' ) ) {
			return new WP_Error( 'missing-user', __( "User doesn't exist", 'bylines' ) );
		}
		$existing_id = $wpdb->get_var( $wpdb->prepare( "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key='user_id' AND meta_value=%d", $user->ID ) );
		if ( $existing_id ) {
			return new WP_Error( 'existing-byline', __( 'User already has a byline.', 'bylines' ) );
		}
		$term = wp_insert_term( $user->display_name, 'byline', array(
			'slug'     => $user->user_nicename,
		) );
		if ( is_wp_error( $term ) ) {
			return $term;
		}
		$byline = new Byline( $term['term_id'] );
		// Clone applicable user fields.
		$user_fields = array(
			'user_login',
			'first_name',
			'last_name',
			'user_email',
		);
		update_term_meta( $byline->term_id, 'user_id', $user->ID );
		foreach ( $user_fields as $field ) {
			update_term_meta( $byline->term_id, $field, $user->$field );
		}
		$meta = get_term_meta( $byline->term_id );
		return $byline;
	}

	/**
	 * Get a byline object based on its term id.
	 *
	 * @param integer $term_id ID for the byline term.
	 * @return Byline|false
	 */
	public static function get_by_term_id( $term_id ) {
		return new Byline( $term_id );
	}

	/**
	 * Instantiate a new byline object
	 *
	 * Bylines are always fetched by static fetchers.
	 *
	 * @param integer $term_id ID for the correlated term.
	 */
	private function __construct( $term_id ) {
		$this->term_id = (int) $term_id;
	}

	/**
	 * Get an object attribute.
	 *
	 * @param string $attribute Attribute name.
	 * @return mixed
	 */
	public function __get( $attribute ) {
		// Underscore prefix means protected.
		if ( '_' === $attribute[0] ) {
			return null;
		}
		if ( isset( $this->$attribute ) ) {
			return $this->$attribute;
		}
		if ( in_array( $attribute, array( 'display_name', 'slug' ), true ) ) {
			$field = 'display_name' === $attribute ? 'name' : 'slug';
			return get_term_field( $field, $this->term_id, 'byline', 'raw' );
		}
		return get_term_meta( $this->term_id, $attribute, true );
	}

}
