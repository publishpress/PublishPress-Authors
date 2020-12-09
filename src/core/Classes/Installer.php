<?php
/**
 * @package PublishPress Authors
 * @author  PublishPress
 *
 * Copyright (C) 2018 PublishPress
 *
 * This file is part of PublishPress Authors
 *
 * PublishPress Authors is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * PublishPress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PublishPress.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace MultipleAuthors\Classes;

use MultipleAuthors\Classes\Objects\Author;
use WP_Role;

class Installer
{
    /**
     * Runs methods when the plugin is running for the first time.
     *
     * @param string $current_version
     */
    public static function install($current_version)
    {
        // Do not execute the post_author migration to post terms if Co-Authors Plus is activated.
        if (!isset($GLOBALS['coauthors_plus']) || empty($GLOBALS['coauthors_plus'])) {
            self::convert_post_author_into_taxonomy();
            self::add_author_term_for_posts();
        }

        self::add_administrator_capabilities();
        self::add_new_edit_post_authors_cap();
        self::flush_permalinks();

        /**
         * @param string $currentVersion
         */
        do_action('pp_authors_install', $current_version);
    }

    /**
     * Creates terms for users found as authors in the content.
     */
    public static function convert_post_author_into_taxonomy()
    {
        global $wpdb;

        $enabledPostTypes = Utils::get_enabled_post_types();
        $enabledPostTypes = '"' . implode('","', $enabledPostTypes) . '"';

        // Get a list of authors (users) from the posts which has no terms.
        $authors = $wpdb->get_results(
            "SELECT DISTINCT p.post_author, u.display_name, u.user_nicename, u.user_email, u.user_url
				 FROM {$wpdb->posts} as p
				 LEFT JOIN {$wpdb->users} AS u ON (post_author = u.ID)
				 WHERE
				     p.post_status NOT IN ('trash') AND
				 	 p.post_author NOT IN (
					     SELECT meta.`meta_value`
						 FROM {$wpdb->terms} AS term
						 INNER JOIN {$wpdb->term_taxonomy} AS tax ON (term.`term_id` = tax.`term_id`)
						 INNER JOIN {$wpdb->termmeta} AS meta ON (term.term_id = meta.`term_id`)
						 WHERE tax.`taxonomy` = 'author'
						 AND meta.meta_key = 'user_id'
						 AND meta.meta_value <> 0
				 	)
				 	AND p.post_type IN ({$enabledPostTypes})
				 	AND p.post_author <> 0
					AND u.display_name != ''
			    "
        );

        // Check if the authors have a term. If not, create one.
        if (!empty($authors)) {
            foreach ($authors as $author) {
                $term = wp_insert_term(
                    $author->display_name,
                    'author',
                    [
                        'slug' => $author->user_nicename,
                    ]
                );

                // Get user's description
                $description = get_user_meta($author->post_author, 'description', true);
                if (empty($description)) {
                    $description = '';
                }

                if (is_wp_error($term)) {
                    continue;
                }

                $first_name = get_user_meta($author->post_author, 'first_name', true);
                $last_name  = get_user_meta($author->post_author, 'last_name', true);

                $meta = [
                    'first_name'                      => $first_name,
                    'last_name'                       => $last_name,
                    'user_email'                      => $author->user_email,
                    'user_id_' . $author->post_author => 'user_id',
                    'user_id'                         => $author->post_author,
                    'user_url'                        => $author->user_url,
                    'description'                     => $description,
                ];

                foreach ($meta as $key => $value) {
                    add_term_meta($term['term_id'], $key, $value);
                }
            }
        }
    }

    /**
     * Add author term for posts which only have the post_author.
     */
    public static function add_author_term_for_posts()
    {
        global $wpdb;

        // Add the relationship into the term and the post
        $posts_to_update = $wpdb->get_results(
            "SELECT p.ID, p.post_author
				FROM {$wpdb->posts} as p WHERE ID NOT IN (
					SELECT DISTINCT p.ID
					FROM {$wpdb->posts} AS p
					INNER JOIN {$wpdb->termmeta} AS meta ON (p.post_author = meta.meta_value)
					INNER JOIN {$wpdb->term_taxonomy} AS tax ON (meta.term_id = tax.term_id)
					INNER JOIN {$wpdb->term_relationships} AS rel ON (tax.term_taxonomy_id = rel.term_taxonomy_id)
					WHERE
						p.post_status NOT IN ('trash')
						AND p.post_author <> 0
						AND p.post_type = 'post'
						AND meta.meta_key = 'user_id'
						AND tax.taxonomy = 'author'
						AND rel.object_id = p.id
				)
				AND	p.post_type = 'post'
				AND p.post_status NOT IN ('trash')"
        );

        if (!empty($posts_to_update)) {
            foreach ($posts_to_update as $post_data) {
                $author = Author::get_by_user_id($post_data->post_author);

                if (is_object($author)) {
                    $authors = [$author];

                    Utils::set_post_authors_name_meta($post_data->ID, $authors);
                    Utils::sync_post_author_column($post_data->ID, $authors);

                    $authors = wp_list_pluck($authors, 'term_id');

                    wp_add_object_terms($post_data->ID, $authors, 'author');
                }
            }
        }
    }

    private static function add_administrator_capabilities()
    {
        $role = get_role('administrator');
        $role->add_cap('ppma_manage_authors');
        $role->add_cap('manage_options');
        $role->add_cap('ppma_edit_post_authors');
    }

    /**
     * Flushes the permalinks rules.
     */
    protected static function flush_permalinks()
    {
        global $wp_rewrite;

        if (is_object($wp_rewrite)) {
            $wp_rewrite->flush_rules();
        }
    }

    private static function add_new_edit_post_authors_cap()
    {
        $cap = 'ppma_edit_post_authors';
        $roles = [
            'author',
            'editor',
            'contributor',
        ];

        foreach ($roles as $roleNmae)
        {
            $role = get_role($roleNmae);
            if ($role instanceof WP_Role) {
                $role->add_cap($cap);
            }
        }
    }

    /**
     * Runs methods when the plugin is being upgraded to a most recent version.
     *
     * @param string $previous_version
     */
    public static function upgrade($previous_version)
    {
        if (version_compare($previous_version, '2.0.2', '<')) {
            // Do not execute the post_author migration to post terms if Co-Authors Plus is activated.
            if (!isset($GLOBALS['coauthors_plus']) || empty($GLOBALS['coauthors_plus'])) {
                self::convert_post_author_into_taxonomy();
                self::add_author_term_for_posts();
            }
        }

        if (version_compare($previous_version, '3.6.0', '<')) {
            self::add_new_edit_post_authors_cap();
        }

        /**
         * @param string $previousVersion
         */
        do_action('pp_authors_upgrade', $previous_version);

        self::add_administrator_capabilities();
        self::flush_permalinks();
    }
}
