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
use MultipleAuthors\Classes\Utils;
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
            // Color scheme. This is here, before the Pro loaded check because the Pro uses this style too.
            wp_add_inline_style(
                'multiple-authors-widget-css',
                ':root { --ppa-color-scheme: ' . $args['color_scheme'] . '; --ppa-color-scheme-active: ' . $this->luminanceColor($args['color_scheme'])  . '; }'
            );

            if (defined('PP_AUTHORS_PRO_LOADED')) {
                return $html;
            }

            if (!isset($args['layout'])) {
                $args['layout'] = Utils::getDefaultLayout();
            }

            // Check if the layout exists
            $twigFile = 'author_layout/' . $args['layout'] . '.twig';
            
            $theme_layout = locate_template(['publishpress-authors/twig/' . $args['layout'] . '.twig']);

            if ($theme_layout) {
                $twigFile       = $args['layout'] . '.twig';
            } else {
                if (!file_exists(PP_AUTHORS_TWIG_PATH . $twigFile)) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                        sprintf(
                            '[PublishPress Authors] Twig file not found for the layout: %s. Falling back to "%s"',
                            $args['layout'],
                            Utils::getDefaultLayout()
                        )
                        );
                    }

                    $args['layout'] = Utils::getDefaultLayout();
                    $twigFile       = 'author_layout/' . $args['layout'] . '.twig';
                }

                if (!file_exists(PP_AUTHORS_TWIG_PATH . $twigFile)) {
                    if (defined('WP_DEBUG') && WP_DEBUG) {
                        error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                        sprintf(
                            '[PublishPress Authors] Twig file not found for the layout: %s.',
                            $args['layout']
                        )
                        );
                    }
                }
            }

            $args['strings'] = [
                'view_all' => __('View all posts', 'publishpress-authors'),
            ];

            $container = Factory::get_container();

            if ($theme_layout) {
                $theme_path = substr($theme_layout, 0, strrpos($theme_layout, '/'));
                $container['twig_loader'] = new Twig_Loader_Filesystem($theme_path);
            }
            
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
                'boxed'          => __('Boxed', 'publishpress-authors'),
                'centered'       => __('Centered', 'publishpress-authors'),
                'inline'         => __('Inline', 'publishpress-authors'),
                'inline_avatar'  => __('Inline with avatar', 'publishpress-authors'),
                'simple_list'    => __('Simple list', 'publishpress-authors'),
                'authors_index'  => __('Authors index', 'publishpress-authors'),
                'authors_recent' => __('Authors recent', 'publishpress-authors'),
            ];

            return $layouts;
        }

        /**
         * Lightens/darkens a given colour (hex format), returning the altered colour in hex format
         * @credits: https://gist.github.com/stephenharris/5532899
         *
         * @param    string  $hex       Colour as hexadecimal (with or without hash)
         * @param    float   $percent   Decimal (0.2 = lighten by 20%(), -0.4 = darken by 40%)
         *
         * @return   string  Lightened/Darkend colour as hexadecimal (with hash)
         */
        public function luminanceColor($color, $percent = -0.2)
        {
            $color      = preg_replace( '/[^0-9a-f]/i', '', $color );
            $new_color = '#';

            if (strlen($color) < 6) {
            	$color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
            }

            // convert to decimal and change luminosity
            for ($i = 0; $i < 3; $i++) {
            	$dec        = hexdec(substr($color, $i*2, 2));
            	$dec        = min(max(0, $dec + $dec * $percent), 255);
            	$new_color .= str_pad(dechex($dec), 2, 0, STR_PAD_LEFT);
            }

            return $new_color;
        }
    }
}
