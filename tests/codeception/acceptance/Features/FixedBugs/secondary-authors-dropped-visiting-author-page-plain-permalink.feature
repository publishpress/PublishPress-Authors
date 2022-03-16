Feature: Secondary authors of a post should not be dropped after visiting a author page, issue #593
    In order to make sure post authors are not modified automatically by the bug
    As an admin
    I want to see if the secondary authors are not removed after visiting author page using plain permalinks

    Background:
        Given the user "admin_user" exists with role "administrator"
        And I am logged in as "admin_user"
        And I set permalink structure to plain

    Scenario: I see the secondary post author (guest) after visiting the author page using plain permalinks
        Given guest author exists with name "Guest Author 1" and slug "guest_author_1"
        And the user "user_author_1" exists with role "author"
        And author exists for user "user_author_1"
        And a post named "post_multi_authors" exists for "user_author_1" and "guest_author_1"
        And I view the post "post_multi_authors"
        And I view the author page for "user_author_1"
        When I view the post "post_multi_authors"
        Then I see the author box for author "user_author_1" after the content
        And I see the author box for author "guest_author_1" after the content

    Scenario: I see the secondary post author (mapped to user) after visiting the author page using plain permalinks
        Given the user "user_author_1" exists with role "author"
        And the user "user_author_2" exists with role "author"
        And author exists for user "user_author_1"
        And author exists for user "user_author_2"
        And a post named "post_multi_authors" exists for "user_author_1" and "user_author_2"
        And I view the post "post_multi_authors"
        And I view the author page for "user_author_1"
        When I view the post "post_multi_authors"
        Then I see the author box for author "user_author_1" after the content
        And I see the author box for author "user_author_2" after the content
