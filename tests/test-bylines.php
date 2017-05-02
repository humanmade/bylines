<?php
/**
 * Class Test_Bylines
 *
 * @package Bylines
 */

/**
 * Test functionality related to bylines
 */
class Test_Bylines extends WP_UnitTestCase {

	/**
	 * Create a new byline from thin air
	 */
	public function test_create_byline() {
		$byline = Bylines\Objects\Byline::create( array(
			'display_name' => 'Foo Bar',
			'slug'         => 'foobar',
		) );
		$this->assertInstanceOf( 'Bylines\Objects\Byline', $byline );
		$this->assertEquals( 'Foo Bar', $byline->display_name );
		$this->assertEquals( 'foobar', $byline->slug );
	}

	/**
	 * Creating a byline but missing arguments
	 */
	public function test_create_byline_missing_arguments() {
		$byline = Bylines\Objects\Byline::create( array() );
		$this->assertInstanceOf( 'WP_Error', $byline );
		$this->assertEquals( 'missing-slug', $byline->get_error_code() );
		$byline = Bylines\Objects\Byline::create( array(
			'slug' => 'foobar',
		) );
		$this->assertInstanceOf( 'WP_Error', $byline );
		$this->assertEquals( 'missing-display_name', $byline->get_error_code() );
	}

	/**
	 * Creating a byline from an existing user
	 */
	public function test_create_byline_from_user() {
		$user_id = $this->factory->user->create( array(
			'display_name'    => 'Foo Bar',
			'first_name'      => 'Foo',
			'last_name'       => 'Bar',
			'user_email'      => 'foobar@gmail.com',
			'user_login'      => 'foobar',
		) );
		$byline = Bylines\Objects\Byline::create_from_user( $user_id );
		$this->assertInstanceOf( 'Bylines\Objects\Byline', $byline );
		$this->assertEquals( $user_id, $byline->user_id );
		$this->assertEquals( 'Foo Bar', $byline->display_name );
		$this->assertEquals( 'foobar', $byline->slug );
		$this->assertEquals( 'Foo', $byline->first_name );
		$this->assertEquals( 'Bar', $byline->last_name );
		$this->assertEquals( 'foobar@gmail.com', $byline->user_email );
		$this->assertEquals( 'foobar', $byline->user_login );
	}

	/**
	 * Creating a byline from a user that doesn't exist
	 */
	public function test_create_byline_from_missing_user() {
		$byline = Bylines\Objects\Byline::create_from_user( BYLINES_IMPOSSIBLY_HIGH_NUMBER );
		$this->assertInstanceOf( 'WP_Error', $byline );
		$this->assertEquals( 'missing-user', $byline->get_error_code() );
	}

	/**
	 * Creating a byline from a user that already has a byline
	 */
	public function test_create_byline_from_existing_user_byline() {
		$user_id = $this->factory->user->create();
		// Create the first byline.
		Bylines\Objects\Byline::create_from_user( $user_id );
		// Attempt creating the second byline.
		$byline = Bylines\Objects\Byline::create_from_user( $user_id );
		$this->assertInstanceOf( 'WP_Error', $byline );
		$this->assertEquals( 'existing-byline', $byline->get_error_code() );
	}

	/**
	 * Getting bylines generically
	 */
	public function test_get_bylines() {
		$b1 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b1',
			'display_name' => 'Byline 1',
		) );
		$b2 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b2',
			'display_name' => 'Byline 2',
		) );
		$post_id = $this->factory->post->create();
		wp_set_object_terms( $post_id, array( $b1->term_id, $b2->term_id ), 'byline' );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 2, $bylines );
		$this->assertEquals( array( 'b1', 'b2' ), wp_list_pluck( $bylines, 'slug' ) );
		// Ensure the order persists.
		wp_set_object_terms( $post_id, array( $b2->term_id, $b1->term_id ), 'byline' );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 2, $bylines );
		$this->assertEquals( array( 'b2', 'b1' ), wp_list_pluck( $bylines, 'slug' ) );
	}

	/**
	 * Saving bylines generically
	 */
	public function test_save_bylines() {
		$post_id = $this->factory->post->create();
		$b1 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b1',
			'display_name' => 'Byline 1',
		) );
		$b2 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b2',
			'display_name' => 'Byline 2',
		) );
		// Mock a POST request.
		$_POST = array(
			'bylines' => array(
				$b1->term_id,
				$b2->term_id,
			),
		);
		do_action( 'save_post', $post_id, get_post( $post_id ) );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 2, $bylines );
		$this->assertEquals( array( 'b1', 'b2' ), wp_list_pluck( $bylines, 'slug' ) );
	}

	/**
	 * Saving bylines by creating a new user
	 */
	public function test_save_bylines_create_new_user() {
		$post_id = $this->factory->post->create();
		$b1 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b1',
			'display_name' => 'Byline 1',
		) );
		$user_id = $this->factory->user->create( array(
			'display_name'  => 'Foo Bar',
			'user_nicename' => 'foobar',
		) );
		$this->assertFalse( Bylines\Objects\Byline::get_by_user_id( $user_id ) );
		// Mock a POST request.
		$_POST = array(
			'bylines' => array(
				'u' . $user_id,
				$b1->term_id,
			),
		);
		do_action( 'save_post', $post_id, get_post( $post_id ) );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 2, $bylines );
		$this->assertEquals( array( 'foobar', 'b1' ), wp_list_pluck( $bylines, 'slug' ) );
		$byline = Bylines\Objects\Byline::get_by_user_id( $user_id );
		$this->assertInstanceOf( 'Bylines\Objects\Byline', $byline );
		$this->assertEquals( 'Foo Bar', $byline->display_name );
	}

	/**
	 * Saving bylines by repurposing an existing user
	 */
	public function test_save_bylines_existing_user() {
		$post_id = $this->factory->post->create();
		$b1 = Bylines\Objects\Byline::create( array(
			'slug'  => 'b1',
			'display_name' => 'Byline 1',
		) );
		$user_id = $this->factory->user->create( array(
			'display_name'  => 'Foo Bar',
			'user_nicename' => 'foobar',
		) );
		$byline = Bylines\Objects\Byline::create_from_user( $user_id );
		$this->assertInstanceOf( 'Bylines\Objects\Byline', $byline );
		// Mock a POST request.
		$_POST = array(
			'bylines' => array(
				'u' . $user_id,
				$b1->term_id,
			),
		);
		do_action( 'save_post', $post_id, get_post( $post_id ) );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 2, $bylines );
		$this->assertEquals( array( 'foobar', 'b1' ), wp_list_pluck( $bylines, 'slug' ) );
	}

}
