Feature: Display author box on single post page by default
    In order to check it works in the frontend
    As a visitor
    I want to see the author box in the single post page

    Background:
        Given the user "abos_admin_user" exists with role "administrator"
        And I am logged in as "abos_admin_user"
        And I set permalink structure to "/%postname%/"

    Scenario: I see the author box after the content for a guest author
        Given guest author exists with name "AOS Guest Author 1" and slug "abos_guest_author_1"
        And a post named "abos_post_guest_author_1" exists for "abos_guest_author_1"
        When I view the post "abos_post_guest_author_1"
        Then I see the author box for author "abos_guest_author_1" after the content

    Scenario: I see the author box after the content for an author mapped to user
        Given the user "abos_user_1" exists with role "administrator"
        And author exists for user "abos_user_1"
        And a post named "abos_post_author_1" exists for "abos_user_1"
        When I view the post "abos_post_author_1"
        Then I see the author box for author "abos_user_1" after the content

    Scenario: I see the author name in the author box after the content for a guest author
        Given guest author exists with name "AOS Guest Author 2" and slug "abos_guest_author_2"
        And a post named "abos_post_guest_author_2" exists for "abos_guest_author_2"
        When I view the post "abos_post_guest_author_2"
        Then I see the author name for author "AOS Guest Author 2" in the box after the content

    Scenario: I see the author name in the author box after the content for an author mapped to user
        Given the user "abos_user_2" exists with role "administrator"
        And author exists for user "abos_user_2"
        And a post named "abos_post_author_2" exists for "abos_user_2"
        When I view the post "abos_post_author_2"
        Then I see the author name for author "abos_user_2" in the box after the content
