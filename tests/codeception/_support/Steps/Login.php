<?php

namespace Steps;


trait Login
{
    /**
     * @Given I am logged in as :userLogin
     */
    public function iAmLoggedInAsUser($userLogin)
    {
        $this->loginAs($userLogin, $userLogin);
    }
}
