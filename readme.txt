=== PublishPress Authors ===
Contributors: publishpress, andergmartins, stevejburge, pressshack
Author: PublishPress, PressShack
Author URI: https://publishpress.com
Tags: publishpress, authors
Requires at least: 4.6
Tested up to: 5.2
Stable tag: 3.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extend PublishPress implementing support for multiple authors.

== Description ==
Extend PublishPress implementing support for multiple authors.

----------------------------------------------------------------------------
Based on Co-Authors Plus (contributors: batmoo, danielbachhuber, automattic)
----------------------------------------------------------------------------

== Installation ==
There're two ways to install PublishPress plugin:

**Through your WordPress site's admin**

1. Go to your site's admin page;
2. Access the "Plugins" page;
3. Click on the "Add New" button;
4. Search for "PublishPress Authors";
5. Install PublishPress Authors plugin;
6. Activate the PublishPress Authors plugin.

**Manually uploading the plugin to your repository**

1. Download the PublishPress Authors plugin zip file;
2. Upload the plugin to your site's repository under the *"/wp-content/plugins/"* directory;
3. Go to your site's admin page;
4. Access the "Plugins" page;
5. Activate the PublishPress Authors plugin.

== Usage ==
- Make sure you have PublishPress plugin installed and active;
- Go to PublishPress Settings page, click on "Checklist" tab and customize its options at will;
- That's it.

== Changelog ==

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning v2.0.0](https://semver.org/spec/v2.0.0.html).

= [3.1.0] - 2019-11-19 =

* Added: Added support to the function "do_shortcode" in the custom layouts;
* Added: Added a shortcode for testing the output in the frontend (ppma_test);
* Added: Expose the method "author.has_custom_avatar" method on custom layouts;
* Added: New argument for post ID in the action "pp_multiple_authors_show_author_box";
* Changed: Updated the EDD library;
* Fixed: The React library is being downgraded (overridden) in WP 5.3;

= [3.0.1] - 2019-09-30 =

* Fixed: Backward compatibility issue with themes using the class PP_Multiple_authors_plugin and other legacy classes for custom layouts;
* Fixed: Wrong size for avatars in Twenty Nineteen theme and probably other themes where the author avatar is using the CSS class "photo" instead of "avatar" for the image;
* Fixed: JS error related to select2 being undefined;
* Fixed: The author field was not working if no author was set for new posts;
* Changed: Simplified the documentation on the sidebar for custom layouts;

= [3.0.0] - 2019-09-24 =

* Changed: Converted to a standalone plugin removing any dependency of PublishPress. Some constants and methods where renamed to avoid name collisions;
* Added: Custom fields for authors;
* Added: Custom layouts for customising the output;
* Added: New filter for customising the rendered author box markup: pp_multiple_authors_author_box_rendered_markup;
* Added: A warning message advising to update PressPermit Pro to the version 2.7.24 or later, if installed. This is not compatible with prior versions;
* Added: Menu item specific for Authors;
* Added: Settings field to hide the PublishPress brand in the admin;
* Fixed: The shortcode was not supported in the archive page for displaying the author box;
* Fixed: Incompatibility with legacy version of select2;
* Fixed: PHP notice when the capability "ppma_edit_orphan_post" was not defined;
* Fixed: WSOD in Gutenberg when a user tried to edit an orphan post (with no author);
* Fixed: Some style issues related to settings fields and delete button on author fields;
* Fixed: The query on author pages was catching diverse post types for logged in users with the capability ppma_edit_orphan_post;
* Fixed: Error in the author page regarding the class Author not being found;
* Fixed: Invalid rewrite rules being defined for custom post types, but not implemented;
* Fixed: Author page displaying auto-drafts for logged in authors;
* Fixed: Custom author's name being overwritten after select a mapped user;
* Fixed: The query for deleting Guest authors is not detecting/deleting authors with custom avatar;
* Fixed: Fix an issue related to wrong paths for assets in installations where ABSPATH = //;
* Changed: Improved the settings page adding tabs;
* Changed: The license key field was moved from PublishPress to the Multiple Authors Settings page;
* Changed: The filter "pp_multiple_authors_filter_author_box_markup" is deprecated in favor of "pp_multiple_authors_author_box_rendered_markup";
* Changed: Posts and Pages are enabled by default for Multiple Authors;
* Changed: Updated the POT file;
* Changed: Updated the text of notices for maintenance actions;

= [2.3.0] - 2019-02-11 =

* Fixed the authors column removing it from unselected post types;
* Fixed the social icons in the frontend if not logged in;
* Fixed PT-BR translations (Thanks to Dionizio Bach);
* Fixed hardcoded table names in some queries;
* Added support to custom avatars for guest and user mapped authors;
* Added new inline layout with avatar;

= [2.2.0] - 2019-01-14 =

* Added support for the attributes: "show_title" and "layout" in the shortcode "author_box";
* Added new layout: inline. This is good for using in theme builders or themes to replace standard author links. Fix support for Beaver Themer and Beaver Builder.
* Removed the legacy field for the license key;
* Fixed author metabox in the post form when the post has no author;
* Fixed Author column in the post for no author;
* Fixed the widget to not display anything if there is no author;
* Added option to create posts with no author by default;

= [2.1.3] - 2018-07-19 =

*Fixed:*

* Fixed upload of media files in a post for "secondary" authors if using multiple authors;

= [2.1.2] - 2018-06-27 =

*Fixed:*

* Fixed the author link for guest authors when using the function get_author_posts_url;
* Fixed the author display name for guest authors;

= [2.1.1] - 2018-06-21 =

*Fixed:*

* Fixed avatar after selecting an author for the post;
* Fixed the author page, making it filter correctly the author's posts;
* Fixed the permalinks structure after upgrading or installing the plugin;
* Fixed the submenu for Author which appeared in other post types;

= [2.1.0] - 2018-06-12 =

*Fixed:*

* Fixed filter for custom output on author boxes;
* Fixed PHP warnings;
* Fixed the change of posts/authors to only modify the queries if the post type is selected;
* Fixed menu structure when the calendar and other modules are disabled;
* Fixed hardcoded version number when loading some scripts;
* Fixed the author page if called without the author_id in the URL;
* Fixed minor details in the style of pages;
* Fixed migration of data when installing or updating to link correctly to posts;
* Fixed the Author class adding the missed ID property;
* Fixed fatal error when running 5.4, related to the Initialized class in the add-on framework library;

*Changed:*

* Changed minimum version required of PublishPress to 1.14.0;

*Added:*

* Added bulk action to update the author data for author with mapped users;
* Added buttons in the settings to reset the authors terms (for maintenance). Multiple authors are removed from posts, Guest authors are removed. Authors are created from users selected as authors in the posts, defined in the post_author field, and from the specific roles, if configured;
* Added chosen JS script for add-ons;
* Added some error messages to the log when there is an issue looking for the author of the post;

= [2.0.3] - 2018-05-18 =

*Fixed:*

* Fixed a fatal error on some environments;

= [2.0.2] - 2018-05-16 =

*Fixed:*

* Fixed duplicated authors generated with a prefix "cap-" in the Author URL (slug);
* Fixed a PHP warning in the calendar page related a non defined object;
* Fixed a hardcoded table prefix and name in a query to get author by user id;
* Fixed author'slug (author url) forcing it to be equals to the user's nicename, if mapped to a user - on saving and on install/upgrade;
* Fixed the author_name param in the author link on the list of posts in the admin;
* Fixed query of posts by author (front-end and back-end) - now it displays the correct posts related to the selected author;
* Fixed conflict with CoAuthors Plus, if both plugins where activated. Related to a PHP Error about not being able to redefine an existent function;
* Fixed the migration of data when installing and upgrading, now recognizing the correct version in the conditional;
* Fixed the list of authors in the search box on posts to display only authors, removing users;
* Fixed the migration of post authors to multiple authors terms;
* Fixed query making sure to sanitize the user_id retrieved from the URL;

*Changed:*

* Removed column "Count" from authors and users list;
* Increased PublishPress minimum version required to 1.13.0;

= [2.0.1] - 2018-05-14 =

*Fixed:*

* Fixed the URL in the author link for guest authors;
* Fixed the height of the avatars in some themes;
* Fixed PHP warning when creating a new user;
* Fixed PHP warning when opening a BuddyPress member page;
* Fixed typo in the Email field description;
* Fixed PHP warning when adding a widget before saving the plugins' settings after the update;
* Fixed outdated .POT file;
* Fixed PT-BR translation file;

*Changed:*

* Author URL is now auto-populated after selecting a mapped user, and the field is disabled;
* Removed Quick Edit for authors;
* Minor improvement on styling for some form fields;
* Updated language files;
* Allow to create multiple authors mapped to the same user;
* Removed outdated and wrong translations;

*Added:*

* Added Mapped User field to the quick add form for Authors;
* Added description to some fields and improved text to make their functioning clear;
* Added support for the shortcodes in notifications which are related to authors, to display data from the multiple authors;

= [2.0.0] - 2018-05-09 =

*Added:*

* Added option to create guest authors;
* Added submenu for Authors;
* Added support for migrating from Bylines;
* Added setting to change the style of the author box, with 3 layouts available (including in the widget);
* Added more CSS classes to the elements in the author box to allow a more customized result;
* Added option to display the email and website link for the author;
* Added option to automatically convert new users of specific roles into authors;
* Added button to edit the user on authors mapped to users;

*Fixed:*

* Fixed support for custom post types;
* Fixed PHP warnings about undefined properties;
* Fixed compatibility with themes which support only co-authors;
* Fixed JS error in the post form when Avatars are disabled in the Discussions settings;
* Fixed author page to display the correct posts for the author;
* Fixed the post page to display the multiple authors when using the get_the_author() method;
* Fixed the posts counter in the authors page on the admin;
* Fixed the posts counter in the users pages on the admin;
* Fixed authors' avatars in the admin and frontend. Now it supports custom avatars defined by 3rd party plugins;
* Fixed a PHP warning when the user doesn't have permissions to set the author;

*Changed:*

* Fixed code style for files;
* Reorganized files structure in the repository;
* Changed requirement for PublishPress 1.12.0;
* Removed module descriptions from some screens, to be more consistent and clearer;
* Renamed author's slug fields and columns to "Author URL";
* Deprecated the method multiple_authors_wp_list_authors. Please, use get_the_authors();

= [1.0.6] - 2018-02-07 =

*Fixed:*

* Fixed license key validation and automatic updates;

*Changed:*

* Removed Quick Edit for authors;

*Changed:*

* Rebranded to PublishPress;

= [1.0.5] - 2017-10-16 =

* Added:
* Added field to change the title of the author box appended to the content;

= [1.0.4] - 2017-10-11 =

* Fixed:
* Fixed the filter which checks if the post type is selected to enable the module;

* Added:
* Added field to customize the widget title;

= [1.0.3] - 2017-08-31 =

* Changed:
* Implemented compatilbility with new Notification Workflows on PublishPress;
* Changed design for multiple authors box in dashboard;
* Removed Freemius integration;

* Fixed:
* Fixed conflict in the name of a method checking if PublishPress is active;

= [1.0.2] - 2017-07-28 =

* Fixed:
* Fixed the widget syntax and title;
* Fixed style of the list of authors, removing padding-left;

= [1.0.1] - 2017-07-28 =

* Fixed:
* Fixed license validation;

= [1.0.0] - 2017-07-27 =

* Changed:
* Refactored CoAuthor Plus to match the new vendor and plugin name;

* Added:
* Added new widget to display the author box;
* Added an option to display the author box automatically appended to the content. Eenabled by default, but can be disabled;
* Added a shortcode [author_box] to display the author box anywhere in the content;
* Added an action to display the author box programatically: pp_multiple_authors_show_author_box;
* Added a filter to customize the content of the author box: pp_multiple_authors_filter_author_box_markup;
* Added a filter to customize when the author box will be displayed: pp_multiple_authors_filter_should_display_author_box

* Fixed:
* Fixed the avatar in the metabox to match the WP configuration;
