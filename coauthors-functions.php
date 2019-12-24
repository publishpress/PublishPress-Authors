<?php
/**
 * @package     MultipleAuthors\
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.7
 */

if ( ! function_exists('get_coauthors')) {
    function get_coauthors($post_id = 0)
    {
        return get_multiple_authors($post_id);
    }
}

if ( ! function_exists('is_coauthors_for_post')) {
    function is_coauthors_for_post($user, $post_id = 0)
    {
        return is_multiple_author_for_post($user, $post_id);
    }
}

if ( ! class_exists('Couthors_iterator')) {
    class Couthors_iterator extends Multiple_authors_iterator
    {

    }
}

if ( ! function_exists('coauthors__echo')) {
    function coauthors__echo($tag, $type = 'tag', $separators = [], $tag_args = null, $echo = true)
    {
        return multiple_authors__echo($tag, $type, $separators, $tag_args, $echo);
    }
}

if ( ! function_exists('coauthors')) {
    function coauthors($between = null, $betweenLast = null, $before = null, $after = null, $echo = true)
    {
        return multiple_authors($between, $betweenLast, $before, $after, $echo);
    }
}

if ( ! function_exists('coauthors_posts_links')) {
    function coauthors_posts_links(
        $between = null,
        $betweenLast = null,
        $before = null,
        $after = null,
        $echo = true
    ) {
        return multiple_authors_posts_links($between, $betweenLast, $before, $after, $echo);
    }
}

if ( ! function_exists('coauthors_posts_links_single')) {
    function coauthors_posts_links_single($author)
    {
        return multiple_authors_posts_links_single($author);
    }
}

if ( ! function_exists('coauthors_firstnames')) {
    function coauthors_firstnames($between = null, $betweenLast = null, $before = null, $after = null, $echo = true)
    {
        return multiple_authors_firstnames($between, $betweenLast, $before, $after, $echo);
    }
}

if ( ! function_exists('coauthors_lastnames')) {
    function coauthors_lastnames($between = null, $betweenLast = null, $before = null, $after = null, $echo = true)
    {
        return multiple_authors_lastnames($between, $betweenLast, $before, $after, $echo);
    }
}

if ( ! function_exists('coauthors_nicknames')) {
    function coauthors_nicknames($between = null, $betweenLast = null, $before = null, $after = null, $echo = true)
    {
        return multiple_authors_nicknames($between, $betweenLast, $before, $after, $echo);
    }
}

if ( ! function_exists('coauthors_links')) {
    function coauthors_links($between = null, $betweenLast = null, $before = null, $after = null, $echo = true)
    {
        return multiple_authors_links($between, $betweenLast, $before, $after, $echo);
    }
}

if ( ! function_exists('coauthors_emails')) {
    function coauthors_emails($between = null, $betweenLast = null, $before = null, $after = null, $echo = true)
    {
        return multiple_authors_emails($between, $betweenLast, $before, $after, $echo);
    }
}

if ( ! function_exists('coauthors_links_single')) {
    function coauthors_links_single($author)
    {
        return multiple_authors_links_single($author);
    }
}

if ( ! function_exists('coauthors_ids')) {
    function coauthors_ids($between = null, $betweenLast = null, $before = null, $after = null, $echo = true)
    {
        return multiple_authors_ids($between, $betweenLast, $before, $after, $echo);
    }
}

if ( ! function_exists('get_the_coauthor_meta')) {
    function get_the_coauthor_meta($field)
    {
        return get_the_multiple_author_meta($field);
    }
}

if ( ! function_exists('the_coauthor_meta')) {
    function the_coauthor_meta($field, $user_id = 0)
    {
        the_multiple_author_meta($field, $user_id);
    }
}

if ( ! function_exists('coauthors_wp_list_authors')) {
    function coauthors_wp_list_authors($args)
    {
        return multiple_authors_wp_list_authors($args);
    }
}

if ( ! function_exists('coauthors_get_avatar')) {
    function coauthors_get_avatar($coauthor, $size = 32, $default = '', $alt = false)
    {
        return multiple_authors_get_avatar($coauthor, $size, $default, $alt);
    }
}
