<?php
/**
 * Class Test_Bylines_Template_Tags
 *
 * @package Bylines
 */

/**
 * Test functionality related to the Bylines object
 */
class Test_Bylines_Template_Tags extends WP_UnitTestCase {

	/**
	 * Render one byline, without the link to its post
	 */
	public function test_template_tag_the_bylines_one_byline() {
		global $post;
		$b1 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b1',
			'display_name' => 'Byline 1',
		) );
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		wp_set_object_terms( $post_id, array( $b1->term_id ), 'byline' );
		$this->expectOutputString( 'Byline 1' );
		the_bylines();
	}

	/**
	 * Render two bylines, without the link to its post
	 */
	public function test_template_tag_the_bylines_two_byline() {
		global $post;
		$b1 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b1',
			'display_name' => 'Byline 1',
		) );
		$b2 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b2',
			'display_name' => 'Byline 2',
		) );
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		wp_set_object_terms( $post_id, array( $b2->term_id, $b1->term_id ), 'byline' );
		$this->expectOutputString( 'Byline 2 and Byline 1' );
		the_bylines();
	}

	/**
	 * Render three bylines, without the link to its post
	 */
	public function test_template_tag_the_bylines_three_byline() {
		global $post;
		$b1 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b1',
			'display_name' => 'Byline 1',
		) );
		$b2 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b2',
			'display_name' => 'Byline 2',
		) );
		$b3 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b3',
			'display_name' => 'Byline 3',
		) );
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		wp_set_object_terms( $post_id, array( $b2->term_id, $b3->term_id, $b1->term_id ), 'byline' );
		$this->expectOutputString( 'Byline 2, Byline 3, and Byline 1' );
		the_bylines();
	}

	/**
	 * Render four bylines, without the link to its post
	 */
	public function test_template_tag_the_bylines_four_byline() {
		global $post;
		$b1 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b1',
			'display_name' => 'Byline 1',
		) );
		$b2 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b2',
			'display_name' => 'Byline 2',
		) );
		$b3 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b3',
			'display_name' => 'Byline 3',
		) );
		$b4 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b4',
			'display_name' => 'Byline 4',
		) );
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		wp_set_object_terms( $post_id, array( $b2->term_id, $b4->term_id, $b3->term_id, $b1->term_id ), 'byline' );
		$this->expectOutputString( 'Byline 2, Byline 4, Byline 3, and Byline 1' );
		the_bylines();
	}

	/**
	 * Render two bylines, with the link to its post
	 */
	public function test_template_tag_the_bylines_posts_links_two_byline() {
		global $post;
		$b1 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b1',
			'display_name' => 'Byline 1',
		) );
		$b2 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b2',
			'display_name' => 'Byline 2',
		) );
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		wp_set_object_terms( $post_id, array( $b2->term_id, $b1->term_id ), 'byline' );
		$this->expectOutputString( '<a href="' . $b2->link . '" title="Posts by Byline 2" class="author url fn" rel="author">Byline 2</a> and <a href="' . $b1->link . '" title="Posts by Byline 1" class="author url fn" rel="author">Byline 1</a>' );
		the_bylines_posts_links();
	}

}
