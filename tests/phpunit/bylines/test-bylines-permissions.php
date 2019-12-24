<?php
/**
 * Test functionality related to byline editing permissions
 *
 * @package Bylines
 */

use Bylines\Objects\Byline;
use Bylines\Utils;

/**
 * Class Test_Bylines_Permissions
 */
class Test_Bylines_Permissions extends Bylines_Testcase {

	/**
	 * Contributors shouldn't be able to assign or edit bylines
	 */
	public function test_permissions_contributors_cannot_assign_bylines() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'contributor',
			)
		);
		wp_set_current_user( $user_id );
		$taxonomy = get_taxonomy( 'byline' );
		$this->assertFalse( current_user_can( $taxonomy->cap->manage_terms ) );
		$this->assertFalse( current_user_can( $taxonomy->cap->edit_terms ) );
		$this->assertFalse( current_user_can( $taxonomy->cap->delete_terms ) );
		$this->assertFalse( current_user_can( $taxonomy->cap->assign_terms ) );
	}

	/**
	 * Editors should be able to assign bylines, but not edit because they don't have 'list_users'
	 */
	public function test_permissions_editors_can_assign_bylines_but_not_edit() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'editor',
			)
		);
		wp_set_current_user( $user_id );
		$taxonomy = get_taxonomy( 'byline' );
		$this->assertFalse( current_user_can( $taxonomy->cap->manage_terms ) );
		$this->assertFalse( current_user_can( $taxonomy->cap->edit_terms ) );
		$this->assertFalse( current_user_can( $taxonomy->cap->delete_terms ) );
		$this->assertTrue( current_user_can( $taxonomy->cap->assign_terms ) );
	}

	/**
	 * Administrators should be able to edit bylines, because they have 'list_users'
	 */
	public function test_permissions_administrators_can_edit_bylines() {
		$user_id = $this->factory->user->create(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_current_user( $user_id );
		$taxonomy = get_taxonomy( 'byline' );
		$this->assertTrue( current_user_can( $taxonomy->cap->manage_terms ) );
		$this->assertTrue( current_user_can( $taxonomy->cap->edit_terms ) );
		$this->assertTrue( current_user_can( $taxonomy->cap->delete_terms ) );
		$this->assertTrue( current_user_can( $taxonomy->cap->assign_terms ) );
	}

}
