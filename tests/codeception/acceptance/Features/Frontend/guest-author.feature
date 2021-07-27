Feature: Guest author in the frontend
    In order to see the author of a post in the frontend
    As a visitor
    I want to see the name of the name of the guest author in the frontend

    Background:
        Given the user "admin_user_guest_author" exists with role "administrator"
        And I am logged in as "admin_user_guest_author"
        And I set permalink structure to "/%postname%/"

    Scenario: I see the name of the guest author in the byline added by the theme "Twenty Twenty-One"
        Given guest author exists with name "Guest Author 1" and slug "guest_author_1"
        Given a post named "post_guest_author_1" exists for "guest_author_1"
        When I view the post "post_guest_author_1"
        Then I see the author name "Guest Author 1" in the byline
