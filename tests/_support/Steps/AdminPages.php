<?php

namespace Steps;


trait AdminPages
{
    /**
     * @Given I am on the users admin page
     */
    public function iAmOnUsersAdminPage()
    {
        $this->amOnAdminPage('/users.php');
    }

    /**
     * @When I open the authors admin page
     */
    public function iOpenTheAuthorsAdminPage()
    {
        $this->amOnAdminPage('edit-tags.php?taxonomy=author');
    }

    /**
     * @When I am on the posts admin page
     * @When I open the posts admin page
     */
    public function iOpenThePostsAdminPage()
    {
        $this->amOnAdminPage('edit.php');
    }

    /**
     * @When I open the Add New Post page
     */
    public function iOpenTheAddNewPostPage()
    {
        $this->amOnAdminPage('post-new.php');
    }

    /**
     * @When I open the plugin Settings page
     */
    public function iOpenThePluginSettingsPage()
    {
        $this->amOnAdminPage('admin.php?page=ppma-modules-settings');
    }
}
