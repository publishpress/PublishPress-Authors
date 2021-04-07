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

    public function tryToCreateAuthorTermsForPostsWithLegacyCoreAuthors(WpunitTester $I)
    {
        $postIds     = $I->havePostsWithDifferentAuthors(10);
        $postAuthors = $I->getCorePostAuthorFromPosts($postIds);

        Installer::createAuthorTermsForLegacyCoreAuthors();

        $I->assertUsersHaveAuthorTerm($postAuthors);
    }
}
