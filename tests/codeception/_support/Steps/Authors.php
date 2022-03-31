<?php

namespace Steps;


use MultipleAuthors\Classes\Objects\Author;

use function sq;

trait Authors
{
    /**
     * @Given author exists for user :userLogin
     */
    public function authorExistsForUser($userLogin)
    {
        $user = get_user_by('login', sq($userLogin));

        Author::create_from_user($user);
    }

    /**
     * @Given guest author exists with name :authorName and slug :authorSlug
     */
    public function guestAuthorExistsWithNameAndSlug($authorName, $authorSlug)
    {
        Author::create(
            [
                'slug'         => sq($authorSlug),
                'display_name' => sq($authorName),
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
        $this->dontSeeElement('a.row-title[aria-label="' . sq($userName) . '"]');
    }

    /**
     * @Then I see user :userName as author in the list
     */
    public function iSeeUserAsAuthor($userName)
    {
        $this->seeElement('a.row-title[aria-label="' . sq($userName) . '"]');
    }

    /**
     * @Given I view the author page for :authorSlug
     */
    public function iViewAuthorPageForAuthor($authorSlug)
    {
        $authorSlug = sq($authorSlug);

        $author = Author::get_by_term_slug($authorSlug);

        $this->amOnPage($author->link);
    }

    /**
     * @Given I create a new author :authorSlug
     */
    public function iCreateNewAuthor($authorSlug)
    {
        $authorSlug = sq($authorSlug);

        Author::create(
            [
                'slug' => $authorSlug,
                'display_name' => $authorSlug
            ]
        );
    }

    /**
     * @Given I edit author :authorSlug setting biographical info :bioText
     */
    public function iEditAuthorSettingBioInfo($authorSlug, $bioText)
    {
        $authorSlug = sq($authorSlug);

        $author = Author::get_by_term_slug($authorSlug);

        $this->amOnAdminPage('term.php?taxonomy=author&tag_ID=' . $author->term_id);
        $this->fillField('.term-authors-description-wrap textarea', $bioText);
        $this->click('Update');
    }

    /**
     * @When I view the author profile :authorSlug
     */
    public function iViewAuthorProfile($authorSlug)
    {
        $authorSlug = sq($authorSlug);

        $author = Author::get_by_term_slug($authorSlug);

        $this->amOnAdminPage('term.php?taxonomy=author&tag_ID=' . $author->term_id);
    }

    /**
     * @Then I see :bioText in the biographical info field
     */
    public function iSeeBioText($bioText)
    {
        $this->seeInField('.term-authors-description-wrap textarea', $bioText);
    }
}
