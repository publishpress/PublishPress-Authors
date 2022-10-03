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
use MultipleAuthorBoxes\AuthorBoxesStyles;
use MultipleAuthorBoxes\AuthorBoxesAjax;
use MultipleAuthors\Classes\Legacy\Util;
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
        add_action('add_meta_boxes', [$this, 'addEditorMetabox']);
        add_action('add_meta_boxes', [$this, 'addLayoutSlugMetabox']);
        add_action('add_meta_boxes', [$this, 'addShortcodeMetabox']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getTitleFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getAvatarFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getNameFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getMetaFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getBioFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getRecentPostsFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getBoxLayoutFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getCustomCssFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getExportFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getImportFields'], 10, 2);
        add_filter('author_boxes_editor_fields', ['MultipleAuthorBoxes\AuthorBoxesEditorFields', 'getGenerateTemplateFields'], 10, 2);
        add_action("save_post_" . MA_Author_Boxes::POST_TYPE_BOXES, [$this, 'saveAuthorBoxesData']);
        add_filter('pp_multiple_authors_author_layouts', [$this, 'filterAuthorLayouts'], 20);
        add_filter('pp_multiple_authors_author_box_html', [$this, 'filterAuthorBoxHtml'], 9, 2);
        add_filter('pp_multiple_authors_authors_list_box_html', [$this, 'filterAuthorBoxHtml'], 9, 2);

        add_action(
            'wp_ajax_author_boxes_editor_get_preview', 
            [
                'MultipleAuthorBoxes\AuthorBoxesAjax', 
                'handle_author_boxes_editor_get_preview'
            ]
        );
        add_action(
            'wp_ajax_author_boxes_editor_get_template', 
            [
                'MultipleAuthorBoxes\AuthorBoxesAjax', 
                'handle_author_boxes_editor_get_template'
            ]
        );

        $this->registerPostType();
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
        foreach ($fields as $key => $args) {
            if (!isset($_POST[$key]) || in_array($key, $excluded_input)) {
                continue;
            }
            $sanitize = isset($args['sanitize']) ? $args['sanitize'] : 'sanitize_text_field';
            $meta_data[$key] = (isset($_POST[$key]) && $_POST[$key] !== '') ? $sanitize($_POST[$key]) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        }
        update_post_meta($post_id, self::META_PREFIX . 'layout_meta_value', $meta_data);
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
     * Create the layout based on a twig file with the same name.
     *
     * @param string $name
     * @param string $title
     */
    protected static function createLayoutPost($name, $title)
    {
        // Check if we already have the layout based on the slug.
        if (get_page_by_title($title, OBJECT, MA_Author_Boxes::POST_TYPE_BOXES)) {
            return;
        }

        $editor_data = AuthorBoxesDefault::getAuthorBoxesDefaultData($name);
        if ($editor_data && is_array($editor_data)) {
            $post_id = wp_insert_post(
                [
                    'post_type' => MA_Author_Boxes::POST_TYPE_BOXES,
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
            apply_filters('pp_multiple_authors_manage_author_boxes_cap', 'manage_options'),
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
                    '%1$s %3$s updated, somebody is editing them.',
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
     * @param $layouts
     *
     * @return array
     */
    public function filterAuthorLayouts($layouts)
    {
        //add boxes layout
        $layouts = array_merge($layouts, self::getAuthorBoxes());
        //add theme layouts
        $layouts = array_merge($layouts, self::getThemeAuthorBoxes());

        return $layouts;
    }

    /**
     * @return array
     */
    public static function getAuthorBoxes()
    {
        $posts = get_posts(
            [
                'post_type' => self::POST_TYPE_BOXES,
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ]
        );

        $author_boxes = [];

        if (! empty($posts)) {
            foreach ($posts as $post) {
                $author_boxes[MA_Author_Boxes::POST_TYPE_BOXES . '_' . $post->ID] = $post->post_title . ' [' . __('Author Boxes', 'publishpress-authors') . ']';
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
                        $theme_author_boxes[$filename] = self::cleanThemeBoxName($filename) . ' [' . __('Theme Boxes', 'publishpress-authors') . ']';
                    }
                }
            } 
        }

        return $theme_author_boxes;
    }

    /**
     * This is a clone of wordpress 'list_files' that's been caught in undefined function.
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

        if (function_exists('list_files')) {
            return list_files($folder, $levels);
        } else {
            if ( empty( $folder ) ) {
                return false;
            }
        
            $folder = trailingslashit( $folder );
        
            if ( ! $levels ) {
                return false;
            }
        
            $files = array();
        
            $dir = @opendir( $folder );
        
            if ( $dir ) {
                while ( ( $file = readdir( $dir ) ) !== false ) {
                    // Skip current and parent folder links.
                    if ( in_array( $file, array( '.', '..' ), true ) ) {
                        continue;
                    }
        
                    // Skip hidden and excluded files.
                    if ( '.' === $file[0] || in_array( $file, $exclusions, true ) ) {
                        continue;
                    }
        
                    if ( is_dir( $folder . $file ) ) {
                        $files2 = list_files( $folder . $file, $levels - 1 );
                        if ( $files2 ) {
                            $files = array_merge( $files, $files2 );
                        } else {
                            $files[] = $folder . $file . '/';
                        }
                    } else {
                        $files[] = $folder . $file;
                    }
                }
        
                closedir( $dir );
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
        if (substr($layoutName, 0, 10) === MA_Author_Boxes::POST_TYPE_BOXES) {
            $author_box_id = preg_replace("/[^0-9]/", "", $layoutName );
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

        $editor_data = get_post_meta($author_box_id, self::META_PREFIX . 'layout_meta_value', true);

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

        $post_id = (isset($args['post']) && isset($args['post']->ID)) ? $args['post']->ID : 0;
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
     * Add editor metabox
     *
     * @return void
     */
    public function addEditorMetabox()
    {
        add_meta_box(
            self::META_PREFIX . 'editor_area',
            __('Author Box Editor', 'publishpress-authors'),
            [$this, 'renderMetaboxes'],
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
            'normal',
            'high'
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
            'normal',
            'high'
        );
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
                'label' => __('Name', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-editor-spellcheck',
            ],
            'author_bio'  => [
                'label' => __('Author Bio', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-welcome-write-blog',
            ],
            'meta'  => [
                'label' => __('Meta', 'publishpress-authors'),
                'icon'  => 'dashicons dashicons-forms',
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
        $layout_slug = MA_Author_Boxes::POST_TYPE_BOXES . '_' . $post->ID;
    ?>
        <input type="text" value="<?php echo esc_attr($layout_slug); ?>" readonly />
    <?php
    }

    /**
     * Render shortcode metaboxes
     *
     * @param \WP_Post $post
     * @return void
     */
    public function renderShortcodeMetabox(\WP_Post $post)
    { 
        $layout_slug = MA_Author_Boxes::POST_TYPE_BOXES . '_' . $post->ID;
    ?>
        <input type="text" value='[publishpress_authors_box layout="<?php echo esc_attr($layout_slug); ?>"]'' readonly />
        <p class="description"><?php esc_html_e('Shortcode will only render for saved author box.', 'publishpress-authors'); ?></p>
    <?php
    }

    /**
     * Render metaboxes
     *
     * @param \WP_Post $post
     * 
     * @return void
     */
    public function renderMetaboxes(\WP_Post $post)
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
                <?php 
                if ($post->post_status === 'auto-draft'
                    || empty(get_post_meta($post->ID, self::META_PREFIX . 'layout_meta_value', true))
                ) {
                    $editor_data = AuthorBoxesDefault::getAuthorBoxesDefaultData('author_boxes_boxed');
                } else {
                    $editor_data = (array) get_post_meta($post->ID, self::META_PREFIX . 'layout_meta_value', true);
                }

                /**
                 * Render fields
                 */
                $preview_args = [];
                //set current user as author
                $preview_args['authors'] = [Author::get_by_user_id(get_current_user_id())];
                $preview_args['post_id'] = $post->ID;
                $preview_args['admin_preview'] = true;
                foreach ($fields as $key => $args) {
                    $args['key']   = $key;
                    $args['value'] = isset($editor_data[$key]) ? $editor_data[$key] : '';
                    $preview_args[$key] = $args;
                    echo self::get_rendered_author_boxes_editor_partial($args); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }

                wp_nonce_field('author-boxes-editor', 'author-boxes-editor-nonce');
                ?>
            </div>

            <div class="preview-section wrapper-column">
                <?php 
                /**
                 * Render editor preview
                 */
                echo self::get_rendered_author_boxes_editor_preview($preview_args);
                ?>
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
        ob_start();

        //custom styles
        $custom_styles = '';
        $custom_styles = AuthorBoxesStyles::getTitleFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getAvatarFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getNameFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getBioFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getMetaFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getRecentPostsFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getBoxLayoutFieldStyles($args, $custom_styles);
        $custom_styles = AuthorBoxesStyles::getCustomCssFieldStyles($args, $custom_styles);

        $admin_preview = (isset($args['admin_preview']) && $args['admin_preview']) ? true : false;
        ?>

        <?php if ($admin_preview) : ?>
        <div class="preview-container">
            <div class="live-preview">
                <div class="live-preview-label">
                    <?php echo esc_html__('Previewing as current user', 'publishpress-authors'); ?>
                </div>
                <div class="live-preview-box">
        <?php endif; ?>
                    <!--begin code -->
                    <div class="pp-multiple-authors-boxes-wrapper pp-multiple-authors-wrapper <?php echo esc_attr($args['box_tab_custom_wrapper_class']['value']); ?> box-post-id-<?php echo esc_attr($args['post_id']); ?>"
                    data-original_class="pp-multiple-authors-boxes-wrapper pp-multiple-authors-wrapper box-post-id-<?php echo esc_attr($args['post_id']); ?>">
                        <?php if ($args['show_title']['value']) : ?>
                            <<?php echo esc_html($args['title_html_tag']['value']); ?> class="widget-title box-header-title"><?php echo esc_html($args['title_text']['value']); ?></<?php echo esc_html($args['title_html_tag']['value']); ?>>
                        <?php endif; ?>
                        <ul class="pp-multiple-authors-boxes-ul">
                            <?php if (isset($args['authors']) && is_array($args['authors']) && !empty($args['authors'])) : ?>
                                <?php foreach ($args['authors'] as $index => $author) : ?>
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
                                        ?>
                                        <li class="pp-multiple-authors-boxes-li author_index_<?php echo esc_attr($index); ?> author_<?php echo esc_attr($author->slug); ?> <?php echo ($args['avatar_show']['value']) ? 'has-avatar' : 'no-avatar'; ?>">

                                            <?php if ($args['avatar_show']['value']) : ?>
                                                <div class="pp-author-boxes-avatar">
                                                    <?php if ($author->get_avatar) : ?>
                                                        <?php echo $author->get_avatar($args['avatar_size']['value']); ?>
                                                    <?php else : ?>
                                                        <?php echo get_avatar($author->user_email, $args['avatar_size']['value']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else : 
                                                $custom_styles .= '.pp-multiple-authors-layout-boxed ul li > div:nth-child(1) {flex: 1 !important;}';
                                            ?>
                                            <?php endif; ?>

                                            <div class="pp-author-boxes-avatar-details">
                                                <<?php echo esc_html($args['name_html_tag']['value']); ?> class="pp-author-boxes-name multiple-authors-name">
                                                    <a href="<?php echo esc_url($author->link); ?>" rel="author" title="<?php echo esc_attr($author->display_name); ?>" class="author url fn">
                                                        <?php echo esc_html($author->display_name); ?>
                                                    </a>
                                                </<?php echo esc_html($args['name_html_tag']['value']); ?>>
                                                <?php if ($args['author_bio_show']['value']) : ?>
                                                    <<?php echo esc_html($args['author_bio_html_tag']['value']); ?> class="pp-author-boxes-description multiple-authors-description">
                                                        <?php echo $author->get_description($args['author_bio_limit']['value']); ?>
                                                    </<?php echo esc_html($args['author_bio_html_tag']['value']); ?>>
                                                <?php endif; ?>

                                                <?php if ($args['meta_show']['value']) : ?>
                                                    <<?php echo esc_html($args['meta_html_tag']['value']); ?> class="pp-author-boxes-meta multiple-authors-links">
                                                        <?php if ($args['meta_view_all_show']['value']) : ?>
                                                            <a href="<?php echo esc_url($author->link); ?>" title="<?php echo esc_attr__('View all posts', 'publishpress-authors'); ?>">
                                                                <span><?php echo esc_html__('View all posts', 'publishpress-authors'); ?></span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($args['meta_email_show']['value'] && $author->user_email) : ?>
                                                            <a href="<?php echo esc_url('mailto:'.$author->user_email); ?>" target="_blank">
                                                                <span class="dashicons dashicons-email-alt"></span>
                                                            </a>
                                                        <?php endif; ?>
                                                        <?php if ($args['meta_site_link_show']['value'] && $author->user_url) : ?>
                                                            <a href="<?php echo esc_url($author->user_url); ?>" target="_blank">
                                                                <span class="dashicons dashicons-admin-links"></span>
                                                            </a>
                                                        <?php endif; ?>
                                                    </<?php echo esc_html($args['meta_html_tag']['value']); ?>>
                                                <?php endif; ?>

                                                <?php if ($args['author_recent_posts_show']['value']) : ?>
                                                    <div class="pp-author-boxes-recent-posts">
                                                        <?php if ($args['author_recent_posts_title_show']['value'] && (!empty($author_recent_posts) || $args['author_recent_posts_empty_show']['value'])) : ?>
                                                            <div class="pp-author-boxes-recent-posts-title">
                                                                <?php echo esc_html__('Recent Posts', 'publishpress-authors'); ?>
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
                                            </div>
                                            
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <!--end code -->
                    <?php if ($admin_preview) : ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($admin_preview || is_admin()) : ?>
            <?php wp_enqueue_style(
                'multiple-authors-widget-css',
                PP_AUTHORS_ASSETS_URL . 'css/multiple-authors-widget.css',
                false,
                PP_AUTHORS_VERSION,
                'all'
            ); ?>
            <div class="pp-author-boxes-editor-preview-styles">
                <style>
                    <?php echo $custom_styles; ?>
                </style>
            </div>
        <?php else : ?>
            <?php wp_add_inline_style(
                'multiple-authors-widget-css',
                $custom_styles
            );
            ?>
        <?php endif; ?>
        
        <?php
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
            'show_input'  => false,
        ];
        $args      = array_merge($defaults, $args);
        $key       = $args['key'];
        $tab_class = 'ppma-boxes-editor-tab-content ppma-' . $args['tab'] . '-tab ' . $args['type'] . ' ppma-editor-'.$key;
        if ('range' === $args['type'] && $args['show_input']) {
            $tab_class .= ' double-input';
        }
        $tab_style = ($args['tab'] === self::AUTHOR_BOXES_EDITOR_DEFAULT_TAB) ? '' : 'display:none;';
        ob_start();
        $generate_tab_title = false;
        ?>
        <div 
            class="<?php echo esc_attr($tab_class); ?>"
            data-tab="<?php echo esc_attr($args['tab']); ?>"
            style="<?php echo esc_attr($tab_style); ?>">
            <?php if (!empty($args['label'])) : ?>
                <div class="label">
                    <label for="<?php echo esc_attr($key); ?>">
                    <?php echo esc_html($args['label']); ?>
                    <?php if (isset($args['description']) && !empty($args['description'])) : ?>
                        <span class="description pp-editor-tooltip">
                            <span class="dashicons dashicons-info"></span>
                            <span class="text"><?php echo $args['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        </span>
                    <?php endif; ?>
                    </label>
                </div>
            <?php endif; ?>
            <div class="input">
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
                elseif ('range' === $args['type']) :
                    ?>
                    <input name="<?php echo esc_attr($key); ?>-slider"
                        class="pp-editor-range"
                        id="<?php echo esc_attr($key); ?>" 
                        type="<?php echo esc_attr($args['type']); ?>"
                        value="<?php echo esc_attr($args['value']); ?>"
                        min="<?php echo esc_attr($args['min']); ?>"
                        max="<?php echo esc_attr($args['max']); ?>" />
                    <?php if ($args['show_input']) : ?>
                        <input name="<?php echo esc_attr($key); ?>"
                        class="pp-editor-range-input <?php echo esc_attr($key); ?>-input small-text" 
                        id="<?php echo esc_attr($key); ?>-input" 
                        type="number"
                        value="<?php echo esc_attr($args['value']); ?>"
                        min="<?php echo esc_attr($args['min']); ?>"
                        max="<?php echo esc_attr($args['max']); ?>" />
                    <?php endif; ?>
                <?php
                elseif ('color' === $args['type']) :
                    ?>
                    <input name="<?php echo esc_attr($key); ?>"
                        class="pp-editor-color-picker"
                        id="<?php echo esc_attr($key); ?>" 
                        type="text"
                        value="<?php echo esc_attr($args['value']); ?>"
                        style="display:none;" />
                    <div class="<?php echo esc_attr($key); ?>"></div>
                <?php
                elseif ('export_action' === $args['type']) :
                    ?>
                    <h2 class="title"><?php echo esc_html__('Export Editor Settings', 'publishpress-authors'); ?></h2>
                    <p class="description"><?php echo sprintf(esc_html__('You can import the below data using author box %1s editor import tab to copy this editor design to a %2s new editor or another website with the plugin active.', 'publishpress-authors'), '<br />', '<br />'); ?></p>
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
                    <p class="description"><?php echo esc_html__('Paste a valid editor data below to import it design.', 'publishpress-authors'); ?></p>
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
                    <h2 class="title"><?php echo esc_html__('How to generate and use template', 'publishpress-authors'); ?></h2>
                    <div class="input-area-text">
                        <ul class="template-generator-instruction">
                            <li><?php echo esc_html__('Click "Generate Template" button under the textarea and wait for the code to be generated.', 'publishpress-authors'); ?></li>
                            <li><?php echo sprintf(esc_html__('Create an empty php template file with your desired file slug in %1s /publishpress-authors/author-boxes/ %2s folder of your theme. For example %3s /wp-content/themes/%4syour-theme-name%5s/publishpress-authors/author-boxes/my-first-custom-author-template.php %6s .', 'publishpress-authors'), '<font color="red">', '</font>', '<font color="red">', '<strong>', '</strong>', '</font>'); ?></li>
                            <li><?php echo esc_html__('Copy the generated code and paste inside the newly created file. You can add as many templates as you want for different design.', 'publishpress-authors'); ?></li>
                            <li><?php echo sprintf(esc_html__('All template inside %1s /publishpress-authors/author-boxes/ %2s folder of your theme will be available for selection in settings layouts and the template slug can be use as layout parameter in shortcode.', 'publishpress-authors'), '<font color="red">', '</font>'); ?></li>
                            <li><?php echo esc_html__('Note, the template is independent of this editor layout settings and can be use across different themes and sites.', 'publishpress-authors'); ?></li>
                        </ul>
                        <p><?php
                            printf(
                                esc_html__('You can read more information on the %s.'),
                                '<a href="#">' . esc_html__(
                                    'documentation page',
                                    'publishpress-authors'
                                ) . '</a>'
                            ); ?>
                        </p>
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
                <?php else : ?>
                    <input name="<?php echo esc_attr($key); ?>"
                        id="<?php echo esc_attr($key); ?>" 
                        type="<?php echo esc_attr($args['type']); ?>"
                        value="<?php echo esc_attr($args['value']); ?>"
                        placeholder="<?php echo esc_attr($args['placeholder']); ?>"
                        <?php echo (isset($args['readonly']) && $args['readonly'] === true) ? 'readonly' : ''; ?>
                         />
                <?php endif; ?>
            </div>
        </div> 
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
            || $post_type !== MA_Author_Boxes::POST_TYPE_BOXES
        ) {
            return;
        }
        $author          = Author::get_by_user_id(get_current_user_id());
        $moduleAssetsUrl = PP_AUTHORS_URL . 'src/modules/author-boxes/assets';

        wp_enqueue_script(
            'author-boxes-pickr-js',
            $moduleAssetsUrl . '/lib/pickr/js/pickr.es5.min.js',
            [],
            PP_AUTHORS_VERSION
        );

        wp_enqueue_script(
            'author-boxes-editor-js',
            $moduleAssetsUrl . '/js/author-boxes-editor.js',
            [
                'jquery',
            ],
            PP_AUTHORS_VERSION
        );

        $localized_data = [
            'btnSave'   => esc_html__('Apply', 'publishpress-authors'),
            'btnCancel' => esc_html__('Cancel', 'publishpress-authors'),
            'btnClear'  => esc_html__('Clear', 'publishpress-authors'),
            'post_id'   => $post->ID,
            'nonce'     => wp_create_nonce('author-boxes-request-nonce')
        ];
        if ($author && is_object($author) && isset($author->term_id)) {
            $localized_data['author_slug']         = $author->slug;
            $localized_data['author_user_email']   = $author->user_email;
            $localized_data['author_link']         = $author->link;
            $localized_data['author_display_name'] = $author->display_name;
            $localized_data['author_term_id']      = $author->term_id;
        } else {
            $localized_data['author_slug']         = '';
            $localized_data['author_user_email']   = '';
            $localized_data['author_link']         = '';
            $localized_data['author_display_name'] = '';
            $localized_data['author_term_id']      = '';
        }
        wp_localize_script(
            'author-boxes-editor-js',
            'authorBoxesEditor',
            $localized_data
        );

        wp_enqueue_style(
            'author-boxes-pickr-css',
             $moduleAssetsUrl . '/lib/pickr/css/nano.min.css',
            [],
            PP_AUTHORS_VERSION
        );

        wp_enqueue_style(
            'author-boxes-editor-css',
            $moduleAssetsUrl . '/css/author-boxes-editor.css',
            [],
            PP_AUTHORS_VERSION
        );
    }
}
