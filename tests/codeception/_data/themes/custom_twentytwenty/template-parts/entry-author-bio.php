<?php
/**
 * The template for displaying Author info
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */

if ( (bool) get_the_author_meta( 'description' ) && (bool) get_theme_mod( 'show_author_bio', true ) ) : ?>
<div class="author-bio">
	<div class="author-title-wrapper">
		<div class="author-avatar vcard">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 160 ); ?>
		</div>
		<h2 class="author-title heading-size-4">
			<?php
			printf(
				/* translators: %s: Author name. */
				__( 'By %s', 'twentytwenty' ),
				esc_html( get_the_author() )
			);
			?>
		</h2>
	</div><!-- .author-name -->
	<div class="author-description">
		<?php echo wp_kses_post( wpautop( get_the_author_meta( 'description' ) ) ); ?>
		<a class="author-link" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" rel="author">
			<?php _e( 'View Archive <span aria-hidden="true">&rarr;</span>', 'twentytwenty' ); ?>
		</a>
	</div><!-- .author-description -->
</div><!-- .author-bio -->
<?php endif; ?>

<!-- Author data for testing WP functions -->
<div id="ppa_tests">
    <div id="ppa_tests_author_id"><?php echo get_the_author_meta('ID'); ?></div>
    <div id="ppa_tests_author_display_name"><?php echo get_the_author_meta('display_name'); ?></div>
    <div id="ppa_tests_author_first_name"><?php echo get_the_author_meta('first_name'); ?></div>
    <div id="ppa_tests_author_last_name"><?php echo get_the_author_meta('last_name'); ?></div>
    <div id="ppa_tests_author_headline"><?php echo get_the_author_meta('headline'); ?></div>
    <div id="ppa_tests_author_description"><?php echo get_the_author_meta('description'); ?></div>
    <div id="ppa_tests_author_nickname"><?php echo get_the_author_meta('nickname'); ?></div>
    <div id="ppa_tests_author_aim"><?php echo get_the_author_meta('aim'); ?></div>
    <div id="ppa_tests_author_jabber"><?php echo get_the_author_meta('jabber'); ?></div>
    <div id="ppa_tests_author_yim"><?php echo get_the_author_meta('yim'); ?></div>
    <div id="ppa_tests_author_facebook"><?php echo get_the_author_meta('facebook'); ?></div>
    <div id="ppa_tests_author_twitter"><?php echo get_the_author_meta('twitter'); ?></div>
    <div id="ppa_tests_author_instagram"><?php echo get_the_author_meta('instagram'); ?></div>
    <div id="ppa_tests_author_user_description"><?php echo get_the_author_meta('user_description'); ?></div>
    <div id="ppa_tests_author_user_email"><?php echo get_the_author_meta('user_email'); ?></div>
    <div id="ppa_tests_author_user_firstname"><?php echo get_the_author_meta('user_firstname'); ?></div>
    <div id="ppa_tests_author_user_lastname"><?php echo get_the_author_meta('user_lastname'); ?></div>
    <div id="ppa_tests_author_user_nicename"><?php echo get_the_author_meta('user_nicename'); ?></div>
    <div id="ppa_tests_author_user_url"><?php echo get_the_author_meta('user_url'); ?></div>
</div>

