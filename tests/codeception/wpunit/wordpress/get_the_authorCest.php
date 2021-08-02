<?php namespace wordpress;

use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Utils;

class get_the_authorCest
{
    public function tryToGetTheAuthorForAuthorMappedToUser(\WpunitTester $I)
    {
        $userID = $I->factory('a new user')->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $postId = $I->factory('a new post')->post->create();
        Utils::set_post_authors($postId, [$author]);

        $GLOBALS['post'] = get_post($postId);

        $I->assertEquals(
            $author->display_name,
            get_the_author(),
            'The author name is the same as the user\' display_name'
        );
    }

    public function tryToGetTheAuthorPostsLinkForGuestAuthor(\WpunitTester $I)
    {
        $authorSlug = sprintf('guest_author_%s', rand(1, PHP_INT_MAX));
        $authorName = strtoupper($authorSlug);

        $author = Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => $authorName,
            ]
        );

        $postId = $I->factory('a new post')->post->create();
        Utils::set_post_authors($postId, [$author]);

        $GLOBALS['post'] = get_post($postId);

        $I->assertEquals(
            $authorName,
            get_the_author()
        );
    }
}
