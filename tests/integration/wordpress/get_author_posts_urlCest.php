<?php namespace wordpress;

use MultipleAuthors\Classes\Objects\Author;

class get_author_posts_urlCest
{
    public function tryToGetAuthorPostsUrlForAuthorMappedToUserAndNoPermalinkStruct(\WpunitTester $I)
    {
        global $wp_rewrite;

        $wp_rewrite->author_structure = null;

        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $authorLink = get_author_posts_url($author->ID);

        $I->assertEquals(
            sprintf('http://%s/?author=%d', $_ENV['TEST_SITE_WP_DOMAIN'], $author->ID),
            $authorLink
        );
    }

    public function tryToGetAuthorPostsUrlForAuthorMappedToUserAndCustomPermalinkStruct(\WpunitTester $I)
    {
        global $wp_rewrite;

        $wp_rewrite->author_structure = 'author/%author%';

        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $authorLink = get_author_posts_url($author->ID);

        $I->assertEquals(
            sprintf('http://%s/author/%s', $_ENV['TEST_SITE_WP_DOMAIN'], $author->user_nicename),
            $authorLink
        );

        $wp_rewrite->author_structure = null;
    }

    public function tryToGetAuthorPostsUrlForGuestAuthorAndNoPermalinkStruct(\WpunitTester $I)
    {
        global $wp_rewrite;

        $wp_rewrite->author_structure = null;

        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));

        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $authorLink = get_author_posts_url($author->ID);

        $I->assertEquals(
            sprintf('http://%s/?author=%d', $_ENV['TEST_SITE_WP_DOMAIN'], $author->ID),
            $authorLink
        );
    }

    public function tryToGetAuthorPostsUrlForGuestAuthorAndCustomPermalinkStruct(\WpunitTester $I)
    {
        global $wp_rewrite;

        $wp_rewrite->author_structure = 'author/%author%';

        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));

        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $authorLink = get_author_posts_url($author->ID);

        $I->assertEquals(
            sprintf('http://%s/author/%s', $_ENV['TEST_SITE_WP_DOMAIN'], $author->user_nicename),
            $authorLink
        );
    }
}
