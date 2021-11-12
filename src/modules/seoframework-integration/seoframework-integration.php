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
use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Factory;
use PublishPressAuthors\ElementorIntegration\Modules\Posts\Skins\PostsSkinCards;
use PublishPressAuthors\ElementorIntegration\Modules\Posts\Skins\PostsSkinClassic;
use PublishPressAuthors\ElementorIntegration\Modules\Posts\Skins\PostsSkinFullContent;
use PublishPressAuthors\ElementorIntegration\Modules\ThemeBuilder\Skins\ArchivePostsSkinCards;
use PublishPressAuthors\ElementorIntegration\Modules\ThemeBuilder\Skins\ArchivePostsSkinClassic;
use PublishPressAuthors\ElementorIntegration\Modules\ThemeBuilder\Skins\ArchivePostsSkinFullContent;

if (!class_exists('MA_Seoframework_Integration')) {
    /**
     * class MA_Seoframework_Integration
     */
    class MA_Seoframework_Integration extends Module
    {
        public $module_name = 'seoframework_integration';

        /**
         * Instance for the module
         *
         * @var stdClass
         */
        public $module;

        /**
         * Construct the MA_Seoframework_Integration class
         */
        public function __construct()
        {
            $this->module_url = $this->get_module_url(__FILE__);

            // Register the module with PublishPress
            $args = [
                'title'             => __('The SEO Framework Integration', 'publishpress-authors'),
                'short_description' => __(
                    'Add compatibility with The SEO Framework plugin.',
                    'publishpress-authors'
                ),
                'module_url'        => $this->module_url,
                'icon_class'        => 'dashicons dashicons-feedback',
                'slug'              => 'seoframework-integration',
                'default_options'   => [
                    'enabled' => 'on',
                ],
                'options_page'      => false,
                'autoload'          => true,
            ];

            // Apply a filter to the default options
            $args['default_options'] = apply_filters(
                'pp_seoframeworkault_options',
                $args['default_options']
            );

            $this->module = Factory::getLegacyPlugin()->register_module($this->module_name, $args);

            parent::__construct();
        }

        /**
         * Initialize the module. Conditionally loads if the module is enabled
         */
        public function init()
        {
            add_filter('the_seo_framework_the_archive_title', [$this, 'setArchiveTitle'], 10, 2);
        }

        public function setArchiveTitle($title, $term)
        {
            $author = Author::get_by_term_id($term->term_id);

            if (is_a($author, Author::class) && $author->is_guest()) {
                $title = $author->display_name;
            }

            return $title;
        }
    }
}
