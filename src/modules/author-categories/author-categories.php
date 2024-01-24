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

use MultipleAuthors\Classes\Legacy\Module;
use MultipleAuthorCategories\AuthorCategoriesSchema;
use MultipleAuthorCategories\AuthorCategoriesTable;
use MultipleAuthors\Factory;

/**
 * class MA_Author_Categories
 */
class MA_Author_Categories extends Module
{

    /**
     * Instance of the module
     *
     * @var stdClass
     */
    public $module;
    public $module_url;
    public $author_categories_table;

    public $module_name = 'author_categories';

    const MENU_SLUG = 'ppma-author-categories';

    /**
     * Construct the MA_Multiple_Authors class
     */
    public function __construct()
    {

        global $hook_suffix;

        if (!isset($hook_suffix)) {
            $hook_suffix = '';
        }

        $this->module_url = $this->get_module_url(__FILE__);

        // Register the module with PublishPress
        $args = [
            'title' => __('Author Categories', 'publishpress-authors'),
            'short_description' => __(
                'Add support for author categories.',
                'publishpress-authors'
            ),
            'extended_description' => __(
                'Add support for author categories.',
                'publishpress-authors'
            ),
            'module_url' => $this->module_url,
            'icon_class' => 'dashicons dashicons-edit',
            'slug' => 'author-categories',
            'default_options' => [
                'enabled' => 'on',
            ],
            'options_page' => false,
            'autoload' => true,
        ];

        // Apply a filter to the default options
        $args['default_options'] = apply_filters('MA_Author_Categories_default_options', $args['default_options']);

        $legacyPlugin = Factory::getLegacyPlugin();

        $this->module = $legacyPlugin->register_module($this->module_name, $args);

        parent::__construct();
    }

    /**
     * Initialize the module. Conditionally loads if the module is enabled
     */
    public function init()
    {
        add_action('pp_authors_install', [$this, 'runInstallTasks']);
        add_action('pp_authors_upgrade', [$this, 'runUpgradeTasks']);
        add_action('multiple_authors_admin_submenu', [$this, 'adminSubmenu'], 50);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        add_action('wp_ajax_save_ppma_author_category', [$this, 'handleNewCategory']);
        add_action('wp_ajax_edit_ppma_author_category', [$this, 'handleEditCategory']);
        add_action('wp_ajax_reorder_ppma_author_category', [$this, 'handleReOrderCategory']);
        add_filter('removable_query_args', [$this, 'removableQueryArgs']);
        add_action('delete_post', [$this, 'deleteAuthorCategoryRelation']);
    }

    /**
     * Enqueue Admin Scripts
     *
     * @return void
     */
    public function enqueueAdminScripts()
    {

        if (!isset($_GET['page']) || $_GET['page'] !== self::MENU_SLUG) {
            return;
        }

        $moduleAssetsUrl = PP_AUTHORS_URL . 'src/modules/author-categories/assets';

        // Load jquery and jquery ui for sortable
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');

        // Inline edit script
        wp_enqueue_script('author-categories-inline-edit', $moduleAssetsUrl . '/js/inline-edit.js', ['jquery'], PP_AUTHORS_VERSION);

        // Drag and drop re-order script
        wp_enqueue_script('author-category-reorder', $moduleAssetsUrl . '/js/category-reorder.js', ['jquery', 'jquery-ui-sortable'], PP_AUTHORS_VERSION);
        wp_localize_script(
            'author-category-reorder',
            'authorCategoriesReorder',
            [
                'nonce'     => wp_create_nonce('author-categories-reorder-nonce')
            ]
        );

        // Author category general script
        wp_enqueue_script('author-categories-js', $moduleAssetsUrl . '/js/author-categories.js', ['jquery'], PP_AUTHORS_VERSION);
        wp_localize_script(
            'author-categories-js',
            'authorCategories',
            [
                'nonce'     => wp_create_nonce('author-categories-save-nonce')
            ]
        );

        // Author category styles
        wp_enqueue_style(
            'author-categories-css',
            $moduleAssetsUrl . '/css/author-categories.css',
            [],
            PP_AUTHORS_VERSION
        );
    }

    public function removableQueryArgs($args) {

        if (!isset($_GET['page']) || $_GET['page'] !== self::MENU_SLUG) {
            return $args;
        }
        
        return array_merge(
            $args,
            [
                'action',
                'author_categories',
                '_wpnonce'
            ]
        );

    }

    /**
     * Add the admin submenu.
     */
    public function adminSubmenu()
    {

        // Add the submenu to the PublishPress menu.
        $hook = add_submenu_page(
            \MA_Multiple_Authors::MENU_SLUG,
            esc_html__('Author Categories', 'publishpress-authors'),
            esc_html__('Author Categories', 'publishpress-authors'),
            apply_filters('pp_multiple_authors_manage_categories_cap', 'ppma_manage_author_categories'),
            self::MENU_SLUG,
            [$this, 'manageAuthorCategories'],
            11
        );

        add_action("load-$hook", [$this, 'screenOption']);
    }

    /**
     * Screen options
     */
    public function screenOption()
    {

        $option = 'per_page';
        $args   = [
            'label'   => esc_html__('Number of items per page', 'publishpress-authors'),
            'default' => 20,
            'option'  => 'author_categories_per_page'
        ];

        add_screen_option($option, $args);

        $this->author_categories_table = new AuthorCategoriesTable();
    }

    /**
     * Handle a request to save author category
     *
     * @return mixed
     */
    public function handleNewCategory() {

        $response['status']     = 'error';
        $response['content']    = '';
        $response_message       = esc_html__('An error occured.', 'publishpress-authors');

        if (empty($_POST['nonce']) 
            || !wp_verify_nonce(sanitize_key($_POST['nonce']), 'author-categories-save-nonce')
        ) {
            $response_message = esc_html__(
                'Security error. Kindly reload this page and try again', 
                'publishpress-authors'
            );
        } elseif (!current_user_can(apply_filters('pp_multiple_authors_manage_categories_cap', 'ppma_manage_author_categories'))) {
            $response_message = esc_html__(
                'You do not have permission to perform this action', 
                'publishpress-authors'
            );
        } else {

            $category_name = isset($_POST['category_name']) ? sanitize_text_field($_POST['category_name']) : '';
            $plural_name = isset($_POST['plural_name']) ? sanitize_text_field($_POST['plural_name']) : '';
            $enabled_category = isset($_POST['enabled_category']) ? intval($_POST['enabled_category']) : 0;
            $slug = sanitize_title($category_name);

            if (empty($category_name)) {
                $response_message = esc_html__(
                    'Singular name is required.', 
                    'publishpress-authors'
                );
            } elseif (empty($plural_name)) {
                $response_message = esc_html__(
                    'Plural name is required.', 
                    'publishpress-authors'
                );
            } elseif(!empty(self::get_author_categories(['slug' => $slug]))) {
                $response_message = esc_html__(
                    'Author category with this name already exist.', 
                    'publishpress-authors'
                );
            } else {
                $category_args = [
                    'category_name'     => $category_name,
                    'plural_name'       => $plural_name,
                    'slug'              => $slug,
                    'category_order'    => 0,
                    'category_status'   => $enabled_category,
                    'created_at'        => current_time('mysql', true)
                ];
                $added_category = $this->addAuthorCategory($category_args);
                if ($added_category && !empty($added_category)) {
                    $response_message = esc_html__(
                        'Category added.', 
                        'publishpress-authors'
                    );
                    $response['status']     = 'success';
                            
                    ob_start();
                    
                    $ajax_author_categories_table = new AuthorCategoriesTable();
                    $ajax_author_categories_table->single_row($added_category);
                    $response['content'] = ob_get_clean();

                } else {
                    $response_message = esc_html__(
                        'Error inserting new author category.', 
                        'publishpress-authors'
                    );
                }

            }

        }

        $response['message']    = '<div class="notice notice-'. $response['status'] .' is-dismissible"><p>'. $response_message .'</p></div>';
        wp_send_json($response);
    }

    /**
     * Handle a request to save author category.
     * Copied from WordPress wp_ajax_inline_save_tax
     * to match the Quick Edit
     *
     * @return mixed
     */
    public function handleEditCategory() {

        if (empty($_POST['nonce']) 
            || !wp_verify_nonce(sanitize_key($_POST['nonce']), 'ppma-category-inline-edit-nonce')
        ) {
            wp_die(esc_html__(
                'Security error. Kindly reload this page and try again', 
                'publishpress-authors'
            ));
        } elseif (!current_user_can(apply_filters('pp_multiple_authors_manage_categories_cap', 'ppma_manage_author_categories'))) {
            wp_die(esc_html__(
                'You do not have permission to perform this action', 
                'publishpress-authors'
            ));
        } else {

            $category_name = isset($_POST['singular_name']) ? sanitize_text_field($_POST['singular_name']) : '';
            $plural_name = isset($_POST['plural_name']) ? sanitize_text_field($_POST['plural_name']) : '';
            $category_status = isset($_POST['enabled_category']) ? intval($_POST['enabled_category']) : 0;
            $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
            $slug = sanitize_title($category_name);
            $existing_category = self::get_author_categories(['slug' => $slug]);
            if (empty($category_name) || empty($plural_name) || empty($category_id)) {
                wp_die(esc_html__(
                    'All fields are required.', 
                    'publishpress-authors'
                ));
            } elseif (!empty($existing_category) && (int)$existing_category['id'] !== (int)$category_id) {
                wp_die(esc_html__(
                    'Author category with this name already exist.', 
                    'publishpress-authors'
                ));
            } else {

                $category_args = [
                    'category_name'     => $category_name,
                    'plural_name'       => $plural_name,
                    'slug'              => $slug,
                    'category_status'   => $category_status
                ];
                $edited_category = $this->editAuthorCategory($category_args, $category_id);
                if ($edited_category && !empty($edited_category)) {
                    ob_start();
                    
                    $ajax_author_categories_table = new AuthorCategoriesTable();
                    $ajax_author_categories_table->single_row($edited_category);
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    echo ob_get_clean();
                    wp_die();
                } else {
                    wp_die(esc_html__(
                        'Error updating category data.', 
                        'publishpress-authors'
                    ));
                }
            }

        }
        wp_die(esc_html__('An error occured.', 'publishpress-authors'));
    }

    public function handleReOrderCategory() {


        $response['status']     = 'error';
        $response['content']    = '';
        $response_message       = esc_html__('An error occured.', 'publishpress-authors');
     
        if (empty($_POST['nonce']) 
            || !wp_verify_nonce(sanitize_key($_POST['nonce']), 'author-categories-reorder-nonce')
        ) {
            $response_message = esc_html__(
                'Security error. Kindly reload this page and try again.', 
                'publishpress-authors'
            );
        } elseif (empty($_POST['categories'])) {
            $response_message = esc_html__(
                'Invalid form data.', 
                'publishpress-authors'
            );
        } else {
            $categories = array_map('intval', $_POST['categories']);

            foreach($categories as $key => $category) {
                $this->editAuthorCategory(['category_order' => ($key + 1)], $category);
            }

            $response_message = esc_html__(
                'Category order updated.', 
                'publishpress-authors'
            );
            $response['status']     = 'success';
        }

        $response['message']    = '<div class="notice notice-'. $response['status'] .' is-dismissible"><p>'. $response_message .'</p></div>';
        wp_send_json($response);
    }

    /**
     * Add new author category
     *
     * @param array $insert_args
     * 
     * @return array|bool
     */
    private function addAuthorCategory($insert_args) {
        global $wpdb;

        $table_name     = AuthorCategoriesSchema::tableName();

        $wpdb->insert(
            $table_name,
            $insert_args
        );

        $category_id = $wpdb->insert_id;

        if ((int) $category_id > 0) {
            return self::get_author_categories(['id' => $category_id]);
        } else {
            return false;
        }
    }

    /**
     * Edit author category
     *
     * @param array $edit_args
     * @param integer $id
     * 
     * @return array|bool
     */
    private function editAuthorCategory($edit_args, $id) {
        global $wpdb;

        if (!current_user_can(apply_filters('pp_multiple_authors_manage_categories_cap', 'ppma_manage_author_categories'))) {
            return false;
        }

        $table_name     = AuthorCategoriesSchema::tableName();

        $wpdb->update(
            $table_name,
            $edit_args,
            [
                'id' => $id
            ]
        );

        return self::get_author_categories(['id' => $id]);
    }

    /**
     * Update post author category
     *
     * @param integer $post_id
     * @param array $authors
     * @param array $post_author_categories
     * 
     * @return void
     */
    public static function updatePostAuthorCategory($post_id, $authors, $post_author_categories) {
        global $wpdb;

        if (!current_user_can(get_taxonomy('author')->cap->assign_terms) || empty(array_filter(array_values($post_author_categories)))) {
            return;
        }


        $all_author_categories = \MA_Author_Categories::get_author_categories([]);
        $all_author_category_ids = array_column($all_author_categories, 'id');
        
        // Make 'id' the array index
        $all_author_categories = array_combine($all_author_category_ids, $all_author_categories);

        $table_name     = AuthorCategoriesSchema::relationTableName();

        // Make sure there's no relationship for authors that could have been possibly removed
        $wpdb->delete($table_name, ['post_id' => $post_id], ['%d']);

        if (!empty($authors)) {
            foreach ($authors as $author) {
                if (isset($post_author_categories[$author])) {
                    $category_id = $post_author_categories[$author];
                    $wpdb->insert(
                        $table_name,
                        [
                            'category_id'       => $all_author_categories[$category_id]['id'],
                            'category_slug'     => $all_author_categories[$category_id]['slug'],
                            'post_id'           => $post_id,
                            'author_term_id'    => $author,
                        ],
                        [
                            '%d',
                            '%s',
                            '%d',
                            '%d',
                        ]
                    );
                }
            }
        }
    }


    /**
     * Get author categories
     *
     * @param array $args
     * 
     * @return array|integer
     */
    public static function get_author_categories($args = []) {
        global $wpdb;

        $default_args = [
            'paged'             => 1,
            'limit'             => 20,
            'id'                => 0,
            'slug'              => '',
            'category_name'     => '',
            'plural_name'       => '',
            'search'            => '',
            'category_status'   => '',
            'orderby'           => 'category_order',
            'order'             => 'ASC',
            'count_only'        => false,
        ];

        $args = wp_parse_args($args, $default_args);

        $table_name     = AuthorCategoriesSchema::tableName();

        $paged           = intval($args['paged']);
        $limit           = intval($args['limit']);
        $id              = intval($args['id']);
        $slug            = sanitize_text_field($args['slug']);
        $category_name   = sanitize_text_field($args['category_name']);
        $plural_name     = sanitize_text_field($args['plural_name']);
        $orderby         = sanitize_text_field($args['orderby']);
        $search          = sanitize_text_field($args['search']);
        $order           = strtoupper(sanitize_text_field($args['order']));
        $category_status = sanitize_text_field($args['category_status']);
        $count_only      = boolval($args['count_only']);

        $field_search = $field_value = false;
        if (!empty($id)) {
            $field_search = 'id';
            $field_value  = $id;
        } elseif (!empty($slug)) {
            $field_search = 'slug';
            $field_value  = $slug;
        } elseif (!empty($category_name)) {
            $field_search = 'category_name';
            $field_value  = $category_name;
        } elseif (!empty($plural_name)) {
            $field_search = 'plural_name';
            $field_value  = $plural_name;
        }

        $cache_key = 'author_categories_results_' . md5(serialize($args));
    
        $category_results = wp_cache_get($cache_key, 'author_categories_results_cache');

        if ($category_results === false) {
            $category_results = [];
            if ($field_search) {
                $query = $wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE {$field_search} = %s ORDER BY {$orderby} {$order} LIMIT 1",
                    $field_value
                );
                $category_results = $wpdb->get_row($query, \ARRAY_A);
            } else {

                $offset = ($paged - 1) * $limit;

                $query = "SELECT * FROM {$table_name} WHERE 1=1";
                
                if (!empty($search)) {
                    $query .= $wpdb->prepare(
                        " AND (slug LIKE '%%%s%%' OR category_name LIKE '%%%s%%' OR plural_name LIKE '%%%s%%')",
                        $search,
                        $search,
                        $search
                    );
                }

                if ($category_status !== '') {
                    $query .= $wpdb->prepare(
                        " AND category_status = %d",
                        $category_status
                    );
                }

                if ($count_only) {
                    $query = str_replace("SELECT *", "SELECT COUNT(*)", $query);
                    return $wpdb->get_var($query);
                }

                $query .= $wpdb->prepare(
                    " ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d",
                    $limit,
                    $offset
                );
                
                $category_results = $wpdb->get_results($query, \ARRAY_A);
                wp_cache_set($cache_key, $category_results, 'author_categories_results_cache', 3600);
            }
        }

        return $category_results;
    }

    public static function get_author_relations($args = []) {
        global $wpdb;
    
        $default_args = [
            'post_id'  => '',
            'author_term_id' => ''
        ];
    
        $args = wp_parse_args($args, $default_args);
    
        $post_id        = intval($args['post_id']);
        $author_term_id = intval($args['author_term_id']);
    
        $cache_key = 'author_categories_relation_' . md5(serialize($args));
    
        $results = wp_cache_get($cache_key, 'author_categories_relation_cache');
    
        if ($results === false) {
            $table_name = AuthorCategoriesSchema::relationTableName();
    
            $sql = "SELECT * FROM $table_name WHERE 1=1";
    
            if ($post_id !== '') {
                $sql .= $wpdb->prepare(" AND post_id = %d", $post_id);
            }
    
            if (!empty($author_term_id)) {
                $sql .= $wpdb->prepare(" AND author_term_id = %d", $author_term_id);
            }
    
            $results = $wpdb->get_results($sql, ARRAY_A);
    
            wp_cache_set($cache_key, $results, 'author_categories_relation_cache', 3600);
        }
    
        return $results;
    }    

    /**
     * Get author category
     *
     * @param object $author
     * @param array $author_categories_data
     * 
     * @return array
     */
    public static function get_author_category($author, $author_categories_data) {
        
        $author_category = [];

        foreach ($author_categories_data as $author_category_data) {
            if (!empty($author_category_data['singular_title']) && !empty($author_category_data['authors'])) {
                $author_term_id = array_column($author_category_data['authors'], 'term_id');
                if (in_array($author->term_id, $author_term_id)) {
                    $author_category = $author_category_data;
                }
            }

        }

        return $author_category;
    }



    /**
     * Author categories callback
     *
     * @return void
     */
    public function manageAuthorCategories() {

        $this->author_categories_table->prepare_items();
        ?>

        <div class="wrap">

            <h1 class="wp-heading-inline"><?php esc_html_e('Author Categories', 'publishpress-authors'); ?></h1>
            <?php
                if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
                    echo '<span class="subtitle">';
                    printf(
                        /* translators: %s: Search query. */
                        esc_html__( 'Search results for: %s' ),
                        '<strong>' . esc_html( wp_unslash( $_REQUEST['s'] ) ) . '</strong>'
                    );
                    echo '</span>';
                }
            ?>
            <hr class="wp-header-end">
            <div id="ajax-response"></div>


            <form class="search-form wp-clearfix" method="get">
                <?php $this->author_categories_table->search_box(esc_html__('Search Author Categories', 'publishpress-authors'), 'author-categories'); ?>
            </form>

            <div id="col-container" class="wp-clearfix">
                <div id="col-left">
                    <div class="col-wrap">  
                        <div class="form-wrap">
                            <h2><?php esc_html_e('Add Author Category', 'publishpress-authors'); ?></h2>
                            <form id="addauthorcategory" method="post" action="#" class="validate">
                                <div class="form-field form-required category-name-wrap">
                                    <label for="category-name"><?php esc_html_e( 'Singular Name', 'publishpress-authors' ); ?></label>
                                    <input name="category-name" id="category-name" type="text" value="" size="40" required autocomplete="off" />
                                    <p id="category-name-description"><?php esc_html_e('Enter the Author Category name when it\'s a single author', 'publishpress-authors'); ?></p>
                                </div>
                                <div class="form-field form-required category-plural-name-wrap">
                                    <label for="category-plural-name"><?php esc_html_e( 'Plural Name', 'publishpress-authors' ); ?></label>
                                    <input name="category-plural-name" id="category-plural-name" type="text" value="" size="40" required autocomplete="off" />
                                    <p id="category-plural-description"><?php esc_html_e('Enter the Author Category name when there are more than 1 author', 'publishpress-authors'); ?></p>
                                </div>
                                <div class="form-field form-required category-enabled-category-wrap">
                                    <label for="category-enabled-category">
                                        <input name="category-enabled-category" id="category-enabled-category" type="checkbox" value="1" checked />
                                        <?php esc_html_e( 'Enable Category', 'publishpress-authors' ); ?>
                                    </label>
                                </div>
                            <p class="submit">
                                <?php submit_button( __('Add New Author Category', 'publishpress-authors'), 'primary', 'submit', false ); ?>
                                <span class="spinner"></span>
                            </p>
                            </form>
                        </div>
                    </div>
                </div><!-- /col-left -->

                <div id="col-right">
                    <div class="col-wrap">
                        <form action="<?php echo esc_url(add_query_arg('', '')); ?>" method="post">
                            <?php $this->author_categories_table->display(); ?>
                        </form>
                    </div>
                </div><!-- /col-right -->
            </div><!-- /col-container -->

        </div><!-- /wrap -->
        <?php $this->author_categories_table->inline_edit(); ?>
        <?php
    }

    /**
     * Insert default author Categories
     *
     * @return void
     */
    private function insertDefaultCategories() {

        $default_categories = [];

        $default_categories[] = [
            'category_name'     => esc_html__('Author', 'publishpress-authors'),
            'plural_name'       => esc_html__('Authors', 'publishpress-authors'),
            'slug'              => sanitize_title(esc_html__('Author', 'publishpress-authors')),
            'category_order'    => 1,
            'category_status'   => 1,
            'created_at'        => current_time('mysql', true)
        ];

        $default_categories[] = [
            'category_name'     => esc_html__('Coauthor', 'publishpress-authors'),
            'plural_name'       => esc_html__('Coauthors', 'publishpress-authors'),
            'slug'              => sanitize_title(esc_html__('Coauthor', 'publishpress-authors')),
            'category_order'    => 2,
            'category_status'   => 1,
            'created_at'        => current_time('mysql', true)
        ];

        $default_categories[] = [
            'category_name'     => esc_html__('Reviewer', 'publishpress-authors'),
            'plural_name'       => esc_html__('Reviewers', 'publishpress-authors'),
            'slug'              => sanitize_title(esc_html__('Reviewer', 'publishpress-authors')),
            'category_order'    => 3,
            'category_status'   => 0,
            'created_at'        => current_time('mysql', true)
        ];

        $default_categories[] = [
            'category_name'     => esc_html__('Editor', 'publishpress-authors'),
            'plural_name'       => esc_html__('Editors', 'publishpress-authors'),
            'slug'              => sanitize_title(esc_html__('Editor', 'publishpress-authors')),
            'category_order'    => 4,
            'category_status'   => 0,
            'created_at'        => current_time('mysql', true)
        ];
        
        foreach ($default_categories as $default_category) {
            $this->addAuthorCategory($default_category);
        }
    }

    /**
     * Delete author category relation for post id
     */
    public function deleteAuthorCategoryRelation($post_id) {
        global $wpdb;

        $table_name     = AuthorCategoriesSchema::relationTableName();
        
        $wpdb->delete($table_name, ['post_id' => $post_id], ['%d']);
    }

    /**
     * Runs methods when the plugin is running for the first time.
     *
     * @param string $currentVersion
     *
     * @param void
     */
    public function runInstallTasks($currentVersion) {

        // add new table
        AuthorCategoriesSchema::createTableIfNotExists();
        AuthorCategoriesSchema::createRelationTableIfNotExists();

        // add default author category
        $this->insertDefaultCategories();

        // add author category capability
        $capability_roles = ['administrator', 'editor', 'author', 'contributor'];
        foreach ($capability_roles as $capability_role) {
             $role = get_role($capability_role);
             if ($role instanceof \WP_Role) {
                 $role->add_cap('ppma_manage_author_categories');
             }
         }
         update_option('ppma_author_categories_installed', 1);
    }

    /**
     * Runs methods when the plugin is being upgraded to a most recent version.
     *
     * @param string $currentVersion
     *
     * @param void
     */
    public function runUpgradeTasks($currentVersion) {
        if (empty(get_option('ppma_author_categories_installed'))) {
            $this->runInstallTasks($currentVersion);
        }
    }
}
