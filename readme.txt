=== PublishPress Authors: Multiple Authors, Guest Authors, Co-Authors in WordPress ===

Contributors: publishpress, kevinB, stevejburge, andergmartins
Author: PublishPress
Author URI: https://publishpress.com
Tags: multiple authors, authors, guest authors, author fields, author layouts
Requires at least: 4.7
Requires PHP: 5.6
Tested up to: 5.4
Stable tag: 3.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

PublishPress Authors is the best plugin for adding many authors to one WordPress post. You can create multiple authors, co-authors and guest authors.

== Description ==

[PublishPress Authors](https://publishpress.com/authors/) is the best plugin for adding many authors to one WordPress post. You can create multiple authors, co-authors and guest authors.

= Multiple Authors and Co-Authors =

With PublishPress Authors, you can set multiple authors and coauthors for each post. When you write a post, you’ll see a box in the right sidebar. Here you can choose from all the users on your site, and assign them as authors.

On the frontend of your site, PublishPress Authors gives you several different options to display the authors’ box:

* Replacing the default author display.
* At the bottom of your content.
* In a widget or a Gutenberg block.
* Using shortcodes.
* Adding filters and actions in your template files.

[Click here to read about displaying authors](https://publishpress.com/knowledge-base/display-multiple-authors/).

= Guest Authors =

Using PublishPress Authors, you can create Guest Authors who don’t need an account on your site. PublishPress will treat Guest Authors identically to Authors who are linked users.

You will be able to select and display Guest Authors in exactly the same way as for registered users. Each Guest Author can have a full profile, plus an avatar, and their own archive page for blog posts.

[Click here to see how to create Guest Authors](https://publishpress.com/knowledge-base/add-guest-authors-wordpress/).

= Multiple Layout Options for Author Profiles =

PublishPress Authors provides five default ways to display the author profiles on your site.

In the PublishPress Authors settings you can choose from these layouts:

* Simple list
* Boxed
* Centered
* Inline
* Inline with Avatars

[Click here to see details on all the layout options](https://publishpress.com/knowledge-base/layout/).

= Custom Fields for Author Profiles (Pro version) =

PublishPress Authors Pro enables you to create custom fields and enhance your author profiles.

You can add Text, WYSIWYG, Link and email address fields. Then you can place those fields in author profiles using custom layouts.

[Click here to see how to add fields for author profiles](https://publishpress.com/knowledge-base/custom-fields/).

= Custom Layouts for Author Profiles (Pro version) =

PublishPress Authors enables you to build custom layouts for your author profiles.

Using all your author information and custom fields, you can design beautiful layouts for your authors. Each layout is editable using Twig. You can add many different types of author information to these layouts including custom fields.

[Click here to see how to customize author layouts](https://publishpress.com/knowledge-base/custom-layouts/).

= Join PublishPress and get the Pro plugins =

The Pro versions of the PublishPress plugins are well worth your investment. The Pro versions have extra features and faster support. [Click here to join PublishPress](https://publishpress.com/pricing/).

Join PublishPress and you’ll get access to these 6 Pro plugins:

* [PublishPress Authors Pro](https://publishpress.com/authors) allows you to add multiple authors and guest authors to WordPress posts
* [PublishPress Capabilities Pro](https://publishpress.com/capabilities) is trusted to manage the permissions for over 80,000 WordPress sites
* [PublishPress Checklists Pro](https://publishpress.com/checklists) enables you to define tasks that must be completed before content is published.
* [PublishPress Permissions Pro](https://publishpress.com/permissions)  is the plugin for advanced WordPress permissions.
* [PublishPress Pro](https://publishpress.com/publishpress) is the plugin for managing and scheduling WordPress content.
* [PublishPress Revisions Pro](https://publishpress.com/revisions) allows you to update your published pages with teamwork and precision.

Together, these plugins are a suite of powerful publishing tools for WordPress. If you need to create a professional workflow in WordPress, with moderation, revisions, permissions and more … then you should try PublishPress.

=  Bug Reports =

Bug reports for PublishPress Authors are welcomed in our [repository on GitHub](https://github.com/publishpress/publishpress-authors). Please note that GitHub is not a support forum, and that issues that aren’t properly qualified as bugs will be closed.

= Follow the PublishPress team =

Follow PublishPress on [Facebook](https://www.facebook.com/publishpress), [Twitter](https://www.twitter.com/publishpresscom) and [YouTube](https://www.youtube.com/channel/UC8VExJ7eS8EduxYD_GSMNdA).

= Thank You =

This plugin is partly based on Co-Authors Plus, which includes the work of batmoo, danielbachhuber and automattic. This plugin also uses work from the Bylines plugin by danielbachhuber.

== Installation ==

There are two ways to install the PublishPress Authors plugin:

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

* Go to the admin page, click on the "Authors" menu and create new author profiles;
* Go to write a new post and you'll see the box for selecting multiple authors in the sidebar.

== Changelog ==

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning v2.0.0](https://semver.org/spec/v2.0.0.html).

= [3.3.2] - UNRELEASED =

* Fixed: Fix the text domain loading, fixing the translations;
* Fixed: Fix "orphan" authors when the mapped user is deleted, converting them in guest authors, #142;
* Changed: Updated the min PHP version to 5.6;
* Changed: Updated the WordPress tested up to version, to 5.4;

= [3.3.1] - 2020-05-27 =

* Added: Added the static function "get_by_email" to the Author class for getting an author by his e-mail address;
* Changed: Improved error messages;
* Fixed: Fatal error for WP < 5.4 due to the function "is_favicon" not being defined;
* Fixed: Fix the get_avatar_url output for authors with a custom avatar, #122;
* Fixed: HTML entities were not rendered in the frontend using the default author layouts, #123;
* Fixed: Secondary authors don't have the edit_others_posts capability for their own posts, #129;
* Fixed: Improved integration with PublishPress adding support for multiple authors in the calendar. #129, #131;
* Fixed: Updated the POT file;

= [3.3.0] - 2020-05-05 =

* Added: Some error messages are now added to the error log;
* Added: Added links to the slug column in the authors list to open the authors page in the frontend;
* Added: Added post data to the twig layout context - #112;
* Added: Added multiple authors support to the Elementor Pro, adding new skins to the Posts and Archive Posts widgets;
* Added: Added support to Divi's theme builder and dynamic data related to authors;
* Changed: Reorganized the folder structure of the plugin moving the code to a "src" folder;
* Changed: Guest authors are now identified by author->ID < 0, which corresponds to the term_id. If ID > 0, it is a user, otherwise, an author term. This increases the compatibility rate with standard author functions;
* Fixed: Improved text and fixed typo in the data migration messages;
* Fixed: Added pointer cursor when hovering the "x" for removing authors from the list;
* Fixed: Fixed minor style issue in the Simple List layout CSS;
* Fixed: Centered avatar and fixed minor style issues in the Centered layout;
* Fixed: Removed the blank '-' char from the Simple List layout due to undefined "age" field;
* Fixed: Error message when the installed Yoast SEO doesn't have the function add_piece_language;
* Fixed: Detection of minimum required Yoast SEO version for the module to be activated, so the error message is not displayed if Yoast SEO is not installed;
* Fixed: Yoast SEO structured data was not displaying pages data correctly;
* Fixed: Yoast SEO structured data was not working well when a guest author was the first author in posts;
* Fixed: Yoast SEO structured data with incorrect @id for pages;
* Fixed: Fixed the method that implements the column for authors in the post list, to only run for enabled post types;
* Fixed: Fixed PHP Deprecated error for non-static method being called statically in the Term_Editor class;
* Fixed: Fixed the notice in the frontend saying the is_author was called incorrectly;
* Fixed: Empty output for shortcodes if the layout doesn't exists. Added fallback layout and an error message in the error log;
* Fixed: Wrong author data in the query for authors mapped to user;
* Fixed: The get_author_posts_url function was not working for guest authors;
* Fixed: The get_the_author_meta function was not working for guest authors;
* Fixed: The get_the_author_posts_link function was not working for guest authors;
* Fixed: The get_the_author function was not working for guest authors;
* Fixed: The the_post function was not working well for posts with guest authors;
* Fixed: The feed_links_extra function was not working for guest authors;
* Fixed: For themes that dont't implement support for PublishPress Authors and multiple authors, we were displaying all the author names separated by comma, but only one link (due to the limitations imposed by the theme). Now we display only the first author and its respective link on those cases. The multiple authors can be added creating a child theme and adapting the code;
* Fixed: The title for authors archive pages of guest authors;
* Fixed: The author object is now compatible with the main properties of WP_User objects, so guest authors can be treated as users on most cases;
* Fixed: The custom user_url is not returned for authors mapped to user;

= [3.2.4] - 2020-04-13 =

* Added: Button to migrate data from the Bylines (Daniel Bachhuber) plugin;
* Added: Button to migrate data from the Byline (Matt Dulin) plugin;
* Added: Added a body class for guest authors "author-<author_slug>" (#45);
* Fixed: Fixed the error displayed on Windows servers when the constant DIRECTORY_SEPARATOR is not defined;
* Fixed: Fixed compatibility with composer based sites;
* Fixed: Broken body class for guest authors "author-" (#43);
* Fixed: Wrong authors in the header of pages based on Genesis framework (#46);
* Fixed: Empty author headline for guest authors (#47);
* Fixed: Fixed some texts and style in the Co-authors plugin migration box;
* Fixed: Wrong author data in the Yoast SEO schema for structured data (#77);
* Fixed: Fixed author page title when using Yoast SEO (#80);
* Fixed: Fix the result of the function get_the_author_posts_link for supporting multiple authors;
* Changed: Renamed the name of the Widget, from Multiple Authors to Authors;

= [3.2.3] - 2020-03-16 =

* Fixed: Wrong URL for the file multiple-authors-widget.css;
* Fixed: Fixed the author page for compatibility to the Genesis framework;
* Added: Added new filter to bypass the installation and data migration on special cases;
* Added: Add top banner for the Pro version;

= [3.2.2] - 2020-02-25 =

* Fixed: Undefined class Authors_Iterator, #26;
* Fixed: Error message related to Phing class file not found. Removed Phing from the package;

= [3.2.1] - 2020-02-13 =

* Fixed: Fixed the query for migrating posts' authors when installed for the first time;
* Fixed: Fixed the assets URL for the plugin when it is installed in a folder different from wp-content/plugins
* Fixed: Fixed the count of authors' posts using the correct field in the query: term_id. Issue #17;
* Fixed: Fixed the query in the installer that look for posts without author's taxonomy to migrate;
* Added: Added actions for before and after the settings fields: publishpress_authors_register_settings_before, publishpress_authors_register_settings_after;
* Added: Added new filter: pp_authors_twig for extending the Twig environment object;
* Changed: Cleanup the installer class;
* Changed: Removed the CMB2 library since it is only used in the Pro plugins;
* Changed: Refactored the code to support the Pro version;
* Removed: Removed the CMB2 library dependency;

= [3.2.0] - 2020-01-03 =

* First free public release. Based on PublishPress Multiple Authors v3.1.0.
