<?php
/**
 * PublishPress Authors commands for the WP-CLI framework
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 * @see     https://github.com/wp-cli/wp-cli
 */

namespace MultipleAuthors;

use WP_CLI_Command;

class WP_Cli extends WP_CLI_Command
{

    /**
     * Create author terms for all posts that don't have them
     *
     * @subcommand create-terms-for-posts
     */
    public function create_terms_for_posts()
    {
        global $multiple_authors_addon, $wp_post_types;

        // Cache these to prevent repeated lookups
        $authors      = [];
        $author_terms = [];

        $args = [
            'order'             => 'ASC',
            'orderby'           => 'ID',
            'post_type'         => $multiple_authors_addon->supported_post_types,
            'posts_per_page'    => 100,
            'paged'             => 1,
            'update_meta_cache' => false,
        ];

        $posts       = new WP_Query($args);
        $affected    = 0;
        $count       = 0;
        $total_posts = $posts->found_posts;
        self::line("Now inspecting or updating {$posts->found_posts} total posts.");
        while ($posts->post_count) {
            foreach ($posts->posts as $single_post) {
                $count++;

                $terms = cap_get_coauthor_terms_for_post($single_post->ID);
                if (empty($terms)) {
                    self::error(sprintf('No co-authors found for post #%d.', $single_post->ID));
                }

                if (!empty($terms)) {
                    self::line(
                        "{$count}/{$posts->found_posts}) Skipping - Post #{$single_post->ID} '{$single_post->post_title}' already has these terms: " . implode(
                            ', ',
                            wp_list_pluck($terms, 'name')
                        )
                    );
                    continue;
                }

                $author                             = (!empty($authors[$single_post->post_author])) ? $authors[$single_post->post_author] : get_user_by(
                    'id',
                    $single_post->post_author
                );
                $authors[$single_post->post_author] = $author;

                $author_term                             = (!empty($author_terms[$single_post->post_author])) ? $author_terms[$single_post->post_author] : $multiple_authors_addon->update_author_term(
                    $author
                );
                $author_terms[$single_post->post_author] = $author_term;

                wp_set_post_terms(
                    $single_post->ID,
                    [$author_term->slug],
                    $multiple_authors_addon->coauthor_taxonomy
                );
                self::line(
                    "{$count}/{$total_posts}) Added - Post #{$single_post->ID} '{$single_post->post_title}' now has an author term for: " . $author->user_nicename
                );
                $affected++;
            }

            if ($count && 0 === $count % 500) {
                $this->stop_the_insanity();
                sleep(1);
            }

            $args['paged']++;
            $posts = new WP_Query($args);
        }
        self::line('Updating author terms with new counts');
        foreach ($authors as $author) {
            $multiple_authors_addon->update_author_term($author);
        }

        self::success("Done! Of {$total_posts} posts, {$affected} now have author terms.");
    }

    /**
     * Clear all of the caches for memory management
     */
    private function stop_the_insanity()
    {
        global $wpdb, $wp_object_cache;

        $wpdb->queries = []; // or define( 'WP_IMPORTING', true );

        if (!is_object($wp_object_cache)) {
            return;
        }

        $wp_object_cache->group_ops      = [];
        $wp_object_cache->stats          = [];
        $wp_object_cache->memcache_debug = [];
        $wp_object_cache->cache          = [];

        if (is_callable($wp_object_cache, '__remoteset')) {
            $wp_object_cache->__remoteset(); // important
        }
    }

    /**
     * Subcommand to assign coauthors to a post based on a given meta key
     *
     * @since      3.0
     *
     * @subcommand assign-coauthors
     * @synopsis [--meta_key=<key>] [--post_type=<ptype>]
     */
    public function assign_coauthors($args, $assoc_args)
    {
        global $multiple_authors_addon;

        $defaults   = [
            'meta_key'         => '_original_import_author',
            'post_type'        => 'post',
            'order'            => 'ASC',
            'orderby'          => 'ID',
            'posts_per_page'   => 100,
            'paged'            => 1,
            'append_coauthors' => false,
        ];
        $this->args = wp_parse_args($assoc_args, $defaults);

        // For global use and not a part of WP_Query
        $append_coauthors = $this->args['append_coauthors'];
        unset($this->args['append_coauthors']);

        $posts_total              = 0;
        $posts_already_associated = 0;
        $posts_missing_coauthor   = 0;
        $posts_associated         = 0;
        $missing_coauthors        = [];

        $posts = new WP_Query($this->args);
        while ($posts->post_count) {
            foreach ($posts->posts as $single_post) {
                $posts_total++;

                // See if the value in the post meta field is the same as any of the existing coauthors
                $original_author    = get_post_meta($single_post->ID, $this->args['meta_key'], true);
                $existing_coauthors = get_multiple_authors($single_post->ID);
                $already_associated = false;
                foreach ($existing_coauthors as $existing_coauthor) {
                    if ($original_author == $existing_coauthor->user_nicename) {
                        $already_associated = true;
                    }
                }
                if ($already_associated) {
                    $posts_already_associated++;
                    self::line(
                        $posts_total . ': Post #' . $single_post->ID . ' already has "' . $original_author . '" associated as a coauthor'
                    );
                    continue;
                }

                // Make sure this original author exists as a co-author
                if ((!$coauthor = $multiple_authors_addon->get_coauthor_by('user_nicename', $original_author)) &&
                    (!$coauthor = $multiple_authors_addon->get_coauthor_by(
                        'user_nicename',
                        sanitize_title($original_author)
                    ))) {
                    $posts_missing_coauthor++;
                    $missing_coauthors[] = $original_author;
                    self::line(
                        $posts_total . ': Post #' . $single_post->ID . ' does not have "' . $original_author . '" associated as a coauthor but there is not a coauthor profile'
                    );
                    continue;
                }

                // Assign the coauthor to the post
                $multiple_authors_addon->add_coauthors(
                    $single_post->ID,
                    [$coauthor->user_nicename],
                    $append_coauthors
                );
                self::line(
                    $posts_total . ': Post #' . $single_post->ID . ' has been assigned "' . $original_author . '" as the author'
                );
                $posts_associated++;
                clean_post_cache($single_post->ID);
            }

            $this->args['paged']++;
            $this->stop_the_insanity();
            $posts = new WP_Query($this->args);
        }

        self::line('All done! Here are your results:');
        if ($posts_already_associated) {
            self::line("- {$posts_already_associated} posts already had the coauthor assigned");
        }
        if ($posts_missing_coauthor) {
            self::line("- {$posts_missing_coauthor} posts reference coauthors that don't exist. These are:");
            self::line('  ' . implode(', ', array_unique($missing_coauthors)));
        }
        if ($posts_associated) {
            self::line("- {$posts_associated} posts now have the proper coauthor");
        }
    }

    /**
     * Assign posts associated with a WordPress user to a co-author
     * Only apply the changes if there aren't yet co-authors associated with the post
     *
     * @since      3.0
     *
     * @subcommand assign-user-to-coauthor
     * @synopsis   --user_login=<user-login> --coauthor=<coauthor>
     */
    public function assign_user_to_coauthor($args, $assoc_args)
    {
        global $multiple_authors_addon, $wpdb;

        $defaults   = [
            'user_login' => '',
            'coauthor'   => '',
        ];
        $assoc_args = wp_parse_args($assoc_args, $defaults);

        $user     = get_user_by('login', $assoc_args['user_login']);
        $coauthor = $multiple_authors_addon->get_coauthor_by('login', $assoc_args['coauthor']);

        if (!$user) {
            self::error(__('Please specify a valid user_login', 'publishpress-authors'));
        }

        if (!$coauthor) {
            self::error(__('Please specify a valid co-author login', 'publishpress-authors'));
        }

        $post_types = implode("','", $multiple_authors_addon->supported_post_types);
        $posts      = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE post_author=%d AND post_type IN ('$post_types')",
                $user->ID
            )
        );
        $affected   = 0;
        foreach ($posts as $post_id) {
            $coauthors = cap_get_coauthor_terms_for_post($post_id);
            if (!empty($coauthors)) {
                self::line(
                    sprintf(
                        __('Skipping - Post #%d already has co-authors assigned: %s', 'publishpress-authors'),
                        $post_id,
                        implode(', ', wp_list_pluck($coauthors, 'slug'))
                    )
                );
                continue;
            }

            $multiple_authors_addon->add_coauthors($post_id, [$coauthor->user_login]);
            self::line(
                sprintf(
                    __("Updating - Adding %s's byline to post #%d", 'publishpress-authors'),
                    $coauthor->user_login,
                    $post_id
                )
            );
            $affected++;
            if ($affected && 0 === $affected % 20) {
                sleep(5);
            }
        }
        self::success(
            sprintf(
                __('All done! %d posts were affected.', 'publishpress-authors'),
                $affected
            )
        );
    }

    /**
     * Subcommand to reassign co-authors based on some given format
     * This will look for terms with slug 'x' and rename to term with slug and name 'y'
     * This subcommand can be helpful for cleaning up after an import if the usernames
     * for authors have changed. During the import process, 'author' terms will be
     * created with the old user_login value. We can use this to migrate to the new user_login
     *
     * @todo       support reassigning by CSV
     *
     * @since      3.0
     *
     * @subcommand reassign-terms
     * @synopsis [--author-mapping=<file>] [--old_term=<slug>] [--new_term=<slug>]
     */
    public function reassign_terms($args, $assoc_args)
    {
        global $multiple_authors_addon;

        $defaults   = [
            'author_mapping' => null,
            'old_term'       => null,
            'new_term'       => null,
        ];
        $this->args = wp_parse_args($assoc_args, $defaults);

        $author_mapping = $this->args['author_mapping'];
        $old_term       = $this->args['old_term'];
        $new_term       = $this->args['new_term'];

        // Get the reassignment data
        if ($author_mapping && file_exists($author_mapping)) {
            require_once($author_mapping);
            $authors_to_migrate = $cli_user_map;
        } elseif ($author_mapping) {
            self::error("author_mapping doesn't exist: " . $author_mapping);
            exit;
        }

        // Alternate reassigment approach
        if ($old_term && $new_term) {
            $authors_to_migrate = [
                $old_term => $new_term,
            ];
        }

        // For each author to migrate, check whether the term exists,
        // whether the target term exists, and only do the migration if both are met
        $results = (object)[
            'old_term_missing' => 0,
            'new_term_exists'  => 0,
            'success'          => 0,
        ];
        foreach ($authors_to_migrate as $old_user => $new_user) {
            if (is_numeric($new_user)) {
                $new_user = get_user_by('id', $new_user)->user_login;
            }

            // The old user should exist as a term
            $old_term = $multiple_authors_addon->get_author_term(
                $multiple_authors_addon->get_coauthor_by(
                    'login',
                    $old_user
                )
            );
            if (!$old_term) {
                self::line("Error: Term '{$old_user}' doesn't exist, skipping");
                $results->old_term_missing++;
                continue;
            }

            // If the new user exists as a term already, we want to reassign all posts to that
            // new term and delete the original
            // Otherwise, simply rename the old term
            $new_term = $multiple_authors_addon->get_author_term(
                $multiple_authors_addon->get_coauthor_by(
                    'login',
                    $new_user
                )
            );
            if (is_object($new_term)) {
                self::line(
                    "Success: There's already a '{$new_user}' term for '{$old_user}'. Reassigning {$old_term->count} posts and then deleting the term"
                );
                $args = [
                    'default'       => $new_term->term_id,
                    'force_default' => true,
                ];
                wp_delete_term($old_term->term_id, $multiple_authors_addon->coauthor_taxonomy, $args);
                $results->new_term_exists++;
            } else {
                $args = [
                    'slug' => $new_user,
                    'name' => $new_user,
                ];
                wp_update_term($old_term->term_id, $multiple_authors_addon->coauthor_taxonomy, $args);
                self::line("Success: Converted '{$old_user}' term to '{$new_user}'");
                $results->success++;
            }
            clean_term_cache($old_term->term_id, $multiple_authors_addon->coauthor_taxonomy);
        }

        self::line('Reassignment complete. Here are your results:');
        self::line("- $results->success authors were successfully reassigned terms");
        self::line("- $results->new_term_exists authors had their old term merged to their new term");
        self::line("- $results->old_term_missing authors were missing old terms");
    }

    /**
     * Change a term from representing one user_login value to another
     *
     * @since      3.0.1
     *
     * @subcommand rename-coauthor
     * @synopsis   --from=<user-login> --to=<user-login>
     */
    public function rename_coauthor($args, $assoc_args)
    {
        global $multiple_authors_addon, $wpdb;

        $defaults   = [
            'from' => null,
            'to'   => null,
        ];
        $assoc_args = array_merge($defaults, $assoc_args);

        $to_userlogin = $assoc_args['to'];

        $orig_coauthor = $multiple_authors_addon->get_coauthor_by('user_login', $assoc_args['from']);
        if (!$orig_coauthor) {
            self::error("No co-author found for {$assoc_args['from']}");
        }

        if (!$to_userlogin) {
            self::error('--to param must not be empty');
        }

        if ($multiple_authors_addon->get_coauthor_by('user_login', $to_userlogin)) {
            self::error('New user_login value conflicts with existing co-author');
        }

        $orig_term = $multiple_authors_addon->get_author_term($orig_coauthor);

        self::line("Renaming {$orig_term->name} to {$to_userlogin}");
        $rename_args = [
            'name' => $to_userlogin,
            'slug' => $to_userlogin,
        ];
        wp_update_term($orig_term->term_id, $multiple_authors_addon->coauthor_taxonomy, $rename_args);

        self::success('All done!');
    }

    /**
     * Swap one Co Author with another on all posts for which they are an author. Unlike rename-coauthor,
     * this leaves the original Co Author term intact and works when the 'to' user already has a co-author term.
     *
     * @subcommand swap-coauthors
     * @synopsis   --from=<user-login> --to=<user-login> [--post_type=<ptype>] [--dry=<dry>]
     */
    public function swap_coauthors($args, $assoc_args)
    {
        global $multiple_authors_addon, $wpdb;

        $defaults = [
            'from'      => null,
            'to'        => null,
            'post_type' => 'post',
            'dry'       => false,
        ];

        $assoc_args = array_merge($defaults, $assoc_args);

        $dry = $assoc_args['dry'];

        $from_userlogin = $assoc_args['from'];
        $to_userlogin   = $assoc_args['to'];

        $orig_coauthor = $multiple_authors_addon->get_coauthor_by('user_login', $from_userlogin);

        if (!$orig_coauthor) {
            self::error("No co-author found for $from_userlogin");
        }

        if (!$to_userlogin) {
            self::error('--to param must not be empty');
        }

        $to_coauthor = $multiple_authors_addon->get_coauthor_by('user_login', $to_userlogin);

        if (!$to_coauthor) {
            self::error("No co-author found for $to_userlogin");
        }

        self::line("Swapping authorship from {$from_userlogin} to {$to_userlogin}");

        $query_args = [
            'post_type'      => $assoc_args['post_type'],
            'order'          => 'ASC',
            'orderby'        => 'ID',
            'posts_per_page' => 100,
            'paged'          => 1,
            'tax_query'      => [
                [
                    'taxonomy' => $multiple_authors_addon->coauthor_taxonomy,
                    'field'    => 'slug',
                    'terms'    => [$from_userlogin],
                ],
            ],
        ];

        $posts = new WP_Query($query_args);

        $posts_total = 0;

        self::line("Found $posts->found_posts posts to update.");

        while ($posts->post_count) {
            foreach ($posts->posts as $post) {
                $coauthors = get_multiple_authors($post->ID);

                if (!is_array($coauthors) || !count($coauthors)) {
                    continue;
                }

                $coauthors = wp_list_pluck($coauthors, 'user_login');

                $posts_total++;

                if (!$dry) {
                    // Remove the $from_userlogin from $coauthors
                    foreach ($coauthors as $index => $user_login) {
                        if ($from_userlogin === $user_login) {
                            unset($coauthors[$index]);

                            break;
                        }
                    }

                    // Add the 'to' author on
                    $coauthors[] = $to_userlogin;

                    // By not passing $append = false as the 3rd param, we replace all existing coauthors
                    $multiple_authors_addon->add_coauthors($post->ID, $coauthors, false);

                    self::line(
                        $posts_total . ': Post #' . $post->ID . ' has been assigned "' . $to_userlogin . '" as a co-author'
                    );

                    clean_post_cache($post->ID);
                } else {
                    self::line(
                        $posts_total . ': Post #' . $post->ID . ' will be assigned "' . $to_userlogin . '" as a co-author'
                    );
                }
            }

            // In dry mode, we must manually advance the page
            if ($dry) {
                $query_args['paged']++;
            }

            $this->stop_the_insanity();

            $posts = new WP_Query($query_args);
        }

        self::success('All done!');
    }

    /**
     * List all of the posts without assigned co-authors terms
     *
     * @since      3.0
     *
     * @subcommand list-posts-without-terms
     * @synopsis [--post_type=<ptype>]
     */
    public function list_posts_without_terms($args, $assoc_args)
    {
        global $multiple_authors_addon;

        $defaults   = [
            'post_type'         => 'post',
            'order'             => 'ASC',
            'orderby'           => 'ID',
            'year'              => '',
            'posts_per_page'    => 300,
            'paged'             => 1,
            'no_found_rows'     => true,
            'update_meta_cache' => false,
        ];
        $this->args = wp_parse_args($assoc_args, $defaults);

        $posts = new WP_Query($this->args);
        while ($posts->post_count) {
            foreach ($posts->posts as $single_post) {
                $terms = cap_get_coauthor_terms_for_post($single_post->ID);
                if (empty($terms)) {
                    $saved = [
                        $single_post->ID,
                        addslashes($single_post->post_title),
                        get_permalink($single_post->ID),
                        $single_post->post_date,
                    ];
                    self::line('"' . implode('","', $saved) . '"');
                }
            }

            $this->stop_the_insanity();

            $this->args['paged']++;
            $posts = new WP_Query($this->args);
        }
    }

    /**
     * Update the post count and description for each author
     *
     * @since      3.0
     *
     * @subcommand update-author-terms
     */
    public function update_author_terms()
    {
        global $multiple_authors_addon;
        $author_terms = get_terms($multiple_authors_addon->coauthor_taxonomy, ['hide_empty' => false]);
        self::line('Now updating ' . count($author_terms) . ' terms');
        foreach ($author_terms as $author_term) {
            $old_count = $author_term->count;
            $coauthor  = $multiple_authors_addon->get_coauthor_by('user_nicename', $author_term->slug);
            $multiple_authors_addon->update_author_term($coauthor);
            wp_cache_delete($author_term->term_id, $multiple_authors_addon->coauthor_taxonomy);
            $new_count = get_term_by('id', $author_term->term_id, $multiple_authors_addon->coauthor_taxonomy)->count;
            self::line(
                "Term {$author_term->slug} ({$author_term->term_id}) changed from {$old_count} to {$new_count} and the description was refreshed"
            );
        }
        // Create author terms for any users that don't have them
        $users = get_users();
        foreach ($users as $user) {
            $term = $multiple_authors_addon->get_author_term($user);
            if (empty($term) || empty($term->description)) {
                $multiple_authors_addon->update_author_term($user);
                self::line("Created author term for {$user->user_login}");
            }
        }

        self::success('All done');
    }

    /**
     * Remove author terms from revisions, which we've been adding since the dawn of time
     *
     * @since      3.0.1
     *
     * @subcommand remove-terms-from-revisions
     */
    public function remove_terms_from_revisions()
    {
        global $wpdb;

        $ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type='revision' AND post_status='inherit'");

        self::line('Found ' . count($ids) . ' revisions to look through');
        $affected = 0;
        foreach ($ids as $post_id) {
            $terms = cap_get_coauthor_terms_for_post($post_id);
            if (empty($terms)) {
                continue;
            }

            self::line("#{$post_id}: Removing " . implode(',', wp_list_pluck($terms, 'slug')));
            wp_set_post_terms($post_id, [], 'author');
            $affected++;
        }
        self::line("All done! {$affected} revisions had author terms removed");
    }
}
