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
use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Factory;


if (!class_exists('MA_REST_API')) {
    /**
     * class MA_REST_API
     */
    class MA_REST_API extends Module
    {
        const SETTINGS_SLUG = 'ppma-settings';

        public $module_name = 'rest_api';

        /**
         * Instance for the module
         *
         * @var stdClass
         */
        public $module;


        /**
         * Construct the MA_REST_API class
         */
        public function __construct()
        {
            $this->module_url = $this->get_module_url(__FILE__);

            // Register the module with PublishPress
            $args = [
                'title'                => __('Rest API', 'publishpress-authors'),
                'short_description'    => __('Rest API support', 'publishpress-authors'),
                'extended_description' => __('Rest API support', 'publishpress-authors'),
                'module_url'           => $this->module_url,
                'icon_class'           => 'dashicons dashicons-feedback',
                'slug'                 => 'rest-api',
                'default_options'      => [
                    'enabled' => 'on'
                ],
                'options_page'         => false,
                'autoload'             => true,
            ];

            // Apply a filter to the default options
            $args['default_options'] = apply_filters('pp_rest_api_default_options', $args['default_options']);

            $legacyPlugin = Factory::getLegacyPlugin();

            $this->module = $legacyPlugin->register_module($this->module_name, $args);

            parent::__construct();
        }

        /**
         * Initialize the module. Conditionally loads if the module is enabled
         */
        public function init()
        {
            add_action('rest_api_init', [$this, 'initRestAPI']);
        }

        public function initRestAPI()
        {
            register_rest_field(
                'post',
                'authors',
                [
                    'get_callback'    => [$this, 'getPostAuthorsCallback'],
                    'schema'          => [
                        'description' => __('Authors.'),
                        'type'        => 'array'
                    ],
                ]
            );
        }

        public function getPostAuthorsCallBack($postData)
        {
            $legacyPlugin = Factory::getLegacyPlugin();

            $selectedPostTypes = array_values(Util::get_post_types_for_module($legacyPlugin->modules->multiple_authors));

            $post = get_post($postData['id']);

            if (!in_array($post->post_type, $selectedPostTypes)) {
                return [];
            }

            $authors = get_post_authors($postData['id']);

            $authorsData = [];

            foreach ($authors as $author) {
                $isGuest = 0;
                if (is_a($author, Author::class)) {
                    $isGuest = $author->is_guest() ? 1 : 0;
                }

                $authorsData[] = [
                    'term_id'      => (int)$author->term_id,
                    'user_id'      => (int)$author->user_id,
                    'is_guest'     => $isGuest,
                    'slug'         => $author->slug,
                    'display_name' => $author->display_name,
                ];
            }

            return $authorsData = apply_filters('ppma_rest_api_authors_data', $authorsData);
        }
    }
}
