<?php
/**
 * Plugin Name:     Bylines
 * Plugin URI:      https://bylines.io
 * Description:     Modern multi-author publishing for WordPress
 * Author:          Daniel Bachhuber, Hand Built
 * Author URI:      https://handbuilt.co
 * Text Domain:     bylines
 * Domain Path:     /languages
 * Version:         0.1.0-alpha
 *
 * @package         Bylines
 */

add_action( 'init', array( 'Bylines\Content_Model', 'action_init_register_taxonomies' ) );
add_action( 'init', array( 'Bylines\Content_Model', 'action_init_late_register_taxonomy_for_object_type' ), 100 );

add_action( 'admin_enqueue_scripts', array( 'Bylines\Assets', 'action_admin_enqueue_scripts' ) );
add_action( 'add_meta_boxes', array( 'Bylines\Editor', 'action_add_meta_boxes_late' ), 100 );
add_action( 'save_post', array( 'Bylines\Editor', 'action_save_post_bylines_metabox' ), 10, 2 );

/**
 * Autoload without Composer
 */
spl_autoload_register( function( $class ) {
	$class = ltrim( $class, '\\' );
	if ( 0 !== stripos( $class, 'Bylines\\' ) ) {
		return;
	}

	$parts = explode( '\\', $class );
	array_shift( $parts ); // Don't need "Bylines".
	$last = array_pop( $parts ); // File should be 'class-[...].php'.
	$last = 'class-' . $last . '.php';
	$parts[] = $last;
	$file = dirname( __FILE__ ) . '/inc/' . str_replace( '_', '-', strtolower( implode( $parts, '/' ) ) );
	if ( file_exists( $file ) ) {
		require $file;
	}

});

// Template tags should always be available.
require_once dirname( __FILE__ ) . '/template-tags.php';
