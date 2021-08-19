<?php

namespace Steps;


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
     * @Given guest author exists with name :authorName and slug :authorSlug
     */
    public function guestAuthorExistsWithNameAndSlug($authorName, $authorSlug)
    {
        Author::create(
            [
                'slug'         => $authorSlug,
                'display_name' => $authorName,
            ]
        );
    }

    /**
     * @Then I don't see the core author field
     */
    public function iDontSeeCoreAuthorField()
    {
        $this->wait(3);
        $this->dontSeeElement('div.post-author-selector');
    }

    /**
     * @Then I see the core author field
     */
    public function iSeeCoreAuthorField()
    {
        $this->waitForElement('div.post-author-selector');
        $this->seeElementInDOM('div.post-author-selector');
    }

    /**
     * @Then I don't see user :userName as author in the list
     */
    public function iDontSeeUserAsAuthor($userName)
    {
        $this->dontSeeElement('a.row-title[aria-label="' . $userName . '"]');
    }

    /**
     * @Then I see user :userName as author in the list
     */
    public function iSeeUserAsAuthor($userName)
    {
        $this->seeElement('a.row-title[aria-label="' . $userName . '"]');
    }
}
