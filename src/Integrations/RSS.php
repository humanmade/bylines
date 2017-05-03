<?php
/**
 * Render bylines within WordPress' default RSS feed templates.
 *
 * @package Bylines
 */

namespace Bylines\Integrations;

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
		if ( ! is_feed() ) {
			return $author;
		}
		$bylines = get_bylines();
		$first = array_shift( $bylines );
		return ! empty( $first ) ? $first->display_name : '';
	}

	/**
	 * Add any additional bylines to the feed.
	 */
	public static function action_rss2_item() {
		$bylines = get_bylines();
		// Ditch the first byline, which was already rendered above.
		array_shift( $bylines );
		foreach ( $bylines as $byline ) {
			echo '<dc:creator><![CDATA[' . esc_html( $byline->display_name ) . ']]></dc:creator>' . PHP_EOL;
		}
	}

}
