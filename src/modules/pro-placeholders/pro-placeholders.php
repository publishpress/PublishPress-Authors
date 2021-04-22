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


if (!class_exists('MA_Pro_Placeholders')) {
    /**
     * class MA_Pro_Placeholders
     */
    class MA_Pro_Placeholders extends Module
    {
        const SETTINGS_SLUG = 'ppma-settings';

        public $module_name = 'pro_placeholders';

        /**
         * Instance for the module
         *
         * @var stdClass
         */
        public $module;


        /**
         * Construct the MA_Pro_Placeholders class
         */
        public function __construct()
        {
            $this->module_url = $this->get_module_url(__FILE__);

            // Register the module with PublishPress
            $args = [
                'title'                => __('Pro Placeholders', 'publishpress-authors'),
                'short_description'    => __('Pro Placeholders', 'publishpress-authors'),
                'extended_description' => __('Pro Placeholders', 'publishpress-authors'),
                'module_url'           => $this->module_url,
                'icon_class'           => 'dashicons dashicons-feedback',
                'slug'                 => 'pro-placeholders',
                'default_options'      => [
                    'enabled' => 'on'
                ],
                'options_page'         => false,
                'autoload'             => true,
            ];

            // Apply a filter to the default options
            $args['default_options'] = apply_filters('pp_pro_placeholders_default_options', $args['default_options']);

            $legacyPlugin = Factory::getLegacyPlugin();

            $this->module = $legacyPlugin->register_module($this->module_name, $args);

            parent::__construct();
        }

        /**
         * Initialize the module. Conditionally loads if the module is enabled
         */
        public function init()
        {
            if (defined('PP_AUTHORS_PRO_LOADED')) {
                return;
            }

            add_action('multiple_authors_admin_submenu', [$this, 'addProPlaceholdersMenus']);
        }

        public function addProPlaceholdersMenus()
        {
            add_submenu_page(
                MA_Multiple_Authors::MENU_SLUG,
                esc_html__('Fields', 'publishpress-authors'),
                esc_html__('Fields', 'publishpress-authors'),
                apply_filters('pp_multiple_authors_manage_authors_cap', 'ppma_manage_authors'),
                'ppma-pro-placeholders-fields',
                [$this, 'placeholderPageFields'],
                12
            );

            add_submenu_page(
                MA_Multiple_Authors::MENU_SLUG,
                esc_html__('Layouts', 'publishpress-authors'),
                esc_html__('Layouts', 'publishpress-authors'),
                apply_filters('pp_multiple_authors_manage_authors_cap', 'ppma_manage_authors'),
                'ppma-pro-placeholders-layouts',
                [$this, 'placeholderPageLayouts'],
                13
            );
        }

        public function placeholderPageFields()
        {
            include_once __DIR__ . '/views/fields-placeholder.php';
        }

        public function placeholderPageLayouts()
        {
            include_once __DIR__ . '/views/layouts-placeholder.php';
        }
    }
}
