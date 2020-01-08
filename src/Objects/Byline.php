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
		$term = wp_insert_term(
			$args['display_name'], 'byline', array(
				'slug'     => $args['slug'],
			)
		);
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
		if ( is_int( $user ) ) {
			$user = get_user_by( 'id', $user );
		}
		if ( ! is_a( $user, 'WP_User' ) ) {
			return new WP_Error( 'missing-user', __( "User doesn't exist", 'bylines' ) );
		}
		$existing = self::get_by_user_id( $user->ID );
		if ( $existing ) {
			return new WP_Error( 'existing-byline', __( 'User already has a byline.', 'bylines' ) );
		}
		$byline = self::create(
			array(
				'display_name'    => $user->display_name,
				'slug'            => $user->user_nicename,
			)
		);
		if ( is_wp_error( $byline ) ) {
			return $byline;
		}
		// Clone applicable user fields.
		$user_fields = array(
			'first_name',
			'last_name',
			'user_email',
			'user_login',
			'user_url',
			'description',
		);
		update_term_meta( $byline->term_id, 'user_id', $user->ID );
		foreach ( $user_fields as $field ) {
			update_term_meta( $byline->term_id, $field, $user->$field );
		}
		$meta = get_term_meta( $byline->term_id );

		/**
		 * Fires when a byline term is created from a WP user object.
		 *
		 * @param Bylines\Objects\Byline $byline Byline term created.
		 * @param WP_User                $user   WordPress user.
		 */
		$byline = apply_filters( 'byline_created_from_user', $byline, $user );

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
	 * Get a byline object based on its term slug.
	 *
	 * @param string $slug Slug for the byline term.
	 * @return Byline|false
	 */
	public static function get_by_term_slug( $slug ) {
		$term = get_term_by( 'slug', $slug, 'byline' );
		if ( ! $term || is_wp_error( $term ) ) {
			return false;
		}
		return new Byline( $term->term_id );
	}

	/**
	 * Get a byline object based on its user id.
	 *
	 * @param integer $user_id ID for the byline's user.
	 * @return Byline|false
	 */
	public static function get_by_user_id( $user_id ) {
		global $wpdb;
		$term_id = $wpdb->get_var( $wpdb->prepare( "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key=%s", 'user_id_' . $user_id ) );
		if ( ! $term_id ) {
			return false;
		}
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

		if ( 'link' === $attribute ) {
			return get_term_link( $this->term_id, 'byline' );
		}

		// These two fields are actually on the Term object.
		if ( 'display_name' === $attribute ) {
			$attribute = 'name';
		}
		if ( 'user_nicename' === $attribute ) {
			$attribute = 'slug';
		}

		if ( in_array( $attribute, array( 'name', 'slug' ), true ) ) {
			return get_term_field( $attribute, $this->term_id, 'byline', 'raw' );
		}
		return get_term_meta( $this->term_id, $attribute, true );
	}

}
