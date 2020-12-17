<?php

/**
 * @package   MultipleAuthors
 * @author    PublishPress <help@publishpress.com>
 * @copyright Copyright (C) 2018 PublishPress. All rights reserved.
 * @license   GPLv2 or later
 * @since     1.0.0
 */

use MultipleAuthors\Classes\Authors_Iterator;
use MultipleAuthors\Classes\Legacy\Util;
use MultipleAuthors\Classes\Objects\Author;

if (!function_exists('get_multiple_authors')) {
    /**
     * Get all authors for a post.
     *
     * @param WP_Post|int|null $post Post to fetch authors for. Defaults to global post.
     * @param bool $filter_the_author If false, will not trigger the filter for the author, to avoid infinite
     *                                            loop.
     * @param bool $archive If true, will ignore the $post param and return the current author
     *                                            specified by the "author_name" URL param - for author pages.
     * @param bool $ignoreCache This cache cause sometimes errors in data received especially
     *                                            in quick edit after saving.
     *                                            That's why in Post_Editor we called this function with overriding
     *                                            ignoreCache value to be equal true.
     *
     * @return array Array of Author objects, a single WP_User object, or empty.
     */
    function get_multiple_authors($post = 0, $filter_the_author = true, $archive = false, $ignoreCache = false)
    {
        global $wpdb;

        if (is_object($post)) {
            $post = $post->ID;
        } elseif (empty($post)) {
            $post = get_post();

            if (is_object($post) && !is_wp_error($post)) {
                $post = $post->ID;
            }
        }

        $postId = (int)$post;


        $cacheKey = $postId . ':' . ($filter_the_author ? 1 : 0) . ':' . ($archive ? 1 : 0);

        $authorName = '';
        if ($archive) {
            $authorName = get_query_var('author_name');

            $cacheKey .= ':' . $authorName;
        }

        $authors = wp_cache_get($cacheKey, 'get_multiple_authors:authors');

        if (false === $authors || $ignoreCache) {
            $authors = [];

            if (!$archive) {
                if (empty($postId)) {
                    $post = get_post();

                    if (!empty($post)) {
                        $postId = $post->ID;
                    }
                }

                if (empty($postId)) {
                    return [];
                }

                $terms = wp_cache_get($postId, 'get_multiple_authors:terms');

                if (false === $terms || $ignoreCache) {
                    $terms = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT tt.term_id
                                FROM {$wpdb->term_relationships} AS tr
                                INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tr.`term_taxonomy_id` = tt.`term_taxonomy_id`)
                                WHERE tr.object_id = %d AND tt.taxonomy = 'author'
                                ORDER BY tr.term_order",
                            $postId
                        )
                    );

                    wp_cache_set($postId, $terms, 'get_multiple_authors:terms');
                }

            } else {
                // Get the term related to the current author from the archive page.
                $terms = [];

                if (!empty($authorName)) {
                    $terms[] = get_term_by('slug', $authorName, 'author');
                }
            }

            if (is_wp_error($terms)) {
                return [];
            }

            if (!empty($terms)) {
                // We found authors
                foreach ($terms as $term) {
                    if (is_wp_error($term) || empty($term)) {
                        continue;
                    }

                    if (is_object($term)) {
                        $term = $term->term_id;
                    }

                    $termId = (int)$term;

                    $author = Author::get_by_term_id($termId);

                    if ($filter_the_author && !is_wp_error($author)) {
                        $author->display_name = apply_filters('the_author', $author->display_name);
                    }

                    $authors[] = $author;
                }
            } else {
                // Fallback to the post author
                $post = get_post($postId);
                $user = get_user_by('id', $post->post_author);

                if ($user) {
                    $user->link = get_author_posts_url($user->ID);

                    if ($filter_the_author) {
                        $user->display_name = apply_filters('the_author', $user->display_name);
                    }

                    $authors = [$user];
                }
            }

            wp_cache_set($cacheKey, $authors, 'get_multiple_authors:authors');
        }

        return empty($authors) ? [] : $authors;
    }
}

if (!function_exists('multiple_authors_get_all_authors')) {
    /**
     * @param array $args
     *
     * @return array|int|WP_Error
     */
    function multiple_authors_get_all_authors($args = [])
    {
        $defaults = [
            'hide_empty' => false,
        ];

        $args = wp_parse_args($args, $defaults);

        $terms   = get_terms('author', $args);
        $authors = [];
        foreach ($terms as $term) {
            $author               = Author::get_by_term_id($term->term_id);
            $author->display_name = apply_filters('the_author', $author->display_name);
            $authors[]            = $author;
        }

        return $authors;
    }
}

if (!function_exists('is_multiple_author_for_post')) {
    /**
     * Checks to see if the the specified user is author of the current global post or post (if specified)
     *
     * @param object|int $user
     * @param int $post_id
     */
    function is_multiple_author_for_post($user, $post_id = 0)
    {
        global $post;
        global $postAuthorsCache;

        if (empty($postAuthorsCache)) {
            $postAuthorsCache = [];
        }

        if (!$post_id && $post) {
            $post_id = $post->ID;
        }

        if (!$post_id) {
            return false;
        }

        if (!$user) {
            return false;
        }

        if (!isset($postAuthorsCache[$post_id])) {
            $coauthors = get_multiple_authors($post_id);

            $postAuthorsCache[$post_id] = $coauthors;
        }
        $coauthors = $postAuthorsCache[$post_id];


        if (is_numeric($user)) {
            $user = (int)$user;

            if ($user > 0) {
                $user_term = Author::get_by_user_id($user);
            } elseif ($user < 0) {
                $user_term = Author::get_by_term_id($user);
            }
        } else {
            $user_term = Author::get_by_user_id($user->ID);
        }

        if (empty($user_term) || is_wp_error($user_term)) {
            return false;
        }

        foreach ($coauthors as $coauthor) {
            if ($user_term->term_id == $coauthor->term_id) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('multiple_authors__echo')) {
    //Helper function for the following new template tags
    function multiple_authors__echo($tag, $type = 'tag', $separators = [], $tag_args = null, $echo = true)
    {
        // Define the standard output separator. Constant support is for backwards compat.
        $default_before       = (defined('COAUTHORS_DEFAULT_BEFORE')) ? COAUTHORS_DEFAULT_BEFORE : '';
        $default_between      = (defined('COAUTHORS_DEFAULT_BETWEEN')) ? COAUTHORS_DEFAULT_BETWEEN : ', ';
        $default_between_last = (defined('COAUTHORS_DEFAULT_BETWEEN_LAST')) ? COAUTHORS_DEFAULT_BETWEEN_LAST : __(
            ' and ',
            'publishpress-authors'
        );
        $default_after        = (defined('COAUTHORS_DEFAULT_AFTER')) ? COAUTHORS_DEFAULT_AFTER : '';

        if (!isset($separators['before']) || null === $separators['before']) {
            $separators['before'] = apply_filters('coauthors_default_before', $default_before);
        }
        if (!isset($separators['between']) || null === $separators['between']) {
            $separators['between'] = apply_filters('coauthors_default_between', $default_between);
        }
        if (!isset($separators['betweenLast']) || null === $separators['betweenLast']) {
            $separators['betweenLast'] = apply_filters('coauthors_default_between_last', $default_between_last);
        }
        if (!isset($separators['after']) || null === $separators['after']) {
            $separators['after'] = apply_filters('coauthors_default_after', $default_after);
        }

        $output = '';

        $authors_iterator = new Authors_Iterator();
        $output           .= $separators['before'];
        while ($authors_iterator->iterate()) {
            $author_text = '';

            if ('tag' === $type) {
                $author_text = $tag($tag_args);
            } else {
                if ('field' === $type && isset($authors_iterator->current_author->$tag)) {
                    $author_text = $authors_iterator->current_author->$tag;
                } else {
                    if ('callback' === $type && is_callable($tag)) {
                        $author_text = call_user_func($tag, $authors_iterator->current_author);
                    }
                }
            }

            // Fallback to user_nicename if we get something empty
            if (empty($author_text)) {
                $author_text = $authors_iterator->current_author->user_nicename;
            }

            // Append separators
            if ($authors_iterator->count() - $authors_iterator->position == 1) { // last author or only author
                $output .= $author_text;
            } else {
                if ($authors_iterator->count() - $authors_iterator->position == 2) { // second to last
                    $output .= $author_text . $separators['betweenLast'];
                } else {
                    $output .= $author_text . $separators['between'];
                }
            }
        }

        $output .= $separators['after'];

        if ($echo) {
            echo $output;
        }

        return $output;
    }
}

if (!function_exists('multiple_authors')) {
    /**
     * Outputs the co-authors display names, without links to their posts.
     * PublishPress Authors equivalent of the_author() template tag.
     *
     * @param string $between Delimiter that should appear between the co-authors
     * @param string $betweenLast Delimiter that should appear between the last two co-authors
     * @param string $before What should appear before the presentation of co-authors
     * @param string $after What should appear after the presentation of co-authors
     * @param bool $echo Whether the co-authors should be echoed or returned. Defaults to true.
     */
    function multiple_authors($between = null, $betweenLast = null, $before = null, $after = null, $echo = true)
    {
        return multiple_authors__echo(
            'display_name',
            'field',
            [
                'between'     => $between,
                'betweenLast' => $betweenLast,
                'before'      => $before,
                'after'       => $after,
            ],
            null,
            $echo
        );
    }
}

if (!function_exists('multiple_authors_posts_links')) {
    /**
     * Outputs the co-authors display names, with links to their posts.
     * PublishPress Authors equivalent of the_author_posts_link() template tag.
     *
     * @param string $between Delimiter that should appear between the co-authors
     * @param string $betweenLast Delimiter that should appear between the last two co-authors
     * @param string $before What should appear before the presentation of co-authors
     * @param string $after What should appear after the presentation of co-authors
     * @param bool $echo Whether the co-authors should be echoed or returned. Defaults to true.
     */
    function multiple_authors_posts_links(
        $between = null,
        $betweenLast = null,
        $before = null,
        $after = null,
        $echo = true
    ) {
        return multiple_authors__echo(
            'multiple_authors_posts_links_single',
            'callback',
            [
                'between'     => $between,
                'betweenLast' => $betweenLast,
                'before'      => $before,
                'after'       => $after,
            ],
            null,
            $echo
        );
    }
}

if (!function_exists('multiple_authors_posts_links_single')) {
    /**
     * Outputs a single co-author linked to their post archive.
     *
     * @param object $author
     *
     * @return string
     */
    function multiple_authors_posts_links_single($author)
    {
        // Return if the fields we are trying to use are not sent
        if (!isset($author->display_name)) {
            _doing_it_wrong(
                'multiple_authors_posts_links_single',
                'Invalid author object used',
                '3.2'
            );

            return;
        }
        $args        = [
            'before_html' => '',
            'href'        => get_author_posts_url($author->ID, $author->user_nicename),
            'rel'         => 'author',
            'title'       => sprintf(
                __('Posts by %s', 'publishpress-authors'),
                apply_filters('the_author', $author->display_name)
            ),
            'class'       => 'author url fn',
            'text'        => apply_filters('the_author', $author->display_name),
            'after_html'  => '',
        ];
        $args        = apply_filters('coauthors_posts_link', $args, $author);
        $single_link = sprintf(
            '<a href="%1$s" title="%2$s" class="%3$s" rel="%4$s">%5$s</a>',
            esc_url($args['href']),
            esc_attr($args['title']),
            esc_attr($args['class']),
            esc_attr($args['rel']),
            esc_html($args['text'])
        );

        return $args['before_html'] . $single_link . $args['after_html'];
    }
}

if (!function_exists('multiple_authors_firstnames')) {
    /**
     * Outputs the co-authors first names, without links to their posts.
     *
     * @param string $between Delimiter that should appear between the co-authors
     * @param string $betweenLast Delimiter that should appear between the last two co-authors
     * @param string $before What should appear before the presentation of co-authors
     * @param string $after What should appear after the presentation of co-authors
     * @param bool $echo Whether the co-authors should be echoed or returned. Defaults to true.
     */
    function multiple_authors_firstnames(
        $between = null,
        $betweenLast = null,
        $before = null,
        $after = null,
        $echo = true
    ) {
        return multiple_authors__echo(
            'get_the_author_meta',
            'tag',
            [
                'between'     => $between,
                'betweenLast' => $betweenLast,
                'before'      => $before,
                'after'       => $after,
            ],
            'first_name',
            $echo
        );
    }
}

if (!function_exists('multiple_authors_lastnames')) {
    /**
     * Outputs the co-authors last names, without links to their posts.
     *
     * @param string $between Delimiter that should appear between the co-authors
     * @param string $betweenLast Delimiter that should appear between the last two co-authors
     * @param string $before What should appear before the presentation of co-authors
     * @param string $after What should appear after the presentation of co-authors
     * @param bool $echo Whether the co-authors should be echoed or returned. Defaults to true.
     */
    function multiple_authors_lastnames(
        $between = null,
        $betweenLast = null,
        $before = null,
        $after = null,
        $echo = true
    ) {
        return multiple_authors__echo(
            'get_the_author_meta',
            'tag',
            [
                'between'     => $between,
                'betweenLast' => $betweenLast,
                'before'      => $before,
                'after'       => $after,
            ],
            'last_name',
            $echo
        );
    }
}

if (!function_exists('multiple_authors_nicknames')) {
    /**
     * Outputs the co-authors nicknames, without links to their posts.
     *
     * @param string $between Delimiter that should appear between the co-authors
     * @param string $betweenLast Delimiter that should appear between the last two co-authors
     * @param string $before What should appear before the presentation of co-authors
     * @param string $after What should appear after the presentation of co-authors
     * @param bool $echo Whether the co-authors should be echoed or returned. Defaults to true.
     */
    function multiple_authors_nicknames(
        $between = null,
        $betweenLast = null,
        $before = null,
        $after = null,
        $echo = true
    ) {
        return multiple_authors__echo(
            'get_the_author_meta',
            'tag',
            [
                'between'     => $between,
                'betweenLast' => $betweenLast,
                'before'      => $before,
                'after'       => $after,
            ],
            'nickname',
            $echo
        );
    }
}

if (!function_exists('multiple_authors_links')) {
    /**
     * Outputs the co-authors display names, with links to their websites if they've provided them.
     *
     * @param string $between Delimiter that should appear between the co-authors
     * @param string $betweenLast Delimiter that should appear between the last two co-authors
     * @param string $before What should appear before the presentation of co-authors
     * @param string $after What should appear after the presentation of co-authors
     * @param bool $echo Whether the co-authors should be echoed or returned. Defaults to true.
     */
    function multiple_authors_links($between = null, $betweenLast = null, $before = null, $after = null, $echo = true)
    {
        return multiple_authors__echo(
            'multiple_authors_links_single',
            'callback',
            [
                'between'     => $between,
                'betweenLast' => $betweenLast,
                'before'      => $before,
                'after'       => $after,
            ],
            null,
            $echo
        );
    }
}

if (!function_exists('multiple_authors_emails')) {
    /**
     * Outputs the co-authors email addresses
     *
     * @param string $between Delimiter that should appear between the email addresses
     * @param string $betweenLast Delimiter that should appear between the last two email addresses
     * @param string $before What should appear before the presentation of email addresses
     * @param string $after What should appear after the presentation of email addresses
     * @param bool $echo Whether the co-authors should be echoed or returned. Defaults to true.
     */
    function multiple_authors_emails($between = null, $betweenLast = null, $before = null, $after = null, $echo = true)
    {
        return multiple_authors__echo(
            'get_the_author_meta',
            'tag',
            [
                'between'     => $between,
                'betweenLast' => $betweenLast,
                'before'      => $before,
                'after'       => $after,
            ],
            'user_email',
            $echo
        );
    }
}

if (!function_exists('multiple_authors_links_single')) {
    /**
     * Outputs a single co-author, linked to their website if they've provided one.
     *
     * @param object $author
     *
     * @return string
     */
    function multiple_authors_links_single($author)
    {
        if (get_the_author_meta('url')) {
            return sprintf(
                '<a href="%s" title="%s" rel="external">%s</a>',
                get_the_author_meta('url'),
                esc_attr(sprintf(__('Visit %s&#8217;s website'), get_the_author())),
                get_the_author()
            );
        } else {
            return get_the_author();
        }
    }
}

if (!function_exists('multiple_authors_ids')) {
    /**
     * Outputs the co-authors IDs
     *
     * @param string $between Delimiter that should appear between the co-authors
     * @param string $betweenLast Delimiter that should appear between the last two co-authors
     * @param string $before What should appear before the presentation of co-authors
     * @param string $after What should appear after the presentation of co-authors
     * @param bool $echo Whether the co-authors should be echoed or returned. Defaults to true.
     */
    function multiple_authors_ids($between = null, $betweenLast = null, $before = null, $after = null, $echo = true)
    {
        return multiple_authors__echo(
            'ID',
            'field',
            [
                'between'     => $between,
                'betweenLast' => $betweenLast,
                'before'      => $before,
                'after'       => $after,
            ],
            null,
            $echo
        );
    }
}

if (!function_exists('get_the_multiple_author_meta')) {
    function get_the_multiple_author_meta($field)
    {
        global $wp_query, $post;

        $authors = get_multiple_authors();
        $meta    = [];

        foreach ($authors as $author) {
            $meta[] = $author->$field;
        }

        return $meta;
    }
}

if (!function_exists('the_multiple_author_meta')) {
    function the_multiple_author_meta($field, $user_id = 0)
    {
        // TODO: need before after options
        echo get_the_multiple_author_meta($field, $user_id);
    }
}

if (!function_exists('multiple_authors_wp_list_authors')) {
    /**
     * List all the *co-authors* of the blog, with several options available.
     * optioncount (boolean) (false): Show the count in parenthesis next to the author's name.
     * show_fullname (boolean) (false): Show their full names.
     * hide_empty (boolean) (true): Don't show authors without any posts.
     * feed (string) (''): If isn't empty, show links to author's feeds.
     * feed_image (string) (''): If isn't empty, use this image to link to feeds.
     * echo (boolean) (true): Set to false to return the output, instead of echoing.
     *
     * @param array $args The argument array.
     *
     * @return null|string The output, if echo is set to false.
     */
    function multiple_authors_wp_list_authors($args = [])
    {
        $defaults = [
            'optioncount'   => false,
            'show_fullname' => false,
            'hide_empty'    => true,
            'feed'          => '',
            'feed_image'    => '',
            'feed_type'     => '',
            'echo'          => true,
            'style'         => 'list',
            'html'          => true,
            'number'        => 20, // A sane limit to start to avoid breaking all the things
        ];

        $args   = wp_parse_args($args, $defaults);
        $return = get_the_authors();

        if (!$args['echo']) {
            return $return;
        }

        echo $return;
    }
}

if (!function_exists('multiple_authors_get_avatar')) {
    /**
     * Retrieve a Co-Author's Avatar.
     *
     * Since Guest Authors doesn't enforce unique email addresses, simply loading the avatar by email won't work when
     * multiple Guest Authors share the same address.
     *
     * This is a replacement for using get_avatar(), which only operates on email addresses and cannot differentiate
     * between Guest Authors (who may share an email) and regular user accounts
     *
     * @param object $coauthor The Co Author
     * @param int $size The desired size
     *
     * @return string             The image tag for the avatar, or an empty string if none could be determined
     */
    function multiple_authors_get_avatar($coauthor, $size = 32, $default = '', $alt = false)
    {
        global $multiple_authors_addon;

        if (is_object($coauthor)) {
            if (method_exists($coauthor, 'get_avatar')) {
                return $coauthor->get_avatar($size);
            }

            $email = $coauthor->user_email;
        } else {
            $email = $coauthor;
        }

        // Make sure we're dealing with an object for which we can retrieve an email
        if (!empty($email)) {
            return get_avatar($email, $size, $default, $alt);
        }

        // Nothing matched, an invalid object was passed.
        return '';
    }
}

// ========================================
// Bylines methods

/**
 * Utility functions for use by themes.
 */

if (!function_exists('the_authors')) {
    /**
     * Renders the authors display names, without links to their posts.
     *
     * Equivalent to the_author() template tag.
     */
    function the_authors()
    {
        echo get_the_authors();
    }
}

if (!function_exists('get_the_authors')) {
    /**
     * Gets the authors display names, without links to their posts.
     *
     * Equivalent to get_the_author() template tag.
     */
    function get_the_authors()
    {
        return authors_render(
            get_multiple_authors(),
            function ($author) {
                return $author->display_name;
            }
        );
    }
}

if (!function_exists('the_authors_posts_links')) {
    /**
     * Renders the authors display names, with links to their posts.
     *
     * Equivalent to the_author_posts_link() template tag.
     */
    function the_authors_posts_links()
    {
        echo get_the_authors_posts_links();
    }
}

if (!function_exists('get_the_authors_posts_links')) {
    /**
     * Renders the authors display names, with links to their posts.
     */
    function get_the_authors_posts_links()
    {
        return authors_render(
            get_multiple_authors(),
            function ($author) {
                $link = is_a($author, 'WP_User') ? get_author_posts_url($author->ID) : $author->link;
                $args = [
                    'before_html' => '',
                    'href'        => $link,
                    'rel'         => 'author',
                    // translators: Posts by a given author.
                    'title'       => sprintf(
                        __('Posts by %1$s', 'publishpress-authors'),
                        apply_filters('the_author', $author->display_name)
                    ),
                    'class'       => 'author url fn',
                    'text'        => apply_filters('the_author', $author->display_name),
                    'after_html'  => '',
                ];
                /**
                 * Arguments for determining the display of authors with posts links
                 *
                 * @param array $args Arguments determining the rendering of the author.
                 * @param Author $author The author to be rendered.
                 */
                $args        = apply_filters('authors_posts_links', $args, $author);
                $single_link = sprintf(
                    '<a href="%1$s" title="%2$s" class="%3$s" rel="%4$s">%5$s</a>',
                    esc_url($args['href']),
                    esc_attr($args['title']),
                    esc_attr($args['class']),
                    esc_attr($args['rel']),
                    esc_html($args['text'])
                );

                return $args['before_html'] . $single_link . $args['after_html'];
            }
        );
    }
}

if (!function_exists('the_authors_links')) {
    /**
     * Renders the authors display names, with their website link if it exists.
     *
     * Equivalent to the_author_link() template tag.
     */
    function the_authors_links()
    {
        echo get_the_authors_links();
    }
}

if (!function_exists('get_the_authors_links')) {
    /**
     * Renders the authors display names, with their website link if it exists.
     */
    function get_the_authors_links()
    {
        return authors_render(
            get_multiple_authors(),
            function ($author) {
                if ($author->user_url) {
                    return sprintf(
                        '<a href="%s" title="%s" rel="external">%s</a>',
                        esc_url($author->user_url),
                        // Translators: refers to the author's website.
                        esc_attr(sprintf(__('Visit %s&#8217;s website'), $author->display_name)),
                        $author->display_name
                    );
                } else {
                    return $author->display_name;
                }
            }
        );
    }
}

if (!function_exists('authors_render')) {
    /**
     * Display one or more authors, according to arguments provided.
     *
     * @param array $authors Set of authors to display.
     * @param callable $render_callback Callback to return rendered author.
     * @param array $args Arguments to affect display.
     */
    function authors_render($authors, $render_callback, $args = [])
    {
        if (
            empty($authors)
            || empty($render_callback)
            || !is_callable($render_callback)
        ) {
            return '';
        }
        $defaults = [
            'between'           => ', ',
            'between_last_two'  => __(' and ', 'publishpress-authors'),
            'between_last_many' => __(', and ', 'publishpress-authors'),
        ];
        $args     = array_merge($defaults, $args);
        $total    = count($authors);
        $current  = 0;
        $output   = '';
        foreach ($authors as $author) {
            $current++;
            if ($current > 1) {
                if ($current === $total) {
                    if (2 === $total) {
                        $output .= $args['between_last_two'];
                    } else {
                        $output .= $args['between_last_many'];
                    }
                } elseif ($total >= 2) {
                    $output .= $args['between'];
                }
            }
            $output .= $render_callback($author);
        }

        return $output;
    }
}

// Keep backward compatibility with Bylines, legacy versions of PublishPress Authors and CoAuthors
if (PUBLISHPRESS_AUTHORS_LOAD_COAUTHORS_FUNCTIONS) {
    require_once 'coauthors-functions.php';
}

if (PUBLISHPRESS_AUTHORS_LOAD_BYLINES_FUNCTIONS) {
    require_once 'bylines-functions.php';
}
