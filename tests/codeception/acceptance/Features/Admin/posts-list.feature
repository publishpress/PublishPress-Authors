Feature: Posts list
    In order to see the post author in the posts list
    As an admin
    I need to be able to see the authors in the Authors column

    Background:
        Given the user "admin_user" exists with role "administrator"
        And I am logged in as "admin_user"

    Scenario: Author column is removed from the list of posts
        When I open the All Posts page
        Then I don't see the column "author"

    Scenario: Authors column is added to the list of posts
        When I open the All Posts page
        Then I see the column "authors"

    Scenario: Authors column has the name of the author for single author mapped to user
        Given the user "user_1" exists with role "author"
        And author exists for user "user_1"
        And a post named "post_1" exists for "user_1"
        When I open the All Posts page
        Then I see the text "user_1" in the column "authors" for the post "post_1"

    Scenario: Authors column has the name of the authors for multiple authors mapped to user
        Given the user "user_2" exists with role "author"
        And the user "user_3" exists with role "author"
        And author exists for user "user_2"
        And author exists for user "user_3"
        And a post named "post_2" exists for "user_2" and "user_3"
        When I open the All Posts page
        Then I see the text "user_2, user_3" in the column "authors" for the post "post_2"

    Scenario: Authors column has the name of the guest author and fallback user for single guest author
        Given the user "user_4" exists with role "author"
        And guest author exists with name "PL Guest Author 1" and slug "guest_author_1"
        And a post named "post_3" exists for guest author "guest_author_1" and fallback user "user_4"
        When I open the All Posts page
        Then I see the text "PL Guest Author 1 [user_4]" in the column "authors" for the post "post_3"
