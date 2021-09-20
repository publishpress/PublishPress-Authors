<?php
/**
 * PublishPress Authors commands for the WP-CLI framework
 *
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 * @see     https://github.com/wp-cli/wp-cli
 */

namespace MultipleAuthors;

use MultipleAuthors\Classes\Installer;
use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Utils;
use WP_CLI_Command;
use WP_Query;

class WP_Cli extends WP_CLI_Command
{
    public function _logCallback($message, $messageType = 'log')
    {
        if ('log' === $messageType) {
            \WP_CLI::line($message);
        } elseif ('error' === $messageType) {
            \WP_CLI::error($message);
        } elseif ('success' === $messageType) {
            \WP_CLI::success($message);
        }
    }

    /**
     * List all of the posts without assigned co-authors terms
     *
     * @since      3.0
     *
     * @subcommand list-posts-without-terms
     * @synopsis [--post_type=<ptype>] [--posts_per_page=<num>] [--paged=<page>] [--order=<order>] [--orederby=<orderby>]
     */
    public function list_posts_without_terms($args, $assocArgs)
    {
        $defaults   = [
            'post_type'      => 'post',
            'order'          => 'ASC',
            'orderby'        => 'ID',
            'posts_per_page' => 300,
            'paged'          => 1,
        ];
        $parsedArgs = wp_parse_args($assocArgs, $defaults);

        $posts = Installer::getPostsWithoutAuthorTerms($parsedArgs);

        if (count($posts) === 0) {
            \WP_CLI::success(__('No posts without author terms were found', 'publishpress-authors'));
            return;
        }

        foreach ($posts as $singlePost) {
            $postData = [
                $singlePost->ID,
                addslashes($singlePost->post_title),
                get_permalink($singlePost->ID),
                $singlePost->post_date,
            ];
            \WP_CLI::line('"' . implode('","', $postData) . '"');
        }
        \WP_CLI::success(
            sprintf(
                __('%d posts were found without author terms', 'publishpress-authors'),
                count($posts)
            )
        );
    }

    /**
     * Create author terms for all posts that don't have them
     *
     * @subcommand create-terms-for-posts
     * @synopsis [--post_type=<ptype>] [--posts_per_page=<num>] [--paged=<page>]
     */
    public function create_terms_for_posts($args, $assocArgs)
    {
        Installer::createAuthorTermsForPostsWithLegacyCoreAuthors($assocArgs, [$this, '_logCallback']);

        \WP_CLI::success('Finished');
    }

//    /**
//     * Update the post count and description for each author
//     *
//     * @since      3.0
//     *
//     * @subcommand update-author-terms
//     * @synopsis [--post_type=<ptype>] [--posts_per_page=<num>] [--paged=<page>]
//     */
//    public function update_author_terms($args, $assocArgs)
//    {
//        Installer::createAuthorTermsForLegacyCoreAuthors($assocArgs, [$this, '_logCallback']);
//
//        \WP_CLI::success('Finished');
//    }

    /**
     * Clear all of the caches for memory management
     */
    private function clearAllCaches()
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
     * @subcommand assign-author-by-meta-key
     * @synopsis [--meta_key=<key>] [--post_type=<ptype>] [--append_author=<bool>] [--posts_per_page=<num>] [--paged=<page>] [--post_status=<string>]
     */
    public function assign_author_by_meta_key($args, $assocArgs)
    {
        $defaults   = [
            'meta_key'         => '_original_import_author',
            'post_type'        => 'post',
            'order'            => 'ASC',
            'orderby'          => 'ID',
            'posts_per_page'   => 100,
            'paged'            => 1,
            'append_author' => false,
        ];
        $this->args = wp_parse_args($assocArgs, $defaults);

        // For global use and not a part of WP_Query
        $appendAuthor = $this->args['append_author'];
        unset($this->args['append_author']);

        $postsTotal             = 0;
        $postsAlreadyAssociated = 0;
        $postsMissingCoauthor   = 0;
        $postsAssociated        = 0;
        $missionCoauthors       = [];

        $posts = new WP_Query($this->args);
        while ($posts->post_count) {
            foreach ($posts->posts as $singlePost) {
                $postsTotal++;

                // See if the value in the post meta field is the same as any of the existing coauthors
                $originalAuthor    = get_post_meta($singlePost->ID, $this->args['meta_key'], true);
                $existingCoauthors = get_multiple_authors($singlePost->ID);
                $isAlreadyAssociated = false;
                foreach ($existingCoauthors as $existingCoauthor) {
                    if ($originalAuthor == $existingCoauthor->user_nicename) {
                        $isAlreadyAssociated = true;
                    }
                }
                if ($isAlreadyAssociated && $appendAuthor) {
                    $postsAlreadyAssociated++;
                    \WP_CLI::line(
                        $postsTotal . ': Post #' . $singlePost->ID . ' already has "' . $originalAuthor . '" associated as a coauthor'
                    );
                    continue;
                }

                $coauthor = Author::get_by_term_slug(sanitize_title($originalAuthor));
                if (false === $coauthor || is_wp_error($coauthor)) {
                    $postsMissingCoauthor++;
                    $missionCoauthors[] = $originalAuthor;
                    \WP_CLI::line(
                        $postsTotal . ': Post #' . $singlePost->ID . ' does not have "' . $originalAuthor . '" associated as a coauthor but there is not a coauthor profile'
                    );
                    continue;
                }


                if ($appendAuthor) {
                    $coauthors = $existingCoauthors;
                    $coauthors[] = $coauthor;
                } else {
                    $coauthors = [$coauthor];
                }

                Utils::set_post_authors($singlePost->ID, $coauthors);

                \WP_CLI::line(
                    $postsTotal . ': Post #' . $singlePost->ID . ' has been assigned "' . $originalAuthor . '" as the author'
                );
                $postsAssociated++;
                clean_post_cache($singlePost->ID);
            }

            $this->args['paged']++;
            $this->clearAllCaches();
            $posts = new WP_Query($this->args);
        }

        \WP_CLI::line('All done! Here are your results:');
        if ($postsAlreadyAssociated) {
            \WP_CLI::line("- {$postsAlreadyAssociated} posts already had the coauthor assigned");
        }
        if ($postsMissingCoauthor) {
            \WP_CLI::line("- {$postsMissingCoauthor} posts reference coauthors that don't exist. These are:");
            \WP_CLI::line('  ' . implode(', ', array_unique($missionCoauthors)));
        }
        if ($postsAssociated) {
            \WP_CLI::line("- {$postsAssociated} posts now have the proper coauthor");
        }
    }
//
//    /**
//     * Assign posts associated with a WordPress user to a co-author
//     * Only apply the changes if there aren't yet co-authors associated with the post
//     *
//     * @since      3.0
//     *
//     * @subcommand assign-user-to-coauthor
//     * @synopsis   --user_login=<user-login> --coauthor=<coauthor>
//     */
//    public function assign_user_to_coauthor($args, $assoc_args)
//    {
//        global $multiple_authors_addon, $wpdb;
//
//        $defaults   = [
//            'user_login' => '',
//            'coauthor'   => '',
//        ];
//        $assoc_args = wp_parse_args($assoc_args, $defaults);
//
//        $user     = get_user_by('login', $assoc_args['user_login']);
//        $coauthor = $multiple_authors_addon->get_coauthor_by('login', $assoc_args['coauthor']);
//
//        if (!$user) {
//            \WP_CLI::error(__('Please specify a valid user_login', 'publishpress-authors'));
//        }
//
//        if (!$coauthor) {
//            \WP_CLI::error(__('Please specify a valid co-author login', 'publishpress-authors'));
//        }
//
//        $post_types = implode("','", $multiple_authors_addon->supported_post_types);
//        $posts      = $wpdb->get_col(
//            $wpdb->prepare(
//                "SELECT ID FROM $wpdb->posts WHERE post_author=%d AND post_type IN ('$post_types')",
//                $user->ID
//            )
//        );
//        $affected   = 0;
//        foreach ($posts as $post_id) {
//            $coauthors = cap_get_coauthor_terms_for_post($post_id);
//            if (!empty($coauthors)) {
//                \WP_CLI::line(
//                    sprintf(
//                        __('Skipping - Post #%d already has co-authors assigned: %s', 'publishpress-authors'),
//                        $post_id,
//                        implode(', ', wp_list_pluck($coauthors, 'slug'))
//                    )
//                );
//                continue;
//            }
//
//            $multiple_authors_addon->add_coauthors($post_id, [$coauthor->user_login]);
//            \WP_CLI::line(
//                sprintf(
//                    __("Updating - Adding %s's byline to post #%d", 'publishpress-authors'),
//                    $coauthor->user_login,
//                    $post_id
//                )
//            );
//            $affected++;
//            if ($affected && 0 === $affected % 20) {
//                sleep(5);
//            }
//        }
//        \WP_CLI::success(
//            sprintf(
//                __('All done! %d posts were affected.', 'publishpress-authors'),
//                $affected
//            )
//        );
//    }
//
//    /**
//     * Subcommand to reassign co-authors based on some given format
//     * This will look for terms with slug 'x' and rename to term with slug and name 'y'
//     * This subcommand can be helpful for cleaning up after an import if the usernames
//     * for authors have changed. During the import process, 'author' terms will be
//     * created with the old user_login value. We can use this to migrate to the new user_login
//     *
//     * @todo       support reassigning by CSV
//     *
//     * @since      3.0
//     *
//     * @subcommand reassign-terms
//     * @synopsis [--author-mapping=<file>] [--old_term=<slug>] [--new_term=<slug>]
//     */
//    public function reassign_terms($args, $assoc_args)
//    {
//        global $multiple_authors_addon;
//
//        $defaults   = [
//            'author_mapping' => null,
//            'old_term'       => null,
//            'new_term'       => null,
//        ];
//        $this->args = wp_parse_args($assoc_args, $defaults);
//
//        $author_mapping = $this->args['author_mapping'];
//        $old_term       = $this->args['old_term'];
//        $new_term       = $this->args['new_term'];
//
//        // Get the reassignment data
//        if ($author_mapping && file_exists($author_mapping)) {
//            require_once($author_mapping);
//            $authors_to_migrate = $cli_user_map;
//        } elseif ($author_mapping) {
//            \WP_CLI::error("author_mapping doesn't exist: " . $author_mapping);
//            exit;
//        }
//
//        // Alternate reassigment approach
//        if ($old_term && $new_term) {
//            $authors_to_migrate = [
//                $old_term => $new_term,
//            ];
//        }
//
//        // For each author to migrate, check whether the term exists,
//        // whether the target term exists, and only do the migration if both are met
//        $results = (object)[
//            'old_term_missing' => 0,
//            'new_term_exists'  => 0,
//            'success'          => 0,
//        ];
//        foreach ($authors_to_migrate as $old_user => $new_user) {
//            if (is_numeric($new_user)) {
//                $new_user = get_user_by('id', $new_user)->user_login;
//            }
//
//            // The old user should exist as a term
//            $old_term = $multiple_authors_addon->get_author_term(
//                $multiple_authors_addon->get_coauthor_by(
//                    'login',
//                    $old_user
//                )
//            );
//            if (!$old_term) {
//                \WP_CLI::line("Error: Term '{$old_user}' doesn't exist, skipping");
//                $results->old_term_missing++;
//                continue;
//            }
//
//            // If the new user exists as a term already, we want to reassign all posts to that
//            // new term and delete the original
//            // Otherwise, simply rename the old term
//            $new_term = $multiple_authors_addon->get_author_term(
//                $multiple_authors_addon->get_coauthor_by(
//                    'login',
//                    $new_user
//                )
//            );
//            if (is_object($new_term)) {
//                \WP_CLI::line(
//                    "Success: There's already a '{$new_user}' term for '{$old_user}'. Reassigning {$old_term->count} posts and then deleting the term"
//                );
//                $args = [
//                    'default'       => $new_term->term_id,
//                    'force_default' => true,
//                ];
//                wp_delete_term($old_term->term_id, $multiple_authors_addon->coauthor_taxonomy, $args);
//                $results->new_term_exists++;
//            } else {
//                $args = [
//                    'slug' => $new_user,
//                    'name' => $new_user,
//                ];
//                wp_update_term($old_term->term_id, $multiple_authors_addon->coauthor_taxonomy, $args);
//                \WP_CLI::line("Success: Converted '{$old_user}' term to '{$new_user}'");
//                $results->success++;
//            }
//            clean_term_cache($old_term->term_id, $multiple_authors_addon->coauthor_taxonomy);
//        }
//
//        \WP_CLI::line('Reassignment complete. Here are your results:');
//        \WP_CLI::line("- $results->success authors were successfully reassigned terms");
//        \WP_CLI::line("- $results->new_term_exists authors had their old term merged to their new term");
//        \WP_CLI::line("- $results->old_term_missing authors were missing old terms");
//    }
//
//    /**
//     * Change a term from representing one user_login value to another
//     *
//     * @since      3.0.1
//     *
//     * @subcommand rename-coauthor
//     * @synopsis   --from=<user-login> --to=<user-login>
//     */
//    public function rename_coauthor($args, $assoc_args)
//    {
//        global $multiple_authors_addon, $wpdb;
//
//        $defaults   = [
//            'from' => null,
//            'to'   => null,
//        ];
//        $assoc_args = array_merge($defaults, $assoc_args);
//
//        $to_userlogin = $assoc_args['to'];
//
//        $orig_coauthor = $multiple_authors_addon->get_coauthor_by('user_login', $assoc_args['from']);
//        if (!$orig_coauthor) {
//            \WP_CLI::error("No co-author found for {$assoc_args['from']}");
//        }
//
//        if (!$to_userlogin) {
//            \WP_CLI::error('--to param must not be empty');
//        }
//
//        if ($multiple_authors_addon->get_coauthor_by('user_login', $to_userlogin)) {
//            \WP_CLI::error('New user_login value conflicts with existing co-author');
//        }
//
//        $orig_term = $multiple_authors_addon->get_author_term($orig_coauthor);
//
//        \WP_CLI::line("Renaming {$orig_term->name} to {$to_userlogin}");
//        $rename_args = [
//            'name' => $to_userlogin,
//            'slug' => $to_userlogin,
//        ];
//        wp_update_term($orig_term->term_id, $multiple_authors_addon->coauthor_taxonomy, $rename_args);
//
//        \WP_CLI::success('All done!');
//    }
//
//    /**
//     * Swap one Co Author with another on all posts for which they are an author. Unlike rename-coauthor,
//     * this leaves the original Co Author term intact and works when the 'to' user already has a co-author term.
//     *
//     * @subcommand swap-coauthors
//     * @synopsis   --from=<user-login> --to=<user-login> [--post_type=<ptype>] [--dry=<dry>]
//     */
//    public function swap_coauthors($args, $assoc_args)
//    {
//        global $multiple_authors_addon, $wpdb;
//
//        $defaults = [
//            'from'      => null,
//            'to'        => null,
//            'post_type' => 'post',
//            'dry'       => false,
//        ];
//
//        $assoc_args = array_merge($defaults, $assoc_args);
//
//        $dry = $assoc_args['dry'];
//
//        $from_userlogin = $assoc_args['from'];
//        $to_userlogin   = $assoc_args['to'];
//
//        $orig_coauthor = $multiple_authors_addon->get_coauthor_by('user_login', $from_userlogin);
//
//        if (!$orig_coauthor) {
//            \WP_CLI::error("No co-author found for $from_userlogin");
//        }
//
//        if (!$to_userlogin) {
//            \WP_CLI::error('--to param must not be empty');
//        }
//
//        $to_coauthor = $multiple_authors_addon->get_coauthor_by('user_login', $to_userlogin);
//
//        if (!$to_coauthor) {
//            \WP_CLI::error("No co-author found for $to_userlogin");
//        }
//
//        \WP_CLI::line("Swapping authorship from {$from_userlogin} to {$to_userlogin}");
//
//        $query_args = [
//            'post_type'      => $assoc_args['post_type'],
//            'order'          => 'ASC',
//            'orderby'        => 'ID',
//            'posts_per_page' => 100,
//            'paged'          => 1,
//            'tax_query'      => [
//                [
//                    'taxonomy' => $multiple_authors_addon->coauthor_taxonomy,
//                    'field'    => 'slug',
//                    'terms'    => [$from_userlogin],
//                ],
//            ],
//        ];
//
//        $posts = new WP_Query($query_args);
//
//        $posts_total = 0;
//
//        \WP_CLI::line("Found $posts->found_posts posts to update.");
//
//        while ($posts->post_count) {
//            foreach ($posts->posts as $post) {
//                $coauthors = get_multiple_authors($post->ID);
//
//                if (!is_array($coauthors) || !count($coauthors)) {
//                    continue;
//                }
//
//                $coauthors = wp_list_pluck($coauthors, 'user_login');
//
//                $posts_total++;
//
//                if (!$dry) {
//                    // Remove the $from_userlogin from $coauthors
//                    foreach ($coauthors as $index => $user_login) {
//                        if ($from_userlogin === $user_login) {
//                            unset($coauthors[$index]);
//
//                            break;
//                        }
//                    }
//
//                    // Add the 'to' author on
//                    $coauthors[] = $to_userlogin;
//
//                    // By not passing $append = false as the 3rd param, we replace all existing coauthors
//                    $multiple_authors_addon->add_coauthors($post->ID, $coauthors, false);
//
//                    \WP_CLI::line(
//                        $posts_total . ': Post #' . $post->ID . ' has been assigned "' . $to_userlogin . '" as a co-author'
//                    );
//
//                    clean_post_cache($post->ID);
//                } else {
//                    \WP_CLI::line(
//                        $posts_total . ': Post #' . $post->ID . ' will be assigned "' . $to_userlogin . '" as a co-author'
//                    );
//                }
//            }
//
//            // In dry mode, we must manually advance the page
//            if ($dry) {
//                $query_args['paged']++;
//            }
//
//            $this->clearAllCaches();
//
//            $posts = new WP_Query($query_args);
//        }
//
//        \WP_CLI::success('All done!');
//    }
//
//    /**
//     * Update the post count and description for each author
//     *
//     * @since      3.0
//     *
//     * @subcommand update-author-terms
//     */
//    public function update_author_terms()
//    {
//        global $multiple_authors_addon;
//        $author_terms = get_terms($multiple_authors_addon->coauthor_taxonomy, ['hide_empty' => false]);
//        \WP_CLI::line('Now updating ' . count($author_terms) . ' terms');
//        foreach ($author_terms as $author_term) {
//            $old_count = $author_term->count;
//            $coauthor  = $multiple_authors_addon->get_coauthor_by('user_nicename', $author_term->slug);
//            $multiple_authors_addon->update_author_term($coauthor);
//            wp_cache_delete($author_term->term_id, $multiple_authors_addon->coauthor_taxonomy);
//            $new_count = get_term_by('id', $author_term->term_id, $multiple_authors_addon->coauthor_taxonomy)->count;
//            \WP_CLI::line(
//                "Term {$author_term->slug} ({$author_term->term_id}) changed from {$old_count} to {$new_count} and the description was refreshed"
//            );
//        }
//        // Create author terms for any users that don't have them
//        $users = get_users();
//        foreach ($users as $user) {
//            $term = $multiple_authors_addon->get_author_term($user);
//            if (empty($term) || empty($term->description)) {
//                $multiple_authors_addon->update_author_term($user);
//                \WP_CLI::line("Created author term for {$user->user_login}");
//            }
//        }
//
//        \WP_CLI::success('All done');
//    }
//
//    /**
//     * Remove author terms from revisions, which we've been adding since the dawn of time
//     *
//     * @since      3.0.1
//     *
//     * @subcommand remove-terms-from-revisions
//     */
//    public function remove_terms_from_revisions()
//    {
//        global $wpdb;
//
//        $ids = $wpdb->get_col("SELECT ID FROM $wpdb->posts WHERE post_type='revision' AND post_status='inherit'");
//
//        \WP_CLI::line('Found ' . count($ids) . ' revisions to look through');
//        $affected = 0;
//        foreach ($ids as $post_id) {
//            $terms = cap_get_coauthor_terms_for_post($post_id);
//            if (empty($terms)) {
//                continue;
//            }
//
//            \WP_CLI::line("#{$post_id}: Removing " . implode(',', wp_list_pluck($terms, 'slug')));
//            wp_set_post_terms($post_id, [], 'author');
//            $affected++;
//        }
//        \WP_CLI::line("All done! {$affected} revisions had author terms removed");
//    }
}
