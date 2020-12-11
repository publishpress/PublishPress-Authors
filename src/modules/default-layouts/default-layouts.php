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

if (!class_exists('MA_Default_Layouts')) {
    /**
     * class MA_Default_Layouts
     */
    class MA_Default_Layouts extends Module
    {
        const SETTINGS_SLUG = 'ppma-settings';

        public $module_name = 'default_layouts';

        /**
         * Instance for the module
         *
         * @var stdClass
         */
        public $module;

        /**
         * Construct the MA_Default_Layouts class
         */
        public function __construct()
        {
            $this->module_url = $this->get_module_url(__FILE__);

            // Register the module with PublishPress
            $args = [
                'title'           => __('Default Layouts', 'publishpress-authors'),
                'module_url'      => $this->module_url,
                'icon_class'      => 'dashicons dashicons-feedback',
                'slug'            => 'default-layouts',
                'default_options' => [
                    'enabled' => 'on',
                ],
                'options_page'    => false,
                'autoload'        => true,
            ];

            $legacyPlugin = Factory::getLegacyPlugin();

            $this->module = $legacyPlugin->register_module($this->module_name, $args);

            parent::__construct();
        }

        /**
         * Initialize the module. Conditionally loads if the module is enabled
         */
        public function init()
        {
            add_filter('pp_multiple_authors_author_box_html', [$this, 'renderBoxHTML'], 10, 2);
            add_filter('pp_multiple_authors_authors_list_box_html', [$this, 'renderBoxHTML'], 10, 2);
            add_filter('pp_multiple_authors_author_layouts', [$this, 'getListOfLayouts'], 10, 2);
        }

        /**
         * @param string $html
         * @param array $args
         *
         * @return string
         */
        public function renderBoxHTML($html, $args)
        {
            if (!isset($args['layout'])) {
                $args['layout'] = apply_filters('pp_multiple_authors_default_layout', 'inline');
            }

            // Check if the layout exists
            $twigFile = 'author_layout/' . $args['layout'] . '.twig';
            if (!file_exists(PP_AUTHORS_TWIG_PATH . $twigFile)) {
                error_log(
                    sprintf(
                        '[PublishPress Authors] Twig file not found for the layout: %s. Falling back to "simple_list"',
                        $args['layout']
                    )
                );

                $args['layout'] = 'simple_list';
                $twigFile       = 'author_layout/' . $args['layout'] . '.twig';
            }

            if (!file_exists(PP_AUTHORS_TWIG_PATH . $twigFile)) {
                error_log(
                    sprintf(
                        '[PublishPress Authors] Twig file not found for the layout: %s.',
                        $args['layout']
                    )
                );
            }

            $container = Factory::get_container();
            $twig      = $container['twig'];


            $html = $twig->render($twigFile, $args);

            return $html;
        }

        /**
         * @param array $layouts
         *
         * @return array
         */
        public function getListOfLayouts($layouts)
        {
            $layouts = [
                'boxed'         => __('Boxed', 'publishpress-authors'),
                'centered'      => __('Centered', 'publishpress-authors'),
                'inline'        => __('Inline', 'publishpress-authors'),
                'inline_avatar' => __('Inline with avatar', 'publishpress-authors'),
                'simple_list'   => __('Simple list', 'publishpress-authors'),
            ];

            return $layouts;
        }
    }
}
