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

use MultipleAuthors\Classes\Authors_Iterator;
use Yoast\WP\SEO\Config\Schema_IDs;

use function wp_hash;

class SchemaFacade
{
    public function addSupportForMultipleAuthors()
    {
        add_filter('wpseo_schema_webpage', [$this, 'handleSchemaArticle'], 10, 2);
        add_filter('wpseo_schema_author', [$this, 'handleSchemaAuthor'], 10, 2);
        add_filter('wpseo_schema_article', [$this, 'handleSchemaArticle'], 10, 2);
        add_filter('wpseo_opengraph_title', [$this, 'handleAuthorWpseoTitle']);
        add_filter('wpseo_title', [$this, 'handleAuthorWpseoTitle']);
    }

    public function handleSchemaAuthor($graphPiece, $context)
    {
        $author = $this->getAuthorFromContext($context);

        if (!is_object($author)) {
            return $graphPiece;
        }

        $graphPiece['@id']              = $this->getAuthorSchemaId($author, $context);
        $graphPiece['name']             = $author->display_name;
        $graphPiece['image']['caption'] = $graphPiece['name'];

        if (method_exists($author, 'get_avatar_url')) {
            $avatarUrl = $author->get_avatar_url(256);

            if (isset($avatarUrl['url'])) {
                $graphPiece['image']['url'] = $avatarUrl['url'];
                $graphPiece['image']['contentUrl'] = $avatarUrl['url'];
            }
        }

        if (isset($author->link)) {
            $graphPiece['url'] = $author->link;
        }

        return $graphPiece;
    }

    public function handleSchemaArticle($graphPiece, $context)
    {
        if (isset($graphPiece['author'])) {
            $author = $this->getAuthorFromContext($context);

            if (!is_object($author)) {
                return $graphPiece;
            }

            $graphPiece['author']['@id'] = $this->getAuthorSchemaId($author, $context);
        }

        return $graphPiece;
    }

    private function getAuthorFromContext($context)
    {
        if (!isset($context->post) || !is_object($context->post) || is_wp_error($context->post)) {
            return null;
        }

        $authorsIterator = new Authors_Iterator($context->post->ID);
        $authorsIterator->iterate();

        return $authorsIterator->current_author;
    }

    private function getAuthorSchemaId($author, $context)
    {
        return $context->site_url . Schema_IDs::PERSON_HASH . wp_hash(
                $author->slug . $author->ID
            );
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
