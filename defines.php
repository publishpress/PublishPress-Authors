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

if ( ! defined('ABSPATH')) {
    die('No direct script access allowed.');
}

if ( ! defined('PP_AUTHORS_DEFINED')) {
    define('PP_AUTHORS_VERSION', '3.2.0-alpha.1');
    define('PP_AUTHORS_ITEM_ID', '7203');
    define('PP_AUTHORS_SITE_URL', 'https://publishpress.com');
    define('PP_AUTHORS_PLUGIN_AUTHOR', 'PublishPress');
    define('PP_AUTHORS_FILE', 'publishpress-authors/publishpress-authors.php');
    define('PP_AUTHORS_BASE_PATH', plugin_dir_path(__FILE__));
    define('PP_AUTHORS_MODULES_PATH', PP_AUTHORS_BASE_PATH . 'modules');
    define('PP_AUTHORS_ASSETS_URL', plugins_url('publishpress-authors/assets'));
    define('PP_AUTHORS_URL', plugins_url('/', __FILE__));
    define('PP_AUTHORS_BASENAME', plugin_basename(PP_AUTHORS_BASE_PATH));

    define('PP_AUTHORS_DEFINED', 1);
}
