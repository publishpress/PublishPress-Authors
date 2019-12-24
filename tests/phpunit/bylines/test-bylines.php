<?php
/**
 * Class Test_Bylines
 *
 * @package Bylines
 */

use Bylines\Objects\Byline;
use Bylines\Utils;

/**
 * Test functionality related to bylines
 */
class Test_Bylines extends Bylines_Testcase {

	/**
	 * Byline should be assigned when a new post is created
	 */
	public function test_create_post_assigns_initial_byline() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'author',
			)
		);
		$byline = Byline::create_from_user( $user_id );
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
			)
		);
		$this->assertEquals( array( $byline ), get_bylines( $post_id ) );
	}

	/**
	 * Saving bylines generically
	 */
	public function test_save_bylines() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'editor',
			)
		);
		wp_set_current_user( $user_id );
		$post_id = $this->factory->post->create();
		$b1 = Byline::create(
			array(
				'slug'  => 'b1',
				'display_name' => 'Byline 1',
			)
		);
		$b2 = Byline::create(
			array(
				'slug'  => 'b2',
				'display_name' => 'Byline 2',
			)
		);
		// Mock a POST request.
		$_POST = array(
			'bylines' => array(
				$b1->term_id,
				$b2->term_id,
			),
			'bylines-save' => wp_create_nonce( 'bylines-save' ),
		);
		do_action( 'save_post', $post_id, get_post( $post_id ), true );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 2, $bylines );
		$this->assertEquals( array( 'b1', 'b2' ), wp_list_pluck( $bylines, 'slug' ) );
	}

	/**
	 * Saving bylines by creating a new user
	 */
	public function test_save_bylines_create_new_user() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'editor',
			)
		);
		wp_set_current_user( $user_id );
		$post_id = $this->factory->post->create();
		$b1 = Byline::create(
			array(
				'slug'  => 'b1',
				'display_name' => 'Byline 1',
			)
		);
		$user_id = $this->factory->user->create(
			array(
				'display_name'  => 'Foo Bar',
				'user_nicename' => 'foobar',
			)
		);
		$this->assertFalse( Byline::get_by_user_id( $user_id ) );
		// Mock a POST request.
		$_POST = array(
			'bylines' => array(
				'u' . $user_id,
				$b1->term_id,
			),
			'bylines-save' => wp_create_nonce( 'bylines-save' ),
		);
		do_action( 'save_post', $post_id, get_post( $post_id ), true );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 2, $bylines );
		$this->assertEquals( array( 'foobar', 'b1' ), wp_list_pluck( $bylines, 'slug' ) );
		$byline = Byline::get_by_user_id( $user_id );
		$this->assertInstanceOf( 'Bylines\Objects\Byline', $byline );
		$this->assertEquals( 'Foo Bar', $byline->display_name );
	}

	/**
	 * Saving bylines by repurposing an existing user
	 */
	public function test_save_bylines_existing_user() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'editor',
			)
		);
		wp_set_current_user( $user_id );
		$post_id = $this->factory->post->create();
		$b1 = Byline::create(
			array(
				'slug'  => 'b1',
				'display_name' => 'Byline 1',
			)
		);
		$user_id = $this->factory->user->create(
			array(
				'display_name'  => 'Foo Bar',
				'user_nicename' => 'foobar',
			)
		);
		$byline = Byline::create_from_user( $user_id );
		$this->assertInstanceOf( 'Bylines\Objects\Byline', $byline );
		// Mock a POST request.
		$_POST = array(
			'bylines' => array(
				'u' . $user_id,
				$b1->term_id,
			),
			'bylines-save' => wp_create_nonce( 'bylines-save' ),
		);
		do_action( 'save_post', $post_id, get_post( $post_id ), true );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 2, $bylines );
		$this->assertEquals( array( 'foobar', 'b1' ), wp_list_pluck( $bylines, 'slug' ) );
	}

	/**
	 * Saving a post without any bylines
	 */
	public function test_save_bylines_none() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'editor',
			)
		);
		wp_set_current_user( $user_id );
		$post_id = $this->factory->post->create( array(
			'post_author' => $user_id,
		) );
		$this->assertEquals( $user_id, get_post( $post_id )->post_author );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 1, $bylines );
		// Mock a POST request.
		$_POST = array(
			'bylines' => array(),
			'bylines-save' => wp_create_nonce( 'bylines-save' ),
		);
		do_action( 'save_post', $post_id, get_post( $post_id ), true );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 0, $bylines );
		$this->assertEquals( 0, get_post( $post_id )->post_author );
	}

}
