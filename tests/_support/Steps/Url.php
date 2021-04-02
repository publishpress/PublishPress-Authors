<?php

namespace Steps;


trait Url
{
    /**
     * @Then I see the text :text in the current URL
     */
    public function iSeeTextInTheCurrentURL($text)
    {
        $this->seeInCurrentUrl($text);
    }
}
