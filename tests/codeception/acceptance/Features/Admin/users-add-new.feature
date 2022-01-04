Feature: Add new user in the backend
    In order to automatically create authors from new users
    As an admin
    I need to be able to select post types to automatically create authors and see them created

    Background:
        Given the user "admin_user" exists with role "administrator"
        And I am logged in as "admin_user"

    Scenario: Author is created when user is created in the admin for a selected role
        Given I selected role "author" for the Automatically Create Author Profiles setting
        When I create a new user "user_1" with role "author"
        Then I wait for 30 seconds
        And I open the Authors admin page
        Then I see user "user_1" as author in the list

    Scenario: Author is not created when user is created in the admin for default role
        Given I selected role "author" for the Automatically Create Author Profiles setting
        When I create a new user "user_2" with role "subscriber"
        And I open the Authors admin page
        Then I don't see user "user_2" as author in the list
