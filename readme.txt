=== PublishPress Authors: Multiple Authors, Guest Authors, Co-Authors in WordPress ===

Contributors: publishpress, kevinB, stevejburge, andergmartins
Author: PublishPress
Author URI: https://publishpress.com
Tags: multiple authors, authors, guest authors, author fields, author layouts
Requires at least: 4.7
Requires PHP: 5.4
Tested up to: 5.3
Stable tag: 3.2.2
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
