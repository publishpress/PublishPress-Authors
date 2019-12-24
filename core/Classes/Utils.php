<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthors\Classes;

use MultipleAuthors\Classes\Legacy\Util;
use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Factory;
use WP_Error;

/**
 * Utility methods for managing authors
 *
 * Based on Bylines.
 *
 * @package MultipleAuthors\Classes
 *
 */
class Utils
{

    /**
     * @var array
     */
    protected static $supported_post_types = [];

    /**
     * @var array
     */
    protected static $pages_whitelist = [
        'post.php',
        'post-new.php',
        'edit.php',
        'edit-tags.php',
        'term.php',
        'admin.php',
    ];

    /**
     * Convert co-authors to authors on a post.
     *
     * Errors if the post already has authors. To re-convert, remove authors
     * from the post.
     *
     * @param integer $post_id ID for the post to convert.
     *
     * @return object|WP_Error Result object if successful; WP_Error on error.
     */
    public static function convert_post_coauthors($post_id)
    {
        if ( ! function_exists('get_coauthors')) {
            return new WP_Error('authors_missing_cap',
                __('Co-Authors Plus must be installed and active.', 'publishpress-authors'));
        }
        $post = get_post($post_id);
        if ( ! $post) {
            return new WP_Error('authors_missing_post', "Invalid post: {$post_id}");
        }
        $authors = get_the_terms($post_id, 'author');
        if ($authors && ! is_wp_error($authors)) {
            return new WP_Error('authors_post_has_authors', "Post {$post_id} already has authors.");
        }
        $authors          = [];
        $result           = new \stdClass;
        $result->created  = 0;
        $result->existing = 0;
        $result->post_id  = 0;
        $coauthors        = get_coauthors($post_id);
        foreach ($coauthors as $coauthor) {
            switch ($coauthor->type) {
                case 'wpuser':
                    $author = Author::get_by_user_id($coauthor->ID);
                    if ($author) {
                        $authors[] = $author;
                        $result->existing++;
                    } else {
                        $author = Author::create_from_user($coauthor->ID);
                        if (is_wp_error($author)) {
                            return $author;
                        }
                        $authors[] = $author;
                        $result->created++;
                    }
                    break;
                case 'guest-author':
                    $author = Author::get_by_term_slug($coauthor->user_nicename);
                    if ($author) {
                        $authors[] = $author;
                        $result->existing++;
                    } else {
                        $args   = [
                            'display_name' => $coauthor->display_name,
                            'slug'         => $coauthor->user_nicename,
                        ];
                        $author = Author::create($args);
                        if (is_wp_error($author)) {
                            return $author;
                        }
                        $ignored = [
                            'ID',
                            'display_name',
                            'user_nicename',
                            'user_login',
                        ];
                        foreach ($coauthor as $key => $value) {
                            if (in_array($key, $ignored, true)) {
                                continue;
                            }
                            if ('linked_account' === $key) {
                                $key   = 'user_id';
                                $user  = get_user_by('login', $value);
                                $value = $user ? $user->ID : '';
                            }
                            if ('' !== $value) {
                                update_term_meta($author->term_id, $key, $value);
                            }
                        }
                        $authors[] = $author;
                        $result->created++;
                    }
                    break;
            } // End switch().
        } // End foreach().
        if (empty($authors) || count($coauthors) !== count($authors)) {
            return new WP_Error('authors_post_missing_coauthors',
                "Failed to convert some authors for post {$post_id}.");
        }
        Utils::set_post_authors($post_id, $authors);

        return $result;
    }

    /**
     * Set the authors for a post
     *
     * @param integer $post_id ID for the post to modify.
     * @param array   $authors Bylines to set on the post.
     */
    public static function set_post_authors($post_id, $authors)
    {
        static::set_post_authors_name_meta($post_id, $authors);

        $authors = wp_list_pluck($authors, 'term_id');
        wp_set_object_terms($post_id, $authors, 'author');
    }

    /**
     * Save a metadata with the post authors' name. Add compatibility to
     * Yoast for using in the custom title, and other 3rd party plugins.
     *
     * @param $post_id
     * @param $authors
     */
    protected static function set_post_authors_name_meta($post_id, $authors)
    {
        if ( ! is_array($authors)) {
            $authors = [];
        }

        $metadata = 'ppma_authors_name';

        if (empty($authors)) {
            delete_post_meta($post_id, $metadata);
        } else {
            $names = [];

            foreach ($authors as $author) {
                if ( ! is_object($author) && is_numeric($author)) {
                    $author = Author::get_by_term_id($author);
                }

                $names[] = $author->name;
            }

            $names = implode(', ', $names);

            update_post_meta($post_id, $metadata, $names);
        }
    }

    /**
     * Helper to only add javascript to necessary pages. Avoids bloat in admin.
     *
     * @return bool
     */
    public static function is_valid_page()
    {
        global $pagenow;


        $valid = (bool)in_array($pagenow, self::$pages_whitelist);


        if ( ! $valid) {
            return false;
        }

        if (in_array($pagenow, ['edit-tags.php', 'term.php'])) {
            $taxonomy = isset($_GET['taxonomy']) ? $_GET['taxonomy'] : null;

            if ('author' !== $taxonomy) {
                return false;
            }
        } elseif (in_array($pagenow, ['admin.php'])) {
            if (isset($_GET['page']) && $_GET['page'] === 'ppma-modules-settings' && isset($_GET['module']) && $_GET['module'] === 'multiple-authors-settings') {
                return true;
            }
        } else {
            return self::is_post_type_enabled() && self::current_user_can_set_authors();
        }

        return true;
    }

    /**
     * Whether or not PublishPress Authors is enabled for this post type
     * Must be called after init
     *
     * @param string $post_type The name of the post type we're considering
     *
     * @return bool Whether or not it's enabled
     * @since 3.0
     *
     */
    public static function is_post_type_enabled($post_type = null)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        if (empty(self::$supported_post_types)) {
            self::$supported_post_types = self::get_supported_post_types();
        }

        if ( ! $post_type) {
            $post_type = Util::get_current_post_type();
        }

        $supported = (bool)in_array($post_type, self::$supported_post_types);

        $post_types = Util::get_post_types_for_module($legacyPlugin->multiple_authors->module);

        $is_enabled = (bool)in_array($post_type, $post_types);

        return $supported && $is_enabled;
    }

    /**
     * Returns a list of post types which supports authors.
     */
    public static function get_supported_post_types()
    {
        if (empty(self::$supported_post_types)) {
            // Get the post types which supports authors
            $post_types_with_authors = array_values(get_post_types());
            // Get post types which doesn't support authors, but should support Multiple Authors.
            $thirdPartySupport = [
                // LearnPress
                'lp_course',
                'lp_lesson',
                'lp_quiz',
            ];


            foreach ($post_types_with_authors as $key => $name) {
                // Ignore some 3rd party post types.
                if (in_array($name, $thirdPartySupport)) {
                    continue;
                }

                if ( ! post_type_supports($name, 'author') || in_array($name, ['revision', 'attachment'])) {
                    unset($post_types_with_authors[$key]);
                }
            }

            self::$supported_post_types = apply_filters('coauthors_supported_post_types', $post_types_with_authors);
        }

        /**
         * Modify post types that use authors.
         *
         * @param array $post_types_with_authors Post types that support authors.
         */
        self::$supported_post_types = apply_filters('authors_post_types', self::$supported_post_types);

        return self::$supported_post_types;
    }

    /**
     * Checks to see if the current user can set authors or not
     */
    public static function current_user_can_set_authors($post = null)
    {
        if ( ! $post) {
            $post = get_post();
            if ( ! $post) {
                if (isset($_GET['post'])) {
                    $post = get_post($_GET['post']);
                } else {
                    return false;
                }
            }
        }

        $post_type = $post->post_type;

        // TODO: need to fix this; shouldn't just say no if don't have post_type
        if ( ! $post_type) {
            return false;
        }


        $current_user = wp_get_current_user();

        if ( ! $current_user) {
            return false;
        }
        // Super admins can do anything
        if (function_exists('is_super_admin') && is_super_admin()) {
            return true;
        }

        $can_set_authors = isset($current_user->allcaps['edit_others_posts']) ? $current_user->allcaps['edit_others_posts'] : false;

        if ( ! $can_set_authors) {
            $can_set_authors = isset($current_user->allcaps['ppma_edit_orphan_post']) ? $current_user->allcaps['ppma_edit_orphan_post'] : false;
        }

        return apply_filters('coauthors_plus_edit_authors', $can_set_authors);
    }

    /**
     * Written because WP is_plugin_active() requires plugin folder in arg
     *
     * @param string $check_plugin_file
     *
     * @return bool|mixed
     */
    public static function isPluginActive($check_plugin_file)
    {
        if ( ! is_multisite()) {
            $plugins = (array)get_option('active_plugins');
            foreach ($plugins as $plugin_file) {
                if (false !== strpos($plugin_file, $check_plugin_file)) {
                    return $plugin_file;
                }
            }
        } else {
            $plugins = (array)get_site_option('active_sitewide_plugins');

            // network activated plugin names are array keys
            foreach (array_keys($plugins) as $plugin_file) {
                if (false !== strpos($plugin_file, $check_plugin_file)) {
                    return $plugin_file;
                }
            }
        }

        return false;
    }
}
