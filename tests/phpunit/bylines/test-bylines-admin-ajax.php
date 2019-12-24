<?php
/**
 * Class Test_Bylines_Admin_Ajax
 *
 * @package Bylines
 */

use Bylines\Admin_Ajax;
use Bylines\Objects\Byline;

/**
 * Test functionality related to admin ajax interactions
 */
class Test_Bylines_Admin_Ajax extends Bylines_Testcase {

	/**
	 * Searching for potential bylines.
	 */
	public function test_ajax_search_possible_bylines() {
		$user_id1 = $this->factory->user->create(
			array(
				'display_name' => 'A User 1',
			)
		);
		$user_id2 = $this->factory->user->create(
			array(
				'display_name' => 'B User 2',
			)
		);
		$user_id3 = $this->factory->user->create(
			array(
				'display_name' => 'C User 3',
			)
		);
		$user_id4 = $this->factory->user->create(
			array(
				'display_name' => 'D User 4',
			)
		);
		$byline1 = Byline::create_from_user( $user_id3 );
		// Empty search should return all users.
		$bylines = Admin_Ajax::get_possible_bylines_for_search( '' );
		$this->assertEquals(
			array(
				'A User 1',
				'B User 2',
				'C User 3',
				'D User 4',
				'admin',
			), wp_list_pluck( $bylines, 'text' )
		);
		// Search should default to wildcard.
		$bylines = Admin_Ajax::get_possible_bylines_for_search( 'use' );
		$this->assertEquals(
			array(
				'A User 1',
				'B User 2',
				'C User 3',
				'D User 4',
			), wp_list_pluck( $bylines, 'text' )
		);
		$bylines = Admin_Ajax::get_possible_bylines_for_search( 'C U' );
		$this->assertEquals(
			array(
				'C User 3',
			), wp_list_pluck( $bylines, 'text' )
		);
	}

	/**
	 * When a byline is assigned to a post, its user should not appear.
	 */
	public function test_ajax_search_exclude_user_when_byline_assigned() {
		$user_id1 = $this->factory->user->create(
			array(
				'display_name' => 'A User 1',
			)
		);
		$byline1 = Byline::create_from_user( $user_id1 );
		$user_id2 = $this->factory->user->create(
			array(
				'display_name' => 'B User 2',
			)
		);
		// Default search should only include 'B User 2'.
		$bylines = Admin_Ajax::get_possible_bylines_for_search( '', array( $byline1->term_id ) );
		$this->assertEquals(
			array(
				'B User 2',
				'admin',
			), wp_list_pluck( $bylines, 'text' )
		);
	}

}
