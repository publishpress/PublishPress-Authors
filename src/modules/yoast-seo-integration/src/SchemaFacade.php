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

namespace PPAuthors\YoastSEO;

use MultipleAuthors\Classes\Utils;
use PPAuthors\YoastSEO\YoastAuthor;
use Yoast\WP\SEO\Context\Meta_Tags_Context;
use MultipleAuthors\Classes\Objects\Author as PPAuthor;
use Yoast\WP\SEO\Generators\Schema\Abstract_Schema_Piece;
use WP_User;

use function wp_hash;

class SchemaFacade
{
    public function addSupportForMultipleAuthors()
    {
        add_filter('wpseo_schema_graph', [$this, 'filter_graph' ], 11, 2);
        add_filter('wpseo_schema_graph', [$this, 'filter_author_term_graph' ], 11, 2);
        add_filter('wpseo_schema_author', [$this, 'filter_author_graph' ], 11, 4);
        add_filter('wpseo_meta_author', [$this, 'filter_author_meta' ], 11, 2);
        add_filter('wpseo_opengraph_title', [$this, 'handleAuthorWpseoTitle']);
        add_filter('wpseo_title', [$this, 'handleAuthorWpseoTitle']);
    }

    /**
     * Filters the graph output to add authors.
     *
     * @param array                   $data                   The schema graph.
     * @param Meta_Tags_Context       $context                The context object.
     * @param Abstract_Schema_Piece   $graph_piece_generator  The graph piece generator.
     * @param Abstract_Schema_Piece[] $graph_piece_generators The graph piece generators.
     *
     * @return array The (potentially altered) schema graph.
     */
    public function filter_author_graph($data, $context, $graph_piece_generator, $graph_piece_generators)
    {
        if (! isset($data['image']['url'])) {
            return $data;
        }

        if (isset($data['image']['@id'])) {
            $data['image']['@id'] .= md5($data['image']['url']);
        }

        if (isset($data['logo']['@id'])) {
            $data['logo']['@id'] .= md5($data['image']['url']);
        }

        return $data;
    }

    /**
     * Filters author term graph output.
     *
     * @param array             $data    The schema graph.
     * @param Meta_Tags_Context $context Context object.
     *
     * @return array The (potentially altered) schema graph.
     */
    public function filter_author_term_graph($data, $context)
    {
        if (! is_tax('author')) {
            return $data;
        }

        $author    = PPAuthor::get_by_term_id($context->indexable->object_id);

        $author_generator          = new YoastAuthor();
        $author_generator->context = $context;
        $author_generator->helpers = YoastSEO()->helpers;

        if ($author->ID > 0) {
            $author_data = $author_generator->generate_from_user_id($author->ID);
        } else {
            $author_data = $author_generator->generate_from_guest_author($author);
        }

        if (!is_array($author_data) || !isset($author_data['url'])) {
            return $data;
        }

        if (! empty($author_data)) {
            if (isset($author_data['image']['caption'])) {
                $author_data['image']['caption']   = $author->display_name;
            }
            if (isset($author_data['name'])) {
                $author_data['name']   = $author->display_name;
            }
            $author_data['mainEntityOfPage'] = ['@id' => $author_data['url']];

            $data[] = $author_data;
        }

        if (! empty($author_data)) {
            foreach ($data as $key => $piece) {
                if ($piece['@type'] === 'CollectionPage') {
                    $data[$key]['@type'] = 'ProfilePage';
                    $data[$key]['potentialAction'][] = [
                        '@type' => 'ReadAction',
                        'target' => [$author_data['url']]
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * Filters the graph output to add authors.
     *
     * @param array             $data    The schema graph.
     * @param Meta_Tags_Context $context Context object.
     *
     * @return array The (potentially altered) schema graph.
     */
    public function filter_graph($data, $context)
    {
        if (!is_singular(Utils::get_enabled_post_types()) || !$context->post) {
            return $data;
        }

        if (!function_exists('publishpress_authors_get_post_authors')) {
            require_once PP_AUTHORS_BASE_PATH . 'functions/template-tags.php';
        }
        
        $author_objects = get_post_authors($context->post->ID, false, false);

        $ids     = [];
        $authors = [];

        // Add the authors to the schema.
        foreach ($author_objects as $author) {
            if (is_object($author) && isset($author->ID)) {
                $author_generator          = new YoastAuthor();
                $author_generator->context = $context;
                $author_generator->helpers = YoastSEO()->helpers;

                if ($author->ID > 0) {
                    $author_data = $author_generator->generate_from_user_id($author->ID);
                } else {
                    $author_data = $author_generator->generate_from_guest_author($author);
                }

                if (! empty($author_data)) {
                    if (isset($author_data['image']['caption'])) {
                        $author_data['image']['caption']   = $author->display_name;
                    }
                    if (isset($author_data['name'])) {
                        $author_data['name']   = $author->display_name;
                    }

                    $ids[]     = [ '@id' => $author_data['@id'] ];
                    $authors[] = $author_data;
                }
            }
        }

        if (count($author_objects) === 1) {
            $authors = $ids[0];
        }

        foreach ($data as $key => $piece) {
            if (isset($piece['author'])) {
                $data[$key]['author'] = $authors;
            }
            if (count($author_objects) === 1 && $piece['@type'] === 'Person') {
                $data[$key] = $author_data;
            }
        }

        return $data;
    }

    /**
     * Filters the author meta tag
     *
     * @param string                 $author_name  The article author's display name. Return empty to disable the tag.
     * @param Indexable_Presentation $presentation The presentation of an indexable.
     * @return string
     */
    public function filter_author_meta($author_name, $presentation)
    {

        if (!function_exists('publishpress_authors_get_post_authors')) {
            require_once PP_AUTHORS_BASE_PATH . 'functions/template-tags.php';
        }
        
        $author_objects = get_post_authors($presentation->context->post->id, false, false);

        // Fallback in case of error.
        if (empty($author_objects)) {
            return $author_name;
        }

        $output = '';
        foreach ($author_objects as $i => $author) {
            $output .= $author->display_name;
            if ($i <= (count($author_objects) - 2)) {
                $output .= ', ';
            }
        }
        return $output;
    }

    /**
     * Replace author name for yoast SEO
     *
     * @param string $title current page title
     * 
     * @return string
     */
    public function handleAuthorWpseoTitle($title)
    {
        if (is_author()) {
            $titleAuthorName = get_the_author();
            $realAuthorData  = get_queried_object();
            if (is_object($realAuthorData) && !is_wp_error($realAuthorData) && isset($realAuthorData->display_name)) {
                $title = str_replace($titleAuthorName, $realAuthorData->display_name, $title);
            }
        }

        return $title;
    }
}
