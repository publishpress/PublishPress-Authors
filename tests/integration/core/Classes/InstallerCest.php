<?php

namespace core\Classes;

use MultipleAuthors\Classes\Installer;
use MultipleAuthors\Classes\Objects\Author;
use WpunitTester;

class InstallerCest
{
    public function _before(WpunitTester $I)
    {
    }

    public function tryToConvertPostAuthorsIntoTermsForPostsWithAuthorsWithNoTerms(WpunitTester $I)
    {
        $postIds     = $I->havePostsWithDifferentAuthors(10);
        $postAuthors = $I->getCorePostAuthorFromPosts($postIds);

        Installer::createAuthorTermsForLegacyCoreAuthors();

        $I->assertUsersHaveAuthorTerm($postAuthors);
    }

    public function tryToConvertPostAuthorsIntoTermsForPostsWithAuthorsWithTermsAndWithoutTerms(WpunitTester $I)
    {
        $postIds     = $I->havePostsWithDifferentAuthors(10);
        $postAuthors = $I->getCorePostAuthorFromPosts($postIds);

        Author::create_from_user($postAuthors[0]);
        Author::create_from_user($postAuthors[1]);
        Author::create_from_user($postAuthors[5]);

        Installer::createAuthorTermsForLegacyCoreAuthors();

        $I->assertUsersHaveAuthorTerm($postAuthors);
    }
}
