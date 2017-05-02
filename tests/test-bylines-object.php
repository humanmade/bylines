<?php
/**
 * Class Test_Bylines_Object
 *
 * @package Bylines
 */

use Bylines\Objects\Byline;

/**
 * Test functionality related to the Bylines object
 */
class Test_Bylines_Object extends WP_UnitTestCase {

	/**
	 * Bylines object should look roughly like a WP_User object
	 */
	public function test_bylines_object_looks_like_wp_user_object() {
		$user_id = $this->factory->user->create( array(
			'display_name'   => 'Test Author',
			'first_name'     => 'Test',
			'last_name'      => 'Author',
			'user_email'     => 'test@example.org',
			'user_login'     => 'test-author',
			'user_nicename'  => 'testauthor',
			'user_url'       => 'http://example.org',
			'description'    => 'This is a description',
		) );
		$user = get_user_by( 'id', $user_id );
		$byline = Byline::create_from_user( $user );
		// Field: display_name.
		$this->assertEquals( $user->display_name, $byline->display_name );
		$this->assertEquals( 'Test Author', $byline->display_name );
		// Field: first_name.
		$this->assertEquals( $user->first_name, $byline->first_name );
		$this->assertEquals( 'Test', $byline->first_name );
		// Field: last_name.
		$this->assertEquals( $user->last_name, $byline->last_name );
		$this->assertEquals( 'Author', $byline->last_name );
		// Field: user_email.
		$this->assertEquals( $user->user_email, $byline->user_email );
		$this->assertEquals( 'test@example.org', $byline->user_email );
		// Field: user_login.
		$this->assertEquals( $user->user_login, $byline->user_login );
		$this->assertEquals( 'test-author', $byline->user_login );
		// Field: user_nicename.
		$this->assertEquals( $user->user_nicename, $byline->user_nicename );
		$this->assertEquals( 'testauthor', $byline->user_nicename );
		// Field: user_url.
		$this->assertEquals( $user->user_url, $byline->user_url );
		$this->assertEquals( 'http://example.org', $byline->user_url );
		// Field: description.
		$this->assertEquals( $user->description, $byline->description );
		$this->assertEquals( 'This is a description', $byline->description );
	}

	/**
	 * Ensure metadata isn't left around when reassigning users
	 */
	public function test_byline_reassign_user_remaining_meta() {
		$user_id1 = $this->factory->user->create();
		$user_id2 = $this->factory->user->create();
		$byline = Byline::create_from_user( $user_id1 );
		$this->assertEquals( $user_id1, $byline->user_id );
		$metas = get_term_meta( $byline->term_id );
		$metas = array_keys( $metas );
		$this->assertEquals( array(
			'user_id_' . $user_id1,
			'user_id',
			'first_name',
			'last_name',
			'user_email',
			'user_login',
			'user_url',
			'description',
		), $metas );
		update_term_meta( $byline->term_id, 'user_id', $user_id2 );
		$this->assertEquals( $user_id2, $byline->user_id );
		$metas = get_term_meta( $byline->term_id );
		$metas = array_keys( $metas );
		$this->assertEquals( array(
			'user_id',
			'first_name',
			'last_name',
			'user_email',
			'user_login',
			'user_url',
			'description',
			'user_id_' . $user_id2,
		), $metas );
	}

	/**
	 * Verify a byline can be fetched by its slug.
	 */
	public function test_byline_get_by_term_slug() {
		$byline = Byline::create( array(
			'slug'         => 'foo',
			'display_name' => 'Foo',
		) );
		$this->assertEquals( $byline, Byline::get_by_term_slug( 'foo' ) );
	}

}
