<?php
/**
 * Class Bylines_Testcase
 *
 * @package Bylines
 */

/**
 * Test suite modifications for Bylines
 */
class Bylines_Testcase extends WP_UnitTestCase {

	/**
	 * Set up the tests
	 */
	public function setUp() {
		$this->setup_permalink_structure();
		parent::setUp();
	}

	/**
	 * Set up sexy permalink structure
	 */
	protected function setup_permalink_structure() {
		global $wp_rewrite;

		$structure = '/%year%/%monthnum%/%day%/%postname%/';

		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( $structure );

		create_initial_taxonomies();

		$wp_rewrite->flush_rules();
	}

}
