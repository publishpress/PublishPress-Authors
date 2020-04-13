<?php
/**
 * @package PublishPress Authors
 * @author  PublishPress
 *
 * Copyright (C) 2020 PublishPress
 * Copyright (C) 2020 Yoast SEO
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

namespace PPAuthors\YoastSEO\Schema;


use MultipleAuthors\Classes\Authors_Iterator;

/**
 * Returns schema Article data.
 *
 * Based on the class \WPSEO_Schema_Article, from Yoast SEO Premium.
 */
class Author extends Person implements \WPSEO_Graph_Piece
{
    /**
     * A value object with context variables.
     *
     * @var \WPSEO_Schema_Context
     */
    private $context;

    /**
     * The Schema type we use for this class.
     *
     * @var string[]
     */
    protected $type = ['Person'];

    /**
     * WPSEO_Schema_Author constructor.
     *
     * @param \WPSEO_Schema_Context $context A value object with context variables.
     */
    public function __construct(\WPSEO_Schema_Context $context)
    {
        parent::__construct($context);
        $this->context    = $context;
        $this->image_hash = \WPSEO_Schema_IDs::AUTHOR_LOGO_HASH;
    }

    /**
     * Determine whether we should return Person schema.
     *
     * @return bool
     */
    public function is_needed()
    {
        if (is_author()) {
            return true;
        }

        if ($this->is_post_author()) {
            $author = $this->getPostAuthor();

            // If the author is the user the site represents, no need for an extra author block.
            if ((int)$author->user_id === $this->context->site_user_id) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Returns Person Schema data.
     *
     * @return bool|array Person data on success, false on failure.
     */
    public function generate()
    {
        $user_id = $this->determine_user_id();
        if (!$user_id) {
            return false;
        }

        $data = $this->build_person_data($user_id);

        // If this is an author page, the Person object is the main object, so we set it as such here.
        if (is_author()) {
            $data['mainEntityOfPage'] = [
                '@id' => $this->context->canonical . \WPSEO_Schema_IDs::WEBPAGE_HASH,
            ];
        }

        return $data;
    }

    /**
     * Determine whether the current URL is worthy of Article schema.
     *
     * @return bool
     */
    protected function is_post_author()
    {
        if (is_singular() && \WPSEO_Schema_Article::is_article_post_type()) {
            return true;
        }

        return false;
    }

    /**
     * Determines a User ID for the Person data.
     *
     * @return bool|int User ID or false upon return.
     */
    protected function determine_user_id()
    {
        if (is_author()) {
            $author = get_queried_object();

            if (get_class($author) === 'WP_User') {
                $user_id = get_queried_object_id();
            } else {
                $user_id = $author->slug;
            }
        } else {
            $author  = $this->getPostAuthor();
            $user_id = $author->slug;
        }

        /**
         * Filter: 'wpseo_schema_person_user_id' - Allows filtering of user ID used for person output.
         *
         * @api int|bool $user_id The user ID currently determined.
         */
        return apply_filters('wpseo_schema_person_user_id', $user_id);
    }

    /**
     * An author should not have an image from options, this only applies to persons.
     *
     * @param array $data The Person schema.
     * @param string $schema_id The string used in the `@id` for the schema.
     *
     * @return array The Person schema.
     */
    private function set_image_from_options($data, $schema_id)
    {
        return $data;
    }

    /**
     * Gets the Schema type we use for this class.
     *
     * @return string[] The schema type.
     */
    public static function get_type()
    {
        return self::$type;
    }
}
