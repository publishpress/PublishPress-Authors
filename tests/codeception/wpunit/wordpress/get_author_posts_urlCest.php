<?php
namespace wordpress;

use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Utils;
use WpunitTester;

class get_author_posts_urlCest
{
    public function tryToGetAuthorPostsUrlForAuthorMappedToUserAndNoPermalinkStruct(WpunitTester $I)
    {
        $I->setPermalinkStructure('');

        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $authorLink = get_author_posts_url($author->ID);

        $I->assertEquals(
            sprintf('http://%s/?author=%d', $_ENV['TEST_SITE_WP_DOMAIN'], $author->ID),
            $authorLink
        );
    }

    public function tryToGetAuthorPostsUrlForAuthorMappedToUserAndCustomPermalinkStruct(WpunitTester $I)
    {
        $I->setPermalinkStructure('/%postname%/');

        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $authorLink = get_author_posts_url($author->ID);

        $I->assertEquals(
            sprintf('http://%s/author/%s/', $_ENV['TEST_SITE_WP_DOMAIN'], $author->user_nicename),
            $authorLink
        );
    }

    public function tryToGetAuthorPostsUrlForGuestAuthorAndNoPermalinkStruct(WpunitTester $I)
    {
        $I->setPermalinkStructure('');

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

    public function tryToGetAuthorPostsUrlForGuestAuthorAndCustomPermalinkStruct(WpunitTester $I)
    {
        $I->setPermalinkStructure('/%postname%/');

        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));

        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $authorLink = get_author_posts_url($author->ID);

        $I->assertEquals(
            sprintf('http://%s/author/%s/', $_ENV['TEST_SITE_WP_DOMAIN'], $author->user_nicename),
            $authorLink
        );
    }

    public function tryToGetAuthorPostsUrlForGuestAuthorAndCustomPermalinkStructAndIdFromGlobal(WpunitTester $I)
    {
        $I->setPermalinkStructure('/%postname%/');

        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));
        $userId     = $I->factory('a new user')->user->create(['role' => 'author']);

        $postId          = $I->factory('a new post')->post->create();
        $post            = get_post($postId);
        $GLOBALS['post'] = $post;

        $guestAuthor = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        Utils::set_post_authors($postId, [$guestAuthor]);

        // We need to initialize authordata otherwise the link is always empty.
        global $authordata;
        $authordata = get_user_by('ID', $userId);

        $authorLink = get_author_posts_url(- $guestAuthor->term_id);

        $I->assertEquals(
            sprintf('http://%s/author/%s/', $_ENV['TEST_SITE_WP_DOMAIN'], $guestAuthor->user_nicename),
            $authorLink
        );
    }
}
