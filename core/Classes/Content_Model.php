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
        return Utils::get_supported_post_types();
    }

    /**
     * Filter author term links to look like author links
     *
     * @param string $link     Term link URL.
     * @param object $term     Term object.
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

        $author          = Author::get_by_term_id($term->term_id);
        $author_nicename = is_object($author) ? $author->slug : '';
        $permastruct     = $wp_rewrite->get_author_permastruct();

        if ($permastruct) {
            $link = str_replace('%author%', $author_nicename, $permastruct);
            $link = home_url(user_trailingslashit($link));
        } else {
            $link = add_query_arg('author_name', rawurlencode($author_nicename), home_url());
        }

        return $link;
    }

    /**
     * Filter author term links to look like author links
     *
     * @param $link
     * @param $author_id
     * @param $author_nicename
     *
     * @return string
     */
    public static function filter_author_link($link, $author_id, $author_nicename)
    {
        global $wp_rewrite;

        $permastruct = $wp_rewrite->get_author_permastruct();

        if (empty($permastruct)) {
            return $link;
        }

        // We probably have a call for a guest author, without an author_id and a undefined author_nicename argument.
        if (empty($author_id) && empty($author_nicename)) {
            // Try to identify the current authors.

            $authors = get_multiple_authors();

            if ( ! empty($authors)) {
                // Even for multiple authors, if not specified one, we will always get the first author.
                $author = $authors[0];

                // Based on the method get_author_posts_url.
                $link = str_replace('%author%', $author->user_nicename, $permastruct);
                $link = home_url(user_trailingslashit($link));
            }
        }

        $link_path = str_replace(home_url(), '', $link);

        // Check if the author slug is empty in the link.
        if ($link_path === str_replace('%author%', '', $permastruct)) {
            global $wp;

            // Redirects to the post page.
            $link = get_the_permalink();
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
            $authors = get_multiple_authors();

            if ( ! empty($authors)) {
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
     * @param mixed   $check      Whether or not the update should be short-circuited.
     * @param integer $object_id  ID for the author term object.
     * @param string  $meta_key   Meta key being updated.
     * @param string  $meta_value New meta value.
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
     * @param WP $query Current WordPress environment instance.
     */
    public static function action_parse_request($query)
    {
        if ( ! isset($query->query_vars['author_name'])) {
            return $query;
        }

        // No redirection needed on admin requests.
        if (is_admin()) {
            return $query;
        }

        $author = get_user_by('slug', sanitize_title($query->query_vars['author_name']));
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
        $authors = get_multiple_authors($post);

        if (empty($authors)) {
            return $data;
        }

        $field_map = [
            'author_display_name' => 'display_name',
            'author_email'        => 'user_email',
            'author_login'        => 'user_login',
        ];

        if ( ! isset($field_map[$field])) {
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
