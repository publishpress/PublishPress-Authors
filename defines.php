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
    define('PP_AUTHORS_VERSION', '3.16.0');
    define('PP_AUTHORS_FILE', 'publishpress-authors/publishpress-authors.php');
    define('PP_AUTHORS_BASE_PATH', plugin_dir_path(__DIR__ . '/publishpress-authors.php'));
    define('PP_AUTHORS_MODULES_PATH', PP_AUTHORS_BASE_PATH . 'src/modules/');
    define('PP_AUTHORS_TWIG_PATH', PP_AUTHORS_BASE_PATH . 'src/twig/');
    define('PP_AUTHORS_VENDOR_PATH', PP_AUTHORS_BASE_PATH . 'vendor/');
    define('PP_AUTHORS_URL', rtrim(plugins_url('/', PP_AUTHORS_BASE_PATH . 'publishpress-authors.php'), '/') . '/');
    define('PP_AUTHORS_ASSETS_URL', PP_AUTHORS_URL . 'src/assets/');
    define('PP_AUTHORS_AUTOLOAD_CLASS_NAME', 'ComposerStaticInit92fc51e620da052063312bd38c6157a4');

    if (!defined('PUBLISHPRESS_AUTHORS_LOAD_DEPRECATED_LEGACY_CODE')) {
        define('PUBLISHPRESS_AUTHORS_LOAD_DEPRECATED_LEGACY_CODE', true);
    }

    if (!defined('PUBLISHPRESS_AUTHORS_LOAD_LEGACY_SHORTCODES')) {
        define('PUBLISHPRESS_AUTHORS_LOAD_LEGACY_SHORTCODES', true);
    }

    if (!defined('PUBLISHPRESS_AUTHORS_LOAD_COAUTHORS_FUNCTIONS')) {
        define('PUBLISHPRESS_AUTHORS_LOAD_COAUTHORS_FUNCTIONS', true);
    }

    if (!defined('PUBLISHPRESS_AUTHORS_LOAD_BYLINES_FUNCTIONS')) {
        define('PUBLISHPRESS_AUTHORS_LOAD_BYLINES_FUNCTIONS', true);
    }

    if (!defined('PUBLISHPRESS_AUTHORS_SYNC_POST_AUTHOR_CHUNK_SIZE')) {
        define('PUBLISHPRESS_AUTHORS_SYNC_POST_AUTHOR_CHUNK_SIZE', 10);
    }

    if (!defined('PUBLISHPRESS_AUTHORS_SYNC_AUTHOR_SLUG_CHUNK_SIZE')) {
        define('PUBLISHPRESS_AUTHORS_SYNC_AUTHOR_SLUG_CHUNK_SIZE', 50);
    }

    if (!defined('PUBLISHPRESS_AUTHORS_LOAD_STYLE_IN_FRONTEND')) {
        define('PUBLISHPRESS_AUTHORS_LOAD_STYLE_IN_FRONTEND', true);
    }
}
