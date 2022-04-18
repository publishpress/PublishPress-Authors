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
 * Modifications to the main query, and helper query methods
 *
 * Based on Bylines.
 *
 * @package MultipleAuthors\Classes
 */
class Query
{
    /**
     * Fix for author pages 404ing or not properly displaying on author pages, or queries filtering posts by author.
     *
     * If an author has no posts, we only want to force the queried object to be
     * the author if they're a member of the blog.
     *
     * If the author does have posts, it doesn't matter that they're not an author.
     *
     * @param WP_Query $wp_query Query object.
     */
    public static function fix_query_pre_get_posts($wp_query)
    {
        if (is_string($wp_query) || empty($wp_query)) {
            global $wp_query; // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.VariableRedeclaration
        }

        if (isset($wp_query->query['post_type']) && $wp_query->query['post_type'] === 'ppmacf_field') {
            return;
        }

        if (!$wp_query->is_author()) {
            return;
        }

        $author_name = $wp_query->get('author_name');
        if (!$author_name) {
            return;
        }

        $author = Utils::getUserBySlug($author_name);
        $is_guest = false;

        if (empty($author)) {
            $author = Author::get_by_term_slug($author_name);

            if (is_object($author)) {
                $is_guest = $author->is_guest();
            }
        } else {
            $is_guest = true;
        }

        global $authordata;

        if (is_object($author)) {
            $wp_query->queried_object    = $author;
            $wp_query->queried_object_id = $author->ID;
            $wp_query->set('author_name', $author->user_nicename);
            $wp_query->set('author', $author->ID);

            $authordata = $author; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        } else {
            $wp_query->queried_object    = null;
            $wp_query->queried_object_id = null;
            $wp_query->is_author         = false;
            $wp_query->is_archive        = false;

            $authordata = null; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
        }

        $wp_query->set('is_guest', $is_guest);
    }

    /**
     * Modify the WHERE clause on author queries.
     *
     * @param string $where Existing WHERE clause.
     * @param WP_Query $query Query object.
     *
     * @return string
     */
    public static function filter_author_posts_where($where, $query)
    {
        global $wpdb;

        if (!$query->is_author()) {
            return $where;
        }

        if (!empty($query->query_vars['post_type']) && !is_object_in_taxonomy(
                $query->query_vars['post_type'],
                'author'
            )) {
            return $where;
        }

        $author_name = sanitize_title($query->get('author_name'));

        if (empty($author_name)) {
            $author_id = (int)$query->get('author');

            $author = Author::get_by_id($author_id);

            if (!$author) {
                return $where;
            }

            $query->queried_object = $author;
            $query->queried_object_id = $author->ID;
        }

        if (is_a($query->queried_object, 'WP_User')) {
            $term = Author::get_by_user_id($query->queried_object_id);
        } else {
            $term = $query->queried_object;
        }

        if (empty($term)) {
            return $where;
        }

        // Shamelessly copied from CAP, because it'd be a shame to have to deal with this twice.
        if (stripos($where, '.post_author = 0)')) {
            $maybe_both = false;
        } else {
            $maybe_both = apply_filters('authors_query_post_author', false);
        }

        $maybe_both_query = $maybe_both ? '$0 OR ' : '';

        $query->authors_having_terms = ' ' . $wpdb->term_taxonomy . '.term_id = \'' . (int)$term->term_id . '\' ';

        /**
         * Private post author regex
         */
        $current_user_id   = get_current_user_id();
        $current_author    = Author::get_by_user_id($current_user_id);
        if (
            $current_author
            && is_object($current_author)
            && isset($current_author->term_id)
            && (int)$current_author->term_id > 0
        ) {
            $current_user_term_id = $current_author->term_id;
        } else {
            $current_user_term_id = 0;
        }
        $where = preg_replace(
            '/\(?\b(?:' . $wpdb->posts . '\.)?post_author\s*(?:=|IN)\s*\(?(\d+)\)? AND (?:' . $wpdb->posts . '\.)?post_status = \'private\'/',
            '(' . $maybe_both_query . ' ' . '' . $wpdb->term_taxonomy . '.taxonomy = "author" AND ' . $wpdb->term_taxonomy . '.term_id = \'' . (int)$current_user_term_id . '\' ' . ' AND ' . $wpdb->posts . '.post_status = \'private\'',
            $where,
            -1
        );

        /**
         * Post author replace
         */
        $where = preg_replace(
            '/\(?\b(?:' . $wpdb->posts . '\.)?post_author\s*(?:=|IN)\s*\(?(\d+)\)?/',
            '(' . $maybe_both_query . ' ' . '(' . $wpdb->term_taxonomy . '.taxonomy = "author" AND ' . $wpdb->term_taxonomy . '.term_id = \'' . (int)$term->term_id . '\') ' . ')',
            $where,
            -1
        );

        $where = static::add_custom_post_types_to_query($where);

        return apply_filters('publishpress_authors_filter_posts_list_where', $where, $query, $term);
    }

    /**
     * Modify the JOIN clause on author queries.
     *
     * @param string $join Existing JOIN clause.
     * @param WP_Query $query Query object.
     *
     * @return string
     */
    public static function filter_posts_join($join, $query)
    {
        global $wpdb;

        if (!$query->is_author() || empty($query->authors_having_terms)) {
            return $join;
        }

        // Check to see that JOIN hasn't already been added. Props michaelingp and nbaxley.
        $term_relationship_inner_join = " INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)";
        $term_relationship_left_join  = " LEFT JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)";
        $term_taxonomy_join           = " INNER JOIN {$wpdb->term_taxonomy} ON ( {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id )";

        // 4.6+ uses a LEFT JOIN for tax queries so we need to check for both.
        if (false === strpos($join, trim($term_relationship_inner_join))
            && false === strpos($join, trim($term_relationship_left_join))) {
            $join .= $term_relationship_left_join;
        }

        if (false === strpos($join, trim($term_taxonomy_join))) {
            $join .= str_replace('INNER JOIN', 'LEFT JOIN', $term_taxonomy_join);
        }

        return $join;
    }

    /**
     * Modify the GROUP BY clause on author queries.
     *
     * @param string $groupby Existing GROUP BY clause.
     * @param WP_Query $query Query object.
     *
     * @return string
     */
    public static function filter_posts_groupby($groupby, $query)
    {
        global $wpdb;

        if (!$query->is_author() || empty($query->authors_having_terms)) {
            return $groupby;
        }

        $having  = 'MAX( IF ( ' . $wpdb->term_taxonomy . '.taxonomy = "author", IF ( ' . $query->authors_having_terms . ',2,1 ),0 ) ) <> 1 ';
        $groupby = $wpdb->posts . '.ID HAVING ' . $having;

        return $groupby;
    }

    /**
     * Modify the WHERE clause on author queries.
     *
     * @param string $where Existing WHERE clause.
     * @param WP_Query $query Query object.
     *
     * @return string
     */
    public static function filter_admin_posts_list_where($where, $query)
    {
        global $wpdb;

        if (!isset($query->query_vars['author'])) {
            return $where;
        }

        if (!empty($query->query_vars['post_type']) && !is_object_in_taxonomy(
                $query->query_vars['post_type'],
                'author'
            )) {
            return $where;
        }

        $author_id = (int)$query->get('author');

        if (empty($author_id)) {
            return $where;
        }


        if (is_a($query->queried_object, 'WP_User')) {
            $author = Author::get_by_user_id($query->queried_object_id);
        } else {
            $author = $query->queried_object;

            if (!is_a($author, Author::class)) {
                return $where;
            }
        }

        if (!is_object($author) || is_wp_error($author)) {
            return $where;
        }

        $terms_implode = '(' . $wpdb->term_taxonomy . '.taxonomy = "author" AND ' . $wpdb->term_taxonomy . '.term_id = \'' . (int)$author->getTerm()->term_id . '\') ';

        /**
         * Private post author regex
         */
        $current_user_id   = get_current_user_id();
        $current_author    = Author::get_by_user_id($current_user_id);
        if (
            $current_author
            && is_object($current_author)
            && isset($current_author->term_id)
            && (int)$current_author->term_id > 0
        ) {
            $current_user_term_id = $current_author->term_id;
        } else {
            $current_user_term_id = 0;
        }
        $where = preg_replace(
            '/\(?\b(?:' . $wpdb->posts . '\.)?post_author\s*(?:=|IN)\s*\(?(\d+)\)? AND (?:' . $wpdb->posts . '\.)?post_status = \'private\'/',
            '(' . '' . $wpdb->term_taxonomy . '.taxonomy = "author" AND ' . $wpdb->term_taxonomy . '.term_id = \'' . (int)$current_user_term_id . '\' ' . ' AND ' . $wpdb->posts . '.post_status = \'private\'',
            $where,
            -1
        );

        $where = preg_replace(
            '/\(?\b(?:' . $wpdb->posts . '\.)?post_author\s*(?:=|IN|NOT IN)\s*\(?(\d+)\)?/',
            '(' . ' ' . $terms_implode . ')',
            $where,
            -1
        );

        return apply_filters('publishpress_authors_filter_posts_list_where', $where, $query, $author);
    }

    public static function getSelectedPostTypesForAuthorsPage()
    {
        $legacyPlugin  = Factory::getLegacyPlugin();
        $moduleOptions = $legacyPlugin->multiple_authors->module->options;

        $enabledPostTypes                = Utils::get_enabled_post_types();
        $selectedPostTypesForAuthorsPage = apply_filters('publishpress_authors_posts_query_post_types', []);

        if (empty($selectedPostTypesForAuthorsPage)) {
            foreach ($moduleOptions->author_page_post_types as $postType => $status) {
                if ($status !== 'on') {
                    continue;
                }

                if (in_array($postType, $enabledPostTypes)) {
                    $selectedPostTypesForAuthorsPage[] = esc_sql($postType);
                }
            }

            return $selectedPostTypesForAuthorsPage;
        }

        return [];
    }

    private static function add_custom_post_types_to_query($where)
    {
        $legacyPlugin  = Factory::getLegacyPlugin();
        $moduleOptions = $legacyPlugin->multiple_authors->module->options;

        if (!isset($moduleOptions->author_page_post_types)) {
            return $where;
        }

        $selectedPostTypesForAuthorsPage = static::getSelectedPostTypesForAuthorsPage();

        if (!empty($selectedPostTypesForAuthorsPage)) {
            $postTypesString = implode('\', \'', $selectedPostTypesForAuthorsPage);

            return str_replace(".post_type = 'post'", ".post_type IN ('" . $postTypesString . "')", $where);
        }

        return $where;
    }

    /**
     * Modify the JOIN clause on author queries.
     *
     * @param string $join Existing JOIN clause.
     * @param WP_Query $query Query object.
     *
     * @return string
     */
    public static function filter_posts_list_join($join, $query)
    {
        global $wpdb;

        if (!isset($query->query_vars['author'])) {
            return $join;
        }

        if (!empty($query->query_vars['post_type']) && !is_object_in_taxonomy(
                $query->query_vars['post_type'],
                'author'
            )) {
            return $join;
        }

        $author_id = (int)$query->get('author');

        if (empty($author_id)) {
            return $join;
        }

        if (is_a($query->queried_object, 'WP_User')) {
            $author = Author::get_by_user_id($query->queried_object_id);
        } else {
            $author = $query->queried_object;
        }

        if (!is_object($author) || is_wp_error($author)) {
            return $join;
        }

        // Check to see that JOIN hasn't already been added. Props michaelingp and nbaxley.
        $term_relationship_inner_join = " INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)";
        $term_relationship_left_join  = " LEFT JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)";
        $term_taxonomy_join           = " INNER JOIN {$wpdb->term_taxonomy} ON ( {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id )";

        // 4.6+ uses a LEFT JOIN for tax queries so we need to check for both.
        if (false === strpos($join, trim($term_relationship_inner_join))
            && false === strpos($join, trim($term_relationship_left_join))) {
            $join .= $term_relationship_left_join;
        }

        if (false === strpos($join, trim($term_taxonomy_join))
            && false === strpos($join, trim($term_relationship_left_join))) {
            $join .= str_replace('INNER JOIN', 'LEFT JOIN', $term_taxonomy_join);
        }

        return $join;
    }

    /**
     * Modify the GROUP BY clause on author queries.
     *
     * @param string $groupby Existing GROUP BY clause.
     * @param WP_Query $query Query object.
     *
     * @return string
     */
    public static function filter_posts_list_groupby($groupby, $query)
    {
        global $wpdb;

        if (!isset($query->query_vars['author'])) {
            return $groupby;
        }

        if (!empty($query->query_vars['post_type']) && !is_object_in_taxonomy(
                $query->query_vars['post_type'],
                'author'
            )) {
            return $groupby;
        }

        $author_id = (int)$query->get('author');

        if (empty($author_id)) {
            return $groupby;
        }

        if (is_a($query->queried_object, 'WP_User')) {
            $author = Author::get_by_user_id($query->queried_object_id);
        } else {
            $author = $query->queried_object;
        }

        if (!is_object($author) || is_wp_error($author)) {
            return $groupby;
        }

        $groupby = $wpdb->posts . '.ID';

        return $groupby;
    }
}
