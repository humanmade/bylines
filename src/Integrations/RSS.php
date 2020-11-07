<?php
/**
 * Render bylines within WordPress' default RSS feed templates.
 *
 * @package Bylines
 */

namespace Bylines\Integrations;

use Bylines\Content_Model;

/**
 * Render bylines within WordPress' default RSS feed templates.
 */
class RSS {

	/**
	 * Display the first byline in WordPress' use of the_author()
	 *
	 * @param string $author Existing author string.
	 */
	public static function filter_the_author( $author ) {
		if ( ! is_feed() || ! self::is_supported_post_type() ) {
			return $author;
		}

		$bylines = get_bylines();
		$first   = array_shift( $bylines );
		return ! empty( $first ) ? $first->display_name : '';
	}

	/**
	 * Add any additional bylines to the feed.
	 */
	public static function action_rss2_item() {
		if ( ! self::is_supported_post_type() ) {
			return;
		}
		$bylines = get_bylines();
		// Ditch the first byline, which was already rendered above.
		array_shift( $bylines );
		foreach ( $bylines as $byline ) {
			echo '<dc:creator><![CDATA[' . esc_html( $byline->display_name ) . ']]></dc:creator>' . PHP_EOL;
		}
	}

	/**
	 * Whether or not the global post is a supported post type
	 *
	 * @return boolean
	 */
	private static function is_supported_post_type() {
		global $post;

		// Can't determine post, so assume true.
		if ( ! $post ) {
			return true;
		}
		return in_array( $post->post_type, Content_Model::get_byline_supported_post_types(), true );
	}

}
