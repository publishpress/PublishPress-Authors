<?php

namespace Steps;


use Behat\Gherkin\Node\TableNode;
use MultipleAuthors\Classes\Objects\Author;

trait Authors
{
    /**
     * @Given author exists for user :userLogin
     */
    public function authorExistsForUser($userLogin)
    {
        $user = get_user_by('login', $userLogin);

        Author::create_from_user($user);
    }

    /**
     * @Then I don't see the core author field
     */
    public function iDontSeeCoreAuthorField()
    {
        $this->dontSeeElement('div.post-author-selector');
    }

    /**
     * @Then I see the core author field
     */
    public function iSeeCoreAuthorField()
    {
        $this->seeElement('div.post-author-selector');
    }
}
