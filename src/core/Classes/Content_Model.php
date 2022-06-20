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
use MultipleAuthors\Factory;

/**
 * Declaration of the content model
 */
class Content_Model
{
    /**
     * Register taxonomies to objects after post types have been registered
     */
    public static function action_init_late_register_taxonomy_for_object_type()
    {
        foreach (self::get_author_supported_post_types() as $post_type) {
            register_taxonomy_for_object_type('author', $post_type);
        }
    }

    /**
     * Get the supported post types for authors
     */
    public static function get_author_supported_post_types()
    {
        return Utils::get_post_types_that_support_authors();
    }

    /**
     * Filter author term links to look like author links
     *
     * @param string $link Term link URL.
     * @param object $term Term object.
     * @param string $taxonomy Taxonomy slug.
     *
     * @return string
     */
    public static function filter_term_link($link, $term, $taxonomy)
    {
        global $wp_rewrite;

        if ('author' !== $taxonomy) {
            return $link;
        }

        $legacyPlugin = Factory::getLegacyPlugin();

        if (!empty($legacyPlugin) && isset($legacyPlugin->multiple_authors)
            && isset($legacyPlugin->modules->multiple_authors->options->enable_plugin_author_pages)
            && $legacyPlugin->modules->multiple_authors->options->enable_plugin_author_pages === 'yes'
        ) {
            $enable_authors_profile = true;
        } else {
            $enable_authors_profile = false;
        }

        if (!$enable_authors_profile) {
            $author      = Author::get_by_term_id($term->term_id);
            $author_slug = is_object($author) ? $author->slug : '';
            $permastruct = $wp_rewrite->get_author_permastruct();

            if ($permastruct) {
                $link = str_replace('%author%', $author_slug, $permastruct);
                $link = home_url(user_trailingslashit($link));
            } else {
                $link = add_query_arg('author_name', rawurlencode($author_slug), home_url() . '/');
            }
        }

        return $link;
    }

    /**
     * Filter author term links to look like author links
     *
     * @param $link
     * @param $author_id
     *
     * @return string
     */
    public static function filter_author_link($link, $author_id)
    {
        global $wp_rewrite;

        $legacyPlugin = Factory::getLegacyPlugin();

        if (!empty($legacyPlugin) && isset($legacyPlugin->multiple_authors)
            && isset($legacyPlugin->modules->multiple_authors->options->enable_plugin_author_pages)
            && $legacyPlugin->modules->multiple_authors->options->enable_plugin_author_pages === 'yes'
        ) {
            $enable_authors_profile = true;
        } else {
            $enable_authors_profile = false;
        }

        $permastruct = $wp_rewrite->get_author_permastruct();

        if (empty($permastruct) && !$enable_authors_profile) {
            return $link;
        }

        // We probably have a call for a guest author if the author_id is a string
        if ($author_id < 0) {
            // Try to identify the current author.
            $author = Author::get_by_term_id(abs($author_id ));

            if (is_object($author)) {
                return $author->link;
            }
        }

        if ($enable_authors_profile) {
            $author_data    = Author::get_by_user_id($author_id);
            if (is_object($author_data)) {
                return get_term_link($author_data->term_id);
            }
        }

        $link_path = str_replace(home_url(), '', $link);

        // Check if the author slug is empty in the link.
        if ($link_path === str_replace('%author%', '', $permastruct)) {
            // Redirects to the post page, or home page on some situations.
            $link = get_the_permalink();

            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('[PublishPress Authors] Warning - The author_id is empty so the link for the author page was changed to the home URL.');
            }
        }

        return $link;
    }

    /**
     * Filters author term display name.
     *
     * @param $author_meta
     * @param $user_id
     *
     * @return mixed
     */
    public static function filter_author_display_name($author_meta, $user_id)
    {
        if (empty($author_meta) && empty($user_id)) {
            $authors = get_post_authors();

            if (!empty($authors)) {
                // Even for multiple authors, if not specified one, we will always get the first author.
                $author = $authors[0];

                if (isset($author->display_name)) {
                    return $author->display_name;
                }
            }
        }

        return $author_meta;
    }

    /**
     * Store user id as a term meta key too, for faster querying
     *
     * @param mixed $check Whether or not the update should be short-circuited.
     * @param int $object_id ID for the author term object.
     * @param string $meta_key Meta key being updated.
     * @param string $meta_value New meta value.
     */
    public static function filter_update_term_metadata($check, $object_id, $meta_key, $meta_value)
    {
        if ('user_id' !== $meta_key) {
            return $check;
        }
        $term = get_term_by('id', $object_id, 'author');
        if ('author' !== $term->taxonomy) {
            return $check;
        }
        $metas = get_term_meta($object_id);
        foreach ($metas as $key => $meta) {
            if (0 === strpos($key, 'user_id_')) {
                delete_term_meta($object_id, $key);
            }
        }
        if ($meta_value) {
            update_term_meta($object_id, 'user_id_' . $meta_value, 'user_id');
        }

        return $check;
    }

    /**
     * Redirect mapped accounts. If user_nicename and author slug doesn't match,
     * redirect from the user_nicename to the author slug.
     *
     * @param WP_Query $query Current WordPress environment instance.
     * @return WP_Query
     */
    public static function action_parse_request($query)
    {
        // No redirection needed on admin requests.
        if (is_admin()) {
            return $query;
        }

        if (!isset($query->query_vars['author_name'])) {
            return $query;
        }

        $author = Utils::getUserBySlug(sanitize_title($query->query_vars['author_name']));

        if (is_a($author, 'WP_User')) {
            $author = Author::get_by_user_id($author->ID);
            if ($author && $query->query_vars['author_name'] !== $author->slug) {
                if (wp_safe_redirect($author->link)) {
                    exit;
                }
            }
        }

        return $query;
    }

    public static function filter_ma_get_author_data($data, $field, $post)
    {
        $authors = get_post_authors($post);

        if (empty($authors)) {
            return $data;
        }

        $field_map = [
            'author_display_name' => 'display_name',
            'author_email'        => 'user_email',
            'author_login'        => 'user_login',
        ];

        if (!isset($field_map[$field])) {
            return $data;
        }

        $field = $field_map[$field];
        $data  = [];
        foreach ($authors as $author) {
            $data[] = $author->{$field};
        }

        $data = implode(', ', $data);

        return $data;
    }
}
