<?php namespace wordpress;

use MultipleAuthors\Classes\Objects\Author;

class get_the_authorCest
{
    public function tryToGetTheAuthorForAuthorMappedToUser(\WpunitTester $I)
    {
        global $authordata;

        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $authordata = $author->get_user_object();

        $I->assertEquals(
            $authordata->display_name,
            get_the_author(),
            'The author name is the same as the user\' display_name'
        );
    }

    public function tryToGetTheAuthorPostsLinkForGuestAuthor(\WpunitTester $I)
    {
        global $authordata;

        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));
        $authorName = strtoupper($authorSlug);

        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => $authorName,
            ]
        );

        $authordata = $author;

        $I->assertEquals(
            $authorName,
            get_the_author()
        );
    }
}
