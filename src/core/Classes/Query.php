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
 * Modifications to the main query, and helper query methods
 *
 * Based on Bylines.
 *
 * @package MultipleAuthors\Classes
 */
class Query
{
    /**
     * Fix for author pages 404ing or not properly displaying on author pages
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
            global $wp_query;
        }

        if (isset($wp_query->query['post_type'])  && $wp_query->query['post_type'] === 'ppmacf_field') {
            return;
        }

        if (!$wp_query->is_author()) {
            return;
        }

        $author_name = $wp_query->get('author_name');
        if (!$author_name) {
            return;
        }

        $user       = get_user_by('slug', $author_name);
        $authorTerm = get_term_by('slug', $author_name, 'author');

        $author = null;
        if (is_object($user)) {
            $author = $user;
        } elseif (is_object($authorTerm)) {
            $author = Author::get_by_term_id($authorTerm->term_id);
        }

        global $authordata;

        if (is_object($author)) {
            $wp_query->queried_object    = $author;
            $wp_query->queried_object_id = $author->ID;
            $wp_query->set('author_name', $author->user_nicename);
            $wp_query->set('author', $author->ID);

            $authordata = $author;
        } else {
            $wp_query->queried_object    = null;
            $wp_query->queried_object_id = null;
            $wp_query->is_author         = false;
            $wp_query->is_archive        = false;

            $authordata = null;
        }
    }

    /**
     * Modify the WHERE clause on author queries.
     *
     * @param string $where Existing WHERE clause.
     * @param WP_Query $query Query object.
     *
     * @return string
     */
    public static function filter_posts_where($where, $query)
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
            $user      = get_user_by('id', $author_id);

            if (!$author_id || !$user) {
                return $where;
            }

            $author_name = $user->user_nicename;
        }

        $terms = [];
        $term  = get_term_by('slug', $author_name, 'author');

        if (!empty($term)) {
            $terms[] = $term;
        }

        // Shamelessly copied from CAP, because it'd be a shame to have to deal with this twice.
        if (stripos($where, '.post_author = 0)')) {
            $maybe_both = false;
        } else {
            $maybe_both = apply_filters('authors_query_post_author', false);
        }

        $maybe_both_query = $maybe_both ? '$0 OR ' : '';

        if (!empty($terms)) {
            $terms_implode = '';

            $query->authors_having_terms = '';

            foreach ($terms as $term) {
                $terms_implode .= '(' . $wpdb->term_taxonomy . '.taxonomy = "author" AND ' . $wpdb->term_taxonomy . '.term_id = \'' . $term->term_id . '\') OR ';

                $query->authors_having_terms .= ' ' . $wpdb->term_taxonomy . '.term_id = \'' . $term->term_id . '\' OR ';
            }

                $terms_implode = rtrim($terms_implode, ' OR');

            $query->authors_having_terms = rtrim($query->authors_having_terms, ' OR');

            // post_author = 2 OR post_author IN (2).'/\b(?:' . $wpdb->posts . '\.)?post_author\s*(?:=|IN)\s*\(?(\d+)\)?/'
            $regex = '/\(?\b(?:' . $wpdb->posts . '\.)?post_author\s*(?:=|IN)\s*\(?(\d+)\)?/';
            $where = preg_replace($regex, '(' . $maybe_both_query . ' ' . $terms_implode . ')', $where, -1);
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
    public static function filter_posts_list_where($where, $query)
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

        $author = Author::get_by_user_id($author_id);

        if (!is_object($author) || is_wp_error($author)) {
            return $where;
        }

        $terms_implode = '(' . $wpdb->term_taxonomy . '.taxonomy = "author" AND ' . $wpdb->term_taxonomy . '.term_id = \'' . $author->getTerm()->term_id . '\') OR ';
        $terms_implode = rtrim($terms_implode, ' OR');

        // post_author = 2 OR post_author IN (2).'/\b(?:' . $wpdb->posts . '\.)?post_author\s*(?:=|IN)\s*\(?(\d+)\)?/'
        $regex = '/\(?\b(?:' . $wpdb->posts . '\.)?post_author\s*(?:=|IN)\s*\(?(\d+)\)?/';
        $where = preg_replace($regex, '(' . ' ' . $terms_implode . ')', $where, -1);

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

        $author = Author::get_by_user_id($author_id);

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

        $author = Author::get_by_user_id($author_id);

        if (!is_object($author) || is_wp_error($author)) {
            return $groupby;
        }

//        $having  = 'MAX( IF ( ' . $wpdb->term_taxonomy . '.taxonomy = "author", 1,0 ) ) <> 1 ';
        $groupby = $wpdb->posts . '.ID';

        return $groupby;
    }
}
