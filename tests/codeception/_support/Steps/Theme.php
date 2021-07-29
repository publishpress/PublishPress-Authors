<?php

namespace Steps;


use Codeception\Util\Locator;
use MultipleAuthors\Classes\Objects\Author;

trait Theme
{
    /**
     * @Then I see the author name :authorName in the byline
     */
    public function iSeeAuthorNameInTheByline($authorName)
    {
        $this->see($authorName, '.posted-by .byline a');
    }

    /**
     * @Then I see the link for author :authorSlug in the byline
     */
    public function iSeeTheLinkForAuthorInTheByline($authorSlug)
    {
        $this->setPermalinkStructure('/%postname%/');

        $author = Author::get_by_term_slug($authorSlug);

        $expectedAuthorLink = $_ENV['TEST_SITE_WP_URL'] . '/author/' . $author->slug . '/';
        $this->seeElement('.posted-by .byline a', ['href' => $expectedAuthorLink]);
    }
}
