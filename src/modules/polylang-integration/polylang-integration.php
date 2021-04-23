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
use MultipleAuthors\Factory;


if (!class_exists('MA_Polylang_Integration')) {
    /**
     * class MA_Polylang_Integration
     */
    class MA_Polylang_Integration extends Module
    {
        const SETTINGS_SLUG = 'ppma-settings';

        public $module_name = 'polylang_integration';

        /**
         * Instance for the module
         *
         * @var stdClass
         */
        public $module;


        /**
         * Construct the MA_Polylang_Integration class
         */
        public function __construct()
        {
            $this->module_url = $this->get_module_url(__FILE__);

            // Register the module with PublishPress
            $args = [
                'title'                => __('Polylang Integration', 'publishpress-authors'),
                'short_description'    => __('Polylang Integration', 'publishpress-authors'),
                'extended_description' => __('Polylang Integration', 'publishpress-authors'),
                'module_url'           => $this->module_url,
                'icon_class'           => 'dashicons dashicons-feedback',
                'slug'                 => 'polylang-integration',
                'default_options'      => [
                    'enabled' => 'on'
                ],
                'options_page'         => false,
                'autoload'             => true,
            ];

            // Apply a filter to the default options
            $args['default_options'] = apply_filters(
                'pp_polylang_integration_default_options',
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
            add_filter('pll_get_taxonomies', [$this, 'filterTaxonomies']);
        }

        public function filterTaxonomies($taxonomies)
        {
            $taxonomies['author'] = 'author';

            return $taxonomies;
        }
    }
}
