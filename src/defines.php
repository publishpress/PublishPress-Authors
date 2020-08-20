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

if (!defined('PP_AUTHORS_VERSION')) {
    define('PP_AUTHORS_VERSION', '3.5.1');
    define('PP_AUTHORS_FILE', 'publishpress-authors/publishpress-authors.php');
    define('PP_AUTHORS_BASE_PATH', plugin_dir_path(realpath(__DIR__ . '/../publishpress-authors.php')));
    define('PP_AUTHORS_SRC_PATH', PP_AUTHORS_BASE_PATH . 'src/');
    define('PP_AUTHORS_MODULES_PATH', PP_AUTHORS_SRC_PATH . 'modules/');
    define('PP_AUTHORS_TWIG_PATH', PP_AUTHORS_SRC_PATH . 'twig/');
    define('PP_AUTHORS_VENDOR_PATH', PP_AUTHORS_BASE_PATH . 'vendor/');
    define('PP_AUTHORS_URL', plugins_url('/', PP_AUTHORS_BASE_PATH . 'publishpress-authors.php'));
    define('PP_AUTHORS_ASSETS_URL', plugins_url('/src/assets/', PP_AUTHORS_SRC_PATH));
    define('PP_AUTHORS_AUTOLOAD_CLASS_NAME', 'ComposerStaticInit92fc51e620da052063312bd38c6157a4');
}
