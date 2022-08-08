<?php
/**
 * Plugin Name: PublishPress Authors
 * Plugin URI:  https://wordpress.org/plugins/publishpress-authors/
 * Description: PublishPress Authors allows you to add multiple authors and guest authors to WordPress posts
 * Author:      PublishPress
 * Author URI:  https://publishpress.com
 * Version: 3.20.1
 * Text Domain: publishpress-authors
 *
 * ------------------------------------------------------------------------------
 * Based on Co-Authors Plus.
 * Authors: Mohammad Jangda, Daniel Bachhuber, Automattic
 * Copyright: 2008-2015 Shared and distributed between  Mohammad Jangda, Daniel Bachhuber, Weston Ruter
 * ------------------------------------------------------------------------------
 *
 * GNU General Public License, Free Software Foundation <http://creativecommons.org/licenses/GPL/2.0/>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link        https://publishpress.com/authors/
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2020 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

use MultipleAuthors\Factory;
use MultipleAuthors\Plugin;

$includeFilebRelativePath = '/publishpress/publishpress-instance-protection/include.php';
if (file_exists(__DIR__ . '/vendor' . $includeFilebRelativePath)) {
    require_once __DIR__ . '/vendor' . $includeFilebRelativePath;
} else if (defined('PP_AUTHORS_VENDOR_PATH') && file_exists(PP_AUTHORS_VENDOR_PATH . $includeFilebRelativePath)) {
    require_once PP_AUTHORS_VENDOR_PATH . $includeFilebRelativePath;
}

if (class_exists('PublishPressInstanceProtection\\Config')) {
    $pluginCheckerConfig = new PublishPressInstanceProtection\Config();
    $pluginCheckerConfig->pluginSlug = 'publishpress-authors';
    $pluginCheckerConfig->pluginName = 'PublishPress Authors';

    $pluginChecker = new PublishPressInstanceProtection\InstanceChecker($pluginCheckerConfig);
}

if (defined('PP_AUTHORS_PRO_LOADED')) {
    add_filter(
        'plugin_row_meta',
        function ($links, $file) {
            if ($file == plugin_basename(__FILE__)) {
                $links[]= '<strong>' . esc_html__('This plugin can be deleted.', 'publishpress-authors') . '</strong>';
            }

            return $links;
        },
        10,
        2
    );
}

if (! defined('PP_AUTHORS_LOADED')) {

    require_once __DIR__ . '/includes.php';

    global $multiple_authors_addon;

    if (defined('WP_CLI') && WP_CLI) {
        WP_CLI::add_command('publishpress-authors', 'MultipleAuthors\\WP_Cli');
    }

    // Init the legacy plugin instance
    $legacyPlugin = Factory::getLegacyPlugin();

    $multiple_authors_addon = new Plugin();

    register_activation_hook(
        PP_AUTHORS_FILE,
        function () {
            require_once PP_AUTHORS_BASE_PATH . 'activation.php';
        }
    );

    include_once __DIR__ . '/src/functions/notify.php';
}
