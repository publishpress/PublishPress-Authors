<?php namespace wordpress;

use MultipleAuthors\Classes\Objects\Author;
use WpunitTester;

class get_author_feed_linkCest
{
    public function tryToGetTheFeedLinkForAuthorMappedToUser(WpunitTester $I)
    {
        $I->setPermalinkStructure('/%postname%/');

        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $feedLink = get_author_feed_link($author->ID);

        $I->assertEquals(
            sprintf('http://%s/author/%s/feed/', $_ENV['TEST_SITE_WP_DOMAIN'], $author->slug),
            $feedLink
        );
    }

    public function tryToGetTheFeedLinkForGuestAuthor(WpunitTester $I)
    {
        $I->setPermalinkStructure('/%postname%/');

        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));

        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => strtoupper($authorSlug),
            ]
        );

        $feedLink = get_author_feed_link($author->ID);

        $I->assertEquals(
            sprintf('http://%s/author/%s/feed/', $_ENV['TEST_SITE_WP_DOMAIN'], $author->slug),
            $feedLink
        );
    }
}
