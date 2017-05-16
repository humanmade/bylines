<?php
/**
 * Class Bylines\Integrations\Theme
 *
 * @package Bylines
 */

namespace Bylines\Integrations;

/**
 * Filter standard theme template tags
 */
class Theme {

	/**
	 * Filter get_the_archive_title() to use byline on author archives
	 *
	 * @param string $title Original archive title.
	 * @return string
	 */
	public static function filter_get_the_archive_title( $title ) {
		if ( ! is_author() ) {
			return $title;
		}
		/* translators: Author archive title. 1: Author name */
		return sprintf( __( 'Author: %s', 'bylines' ), '<span class="vcard">' . get_queried_object()->display_name . '</span>' );
	}

	/**
	 * Filter get_the_archive_description() to use byline on author archives
	 *
	 * @param string $description Original archive description.
	 * @return string
	 */
	public static function filter_get_the_archive_description( $description ) {
		if ( ! is_author() ) {
			return $description;
		}
		return get_queried_object()->description;
	}

}
