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
