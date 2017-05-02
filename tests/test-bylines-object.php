<?php
/**
 * Class Test_Bylines_Object
 *
 * @package Bylines
 */

/**
 * Test functionality related to the Bylines object
 */
class Test_Bylines_Object extends WP_UnitTestCase {

	/**
	 * Ensure metadata isn't left around when reassigning users
	 */
	public function test_byline_reassign_user_remaining_meta() {
		$user_id1 = $this->factory->user->create();
		$user_id2 = $this->factory->user->create();
		$byline = Bylines\Objects\Byline::create_from_user( $user_id1 );
		$this->assertEquals( $user_id1, $byline->user_id );
		$metas = get_term_meta( $byline->term_id );
		$metas = array_keys( $metas );
		$this->assertEquals( array(
			'user_id_' . $user_id1,
			'user_id',
			'user_login',
			'first_name',
			'last_name',
			'user_email',
		), $metas );
		update_term_meta( $byline->term_id, 'user_id', $user_id2 );
		$this->assertEquals( $user_id2, $byline->user_id );
		$metas = get_term_meta( $byline->term_id );
		$metas = array_keys( $metas );
		$this->assertEquals( array(
			'user_id',
			'user_login',
			'first_name',
			'last_name',
			'user_email',
			'user_id_' . $user_id2,
		), $metas );
	}

	/**
	 * Verify a byline can be fetched by its slug.
	 */
	public function test_byline_get_by_term_slug() {
		$byline = Bylines\Objects\Byline::create( array(
			'slug'         => 'foo',
			'display_name' => 'Foo',
		) );
		$this->assertEquals( $byline, Bylines\Objects\Byline::get_by_term_slug( 'foo' ) );
	}

}
