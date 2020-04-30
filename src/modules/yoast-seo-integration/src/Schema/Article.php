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
use PPAuthors\YoastSEO\SchemaUtils;
use WPSEO_Date_Helper;
use WPSEO_Graph_Piece;
use WPSEO_Schema_Context;
use WPSEO_Schema_IDs;
use WPSEO_Schema_Utils;

/**
 * Returns schema Article data.
 *
 * Based on the class \WPSEO_Schema_Article, from Yoast SEO Premium.
 */
class Article implements WPSEO_Graph_Piece
{

    /**
     * The date helper.
     *
     * @var WPSEO_Date_Helper
     */
    protected $date;

    /**
     * A value object with context variables.
     *
     * @var WPSEO_Schema_Context
     */
    private $context;

    /**
     * WPSEO_Schema_Article constructor.
     *
     * @param WPSEO_Schema_Context $context A value object with context variables.
     */
    public function __construct(WPSEO_Schema_Context $context)
    {
        $this->context = $context;
        $this->date    = new WPSEO_Date_Helper();
    }

    /**
     * Determines whether or not a piece should be added to the graph.
     *
     * @return bool
     */
    public function is_needed()
    {
        if (!is_singular()) {
            return false;
        }

        if ($this->context->site_represents === false) {
            return false;
        }

        return self::is_article_post_type(get_post_type());
    }

    /**
     * Determines whether a given post type should have Article schema.
     *
     * @param string $post_type Post type to check.
     *
     * @return bool True if it has article schema, false if not.
     */
    public static function is_article_post_type($post_type = null)
    {
        if (is_null($post_type)) {
            $post_type = get_post_type();
        }

        /**
         * Filter: 'wpseo_schema_article_post_types' - Allow changing for which post types we output Article schema.
         *
         * @api string[] $post_types The post types for which we output Article.
         */
        $post_types = apply_filters('wpseo_schema_article_post_types', ['post']);

        return in_array($post_type, $post_types, true);
    }

    /**
     * Returns Article data.
     *
     * @return array $data Article data.
     */
    public function generate()
    {
        $post          = get_post($this->context->id);
        $comment_count = get_comment_count($this->context->id);

        // We can only show one author for now - a limitation from the https://schema.org/Article schema.
        $authorsIterator = new Authors_Iterator($post->ID, false);
        $authorsIterator->iterate();
        $author = $authorsIterator->current_author;

        if ($author->ID > 0) {
            $authorIdElement = WPSEO_Schema_Utils::get_user_schema_id($author->ID, $this->context);
        } else {
            $authorIdElement = $this->context->site_url . WPSEO_Schema_IDs::PERSON_HASH . wp_hash(
                    $author->ID
                );
        }

        $data = [
            '@type'            => 'Article',
            '@id'              => $this->context->canonical . WPSEO_Schema_IDs::ARTICLE_HASH,
            'isPartOf'         => ['@id' => $this->context->canonical . WPSEO_Schema_IDs::WEBPAGE_HASH],
            'author'           => ['@id' => $authorIdElement],
            'headline'         => WPSEO_Schema_Utils::get_post_title_with_fallback($this->context->id),
            'datePublished'    => $this->date->format($post->post_date_gmt),
            'dateModified'     => $this->date->format($post->post_modified_gmt),
            'commentCount'     => $comment_count['approved'],
            'mainEntityOfPage' => ['@id' => $this->context->canonical . WPSEO_Schema_IDs::WEBPAGE_HASH],
        ];

        if ($this->context->site_represents_reference) {
            $data['publisher'] = $this->context->site_represents_reference;
        }

        if ($this->context->site_represents_reference) {
            $data['publisher'] = $this->context->site_represents_reference;
        }

        $data = $this->add_image($data);
        $data = $this->add_keywords($data);
        $data = $this->add_sections($data);
        $data = SchemaUtils::addPieceLanguage($data);

        if (post_type_supports($post->post_type, 'comments') && $post->comment_status === 'open') {
            $data = $this->add_potential_action($data);
        }

        return $data;
    }

    /**
     * Adds an image node if the post has a featured image.
     *
     * @param array $data The Article data.
     *
     * @return array $data The Article data.
     */
    private function add_image($data)
    {
        if ($this->context->has_image) {
            $data['image'] = [
                '@id' => $this->context->canonical . WPSEO_Schema_IDs::PRIMARY_IMAGE_HASH,
            ];
        }

        return $data;
    }

    /**
     * Adds tags as keywords, if tags are assigned.
     *
     * @param array $data Article data.
     *
     * @return array $data Article data.
     */
    private function add_keywords($data)
    {
        /**
         * Filter: 'wpseo_schema_article_keywords_taxonomy' - Allow changing the taxonomy used to assign keywords to a post type Article data.
         *
         * @api string $taxonomy The chosen taxonomy.
         */
        $taxonomy = apply_filters('wpseo_schema_article_keywords_taxonomy', 'post_tag');

        return $this->add_terms($data, 'keywords', $taxonomy);
    }

    /**
     * Adds a term or multiple terms, comma separated, to a field.
     *
     * @param array $data Article data.
     * @param string $key The key in data to save the terms in.
     * @param string $taxonomy The taxonomy to retrieve the terms from.
     *
     * @return mixed array $data Article data.
     */
    private function add_terms($data, $key, $taxonomy)
    {
        $terms = get_the_terms($this->context->id, $taxonomy);
        if (is_array($terms)) {
            $keywords = [];
            foreach ($terms as $term) {
                // We are checking against the WordPress internal translation.
                // @codingStandardsIgnoreLine
                if ($term->name !== __('Uncategorized', 'default')) {
                    $keywords[] = $term->name;
                }
            }
            $data[$key] = implode(',', $keywords);
        }

        return $data;
    }

    /**
     * Adds categories as sections, if categories are assigned.
     *
     * @param array $data Article data.
     *
     * @return array $data Article data.
     */
    private function add_sections($data)
    {
        /**
         * Filter: 'wpseo_schema_article_sections_taxonomy' - Allow changing the taxonomy used to assign keywords to a post type Article data.
         *
         * @api string $taxonomy The chosen taxonomy.
         */
        $taxonomy = apply_filters('wpseo_schema_article_sections_taxonomy', 'category');

        return $this->add_terms($data, 'articleSection', $taxonomy);
    }

    /**
     * Adds the potential action JSON LD code to an Article Schema piece.
     *
     * @param array $data The Article data array.
     *
     * @return array $data
     */
    private function add_potential_action($data)
    {
        /**
         * Filter: 'wpseo_schema_article_potential_action_target' - Allows filtering of the schema Article potentialAction target.
         *
         * @api array $targets The URLs for the Article potentialAction target.
         */
        $targets = apply_filters(
            'wpseo_schema_article_potential_action_target',
            [$this->context->canonical . '#respond']
        );

        $data['potentialAction'][] = [
            '@type'  => 'CommentAction',
            'name'   => 'Comment',
            'target' => $targets,
        ];

        return $data;
    }
}
