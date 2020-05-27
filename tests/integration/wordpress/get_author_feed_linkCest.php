<?php namespace wordpress;

use MultipleAuthors\Classes\Objects\Author;

class get_author_feed_linkCest
{
    public function tryToGetTheFeedLinkForAuthorMappedToUser(\WpunitTester $I)
    {
        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $feedLink = get_author_feed_link($author->ID);

        $I->assertEquals(
            sprintf('http://%s/?feed=rss2&amp;author=%d', $_ENV['TEST_SITE_WP_DOMAIN'], $author->ID),
            $feedLink
        );
    }

    public function tryToGetTheFeedLinkForGuestAuthor(\WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));

        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $feedLink = get_author_feed_link($author->ID);

        $I->assertEquals(
            sprintf('http://%s/?feed=rss2&amp;author=%d', $_ENV['TEST_SITE_WP_DOMAIN'], $author->ID),
            $feedLink
        );
    }
}
