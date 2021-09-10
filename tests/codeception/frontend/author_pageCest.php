<?php

use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Utils;

class _author_pageCest
{
    public function _before(FrontendTester $I)
    {
        $I->setPermalinkStructure('/%postname%/');
        $I->switchTheme('twentytwenty');
    }

    public function pageTitleShowsAuthorForAuthorMappedToUser(FrontendTester $I)
    {
        $postId = $I->factory('a new post')->post->create();
        $userId = $I->factory('a new user')->user->create(['role' => 'author']);

        $post = get_post($postId);

        $author = Author::create_from_user($userId);

        Utils::set_post_authors($postId, [$author]);

        $I->amOnPage($I->getRelativeAuthorLink($author));
        $I->see($author->display_name, 'h1.archive-title .vcard');
    }

    public function pageTitleShowsAuthorForGuestAuthor(FrontendTester $I)
    {
        $postId = $I->factory('a new post')->post->create();

        $author = Author::create(
            [
                'display_name' => 'FFAP Author 1',
                'slug'         => 'ffap_author_1',
            ]
        );

        Utils::set_post_authors($postId, [$author]);

        $I->amOnPage($I->getRelativeAuthorLink($author));
        $I->see($author->display_name, 'h1.archive-title .vcard');
    }

    public function articleBylineShowsAuthorForAuthorMappedToUser(FrontendTester $I)
    {
        $postId = $I->factory('a new post')->post->create();
        $userId = $I->factory('a new user')->user->create(['role' => 'author']);

        $post = get_post($postId);

        $author = Author::create_from_user($userId);

        Utils::set_post_authors($postId, [$author]);

        $I->amOnPage($I->getRelativeAuthorLink($author));
        $I->see("By $author->display_name", "#post-$postId .post-author .meta-text");
    }

    public function articleBylineShowsAuthorForGuestAuthor(FrontendTester $I)
    {
        $postId = $I->factory('a new post')->post->create();

        $author = Author::create(
            [
                'display_name' => 'FFAP Author 2',
                'slug'         => 'ffap_author_2',
            ]
        );

        Utils::set_post_authors($postId, [$author]);

        $I->amOnPage($I->getRelativeAuthorLink($author));
        $I->see("By $author->display_name", "#post-$postId .post-author .meta-text");
    }
}
