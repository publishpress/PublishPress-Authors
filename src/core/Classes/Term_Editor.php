<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthors\Classes;

use MultipleAuthors\Classes\Objects\Author;

/**
 * Class Term_Editor
 *
 * @package MultipleAuthors\Classes
 */
class Term_Editor
{
    /**
     * Register callbacks for managing custom columns
     */
    public static function action_admin_init()
    {
        add_filter("manage_author_custom_column", [__CLASS__, 'filter_manage_custom_column'], 10, 3);
        add_filter('manage_edit-author_columns', [__CLASS__, 'filter_columns']);
    }

    /**
     * @param string $column Name of the column.
     * @param int $post_id ID of the post being rendered.
     */
    public static function filter_manage_custom_column($content, $column, $term_id)
    {
        if ('author_url' === $column) {
            $author = Author::get_by_term_id($term_id);

            $content = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                $author->link,
                urldecode($author->slug)
            );
        }

        return $content;
    }

    public static function filter_columns($columns)
    {
        if (isset($columns['slug'])) {
            unset($columns['slug']);
        }

        if (!isset($columns['author_url'])) {
            $columns['author_url'] = __('Author URL', 'publishpress-authors');
        }

        return $columns;
    }
}
