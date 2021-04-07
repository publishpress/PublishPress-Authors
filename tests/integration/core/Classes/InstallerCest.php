<?php

namespace core\Classes;

use MultipleAuthors\Classes\Installer;
use WpunitTester;

class InstallerCest
{
    public function _before(WpunitTester $I)
    {
    }

    public function tryToCreateAuthorTermsForLegacyCoreAuthors(WpunitTester $I)
    {
        $postIds     = $I->havePostsWithDifferentAuthors(10);
        $postAuthors = $I->getCorePostAuthorFromPosts($postIds);

        Installer::createAuthorTermsForLegacyCoreAuthors();

        $I->assertUsersHaveAuthorTerm($postAuthors);
    }

    public function tryToCreateAuthorTermsForPostsWithLegacyCoreAuthors(WpunitTester $I)
    {
    }
}
