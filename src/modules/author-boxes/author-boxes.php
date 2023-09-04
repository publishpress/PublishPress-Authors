<?php
/**
 * @package PublishPress Authors
 * @author  PublishPress
 *
 * Copyright (C) 2018 PublishPress
 *
 * This file is part of PublishPress Authors
 *
 * PublishPress Authors is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation, either version 3 of the License,
 * or (at your option) any later version.
 *
 * PublishPress is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PublishPress.  If not, see <http://www.gnu.org/licenses/>.
 */

use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Legacy\Module;
use MultipleAuthorBoxes\AuthorBoxesDefault;
use MultipleAuthors\Classes\Author_Editor;
use MultipleAuthorBoxes\AuthorBoxesStyles;
use MultipleAuthors\Classes\Utils;
use MultipleAuthors\Factory;

/**
 * class MA_Author_Boxes
 */
class MA_Author_Boxes extends Module
{
    /**
     * Post Type.
     */
    const POST_TYPE_BOXES = 'ppma_boxes';

    /**
     * Default tab
     */
    const AUTHOR_BOXES_EDITOR_DEFAULT_TAB = 'title';

    /**
     * Excluded profile fields
     */
    const AUTHOR_BOXES_EXCLUDED_FIELDS = ['user_id', 'avatar', 'description'];

    /**
     * Meta data prefix.
     */
    const META_PREFIX = 'ppma_boxes_';

    public $module_name = 'author_boxes';

    /**
     * Instance of the module
     *
     * @var stdClass
     */
    public $module;
    public $module_url;

    /**
     * @var array
     */
    protected $customFields = null;

    /**
     * Construct the MA_Multiple_Authors class
     */
    public function __construct()
    {
        $this->module_url = $this->get_module_url(__FILE__);

        // Register the module with PublishPress
        $args = [
            'title' => __('Author Boxes', 'publishpress-authors'),
            'short_description' => __(
                'Add support for author boxes.',
                'publishpress-authors'
            ),
            'extended_description' => __(
                'Add support for author boxes.',
                'publishpress-authors'
            ),
            'module_url' => $this->module_url,
            'icon_class' => 'dashicons dashicons-edit',
            'slug' => 'author-boxes',
            'default_options' => [
                'enabled' => 'on',
            ],
            'options_page' => false,
            'autoload' => true,
        ];

        // Apply a filter to the default options
        $args['default_options'] = apply_filters('MA_Author_Boxes_default_options', $args['default_options']);

        $legacyPlugin = Factory::getLegacyPlugin();

        $this->module = $legacyPlugin->register_module($this->module_name, $args);

        parent::__construct();
    }

    /**
     * Initialize the module. Conditionally loads if the module is enabled
     */
    public function init()
    {
        add_action('multiple_authors_admin_submenu', [$this, 'adminSubmenu'], 50);
        add_filter('post_updated_messages', [$this, 'setPostUpdateMessages']);
        add_filter('bulk_post_updated_messages', [$this, 'setPostBulkUpdateMessages'], 10, 2);
        add_action('add_meta_boxes', [$this, 'addPreviewMetabox']);
        add_action('add_meta_boxes', [$this, 'addEditorMetabox']);
        add_action('add_meta_boxes', [$this, 'addLayoutSlugMetabox']);
        add_action('add_meta_boxes', [$this, 'addShortcodeMetabox']);
        add_action('add_meta_boxes', [$this, 'addBannerMetabox']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getTitleFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getAvatarFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getNameFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getMetaFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getProfileFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getBioFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getRecentPostsFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getBoxLayoutFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getCustomCssFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getExportFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getImportFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getGenerateTemplateFields'], 10, 2);
        add_action("save_post_" . self::POST_TYPE_BOXES, [$this, 'saveAuthorBoxesData']);
        add_filter('manage_edit-' . self::POST_TYPE_BOXES . '_columns', [$this, 'filterAuthorBoxesColumns']);
        add_action('manage_' . self::POST_TYPE_BOXES . '_posts_custom_column', [$this, 'manageAuthorBoxesColumns'], 10, 2);
        add_filter('pp_multiple_authors_author_layouts', [$this, 'filterAuthorLayouts'], 9);
        add_filter('pp_multiple_authors_author_box_html', [$this, 'filterAuthorBoxHtml'], 9, 2);
        add_filter('pp_multiple_authors_authors_list_box_html', [$this, 'filterAuthorBoxHtml'], 9, 2);
        add_filter('bulk_actions-edit-' . self::POST_TYPE_BOXES . '', [$this, 'removeBulkActionEdit'], 11);


        add_action('wp_ajax_author_boxes_editor_get_preview', ['MultipleAuthorBoxes\AuthorBoxesAjax', 'handle_author_boxes_editor_get_preview']);
        add_action('wp_ajax_author_boxes_editor_get_template', ['MultipleAuthorBoxes\AuthorBoxesAjax', 'handle_author_boxes_editor_get_template']);
        add_action('wp_ajax_author_boxes_editor_save_fields_order', ['MultipleAuthorBoxes\AuthorBoxesAjax', 'handle_author_boxes_fields_order']);

        $this->registerPostType();
    }

    /**
     * @param $columns
     *
     * @return array
     */
    public function filterAuthorBoxesColumns($columns)
    {
        $columns['shortcode'] = esc_html__('Shortcode', 'publishpress-authors');

        unset($columns['date']);

        return $columns;
    }

    /**
     * @param $column
     * @param $postId
     */
    public function manageAuthorBoxesColumns($column, $postId)
    {
        if ($column === 'shortcode') {
            $layout_slug = self::POST_TYPE_BOXES . '_' . $postId;
        ?>
            <input readonly type="text" value='[publishpress_authors_box layout="<?php echo esc_attr($layout_slug); ?>"]' />
        <?php
        }
    }

    /**
     * Save Author boxes data
     *
     * @param integer $post_id post id
     * 
     * @return void
     */
    public function saveAuthorBoxesData($post_id) {
        if (empty($_POST['author-boxes-editor-nonce'])
            || !wp_verify_nonce(sanitize_key($_POST['author-boxes-editor-nonce']), 'author-boxes-editor')) {
            return;
        }

        $post = get_post($post_id);

        $fields = apply_filters('multiple_authors_author_boxes_fields', self::get_fields($post), $post);
        $excluded_input = ['template_action', 'import_action'];
        $meta_data = [];
        $preview_author_names = (isset($_POST['preview_author_names']) && is_array($_POST['preview_author_names'])) ? array_map('sanitize_text_field', $_POST['preview_author_names']) : [];
        foreach ($fields as $key => $args) {
            if (!isset($_POST[$key]) || in_array($key, $excluded_input)) {
                continue;
            }
            if (isset($args['sanitize']) && is_array($args['sanitize']) && $_POST[$key] !== '') {
                $value = $_POST[$key]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
                foreach ($args['sanitize'] as $sanitize) {
                    $value = $sanitize($value);
                }
                $meta_data[$key] = $value;
            } else {
                $sanitize = isset($args['sanitize']) ? $args['sanitize'] : 'sanitize_text_field';
                $meta_data[$key] = (isset($_POST[$key]) && $_POST[$key] !== '') ? $sanitize($_POST[$key]) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            }
        }
        update_post_meta($post_id, self::META_PREFIX . 'layout_preview_authors', $preview_author_names);
        update_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', $meta_data);
    }

    /**
     * Strip out unwanted html
     *
     * @param string $string
     * @return string
     */
    public function stripOutUnwantedHtml($string) {

        $allowed_tags = '<span><i>';

        $string = strip_tags($string, $allowed_tags);

        return $string;
    }

    /**
     * Create default author boxes in the database.
     */
    public static function createDefaultAuthorBoxes()
    {
        $defaultAuthorBoxes = array_reverse(AuthorBoxesDefault::getAuthorBoxesDefaultList());

        foreach ($defaultAuthorBoxes as $name => $title) {
            self::createLayoutPost($name, $title);
            sleep(2);
        }
    }

    /**
     * Update author boxes field value.
     *
     * @param array $fields_data
     * @return void
     */
    public static function updateAuthorBoxesFieldValue($fields_data, $layout_slugs = false)
    {
        if ($layout_slugs && is_array($layout_slugs)) {
            foreach ($layout_slugs as $layout_slug) {
                $layout_post = get_page_by_path($layout_slug, OBJECT, self::POST_TYPE_BOXES);
                if ($layout_post && $layout_post->post_status === 'publish') {
                    $post_id = $layout_post->ID;
                    self::updateAuthorBoxesIdFieldValues($post_id, $fields_data);
                }
            }
        } else {
            $author_boxes = self::getAuthorBoxes();
            if (!empty($author_boxes)) {
                foreach (array_keys($author_boxes) as $author_box) {
                    $post_id = preg_replace("/[^0-9]/", "", $author_box);
                    self::updateAuthorBoxesIdFieldValues($post_id, $fields_data);
                }
            }
        }
    }

    /**
     * Update author boxes field value by ID.
     *
     * @param integer $post_id
     * @param array $fields_data
     * @return void
     */
    public static function updateAuthorBoxesIdFieldValues($post_id, $fields_data) {
        $editor_data = get_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', true);
        if ($editor_data && is_array($editor_data)) {
            foreach ($fields_data as $field_name => $field_value) {
                $editor_data[$field_name] = $field_value;
            }
            update_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', $editor_data);
        }
    }

    /**
     * Create the layout based on name and title
     *
     * @param string $name
     * @param string $title
     */
    protected static function createLayoutPost($name, $title)
    {

        // Check if we already have the layout based on the slug.
        $existingAuthorBox = Utils::get_page_by_title($title, OBJECT, self::POST_TYPE_BOXES);
        if ($existingAuthorBox && $existingAuthorBox->post_status === 'publish') {
            return;
        }

        $editor_data = AuthorBoxesDefault::getAuthorBoxesDefaultData($name);
        if ($editor_data && is_array($editor_data)) {
            $post_id = wp_insert_post(
                [
                    'post_type' => self::POST_TYPE_BOXES,
                    'post_title' => $title,
                    'post_content' => $title,
                    'post_status' => 'publish',
                    'post_name' => sanitize_title($name),
                ]
            );
            update_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', $editor_data);
        }
    }

    /**
     * Register the post types.
     */
    private function registerPostType()
    {
        $labelSingular = __('Author Box', 'publishpress-authors');
        $labelPlural = __('Author Boxes', 'publishpress-authors');

        $postTypeLabels = [
            'name' => _x('%2$s', 'Author Box post type name', 'publishpress-authors'),
            'singular_name' => _x(
                '%1$s',
                'singular author box post type name',
                'publishpress-authors'
            ),
            'add_new' => __('New %1s', 'publishpress-authors'),
            'add_new_item' => __('Add New %1$s', 'publishpress-authors'),
            'edit_item' => __('Edit %1$s', 'publishpress-authors'),
            'new_item' => __('New %1$s', 'publishpress-authors'),
            'all_items' => __('%2$s', 'publishpress-authors'),
            'view_item' => __('View %1$s', 'publishpress-authors'),
            'search_items' => __('Search %2$s', 'publishpress-authors'),
            'not_found' => __('No %2$s found', 'publishpress-authors'),
            'not_found_in_trash' => __('No %2$s found in Trash', 'publishpress-authors'),
            'parent_item_colon' => '',
            'menu_name' => _x('%2$s', 'custom layout post type menu name', 'publishpress-authors'),
            'featured_image' => __('%1$s Image', 'publishpress-authors'),
            'set_featured_image' => __('Set %1$s Image', 'publishpress-authors'),
            'remove_featured_image' => __('Remove %1$s Image', 'publishpress-authors'),
            'use_featured_image' => __('Use as %1$s Image', 'publishpress-authors'),
            'filter_items_list' => __('Filter %2$s list', 'publishpress-authors'),
            'items_list_navigation' => __('%2$s list navigation', 'publishpress-authors'),
            'items_list' => __('%2$s list', 'publishpress-authors'),
        ];

        foreach ($postTypeLabels as $labelKey => $labelValue) {
            $postTypeLabels[$labelKey] = sprintf($labelValue, $labelSingular, $labelPlural);
        }

        $postTypeArgs = [
            'labels' => $postTypeLabels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'map_meta_cap' => true,
            'has_archive' => self::POST_TYPE_BOXES,
            'hierarchical' => false,
            'rewrite' => false,
            'supports' => ['title'],
        ];
        register_post_type(self::POST_TYPE_BOXES, $postTypeArgs);
    }

    /**
     * Add the admin submenu.
     */
    public function adminSubmenu()
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        // Add the submenu to the PublishPress menu.
        add_submenu_page(
            \MA_Multiple_Authors::MENU_SLUG,
            esc_html__('Author Boxes', 'publishpress-authors'),
            esc_html__('Author Boxes', 'publishpress-authors'),
            apply_filters('pp_multiple_authors_manage_layouts_cap', 'ppma_manage_layouts'),
            'edit.php?post_type=' . self::POST_TYPE_BOXES
        );
    }

    /**
     * Add custom update messages to the post_updated_messages filter flow.
     *
     * @param array $messages Post updated messages.
     *
     * @return  array   $messages
     */
    public function setPostUpdateMessages($messages)
    {
        $messages[self::POST_TYPE_BOXES] = [
            1 => __('Author Box updated.', 'publishpress-authors'),
            4 => __('Author Box updated.', 'publishpress-authors'),
            6 => __('Author Box added.', 'publishpress-authors'),
            7 => __('Author Box saved.', 'publishpress-authors'),
            8 => __('Author Box submitted.', 'publishpress-authors'),
        ];

        return $messages;
    }

    /**
     * Add custom update messages to the bulk_post_updated_messages filter flow.
     *
     * @param array $messages Array of messages.
     * @param array $counts Array of item counts for each message.
     *
     * @return  array   $messages
     */
    public function setPostBulkUpdateMessages($messages, $counts)
    {
        $countsUpdated = (int)$counts['updated'];
        $countsLocked = (int)$counts['locked'];
        $countsDeleted = (int)$counts['deleted'];
        $countsTrashed = (int)$counts['trashed'];
        $countsUntrashed = (int)$counts['untrashed'];

        $postTypeNameSingular = __('Author Box', 'publishpress-authors');
        $postTypeNamePlural = __('Author Boxes', 'publishpress-authors');

        $messages[self::POST_TYPE_BOXES] = [
            'updated' => sprintf(
                _n('%1$s %2$s updated.', '%1$s %3$s updated.', $countsUpdated),
                $countsUpdated,
                $postTypeNameSingular,
                $postTypeNamePlural
            ),
            'locked' => sprintf(
                _n(
                    '%1$s %2$s not updated, somebody is editing it.',
                    '%1$s %3$s not updated, somebody is editing them.',
                    $countsLocked
                ),
                $countsLocked,
                $postTypeNameSingular,
                $postTypeNamePlural
            ),
            'deleted' => sprintf(
                _n('%1$s %2$s permanently deleted.', '%1$s %3$s permanently deleted.', $countsDeleted),
                $countsDeleted,
                $postTypeNameSingular,
                $postTypeNamePlural
            ),
            'trashed' => sprintf(
                _n('%1$s %2$s moved to the Trash.', '%1$s %3$s moved to the Trash.', $countsTrashed),
                $countsTrashed,
                $postTypeNameSingular,
                $postTypeNamePlural
            ),
            'untrashed' => sprintf(
                _n('%1$s %2$s restored from the Trash.', '%1$s %3$s restored from the Trash.', $countsUntrashed),
                $countsUntrashed,
                $postTypeNameSingular,
                $postTypeNamePlural
            ),
        ];

        return $messages;
    }

    /**
     * Remove "Edit" from bulk action
     *
     * @param array $actions
     * 
     * @return array
     */
    public function removeBulkActionEdit($actions) 
    {
    
        if (isset($actions['edit'])) {
            unset($actions['edit']);
        }
    
        return $actions;
    }

    /**
     * @param $layouts
     *
     * @return array
     */
    public function filterAuthorLayouts($layouts)
    {
        //add theme layouts
        $layouts = array_merge($layouts, self::getThemeAuthorBoxes());
        //add boxes layout
        $layouts = array_merge($layouts, self::getAuthorBoxes());

        return $layouts;
    }

    /**
     * @param boolean $ids_only
     * @return array
     */
    public static function getAuthorBoxes($ids_only = false)
    {
        $post_args = [
            'post_type' => self::POST_TYPE_BOXES,
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ];

        if ($ids_only) {
            $post_args['fields'] = 'ids';
            return get_posts($post_args);
        }

        $posts = get_posts($post_args);

        $author_boxes = [];

        if (! empty($posts)) {
            foreach ($posts as $post) {
                $author_boxes[self::POST_TYPE_BOXES . '_' . $post->ID] = $post->post_title;
            }
        }

        return $author_boxes;
    }

    /**
     * @return array
     */
    public static function getThemeAuthorBoxes()
    {
        $directories = [
            STYLESHEETPATH . '/publishpress-authors/author-boxes/',
            TEMPLATEPATH . '/publishpress-authors/author-boxes/'
        ];
        $directories = array_unique($directories);

        $theme_author_boxes = [];
        foreach ($directories as $directory) {
            $dir_files = self::authorboxesListDirectoryFiles($directory, 1);
            if (!empty($dir_files)) {
                foreach ($dir_files as $dir_file) {
                    $file_extension = pathinfo($dir_file, PATHINFO_EXTENSION);
                    $filename       = basename($dir_file);
                    $filename       = str_ireplace('.php', '', $filename);
                    if ($file_extension === 'php') { 
                        $theme_author_boxes[$filename] = self::cleanThemeBoxName($filename) . ' (' . __('Theme', 'publishpress-authors') . ')';
                    }
                }
            } 
        }

        return $theme_author_boxes;
    }

    /**
     * This is a clone of wordpress 'list_files' that's been caught in undefined function in some themes.
     * 
     * Returns a listing of all files in the specified folder and all subdirectories up to 100 levels deep.
     *
     * The depth of the recursiveness can be controlled by the $levels param.
     *
     * @since 2.6.0
     * @since 4.9.0 Added the `$exclusions` parameter.
     *
     * @param string   $folder     Optional. Full path to folder. Default empty.
     * @param int      $levels     Optional. Levels of folders to follow, Default 100 (PHP Loop limit).
     * @param string[] $exclusions Optional. List of folders and files to skip.
     * @return string[]|false Array of files on success, false on failure.
     */
    public static function authorboxesListDirectoryFiles($folder = '', $levels = 100, $exclusions = array()) {

        if (function_exists('ppma_list_files')) {// wordpress is currently generating error with native list_files. So, we may not use it
            return list_files($folder, $levels);
        } else {
            if (empty($folder)) {
                return false;
            }
                
            $folder = trailingslashit($folder);
                
            if (! $levels) {
                return false;
            }
                
            $files = array();
                
            if (is_dir($folder)) {
                $dir = @opendir($folder);
                while (($file = readdir($dir)) !== false) {
                    // Skip current and parent folder links.
                    if (in_array($file, array( '.', '..' ), true)) {
                        continue;
                    }
                
                    // Skip hidden and excluded files.
                    if ('.' === $file[0] || in_array($file, $exclusions, true)) {
                        continue;
                    }
                
                    if (!is_dir($folder . $file)) {
                        $files[] = $folder . $file;
                    }
                }
                
                closedir($dir);
            }
                
            return $files;
        }
    }

    /**
     * Clean author box name
     *
     * @param string $filename
     * @return string $filename
     */
    private static function cleanThemeBoxName($filename) {

        $filename = str_ireplace(['-', '_', '.'], ' ', $filename);
        //Remove all non-alphanumeric and space characters
        $filename = preg_replace('/[^\da-z ]/i', '', $filename);
        $filename = trim($filename);
        $filename = ucwords($filename);


        return $filename;
    }

    /**
     * @param $html
     * @param $args
     *
     * @return string
     */
    public function filterAuthorBoxHtml($html, $args)
    {

        $layoutName = sanitize_text_field($args['layout']);
        $author_box_id = false;
        if (substr($layoutName, 0, 10) === self::POST_TYPE_BOXES) {
            $author_box_id = preg_replace("/[^0-9]/", "", $layoutName );
        } elseif(in_array($layoutName, ['simple_list', 'centered', 'boxed', 'inline', 'inline_avatar'])) {
            $author_box_id = $this->getLegacyLayoutAuthorBoxId($layoutName);
        } else {
            //check in theme boxes template
            $theme_boxes = self::getThemeAuthorBoxes();
            if (array_key_exists($layoutName, $theme_boxes)) {
                $box_template = locate_template(['publishpress-authors/author-boxes/'.$layoutName.'.php']);
                if ($box_template) {
                    global $ppma_template_authors, $ppma_template_authors_post;
                    $ppma_template_authors      = $args['authors'];
                    $ppma_template_authors_post = isset($args['post']) ? $args['post'] : false;
                    ob_start(); 
                    include $box_template;
                    $html = ob_get_clean();
                    return $html;
                }
            }

            return $html;
        }

        $editor_data = $this->get_author_boxes_layout_meta_values($author_box_id);

        if (!is_array($editor_data)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                    sprintf(
                        '[PublishPress Authors] Author boxes post not found: %s',
                        $layoutName
                    )
                );
            }

            return $html;
        }

        $post_id = $editor_data['post_id'];
        $fields   = apply_filters('multiple_authors_author_boxes_fields', self::get_fields(get_post($post_id)), get_post($post_id));

        $preview_args = [];
        foreach ($fields as $key => $key_args) {
            $field_key   = $key;
            $field_value = isset($editor_data[$key]) ? $editor_data[$key] : '';
            if ($field_key === 'box_tab_custom_wrapper_class') {
                $field_value .= ' ' . $args['css_class'];
            }
            $key_args['key']    = $field_key;
            $key_args['value']  = $field_value;
            $preview_args[$key] = $key_args;
        }
        $preview_args['authors']         = $args['authors'];
        $preview_args['post_id']         = $post_id;
        $preview_args['short_code_args'] = $args;

        $html = self::get_rendered_author_boxes_editor_preview($preview_args);

        return $html;
    }

    /**
     * Get legacy layout author box ID
     */
    public function getLegacyLayoutAuthorBoxId($layout)
    {

        $args = [
            'name'           => 'author_boxes_' . $layout,
            'post_type'      => MA_Author_Boxes::POST_TYPE_BOXES,
            'post_status'    => 'publish',
            'posts_per_page' => 1
        ];
        $layout_post = get_posts($args);
        if (empty($layout_post)) {
            //recreate default
            MA_Author_Boxes::createDefaultAuthorBoxes();
            $layout_post = get_posts($args);
        }
        
        if (!empty($layout_post)) {
            return $layout_post[0]->ID;
        }

        return 0;
    }

    /**
     * Add editor metabox
     *
     * @return void
     */
    public function addPreviewMetabox()
    {
        add_meta_box(
            self::META_PREFIX . 'preview_area',
            __('Author Box Preview', 'publishpress-authors'),
            [$this, 'renderPreviewMetabox'],
            self::POST_TYPE_BOXES,
            'normal',
            'high'
        );
    }

    /**
     * Add editor metabox
     *
     * @return void
     */
    public function addEditorMetabox()
    {
        add_meta_box(
            self::META_PREFIX . 'editor_area',
            __('Author Box Editor', 'publishpress-authors'),
            [$this, 'renderEditorMetabox'],
            self::POST_TYPE_BOXES,
            'normal',
            'high'
        );
    }

    /**
     * Add layout slug metabox
     *
     * @return void
     */
    public function addLayoutSlugMetabox()
    {
        add_meta_box(
            self::META_PREFIX . 'layout_slug',
            __('Layout Slug', 'publishpress-authors'),
            [$this, 'renderLayoutSLugMetabox'],
            self::POST_TYPE_BOXES,
            'side'
        );
    }

    /**
     * Add shortcode metabox
     *
     * @return void
     */
    public function addShortcodeMetabox()
    {
        add_meta_box(
            self::META_PREFIX . 'shortcode',
            __('Shortcode', 'publishpress-authors'),
            [$this, 'renderShortcodeMetabox'],
            self::POST_TYPE_BOXES,
            'side'
        );
    }

    /**
     * Add banner metabox
     *
     * @return void
     */
    public function addBannerMetabox()
    {
        if (!Utils::isAuthorsProActive()) {
            add_meta_box(
                self::META_PREFIX . 'banner',
                __('Banner', 'publishpress-authors'),
                [$this, 'renderBannerMetabox'],
                self::POST_TYPE_BOXES,
                'side',
                'low'
            );
        }
    }

    /**
     * Get the fields tabs to be rendered in the author box editor
     *
     * @param WP_Post $post object.
     *
     * @return array
     */
    public static function get_fields_tabs($post)
    {
        $fields_tabs = [
            'title'     => [
                'label'  => __('Title', 'publishpress-authors'),
                'icon'   => 'dashicons dashicons-translation',
            ],
            'avatar'  => [
                'label' => __('Avatar', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-format-image',
            ],
            'name'  => [
                'label' => __('Display Name', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-editor-spellcheck',
            ],
            'author_bio'  => [
                'label' => __('Biographical Info', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-welcome-write-blog',
            ],
            'meta'  => [
                'label' => __('Meta', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-forms',
            ],
            'profile_fields'  => [
                'label' => __('Author Fields', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-groups',
            ],
            'author_recent_posts'  => [
                'label' => __('Author Recent Posts', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-admin-page',
            ],
            'box_layout'  => [
                'label' => __('Box Layout', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-editor-table',
            ],
            'custom_css'  => [
                'label' => __('Custom CSS', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-editor-code',
            ],
            'export'  => [
                'label' => __('Export', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-database-export',
            ],
            'import'  => [
                'label' => __('Import', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-database-import',
            ],
            'generate_template'  => [
                'label' => __('Generate Theme Template', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-html',
            ],
        ];

        /**
         * Customize fields tabs presented in the author boxes editor.
         *
         * @param array $fields_tabs Existing fields tabs to display.
         * @param WP_Post $post object.
         */
        $fields_tabs = apply_filters('authors_boxes_editor_fields_tabs', $fields_tabs, $post);

        return $fields_tabs;
    }
    
    /**
     * Get the fields to be rendered in the author boxes editor
     *
     * @param WP_Post $post object.
     *
     * @return array
     */
    public static function get_fields($post)
    {
        $fields = [];

        /**
         * Customize fields presented in the author boxes editor.
         *
         * @param array $fields Existing fields to display.
         * @param WP_Post $post object.
         */
        $fields = apply_filters('author_boxes_editor_fields', $fields, $post);

        return $fields;
    }

    /**
     * Render layout slug metaboxes
     *
     * @param \WP_Post $post
     * @return void
     */
    public function renderLayoutSLugMetabox(\WP_Post $post)
    { 
        $layout_slug = self::POST_TYPE_BOXES . '_' . $post->ID;
    ?>
        <input type="text" value="<?php echo esc_attr($layout_slug); ?>" readonly />
    <?php
    }

    /**
     * Render box metaboxes
     *
     * @param \WP_Post $post
     * @return void
     */
    public function renderBannerMetabox(\WP_Post $post)
    { 
        Utils::ppma_pro_sidebar();
    }

    /**
     * Render shortcode metaboxes
     *
     * @param \WP_Post $post
     * @return void
     */
    public function renderShortcodeMetabox(\WP_Post $post)
    { 
        $layout_slug = self::POST_TYPE_BOXES . '_' . $post->ID;
    ?>
        <textarea readonly>[publishpress_authors_box layout="<?php echo esc_attr($layout_slug); ?>"]</textarea>
        <p class="description"><a href="https://publishpress.com/knowledge-base/shortcodes/" target="blank"><?php esc_html_e('Shortcode documentation.', 'publishpress-authors'); ?></a></p>
    <?php
    }

    /**
     * Get Author boxes meta value
     * TODO: Most of options here will be migrated via installer in the next release when deprecating meta
     *
     * @param integer $post_id
     * @param boolean $use_default
     * @return array $editor_data
     */
    public function get_author_boxes_layout_meta_values($post_id, $use_default = false) {

        if ($use_default || empty(get_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', true))) {
            $editor_data = AuthorBoxesDefault::getAuthorBoxesDefaultData('author_boxes_boxed');
        } else {
            $editor_data = (array) get_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', true);
        }

        $editor_data['post_id'] = $post_id;

        //set social profile defaults
        $social_fields = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube'];
        foreach ($social_fields as $social_field) {
            //set default display to icon
            if (!isset($editor_data['profile_fields_'.$social_field.'_display']) 
                || (isset($editor_data['profile_fields_'.$social_field.'_display']) && empty($editor_data['profile_fields_'.$social_field.'_display']))
            ) {
                $editor_data['profile_fields_'.$social_field.'_display'] = 'icon';
            }
            //set default ucon value
            if (!isset($editor_data['profile_fields_'.$social_field.'_display_icon']) 
                || (isset($editor_data['profile_fields_'.$social_field.'_display_icon']) && empty($editor_data['profile_fields_'.$social_field.'_display_icon']))
            ) {
                $editor_data['profile_fields_'.$social_field.'_display_icon'] = '<span class="dashicons dashicons-'.$social_field.'"></span>';
            }

            //set social_field profile html tag to 'a' if icon is select
            if (isset($editor_data['profile_fields_'.$social_field.'_display']) && $editor_data['profile_fields_'.$social_field.'_display'] === 'icon' ) {
                $editor_data['profile_fields_'.$social_field.'_html_tag'] = 'a';
            }

            //set social_field profile display icon size
            if (!isset($editor_data['profile_fields_'.$social_field.'_display_icon_size']) 
                || (isset($editor_data['profile_fields_'.$social_field.'_display_icon_size']) && empty($editor_data['profile_fields_'.$social_field.'_display_icon_size']))
            ) {
                $editor_data['profile_fields_'.$social_field.'_display_icon_size'] = '16';
            }

            //set social_field profile display icon background color
            if (!isset($editor_data['profile_fields_'.$social_field.'_display_icon_background_color'])) {
                $editor_data['profile_fields_'.$social_field.'_display_icon_background_color'] = '#655997';
            }

            //set social_field profile display icon border radius
            if (!isset($editor_data['profile_fields_'.$social_field.'_display_icon_border_radius'])) {
                $editor_data['profile_fields_'.$social_field.'_display_icon_border_radius'] = '50';
            }
            
        }


        return apply_filters('multiple_authors_get_author_boxes_layout_meta_values', $editor_data, $post_id, $use_default);
    }

    /**
     * Render preview metabox
     *
     * @param \WP_Post $post
     * 
     * @return void
     */
    public function renderPreviewMetabox(\WP_Post $post)
    {
        $fields = apply_filters('multiple_authors_author_boxes_fields', self::get_fields($post), $post);

        if ($post->post_status === 'auto-draft') {
            $editor_data = $this->get_author_boxes_layout_meta_values($post->ID, true);
        } else {
            $editor_data = $this->get_author_boxes_layout_meta_values($post->ID);
        }

        $layout_preview_authors = get_post_meta($post->ID, self::META_PREFIX . 'layout_preview_authors', true);
        if (is_array($layout_preview_authors) && !empty($layout_preview_authors)) {
            $preview_authors = [];
            foreach ($layout_preview_authors as $preview_author_slug) {
                $userAuthor = Author::get_by_term_slug($preview_author_slug);
                if (!$userAuthor) {
                    $userAuthor = get_user_by('slug', $preview_author_slug);
                }
                $preview_authors[] = $userAuthor;
            }
        } else {
            $preview_authors = [Author::get_by_user_id(get_current_user_id())];
        }
        
        /**
         * Render fields
         */
        $preview_args = [];
        $preview_args['authors'] = $preview_authors;
        $preview_args['post_id'] = $editor_data['post_id'];
        $preview_args['admin_preview'] = true;
        foreach ($fields as $key => $args) {
            $args['key']   = $key;
            $args['value'] = isset($editor_data[$key]) ? $editor_data[$key] : '';
            $preview_args[$key] = $args;
        }
        
        ?>
        <div class="pressshack-admin-wrapper publishpress-author-box-editor">
            <div class="preview-section wrapper-column">
                <?php 
                /**
                 * Render editor preview
                 */
                echo self::get_rendered_author_boxes_editor_preview($preview_args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
            </div>
        </div>
<?php

    }

    /**
     * Render editor metabox
     *
     * @param \WP_Post $post
     * 
     * @return void
     */
    public function renderEditorMetabox(\WP_Post $post)
    {
        /**
         * Filter the fields tabs on the Author boxes editor.
         *
         * @param array $tabs
         * @param WP_Post $post object
         *
         * @return array
         */
        $fields_tabs  = apply_filters('multiple_authors_author_boxes_fields_tabs', self::get_fields_tabs($post), $post);

        /**
         * Filter the fields on the Author boxes editor.
         *
         * @param array $fields
         * @param WP_Post $post object
         *
         * @return array
         */
        $fields = apply_filters('multiple_authors_author_boxes_fields', self::get_fields($post), $post);
        ?>
        <div class="pressshack-admin-wrapper publishpress-author-box-editor">
            <div class="ppma-author-box-editor-tabs">
                <ul>
                    <?php
                    /**
                     * Render field tabs
                     */
                    foreach ($fields_tabs as $key => $args) {
                        $active_tab = ($key === self::AUTHOR_BOXES_EDITOR_DEFAULT_TAB) ? ' active' : ''; ?>
                    <li>
                        <a data-tab="<?php esc_attr_e($key); ?>" 
                            class="<?php esc_attr_e($active_tab); ?>" 
                            href="#"
                            >
                            <span class="<?php esc_attr_e($args['icon']); ?>"></span>
                            <span class="item"><?php esc_html_e($args['label']); ?></span>
                        </a>
                    </li>
                    <?php
                    } ?>
                </ul>
            </div>

            <div class="ppma-author-box-editor-fields wrapper-column">
                <table class="form-table ppma-author-boxes-editor-table" role="presentation">
                    <tbody>
                        <?php 
                        if ($post->post_status === 'auto-draft') {
                            $editor_data = $this->get_author_boxes_layout_meta_values($post->ID, true);
                        } else {
                            $editor_data = $this->get_author_boxes_layout_meta_values($post->ID);
                        }

                        /**
                         * Render fields
                         */
                        foreach ($fields as $key => $args) {
                            $args['key']       = $key;
                            $args['value']     = isset($editor_data[$key]) ? $editor_data[$key] : '';
                            $args['post_id']   = $post->ID;
                            echo self::get_rendered_author_boxes_editor_partial($args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        }

                        wp_nonce_field('author-boxes-editor', 'author-boxes-editor-nonce');
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Get a rendered editor preview
     *
     * @param array $args Arguments to render the preview.
     */
    public static function get_rendered_author_boxes_editor_preview($args) {
        global $ppma_instance_id, $auto_list_prefix;
        ob_start();

        if (!$ppma_instance_id) {
            $ppma_instance_id = 1;
        } else {
            $ppma_instance_id += 1;
        }

        /**
         * I'm leaving this as 1 as it's not working when generating css, 
         * only one instance is added. Instead, I'll be adding the box additional class
         * to prevent boxes styles from classing.
         */
        $args['instance_id'] = 1;

        $args['additional_class'] = str_replace(' ', '.', trim($args['box_tab_custom_wrapper_class']['value']));

        //custom styles
        $custom_styles = '';
        $custom_styles = AuthorBoxesStyles::getTitleFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getAvatarFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getNameFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getBioFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getMetaFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getRProfileFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getRecentPostsFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getBoxLayoutFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getCustomCssFieldStyles($args, $custom_styles);

        $admin_preview = (isset($args['admin_preview']) && $args['admin_preview']) ? true : false;

        $profile_fields   = self::get_profile_fields($args['post_id']);

        $authors = (isset($args['authors']) && is_array($args['authors']) && !empty($args['authors'])) ? $args['authors'] : [];

        $box_post         = get_post($args['post_id']);
        $box_post_id      = (is_object($box_post) && isset($box_post->ID)) ? $box_post->ID : '1';
        $li_style         = true;
        $author_separator = $args['box_tab_layout_author_separator']['value']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped;
        $author_counts    = count($authors);
        $body_class       = 'pp-multiple-authors-boxes-wrapper pp-multiple-authors-wrapper '. esc_attr($args['box_tab_custom_wrapper_class']['value']) .' box-post-id-'. esc_attr($args['post_id']) .' box-instance-id-'. esc_attr($args['instance_id']) .' ppma_boxes_' . esc_attr($box_post_id);

        if (is_object($box_post) && isset($box_post->post_name) && $box_post->post_name === 'author_boxes_inline') {
            $li_style = false;
            $args['name_html_tag']['value'] = 'span';
        }
        ?>

        <?php if ($admin_preview) : ?>
        <div class="preview-container">
            <div class="live-preview">
                <div class="live-preview-box">
                    <div class="editor-preview-author">
                        <div class="editor-preview-author-label">
                            <?php echo esc_html__('Preview as:', 'publishpress-authors'); ?>
                        </div>
                        <div class="editor-preview-author-users">
                            <select data-nonce="<?php
                                echo esc_attr(wp_create_nonce('authors-user-search')); ?>"
                                    class="authors-select2 authors-user-slug-search"
                                    data-placeholder="<?php
                                    esc_attr_e('Select Authors', 'publishpress-authors'); ?>"
                                    name="preview_author_names[]"
                                    multiple>
                                    <?php foreach ($authors as $author) : ?>
                                        <option value="<?php echo esc_attr($author->slug); ?>" selected>
                                            <?php echo esc_html($author->display_name); ?>
                                        </option>
                                    <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
        <?php endif; ?> 

                    <!--begin code -->

                    <?php if (isset($args['short_code_args']) && isset($args['short_code_args']['search_box_html']) && !empty($args['short_code_args']['search_box_html'])) : ?>
                        <?php echo $args['short_code_args']['search_box_html']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php endif; ?>

                    <<?php echo ($li_style ? 'div' : 'span'); ?> class="<?php echo esc_attr($body_class); ?>"
                    data-post_id="<?php echo esc_attr($args['post_id']); ?>"
                    data-instance_id="<?php echo esc_attr($args['instance_id']); ?>"
                    data-additional_class="<?php echo esc_attr($args['additional_class']); ?>"
                    data-original_class="pp-multiple-authors-boxes-wrapper pp-multiple-authors-wrapper box-post-id-<?php echo esc_attr($args['post_id']); ?> box-instance-id-<?php echo esc_attr($args['instance_id']); ?>">
                        <?php if ($args['show_title']['value']) : ?>
                            <?php if ($author_counts > 1) : ?>
                                <<?php echo esc_html($args['title_html_tag']['value']); ?> class="widget-title box-header-title"><?php echo esc_html($args['title_text_plural']['value']); ?></<?php echo esc_html($args['title_html_tag']['value']); ?>>
                            <?php else : ?>
                                <<?php echo esc_html($args['title_html_tag']['value']); ?> class="widget-title box-header-title"><?php echo esc_html($args['title_text']['value']); ?></<?php echo esc_html($args['title_html_tag']['value']); ?>>
                            <?php endif; ?>
                        <?php endif; ?>
                        <span class="ppma-layout-prefix"><?php echo html_entity_decode($args['box_tab_layout_prefix']['value']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        <?php if ($li_style) : ?>
                            <ul class="pp-multiple-authors-boxes-ul">
                        <?php endif; ?>
                            <?php if (!empty($authors)) : ?>
                                <?php echo esc_html($auto_list_prefix); ?>
                                <?php foreach ($authors as $index => $author) : ?>
                                    <?php if ($author && is_object($author) && isset($author->term_id)) : ?>
                                        <?php 
                                        if ($args['author_recent_posts_show']['value']) :
                                            $author_recent_posts = multiple_authors_get_author_recent_posts(
                                                $author, 
                                                true,
                                                $args['author_recent_posts_limit']['value'],
                                                $args['author_recent_posts_orderby']['value'],
                                                $args['author_recent_posts_order']['value']
                                            );
                                        else :
                                            $author_recent_posts = [];
                                        endif;

                                        //author fields item position
                                        $name_row_extra = '';
                                        $bio_row_extra  = '';
                                        $meta_row_extra = '';

                                        foreach ($profile_fields as $key => $data) {
                                            if (!in_array($key, self::AUTHOR_BOXES_EXCLUDED_FIELDS)) {
                                                $profile_show_field = $args['profile_fields_show_' . $key]['value'] ? true : false;

                                                $profile_html_tag  = !empty($args['profile_fields_' . $key . '_html_tag']['value']) 
                                                    ? $args['profile_fields_' . $key . '_html_tag']['value'] : 'span';

                                                $profile_display  = !empty($args['profile_fields_' . $key . '_display']['value']) 
                                                    ? $args['profile_fields_' . $key . '_display']['value'] : 'icon_prefix_value_suffix';

                                                $profile_value_prefix      = $args['profile_fields_' . $key . '_value_prefix']['value'];
                                                $profile_display_prefix    = $args['profile_fields_' . $key . '_display_prefix']['value'];
                                                $profile_display_suffix    = $args['profile_fields_' . $key . '_display_suffix']['value'];
                                                $profile_display_icon     = $args['profile_fields_' . $key . '_display_icon']['value'];
                                                $profile_display_position = $args['profile_fields_' . $key . '_display_position']['value'];

                                                $profile_before_display_prefix = $args['profile_fields_' . $key . '_before_display_prefix']['value'];
                                                $profile_after_display_suffix  = $args['profile_fields_' . $key . '_after_display_suffix']['value'];

                                                if (empty(trim($profile_display_position))) {
                                                    $profile_display_position = 'meta';
                                                }

                                                if ($data['type'] === 'wysiwyg') {
                                                    $field_value = $author->$key;
                                                } else {
                                                    $field_value = esc_html($author->$key);
                                                }

                                                if (empty(trim($field_value))) {
                                                    continue;
                                                }

                                                $display_field_value = '';
                                                if ($profile_display === 'icon_prefix_value_suffix') {
                                                    if (!empty($profile_display_icon)) {
                                                        $display_field_value .= html_entity_decode($profile_display_icon) . ' ';
                                                    }
                                                    if (!empty($profile_display_prefix)) {
                                                        $display_field_value .= esc_html($profile_display_prefix) . ' ';
                                                    }
                                                    if (!empty($field_value)) {
                                                        $display_field_value .= $field_value . ' ';
                                                    }
                                                    if (!empty($profile_display_suffix)) {
                                                        $display_field_value .= esc_html($profile_display_suffix);
                                                    }
                                                } elseif ($profile_display === 'value') {
                                                    $display_field_value .= $field_value;
                                                } elseif ($profile_display === 'prefix') {
                                                    $display_field_value .= esc_html($profile_display_prefix);
                                                } elseif ($profile_display === 'suffix') {
                                                    $display_field_value .= esc_html($profile_display_suffix);
                                                } elseif ($profile_display === 'icon') {
                                                    $display_field_value .= html_entity_decode($profile_display_icon) . ' ';
                                                } elseif ($profile_display === 'prefix_value_suffix') {
                                                    if (!empty($profile_display_prefix)) {
                                                        $display_field_value .= esc_html($profile_display_prefix) . ' ';
                                                    }
                                                    if (!empty($field_value)) {
                                                        $display_field_value .= $field_value . ' ';
                                                    }
                                                    if (!empty($profile_display_suffix)) {
                                                        $display_field_value .= esc_html($profile_display_suffix);
                                                    }
                                                }

                                                if ($profile_show_field) : ?>
                                                    <?php 
                                                    $profile_field_html  = '';
                                                    
                                                    if (!empty(trim($profile_before_display_prefix))) {
                                                        $profile_field_html  .= '<span class="ppma-author-field-meta-prefix"> '. $profile_before_display_prefix .' </span>';
                                                    }
                                                    $profile_field_html .= '<'. esc_html($profile_html_tag) .'';
                                                    $profile_field_html .= ' class="ppma-author-'. esc_attr($key) .'-profile-data ppma-author-field-meta '. esc_attr('ppma-author-field-type-' . $data['type']) .'" aria-label="'. esc_attr(($data['label'])) .'"';
                                                    if ($profile_html_tag === 'a') {
                                                        $profile_field_html .= ' href="'. $profile_value_prefix.$field_value .'" rel="nofollow"';
                                                    }
                                                    $profile_field_html .= '>';
                                                    if ($profile_show_field) {
                                                        $profile_field_html .= $display_field_value;
                                                    }
                                                    $profile_field_html .= '</'. esc_html($profile_html_tag) .'>';
                                                    if (!empty(trim($profile_after_display_suffix))) {
                                                        $profile_field_html  .= '<span class="ppma-author-field-meta-suffix"> '. $profile_after_display_suffix .' </span>';
                                                    }
                                                    ?>
                                                    <?php 
                                                    if ($profile_display_position === 'name') {
                                                        $name_row_extra .= $profile_field_html;
                                                    } elseif ($profile_display_position === 'bio') {
                                                        $bio_row_extra  .= $profile_field_html;
                                                    } elseif ($profile_display_position === 'meta') {
                                                        $meta_row_extra .= $profile_field_html;
                                                    }
                                                    ?>
                                                <?php endif;
                                            }
                                        }
                                        ?>
                                        <?php if ($li_style) : ?>
                                            <li class="pp-multiple-authors-boxes-li author_index_<?php echo esc_attr($index); ?> author_<?php echo esc_attr($author->slug); ?> <?php echo ($args['avatar_show']['value']) ? 'has-avatar' : 'no-avatar'; ?>">
                                        <?php endif; ?>

                                            <?php if ($args['avatar_show']['value']) : ?>
                                                <div class="pp-author-boxes-avatar">
                                                    <?php if ($author->get_avatar()) : ?>
                                                        <?php echo $author->get_avatar($args['avatar_size']['value']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                    <?php else : ?>
                                                        <?php echo get_avatar($author->user_email, $args['avatar_size']['value']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else : 
                                                $custom_styles .= '.' . str_replace(' ', '.', trim($body_class)) . ' ul li > div:nth-child(1) {flex: 1 !important;}';
                                            ?>
                                            <?php endif; ?>

                                            <<?php echo ($li_style ? 'div' : 'span'); ?> class="pp-author-boxes-avatar-details">
                                            <?php if ($args['name_show']['value']) : ?>
                                                    <<?php echo esc_html($args['name_html_tag']['value']); ?> class="pp-author-boxes-name multiple-authors-name">
                                                        <a href="<?php echo esc_url($author->link); ?>" rel="author" title="<?php echo esc_attr($author->display_name); ?>" class="author url fn"><?php echo esc_html($author->display_name); ?></a><?php if (!$li_style && $author_counts > 1 && $index !== $author_counts - 1) {
                                                            echo html_entity_decode($author_separator); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                                        } ?>
                                                    </<?php echo esc_html($args['name_html_tag']['value']); ?>>
                                                <?php endif; ?>
                                                <?php echo $name_row_extra ; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                <?php if ($args['author_bio_show']['value']) : ?>
                                                    <<?php echo esc_html($args['author_bio_html_tag']['value']); ?> class="pp-author-boxes-description multiple-authors-description">
                                                        <?php echo $author->get_description($args['author_bio_limit']['value']);  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                                                    </<?php echo esc_html($args['author_bio_html_tag']['value']); ?>>
                                                <?php endif; ?>
                                                <?php echo $bio_row_extra ; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                                                <?php if ($args['meta_show']['value']) : ?>
                                                    <<?php echo esc_html($args['meta_html_tag']['value']); ?> class="pp-author-boxes-meta multiple-authors-links">
                                                        <?php if ($args['meta_view_all_show']['value']) : ?>
                                                            <a href="<?php echo esc_url($author->link); ?>" title="<?php echo esc_attr__('View all posts', 'publishpress-authors'); ?>">
                                                                <span><?php echo esc_html__('View all posts', 'publishpress-authors'); ?></span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($args['meta_email_show']['value'] && $author->user_email) : ?>
                                                            <a href="<?php echo esc_url('mailto:'.$author->user_email); ?>" target="_blank" aria-label="<?php echo esc_attr__('Email', 'publishpress-authors'); ?>" rel="nofollow">
                                                                <span class="dashicons dashicons-email-alt"></span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($args['meta_site_link_show']['value'] && $author->user_url) : ?>
                                                            <a href="<?php echo esc_url($author->user_url); ?>" target="_blank" aria-label="<?php echo esc_attr__('Website', 'publishpress-authors'); ?>" rel="nofollow">
                                                                <span class="dashicons dashicons-admin-links"></span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </<?php echo esc_html($args['meta_html_tag']['value']); ?>>
                                                <?php endif; ?>
                                                <?php echo $meta_row_extra ; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                                                <?php if ($args['author_recent_posts_show']['value']) : ?>
                                                    <div class="pp-author-boxes-recent-posts">
                                                        <?php if ($args['author_recent_posts_title_show']['value'] && (!empty($author_recent_posts) || $args['author_recent_posts_empty_show']['value'])) : ?>
                                                            <div class="pp-author-boxes-recent-posts-title">
                                                                <?php echo esc_html__('Recent Posts'); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!empty($author_recent_posts)) : ?>
                                                            <div class="pp-author-boxes-recent-posts-items">
                                                                <?php foreach($author_recent_posts as $recent_post_id) : ?>
                                                                    <<?php echo esc_html($args['author_recent_posts_html_tag']['value']); ?> class="pp-author-boxes-recent-posts-item">
                                                                        <span class="dashicons dashicons-media-text"></span>
                                                                        <a href="<?php echo esc_url(get_the_permalink($recent_post_id)); ?>" title="<?php echo esc_attr(get_the_title($recent_post_id)); ?>">
                                                                            <?php echo esc_html(html_entity_decode(get_the_title($recent_post_id))); ?>
                                                                        </a>
                                                                    </<?php echo esc_html($args['author_recent_posts_html_tag']['value']); ?>>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else : ?>
                                                            <?php if ($args['author_recent_posts_empty_show']['value']) : ?>
                                                                <div class="pp-author-boxes-recent-posts-empty"><?php echo esc_html__('No Recent Posts by this Author', 'publishpress-authors'); ?></div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </<?php echo ($li_style ? 'div' : 'span'); ?>>
                                        <?php if ($li_style) : ?>
                                            </li>
                                        <?php endif; ?>
                                        <?php if ($li_style && $author_counts > 1 && $index !== $author_counts - 1) {
                                            echo html_entity_decode($author_separator); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                        } ?>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php if ($li_style) : ?>
                            </ul>
                        <?php endif; ?>
                    <span class="ppma-layout-suffix"><?php echo html_entity_decode($args['box_tab_layout_suffix']['value']); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                    </<?php echo ($li_style ? 'div' : 'span'); ?>>
                    <!--end code -->
                    <?php if ($admin_preview) : ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($args['short_code_args']) && isset($args['short_code_args']['pagination']) && !empty($args['short_code_args']['pagination'])) : ?>
            <nav class="footer-navigation navigation pagination">
                <div class="nav-links">
                    <?php echo $args['short_code_args']['pagination']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </div>
            </nav>
        <?php endif; ?>
        <?php Utils::loadLayoutFrontCss(); ?>

        <?php if ($admin_preview || is_admin()) : ?>
            <div class="pp-author-boxes-editor-preview-styles">
                <style>
                    <?php echo $custom_styles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                </style>
            </div>
        <?php else : ?>
            <?php /*wp_add_inline_style(
                'multiple-authors-widget-css',
                $custom_styles
            );*/
            ?>
            <style>
                <?php echo $custom_styles; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </style>
        <?php endif; ?>
        
        <?php
        $auto_list_prefix = '';//reset show by

        return ob_get_clean();
    }

    /**
     * Get a rendered field partial
     *
     * @param array $args Arguments to render in the partial.
     */
    private static function get_rendered_author_boxes_editor_partial($args)
    {
        $defaults = [
            'type'        => 'text',
            'tab'         => self::AUTHOR_BOXES_EDITOR_DEFAULT_TAB,
            'options'     => [],
            'value'       => '',
            'label'       => '',
            'description' => '',
            'min'         => '',
            'max'         => '',
            'placeholder' => '',
            'rows'        => '20',
            'readonly'    => false,
            'tabbed'      => 0,
            'tab_name'    => '',
            'show_input'  => false,
            'post_id'     => false,
        ];

        $args      = array_merge($defaults, $args);
        $key       = $args['key'];
        $tab_class = 'ppma-boxes-editor-tab-content ppma-' . $args['tab'] . '-tab ' . $args['type'] . ' ppma-editor-'.$key;
        if ('range' === $args['type'] && $args['show_input']) {
            $tab_class .= ' double-input';
        }

        if ((int)$args['tabbed'] > 0) {
            $tab_class .= ' tabbed-content tabbed-content-' . $args['tab_name'];
        }
            
        $tab_style = ($args['tab'] === self::AUTHOR_BOXES_EDITOR_DEFAULT_TAB) ? '' : 'display:none;';
        ob_start();
        $generate_tab_title = false;
        if (in_array($args['type'], ['textarea', 'export_action', 'import_action', 'template_action', 'line_break', 'profile_header'])) {
            $th_style = 'display: none;';
            $colspan  = 2;
        } else {
            $th_style = '';
            $colspan  = '';
        }
        ?>
        <tr 
            class="<?php echo esc_attr($tab_class); ?>"
            data-tab="<?php echo esc_attr($args['tab']); ?>"
            style="<?php echo esc_attr($tab_style); ?>">
            <?php if (!empty($args['label'])) : ?>
                <th scope="row" style="<?php echo esc_attr($th_style); ?>">
                    <label for="<?php echo esc_attr($key); ?>">
                        <?php echo esc_html($args['label']); ?>
                    </label>
                </th>
            <?php endif; ?>
            <td class="input" colspan="<?php echo esc_attr($colspan); ?>">
                <?php
                if ('number' === $args['type']) :
                    ?>
                    <input name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>" 
                        type="<?php echo esc_attr($args['type']); ?>"
                        value="<?php echo esc_attr($args['value']); ?>"
                        min="<?php echo esc_attr($args['min']); ?>"
                        max="<?php echo esc_attr($args['max']); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo (isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''; ?>
                         />
                        <?php
                elseif ('checkbox' === $args['type']) :
                    ?>
                    <input name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>" 
                        type="<?php echo esc_attr($args['type']); ?>"
                        value="1"
                        <?php echo (isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''; ?>
                        <?php checked($args['value'], 1); ?> />
                <?php
                elseif ('select' === $args['type']) :
                    ?>
                    <select name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo (isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''; ?>
                        />
                        <?php foreach ($args['options'] as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" 
                                <?php selected($key, $args['value']); ?>>
                                <?php echo esc_html($label); ?>
                            </option>

                        <?php endforeach; ?>
                    </select>
                <?php
                elseif ('color' === $args['type']) :
                    ?>
                    <input name="<?php echo esc_attr($key); ?>"
                        class="pp-editor-color-picker"
                        id="<?php echo esc_attr($key); ?>" 
                        type="text"
                        value="<?php echo esc_attr($args['value']); ?>" />
                <?php
                elseif ('export_action' === $args['type']) :
                    ?>
                    <h2 class="title"><?php echo esc_html__('Export Editor Settings', 'publishpress-authors'); ?></h2>
                    <p class="description"><?php echo esc_html__('You can use this data to export your author box design and import it to a new site.', 'publishpress-authors'); ?></p>
                    <textarea name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>" 
                        type="<?php echo esc_attr($args['type']); ?>"
                        rows="<?php echo esc_attr($args['rows']); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo (isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''; ?>
                        ></textarea>
                    <div>
                        <a href="#" data-target_input="<?php echo esc_attr($key); ?>" class="button button-secondary ppma-editor-copy-clipboard">
                            <?php echo esc_html__('Copy to Clipboard', 'publishpress-authors'); ?>
                        </a>
                    </div>
                    <div class="ppma-editor-copied-to-clipboard">
                        <?php echo esc_attr__('Copied to Clipboard!', 'publishpress-authors'); ?>
                    </div>
                <?php
                elseif ('import_action' === $args['type']) :
                    ?>
                    <h2 class="title"><?php echo esc_html__('Import Editor Settings', 'publishpress-authors'); ?></h2>
                    <p class="description"><?php echo esc_html__('Paste the editor data from the "Export" tab on another site.', 'publishpress-authors'); ?></p>
                    <textarea name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>" 
                        type="<?php echo esc_attr($args['type']); ?>"
                        rows="<?php echo esc_attr($args['rows']); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo (isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''; ?>
                        ></textarea>
                    <div>
                        <a data-invalid="<?php echo esc_attr__('Invalid data', 'publishpress-authors'); ?>" data-success="<?php echo esc_attr__('Settings Imported Successfully!', 'publishpress-authors'); ?>" href="#" class="button button-secondary ppma-editor-data-import">
                            <?php echo esc_html__('Import Data', 'publishpress-authors'); ?>
                        </a>
                    </div>
                    <div class="ppma-editor-data-imported">
                        <?php echo esc_attr__('Settings Imported Successfully!', 'publishpress-authors'); ?>
                    </div>
                <?php
                elseif ('template_action' === $args['type']) :
                    ?>
                    <h2 class="title"><?php echo esc_html__('How to generate and use a theme template file', 'publishpress-authors'); ?></h2>
                    <div class="input-area-text">
                        <ul class="template-generator-instruction">
                            <li><?php echo esc_html__('Click the "Generate Template" button under the text area. Wait for the code to be generated.', 'publishpress-authors'); ?></li>
                            <li><?php echo sprintf(esc_html__('Create an empty php template file with your desired file slug in the %1s /publishpress-authors/author-boxes/ %2s folder of your theme. %3s For example, the file can be located here: %4s /wp-content/themes/%5syour-theme-name%6s/publishpress-authors/author-boxes/my-first-custom-author-template.php %7s .', 'publishpress-authors'), '<font color="red">', '</font>', '<br />', '<font color="red">', '<strong>', '</strong>', '</font>'); ?></li>
                            <li><?php echo esc_html__('Copy the generated code and paste it inside the newly created file.', 'publishpress-authors'); ?></li>
                            <li><?php echo sprintf(esc_html__('Congratulations. Your can now choose your template inside the PublishPress Authors Settings.', 'publishpress-authors'), '<font color="red">', '</font>'); ?></li>
                        </ul>
                        <p><?php
                            printf(
                                esc_html__('You can read more information on the %s.'),
                                '<a href="https://publishpress.com/knowledge-base/author-boxes-theme-templates/">' . esc_html__(
                                    'documentation page',
                                    'publishpress-authors'
                                ) . '</a>'
                            ); ?>
                        </p>
                        <br />
                    </div>
                    <textarea name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>" 
                        type="<?php echo esc_attr($args['type']); ?>"
                        rows="<?php echo esc_attr($args['rows']); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo (isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''; ?>
                        ></textarea>
                    <div class="generate-template-buttons">
                        <span>
                            <a href="#" class="button button-primary ppma-editor-generate-template">
                                <?php echo esc_html__('Generate Template', 'publishpress-authors'); ?>
                            </a>
                            <span class="author-editor-loading-spinner spinner"></span>
                        </span>
                        <span>
                            <a href="#" data-target_input="<?php echo esc_attr($key); ?>" class="button button-secondary ppma-editor-copy-clipboard">
                                <?php echo esc_html__('Copy to Clipboard', 'publishpress-authors'); ?>
                            </a>
                        </span>
                    </div>
                    <div class="ppma-editor-copied-to-clipboard">
                        <?php echo esc_attr__('Copied to Clipboard!', 'publishpress-authors'); ?>
                    </div>
                    <div class="ppma-editor-template-generated">
                        <?php echo esc_attr__('Template generated successfuly!', 'publishpress-authors'); ?>
                    </div>
                <?php
                elseif ('textarea' === $args['type']) :
                    ?>
                    <textarea name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>" 
                        type="<?php echo esc_attr($args['type']); ?>"
                        rows="<?php echo esc_attr($args['rows']); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo (isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''; ?>
                        ><?php echo esc_html($args['value']); ?></textarea>
                <?php
                elseif ('profile_header' === $args['type']) :
                    $additional_class = 'closed';
                    //$additional_class .= (int)$args['index'] === 1 ? 'opened' : 'closed';
                    $additional_class .= ' profile-header-' .$args['tab_name'];
                    ?>
                    <?php if ((int)$args['index'] === 1) : ?>
                        <div class="ppma-editor-field-reorder-btn">
                            <span class="dashicons dashicons-admin-generic"></span> 
                            <?php esc_html_e('Reorder Fields', 'publishpress-authors'); ?>
                        </div>
                        <?php 

                        $profile_fields   = self::get_profile_fields($args['post_id']);
                        $modal_content = '';
                        $modal_content .= '<div class="ppma-editor-order-form">';
                        $modal_content .= '<p class="description">';
                        $modal_content .= __('Reorder the fields by dragging them to the correct position and saving your changes.', 'publishpress-authors');
                        $modal_content .= '</p>';
                        $modal_content .= '<div class="ppma-re-order-lists">';
                        foreach ($profile_fields as $key => $data) {
                            if (!in_array($key, MA_Author_Boxes::AUTHOR_BOXES_EXCLUDED_FIELDS)) {
                                $modal_content .= '<div class="field-sort-item"><h2>';
                                $modal_content .= $data['label'];
                                $modal_content .= '<input type="hidden" class="sort-field-names" value="'. esc_attr($key) .'">';
                                $modal_content .= '</h2></div>';
                            }
                        }
                        $modal_content .= '</div>';
                        $modal_content .= '<div class="submit-wrapper">';
                        $modal_content .= '<button class="button button-primary update-order" data-save="current">';
                        $modal_content .= __('Save for Current Author Box', 'publishpress-authors');
                        $modal_content .= '<div class="spinner"></div>';
                        $modal_content .= '</button>';
                        $modal_content .= '<button class="button button-secondary update-order" data-save="all">';
                        $modal_content .= __('Save for All Author Boxes', 'publishpress-authors');
                        $modal_content .= '<div class="spinner"></div>';
                        $modal_content .= '</button>';
                        $modal_content .= '</div>';
                        $modal_content .= '<div class="ppma-order-response-message"></div>';
                        $modal_content .= '</div>';
                        Utils::loadThickBoxModal('ppma-field-reorder-thickbox-btn', 'initial', 'initial', $modal_content);
                        ?>
                    <?php endif; ?>
                <div class="ppma-editor-profile-header-title <?php echo esc_attr($additional_class); ?>"
                    data-fields_name="<?php echo esc_attr($args['tab_name']); ?>">
                    <h2 class="title-text">
                        <?php echo esc_html($args['label']); ?>
                    </h2>
                    <div class="title-toggle">
                        <button type="button">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <?php else : ?>
                    <input name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>" 
                        type="<?php echo esc_attr($args['type']); ?>"
                        value="<?php echo esc_attr($args['value']); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo (isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''; ?>
                         />
                <?php endif; ?>
                <?php if (isset($args['description']) && !empty($args['description'])) : ?>
                        <?php if($args['type'] !== 'checkbox') : ?>
                            <br />
                        <?php endif; ?>
                        <span class="field-description description">
                            <?php echo $args['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        </span>
                <?php endif; ?>
            </td>
        </tr> 
        <?php
        return ob_get_clean();
    }

    /**
     * Enqueue Admin Scripts
     *
     * @return void
     */
    public function enqueueAdminScripts()
    {
        global $pagenow, $post_type, $post;

        if (! in_array($pagenow, ['post.php', 'post-new.php'])
            || $post_type !== self::POST_TYPE_BOXES
        ) {
            return;
        }
        $author          = Author::get_by_user_id(get_current_user_id());
        $moduleAssetsUrl = PP_AUTHORS_URL . 'src/modules/author-boxes/assets';

        //color picker style
        wp_enqueue_style('wp-color-picker');

        wp_enqueue_script(
            'author-boxes-editor-js',
            $moduleAssetsUrl . '/js/author-boxes-editor.js',
            [
                'jquery',
                'wp-color-picker',
                'jquery-ui-sortable'
            ],
            PP_AUTHORS_VERSION
        );

        $localized_data = [
            'post_id'   => $post->ID,
            'author_term_id'   => $author->term_id,
            'nonce'     => wp_create_nonce('author-boxes-request-nonce')
        ];
        $profile_fields   = self::get_profile_fields($post->ID);
        $profile_fields_keys = [];
        foreach ($profile_fields as $key => $data) {
            if (!in_array($key, self::AUTHOR_BOXES_EXCLUDED_FIELDS)) {
                $profile_fields_keys[]  = $key;
                $localized_data[$key] = $author->$key;
            }
        }
        $localized_data['profileFields'] = wp_json_encode($profile_fields_keys);

        wp_localize_script(
            'author-boxes-editor-js',
            'authorBoxesEditor',
            $localized_data
        );

        wp_enqueue_style(
            'author-boxes-editor-css',
            $moduleAssetsUrl . '/css/author-boxes-editor.css',
            [],
            PP_AUTHORS_VERSION
        );
    }

    /**
     * Get author box profile fields sorted by box author field order
     *
     * @param mixed $author_box
     * @param mixed $author
     * 
     * @return array
     */
    public static function get_profile_fields($author_box = false, $author = false) {
        
        $profile_fields   = Author_Editor::get_fields($author);
        $profile_fields   = apply_filters('multiple_authors_author_fields', $profile_fields, false);

        if ($author_box && (int)$author_box > 0) {
            $author_fields_order = get_post_meta($author_box, self::META_PREFIX . 'author_fields_order', true);
            $profile_fields_keys  = array_keys($profile_fields);
            if (!empty($author_fields_order) && is_array($author_fields_order)) {
                $possible_new_fields  = array_diff($profile_fields_keys, $author_fields_order);
                $current_field_sort = array_merge($possible_new_fields, $author_fields_order);
                $ordered_fields = [];
                foreach ($current_field_sort as $field_key) {
                    if (isset($profile_fields[$field_key])) {
                        $ordered_fields[$field_key] = $profile_fields[$field_key];
                    }
                }
                $profile_fields = $ordered_fields;
            }
        }

        return $profile_fields;
    }
}
