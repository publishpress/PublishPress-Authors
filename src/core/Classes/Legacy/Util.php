<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthors\Classes\Legacy;

use MultipleAuthors\Factory;

class Util
{
    /**
     * Checks for the current post type
     *
     * @return string|null $post_type The post type we've found, or null if no post type
     *
     * @deprecated getCurrentPostType
     */
    public static function get_current_post_type()
    {
        return static::getCurrentPostType();
    }

    /**
     * Checks for the current post type
     *
     * @return string|null $post_type The post type we've found, or null if no post type
     */
    public static function getCurrentPostType()
    {
        global $post, $typenow, $pagenow, $current_screen;

        $post_id = isset($_REQUEST['post']) ? (int)$_REQUEST['post'] : false;

        $post_type = null;

        if ($post && $post->post_type) {
            $post_type = static::getPostPostType($post);
        } elseif ($typenow) {
            $post_type = $typenow;
        } elseif ($current_screen && !empty($current_screen->post_type)) {
            $post_type = $current_screen->post_type;
        } elseif (isset($_REQUEST['post_type']) && !empty($_REQUEST['post_type'])) {
            $post_type = sanitize_key($_REQUEST['post_type']);
        } elseif ('post.php' == $pagenow && !empty($post_id)) {
            $post_type = static::getPostPostType($post_id);
        } elseif ('edit.php' == $pagenow && empty($_REQUEST['post_type'])) {
            $post_type = 'post';
        } elseif (self::isAuthor()) {
            $post_type = 'post';
        }

        return $post_type;
    }

    /**
     * @param \WP_Post|int $postOrPostId
     *
     * @return string|false
     */
    public static function getPostPostType($postOrPostId)
    {
        $post = null;

        if (is_numeric($postOrPostId)) {
            $postOrPostId = (int)$postOrPostId;

            if (!empty($postOrPostId)) {
                $post = get_post($postOrPostId);
            }
        } else {
            $post = $postOrPostId;
        }

        if (!$post instanceof \WP_Post) {
            return false;
        }

        return $post->post_type;
    }

    /**
     * @return bool|void
     */
    public static function isAuthor()
    {
        global $wp_query;

        if (!isset($wp_query)) {
            return;
        }

        return is_author();
    }

    /**
     * Collect all of the active post types for a given module
     *
     * @param object $module Module's data
     *
     * @return array $post_types All of the post types that are 'on'
     */
    public static function get_post_types_for_module($module)
    {
        $post_types = [];

        if (isset($module->options->post_types) && is_array($module->options->post_types)) {
            foreach ($module->options->post_types as $post_type => $value) {
                if ('on' === $value) {
                    $post_types[] = $post_type;
                }
            }
        }

        return $post_types;
    }

    public static function get_selected_post_types()
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        return self::get_post_types_for_module($legacyPlugin->modules->multiple_authors);
    }

    /**
     * Sanitizes the module name, making sure we always have only
     * valid chars, replacing - with _.
     *
     * @param string $name
     *
     * @return string
     */
    public static function sanitize_module_name($name)
    {
        return str_replace('-', '_', $name);
    }

    /**
     * Adds an array of capabilities to a role.
     *
     * @param string $role A standard WP user role like 'administrator' or 'author'
     * @param array $caps One or more user caps to add
     * @since 1.9.8
     *
     */
    public static function add_caps_to_role($role, $caps)
    {
        // In some contexts, we don't want to add caps to roles
        if (apply_filters('ppma_kill_add_caps_to_role', false, $role, $caps)) {
            return;
        }

        global $wp_roles;

        if ($wp_roles->is_role($role)) {
            $role = get_role($role);

            foreach ($caps as $cap) {
                $role->add_cap($cap);
            }
        }
    }

    /**
     * @return bool
     */
    public static function isGutenbergEnabled()
    {
        $isEnabled = defined('GUTENBERG_VERSION');

        // Is WordPress 5?
        if (!$isEnabled) {
            $wpVersion = get_bloginfo('version');

            $isEnabled = version_compare($wpVersion, '5.0', '>=');
        }

        return $isEnabled;
    }
}
