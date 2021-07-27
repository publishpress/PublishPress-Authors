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
        And a post named "post_guest_author_1" exists for "guest_author_1"
        When I view the post "post_guest_author_1"
        Then I see the author name "Guest Author 1" in the byline

    Scenario: I see the link for the guest author in the byline added by the theme "Twenty Twenty-One"
        Given guest author exists with name "Guest Author 2" and slug "guest_author_2"
        And a post named "post_guest_author_2" exists for "guest_author_2"
        When I view the post "post_guest_author_2"
        Then I see the link for author "guest_author_2" in the byline
