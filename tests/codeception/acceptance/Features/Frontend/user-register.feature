Feature: Add new user in the frontend
    In order to automatically create authors from new users
    As a guest user
    I need to be able to register myself as user

    Scenario: User registers to the site when subscriber is selected, an author is created
        Given the user "ur_admin_user" exists with role "administrator"
        And I am logged in as "ur_admin_user"
        And anyone can register to the site
        And I selected role "subscriber" for the Automatically Create Author Profiles setting
        And I log out
        And I am on the user register page
        And I submit the user form as "ur_user_1" and "ur_user_1@example.com"
        And I am logged in as "ur_admin_user"
        When I open the authors admin page
        Then I see user "ur_user_1" as author in the list

    Scenario: User registers to the site when subscriber is not selected, an author is not created
        Given the user "ur_admin_user" exists with role "administrator"
        And I am logged in as "ur_admin_user"
        And anyone can register to the site
        And I selected role "author" for the Automatically Create Author Profiles setting
        And I log out
        And I am on the user register page
        And I submit the user form as "ur_user_2" and "ur_user_2@example.com"
        And I am logged in as "ur_admin_user"
        When I open the authors admin page
        Then I don't see user "ur_user_2" as author in the list
