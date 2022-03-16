<?php
/**
 * File responsible for defining basic general constants used by the plugin.
 *
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

use PPVersionNotices\Module\MenuLink\Module;

defined('ABSPATH') or die('No direct script access allowed.');

if (!defined('PP_AUTHORS_LOADED')) {
    require_once __DIR__ . '/defines.php';

    if (!class_exists(PP_AUTHORS_AUTOLOAD_CLASS_NAME) && !class_exists('MultipleAuthors\\Plugin')) {
        $autoloadPath = PP_AUTHORS_VENDOR_PATH . 'autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }
    }

    if (PUBLISHPRESS_AUTHORS_LOAD_DEPRECATED_LEGACY_CODE) {
        require_once PP_AUTHORS_BASE_PATH . 'deprecated.php';
    }

    require_once PP_AUTHORS_BASE_PATH . 'src/functions/template-tags.php';
    require_once PP_AUTHORS_BASE_PATH . 'src/functions/amp.php';

    if (is_admin() && !defined('PUBLISHPRESS_AUTHORS_SKIP_VERSION_NOTICES')) {
        if (!defined('PP_VERSION_NOTICES_LOADED')) {
            $includesPath = PP_AUTHORS_VENDOR_PATH . 'publishpress/wordpress-version-notices/includes.php';

            if (file_exists($includesPath)) {
                require_once $includesPath;
            }
        }

        add_action(
            'plugins_loaded',
            function () {
                if (current_user_can('install_plugins')) {
                    add_filter(
                        \PPVersionNotices\Module\TopNotice\Module::SETTINGS_FILTER,
                        function ($settings) {
                            $settings['publishpress-authors'] = [
                                'message' => 'You\'re using PublishPress Authors Free. The Pro version has more features and support. %sUpgrade to Pro%s',
                                'link'    => 'https://publishpress.com/links/authors-banner',
                                'screens' => [
                                    ['base' => 'edit-tags', 'id' => 'edit-author', 'taxonomy' => 'author'],
                                    ['base' => 'term', 'id' => 'edit-author', 'taxonomy' => 'author'],
                                    [
                                        'base' => 'authors_page_ppma-modules-settings',
                                        'id'   => 'authors_page_ppma-modules-settings'
                                    ],
                                ]
                            ];

                            return $settings;
                        }
                    );

                    add_filter(
                        Module::SETTINGS_FILTER,
                        function ($settings) {
                            $settings['publishpress-authors'] = [
                                'parent' => 'ppma-authors',
                                'label'  => 'Upgrade to Pro',
                                'link'   => 'https://publishpress.com/links/authors-menu',
                            ];

                            return $settings;
                        }
                    );
                }
            }
        );
    }

    require_once PP_AUTHORS_MODULES_PATH . 'multiple-authors/multiple-authors.php';
}
