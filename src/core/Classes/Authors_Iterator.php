<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

namespace MultipleAuthors\Classes;

class Authors_Iterator
{
    public $position = -1;
    public $original_authordata;
    public $current_author;
    public $authordata_array;
    public $count;

    public function __construct($postID = 0, $archive = false)
    {
        global $post, $authordata;

        if (!$archive) {
            $postID = (int)$postID;
            if (!$postID && $post) {
                $postID = (int)$post->ID;
            }

            if (!$postID) {
                trigger_error(
                    esc_html__(
                        'No post ID provided for Authors_Iterator constructor. Are you not in a loop or is $post not set?',
                        'publishpress-authors'
                    )
                ); // return null;
            }
        } else {
            $postID = 0;
        }

        $this->original_authordata = $this->current_author = $authordata;
        $this->authordata_array    = get_multiple_authors($postID, true, $archive);

        $this->count = count($this->authordata_array);
    }

    public function iterate()
    {
        global $authordata;
        $this->position++;

        //At the end of the loop
        if ($this->position > $this->count - 1) {
            $authordata     = $this->current_author = $this->original_authordata;
            $this->position = -1;

            return false;
        }

        //At the beginning of the loop
        if (0 === $this->position && !empty($authordata)) {
            $this->original_authordata = $authordata;
        }

        $authordata = $this->current_author = $this->authordata_array[$this->position];

        return true;
    }

    public function get_position()
    {
        if ($this->position === -1) {
            return false;
        }

        return $this->position;
    }

    public function is_last()
    {
        return $this->position === $this->count - 1;
    }

    public function is_first()
    {
        return $this->position === 0;
    }

    public function count()
    {
        return $this->count;
    }

    public function get_all()
    {
        return $this->authordata_array;
    }

    public function reset()
    {
        $this->position = -1;
    }
}
