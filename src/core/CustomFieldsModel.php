<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

namespace MultipleAuthors;

// Prevent direct access.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Plugin main class file.
 *
 * @package     MultipleAuthors\Classes
 * @author      UpStream <https://upstreamplugin.com>
 * @copyright   Copyright (c) 2018 UpStream Project Management
 * @license     GPL-3
 * @since       1.0.0
 * @final
 */
final class CustomFieldsModel
{
    /**
     * Retrieve all supported field types.
     *
     * @return  array
     * @since   1.0.0
     * @static
     *
     */
    public static function getFieldTypes()
    {
        $fieldTypes = [
            'text' => __('Text', 'publishpress-authors'),
            'textarea' => __('Multiline text', 'publishpress-authors'),
            'wysiwyg' => __('WYSIWYG Editor', 'publishpress-authors'),
            'url' => __('Link', 'publishpress-authors'),
            'email' => __('Email address', 'publishpress-authors'),
        ];

        return $fieldTypes;
    }

    /**
     * Retrieve all supported field status.
     *
     * @return  array
     * @since   4.0.0
     * @static
     *
     */
    public static function getFieldStatus()
    {
        $fieldStatus = [
            'on'   => __('Active', 'publishpress-authors'),
            'off'  => __('Disabled', 'publishpress-authors'),
        ];

        return $fieldStatus;
    }

    /**
     * Retrieve all supported field requirement.
     *
     * @return  array
     * @since   4.0.0
     * @static
     *
     */
    public static function getFieldRequirment()
    {
        $fieldStatus = [
            ''          => __('Optional', 'publishpress-authors'),
            'required'  => __('Required', 'publishpress-authors'),
        ];

        return $fieldStatus;
    }

    /**
     * Retrieve all supported field social profile.
     *
     * @return  array
     * @since   4.1.2
     * @static
     *
     */
    public static function getFieldSocialProfile()
    {
        $fieldStatus = [
            0   => __('No', 'publishpress-authors'),
            1   => __('Yes, this is a Social Profile', 'publishpress-authors'),
        ];

        return $fieldStatus;
    }
}
