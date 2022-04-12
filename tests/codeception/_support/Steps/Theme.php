<?php

namespace Steps;


use MultipleAuthors\Classes\Objects\Author;

use function sq;

trait Theme
{
    /**
     * @Then I see the author name :authorName in the byline
     */
    public function iSeeAuthorNameInTheByline($authorName)
    {
        $this->see(sq($authorName), '.post-meta .post-author a');
    }

    /**
     * @Then I see the link for author :authorSlug in the byline
     */
    public function iSeeTheLinkForAuthorInTheByline($authorSlug)
    {
        $expected = sprintf(
            '%s/author/%s/',
            $_ENV['TEST_SITE_WP_URL'],
            sq($authorSlug)
        );

        $authorLink = $this->grabAttributeFrom('.post-meta .post-author a', 'href');

        $this->assertEquals($expected, $authorLink);
    }

    /**
     * @Then I see the author box for author :authorSlug after the content
     */
    public function iSeeAuthorBoxForAuthorAfterContent($authorSlug)
    {
        $this->seeElement('.multiple-authors-target-the-content li.author_' . sq($authorSlug));
    }

    /**
     * @Then I see the author name for author :authorName in the box after the content
     */
    public function iSeeAuthorNameInTheBoxAfterContent($authorName)
    {
        $this->see(sq($authorName), '.multiple-authors-target-the-content li .multiple-authors-name a.author');
    }
}
