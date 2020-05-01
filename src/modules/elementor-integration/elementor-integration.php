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
use PublishPressAuthors\ElementorIntegration\Modules\Posts\Skins\SkinCardsMultipleAuthors;
use PublishPressAuthors\ElementorIntegration\Modules\Posts\Skins\SkinClassicMultipleAuthors;
use PublishPressAuthors\ElementorIntegration\Modules\Posts\Skins\SkinFullContentMultipleAuthors;

if (!class_exists('MA_Elementor_Integration')) {
    /**
     * class MA_Elementor_Integration
     */
    class MA_Elementor_Integration extends Module
    {
        public $module_name = 'elementor_integration';

        /**
         * Instance for the module
         *
         * @var stdClass
         */
        public $module;

        /**
         * Construct the MA_Elementor_Integration class
         */
        public function __construct()
        {
            $this->module_url = $this->get_module_url(__FILE__);

            // Register the module with PublishPress
            $args = [
                'title'             => __('Elementor Integration', 'publishpress-authors'),
                'short_description' => __(
                    'Add compatibility with the Elementor and Elementor Pro page builder',
                    'publishpress-authors'
                ),
                'module_url'        => $this->module_url,
                'icon_class'        => 'dashicons dashicons-feedback',
                'slug'              => 'elementor-integration',
                'default_options'   => [
                    'enabled' => 'on',
                ],
                'options_page'      => false,
                'autoload'          => true,
            ];

            // Apply a filter to the default options
            $args['default_options'] = apply_filters(
                'pp_elementor_integration_default_options',
                $args['default_options']
            );

            $this->module = Factory::getLegacyPlugin()->register_module($this->module_name, $args);

            parent::__construct();
        }

        private function isElementorInstalled()
        {
            // For now we only integrate with the Pro version because the Free one doesn't have the posts modules.

            if (!defined('ELEMENTOR_PRO_VERSION')) {
                return false;
            }

            $minRequiredVersion = '2.9.3';

            if (version_compare(ELEMENTOR_PRO_VERSION, $minRequiredVersion, '<')) {
                error_log(
                    sprintf(
                        '[PublishPress Authors] Elementor module is disabled because it requires Elementor Pro %s and we found %s',
                        $minRequiredVersion,
                        ELEMENTOR_PRO_VERSION
                    )
                );

                return false;
            }

            $requiredClasses = [
                '\\ElementorPro\\Modules\\Posts\\Skins\\Skin_Cards',
                '\\ElementorPro\\Modules\\Posts\\Skins\\Skin_Classic',
                '\\ElementorPro\\Modules\\Posts\\Skins\\Skin_Full_Content',
            ];

            foreach ($requiredClasses as $className) {
                if (!class_exists($className)) {
                    error_log(
                        sprintf(
                            '[PublishPress Authors] Elementor module did not find the class %s',
                            $className
                        )
                    );
                    return false;
                }
            }

            return true;
        }

        /**
         * Initialize the module. Conditionally loads if the module is enabled
         */
        public function init()
        {
            if (!$this->isElementorInstalled()) {
                return;
            }

            add_action(
                'elementor/widget/posts/skins_init',
                [$this, 'add_posts_skins'],
                10,
                2
            );
        }

        /**
         * @param $widget
         */
        public function add_posts_skins($widget)
        {
            $classes = [
                '\\PublishPressAuthors\\ElementorIntegration\\Modules\\Posts\\Skins\\SkinCardsMultipleAuthors'   =>
                    __DIR__ . '/Modules/Posts/Skins/SkinCardsMultipleAuthors.php',
                '\\PublishPressAuthors\\ElementorIntegration\\Modules\\Posts\\Skins\\SkinClassicMultipleAuthors' =>
                    __DIR__ . '/Modules/Posts/Skins/SkinClassicMultipleAuthors.php',
                '\\PublishPressAuthors\\ElementorIntegration\\Modules\\Posts\\Skins\\SkinFullContentMultipleAuthors' =>
                    __DIR__ . '/Modules/Posts/Skins/SkinFullContentMultipleAuthors.php',
            ];

            foreach ($classes as $className => $path) {
                if (!class_exists($className)) {
                    require_once $path;
                }
            }

            $widget->add_skin(new SkinCardsMultipleAuthors($widget));
            $widget->add_skin(new SkinClassicMultipleAuthors($widget));
            $widget->add_skin(new SkinFullContentMultipleAuthors($widget));
        }
    }
}
