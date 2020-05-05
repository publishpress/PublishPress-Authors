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
    define('PP_AUTHORS_VERSION', '3.3.0-beta.3');
    define('PP_AUTHORS_FILE', 'publishpress-authors/publishpress-authors.php');
    define('PP_AUTHORS_BASE_PATH', plugin_dir_path(realpath(__DIR__ . '/../publishpress-authors.php')));
    define('PP_AUTHORS_SRC_PATH', PP_AUTHORS_BASE_PATH . 'src/');
    define('PP_AUTHORS_MODULES_PATH', PP_AUTHORS_SRC_PATH . 'modules/');
    define('PP_AUTHORS_TWIG_PATH', PP_AUTHORS_SRC_PATH . 'twig/');
    define('PP_AUTHORS_VENDOR_PATH', PP_AUTHORS_BASE_PATH . 'vendor/');
    define('PP_AUTHORS_URL', plugins_url('/', PP_AUTHORS_BASE_PATH . 'publishpress-authors.php'));
    define('PP_AUTHORS_ASSETS_URL', plugins_url('/src/assets/', PP_AUTHORS_SRC_PATH));
    define('PP_AUTHORS_AUTOLOAD_CLASS_NAME', 'ComposerAutoloaderInitab3c563cda53c2aa64d02f08e2541717');

    define('PP_AUTHORS_LOADED', 1);

    if (!class_exists(PP_AUTHORS_AUTOLOAD_CLASS_NAME)) {
        $autoloadPath = PP_AUTHORS_VENDOR_PATH . 'autoload.php';
        if (file_exists($autoloadPath)) {
            require_once $autoloadPath;
        }
    }

    require_once PP_AUTHORS_SRC_PATH . '/deprecated.php';
    require_once PP_AUTHORS_SRC_PATH . '/functions/template-tags.php';
    require_once PP_AUTHORS_SRC_PATH . '/functions/amp.php';

    if (is_admin() && !defined('PUBLISHPRESS_AUTHORS_SKIP_VERSION_NOTICES')) {
        if (!defined('PP_VERSION_NOTICES_LOADED')) {
            $includesPath = PP_AUTHORS_VENDOR_PATH . 'publishpress/wordpress-version-notices/includes.php';

            if (file_exists($includesPath)) {
                require_once $includesPath;
            }
        }

        add_filter(
            \PPVersionNotices\Module\TopNotice\Module::SETTINGS_FILTER,
            function ($settings) {
                $settings['publishpress-authors'] = [
                    'message' => 'You\'re using PublishPress Authors Free. The Pro version has more features and support. %sUpgrade to Pro%s',
                    'link'    => 'https://publishpress.com/links/authors-banner',
                    'screens' => [
                        ['base' => 'edit-tags', 'id' => 'edit-author', 'taxonomy' => 'author'],
                        ['base' => 'term', 'id' => 'edit-author', 'taxonomy' => 'author'],
                        ['base' => 'authors_page_ppma-modules-settings', 'id' => 'authors_page_ppma-modules-settings'],
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

    require_once PP_AUTHORS_MODULES_PATH . 'multiple-authors/multiple-authors.php';
}
