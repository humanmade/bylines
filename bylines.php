<?php
/**
 * Plugin Name:     Bylines
 * Plugin URI:      https://bylines.io
 * Description:     Modern multi-author publishing for WordPress
 * Author:          Daniel Bachhuber, Hand Built
 * Author URI:      https://handbuilt.co
 * Text Domain:     bylines
 * Domain Path:     /languages
 * Version:         0.3.0-alpha
 * License:         GPL v3
 *
 * @package         Bylines
 */

define( 'BYLINES_VERSION', '0.3.0-alpha' );

/**
 * Bylines Plugin
 * Copyright (C) 2017, Daniel Bachhuber - daniel@handbuilt.co
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Warn when minimum version requirements aren't met
 */
function bylines_action_admin_notices_warn_requirements() {
	echo '<div class="error message"><p>' . __( 'Bylines requires at least WordPress 4.4 and PHP 5.3. Please make sure you meet these minimum requirements.', 'bylines' ) . '</p></div>';
}

if ( version_compare( $GLOBALS['wp_version'], '4.4', '<' )
	|| version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices', 'bylines_action_admin_notices_warn_requirements' );
	return;
}

add_action( 'init', array( 'Bylines\Content_Model', 'action_init_register_taxonomies' ) );
add_action( 'init', array( 'Bylines\Content_Model', 'action_init_late_register_taxonomy_for_object_type' ), 100 );
add_filter( 'term_link', array( 'Bylines\Content_Model', 'filter_term_link' ), 10, 3 );
add_filter( 'update_term_metadata', array( 'Bylines\Content_Model', 'filter_update_term_metadata' ), 10, 4 );
add_action( 'parse_request', array( 'Bylines\Content_Model', 'action_parse_request' ) );

// Admin customizations.
add_action( 'admin_init', array( 'Bylines\Post_Editor', 'action_admin_init' ) );
add_filter( 'manage_edit-byline_columns', array( 'Bylines\Byline_Editor', 'filter_manage_edit_byline_columns' ) );
add_filter( 'list_table_primary_column', array( 'Bylines\Byline_Editor', 'filter_list_table_primary_column' ) );
add_filter( 'manage_byline_custom_column', array( 'Bylines\Byline_Editor', 'filter_manage_byline_custom_column' ), 10, 3 );
add_filter( 'user_row_actions', array( 'Bylines\Byline_Editor', 'filter_user_row_actions' ), 10, 2 );
add_action( 'byline_edit_form_fields', array( 'Bylines\Byline_Editor', 'action_byline_edit_form_fields' ) );
add_action( 'edited_byline', array( 'Bylines\Byline_Editor', 'action_edited_byline' ) );

// Query modifications.
add_action( 'pre_get_posts', array( 'Bylines\Query', 'action_pre_get_posts' ) );
add_filter( 'posts_where', array( 'Bylines\Query', 'filter_posts_where' ), 10, 2 );
add_filter( 'posts_join', array( 'Bylines\Query', 'filter_posts_join' ), 10, 2 );
add_filter( 'posts_groupby', array( 'Bylines\Query', 'filter_posts_groupby' ), 10, 2 );

// Editor integrations.
add_action( 'wp_ajax_bylines_search', array( 'Bylines\Admin_Ajax', 'handle_bylines_search' ) );
add_action( 'wp_ajax_bylines_users_search', array( 'Bylines\Admin_Ajax', 'handle_users_search' ) );
add_action( 'wp_ajax_byline_create_from_user', array( 'Bylines\Admin_Ajax', 'handle_byline_create_from_user' ) );
add_action( 'admin_enqueue_scripts', array( 'Bylines\Assets', 'action_admin_enqueue_scripts' ) );
add_action( 'enqueue_block_editor_assets', array( 'Bylines\Assets', 'action_enqueue_block_editor_assets' ) );
add_action( 'add_meta_boxes', array( 'Bylines\Post_Editor', 'action_add_meta_boxes_late' ), 100 );
add_action( 'save_post', array( 'Bylines\Post_Editor', 'action_save_post_bylines_metabox' ), 10, 2 );
add_action( 'save_post', array( 'Bylines\Post_Editor', 'action_save_post_set_initial_byline' ), 10, 3 );

// Rest functionality for block editor integrations.
add_action(
	'init',
	function() {
		if ( ! apply_filters( 'bylines_use_native_block_editor_meta_box', false ) ) {
			return;
		}
		add_action( 'init', array( 'Bylines\Rest', 'register_meta' ) );
		add_action( 'init', array( 'Bylines\Rest', 'save_bylines' ) );
		add_filter( 'get_post_metadata', array( 'Bylines\Rest', 'filter_meta' ), 10, 3 );
		add_action( 'rest_api_init', array( 'Bylines\Rest', 'register_route' ) );
		add_action( 'rest_prepare_post', array( 'Bylines\Rest', 'remove_authors_dropdown' ) );
	},
	1
);

// Theme template tag filters.
add_filter( 'get_the_archive_title', array( 'Bylines\Integrations\Theme', 'filter_get_the_archive_title' ) );
add_filter( 'get_the_archive_description', array( 'Bylines\Integrations\Theme', 'filter_get_the_archive_description' ) );

// Integrations with other systems.
add_filter( 'the_author', array( 'Bylines\Integrations\RSS', 'filter_the_author' ), 11 );
add_action( 'rss2_item', array( 'Bylines\Integrations\RSS', 'action_rss2_item' ) );

if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

// WP-CLI needs to be registere after the autoloader.
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'byline', 'Bylines\CLI' );
}

require_once dirname( __FILE__ ) . '/includes/template-tags.php';
if ( file_exists( dirname( __FILE__ ) . '/includes/dev.php' ) ) {
	require_once dirname( __FILE__ ) . '/includes/dev.php';
}
