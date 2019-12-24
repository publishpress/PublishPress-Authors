<?php
/**
 * Test automatic integration with themes.
 *
 * @package Bylines
 */

use Bylines\Objects\Byline;
use Bylines\Utils;

/**
 * Test automatic integration with themes.
 */
class Test_Bylines_Themes extends Bylines_Testcase {

	/**
	 * Filter get_the_archive_title() to use byline on author archives
	 */
	public function test_filter_the_archive_title_byline_author_archives() {
		$user_id = $this->factory->user->create(
			array(
				'display_name'   => 'User 1',
			)
		);
		$byline = Byline::create(
			array(
				'display_name'   => 'Byline 1',
				'slug'           => 'byline-1',
			)
		);
		// Post has $user_id as post_author, but a set byline.
		$post_id = $this->factory->post->create(
			array(
				'post_author'    => $user_id,
			)
		);
		Utils::set_post_bylines( $post_id, array( $byline ) );
		$this->go_to( '?author_name=' . $byline->slug );
		$this->assertEquals( 1, $GLOBALS['wp_query']->found_posts );
		$this->assertEquals( array( $post_id ), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
		$this->expectOutputString( 'Author: <span class="vcard">Byline 1</span>' );
		the_archive_title();
	}

	/**
	 * Filter get_the_archive_description() to use byline on author archives
	 */
	public function test_filter_the_archive_description_byline_author_archives() {
		$user_id = $this->factory->user->create(
			array(
				'display_name'   => 'User 1',
				'description'    => 'User description 1',
			)
		);
		$byline = Byline::create(
			array(
				'display_name'   => 'Byline 1',
				'slug'           => 'byline-1',
			)
		);
		update_term_meta( $byline->term_id, 'description', 'Byline description 1' );
		// Post has $user_id as post_author, but a set byline.
		$post_id = $this->factory->post->create(
			array(
				'post_author'    => $user_id,
			)
		);
		Utils::set_post_bylines( $post_id, array( $byline ) );
		$this->go_to( '?author_name=' . $byline->slug );
		$this->assertEquals( 1, $GLOBALS['wp_query']->found_posts );
		$this->assertEquals( array( $post_id ), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
		$this->expectOutputString( 'Byline description 1' );
		the_archive_description();
	}

}
