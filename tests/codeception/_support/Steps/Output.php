<?php

namespace Steps;


trait Output
{
    /**
     * @Then I see the text :text
     */
    public function iSeeTheText($text)
    {
        $this->see($text);
    }

    /**
     * @Then I don't see the text :text
     */
    public function iDontSeeTheText($text)
    {
        $this->dontSee($text);
    }

    /**
     * @Then I see input button with value :value
     */
    public function iSeeElementWithAttribute($value)
    {
        $this->seeElement('input', ['value' => $value]);
    }

    /**
     * @Then I see element :selector
     */
    public function iSeeElement($selector)
    {
        $this->seeElement($selector);
    }

    /**
     * @Then I don't see element :selector
     */
    public function iDontSeeElement($selector)
    {
        $this->dontSeeElement($selector);
    }
}
