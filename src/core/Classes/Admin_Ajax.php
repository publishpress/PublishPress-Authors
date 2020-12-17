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
 * Admin ajax endpoints
 *
 * Based on Bylines' class Byline_Editor.
 *
 * @package MultipleAuthors\Classes
 */
class Admin_Ajax
{

    /**
     * Handle a request to search available authors
     */
    public static function handle_authors_search()
    {
        header('Content-Type: application/javascript');

        if (empty($_GET['nonce'])
            || !wp_verify_nonce($_GET['nonce'], 'authors-search')) {
            exit;
        }

        $search   = !empty($_GET['q']) ? sanitize_text_field($_GET['q']) : '';
        $ignored  = !empty($_GET['ignored']) ? array_map('sanitize_text_field', $_GET['ignored']) : [];
        $authors  = self::get_possible_authors_for_search($search, $ignored);
        $response = [
            'results' => $authors,
        ];
        echo wp_json_encode($response);
        exit;
    }

    /**
     * Get the possible authors for a given search query.
     *
     * @param string $search Search query.
     * @param array $ignored Any authors that should be ignored.
     *
     * @return array
     */
    public static function get_possible_authors_for_search($search, $ignored = [])
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        $authors   = [];
        $term_args = [
            'taxonomy'   => 'author',
            'hide_empty' => false,
            'number'     => 20,
            'order_by'   => 'name',
        ];

        if (!empty($search)) {
            $term_args['search'] = $search;
        }

        if (!empty($ignored)) {
            $term_args['exclude'] = [];
            foreach ($ignored as $val) {
                if (is_numeric($val)) {
                    $term_args['exclude'][] = (int)$val;
                }
            }
        }

        $terms = get_terms($term_args);
        if ($terms && !is_wp_error($terms)) {
            $show_user_name = $legacyPlugin->modules->multiple_authors->options->username_in_search_field === 'yes';

            foreach ($terms as $term) {
                $author = Author::get_by_term_id($term->term_id);
                $text = $term->name;

                if ($show_user_name) {
                    if (!$author->is_guest()) {
                        $user = $author->get_user_object();

                        if (!is_wp_error($user) && is_object($user)) {
                            $text .= sprintf(' (%s)', $user->user_nicename);
                        }
                    }
                }

                $authors[] = [
                    'id'           => (int)$term->term_id,
                    'text'         => $text,
                    'term'         => (int)$term->term_id,
                    'display_name' => $text,
                    'user_id'      => $author->user_id,
                ];
            }
        }

        return $authors;
    }

    /**
     * Handle an ajax request to search available users
     */
    public static function handle_users_search()
    {
        header('Content-Type: application/javascript');

        if (empty($_GET['nonce'])
            || !wp_verify_nonce($_GET['nonce'], 'authors-user-search')) {
            exit;
        }

        $user_args = [
            'number' => 20,
        ];
        if (!empty($_GET['q'])) {
            $user_args['search'] = sanitize_text_field('*' . $_GET['q'] . '*');
        }
        $users   = get_users($user_args);
        $results = [];
        foreach ($users as $user) {
            $results[] = [
                'id'   => $user->ID,
                'text' => $user->display_name,
            ];
        }
        $response = [
            'results' => $results,
        ];
        echo wp_json_encode($response);
        exit;
    }

    /**
     * Handle a GET request to create a new author from a user
     */
    public static function handle_author_create_from_user()
    {
        if (empty($_GET['nonce'])
            || empty($_GET['user_id'])
            || !wp_verify_nonce($_GET['nonce'], 'author_create_from_user' . $_GET['user_id'])) {
            exit;
        }

        $user_id = (int)$_GET['user_id'];
        $author  = Author::create_from_user($user_id);
        if (is_wp_error($author)) {
            wp_die($author->get_error_message());
        }
        $link = get_edit_term_link($author->term_id, 'author');
        wp_safe_redirect($link);
        exit;
    }

    /**
     * Handle a GET request to create a new author from a user
     */
    public static function handle_author_get_user_data()
    {
        if (empty($_GET['nonce'])
            || empty($_GET['user_id'])
            || !wp_verify_nonce($_GET['nonce'], 'author_get_user_data_nonce')) {
            exit;
        }

        $user_id = (int)$_GET['user_id'];

        $user = get_user_by('ID', $user_id);

        $response = [
            'first_name'  => $user->first_name,
            'last_name'   => $user->last_name,
            'user_email'  => $user->user_email,
            'user_url'    => $user->user_url,
            'description' => $user->description,
            'slug'        => $user->user_nicename,
        ];

        echo wp_json_encode($response);
        exit;
    }
}
