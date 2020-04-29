<?php namespace wordpress;

use MultipleAuthors\Classes\Objects\Author;

class get_author_posts_urlCest
{
    public function tryToGetAuthorPostsUrlForAuthorMappedToUserAndNoPermastruct(\WpunitTester $I)
    {
        global $wp_rewrite;

        $wp_rewrite->author_structure = null;

        $userID = $I->factory()->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $authorLink = get_author_posts_url($author->ID);

        $I->assertEquals(
            sprintf('http://localhost/?author=%d', $author->ID),
            $authorLink
        );
    }

    public function tryToGetAuthorPostsUrlForAuthorMappedToUserAndCustomPermastruct(\WpunitTester $I)
    {
        global $wp_rewrite;

        $wp_rewrite->author_structure = 'author/%author%';

        $userID = $I->factory()->user->create(['role' => 'author']);
        $author = Author::create_from_user($userID);

        $authorLink = get_author_posts_url($author->ID);

        $I->assertEquals(
            sprintf('http://localhost/author/%s', $author->user_nicename),
            $authorLink
        );
    }

    public function tryToGetAuthorPostsUrlForGuestAuthorAndNoPermastruct(\WpunitTester $I)
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
            sprintf('http://localhost/?author=%d', $author->ID),
            $authorLink
        );
    }

    public function tryToGetAuthorPostsUrlForGuestAuthorAndCustomPermastruct(\WpunitTester $I)
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
            sprintf('http://localhost/author/%s', $author->user_nicename),
            $authorLink
        );
    }
}
