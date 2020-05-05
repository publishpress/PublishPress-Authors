<?php
/**
 * Test automatic integration with RSS.
 *
 * @package Bylines
 */

use Bylines\Objects\Byline;
use Bylines\Utils;

/**
 * Test automatic integration with RSS.
 */
class Test_Bylines_RSS extends Bylines_Testcase {

	/**
	 * Bylines should be added to RSS feed items
	 */
	public function test_bylines_rss_added_feed_items() {
		$b1 = Byline::create(
			array(
				'slug'  => 'b1',
				'display_name' => 'Byline 1',
			)
		);
		$post_id = $this->factory->post->create(
			array(
				'post_status' => 'publish',
			)
		);
		Utils::set_post_bylines( $post_id, array( $b1 ) );
		$this->expectOutputRegex( '#<dc:creator><!\[CDATA\[Byline 1\]\]></dc:creator>#' );
		// @codingStandardsIgnoreStart
		@$this->do_feed( '?feed=feed' );
		// @codingStandardsIgnoreEnd
	}

	/**
	 * Copy pasta of the RSS feed loader
	 */
	// @codingStandardsIgnoreStart
	private function do_feed( $feed ) {
		global $posts, $post, $wp_did_header, $wp_query, $wp_rewrite, $wpdb, $wp_version, $wp, $id, $comment, $user_ID;
		$this->go_to( $feed );

		$feed = get_query_var( 'feed' );
		// Remove the pad, if present.
		$feed = preg_replace( '/^_+/', '', $feed );
		if ( $feed == '' || $feed == 'feed' )
			$feed = get_default_feed();
		$hook = 'do_feed_' . $feed;
		if ( ! has_action( $hook ) )
			wp_die( __( 'ERROR: This is not a valid feed template.' ), '', array( 'response' => 404 ) );
		/**
		 * Fires once the given feed is loaded.
		 *
		 * The dynamic hook name, $hook, refers to the feed name.
		 *
		 * @since 2.1.0
		 *
		 * @param bool $is_comment_feed Whether the feed is a comment feed.
		 */
		if ( 'do_feed_rss2' === $hook ) {
			if ( is_array( $wp_query->query_vars ) ) {
				extract( $wp_query->query_vars, EXTR_SKIP );
			}
			include ABSPATH . WPINC . '/feed-rss2.php';
		} else {
			do_action( $hook, $wp_query->is_comment_feed );
		}
	}
	// @codingStandardsIgnoreEnd

}
