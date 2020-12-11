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

use MultipleAuthors\Classes\Legacy\Module;
use MultipleAuthors\Classes\Legacy\Util;
use MultipleAuthors\Factory;

if (!class_exists('MA_Genesis_Integration')) {
    /**
     * class MA_Genesis_Integration
     */
    class MA_Genesis_Integration extends Module
    {
        public $module_name = 'genesis_integration';

        /**
         * Instance for the module
         *
         * @var stdClass
         */
        public $module;

        /**
         * Construct the MA_Genesis_Integration class
         */
        public function __construct()
        {
            $this->module_url = $this->get_module_url(__FILE__);

            // Register the module with PublishPress
            $args = [
                'title'             => __('Genesis Integration', 'publishpress-authors'),
                'short_description' => __('Add compatibility with the Genesis framework', 'publishpress-authors'),
                'module_url'        => $this->module_url,
                'icon_class'        => 'dashicons dashicons-feedback',
                'slug'              => 'genesis-integration',
                'default_options'   => [
                    'enabled' => 'on',
                ],
                'options_page'      => false,
                'autoload'          => true,
            ];

            // Apply a filter to the default options
            $args['default_options'] = apply_filters(
                'pp_genesis_integration_default_options',
                $args['default_options']
            );

            $legacyPlugin = Factory::getLegacyPlugin();

            $this->module = $legacyPlugin->register_module($this->module_name, $args);

            parent::__construct();
        }

        /**
         * Initialize the module. Conditionally loads if the module is enabled
         */
        public function init()
        {
            add_filter(
                'genesis_post_author_posts_link_shortcode',
                [$this, 'filter_genesis_post_author_posts_link_shortcode'],
                10,
                2
            );

            // Fix compatibility with the Genesis framework in the Authors page.
            add_filter(
                'document_title_parts',
                function ($parts) {
                    if (isset($parts['title']) && function_exists('get_multiple_authors') && Util::isAuthor()) {
                        $authors = get_multiple_authors(0, true, true);
                        if (!empty($authors)) {
                            $author         = $authors[0];
                            $parts['title'] = $author->display_name;
                        }
                    }

                    return $parts;
                },
                20
            );
        }

        /**
         * @param $output
         * @param $attr
         *
         * @return string
         */
        public function filter_genesis_post_author_posts_link_shortcode($output, $attr)
        {
            $authors = get_multiple_authors();

            $output = '';
            foreach ($authors as $author) {
                if (!empty($output)) {
                    $output .= ', ';
                }
                $output .= '<span class="entry-author" itemprop="author" itemscope itemtype="https://schema.org/Person">';
                $output .= $attr['before'];
                $output .= '<a href="' . $author->link . '" class="entry-author-link" rel="author" itemprop="url">';
                $output .= '<span class="entry-author-name" itemprop="name">' . $author->display_name;
                $output .= '</span></a>';
                $output .= $attr['after'];
                $output .= '</span>';
            }

            return $output;
        }
    }
}
