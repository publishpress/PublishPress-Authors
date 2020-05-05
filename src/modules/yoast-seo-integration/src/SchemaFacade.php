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

use WPSEO_Schema_Context;

class SchemaFacade
{
    public function addSupportForMultipleAuthors()
    {
        add_filter('wpseo_schema_graph_pieces', [$this, 'handleSchemaGraphPieces'], 10, 2);
    }

    /**
     * @param array $pieces
     * @param WPSEO_Schema_Context $context An object with context variables.
     *
     * @return array
     */
    public function handleSchemaGraphPieces($pieces, WPSEO_Schema_Context $context)
    {
        $schemaMap = [
            'WPSEO_Schema_Article' => Schema\Article::class,
            'WPSEO_Schema_Author'  => Schema\Author::class,
            'WPSEO_Schema_Person'  => Schema\Person::class,
            'WPSEO_Schema_WebPage' => Schema\Webpage::class,
        ];

        foreach ($pieces as &$piece) {
            $pieceClass = get_class($piece);

            if (array_key_exists($pieceClass, $schemaMap)) {
                // Replace the schema with our own instance adapted for multiple authors
                $piece = new $schemaMap[$pieceClass](
                    $context
                );
            }
        }

        return $pieces;
    }
}