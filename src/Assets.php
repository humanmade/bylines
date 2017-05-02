<?php
/**
 * Enqueuing of scripts and styles
 *
 * @package Bylines
 */

namespace Bylines;

/**
 * Enqueuing of scripts and styles
 */
class Assets {

	/**
	 * Enqueue scripts and styles in the admin
	 */
	public static function action_admin_enqueue_scripts() {

		$screen = get_current_screen();
		if ( ! $screen
			|| ! ( ( 'post' === $screen->base && in_array( $screen->post_type, Content_Model::get_byline_supported_post_types() ) )
			|| ( isset( $screen->taxonomy ) && 'byline' === $screen->taxonomy ) ) ) {
			return;
		}

		wp_enqueue_script( 'bylines-select2', plugins_url( 'assets/lib/select2/js/select2.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '4.0.3' );
		wp_add_inline_script( 'bylines-select2', 'var existingSelect2 = jQuery.fn.select2 || null; if (existingSelect2) { delete jQuery.fn.select2; }', 'before' );
		wp_add_inline_script( 'bylines-select2', 'jQuery.fn["bylinesSelect2"] = jQuery.fn.select2; if (existingSelect2) { delete jQuery.fn.select2; jQuery.fn.select2 = existingSelect2; }', 'after' );
		wp_enqueue_style( 'bylines-select2', plugins_url( 'assets/lib/select2/css/select2.min.css', dirname( __FILE__ ) ), array(), '4.0.3' );

		$mtime = filemtime( dirname( dirname( __FILE__ ) ) . '/assets/js/bylines.js' );
		wp_enqueue_script( 'bylines', plugins_url( 'assets/js/bylines.js?mtime=' . $mtime, dirname( __FILE__ ) ), array( 'jquery', 'bylines-select2', 'jquery-ui-core', 'jquery-ui-sortable' ) );
		$mtime = filemtime( dirname( dirname( __FILE__ ) ) . '/assets/css/bylines.css' );
		wp_enqueue_style( 'bylines', plugins_url( 'assets/css/bylines.css?mtime=' . $mtime, dirname( __FILE__ ) ) );
	}
}
