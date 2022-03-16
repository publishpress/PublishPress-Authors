<?php
/**
 * @package     MultipleAuthors\
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 *
 * The IDE can probably display the following aliases as Ignored Class Aliases Declarations,
 * but that is happening because we created a .ide.php file defining those classes with the
 * a deprecated attribute in the comments, for the IDE is able to recognize it as deprecated.
 * Just ignore the warning.
 */

// Class aliases for backward compatibility
use MultipleAuthors\Classes\Authors_Iterator;

class_alias(MultipleAuthors\Plugin::class, PP_Multiple_authors_plugin::class);
class_alias(MultipleAuthors\Container::class, PublishPress\Addon\Multiple_authors\Container::class);
class_alias(MultipleAuthors\Factory::class, PublishPress\Addon\Multiple_authors\Factory::class);
class_alias(MultipleAuthors\Services::class, PublishPress\Addon\Multiple_authors\Services::class);
class_alias(MultipleAuthors\Widget::class, PublishPress\Addon\Multiple_authors\Widget::class);
//class_alias(MultipleAuthors\WP_Cli::class, PublishPress\Addon\Multiple_authors\WP_Cli::class);
class_alias(MultipleAuthors\Classes\Admin_Ajax::class, PublishPress\Addon\Multiple_authors\Classes\Admin_Ajax::class);
class_alias(
    MultipleAuthors\Classes\Author_Editor::class,
    PublishPress\Addon\Multiple_authors\Classes\Author_Editor::class
);
class_alias(
    MultipleAuthors\Classes\Authors_Iterator::class,
    PublishPress\Addon\Multiple_authors\Classes\Authors_Iterator::class
);
class_alias(
    Authors_Iterator::class,
    'Multiple_authors_iterator'
);
class_alias(MultipleAuthors\Classes\CLI::class, PublishPress\Addon\Multiple_authors\Classes\CLI::class);
class_alias(
    MultipleAuthors\Classes\Content_Model::class,
    PublishPress\Addon\Multiple_authors\Classes\Content_Model::class
);
class_alias(MultipleAuthors\Classes\Installer::class, PublishPress\Addon\Multiple_authors\Classes\Installer::class);
class_alias(MultipleAuthors\Classes\Post_Editor::class, PublishPress\Addon\Multiple_authors\Classes\Post_Editor::class);
class_alias(MultipleAuthors\Classes\Query::class, PublishPress\Addon\Multiple_authors\Classes\Query::class);
class_alias(MultipleAuthors\Classes\Utils::class, PublishPress\Addon\Multiple_authors\Classes\Utils::class);
class_alias(
    MultipleAuthors\Classes\Objects\Author::class,
    PublishPress\Addon\Multiple_authors\Classes\Objects\Author::class
);
class_alias(
    MultipleAuthors\Classes\Integrations\Theme::class,
    PublishPress\Addon\Multiple_authors\Classes\Integrations\Theme::class
);

if (!defined('PUBLISHPRESS_MULTIPLE_AUTHORS_VERSION')) {
    define('PUBLISHPRESS_MULTIPLE_AUTHORS_VERSION', PP_AUTHORS_VERSION);
}
