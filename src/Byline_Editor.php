<?php
/**
 * Customizations to the byline term editor experience
 *
 * @package Bylines
 */

namespace Bylines;

use Bylines\Objects\Byline;

/**
 * Customizations to the byline term editor experience
 */
class Byline_Editor {

	/**
	 * Customize the term table to look more like the users table.
	 *
	 * @param array $columns Columns to render in the list table.
	 * @return array
	 */
	public static function filter_manage_edit_byline_columns( $columns ) {
		// Reserve the description for internal use.
		if ( isset( $columns['description'] ) ) {
			unset( $columns['description'] );
		}
		return $columns;
	}

}
