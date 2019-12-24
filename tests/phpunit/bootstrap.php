<?php
/**
 * PHPUnit bootstrap file
 *
 * @package publishpress-authors
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

// An impossibly high number.
define( 'BYLINES_IMPOSSIBLY_HIGH_NUMBER', 999999 );

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname(dirname(dirname(__FILE__))) . '/publishpress-authors.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

require dirname( __FILE__ ) . '/bylines/class-bylines-testcase.php';
