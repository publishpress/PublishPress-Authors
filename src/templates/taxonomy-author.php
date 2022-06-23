<?php
/**
 * The template for author taxonomy. This file is basically the template engine.
 * 
 * To customize layout template, check the comment on each layout file located at:
 * src/templates/parts
 *
 * @package PublishPress
 */

// Do not allow directly accessing this file.
if (!defined('ABSPATH')) {
    exit('Direct script access denied.');
}

use MultipleAuthors\Classes\Utils;
use MultipleAuthors\Factory;

//load header
if (Utils::authors_locate_template(['header.php'])) {
    get_header(); 
} elseif (Utils::ppma_is_block_theme()) {
    Utils::ppma_format_block_theme_header();
}

$legacyPlugin           = Factory::getLegacyPlugin();
$template_layout        = isset($legacyPlugin->modules->multiple_authors->options->author_pages_layout) ? $legacyPlugin->modules->multiple_authors->options->author_pages_layout : 'list';

//locate layout template
$layout_template = locate_template(['publishpress-authors/templates/author-pages-'.$template_layout.'.php']);
if (!$layout_template ) {
    $layout_template = PP_AUTHORS_BASE_PATH . 'src/templates/parts/author-pages-'.$template_layout.'.php';
}

//load layout template
load_template($layout_template, true);

 //load footer
if (Utils::authors_locate_template(['footer.php'])) {
    get_footer(); 
} elseif (Utils::ppma_is_block_theme()) {
    Utils::ppma_format_block_theme_footer();
}