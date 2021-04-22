<?php

use MultipleAuthors\Factory;

define('TESTS_ROOT_PATH', realpath(__DIR__ . '/../'));
define('PUBLISHPRESS_AUTHORS_BYPASS_INSTALLER', true);

require_once 'publishpress-authors.php';

global $multiple_authors_addon;

$multiple_authors_addon->action_init();
$multiple_authors_addon->action_init_late();
MultipleAuthors\Classes\Content_Model::action_init_late_register_taxonomy_for_object_type();
$legacyPlugin = Factory::get_container()->offsetGet('legacy_plugin');
$legacyPlugin->action_init();
$legacyPlugin->action_init_after();
$legacyPlugin->action_ini_for_admin();
