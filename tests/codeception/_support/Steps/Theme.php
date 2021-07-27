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
}
