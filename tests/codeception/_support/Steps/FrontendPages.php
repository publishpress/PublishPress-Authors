<?php

namespace Steps;


trait FrontendPages
{
    /**
     * @Given I am on the user register page
     */
    public function iAmOnUserRegisterPage()
    {
        $this->amOnPage('/wp-login.php?action=register');
    }
}
