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

        $user = get_user_by('login', $userLogin);

        global $current_user;
        $current_user = $user;
    }
}
