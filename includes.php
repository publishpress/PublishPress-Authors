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
    define('PP_AUTHORS_VERSION', '3.2.5-beta.5');
    define('PP_AUTHORS_FILE', 'publishpress-authors/publishpress-authors.php');
    define('PP_AUTHORS_BASE_PATH', plugin_dir_path(__FILE__));
    define('PP_AUTHORS_MODULES_PATH', PP_AUTHORS_BASE_PATH . 'modules');
    define('PP_AUTHORS_ASSETS_URL', plugins_url('/assets', __DIR__ . '/publishpress-authors.php'));
    define('PP_AUTHORS_URL', plugins_url('/', __FILE__));

    define('PP_AUTHORS_LOADED', 1);

    $autoloadPath = PP_AUTHORS_BASE_PATH . 'vendor/autoload.php';
    if (file_exists($autoloadPath)) {
        require_once $autoloadPath;
    }

    require_once __DIR__ . '/deprecated.php';
    require_once __DIR__ . '/template-tags.php';
    require_once __DIR__ . '/integrations/amp.php';

    if (is_admin() && !defined('PUBLISHPRESS_AUTHORS_SKIP_VERSION_NOTICES')) {
        $includesPath = __DIR__ . '/vendor/publishpress/wordpress-version-notices/includes.php';

        if (file_exists($includesPath)) {
            require_once $includesPath;
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

    require_once __DIR__ . '/modules/multiple-authors/multiple-authors.php';
}
