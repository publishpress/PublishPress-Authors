<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthors\Classes\Integrations;

use MultipleAuthors\Classes\Legacy\Util;

/**
 * Filter standard theme template tags.
 *
 * Based on Bylines
 *
 * @package MultipleAuthors\Classes
 */
class Theme
{

    /**
     * Filter get_the_archive_title() to use author on author archives
     *
     * @param string $title Original archive title.
     *
     * @return string
     */
    public static function filter_get_the_archive_title($title)
    {
        if (!Util::isAuthor()) {
            return $title;
        }

        /* translators: Author archive title. 1: Author name */

        return sprintf(
            __('Author: %s', 'publishpress-authors'),
            '<span class="vcard">' . get_queried_object()->display_name . '</span>'
        );
    }

    /**
     * Filter get_the_archive_description() to use author on author archives
     *
     * @param string $description Original archive description.
     *
     * @return string
     */
    public static function filter_get_the_archive_description($description)
    {
        if (!Util::isAuthor()) {
            return $description;
        }

        return get_queried_object()->description;
    }
}
