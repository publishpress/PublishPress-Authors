<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthors\Classes\Objects;

/**
 * Representation of an individual author.
 * @property int $ID
 * @property string $post_author
 * @property string $post_date
 * @property string $post_date_gmt
 * @property string $post_content
 * @property string $post_title
 * @property string $post_excerpt
 * @property string $post_status
 * @property string $comment_status
 * @property string $ping_status
 * @property string $post_password
 * @property string $post_name
 * @property string $to_ping
 * @property string $pinged
 * @property string $post_modified
 * @property string $post_modified_gmt
 * @property string $post_content_filtered
 * @property int $post_parent
 * @property string $guid
 * @property int $menu_order
 * @property string $post_type
 * @property string $post_mime_type
 * @property string $comment_count
 * @property string $filter
 */
class Post
{

    /**
     * @var \WP_Post
     */
    private $postObject;

    /**
     * Instantiate a new post object
     *
     * @param WP_Post|int $post ID for the correlated post or the post instance.
     */
    public function __construct($post)
    {
        if ($post instanceof \WP_Post) {
            $this->postObject = $post;
        } else {
            $this->postObject = get_post((int)$post);
        }
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        $properties = get_object_vars($this);

        $properties['ID']                    = true;
        $properties['post_author']           = true;
        $properties['post_date']             = true;
        $properties['post_date_gmt']         = true;
        $properties['post_content']          = true;
        $properties['post_title']            = true;
        $properties['post_excerpt']          = true;
        $properties['post_status']           = true;
        $properties['comment_status']        = true;
        $properties['ping_status']           = true;
        $properties['post_password']         = true;
        $properties['post_name']             = true;
        $properties['to_ping']               = true;
        $properties['pinged']                = true;
        $properties['post_modified']         = true;
        $properties['post_modified_gmt']     = true;
        $properties['post_content_filtered'] = true;
        $properties['post_parent']           = true;
        $properties['guid']                  = true;
        $properties['menu_order']            = true;
        $properties['post_type']             = true;
        $properties['post_mime_type']        = true;
        $properties['comment_count']         = true;
        $properties['filter']                = true;

        $isset = array_key_exists($name, $properties);

        if (!$isset) {
            $isset = apply_filters(
                'publishpress_authors_layout_post_property_isset',
                $isset,
                $this->postObject,
                $name
            );
        }

        return $isset;
    }

    /**
     * Get an object attribute.
     *
     * @param string $attribute Attribute name.
     *
     * @return mixed
     */
    public function __get($attribute)
    {
        // Underscore prefix means protected.
        if ('_' === $attribute[0]) {
            return null;
        }

        if (isset($this->postObject->{$attribute})) {
            return $this->postObject->{$attribute};
        }

        return apply_filters(
            'publishpress_authors_layout_post_property_value',
            null,
            $this->postObject,
            $attribute
        );
    }

    public function get_meta($metaKey, $single = true)
    {
        return get_post_meta($this->postObject->ID, $metaKey, (bool)$single);
    }
}
