<?php

use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Utils;

class _post_pageCest
{
    public function _before(FrontendTester $I)
    {
        $I->setPermalinkStructure('/%postname%/');
        $I->switchTheme('twentytwenty');
    }

    public function bylineShowsAuthorForAuthorMappedToUser(FrontendTester $I)
    {
        $postId = $I->factory('a new post')->post->create();
        $userId = $I->factory('a new user')->user->create(['role' => 'author']);

        $author = Author::create_from_user($userId);

        Utils::set_post_authors($postId, [$author]);

        $I->amOnPage($I->getRelativePostPermalink($postId));
        $I->see("By $author->display_name", '.post-author .meta-text');
    }

    public function bylineShowsAuthorForGuestAuthor(FrontendTester $I)
    {
        $postId = $I->factory('a new post')->post->create();

        $author = Author::create(
            [
                'display_name' => 'FFPP Author 1',
                'slug'         => 'ffpp_author_1',
            ]
        );

        Utils::set_post_authors($postId, [$author]);

        $I->amOnPage($I->getRelativePostPermalink($postId));
        $I->see("By $author->display_name", '.post-author .meta-text');
    }

    public function bylineShowsAuthorForPostWithMultipleAuthorsMappedToUser(FrontendTester $I)
    {
        $postId  = $I->factory('a new post')->post->create();
        $userId1 = $I->factory('a new user')->user->create(['role' => 'author']);
        $userId2 = $I->factory('a new user')->user->create(['role' => 'author']);

        $post = get_post($postId);

        $author1 = Author::create_from_user($userId1);
        $author2 = Author::create_from_user($userId2);

        Utils::set_post_authors($postId, [$author1, $author2]);

        $I->amOnPage($I->getRelativePostPermalink($postId));
        $I->see("By $author1->display_name", '.post-author .meta-text');
        $I->dontSee($author2->display_name, '.post-author .meta-text');
    }

    public function bylineShowsAuthorForMultipleGuestAuthors(FrontendTester $I)
    {
        $postId = $I->factory('a new post')->post->create();

        $author1 = Author::create(
            [
                'display_name' => 'FFPP Author 2',
                'slug'         => 'ffpp_author_2',
            ]
        );

        $author2 = Author::create(
            [
                'display_name' => 'FFPP Author 3',
                'slug'         => 'ffpp_author_3',
            ]
        );

        Utils::set_post_authors($postId, [$author1, $author2]);

        $I->amOnPage($I->getRelativePostPermalink($postId));
        $I->see("By $author1->display_name", '.post-author .meta-text');
        $I->dontSee("By $author2->display_name", '.post-author .meta-text');
        $I->makeHtmlSnapshot('hollymolly');
    }

    public function getAuthorMetaForAuthorMappedToUser(FrontendTester $I)
    {
        $displayName = 'FFPP Author 5';
        $slug        = 'ffpp_author_5';
        $firstName   = 'FFPP Author';
        $lastName    = 'Five';
        $description = 'ffpp_author_5_description';
        $nickname    = 'ffpp_author_nickname';
        $aim         = 'ffpp_author_5_aim';
        $jabber      = 'ffpp_author_5_jabber';
        $email       = 'ffpp_author_5@example.com';
        $url         = 'http://testing.example.com';
        $yim         = 'ffpp_author_5_yim';
        $facebook    = 'ffpp_author_5_facebook';
        $twitter     = 'ffpp_author_5_twitter';
        $instagram   = 'ffpp_author_5_instagram';

        $postId = $I->factory('a new post')->post->create();
        $userId = $I->factory('a new user')->user->create(
            [
                'role'          => 'author',
                'display_name'  => $displayName,
                'user_nicename' => $slug
            ]
        );


        $author = Author::create_from_user($userId);

        $author->update_meta('first_name', $firstName);
        $author->update_meta('last_name', $lastName);
        $author->update_meta('description', $description);
        $author->update_meta('nickname', $nickname);
        $author->update_meta('aim', $aim);
        $author->update_meta('jabber', $jabber);
        $author->update_meta('user_email', $email);
        $author->update_meta('user_url', $url);
        $author->update_meta('yim', $yim);
        $author->update_meta('facebook', $facebook);
        $author->update_meta('twitter', $twitter);
        $author->update_meta('instagram', $instagram);

        Utils::set_post_authors($postId, [$author]);

        $I->amOnPage($I->getRelativePostPermalink($postId));
        $I->see($author->ID, '#ppa_tests_author_id', 'Checking the ID');
        $I->see($displayName, '#ppa_tests_author_display_name', 'Checking the display_name');
        $I->see($firstName, '#ppa_tests_author_first_name', 'Checking the first_name');
        $I->see($lastName, '#ppa_tests_author_last_name', 'Checking the last_name');
        $I->see($displayName, '#ppa_tests_author_headline', 'Checking the headline');
        $I->see($description, '#ppa_tests_author_description', 'Checking the description');
        $I->see($nickname, '#ppa_tests_author_nickname', 'Checking the nickname');
        $I->see($aim, '#ppa_tests_author_aim');
        $I->see($jabber, '#ppa_tests_author_jabber');
        $I->see($yim, '#ppa_tests_author_yim');
        $I->see($description, '#ppa_tests_author_user_description');
        $I->see($email, '#ppa_tests_author_user_email');
        $I->see($firstName, '#ppa_tests_author_user_firstname');
        $I->see($lastName, '#ppa_tests_author_user_lastname');
        $I->see($slug, '#ppa_tests_author_user_nicename');
        $I->see($url, '#ppa_tests_author_user_url');
        $I->see($facebook, '#ppa_tests_author_facebook');
        $I->see($twitter, '#ppa_tests_author_twitter');
        $I->see($instagram, '#ppa_tests_author_instagram');
    }

    public function getAuthorMetaAuthorForGuestAuthor(FrontendTester $I)
    {
        $postId = $I->factory('a new post')->post->create();

        $displayName = 'FFPP Author 4';
        $slug        = 'ffpp_author_4';
        $firstName   = 'FFPP Author';
        $lastName    = 'Four';
        $description = 'ffpp_author_4_description';
        $nickname    = 'ffpp_author_nickname';
        $aim         = 'ffpp_author_4_aim';
        $jabber      = 'ffpp_author_4_jabber';
        $email       = 'ffpp_author_4@example.com';
        $url         = 'http://testing.example.com';
        $yim         = 'ffpp_author_4_yim';
        $facebook    = 'ffpp_author_4_facebook';
        $twitter     = 'ffpp_author_4_twitter';
        $instagram   = 'ffpp_author_4_instagram';


        $author = Author::create(
            [
                'display_name' => $displayName,
                'slug'         => $slug,
            ]
        );

        $author->update_meta('first_name', $firstName);
        $author->update_meta('last_name', $lastName);
        $author->update_meta('description', $description);
        $author->update_meta('nickname', $nickname);
        $author->update_meta('aim', $aim);
        $author->update_meta('jabber', $jabber);
        $author->update_meta('user_email', $email);
        $author->update_meta('user_url', $url);
        $author->update_meta('yim', $yim);
        $author->update_meta('facebook', $facebook);
        $author->update_meta('twitter', $twitter);
        $author->update_meta('instagram', $instagram);

        Utils::set_post_authors($postId, [$author]);

        $I->amOnPage($I->getRelativePostPermalink($postId));

        $I->see($author->ID, '#ppa_tests_author_id', 'Checking the ID');
        $I->see($displayName, '#ppa_tests_author_display_name', 'Checking the display_name');
        $I->see($firstName, '#ppa_tests_author_first_name', 'Checking the first_name');
        $I->see($lastName, '#ppa_tests_author_last_name', 'Checking the last_name');
        $I->see($displayName, '#ppa_tests_author_headline', 'Checking the headline');
        $I->see($description, '#ppa_tests_author_description', 'Checking the description');
        $I->see($nickname, '#ppa_tests_author_nickname', 'Checking the nickname');
        $I->see($aim, '#ppa_tests_author_aim');
        $I->see($jabber, '#ppa_tests_author_jabber');
        $I->see($yim, '#ppa_tests_author_yim');
        $I->see($description, '#ppa_tests_author_user_description');
        $I->see($email, '#ppa_tests_author_user_email');
        $I->see($firstName, '#ppa_tests_author_user_firstname');
        $I->see($lastName, '#ppa_tests_author_user_lastname');
        $I->see($slug, '#ppa_tests_author_user_nicename');
        $I->see($url, '#ppa_tests_author_user_url');
        $I->see($facebook, '#ppa_tests_author_facebook');
        $I->see($twitter, '#ppa_tests_author_twitter');
        $I->see($instagram, '#ppa_tests_author_instagram');
    }
}
