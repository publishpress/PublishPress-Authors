<?php

namespace core\Classes;

use MultipleAuthors\Classes\Installer;
use MultipleAuthors\Classes\Objects\Author;
use WpunitTester;

class InstallerCest
{
    public function _before(WpunitTester $I)
    {
        $I->resetTheDatabase();
    }

    public function tryToCreateAuthorTermsForLegacyCoreAuthors(WpunitTester $I)
    {
        $postIds     = $I->havePostsWithDifferentAuthors(10);
        $postAuthors = $I->getCorePostAuthorFromPosts($postIds);

        Installer::createAuthorTermsForLegacyCoreAuthors();

        $I->assertUsersHaveAuthorTerm($postAuthors);
    }

    public function tryToCreateAuthorTermsForLegacyCoreAuthorComparingUserData(WpunitTester $I)
    {
        $postIds     = $I->havePostsWithDifferentAuthors(1);
        $postAuthors = $I->getCorePostAuthorFromPosts($postIds);

        $user = get_user_by('ID', $postAuthors[0]);

        $user->description = 'I create bonsais!';
        $user->first_name  = 'Miyagi';
        $user->last_name   = 'Morita';
        $user->user_url    = 'https://miyagibonsais.com';

        wp_update_user($user);

        Installer::createAuthorTermsForLegacyCoreAuthors();

        $author = Author::get_by_user_id($postAuthors[0]);

        $I->assertUsersHaveAuthorTerm($postAuthors);
        $I->assertEquals(
            $user->ID,
            $author->user_id,
            'The user ID should be the same in the user and author'
        );
        $I->assertEquals(
            $user->display_name,
            $author->display_name,
            'The display_name should be the same in the user and author'
        );
        $I->assertEquals(
            $user->user_nicename,
            $author->slug,
            'The user_nicename and author slug should be the same'
        );
        $I->assertEquals(
            $user->description,
            $author->description,
            'The description should be the same in the user and author'
        );
        $I->assertEquals(
            $user->first_name,
            $author->first_name,
            'The first_name should be the same in the user and author'
        );
        $I->assertEquals(
            $user->last_name,
            $author->last_name,
            'The last_name should be the same in the user and author'
        );
        $I->assertEquals(
            $user->user_email,
            $author->user_email,
            'The user_email should be the same in the user and author'
        );
        $I->assertEquals(
            $user->user_url,
            $author->user_url,
            'The user_url should be the same in the user and author'
        );
    }

    public function tryToCreateAuthorTermsForPostsWithLegacyCoreAuthors(WpunitTester $I)
    {
        $postIds = $I->havePostsWithDifferentAuthors(10);

        Installer::createAuthorTermsForPostsWithLegacyCoreAuthors();

        $I->assertPostsHaveAuthorTerms($postIds);
    }
}
