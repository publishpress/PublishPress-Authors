Feature: Display author box on single post page by default
    In order to check it works in the frontend
    As a visitor
    I want to see the author box in the single post page

    Background:
        Given the user "admin_user" exists with role "administrator"
        And I am logged in as "admin_user"
        And I set permalink structure to "/%postname%/"

    Scenario: I see the author box after the content for a guest author
        Given guest author exists with name "Guest Author 1" and slug "guest_author_1"
        And a post named "post_guest_author_1" exists for "guest_author_1"
        When I view the post "post_guest_author_1"
        Then I see the author box for author "guest_author_1" after the content

    Scenario: I see the author box after the content for an author mapped to user
        Given the user "user_1" exists with role "administrator"
        And author exists for user "user_1"
        And a post named "post_author_1" exists for "user_1"
        When I view the post "post_author_1"
        Then I see the author box for author "user_1" after the content

    Scenario: I see the author name in the author box after the content for a guest author
        Given guest author exists with name "Guest Author 2" and slug "guest_author_2"
        And a post named "post_guest_author_2" exists for "guest_author_2"
        When I view the post "post_guest_author_2"
        Then I see the author name for author "Guest Author 2" in the box after the content

    Scenario: I see the author name in the author box after the content for an author mapped to user
        Given the user "user_2" exists with role "administrator"
        And author exists for user "user_2"
        And a post named "post_author_2" exists for "user_2"
        When I view the post "post_author_2"
        Then I see the author name for author "user_2" in the box after the content
