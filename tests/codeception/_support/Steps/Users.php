<?php

namespace Steps;


use Behat\Gherkin\Node\TableNode;

use function sq;

trait Users
{
    /**
     * @Then I see the user ID for :userLogin in the deletion list
     */
    public function ISeeTheUserIDForUserInTheDeletionList($userLogin)
    {
        $user = get_user_by('login', sq($userLogin));

        $this->see("ID #{$user->ID}");
    }

    /**
     * @Given the user :userLogin exists with role :userRole
     */
    public function theUserExistsWithRole($userLogin, $userRole)
    {
        $userLogin = sq($userLogin);

        $this->factory()->user->create(
            [
                'user_login' => $userLogin,
                'user_pass'  => $userLogin,
                'user_email' => sprintf('%s@example.com', $userLogin),
                'role'       => $userRole
            ]
        );
    }

    /**
     * @Given following users exist
     */
    public function followingUsersExist(TableNode $users)
    {
        foreach ($users->getRows() as $index => $row) {
            if ($index === 0) {
                continue;
            }

            $userLogin = sq($row[0]);

            $this->factory()->user->create(
                [
                    'user_login' => $userLogin,
                    'user_pass'  => $userLogin,
                    'user_email' => $userLogin . '@example.com',
                    'role'       => $row[1]
                ]
            );
        }
    }

    /**
     * @Given the user :userLogin is selected
     * @When I select the user :userLogin
     */
    public function theUserIsSelected($userLogin)
    {
        $user = get_user_by('login', sq($userLogin));

        $this->checkOption('#user_' . $user->ID);
    }

    /**
     * @When I create a new user :userName with role :userRole
     */
    public function iCreateNewUserWithRole($userName, $userRole)
    {
        $userName = sq($userName);
        $password = md5($userName);

        $this->amOnAdminPage('/user-new.php');
        $this->fillField('#user_login', $userName);
        $this->fillField('#email', "{$userName}@example.com");
        $this->fillField('#pass1', $password);
        $this->selectOption('#role', $userRole);
        $this->executeJS('jQuery("#pass1").prop("disabled", false);jQuery("#pass2").prop("disabled", false);jQuery("#pass2").val(jQuery("#pass1").val());');
        $this->click('#createusersub');
    }

    /**
     * @Given I submit the user form as :userName and :userEmail
     */
    public function iSubmitUserForm($userName, $userEmail)
    {
        $userEmail = str_replace($userName, sq($userName), $userEmail);
        $userName = sq($userName);

        $this->fillField('#user_login', $userName);
        $this->fillField('#user_email', $userEmail);
        $this->click('#wp-submit');
    }

    /**
     * @Given I log out
     */
    public function iLogOut()
    {
        $this->logOut();
    }
}
