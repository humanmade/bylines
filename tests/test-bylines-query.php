<?php
/**
 * Class Test_Bylines_Query
 *
 * @package Bylines
 */

use Bylines\Objects\Byline;

/**
 * Test functionality related to modifying the main query
 */
class Test_Bylines_Query extends WP_UnitTestCase {

	/**
	 * Two bylines assigned to a post should each have the post appear in their
	 * archives.
	 */
	public function test_query_two_bylines_assigned_to_post_archive() {
		$this->user_id1 = $this->factory->user->create( array(
			'display_name'   => 'User 1',
		) );
		$this->user_id2 = $this->factory->user->create( array(
			'display_name'   => 'User 2',
		) );
		$this->post_id1 = $this->factory->post->create( array(
			'post_author' => $this->user_id1,
		) );
		$this->post_id2 = $this->factory->post->create( array(
			'post_author' => $this->user_id2,
		) );
		$byline1 = Byline::create_from_user( $this->user_id1 );
		$byline2 = Byline::create_from_user( $this->user_id2 );
		$bylines = array( $byline1->term_id, $byline2->term_id );
		wp_set_object_terms( $this->post_id1, $bylines, 'byline' );
		wp_set_object_terms( $this->post_id2, $bylines, 'byline' );
		// User 1.
		$this->go_to( get_author_posts_url( $this->user_id1 ) );
		$this->assertEquals( 2, $GLOBALS['wp_query']->found_posts );
		// User 2.
		$this->go_to( get_author_posts_url( $this->user_id2 ) );
		$this->assertEquals( 2, $GLOBALS['wp_query']->found_posts );
	}

	/**
	 * An existing post_author should have all assigned posts, unless
	 * limiting filter is applied.
	 */
	public function test_query_existing_post_author_with_byline() {
		$this->user_id1 = $this->factory->user->create( array(
			'display_name'   => 'User 1',
		) );
		$this->post_id1 = $this->factory->post->create( array(
			'post_author' => $this->user_id1,
		) );
		$this->post_id2 = $this->factory->post->create( array(
			'post_author' => $this->user_id1,
		) );
		$this->go_to( get_author_posts_url( $this->user_id1 ) );
		$this->assertEquals( 2, $GLOBALS['wp_query']->found_posts );
		// Create a new post with a byline.
		$this->post_id3 = $this->factory->post->create();
		$byline1 = Byline::create_from_user( $this->user_id1 );
		$bylines = array( $byline1->term_id );
		wp_set_object_terms( $this->post_id3, $bylines, 'byline' );
		$this->go_to( get_author_posts_url( $this->user_id1 ) );
		$this->assertEquals( 3, $GLOBALS['wp_query']->found_posts );
	}

	/**
	 * Queried object is overloaded on the author archive
	 */
	public function test_query_overload_queried_object_author_archive() {
		$this->markTestSkipped( 'Needs implementing' );
	}

	/**
	 * Query isn't modified when a non-existant user id is passed
	 */
	public function test_query_not_modified_when_user_id_doesnt_exist() {
		$this->markTestSkipped( 'Needs implementing' );
	}

}
