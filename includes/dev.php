<?php
/**
 * Loaded for development versions of the plugin
 *
 * @package Bylines
 */

add_action( 'wp_head', function() {
	echo '<!-- This site is running a development version of Bylines 0.1.0 -->' . PHP_EOL;
});
