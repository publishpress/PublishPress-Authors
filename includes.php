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

defined('ABSPATH') or die('No direct script access allowed.');

if ( ! defined('PP_AUTHORS_LOADED')) {
    define('PP_AUTHORS_VERSION', '3.2.2');
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
    require_once __DIR__ . '/modules/multiple-authors/multiple-authors.php';
}
