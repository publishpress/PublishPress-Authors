Feature: Add and edit author profiles
    In order to manage authors
    As an admin
    I need to be able to add or edit the author profile

    Background:
        Given the user "admin_user" exists with role "administrator"
        And I am logged in as "admin_user"

    Scenario: Valid HTML tags are not stripped from author's profile after saving
        Given I create a new author "author_1"
        And I edit author "author_1" setting biographical info "An <b>awesome<b> writer"
        When I view the author profile "author_1"
        Then I see "An <b>awesome<b> writer" in the biographical info field
