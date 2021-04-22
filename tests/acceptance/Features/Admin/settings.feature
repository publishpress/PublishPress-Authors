Feature: Settings page
    In order to configure the PublishPress plugin
    As an admin
    I need to be able to select the settings I need

    Background:
        Given the user "admin_user_settings" exists with role "administrator"
        And I am logged in as "admin_user_settings"

    Scenario: I see the core post types in the field "Add to these post types"
        When I open the plugin Settings page
        Then I see the post type "post" in the field Add to these post types
        And I see the post type "page" in the field Add to these post types

    Scenario: I see a custom post type that supports authors in the field "Add to these post types"
        When I open the plugin Settings page
        Then I see the post type "book" in the field Add to these post types

    Scenario: I don't see a custom post type that don't supports authors in the field "Add to these post types"
        When I open the plugin Settings page
        Then I don't see the post type "motors" in the field Add to these post types
