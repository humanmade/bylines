<?php
/**
 * Class Test_Bylines
 *
 * @package Bylines
 */

use Bylines\Objects\Byline;

/**
 * Test functionality related to bylines
 */
class Test_Bylines extends WP_UnitTestCase {

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
		$byline = Byline::create_from_user( $user_id );
		$this->assertInstanceOf( 'Bylines\Objects\Byline', $byline );
		$this->assertEquals( $user_id, $byline->user_id );
		$this->assertEquals( 'Foo Bar', $byline->display_name );
		$this->assertEquals( 'Foo', $byline->first_name );
		$this->assertEquals( 'Bar', $byline->last_name );
		$this->assertEquals( 'foobar@gmail.com', $byline->user_email );
		$this->assertEquals( 'foobar', $byline->user_login );
	}

	/**
	 * Creating a byline from a user that doesn't exist
	 */
	public function test_create_byline_from_missing_user() {
		$byline = Byline::create_from_user( BYLINES_IMPOSSIBLY_HIGH_NUMBER );
		$this->assertInstanceOf( 'WP_Error', $byline );
		$this->assertEquals( 'missing-user', $byline->get_error_code() );
	}

	/**
	 * Creating a byline from a user that already has a byline
	 */
	public function test_create_byline_from_existing_user_byline() {
		$user_id = $this->factory->user->create();
		// Create the first byline.
		Byline::create_from_user( $user_id );
		// Attempt creating the second byline.
		$byline = Byline::create_from_user( $user_id );
		$this->assertInstanceOf( 'WP_Error', $byline );
		$this->assertEquals( 'existing-byline', $byline->get_error_code() );
	}
}
