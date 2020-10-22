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

		if ( $screen && 'post' === $screen->base && apply_filters( 'bylines_use_native_block_editor_meta_box', false ) ) {
			return;
		}

		if ( 'term' === $screen->base ) {
			wp_enqueue_media();
		}

		wp_enqueue_script( 'bylines-select2', plugins_url( 'assets/lib/select2/js/select2.min.js', dirname( __FILE__ ) ), array( 'jquery' ), '4.0.3' );
		wp_add_inline_script( 'bylines-select2', 'var existingSelect2 = jQuery.fn.select2 || null; if (existingSelect2) { delete jQuery.fn.select2; }', 'before' );
		wp_add_inline_script( 'bylines-select2', 'jQuery.fn["bylinesSelect2"] = jQuery.fn.select2; if (existingSelect2) { delete jQuery.fn.select2; jQuery.fn.select2 = existingSelect2; }', 'after' );
		wp_enqueue_style( 'bylines-select2', plugins_url( 'assets/lib/select2/css/select2.min.css', dirname( __FILE__ ) ), array(), '4.0.3' );

		$bylines_script_translation = array(
			'media_upload_title' => __( 'Select or Upload Image to Your Chosen Byline', 'bylines' ),
			'media_upload_button' => __( 'Use this image', 'bylines' ),
		);
		$mtime = filemtime( dirname( dirname( __FILE__ ) ) . '/assets/js/bylines.js' );
		wp_enqueue_script( 'bylines', plugins_url( 'assets/js/bylines.js?mtime=' . $mtime, dirname( __FILE__ ) ), array( 'jquery', 'bylines-select2', 'jquery-ui-core', 'jquery-ui-sortable' ) );
		wp_localize_script( 'bylines', 'bylines', $bylines_script_translation );
		$mtime = filemtime( dirname( dirname( __FILE__ ) ) . '/assets/css/bylines.css' );
		wp_enqueue_style( 'bylines', plugins_url( 'assets/css/bylines.css?mtime=' . $mtime, dirname( __FILE__ ) ) );
	}

	/**
	 * Enqueue scripts in the block editor
	 */
	public static function action_enqueue_block_editor_assets() {
		if ( ! apply_filters( 'bylines_use_native_block_editor_meta_box', false ) || ! current_user_can( get_taxonomy( 'byline' )->cap->assign_terms ) ) {
			return;
		}
		$screen = get_current_screen();

		// Only render on supported post types.
		if ( ! in_array( $screen->post_type, Content_Model::get_byline_supported_post_types(), true ) ) {
			return;
		}

		$dir               = plugin_dir_path( __DIR__ );
		$script_asset_path = "$dir/assets/block-editor/build/index.asset.php";
		if ( ! file_exists( $script_asset_path ) ) {
			throw new Error(
				'You need to run `npm start` or `npm run build` for the "create-block/promotion-indexing" block first.'
			);
		}
		$index_js     = 'build/index.js';
		$script_asset = require $script_asset_path;
		wp_enqueue_script(
			'bylines-block-editor',
			plugins_url( "assets/block-editor/{$index_js}", dirname( __FILE__ ) ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}
}
