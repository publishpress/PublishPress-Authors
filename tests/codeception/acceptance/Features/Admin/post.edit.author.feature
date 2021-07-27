Feature: Post edit author in the admin
    In order to edit the post author
    As an admin
    I need to be able to select one or more authors for a post

    Background:
        Given the user "admin_user_post_edit" exists with role "administrator"
        And I am logged in as "admin_user_post_edit"

    Scenario: I don't see the core post author field in the post edit page if the post type is activated
        Given I activated Authors for the "post" post type
        When I open the Add New Post page
        Then I don't see the core author field

    Scenario: I see the core post author field in the post edit page if the post type is not activated
        Given I deactivated Authors for the "post" post type
        When I open the Add New Post page
        Then I see the core author field

    Scenario: I see the block editor
        When I open the Add New Post page
        Then I see the block editor working
