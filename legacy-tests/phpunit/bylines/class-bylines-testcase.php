<?php
/**
 * Class Bylines_Testcase
 *
 * @package Bylines
 */

/**
 * Test suite modifications for Bylines
 */
class Bylines_Testcase extends WP_UnitTestCase {

	/**
	 * If a redirect occurs this will be set to the redirect location.
	 *
	 * @var string
	 * @access protected
	 */
	protected $final_redirect_location = '';

	/**
	 * Set up the tests
	 */
	public function setUp() {
		$this->setup_permalink_structure();
		parent::setUp();
		add_filter( 'wp_redirect', array( $this, 'filter_wp_redirect' ), 10, 2 );
	}

	/**
	 * Tear down the tests
	 */
	public function tearDown() {
		remove_filter( 'wp_redirect', array( $this, 'filter_wp_redirect' ), 10, 2 );
		parent::tearDown();
	}

	/**
	 * Set up sexy permalink structure
	 */
	protected function setup_permalink_structure() {
		global $wp_rewrite;

		$structure = '/%year%/%monthnum%/%day%/%postname%/';

		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( $structure );

		create_initial_taxonomies();

		$wp_rewrite->flush_rules();
	}

	/**
	 * Capture any redirects
	 *
	 * @param string  $location The path to redirect to.
	 * @param integer $status   Status code to use.
	 */
	public function filter_wp_redirect( $location, $status ) {
		$this->final_redirect_location = $location;
		$this->go_to( $location );

		// Prevent the redirect from happening.
		return false;
	}

}
