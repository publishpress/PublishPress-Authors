<?php
/**
 * Class Test_Bylines_Template_Tags
 *
 * @package Bylines
 */

use Bylines\Objects\Byline;
use Bylines\Utils;

/**
 * Test functionality related to the Bylines object
 */
class Test_Bylines_Template_Tags extends Bylines_Testcase {

	/**
	 * Getting bylines generically
	 */
	public function test_get_bylines() {
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
		$post_id = $this->factory->post->create();
		Utils::set_post_bylines( $post_id, array( $b1, $b2 ) );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 2, $bylines );
		$this->assertEquals( array( 'b1', 'b2' ), wp_list_pluck( $bylines, 'slug' ) );
		// Ensure the order persists.
		Utils::set_post_bylines( $post_id, array( $b2, $b1 ) );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 2, $bylines );
		$this->assertEquals( array( 'b2', 'b1' ), wp_list_pluck( $bylines, 'slug' ) );
	}

	/**
	 * Ensure get_bylines() returns a user object when no bylines are assigned
	 */
	public function test_get_bylines_returns_wp_user() {
		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
			)
		);
		$bylines = get_bylines( $post_id );
		$this->assertCount( 1, $bylines );
		$this->assertEquals( array( $user_id ), wp_list_pluck( $bylines, 'ID' ) );
		// Adding a byline means the user id should no longer be returned.
		$b1 = Byline::create(
			array(
				'slug'  => 'b1',
				'display_name' => 'Byline 1',
			)
		);
		Utils::set_post_bylines( $post_id, array( $b1 ) );
		$bylines = get_bylines( $post_id );
		$this->assertCount( 1, $bylines );
		$this->assertEquals( array( 'b1' ), wp_list_pluck( $bylines, 'slug' ) );
	}

	/**
	 * Render one byline, without the link to its post
	 */
	public function test_template_tag_the_bylines_one_byline() {
		global $post;
		$b1 = Byline::create(
			array(
				'slug'  => 'b1',
				'display_name' => 'Byline 1',
			)
		);
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		Utils::set_post_bylines( $post_id, array( $b1 ) );
		$this->expectOutputString( 'Byline 1' );
		the_bylines();
	}

	/**
	 * Render two bylines, without the link to its post
	 */
	public function test_template_tag_the_bylines_two_byline() {
		global $post;
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
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		Utils::set_post_bylines( $post_id, array( $b2, $b1 ) );
		$this->expectOutputString( 'Byline 2 and Byline 1' );
		the_bylines();
	}

	/**
	 * Render three bylines, without the link to its post
	 */
	public function test_template_tag_the_bylines_three_byline() {
		global $post;
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
		$b3 = Byline::create(
			array(
				'slug'  => 'b3',
				'display_name' => 'Byline 3',
			)
		);
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		Utils::set_post_bylines( $post_id, array( $b2, $b3, $b1 ) );
		$this->expectOutputString( 'Byline 2, Byline 3, and Byline 1' );
		the_bylines();
	}

	/**
	 * Render four bylines, without the link to its post
	 */
	public function test_template_tag_the_bylines_four_byline() {
		global $post;
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
		$b3 = Byline::create(
			array(
				'slug'  => 'b3',
				'display_name' => 'Byline 3',
			)
		);
		$b4 = Byline::create(
			array(
				'slug'  => 'b4',
				'display_name' => 'Byline 4',
			)
		);
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		Utils::set_post_bylines( $post_id, array( $b2, $b4, $b3, $b1 ) );
		$this->expectOutputString( 'Byline 2, Byline 4, Byline 3, and Byline 1' );
		the_bylines();
	}

	/**
	 * Render two bylines, with the link to its post
	 */
	public function test_template_tag_the_bylines_posts_links_two_byline() {
		global $post;
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
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		Utils::set_post_bylines( $post_id, array( $b2, $b1 ) );
		$this->expectOutputString( '<a href="' . $b2->link . '" title="Posts by Byline 2" class="author url fn" rel="author">Byline 2</a> and <a href="' . $b1->link . '" title="Posts by Byline 1" class="author url fn" rel="author">Byline 1</a>' );
		the_bylines_posts_links();
	}

	/**
	 * Render one user, with the link to its post
	 */
	public function test_template_tag_the_bylines_posts_links_one_user() {
		global $post;
		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create(
			array(
				'post_author' => $user_id,
			)
		);
		$post = get_post( $post_id );
		$user = get_user_by( 'id', $user_id );
		$this->expectOutputString( '<a href="' . get_author_posts_url( $user_id ) . '" title="Posts by ' . $user->display_name . '" class="author url fn" rel="author">' . $user->display_name . '</a>' );
		the_bylines_posts_links();
	}

	/**
	 * Render two bylines, one with a custom URL and the other without
	 */
	public function test_template_tag_the_bylines_links_two_bylines() {
		global $post;
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
		update_term_meta( $b2->term_id, 'user_url', 'https://apple.com' );
		$post_id = $this->factory->post->create();
		$post = get_post( $post_id );
		Utils::set_post_bylines( $post_id, array( $b2, $b1 ) );
		$this->expectOutputString( '<a href="https://apple.com" title="Visit Byline 2&#8217;s website" rel="external">Byline 2</a> and Byline 1' );
		the_bylines_links();
	}

}
