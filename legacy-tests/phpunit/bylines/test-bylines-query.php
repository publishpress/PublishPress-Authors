<?php
/**
 * Class Test_Bylines_Query
 *
 * @package Bylines
 */

use Bylines\Objects\Byline;
use Bylines\Utils;

/**
 * Test functionality related to modifying the main query
 */
class Test_Bylines_Query extends Bylines_Testcase {

	/**
	 * Two bylines assigned to a post should each have the post appear in their
	 * archives.
	 */
	public function test_query_two_bylines_assigned_to_post_archive() {
		$this->user_id1 = $this->factory->user->create(
			array(
				'display_name'   => 'User 1',
			)
		);
		$this->user_id2 = $this->factory->user->create(
			array(
				'display_name'   => 'User 2',
			)
		);
		$this->post_id1 = $this->factory->post->create(
			array(
				'post_author' => $this->user_id1,
			)
		);
		$this->post_id2 = $this->factory->post->create(
			array(
				'post_author' => $this->user_id2,
			)
		);
		$byline1 = Byline::create_from_user( $this->user_id1 );
		$byline2 = Byline::create_from_user( $this->user_id2 );
		$bylines = array( $byline1, $byline2 );
		Utils::set_post_bylines( $this->post_id1, $bylines );
		Utils::set_post_bylines( $this->post_id2, $bylines );
		// User 1.
		$this->go_to( $byline1->link );
		$this->assertContains( 'author/', $GLOBALS['wp']->request );
		$this->assertEquals( 2, $GLOBALS['wp_query']->found_posts );
		$this->assertEquals( $byline1, get_queried_object() );
		$this->assertEquals( $byline1->term_id, get_queried_object_id() );
		// User 2.
		$this->go_to( $byline2->link );
		$this->assertContains( 'author/', $GLOBALS['wp']->request );
		$this->assertEquals( 2, $GLOBALS['wp_query']->found_posts );
		$this->assertEquals( $byline2, get_queried_object() );
		$this->assertEquals( $byline2->term_id, get_queried_object_id() );
	}

	/**
	 * An existing post_author should have all assigned posts, unless
	 * limiting filter is applied.
	 */
	public function test_query_existing_post_author_with_byline() {
		$this->user_id1 = $this->factory->user->create(
			array(
				'display_name'   => 'User 1',
			)
		);
		$this->post_id1 = $this->factory->post->create(
			array(
				'post_author' => $this->user_id1,
			)
		);
		$this->post_id2 = $this->factory->post->create(
			array(
				'post_author' => $this->user_id1,
			)
		);
		$this->go_to( get_author_posts_url( $this->user_id1 ) );
		$this->assertContains( 'author/', $GLOBALS['wp']->request );
		$this->assertEquals( 2, $GLOBALS['wp_query']->found_posts );
		// Create a new post with a byline.
		$this->post_id3 = $this->factory->post->create();
		$byline1 = Byline::create_from_user( $this->user_id1 );
		$bylines = array( $byline1 );
		Utils::set_post_bylines( $this->post_id3, $bylines );
		$this->go_to( get_author_posts_url( $this->user_id1 ) );
		$this->assertContains( 'author/', $GLOBALS['wp']->request );
		$this->assertEquals( 3, $GLOBALS['wp_query']->found_posts );
		// Apply the filter to disable MAX IF.
		add_filter( 'bylines_query_post_author', '__return_false' );
		$this->go_to( get_author_posts_url( $this->user_id1 ) );
		$this->assertContains( 'author/', $GLOBALS['wp']->request );
		$this->assertEquals( 1, $GLOBALS['wp_query']->found_posts );
	}

	/**
	 * Overload queried object for byline without a user attached and no posts.
	 */
	public function test_query_overload_byline_without_user_without_posts() {
		$byline1 = Byline::create(
			array(
				'display_name'   => 'Byline 1',
				'slug'           => 'byline-1',
			)
		);
		$this->go_to( $byline1->link );
		$this->assertContains( 'author/', $GLOBALS['wp']->request );
		$this->assertEquals( $byline1, get_queried_object() );
		$this->assertEquals( $byline1->term_id, get_queried_object_id() );
		$this->assertEquals( 0, $GLOBALS['wp_query']->found_posts );
	}

	/**
	 * Overload queried object for byline without a user attached and with posts.
	 */
	public function test_query_overload_byline_without_user_with_posts() {
		$byline = Byline::create(
			array(
				'display_name'   => 'Byline 2',
				'slug'           => 'byline-2',
			)
		);
		$this->post_id1 = $this->factory->post->create();
		$bylines = array( $byline->term_id );
		Utils::set_post_bylines( $this->post_id1, array( $byline ) );
		$this->go_to( $byline->link );
		$this->assertContains( 'author/', $GLOBALS['wp']->request );
		$this->assertEquals( $byline, get_queried_object() );
		$this->assertEquals( $byline->term_id, get_queried_object_id() );
		$this->assertEquals( 1, $GLOBALS['wp_query']->found_posts );
	}

	/**
	 * Overload queried object for user without posts and no byline
	 */
	public function test_query_overload_user_without_posts_without_byline() {
		$this->user_id1 = $this->factory->user->create(
			array(
				'display_name'   => 'User 1',
			)
		);
		$user1 = get_user_by( 'id', $this->user_id1 );
		$this->go_to( get_author_posts_url( $this->user_id1 ) );
		$this->assertContains( 'author/', $GLOBALS['wp']->request );
		$this->assertEquals( $user1, get_queried_object() );
		$this->assertEquals( 0, $GLOBALS['wp_query']->found_posts );
	}

	/**
	 * Overload queried object for user without posts and no byline
	 */
	public function test_query_overload_user_with_posts_without_byline() {
		$this->user_id2 = $this->factory->user->create(
			array(
				'display_name'   => 'User 1',
			)
		);
		$this->post_id2 = $this->factory->post->create(
			array(
				'post_author' => $this->user_id2,
			)
		);
		$user2 = get_user_by( 'id', $this->user_id2 );
		$this->go_to( get_author_posts_url( $this->user_id2 ) );
		$this->assertContains( 'author/', $GLOBALS['wp']->request );
		$this->assertEquals( $user2, get_queried_object() );
		$this->assertEquals( 1, $GLOBALS['wp_query']->found_posts );
	}

	/**
	 * Query isn't modified when a non-existant user id is passed
	 */
	public function test_query_not_modified_when_user_id_doesnt_exist() {
		$this->go_to( '?author=' . BYLINES_IMPOSSIBLY_HIGH_NUMBER );
		$this->assertTrue( ! isset( $GLOBALS['wp_query']->bylines_having_terms ) );
	}

	/**
	 * Redirect to byline slug if user has mapped byline with a slug that differs from user_nicename.
	 */
	public function test_user_with_mapped_byline_with_different_slug_should_redirect_to_byline_slug() {
		$this->user_id = $this->factory->user->create(
			array(
				'display_name'   => 'User',
			)
		);
		$this->post_id = $this->factory->post->create(
			array(
				'post_author' => $this->user_id,
			)
		);
		$byline = Byline::create_from_user( $this->user_id );
		Utils::set_post_bylines( $this->post_id, array( $byline ) );

		// Change byline slug to something else than user_nicename.
		wp_update_term( $byline->term_id, 'byline', array(
			'slug' => 'nondefaultbylineslug',
		) );

		$this->go_to( get_author_posts_url( $this->user_id ) );

		// $this->final_redirect_location is set in Bylines_Testcase::filter_wp_redirect
		$this->assertEquals( $this->final_redirect_location, $byline->link );
	}

	/**
	 * Request to an author archive where the user has a mapped byline with the
	 * same slug as the user_nicename should not trigger a redirect.
	 */
	public function test_user_with_mapped_byline_with_same_slug_should_not_redirect() {
		$this->user_id = $this->factory->user->create(
			array(
				'display_name'   => 'User',
			)
		);
		$this->post_id = $this->factory->post->create(
			array(
				'post_author' => $this->user_id,
			)
		);
		$byline = Byline::create_from_user( $this->user_id );
		Utils::set_post_bylines( $this->post_id, array( $byline ) );

		$this->go_to( get_author_posts_url( $this->user_id ) );

		// $this->final_redirect_location is set in Bylines_Testcase::filter_wp_redirect
		$this->assertAttributeEmpty( 'final_redirect_location', $this );
	}

	/**
	 * Request to an author archive where the user has no mapped byline
	 * should not trigger a redirect.
	 */
	public function test_user_without_mapped_byline_should_not_redirect() {
		$this->user_id = $this->factory->user->create(
			array(
				'display_name'   => 'User',
			)
		);
		$this->post_id = $this->factory->post->create(
			array(
				'post_author' => $this->user_id,
			)
		);

		$this->go_to( get_author_posts_url( $this->user_id ) );

		// $this->final_redirect_location is set in Bylines_Testcase::filter_wp_redirect
		$this->assertAttributeEmpty( 'final_redirect_location', $this );
	}

}
