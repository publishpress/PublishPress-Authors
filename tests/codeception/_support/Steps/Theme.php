<?php

namespace Steps;


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
        $this->setPermalinkStructure('');

        $author = Author::get_by_term_slug($authorSlug);

        $expectedAuthorLink = $_ENV['TEST_SITE_WP_URL'] . '/?author=' . $author->ID;
        $authorLink         = $this->grabAttributeFrom('.posted-by .byline a', 'href');

        $this->assertEquals($expectedAuthorLink, $authorLink);
    }

    /**
     * @Then I see the author box for author :authorSlug after the content
     */
    public function iSeeAuthorBoxForAuthorAfterContent($authorSlug)
    {
        $this->seeElement('.multiple-authors-target-the-content li.author_' . $authorSlug);
    }

    /**
     * @Then I see the author name for author :authorName in the box after the content
     */
    public function iSeeAuthorNameInTheBoxAfterContent($authorName)
    {
        $this->see($authorName, '.multiple-authors-target-the-content li .multiple-authors-name a.author');
    }
}

