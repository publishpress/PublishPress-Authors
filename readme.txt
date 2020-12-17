=== PublishPress Authors: Show Multiple Authors and Guest Authors in an Author Box ===

Contributors: publishpress, kevinB, stevejburge, andergmartins
Author: PublishPress
Author URI: https://publishpress.com
Tags: multiple authors, authors, guest authors, author fields, author layouts
Requires at least: 4.7
Requires PHP: 5.6
Tested up to: 5.6
Stable tag: 3.10.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

PublishPress Authors is the best plugin for adding multiple authors and guest authors to WordPress posts. You can show authors with a box, widget, shortcode or Gutenberg block.

== Description ==

[PublishPress Authors](https://publishpress.com/authors/) allows you to show an author box at the end of your posts. This author box can display one author, multiple authors or even guest authors. This box has the author’s name, avatar, description and more. You can also place the author box in widgets, shortcodes and Gutenberg blocks.

Here are the three most important features of PublishPress Authors:

* **Author Box**. As soon as you install PublishPress Authors, you’ll see an author box under every post. You can add all the profile details you need for each author.
* **Multiple Authors**. By default, WordPress only allows one author per post. PublishPress Authors allows you to add an unlimited number of authors to each post. This is very useful if you have a busy site and need to manage and give credit to all your writers.
* **Guest Authors**. You can create Guest Authors who don’t have an account on your site. This is important because not all writers need a username and password.

= Multiple Authors and Co-Authors =

With PublishPress Authors, you can set multiple authors for each post. When you write a post, you’ll see a box in the right sidebar. Here you can choose from all the users on your site, and assign them as authors.

On the frontend of your site. PublishPress Authors gives you several different options to display the authors’ box:
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
[Click here to see details on all the layout options](https://publishpress.com/knowledge-base/layout/)
= Custom Fields for Author Profiles (Pro version) =

PublishPress Authors Pro enables you to create custom fields and enhance your author profiles.
You can add Text, WYSIWYG, Link and email address fields. Then you can place those fields in author profiles using custom layouts.
= Custom Layouts for Author Profiles (Pro version) =

PublishPress Authors enables you to build custom layouts for your author profiles.
Using all your author information and custom fields, you can design beautiful layouts for your authors. Each layout is editable using HTML and PHP. You can add many different types of author information to these layouts including custom fields.
[Click here to see how to customize author layouts](https://publishpress.com/knowledge-base/custom-layouts/).
= Join PublishPress and get the Pro plugins =
The Pro versions of the PublishPress plugins are well worth your investment. The Pro versions have extra features and faster support. [Click here to join PublishPress](https://publishpress.com/pricing/).
Join PublishPress and you’ll get access to these plugins:
* [Advanced Gutenberg](https://publishpress.com/blocks) add over 20 layout options, sliders, buttons, icons, image galleries, maps, tabs, testimonials, accordions, and much more.
* [PublishPress Authors Pro](https://publishpress.com/authors) allows you to add multiple authors and guest authors to WordPress posts
* [PublishPress Capabilities Pro](https://publishpress.com/capabilities) is the plugin to manage your WordPress user roles, permissions, and capabilities.
* [PublishPress Checklists Pro](https://publishpress.com/checklists) enables you to define tasks that must be completed before content is published.
* [PublishPress Permissions Pro](https://publishpress.com/permissions)  is the plugin for advanced WordPress permissions.
* [PublishPress Pro](https://publishpress.com/publishpress) is the plugin for managing and scheduling WordPress content.
* [PublishPress Revisions Pro](https://publishpress.com/revisions) allows you to update your published pages with teamwork and precision.

Together, these plugins are a suite of powerful publishing tools for WordPress. If you need to create a professional workflow in WordPress, with moderation, revisions, permissions and more … then you should try PublishPress.

=  Bug Reports =
Bug reports for PublishPress Authors are welcomed in our [repository on GitHub](https://github.com/publishpress/publishpress-authors). Please note that GitHub is not a support forum, and that issues that aren’t properly qualified as bugs will be closed.
= Follow the PublishPress team =
Follow PublishPress on [Facebook](https://www.facebook.com/publishpress), [Twitter](https://www.twitter.com/publishpresscom) and [YouTube](https://www.youtube.com/publishpress).

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
- Go to admin page, click on the "Authors" menu and create new author profiles.
- Go to write a new post and you'll see the box for selecting multiple authors in the sidebar.

== Changelog ==

= [3.10.0] - 2020-12-15 =

* Fixed: Changed the way we sync post_author column: Current user will only be set as author if no terms where found for the post, or there are only guest authors. If post_author is empty, we set it for the current user, creating an author term for it, #286.
* Fixed: Duplicated queries for the same given email in the method MultipleAuthors\Classes\Author_Utils::get_author_term_id_by_email(). Added a cache for the query results and an option to ignore the cache, #293;
* Fixed: Performance issue. Optimized some methods and modules loading for reducing the server overload and reduce duplicated queries. Some modules now are only loaded if the required plugin is installed, #297;
* Fixed: Fix the path for the template-tags.php file when called by the author box, if not loaded yet;
* Fixed: Only register admin hooks if in the admin, #297;
* Fixed: Fixed JS warning about variable being implicitly defined;
* Fixed: Fixed compatibility issue with Select2 library loaded by WS Form Plugin, #292;
* Fixed: Improved performance when opening the post edit page and quick edit panel for sites with thousands of authors;
* Changed: Deprecated functions and classes now can be disabled if you define the constant "PUBLISHPRESS_AUTHORS_LOAD_DEPRECATED_LEGACY_CODE" as false. Default is true, #297;
* Changed: CoAuthors' backward compatibility functions now can be disabled if you define the constant "PUBLISHPRESS_AUTHORS_LOAD_COAUTHORS_FUNCTIONS" as false. Default is true, #297;
* Changed: Bylines' backward compatibility functions now can be disabled if you define the constant "PUBLISHPRESS_AUTHORS_LOAD_BYLINES_FUNCTIONS" as false. Default is true, #297;
* Added: Added new maintenance task for syncing the authors' slug with the respective user's sanitized login (user_nicename). There is a new constant "PUBLISHPRESS_AUTHORS_SYNC_AUTHOR_SLUG_CHUNK_SIZE" for customizing the size of the chunk of authors to update at a time (default to 50), #287;
* Added: Added new constant "PUBLISHPRESS_AUTHORS_SYNC_POST_AUTHOR_CHUNK_SIZE" for defining the size of the chunck of posts to convert authors in the maintenance task: Update author field on posts. Default to 10;
* Removed: Removed the support to the filter "coauthors_auto_apply_template_tags", #297;

= [3.9.0] - 2020-11-24 =

* Added: Added support to Bulk Edit for authors in the post list, #263 and #280;
* Fixed: Fixed maintenance tasks to consider all the selected post types and not "post" only, #276;
* Fixed: Fixed compatibility issue with the WP RSS Aggregator plugin, #278;
* Fixed: Restored the posts count in the Authors and Users list, #275;

= [3.8.1] - 2020-11-05 =

* Fixed: Fixed the consistency of avatar dimensions between the img tag attributes and the CSS, #258;
* Fixed: Fixed edit_posts permission check for the PublishPress calendar, #264;
* Fixed: Restored the post count column in the Authors list, #95;

= [3.8.0] - 2020-10-08 =

* Fixed: Fixed PHP warning about undefined "default_author_for_new_posts" attribute for the module options;
* Fixed: Fixed the empty setting field "Default author for new posts", #242;
* Fixed: Fixed empty post_author on posts saved without any author. The current user will be added as the author, #238;
* Fixed: Fixed post_author field on posts when saving posts to store the user ID of the first author, ignoring guest authors, #171;
* Fixed: Fixed support for authors and guest authors in the PublishPress' calendar and content overview filters, #249;
* Added: Added new maintenance task to sync post_author with author terms for all posts, #171;
* Added: Added basic support for multiple authors in the Ultimate Members plugin's posts, #251;

= [3.7.3] - 2020-09-21 =

* Fixed: Fixed unresponsive author select box for new posts, #244;

= [3.7.2] - 2020-09-14 =

* Fixed: Fixed the reordering issue on authors in the post edit page;

= [3.7.1] - 2020-09-11 =

* Fixed: Fixed the authors field in the quick edit panel. It was displaying all authors instead of only the post authors, #236;

= [3.7.0] - 2020-09-10 =

* Fixed: Fixed performance issue in the post list and edit page removing avatars from the authors fields, #227;
* Added: Added option to change the default author for new posts in the site, #50;

= [3.6.3] - 2020-09-04 =

* Fixed: Fix error "Uncaught Error: Call to a member function add_cap() on null", #223;

= [3.6.2] - 2020-09-03 =

* Fixed: Fix error "Call to a member function get_error_message() on boolean", a regression bug result of the recent updates, #221;

= [3.6.1] - 2020-09-03 =

* Fixed: Fix admin notice for Co-Authors Plus displaying even when the plugin is not installed;

= [3.6.0] - 2020-09-02 =

* Added: Added support to update authors for posts using the quick edit form, #180;
* Added: Added argument "$ignoreCache" to the get_multiple_authors;
* Added: Added new capability (ppma_edit_post_authors) to control who can edit post authors, #213;
* Added: Added an admin notice if Co-Authors Plus is installed asking to read the documentation for migrating data, #209;
* Fixed: Removed mentions to the old name: Multiple Authors;
* Fixed: Error message "The plugin does not have a valid header" in PHP 5.6, #215;
* Fixed: Optimize performance in the get_multiple_authors again, replacing a function call with a specific db query; #190;
* Fixed: Fixed Co-Authors Plus data migration after installing. We still require to manually run the maintenance task to migrate the data;
* Fixed: Fixed get_multiple_authors cache when no arguments are passed to the functions;
* Fixed: Fixed fatal error that happens when get_term returns an error;
* Fixed: Upgrade link and banner were displayed for all users with access to the admin, #208;

= [3.5.1] - 2020-08-20 =

* Fixed: Avoid warnings regarding constants already defined;
* Fixed: Fixed the cache for the get_multiple_authors function for archive pages, #190;
* Fixed: Fixed fatal error Object of class WP_Error could not be converted to string, #182;
* Fixed: Fixed the value for $author->display_name which was returning the value from the user object instead of the custom value set for the author, #183;
* Fixed: Fixed Plugin::filter_user_has_cap() is passing a param to Util::get_current_post_type() which doesn't support params, #187;
* Fixed: Fixed Plugin::filter_user_has_cap() to use the correct user, not the current one, #186;
* Fixed: Removed leftovers from the deprecated capability: ppma_edit_orphan_post, #193;

= [3.5.0] - 2020-08-06 =

* Added: Added a new widget to display all the authors, #76;
* Added: Added option to display the username in the authors search field, #162;
* Fixed: Fix compatibility with WooCommerce products, #169;
* Fixed: Performance issue in the frontend. Added cache for queries that can run multiple times in the frontend, #171;
* Fixed: Fix PHP notice on author page when user is not an author, #156;
* Fixed: Fixed notice when a post doesn't exist after deleting the post, #167;

= [3.4.0] - 2020-07-23 =

* Added: Add new filter "publishpress_authors_author_attribute" for customizing author attributes in the layouts;
* Fixed: Fix syntax on the file Author_Editor.php removing an invalid char;

= [3.3.2] - 2020-07-13 =

* Fixed: Fix the text domain loading, fixing the translations;
* Fixed: Fix "orphan" authors when the mapped user is deleted, converting them in guest authors, #142;
* Fixed: Fix infinity loop when user's and author's slug are different and you are trying to save an author profile, #143;
* Fixed: Fix hardcoded table prefix from a query, #146;
* Fixed: Fix error about missed Authors_Iterator class, #144;
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
* Fixed: For themes that don't implement support for PublishPress Authors and multiple authors, we were displaying all the author names separated by comma, but only one link (due to the limitations imposed by the theme). Now we display only the first author and its respective link on those cases. The multiple authors can be added creating a child theme and adapting the code;
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
