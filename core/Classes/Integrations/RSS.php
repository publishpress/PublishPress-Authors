<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthors\Classes\Integrations;

use MultipleAuthors\Classes\Content_Model;

/**
 * Render authors within WordPress' default RSS feed templates.
 *
 * Based on Bylines
 *
 * @package MultipleAuthors\Classes
 */
class RSS
{

    /**
     * Display the first author in WordPress' use of the_author()
     *
     * @param string $author Existing author string.
     */
    public static function filter_the_author($author)
    {
        if (!is_feed() || !self::is_supported_post_type()) {
            return $author;
        }

        $authors = get_multiple_authors();
        $first   = array_shift($authors);

        return !empty($first) ? $first->display_name : '';
    }

    /**
     * Whether or not the global post is a supported post type
     *
     * @return bool
     */
    private static function is_supported_post_type()
    {
        global $post;

        // Can't determine post, so assume true.
        if (!$post) {
            return true;
        }

        return in_array($post->post_type, Content_Model::get_author_supported_post_types(), true);
    }

    /**
     * Add any additional authors to the feed.
     */
    public static function action_rss2_item()
    {
        if (!self::is_supported_post_type()) {
            return;
        }
        $authors = get_multiple_authors();
        // Ditch the first author, which was already rendered above.
        array_shift($authors);
        foreach ($authors as $author) {
            echo '<dc:creator><![CDATA[' . esc_html($author->display_name) . ']]></dc:creator>' . PHP_EOL;
        }
    }

}
