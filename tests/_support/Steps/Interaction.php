<?php

namespace Steps;


trait Interaction
{
    /**
     * @When I click on the Delete row action for the user :userLogin
     */
    public function iClickOnTheDeleteRowActionForUser($userLogin)
    {
        $user = get_user_by('login', $userLogin);

        $this->moveMouseOver("tr#user-{$user->ID}");
        $this->click("tr#user-{$user->ID} span.delete a.submitdelete");
    }

    /**
     * @When I select and apply the bulk action :bulkAction
     */
    public function iSelectAndApplyTheBulkAction($bulkAction)
    {
        $this->selectOption('#bulk-action-selector-top', $bulkAction);
        $this->click('#doaction');
    }

    /**
     * @When I select the option :option on :select
     */
    public function iSelectTheOption($option, $select)
    {
        $this->selectOption($select, $option);
    }

    /**
     * @When I click on the submit button
     */
    public function iClickOnSubmitButton()
    {
        $this->click('#submit');
    }
}
