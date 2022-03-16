Feature: Display author on single post page for 3rd party theme
    In order to check it works in the frontend using a 3rd party theme
    As a visitor
    I want to see the name and link of the author in the single post page

    Background:
        Given the user "admin_user" exists with role "administrator"
        And I am logged in as "admin_user"
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
