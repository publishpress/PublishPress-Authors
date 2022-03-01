<?php
/**
 * @package     MultipleAuthors\
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

namespace MultipleAuthors;

defined('ABSPATH') or die('No direct script access allowed.');


abstract class Capability
{
    public static function getManageAuthorsCapability()
    {
        return apply_filters('pp_multiple_authors_manage_authors_cap', 'ppma_manage_authors');
    }

    public static function getManageOptionsCapability()
    {
        return apply_filters('pp_multiple_authors_manage_settings_cap', 'manage_options');
    }

    public static function getEditPostAuthorsCapability()
    {
        return apply_filters('pp_multiple_authors_edit_post_authors', 'ppma_edit_post_authors');
    }

    public static function currentUserCanManageSettings()
    {
        return current_user_can(self::getManageOptionsCapability());
    }

    public static function currentUserCanManageAuthors()
    {
        return current_user_can(self::getManageAuthorsCapability());
    }

    public static function currentUserCanEditPostAuthors()
    {
        return current_user_can(self::getEditPostAuthorsCapability());
    }
}
