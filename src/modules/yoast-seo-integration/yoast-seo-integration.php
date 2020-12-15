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
use PPAuthors\YoastSEO\SchemaFacade;

if (!class_exists('MA_Yoast_Seo_Integration')) {
    /**
     * class MA_Yoast_Seo_Integration
     */
    class MA_Yoast_Seo_Integration extends Module
    {
        public $module_name = 'yoast_seo_integration';

        /**
         * Instance for the module
         *
         * @var stdClass
         */
        public $module;

        /**
         * Construct the MA_Yoast_Seo_Integration class
         */
        public function __construct()
        {
            $this->module_url = $this->get_module_url(__FILE__);

            // Register the module with PublishPress
            $args = [
                'title'             => __('Yoast SEO Integration', 'publishpress-authors'),
                'short_description' => __('Add compatibility with the Yoast SEO plugin', 'publishpress-authors'),
                'module_url'        => $this->module_url,
                'icon_class'        => 'dashicons dashicons-feedback',
                'slug'              => 'yoast-seo-integration',
                'default_options'   => [
                    'enabled' => 'on',
                ],
                'options_page'      => false,
                'autoload'          => true,
            ];

            // Apply a filter to the default options
            $args['default_options'] = apply_filters(
                'pp_yoast_seo_integration_default_options',
                $args['default_options']
            );

            $legacyPlugin = Factory::getLegacyPlugin();

            $this->module = $legacyPlugin->register_module($this->module_name, $args);

            parent::__construct();
        }

        private function hasNotCompatibleYoastSeoWarningLogTransient()
        {
            return false !== get_transient('publishpress_authors_not_compatible_yoast_warning');
        }

        private function addNotCompatibleYoastSeoWarningLogTransient()
        {
            set_transient('publishpress_authors_not_compatible_yoast_warning', true, 24 * 60 * 60 * 2);
        }

        /**
         * Initialize the module. Conditionally loads if the module is enabled
         */
        public function init()
        {
            try {
                $schemaFacade = new SchemaFacade();
                $schemaFacade->addSupportForMultipleAuthors();

                add_filter('wpseo_replacements', [$this, 'overrideSEOReplacementsForAuthorsPage'], 10, 2);
            } catch (Exception $e) {
                error_log(
                    sprintf('[PublishPress Authors] Method [%s] caught the exception %s', __METHOD__, $e->getMessage())
                );
            }
        }

        public function overrideSEOReplacementsForAuthorsPage($replacements, $args)
        {
            try {
                foreach ($replacements as $key => &$value) {
                    if ($key === '%%name%%') {
                        $authors = get_multiple_authors(0, true, true);

                        if (is_array($authors) && !empty($authors)) {
                            $author = $authors[0];

                            if (isset($author->display_name)) {
                                $value = $author->display_name;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                error_log(
                    sprintf('[PublishPress Authors] Method %s caught the exception: %s', __METHOD__, $e->getMessage())
                );
            }

            return $replacements;
        }
    }
}
