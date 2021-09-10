Feature: Post edit author in the admin
    In order to edit the post author
    As an admin
    I need to be able to select one or more authors for a post

    Background:
        Given the user "peabe_admin_user" exists with role "administrator"
        And I am logged in as "peabe_admin_user"

    Scenario: Core post author field is not visible in the post edit page if the post type is activated
        Given I activated Authors for the "post" post type
        When I open the Add New Post page
        Then I don't see the core author field

    Scenario: Core post author is visible in the post edit page if the post type is not activated
        Given I deactivated Authors for the "post" post type
        When I open the Add New Post page
        Then I see the core author field
        And I activated Authors for the "post" post type

    Scenario: Block editor works when creating a new post
        When I open the Add New Post page
        Then I don't see the post locked modal
        And I see the post visual editor
        And I can add blocks in the editor

    Scenario: Block editor works when editing a post of guest author
        Given guest author exists with name "PEABE Guest Author 1" and slug "peabe_guest_author_1"
        And a post named "peabe_post_guest_author_1" exists for "peabe_guest_author_1"
        When I edit the post name "peabe_post_guest_author_1"
        Then I don't see the post locked modal
        And I see the post visual editor
        And I can add blocks in the editor

    Scenario: Block editor works when editing a post of author mapped to user
        Given the user "peabe_user_1" exists with role "administrator"
        And author exists for user "peabe_user_1"
        And a post named "peabe_post_guest_author_2" exists for "peabe_user_1"
        When I edit the post name "peabe_post_guest_author_2"
        Then I don't see the post locked modal
        And I see the post visual editor
        And I can add blocks in the editor
