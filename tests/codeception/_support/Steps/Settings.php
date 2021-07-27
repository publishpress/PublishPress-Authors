<?php

namespace Steps;


trait Settings
{
    /**
     * @Given I activated Authors for the :postType post type
     */
    public function iActivatedAuthorsForPostType($postType)
    {
        $this->amOnAdminPage('admin.php?page=ppma-modules-settings');
        $this->checkOption("#{$postType}-multiple-authors");
        $this->iClickOnSubmitButton();
    }

    /**
     * @Given I deactivated Authors for the :postType post type
     */
    public function iDeactivatedAuthorsForPostType($postType)
    {
        $this->amOnAdminPage('admin.php?page=ppma-modules-settings');
        $this->uncheckOption("#{$postType}-multiple-authors");
        $this->iClickOnSubmitButton();
    }

    /**
     * @Then I see the post type :postType in the field Add to these post types
     */
    public function iSeePostTypeInAddToThesePostTypes($postType)
    {
        $this->seeElement("#{$postType}-multiple-authors");
    }

    /**
     * @Then I don't see the post type :postType in the field Add to these post types
     */
    public function iDontSeePostTypeInAddToThesePostTypes($postType)
    {
        $this->dontSeeElement("#{$postType}-multiple-authors");
    }

    /**
     * @Given I set permalink structure to :structure
     */
    public function iSetPermalinkStructure($structure)
    {
        global $wp_rewrite;

        $wp_rewrite->permalink_structure = $structure;
        $wp_rewrite->flush_rules(true);
    }
}
