<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

namespace MultipleAuthors;

use MA_Multiple_Authors;
use MultipleAuthors\Classes\Legacy\Util;
use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Query;
use MultipleAuthors\Classes\Utils;
use MultipleAuthors\Traits\Author_box;
use WP_Post;

defined('ABSPATH') or die('No direct script access allowed.');

class Plugin
{
    use Author_box;

    // Name for the taxonomy we're suing to store relationships
    // and the post type we're using to store co-authors
    public $coauthor_taxonomy = 'author';

    public $coreauthors_meta_box_name = 'authordiv';

    public $coauthors_meta_box_name = 'coauthorsdiv';

    public $gravatar_size = 25;

    public $ajax_search_fields = [
        'display_name',
        'first_name',
        'last_name',
        'user_login',
        'user_nicename',
        'ID',
        'user_email',
    ];

    public $having_terms = '';

    /**
     * __construct()
     */
    public function __construct()
    {
        // Register our models
        add_action('init', [$this, 'action_init']);
        add_action('init', [$this, 'action_init_late'], 100);

        // Installation hooks
        add_action(
            'multiple_authors_install',
            ['MultipleAuthors\\Classes\\Installer', 'install']
        );
        add_action(
            'multiple_authors_upgrade',
            ['MultipleAuthors\\Classes\\Installer', 'upgrade']
        );

        add_action('init', [$this, 'manage_installation'], 2000);

        // Load admin_init function
        add_action('admin_init', [$this, 'admin_init']);

        add_filter('get_usernumposts', [$this, 'filter_count_user_posts'], 10, 2);
        add_filter('get_authornumposts', [$this, 'filter_count_author_posts'], 10, 2);

        // Filter to allow coauthors to edit posts
        add_filter('user_has_cap', [$this, 'filter_user_has_cap'], 10, 3);

        // Restricts WordPress from blowing away term order on bulk edit
        add_filter('wp_get_object_terms', [$this, 'filter_wp_get_object_terms'], 10, 4);

        // Support for Edit Flow's calendar and story budget
        add_filter(
            'ef_calendar_item_information_fields',
            [$this, 'filter_ef_calendar_item_information_fields'],
            10,
            2
        );
        add_filter(
            'ef_story_budget_term_column_value',
            [$this, 'filter_ef_story_budget_term_column_value'],
            10,
            3
        );

        // Support Jetpack Open Graph Tags
        add_filter('jetpack_open_graph_tags', [$this, 'filter_jetpack_open_graph_tags'], 10, 2);

        // Filter to send comment moderation notification e-mail to multiple authors
        add_filter('comment_moderation_recipients', 'cap_filter_comment_moderation_email_recipients', 10, 2);

        // Delete CoAuthor Cache on Post Save & Post Delete
        add_action('save_post', [$this, 'clear_cache']);
        add_action('delete_post', [$this, 'clear_cache']);
        add_action('set_object_terms', [$this, 'clear_cache_on_terms_set'], 10, 6);

        // Widget support
        add_action('widgets_init', [$this, 'action_widget_init']);

        // Author box to the content
        add_filter('the_content', [$this, 'filter_the_content']);

        // Shortcodes
        add_shortcode('author_box', [$this, 'shortcode_author_box']);

        // Action to display the author box
        add_action('pp_multiple_authors_show_author_box', [$this, 'action_echo_author_box'], 10, 5);

        /*
         * @todo: Improve hooks to only add them if post type is selected or if it is an admin page.
         */

        // Fix the author page.
        // Use posts_selection since it's after WP_Query has built the request and before it's queried any posts
        add_filter('posts_selection', [$this, 'fix_query_for_author_page']);
        add_action('the_post', [$this, 'fix_post'], 10);

        add_action(
            'init',
            [
                'MultipleAuthors\\Classes\\Content_Model',
                'action_init_late_register_taxonomy_for_object_type',
            ],
            100
        );
        add_filter(
            'term_link',
            ['MultipleAuthors\\Classes\\Content_Model', 'filter_term_link'],
            10,
            3
        );
        add_filter(
            'author_link',
            ['MultipleAuthors\\Classes\\Content_Model', 'filter_author_link'],
            10,
            3
        );
        add_filter(
            'the_author_display_name',
            ['MultipleAuthors\\Classes\\Content_Model', 'filter_author_display_name'],
            10,
            2
        );
        add_filter(
            'update_term_metadata',
            ['MultipleAuthors\\Classes\\Content_Model', 'filter_update_term_metadata'],
            10,
            4
        );
        add_action(
            'parse_request',
            ['MultipleAuthors\\Classes\\Content_Model', 'action_parse_request']
        );

        // Admin customizations.
        add_action(
            'admin_init',
            ['MultipleAuthors\\Classes\\Post_Editor', 'action_admin_init']
        );
        add_action(
            'admin_init',
            ['MultipleAuthors\\Classes\\Term_Editor', 'action_admin_init']
        );
        add_filter(
            'manage_edit-author_columns',
            [
                'MultipleAuthors\\Classes\\Author_Editor',
                'filter_manage_edit_author_columns',
            ]
        );
        add_filter(
            'list_table_primary_column',
            [
                'MultipleAuthors\\Classes\\Author_Editor',
                'filter_list_table_primary_column',
            ]
        );
        add_filter(
            'manage_author_custom_column',
            [
                'MultipleAuthors\\Classes\\Author_Editor',
                'filter_manage_author_custom_column',
            ],
            10,
            3
        );
        add_filter(
            'user_row_actions',
            ['MultipleAuthors\\Classes\\Author_Editor', 'filter_user_row_actions'],
            10,
            2
        );
        add_filter(
            'author_row_actions',
            ['MultipleAuthors\\Classes\\Author_Editor', 'filter_author_row_actions'],
            10,
            2
        );
        add_action(
            'author_edit_form_fields',
            ['MultipleAuthors\\Classes\\Author_Editor', 'action_author_edit_form_fields']
        );
        add_action(
            'user_register',
            ['MultipleAuthors\\Classes\\Author_Editor', 'action_user_register'],
            20
        );
        add_action(
            'author_term_new_form_tag',
            ['MultipleAuthors\\Classes\\Author_Editor', 'action_new_form_tag'],
            10
        );
        add_filter(
            'wp_insert_term_data',
            ['MultipleAuthors\\Classes\\Author_Editor', 'filter_insert_term_data'],
            10,
            3
        );
        add_filter(
            'created_author',
            ['MultipleAuthors\\Classes\\Author_Editor', 'action_created_author'],
            10
        );
        add_action(
            'edited_author',
            ['MultipleAuthors\\Classes\\Author_Editor', 'action_edited_author']
        );

        add_filter(
            'bulk_actions-edit-author',
            ['MultipleAuthors\\Classes\\Author_Editor', 'filter_author_bulk_actions']
        );
        add_filter(
            'handle_bulk_actions-edit-author',
            ['MultipleAuthors\\Classes\\Author_Editor', 'handle_author_bulk_actions'],
            10,
            3
        );
        add_action(
            'admin_notices',
            ['MultipleAuthors\\Classes\\Author_Editor', 'admin_notices']
        );

        // Query modifications.
        add_action(
            'pre_get_posts',
            ['MultipleAuthors\\Classes\\Query', 'fix_query_pre_get_posts']
        );
        add_filter(
            'posts_where',
            ['MultipleAuthors\\Classes\\Query', 'filter_posts_where'],
            10,
            2
        );
        add_filter(
            'posts_join',
            ['MultipleAuthors\\Classes\\Query', 'filter_posts_join'],
            10,
            2
        );
        add_filter(
            'posts_groupby',
            ['MultipleAuthors\\Classes\\Query', 'filter_posts_groupby'],
            10,
            2
        );
        add_filter(
            'pre_handle_404',
            [$this, 'fix_404_for_authors'],
            10,
            2
        );
        add_action(
            'wp_head',
            ['MultipleAuthors\\Classes\\Query', 'fix_query_pre_get_posts'],
            1
        );

        // Author search
        add_action(
            'wp_ajax_authors_search',
            ['MultipleAuthors\\Classes\\Admin_Ajax', 'handle_authors_search']
        );
        add_action(
            'wp_ajax_authors_users_search',
            ['MultipleAuthors\\Classes\\Admin_Ajax', 'handle_users_search']
        );
        add_action(
            'wp_ajax_author_create_from_user',
            ['MultipleAuthors\\Classes\\Admin_Ajax', 'handle_author_create_from_user']
        );
        add_action(
            'wp_ajax_author_get_user_data',
            ['MultipleAuthors\\Classes\\Admin_Ajax', 'handle_author_get_user_data']
        );

        // Post integration
        add_action(
            'add_meta_boxes',
            ['MultipleAuthors\\Classes\\Post_Editor', 'action_add_meta_boxes_late'],
            100
        );
        add_action(
            'save_post',
            ['MultipleAuthors\\Classes\\Post_Editor', 'action_save_post_authors_metabox'],
            10,
            2
        );
        add_action(
            'save_post',
            [
                'MultipleAuthors\\Classes\\Post_Editor',
                'action_save_post_set_initial_author',
            ],
            10,
            3
        );

        // Notification Workflow support
        add_filter(
            'ppma_get_author_data',
            ['MultipleAuthors\\Classes\\Content_Model', 'filter_ma_get_author_data'],
            10,
            3
        );

        // Theme template tag filters.
        add_filter(
            'get_the_archive_title',
            [
                'MultipleAuthors\\Classes\\Integrations\\Theme',
                'filter_get_the_archive_title',
            ]
        );
        add_filter(
            'get_the_archive_description',
            [
                'MultipleAuthors\\Classes\\Integrations\\Theme',
                'filter_get_the_archive_description',
            ]
        );

        add_filter('admin_footer_text', [$this, 'update_footer_admin']);

        add_shortcode('ppma_test', [$this, 'ppma_test']);

        add_filter('cme_multiple_authors_capabilities', [$this, 'filterCMECapabilities'], 20);
    }

    public function ppma_test()
    {
        echo '<b>PublishPress Authors:</b> shortcode rendered successfully!';
    }

    /**
     * Manages the installation detecting if this is the first time this module runs or is an upgrade.
     * If no version is stored in the options, we treat as a new installation. Otherwise, we check the
     * last version. If different, it is an upgrade or downgrade.
     */
    public function manage_installation()
    {
        $option_name = 'PP_AUTHORS_VERSION';

        $previous_version = get_option($option_name);
        $current_version  = PP_AUTHORS_VERSION;

        if (!apply_filters('publishpress_authors_skip_installation', false, $previous_version, $current_version)) {
            if (empty($previous_version)) {
                /**
                 * Action called when the module is installed.
                 *
                 * @param string $current_version
                 */
                do_action('multiple_authors_install', $current_version);
            } elseif (version_compare($previous_version, $current_version, '>')) {
                /**
                 * Action called when the module is downgraded.
                 *
                 * @param string $previous_version
                 */
                do_action('multiple_authors_downgrade', $previous_version);
            } elseif (version_compare($previous_version, $current_version, '<')) {
                /**
                 * Action called when the module is upgraded.
                 *
                 * @param string $previous_version
                 */
                do_action('multiple_authors_upgrade', $previous_version);
            }
        }

        if ($current_version !== $previous_version) {
            update_option($option_name, $current_version, true);
        }
    }

    /**
     * Register the taxonomy used for managing relationships,
     * and the custom post type to store the author data.
     */
    public function action_init()
    {
        // Allow PublishPress Authors to be easily translated
        load_plugin_textdomain(
            'publishpress-authors',
            null,
            plugin_basename(PP_AUTHORS_BASE_PATH) . '/languages/'
        );

        // Maybe automatically apply our template tags
        if (apply_filters('coauthors_auto_apply_template_tags', false)) {
            global $multiple_authors_addon_template_filters;
            $multiple_authors_addon_template_filters = new Multiple_Authors_Template_Filters();
        }
    }

    /**
     * Register the 'author' taxonomy and add post type support
     */
    public function action_init_late()
    {
        // Register new taxonomy so that we can store all of the relationships
        $args = [
            'labels'             => [
                'name'                       => _x(
                    'Authors',
                    'taxonomy general name',
                    'publishpress-authors'
                ),
                'singular_name'              => _x(
                    'Author',
                    'taxonomy singular name',
                    'publishpress-authors'
                ),
                'search_items'               => __('Search authors', 'publishpress-authors'),
                'popular_items'              => __('Popular authors', 'publishpress-authors'),
                'all_items'                  => __('All authors', 'publishpress-authors'),
                'parent_item'                => __('Parent author', 'publishpress-authors'),
                'parent_item_colon'          => __('Parent author:', 'publishpress-authors'),
                'edit_item'                  => __('Edit author', 'publishpress-authors'),
                'view_item'                  => __('View author', 'publishpress-authors'),
                'update_item'                => __('Update author', 'publishpress-authors'),
                'add_new_item'               => __('New author', 'publishpress-authors'),
                'new_item_name'              => __('New author', 'publishpress-authors'),
                'separate_items_with_commas' => __(
                    'Separate authors with commas',
                    'publishpress-authors'
                ),
                'add_or_remove_items'        => __('Add or remove authors', 'publishpress-authors'),
                'choose_from_most_used'      => __(
                    'Choose from the most used authors',
                    'publishpress-authors'
                ),
                'not_found'                  => __('No authors found.', 'publishpress-authors'),
                'menu_name'                  => __('Author', 'publishpress-authors'),
                'back_to_items'              => __('Back to Authors', 'publishpress-authors'),
            ],
            'public'             => false,
            'hierarchical'       => false,
            'sort'               => true,
            'args'               => [
                'orderby' => 'term_order',
            ],
            'capabilities'       => [
                'manage_terms' => 'ppma_manage_authors',
                'edit_terms'   => 'ppma_manage_authors',
                'delete_terms' => 'ppma_manage_authors',
                'assign_terms' => 'ppma_manage_authors',
            ],
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_quick_edit' => false,
            'meta_box_cb'        => false,
            'query_var'          => 'ppma_author',
            'rewrite'            => false,
        ];

        // If we use the nasty SQL query, we need our custom callback. Otherwise, we still need to flush cache.
        if (!apply_filters('coauthors_plus_should_query_post_author', true)) {
            add_action('edited_term_taxonomy', [$this, 'action_edited_term_taxonomy_flush_cache'], 10, 2);
        }

        $supported_post_types = Utils::get_supported_post_types();
        register_taxonomy($this->coauthor_taxonomy, $supported_post_types, $args);
    }

    /**
     * Initialize the plugin for the admin
     */
    public function admin_init()
    {
        // Add the main JS script and CSS file
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        // Add quick-edit author select field
        add_action('quick_edit_custom_box', [$this, '_action_quick_edit_custom_box'], 10, 2);

        // Hooks to modify the published post number count on the Users WP List Table
        add_filter('manage_users_columns', [$this, '_filter_manage_users_columns']);

        // Apply some targeted filters
        add_action('load-edit.php', [$this, 'load_edit']);

        add_filter('pp_authors_show_footer', [$this, 'filterDisplayFooter'], 10);
    }

    /**
     * Display the PublishPress footer on the custom post pages
     */
    public function update_footer_admin($footer)
    {
        if ($this->shouldDisplayFooter()) {
            $legacyPlugin = Factory::getLegacyPlugin();

            $html = '<div class="pressshack-admin-wrapper">';
            $html .= $this->print_default_footer(
                $legacyPlugin->modules->multiple_authors,
                false
            );

            // We do not close the div by purpose. The footer contains it.

            // Add the wordpress footer
            $html .= $footer;

            return $html;
        }

        return $footer;
    }

    private function shouldDisplayFooter()
    {
        /**
         * @param bool $shouldDisplay
         *
         * @return bool
         */
        return apply_filters('pp_authors_show_footer', false);
    }

    /**
     * Echo or returns the default footer
     *
     * @param object $current_module
     * @param bool $echo
     *
     * @return string
     */
    public function print_default_footer($current_module, $echo = true)
    {
        $html = '';
        /**
         * @param bool $showFooter
         * @param string $currentModule
         */
        $showFooter = apply_filters('pp_authors_show_footer', true, $current_module);

        if ($showFooter) {
            $container = Factory::get_container();
            $twig      = $container['twig'];

            $html = $twig->render(
                'footer-base.twig',
                [
                    'current_module' => $current_module,
                    'plugin_name'    => __('PublishPress Authors', 'publishpress-authors'),
                    'plugin_slug'    => 'publishpress-authors',
                    'plugin_url'     => PP_AUTHORS_URL,
                    'rating_message' => __(
                        'If you like %s please leave us a %s rating. Thank you!',
                        'publishpress-authors'
                    ),
                ]
            );
        }

        if (!$echo) {
            return $html;
        }

        echo $html;
    }

    public function filterDisplayFooter($shouldDisplay = true)
    {
        global $current_screen;

        if ($current_screen->base === 'edit-tags' && $current_screen->taxonomy === 'author') {
            return true;
        }

        if ($current_screen->base === 'term' && $current_screen->taxonomy === 'author') {
            return true;
        }

        if ($current_screen->base === 'authors_page_ppma-modules-settings') {
            return true;
        }

        return $shouldDisplay;
    }

    public function action_widget_init()
    {
        register_widget('MultipleAuthors\\Widget');
    }

    /**
     * Unset the post count column because it's going to be inaccurate and provide our own
     */
    public function _filter_manage_users_columns($columns)
    {
        $new_columns = [];
        // Unset and add our column while retaining the order of the columns
        foreach ($columns as $column_name => $column_title) {
            if ('posts' != $column_name) {
                $new_columns[$column_name] = $column_title;
            }
        }

        return $new_columns;
    }

    /**
     * Quick Edit co-authors box.
     */
    public function _action_quick_edit_custom_box($column_name, $post_type)
    {
        if ('author' != $column_name || !Utils::is_post_type_enabled(
                $post_type
            ) || !Utils::current_user_can_set_authors()) {
            return;
        }
        ?>
        <label class="inline-edit-group inline-edit-coauthors">
            <span class="title"><?php esc_html_e('Authors', 'publishpress-authors') ?></span>
            <div id="coauthors-edit" class="hide-if-no-js">
                <p><?php echo wp_kses(
                        __(
                            'Click on an author to change them. Drag to change their order.',
                            'publishpress-authors'
                        ),
                        ['strong' => []]
                    ); ?></p>
            </div>
            <?php wp_nonce_field('coauthors-edit', 'coauthors-nonce'); ?>
        </label>
        <?php
    }

    /**
     * If we're forcing PublishPress Authors to just do taxonomy queries, we still
     * need to flush our special cache after a taxonomy term has been updated
     *
     * @since 3.1
     */
    public function action_edited_term_taxonomy_flush_cache($tt_id, $taxonomy)
    {
        global $wpdb;

        if ($this->coauthor_taxonomy != $taxonomy) {
            return;
        }

        $term_id = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT term_id FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = %d ",
                $tt_id
            )
        );

        $term     = get_term_by('id', $term_id[0]->term_id, $taxonomy);
        $coauthor = $this->get_coauthor_by('user_nicename', $term->slug);
        if (!$coauthor) {
            return new WP_Error(
                'missing-coauthor',
                __('No co-author exists for that term', 'publishpress-authors')
            );
        }

        wp_cache_delete('author-term-' . $coauthor->user_nicename, 'publishpress-authors');
    }

    /**
     * Get a co-author object by a specific type of key
     *
     * @param string $key Key to search by (slug,email)
     * @param string $value Value to search for
     *
     * @return object|false $coauthor The co-author on success, false on failure
     */
    public function get_coauthor_by($key, $value, $force = false)
    {
        switch ($key) {
            case 'id':
            case 'login':
            case 'user_login':
            case 'email':
            case 'user_nicename':
            case 'user_email':
                if ('user_login' == $key) {
                    $key = 'login';
                }
                if ('user_email' == $key) {
                    $key = 'email';
                }
                if ('user_nicename' == $key) {
                    $key = 'slug';
                }
                // Ensure we aren't doing the lookup by the prefixed value
                if ('login' == $key || 'slug' == $key) {
                    $value = preg_replace('#^cap\-#', '', $value);
                }
                $user = get_user_by($key, $value);
                if (!$user) {
                    return false;
                }
                $user->type = 'wpuser';

                return $user;
                break;
        }

        return false;
    }

    /**
     * Add one or more co-authors as bylines for a post
     *
     * @param int
     * @param array
     * @param bool
     */
    public function add_coauthors($post_id, $coauthors, $append = false)
    {
        global $current_user, $wpdb;

        $post_id = (int)$post_id;
        $insert  = false;

        // Best way to persist order
        if ($append) {
            $existing_coauthors = wp_list_pluck(get_multiple_authors($post_id), 'user_login');
        } else {
            $existing_coauthors = [];
        }

        // A co-author is always required
        if (empty($coauthors)) {
            $coauthors = [$current_user->user_login];
        }

        // Set the coauthors
        $coauthors        = array_unique(array_merge($existing_coauthors, $coauthors));
        $coauthor_objects = [];
        foreach ($coauthors as &$author_name) {
            $author             = $this->get_coauthor_by('user_nicename', $author_name);
            $coauthor_objects[] = $author;
            $term               = $this->update_author_term($author);
            $author_name        = $term->slug;
        }
        wp_set_post_terms($post_id, $coauthors, $this->coauthor_taxonomy, false);

        // If the original post_author is no longer assigned,
        // update to the first WP_User $coauthor
        $post_author_user = get_user_by('id', get_post($post_id)->post_author);
        if (empty($post_author_user)
            || !in_array($post_author_user->user_login, $coauthors)) {
            foreach ($coauthor_objects as $coauthor_object) {
                if ('wpuser' == $coauthor_object->type) {
                    $new_author = $coauthor_object;
                    break;
                }
            }
            // Uh oh, no WP_Users assigned to the post
            if (empty($new_author)) {
                return false;
            }

            $wpdb->update($wpdb->posts, ['post_author' => $new_author->ID], ['ID' => $post_id]);
            clean_post_cache($post_id);
        }

        return true;
    }

    /**
     * Update the author term for a given co-author
     *
     * @param object $coauthor The co-author object
     *
     * @return object|false $success Term object if successful, false if not
     * @since 3.0
     *
     */
    public function update_author_term($coauthor)
    {
        if (!is_object($coauthor)) {
            return false;
        }

        // Update the taxonomy term to include details about the user for searching
        $search_values = [];
        foreach ($this->ajax_search_fields as $search_field) {
            $search_values[] = $coauthor->$search_field;
        }

        $term_description = implode(' ', $search_values);

        if ($term = $this->get_author_term($coauthor)) {
            if ($term->description != $term_description) {
                wp_update_term(
                    $term->term_id,
                    $this->coauthor_taxonomy,
                    ['description' => $term_description]
                );
            }
        } else {
            $args = [
                'slug'        => $coauthor->user_nicename,
                'description' => $term_description,
            ];

            $new_term = wp_insert_term($coauthor->user_login, $this->coauthor_taxonomy, $args);
        }
        wp_cache_delete('author-term-' . $coauthor->user_nicename, 'publishpress-authors');

        return $this->get_author_term($coauthor);
    }

    /**
     * Get the author term for a given co-author
     *
     * @param object $coauthor The co-author object
     *
     * @return object|false $author_term The author term on success
     * @since 3.0
     *
     */
    public function get_author_term($coauthor)
    {
        if (!is_object($coauthor)) {
            return;
        }

        $cache_key = 'author-term-' . $coauthor->user_nicename;
        if (false !== ($term = wp_cache_get($cache_key, 'publishpress-authors'))) {
            return $term;
        }

        // See if the prefixed term is available, otherwise default to just the nicename
        $term = get_term_by('slug', $coauthor->user_nicename, $this->coauthor_taxonomy);

        wp_cache_set($cache_key, $term, 'publishpress-authors');

        return $term;
    }

    /**
     * Restrict WordPress from blowing away author order when bulk editing terms
     *
     * @since 2.6
     * @props kingkool68, http://wordpress.org/support/topic/plugin-publishpress-authors-making-authors-sortable
     */
    public function filter_wp_get_object_terms($terms, $object_ids, $taxonomies, $args)
    {
        if (!isset($_REQUEST['bulk_edit']) || "'author'" !== $taxonomies) {
            return $terms;
        }

        global $wpdb;
        $orderby       = 'ORDER BY tr.term_order';
        $order         = 'ASC';
        $object_ids    = (int)$object_ids;
        $query         = $wpdb->prepare(
            "SELECT t.name, t.term_id, tt.term_taxonomy_id FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON tt.term_id = t.term_id INNER JOIN $wpdb->term_relationships AS tr ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE tt.taxonomy IN (%s) AND tr.object_id IN (%s) $orderby $order",
            $this->coauthor_taxonomy,
            $object_ids
        );
        $raw_coauthors = $wpdb->get_results($query);
        $terms         = [];
        foreach ($raw_coauthors as $author) {
            if (true === is_array($args) && true === isset($args['fields'])) {
                switch ($args['fields']) {
                    case 'names' :
                        $terms[] = $author->name;
                        break;
                    case 'tt_ids' :
                        $terms[] = $author->term_taxonomy_id;
                        break;
                    case 'all' :
                    default :
                        $terms[] = get_term($author->term_id, $this->coauthor_taxonomy);
                        break;
                }
            } else {
                $terms[] = get_term($author->term_id, $this->coauthor_taxonomy);
            }
        }

        return $terms;
    }

    /**
     * Filter the number of author posts. The author can be mapped to a user or not.
     *
     * @return int
     */
    public function filter_count_author_posts($count, $term_id)
    {
        global $wpdb;

        $items = $wpdb->get_results(
            "SELECT *
                     FROM {$wpdb->posts} AS p
                     INNER JOIN {$wpdb->term_relationships} AS tr ON (tr.`object_id` = p.ID)
                     INNER JOIN {$wpdb->term_taxonomy} AS tt ON (tt.`term_taxonomy_id` = tr.`term_taxonomy_id`)
                     WHERE
                      p.post_type = 'post'
                      AND p.post_status NOT IN ('trash', 'auto-draft')
                      AND tt.`term_id` = {$term_id}"
        );

        $count = count($items);

        return $count;
    }

    /**
     * Filter the count_users_posts() core function to include our correct count.
     * The author is always mapped to a user.
     *
     * @return int
     */
    public function filter_count_user_posts($count, $user_id)
    {
        global $wpdb;

        $author = Author::get_by_user_id($user_id);

        if (!is_object($author)) {
            return 0;
        }

        $count = apply_filters('get_authornumposts', $count, $author->term_id);

        return $count;
    }

    /**
     * Fix for author pages 404ing or not properly displaying on author pages
     *
     * If an author has no posts, we only want to force the queried object to be
     * the author if they're a member of the blog.
     *
     * If the author does have posts, it doesn't matter that they're not an author.
     *
     * Alternatively, on an author archive, if the first story has coauthors and
     * the first author is NOT the same as the author for the archive,
     * the query_var is changed.
     *
     * @param string $query_str
     */
    public function fix_query_for_author_page($query_str)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        if (empty($legacyPlugin) || !isset($legacyPlugin->multiple_authors) || !Utils::is_post_type_enabled()) {
            return;
        }

        global $wp_query;
        global $authordata;

        if (!is_object($wp_query)) {
            return;
        }

        if (!Util::isAuthor() && (empty($wp_query->query) || !array_key_exists('author', $wp_query->query))) {
            return;
        }

        $author_name = $wp_query->get('author_name');
        if (!$author_name) {
            return;
        }

        Query::fix_query_pre_get_posts($wp_query);

        $wp_query->is_404 = false;
    }

    /**
     * @param bool $shortCircuit
     * @param \WP_Query $wp_query
     */
    public function fix_404_for_authors($shortCircuit, $wp_query)
    {
        if ($shortCircuit || !$wp_query->is_author) {
            return $shortCircuit;
        }

        if (is_404()) {
            return true;
        }

        $is_favicon = false;
        if (function_exists('is_favicon')) {
            $is_favicon = is_favicon();
        }

        if (is_admin() || is_robots() || $is_favicon || $wp_query->posts) {
            return $shortCircuit;
        }

        if (!is_paged()) {
            // Don't 404 for Authors without posts as long as they matched an author on this site.

            if ($wp_query->queried_object instanceof Author) {
                status_header(200);
                return true;
            } else {
                return $shortCircuit;
            }
        }

        return $shortCircuit;
    }

    public function fix_post(WP_Post $post)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        if (empty($legacyPlugin) || !isset($legacyPlugin->multiple_authors) || !Utils::is_post_type_enabled()) {
            return $post;
        }

        global $authordata;

        $authors = get_multiple_authors($post);

        if (empty($authors)) {
            return $post;
        }

        $firstAuthor = $authors[0];

        if (!empty($authordata)) {
            $authordata->display_name  = $firstAuthor->display_name;
            $authordata->ID            = $firstAuthor->ID;
            $authordata->user_nicename = $firstAuthor->user_nicename;
        }

        return $post;
    }

    /**
     * Get matching authors based on a search value
     */
    public function search_authors($search = '', $ignored_authors = [])
    {
        // Since 2.7, we're searching against the term description for the fields
        // instead of the user details. If the term is missing, we probably need to
        // backfill with user details. Let's do this first... easier than running
        // an upgrade script that could break on a lot of users
        $args = [
            'count_total'   => false,
            'search'        => sprintf('*%s*', $search),
            'search_fields' => [
                'ID',
                'display_name',
                'user_email',
                'user_login',
            ],
            'fields'        => 'all_with_meta',
        ];
        add_action('pre_user_query', [$this, 'action_pre_user_query']);
        $found_users = get_users($args);
        remove_action('pre_user_query', [$this, 'action_pre_user_query']);

        foreach ($found_users as $found_user) {
            $term = $this->get_author_term($found_user);
            if (empty($term) || empty($term->description)) {
                $this->update_author_term($found_user);
            }
        }

        $args = [
            'search' => $search,
            'get'    => 'all',
            'number' => 10,
        ];

        $args = apply_filters('coauthors_search_authors_get_terms_args', $args);
        add_filter('terms_clauses', [$this, 'filter_terms_clauses']);
        $found_terms = get_terms($this->coauthor_taxonomy, $args);
        remove_filter('terms_clauses', [$this, 'filter_terms_clauses']);

        if (empty($found_terms)) {
            return [];
        }

        // Get the co-author objects
        $found_users = [];
        foreach ($found_terms as $found_term) {
            $found_user = $this->get_coauthor_by('user_nicename', $found_term->slug);
            if (!empty($found_user)) {
                $found_users[$found_user->user_login] = $found_user;
            }
        }

        // Allow users to always filter out certain users if needed (e.g. administrators)
        $ignored_authors = apply_filters('coauthors_edit_ignored_authors', $ignored_authors);
        foreach ($found_users as $key => $found_user) {
            // Make sure the user is contributor and above (or a custom cap)
            if (in_array($found_user->user_login, $ignored_authors)) {
                unset($found_users[$key]);
            } else {
                if ('wpuser' === $found_user->type && false === $found_user->has_cap(
                        apply_filters(
                            'coauthors_edit_author_cap',
                            'edit_posts'
                        )
                    )) {
                    unset($found_users[$key]);
                }
            }
        }

        return (array)$found_users;
    }

    /**
     * Modify get_users() to search display_name instead of user_nicename
     */
    public function action_pre_user_query(&$user_query)
    {
        if (is_object($user_query)) {
            $user_query->query_where = str_replace(
                'user_nicename LIKE',
                'display_name LIKE',
                $user_query->query_where
            );
        }
    }

    /**
     * Modify get_terms() to LIKE against the term description instead of the term name
     *
     * @since 3.0
     */
    public function filter_terms_clauses($pieces)
    {
        $pieces['where'] = str_replace('t.name LIKE', 'tt.description LIKE', $pieces['where']);

        return $pieces;
    }

    /**
     * Functions to add scripts and css
     */
    public function enqueue_scripts($hook_suffix)
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-sortable');

        wp_enqueue_style(
            'pressshack-style',
            PP_AUTHORS_ASSETS_URL . 'css/pressshack-admin.css',
            [],
            PP_AUTHORS_VERSION
        );

        wp_enqueue_style(
            'multiple-authors-style',
            PP_AUTHORS_ASSETS_URL . 'css/multiple-authors.css',
            [],
            PP_AUTHORS_VERSION
        );

        wp_enqueue_style(
            'multiple-authors-chosen',
            PP_AUTHORS_ASSETS_URL . 'lib/chosen-v1.8.3/chosen.min.css',
            false,
            PP_AUTHORS_VERSION,
            'all'
        );

        wp_enqueue_script(
            'multiple-authors-chosen',
            PP_AUTHORS_ASSETS_URL . 'lib/chosen-v1.8.3/chosen.jquery.min.js',
            ['jquery'],
            PP_AUTHORS_VERSION
        );

        wp_enqueue_script(
            'multiple-authors-select2',
            PP_AUTHORS_ASSETS_URL . 'lib/select2/js/select2.min.js',
            ['jquery'],
            PP_AUTHORS_VERSION
        );

        wp_enqueue_style(
            'multiple-authors-select2',
            PP_AUTHORS_ASSETS_URL . 'lib/select2/css/select2.min.css',
            [],
            PP_AUTHORS_VERSION
        );

        wp_enqueue_script(
            'multiple-authors-js',
            PP_AUTHORS_ASSETS_URL . 'js/multiple-authors.js',
            ['jquery', 'suggest', 'multiple-authors-select2', 'jquery-ui-sortable', 'wp-util'],
            PP_AUTHORS_VERSION
        );

        $nonce = wp_create_nonce("author_get_user_data_nonce");

        $legacyPlugin = Factory::getLegacyPlugin();

        $js_strings = [
            'edit_label'                    => __('Edit', 'publishpress-authors'),
            'confirm_delete'                => __(
                'Are you sure you want to remove this author?',
                'publishpress-authors'
            ),
            'input_box_title'               => __(
                'Click to change this author, or drag to change their position',
                'publishpress-authors'
            ),
            'search_box_text'               => __('Search for an author', 'publishpress-authors'),
            'help_text'                     => __(
                'Click on an author to change them. Drag to change their order. Click on <strong>Remove</strong> to remove them.',
                'publishpress-authors'
            ),
            'confirm_delete_mapped_authors' => __(
                'Are you sure you want to delete the authors profiles mapped to users? This action can\'t be undone.',
                'publishpress-authors'
            ),
            'confirm_delete_guest_authors'  => __(
                'Are you sure you want to delete the guest authors profiles? This action can\'t be undone.',
                'publishpress-authors'
            ),
            'confirm_create_post_authors'   => __(
                'Are you sure you want to create author profiles for the missed post authors?',
                'publishpress-authors'
            ),
            'confirm_create_role_authors'   => __(
                'Are you sure you want to create authors for the selected roles?',
                'publishpress-authors'
            ),
            'ajax_get_author_data_url'      => admin_url('admin-ajax.php?action=author_get_user_data&nonce=' . $nonce),
            'menu_slug'                     => MA_Multiple_Authors::MENU_SLUG,
            'wait_text'                     => __('Please, wait...', 'publishpress-authors'),
            'error_on_request'              => __(
                'Sorry, the request returned an error.',
                'publishpress-authors'
            ),
        ];

        wp_localize_script('multiple-authors-js', 'MultipleAuthorsStrings', $js_strings);

        wp_enqueue_media();
    }

    /**
     * load-edit.php is when the screen has been set up
     */
    public function load_edit()
    {
        $screen               = get_current_screen();
        $supported_post_types = Utils::get_supported_post_types();
        if (in_array($screen->post_type, $supported_post_types)) {
            add_filter('views_' . $screen->id, [$this, 'filter_views']);
        }
    }

    /**
     * Filter the view links that appear at the top of the Manage Posts view
     *
     * @since 3.0
     */
    public function filter_views($views)
    {
        if (array_key_exists('mine', $views)) {
            return $views;
        }

        $views     = array_reverse($views);
        $all_view  = array_pop($views);
        $mine_args = [
            'author_name' => wp_get_current_user()->user_nicename,
        ];
        if ('post' != Util::get_current_post_type()) {
            $mine_args['post_type'] = Util::get_current_post_type();
        }
        if (!empty($_REQUEST['author_name']) && wp_get_current_user()->user_nicename == $_REQUEST['author_name']) {
            $class = ' class="current"';
        } else {
            $class = '';
        }
        $views['mine'] = $view_mine = '<a' . $class . ' href="' . esc_url(
                add_query_arg(
                    array_map(
                        'rawurlencode',
                        $mine_args
                    ),
                    admin_url('edit.php')
                )
            ) . '">' . __(
                'Mine',
                'publishpress-authors'
            ) . '</a>';

        $views['all'] = str_replace($class, '', $all_view);
        $views        = array_reverse($views);

        return $views;
    }

    /**
     * Allows coauthors to edit the post they're coauthors of
     */
    public function filter_user_has_cap($allcaps, $caps, $args)
    {
        $cap     = $args[0];
        $user_id = isset($args[1]) ? $args[1] : 0;
        $post_id = isset($args[2]) ? $args[2] : 0;

        $obj = get_post_type_object(Util::get_current_post_type($post_id));
        if (!$obj || 'revision' == $obj->name) {
            return $allcaps;
        }

        $caps_to_modify = [
            $obj->cap->edit_post,
            'edit_post', // Need to filter this too, unfortunately: http://core.trac.wordpress.org/ticket/22415
            $obj->cap->edit_others_posts, // This as well: http://core.trac.wordpress.org/ticket/22417
        ];
        if (!in_array($cap, $caps_to_modify)) {
            return $allcaps;
        }

        if (!is_user_logged_in()) {
            return $allcaps;
        }

        $allowEdit = is_multiple_author_for_post($user_id, $post_id);

        // Not an author, does the post has an author? If not, can we edit it?
        if (!$allowEdit) {
            $post_authors = get_multiple_authors($post_id);

            if (empty($post_authors) && isset($allcaps['ppma_edit_orphan_post']) && $allcaps['ppma_edit_orphan_post']) {
                $allowEdit = true;
            }
        }

        if ($allowEdit) {
            $current_user = wp_get_current_user();
            $post_status  = get_post_status($post_id);

            if ('publish' == $post_status &&
                (isset($obj->cap->edit_published_posts) && !empty($current_user->allcaps[$obj->cap->edit_published_posts]))) {
                $allcaps[$obj->cap->edit_published_posts] = true;
            } elseif ('private' == $post_status &&
                (isset($obj->cap->edit_private_posts) && !empty($current_user->allcaps[$obj->cap->edit_private_posts]))) {
                $allcaps[$obj->cap->edit_private_posts] = true;
            }

            $allcaps[$obj->cap->edit_others_posts] = true;
        }

        return $allcaps;
    }

    /**
     * Filter Edit Flow's 'ef_calendar_item_information_fields' to add co-authors
     *
     * @param array $information_fields
     * @param int $post_id
     *
     * @return array
     */
    public function filter_ef_calendar_item_information_fields($information_fields, $post_id)
    {
        // Don't add the author row again if another plugin has removed
        if (!array_key_exists('author', $information_fields)) {
            return $information_fields;
        }

        $co_authors = get_multiple_authors($post_id);
        if (count($co_authors) > 1) {
            $information_fields['author']['label'] = __('Authors', 'publishpress-authors');
        }
        $co_authors_names = '';
        foreach ($co_authors as $co_author) {
            $co_authors_names .= $co_author->display_name . ', ';
        }
        $information_fields['author']['value'] = rtrim($co_authors_names, ', ');

        return $information_fields;
    }

    /**
     * Filter Edit Flow's 'ef_story_budget_term_column_value' to add co-authors to the story budget
     *
     * @param string $column_name
     * @param object $post
     * @param object $parent_term
     *
     * @return string
     */
    public function filter_ef_story_budget_term_column_value($column_name, $post, $parent_term)
    {
        // We only want to modify the 'author' column
        if ('author' != $column_name) {
            return $column_name;
        }

        $co_authors       = get_multiple_authors($post->ID);
        $co_authors_names = '';
        foreach ($co_authors as $co_author) {
            $co_authors_names .= $co_author->display_name . ', ';
        }

        return rtrim($co_authors_names, ', ');
    }

    /**
     * Filter non-native users added by Co-Author-Plus in Jetpack
     *
     * @param array $og_tags Required. Array of Open Graph Tags.
     * @param array $image_dimensions Required. Dimensions for images used.
     *
     * @return array Open Graph Tags either as they were passed or updated.
     * @since 3.1
     *
     */
    public function filter_jetpack_open_graph_tags($og_tags, $image_dimensions)
    {
        if (Util::isAuthor()) {
            $author                        = get_queried_object();
            $og_tags['og:title']           = $author->display_name;
            $og_tags['og:url']             = get_author_posts_url($author->ID, $author->user_nicename);
            $og_tags['og:description']     = $author->description;
            $og_tags['profile:first_name'] = $author->first_name;
            $og_tags['profile:last_name']  = $author->last_name;
            if (isset($og_tags['article:author'])) {
                $og_tags['article:author'] = get_author_posts_url($author->ID, $author->user_nicename);
            }
        } else {
            if (is_singular() && Utils::is_post_type_enabled()) {
                $authors = get_multiple_authors();
                if (!empty($authors)) {
                    $author = array_shift($authors);
                    if (isset($og_tags['article:author'])) {
                        $og_tags['article:author'] = get_author_posts_url(
                            $author->ID,
                            $author->user_nicename
                        );
                    }
                }
            }
        }

        // Send back the updated Open Graph Tags
        return apply_filters('coauthors_open_graph_tags', $og_tags);
    }

    /**
     * Retrieve a list of coauthor terms for a single post.
     *
     * Grabs a correctly ordered list of authors for a single post, appropriately
     * cached because it requires `wp_get_object_terms()` to succeed.
     *
     * @param int $post_id ID of the post for which to retrieve authors.
     *
     * @return array Array of coauthor WP_Term objects
     */
    public function get_coauthor_terms_for_post($post_id)
    {
        if (!$post_id) {
            return [];
        }

        $cache_key      = 'coauthors_post_' . $post_id;
        $coauthor_terms = wp_cache_get($cache_key, 'publishpress-authors');

        if (false === $coauthor_terms) {
            $coauthor_terms = wp_get_object_terms(
                $post_id,
                $this->coauthor_taxonomy,
                [
                    'orderby' => 'term_order',
                    'order'   => 'ASC',
                ]
            );

            // This usually happens if the taxonomy doesn't exist, which should never happen, but you never know.
            if (is_wp_error($coauthor_terms)) {
                return [];
            }

            wp_cache_set($cache_key, $coauthor_terms, 'publishpress-authors');
        }

        return $coauthor_terms;
    }

    /**
     * Callback to clear the cache on post save and post delete.
     *
     * @param $post_id The Post ID.
     */
    public function clear_cache($post_id)
    {
        wp_cache_delete('coauthors_post_' . $post_id, 'publishpress-authors');
    }

    /**
     * Callback to clear the cache when an object's terms are changed.
     *
     * @param $post_id The Post ID.
     */
    public function clear_cache_on_terms_set($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids)
    {
        // We only care about the coauthors taxonomy
        if ($this->coauthor_taxonomy !== $taxonomy) {
            return;
        }

        wp_cache_delete('coauthors_post_' . $object_id, 'publishpress-authors');
    }

    /**
     * Callback for the filter to add the author box to the end of the content
     *
     * @return string
     */
    public function filter_the_content($content)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        // Check if it is configured to append to the content
        $append_to_content = 'yes' === $legacyPlugin->modules->multiple_authors->options->append_to_content;

        if ($this->should_display_author_box() && $append_to_content) {
            $content .= $this->get_author_box_markup('the_content');
        }

        return $content;
    }

    /**
     * Shortcode to get the author box
     *
     * @param array $attributes
     *
     * @return string
     */
    public function shortcode_author_box($attributes)
    {
        $show_title = true;
        $layout     = null;
        $archive    = false;
        $post_id    = null;

        if (isset($attributes['show_title'])) {
            $show_title = $attributes['show_title'] === 'true' || (int)$attributes['show_title'] === 1;
        }

        if (isset($attributes['layout'])) {
            $layout = $attributes['layout'];
        }

        if (isset($attributes['archive'])) {
            $archive = $attributes['archive'] === 'true' || (int)$attributes['archive'] === 1;
        }

        if (isset($attributes['post_id'])) {
            $post_id = $attributes['post_id'];
        }

        return $this->get_author_box_markup('shortcode', $show_title, $layout, $archive, $post_id);
    }

    /**
     * Action to display the author box
     *
     * @param null $show_title
     * @param null $layout
     * @param bool $archive
     * @param bool $force
     * @param null $post_id
     */
    public function action_echo_author_box(
        $show_title = null,
        $layout = null,
        $archive = false,
        $force = false,
        $post_id = null
    ) {
        if ($this->should_display_author_box() || $force) {
            echo $this->get_author_box_markup('action', $show_title, $layout, $archive, $post_id);
        }
    }

    /**
     * Method called on activating the plugin.
     */
    public function activation_hook()
    {
        flush_rewrite_rules();
    }

    public function filterCMECapabilities($capabilities)
    {
        $capabilities = array_merge(
            $capabilities,
            [
                'ppma_manage_authors',
            ]
        );

        return $capabilities;
    }
}
