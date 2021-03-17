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

use MultipleAuthors\Classes\Admin_Ajax;
use MultipleAuthors\Classes\Author_Utils;
use MultipleAuthors\Classes\Installer;
use MultipleAuthors\Classes\Legacy\Module;
use MultipleAuthors\Classes\Legacy\Util;
use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Classes\Utils;
use MultipleAuthors\Factory;


if (!class_exists('MA_Multiple_Authors')) {
    /**
     * class MA_Multiple_Authors
     */
    class MA_Multiple_Authors extends Module
    {
        const SETTINGS_SLUG = 'ppma-settings';

        public $module_name = 'multiple_authors';

        /**
         * The menu slug.
         */
        const MENU_SLUG = 'ppma-authors';

        /**
         * List of post types which supports authors
         *
         * @var array
         */
        protected $post_types = [];

        /**
         * Instance for the module
         *
         * @var stdClass
         */
        public $module;

        private $eddAPIUrl = 'https://publishpress.com';


        /**
         * Construct the MA_Multiple_Authors class
         */
        public function __construct()
        {
            $this->module_url = $this->get_module_url(__FILE__);

            // Register the module with PublishPress
            $args = [
                'title'                => __('Multiple Authors', 'publishpress-authors'),
                'short_description'    => __(
                    'PublishPress Authors allows you to add multiple authors and guest authors to WordPress posts',
                    'publishpress-authors'
                ),
                'extended_description' => __(
                    'PublishPress Authors allows you to add multiple authors and guest authors to WordPress posts',
                    'publishpress-authors'
                ),
                'module_url'           => $this->module_url,
                'icon_class'           => 'dashicons dashicons-feedback',
                'slug'                 => 'multiple-authors',
                'default_options'      => [
                    'enabled'                      => 'on',
                    'post_types'                   => [
                        'post' => 'on',
                        'page' => 'on',
                    ],
                    'append_to_content'            => 'yes',
                    'author_for_new_users'         => [],
                    'layout'                       => Utils::getDefaultLayout(),
                    'force_empty_author'           => 'no',
                    'username_in_search_field'     => 'no',
                    'default_author_for_new_posts' => null,
                ],
                'options_page'         => false,
                'autoload'             => true,
            ];

            // Apply a filter to the default options
            $args['default_options'] = apply_filters('pp_multiple_authors_default_options', $args['default_options']);

            $legacyPlugin = Factory::getLegacyPlugin();

            $this->module = $legacyPlugin->register_module($this->module_name, $args);

            parent::__construct();
        }

        /**
         * Returns a list of post types the multiple authors module.
         *
         * @return array
         */
        public function get_post_types()
        {
            if (empty($this->post_types)) {
                $post_types = [
                    'post' => esc_html__('Posts', 'publishpress-authors'),
                    'page' => esc_html__('Pages', 'publishpress-authors'),
                ];

                // Apply filters to the list of requirements
                $this->post_types = apply_filters('pp_multiple_authors_post_types', $post_types);

                // Try a more readable name
                foreach ($this->post_types as $type => $label) {
                    $this->post_types[$type] = esc_html__(ucfirst($label));
                }
            }

            return $this->post_types;
        }

        /**
         * Initialize the module. Conditionally loads if the module is enabled
         */
        public function init()
        {
            if (is_admin()) {
                add_action('admin_init', [$this, 'register_settings']);
                add_action('admin_init', [$this, 'handle_maintenance_task']);
                add_action('admin_init', [$this, 'migrate_legacy_settings']);
                add_action('admin_init', [$this, 'dismissCoAuthorsMigrationNotice']);
                add_action('admin_init', [$this, 'dismissPermissionsSyncNotice']);
                add_action('admin_notices', [$this, 'coauthorsMigrationNotice']);
                add_action('admin_notices', [$this, 'permissionsSyncNotice']);
                add_action('admin_notices', [$this, 'handle_maintenance_task_notice']);

                add_filter('gettext', [$this, 'filter_get_text'], 101, 3);

                // Menu
                add_action('multiple_authors_admin_menu_page', [$this, 'action_admin_menu_page']);
                add_action('multiple_authors_admin_submenu', [$this, 'action_admin_submenu'], 50);
                add_filter('custom_menu_order', [$this, 'filter_custom_menu_order']);

                add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
            }

            add_action('multiple_authors_delete_mapped_authors', [$this, 'action_delete_mapped_authors']);
            add_action('multiple_authors_delete_guest_authors', [$this, 'action_delete_guest_authors']);
            add_action('multiple_authors_create_post_authors', [$this, 'action_create_post_authors']);
            add_action('multiple_authors_create_role_authors', [$this, 'action_create_role_authors']);
            add_action('multiple_authors_copy_coauthor_plus_data', [$this, 'action_copy_coauthor_plus_data']);

            add_action('deleted_user', [$this, 'handle_deleted_user']);

            // Filters the list of authors in the Improved Notifications add-on.
            add_filter(
                'publishpress_notifications_receiver_post_authors',
                [$this, 'filter_workflow_receiver_post_authors'],
                10,
                3
            );

            add_filter('multiple_authors_validate_module_settings', [$this, 'validate_module_settings'], 10, 2);
            add_filter('publishpress_multiple_authors_settings_tabs', [$this, 'settings_tab']);

            if (!is_admin()) {
                add_filter('body_class', [$this, 'filter_body_class']);
            }

            // Fix upload permissions for multiple authors.
            add_filter('map_meta_cap', [$this, 'filter_map_meta_cap'], 10, 4);

            add_filter('publishpress_is_author_of_post', [$this, 'filter_is_author_of_post'], 10, 3);
            add_filter('publishpress_post_authors_names', [$this, 'filter_post_authors_names'], 10, 2);

            add_action('wp_ajax_migrate_coauthors', [$this, 'migrateCoAuthorsData']);
            add_action('wp_ajax_get_coauthors_migration_data', [$this, 'getCoauthorsMigrationData']);
            add_action('wp_ajax_finish_coauthors_migration', [$this, 'finishCoAuthorsMigration']);
            add_action('wp_ajax_get_sync_post_author_data', [$this, 'getSyncPostAuthorData']);
            add_action('wp_ajax_sync_post_author', [$this, 'syncPostAuthor']);
            add_action('wp_ajax_finish_sync_post_author', [$this, 'finishSyncPostAuthor']);
            add_action('wp_ajax_get_sync_author_slug_data', [$this, 'getSyncAuthorSlugData']);
            add_action('wp_ajax_sync_author_slug', [$this, 'syncAuthorSlug']);
            add_action('wp_ajax_finish_sync_author_slug', [$this, 'finishSyncAuthorSlug']);
            add_action('wp_ajax_deactivate_coauthors_plus', [$this, 'deactivateCoAuthorsPlus']);

            // PublishPress compatibility hooks.
            add_filter('publishpress_search_authors_results_pre_search', [$this, 'publishpressSearchAuthors'], 10, 2);
            add_filter('publishpress_author_can_edit_posts', [$this, 'publishpressAuthorCanEditPosts'], 10, 2);
            add_filter(
                'publishpress_calendar_after_create_post',
                [$this, 'publishpressCalendarAfterCreatePost'],
                10,
                2
            );
            add_filter('publishpress_calendar_default_author', [$this, 'publishpressCalendarDefaultAuthor'], 10, 2);
            add_filter('publishpress_author_filter_selected_option', [$this, 'publishpressAuthorFilterSelectedOption'], 10, 2);
            add_filter('PP_Content_Overview_posts_query_args', [$this, 'publishpressPostQueryArgs']);
            add_filter('publishpress_content_overview_author_column', [$this, 'publishpressContentOverviewAuthorColumn'], 10, 2);
            add_filter('pp_calendar_posts_query_args', [$this, 'publishpressPostQueryArgs']);

            // Add compatibility with GeneratePress theme.
            add_filter('generate_post_author_output', [$this, 'generatepress_author_output']);

            add_filter('the_author_posts_link', [$this, 'theAuthorPostsLink']);

            // Fix authors metadata.
            add_filter('get_the_author_display_name', [$this, 'filter_author_metadata_display_name'], 10, 2);
            add_filter('get_the_author_first_name', [$this, 'filter_author_metadata_first_name'], 10, 2);
            add_filter('get_the_author_last_name', [$this, 'filter_author_metadata_last_name'], 10, 2);
            add_filter('get_the_author_ID', [$this, 'filter_author_metadata_ID'], 10, 2);
            add_filter('get_the_author_headline', [$this, 'filter_author_metadata_headline'], 10, 2);
            add_filter('get_the_author_aim', [$this, 'filter_author_metadata_aim'], 10, 2);
            add_filter('get_the_author_description', [$this, 'filter_author_metadata_description'], 10, 2);
            add_filter('get_the_author_jabber', [$this, 'filter_author_metadata_jabber'], 10, 2);
            add_filter('get_the_author_nickname', [$this, 'filter_author_metadata_nickname'], 10, 2);
            add_filter('get_the_author_user_description', [$this, 'filter_author_metadata_user_description'], 10, 2);
            add_filter('get_the_author_user_email', [$this, 'filter_author_metadata_user_email'], 10, 2);
            add_filter('get_the_author_user_url', [$this, 'filter_author_metadata_user_url'], 10, 2);
            add_filter('get_the_author_user_nicename', [$this, 'filter_author_metadata_user_nicename'], 10, 2);
            add_filter('get_the_author_yim', [$this, 'filter_author_metadata_yim'], 10, 2);
            add_filter('get_the_author_facebook', [$this, 'filter_author_metadata_facebook'], 10, 2);
            add_filter('get_the_author_twitter', [$this, 'filter_author_metadata_twitter'], 10, 2);
            add_filter('get_the_author_instagram', [$this, 'filter_author_metadata_instagram'], 10, 2);

            // Fix authors avatar.
            add_filter('pre_get_avatar_data', [$this, 'filter_pre_get_avatar_data'], 15, 2);

            add_action('publishpress_authors_set_post_authors', [$this, 'actionSetPostAuthors'], 10, 2);

            add_action('profile_update', [$this, 'userProfileUpdate'], 10, 2);
        }

        /**
         * Creates the admin menu if there is no menu set.
         */
        public function action_admin_menu_page()
        {
            add_menu_page(
                esc_html__('Authors', 'publishpress-authors'),
                esc_html__('Authors', 'publishpress-authors'),
                apply_filters('pp_multiple_authors_manage_authors_cap', 'ppma_manage_authors'),
                self::MENU_SLUG,
                '',
                'dashicons-groups',
                26
            );
        }

        /**
         * Add necessary things to the admin menu
         */
        public function action_admin_submenu()
        {
            global $submenu;
            $legacyPlugin = Factory::getLegacyPlugin();

            // Remove the author taxonomy from the all post types
            foreach ($submenu as $menu => $items) {
                if (is_array($items) && !empty($items)) {
                    foreach ($items as $key => $value) {
                        if (isset($value[2]) && strpos($value[2], 'edit-tags.php?taxonomy=author') !== false) {
                            unset($submenu[$menu][$key]);
                        }
                    }
                }
            }

            // Add the submenu to the PublishPress menu.
            add_submenu_page(
                self::MENU_SLUG,
                esc_html__('Authors', 'publishpress-authors'),
                esc_html__('Authors', 'publishpress-authors'),
                apply_filters('pp_multiple_authors_manage_authors_cap', 'ppma_manage_authors'),
                'edit-tags.php?taxonomy=author',
                __return_empty_string(),
                10
            );
        }

        public function redirect_to_edit_terms_page()
        {
            echo 'Redirecting...';
        }

        public function filter_custom_menu_order($menu_ord)
        {
            global $submenu;

            if (isset($submenu[self::MENU_SLUG])) {
                $currentSubmenu   = $submenu[self::MENU_SLUG];
                $newSubmenu       = [];
                $upgradeMenuSlugs = [];

                // Get the index for the menus, removing the first submenu which was automatically created by WP.
                $itemsToSort = [
                    'edit-tags.php?taxonomy=author'    => null,
                    'edit.php?post_type=ppmacf_field'  => null,
                    'edit.php?post_type=ppmacf_layout' => null,
                    'ppma-modules-settings'            => null,
                ];

                if (!defined('PUBLISHPRESS_AUTHORS_SKIP_VERSION_NOTICES')) {
                    $suffix           = \PPVersionNotices\Module\MenuLink\Module::MENU_SLUG_SUFFIX;
                    $upgradeMenuSlugs = [
                        'ppma-authors' . $suffix => null,
                    ];

                    $itemsToSort = array_merge($itemsToSort, $upgradeMenuSlugs);
                }

                foreach ($currentSubmenu as $index => $item) {
                    if (array_key_exists($item[2], $itemsToSort)) {
                        $itemsToSort[$item[2]] = $index;
                    }
                }

                // Authors
                if (isset($itemsToSort['edit-tags.php?taxonomy=author'])) {
                    $newSubmenu[] = $currentSubmenu[$itemsToSort['edit-tags.php?taxonomy=author']];

                    unset($currentSubmenu[$itemsToSort['edit-tags.php?taxonomy=author']]);
                }

                // Fields
                if (isset($itemsToSort['edit.php?post_type=ppmacf_field'])) {
                    $newSubmenu[] = $currentSubmenu[$itemsToSort['edit.php?post_type=ppmacf_field']];

                    unset($currentSubmenu[$itemsToSort['edit.php?post_type=ppmacf_field']]);
                }

                // Layouts
                if (isset($itemsToSort['edit.php?post_type=ppmacf_layout'])) {
                    $newSubmenu[] = $currentSubmenu[$itemsToSort['edit.php?post_type=ppmacf_layout']];

                    unset($currentSubmenu[$itemsToSort['edit.php?post_type=ppmacf_layout']]);
                }

                // Check if we have other menu items, except settings. They will be added to the end.
                if (count($currentSubmenu) >= 1) {
                    $itemsToIgnore = [
                        'ppma-authors',
                        'ppma-modules-settings',
                    ];

                    // Add the additional items
                    foreach ($currentSubmenu as $index => $item) {
                        if (in_array($item[2], $itemsToIgnore)) {
                            continue;
                        }

                        if (!array_key_exists($item[2], $itemsToSort)) {
                            $newSubmenu[] = $item;
                            unset($currentSubmenu[$index]);
                        }
                    }
                }

                // Settings
                if (isset($itemsToSort['ppma-modules-settings'])) {
                    $newSubmenu[] = $currentSubmenu[$itemsToSort['ppma-modules-settings']];

                    unset($currentSubmenu[$itemsToSort['ppma-modules-settings']]);
                }

                // Upgrade to Pro
                if (!defined('PUBLISHPRESS_AUTHORS_SKIP_VERSION_NOTICES')) {
                    $suffix = \PPVersionNotices\Module\MenuLink\Module::MENU_SLUG_SUFFIX;

                    foreach ($upgradeMenuSlugs as $index => $item) {
                        if (!is_null($itemsToSort[$index])) {
                            $newSubmenu[] = $currentSubmenu[$itemsToSort[$index]];
                        }
                    }
                }

                $submenu[self::MENU_SLUG] = $newSubmenu;
            }

            return $menu_ord;
        }

        /**
         * Print the content of the configure tab.
         */
        public function print_configure_view()
        {
            $container = Factory::get_container();
            $twig      = $container['twig'];

            echo $twig->render(
                'settings-tab.twig',
                [
                    'form_action'        => menu_page_url($this->module->settings_slug, false),
                    'options_group_name' => $this->module->options_group_name,
                    'module_name'        => $this->module->slug,
                ]
            );
        }

        /**
         * Register settings for notifications so we can partially use the Settings API
         * (We use the Settings API for form generation, but not saving)
         */
        public function register_settings()
        {
            /**
             * General
             */

            add_settings_section(
                $this->module->options_group_name . '_general',
                __return_false(),
                [$this, 'settings_section_general'],
                $this->module->options_group_name
            );

            do_action(
                'publishpress_authors_register_settings_before',
                $this->module->options_group_name,
                $this->module->options_group_name . '_general'
            );

            add_settings_field(
                'post_types',
                __('Add to these post types:', 'publishpress-authors'),
                [$this, 'settings_post_types_option'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_general'
            );

            add_settings_field(
                'author_for_new_users',
                __(
                    'Automatically create author profiles:',
                    'publishpress-authors'
                ),
                [$this, 'settings_author_for_new_users_option'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_general'
            );

            add_settings_field(
                'username_in_search_field',
                __(
                    'Show username in the search field:',
                    'publishpress-authors'
                ),
                [$this, 'settings_username_in_search_field'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_general'
            );

            add_settings_field(
                'default_author_for_new_posts',
                __(
                    'Default author for new posts:',
                    'publishpress-authors'
                ),
                [$this, 'settings_default_author_for_new_posts'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_general'
            );

            do_action('publishpress_authors_register_settings_after');

            /**
             * Display
             */

            add_settings_section(
                $this->module->options_group_name . '_display',
                __return_false(),
                [$this, 'settings_section_display'],
                $this->module->options_group_name
            );

            add_settings_field(
                'append_to_content',
                __('Show below the content:', 'publishpress-authors'),
                [$this, 'settings_append_to_content_option'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_display'
            );

            add_settings_field(
                'title_appended_to_content',
                __('Title for the author box:', 'publishpress-authors'),
                [$this, 'settings_title_appended_to_content_option'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_display'
            );

            add_settings_field(
                'layout',
                __('Layout:', 'publishpress-authors'),
                [$this, 'settings_layout_option'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_display'
            );

            add_settings_field(
                'color_scheme',
                __('Color scheme:', 'publishpress-authors'),
                [$this, 'settings_color_scheme_option'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_display',
                ['class' => 'ppauthors-color-scheme-field']
            );

            add_settings_field(
                'show_email_link',
                __('Show email link:', 'publishpress-authors'),
                [$this, 'settings_show_email_link_option'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_display'
            );

            add_settings_field(
                'show_site_link',
                __('Show site link:', 'publishpress-authors'),
                [$this, 'settings_show_site_link_option'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_display'
            );

            /**
             * Maintenance
             */

            add_settings_section(
                $this->module->options_group_name . '_maintenance',
                __return_false(),
                [$this, 'settings_section_maintenance'],
                $this->module->options_group_name
            );

            add_settings_field(
                'maintenance',
                __return_empty_string(),
                [$this, 'settings_maintenance_option'],
                $this->module->options_group_name,
                $this->module->options_group_name . '_maintenance'
            );

            do_action('pp_authors_register_settings');
        }

        public function settings_section_general()
        {
            echo '<input type="hidden" id="ppma-tab-general" />';
        }

        public function settings_section_display()
        {
            echo '<input type="hidden" id="ppma-tab-display" />';
        }

        public function settings_section_maintenance()
        {
            echo '<input type="hidden" id="ppma-tab-maintenance" />';
        }

        /**
         * Displays the field to allow select the post types for authors.
         */
        public function settings_post_types_option()
        {
            $legacyPlugin = Factory::getLegacyPlugin();

            $legacyPlugin->settings->helper_option_custom_post_type($this->module);
        }

        /**
         * Displays the field to choose display or not the author box at the
         * end of the content
         *
         * @param array
         */
        public function settings_append_to_content_option($args = [])
        {
            $id    = $this->module->options_group_name . '_append_to_content';
            $value = isset($this->module->options->append_to_content) ? $this->module->options->append_to_content : 'yes';

            echo '<label for="' . $id . '">';
            echo '<input type="checkbox" value="yes" id="' . $id . '" name="' . $this->module->options_group_name . '[append_to_content]" '
                . checked($value, 'yes', false) . ' />';
            echo '&nbsp;&nbsp;&nbsp;<span class="ppma_settings_field_description">' . esc_html__(
                    'This will display the authors box at the end of the content.',
                    'publishpress-authors'
                ) . '</span>';
            echo '</label>';
        }

        /**
         * Displays the field to choose the title for the author box at the
         * end of the content
         *
         * @param array
         */
        public function settings_title_appended_to_content_option($args = [])
        {
            $idSingle    = $this->module->options_group_name . '_title_appended_to_content';
            $singleValue = isset($this->module->options->title_appended_to_content) ? $this->module->options->title_appended_to_content : esc_html__(
                'Author',
                'publishpress-authors'
            );

            $idPlural    = $this->module->options_group_name . '_title_appended_to_content_plural';
            $pluralValue = isset($this->module->options->title_appended_to_content_plural) ? $this->module->options->title_appended_to_content_plural : esc_html__(
                'Authors',
                'publishpress-authors'
            );

            echo '<div class="ppma-settings-left-column">';
            echo '<label for="' . $idSingle . '">' . esc_html__('Single', 'publishpress-authors') . '</label>';
            echo '<input type="text" value="' . esc_attr(
                    $singleValue
                ) . '" id="' . $idSingle . '" name="' . $this->module->options_group_name . '[title_appended_to_content]" class="regular-text" />';
            echo '</div>';

            echo '<div class="ppma-settings-left-column">';
            echo '<label for="' . $idPlural . '">' . esc_html__('Plural', 'publishpress-authors') . '</label>';
            echo '<input type="text" value="' . esc_attr(
                    $pluralValue
                ) . '" id="' . $idPlural . '" name="' . $this->module->options_group_name . '[title_appended_to_content_plural]" class="regular-text" />';
            echo '</div>';
        }

        /**
         * @param array $args
         */
        public function settings_layout_option($args = [])
        {
            $id    = $this->module->options_group_name . '_layout';
            $value = isset($this->module->options->layout) ? $this->module->options->layout : Utils::getDefaultLayout();

            echo '<label for="' . $id . '">';

            echo '<select id="' . $id . '" name="' . $this->module->options_group_name . '[layout]">';

            $layouts = apply_filters('pp_multiple_authors_author_layouts', []);

            foreach ($layouts as $layout => $text) {
                $selected = $value === $layout ? 'selected="selected"' : '';
                echo '<option value="' . $layout . '" ' . $selected . '>' . $text . '</option>';
            }

            echo '</select>';
            echo '</label>';
        }

        /**
         * @param array $args
         */
        public function settings_color_scheme_option($args = [])
        {
            $id    = $this->module->options_group_name . '_color_scheme';
            $value = isset($this->module->options->color_scheme) ? $this->module->options->color_scheme : '#655997';

            echo '<label for="' . $id . '">';

                echo '<input type="text" class="color-picker" data-default-color="#655997" name="' . $this->module->options_group_name . '[color_scheme]" value="' . sanitize_text_field(
                $value
                ) . '"/>';

            echo '</label>';
        }

        /**
         * @param array $args
         */
        public function settings_author_for_new_users_option($args = [])
        {
            $id     = $this->module->options_group_name . '_author_for_new_users';
            $values = isset($this->module->options->author_for_new_users) ? $this->module->options->author_for_new_users : '';

            echo '<label for="' . $id . '">';

            echo '<select id="' . $id . '" name="' . $this->module->options_group_name . '[author_for_new_users][]" multiple="multiple" class="chosen-select">';

            $roles = get_editable_roles();

            foreach ($roles as $role => $data) {
                $selected = in_array($role, $values) ? 'selected="selected"' : '';
                echo '<option value="' . $role . '" ' . $selected . '>' . $data['name'] . '</option>';
            }

            echo '</select>';

            echo '<p class="ppma_settings_field_description">' . __(
                    'Author profiles can be mapped to WordPress user accounts. This option allows you to automatically create author profiles when users are created in these roles. You can also do this for existing users by clicking the "Create missed authors from role" button in the Maintenance tab.',
                    'publishpress-authors'
                );

            echo '</label>';
        }

        /**
         * @param array $args
         */
        public function settings_username_in_search_field($args = [])
        {
            $id    = $this->module->options_group_name . '_username_in_search_field';
            $value = isset($this->module->options->username_in_search_field) ? $this->module->options->username_in_search_field : '';

            echo '<label for="' . $id . '">';

            echo '<input type="checkbox" id="' . $id . '" name="' . $this->module->options_group_name . '[username_in_search_field]" value="yes" ' . ($value === 'yes' ? 'checked="checked"' : '') . '/>';

            echo '&nbsp;&nbsp;&nbsp;<span class="ppma_settings_field_description">'
                . esc_html__(
                    'If the Author is mapped to a WordPress user, this will display the authors\' "Display name" and their "Username". The default is to show only the "Display name". Showing the "Username" is useful if you have several authors with similar names.',
                    'publishpress-authors'
                )
                . '</span>';


            echo '</label>';
        }

        /**
         * Default author for new posts
         *
         * @param array $args
         */
        public function settings_default_author_for_new_posts()
        {
            $id    = $this->module->options_group_name . '_default_author_for_new_posts';
            $value = isset($this->module->options->default_author_for_new_posts) ? $this->module->options->default_author_for_new_posts : '';
            ?>
            <label for="<?php echo $id; ?>">
                <select data-value="<?php echo $value; ?>"
                        name="<?php echo $this->module->options_group_name . '[default_author_for_new_posts]'; ?>"
                        data-nonce="<?php echo esc_attr(wp_create_nonce('authors-search')); ?>"
                        class="default-authors-select2"
                        data-placeholder="<?php esc_attr_e('Search for an author', 'authors'); ?>" style="width: 350px">
                    <option value=""></option>
                    <?php
                    if (!empty($value)) {
                        $author = Author::get_by_term_id($value);
                        ?>
                        <option value="<?php echo $value; ?>"
                                selected="selected"><?php echo $author->display_name; ?></option>
                    <?php } ?>
                </select>

            </label>
            <?php
        }

        /**
         * Displays the field to choose display or not the email link/icon.
         *
         * @param array
         */
        public function settings_show_email_link_option($args = [])
        {
            $id    = $this->module->options_group_name . '_show_email_link';
            $value = isset($this->module->options->show_email_link) ? $this->module->options->show_email_link : 'yes';

            echo '<label for="' . $id . '">';
            echo '<input type="checkbox" value="yes" id="' . $id . '" name="' . $this->module->options_group_name . '[show_email_link]" '
                . checked($value, 'yes', false) . ' />';
            echo '&nbsp;&nbsp;&nbsp;<span class="ppma_settings_field_description">' . esc_html__(
                    'This will display the authors email in the author box.',
                    'publishpress-authors'
                ) . '</span>';
            echo '</label>';
        }

        /**
         * Displays the field to choose display or not the email link/icon.
         *
         * @param array
         */
        public function settings_show_site_link_option($args = [])
        {
            $id    = $this->module->options_group_name . '_show_site_link';
            $value = isset($this->module->options->show_site_link) ? $this->module->options->show_site_link : 'yes';

            echo '<label for="' . $id . '">';
            echo '<input type="checkbox" value="yes" id="' . $id . '" name="' . $this->module->options_group_name . '[show_site_link]" '
                . checked($value, 'yes', false) . ' />';
            echo '&nbsp;&nbsp;&nbsp; <span class="ppma_settings_field_description">' . esc_html__(
                    'This will display the authors site in the author box.',
                    'publishpress-authors'
                ) . '</span>';
            echo '</label>';
        }

        /**
         * Displays the button to reset the author terms.
         *
         * @param array
         */
        public function settings_maintenance_option($args = [])
        {
            $nonce     = wp_create_nonce('multiple_authors_maintenance');
            $base_link = esc_url(admin_url('/admin.php?page=ppma-modules-settings&ppma_action=%s&nonce=' . $nonce));

            $actions = [
                'create_post_authors' => [
                    'title'        => __('Create missed post authors', 'publishpress-authors'),
                    'description'  => 'This action is very helpful if you\'re installing PublishPress Authors on an existing WordPress site. This action analyzes all the posts on your site. If the action finds a WordPress user is set as an author, it will automatically share that data with PublishPress Authors.',
                    'button_label' => __('Create missed post authors', 'publishpress-authors'),
                ],

                'create_role_authors' => [
                    'title'        => __('Create missed authors from role', 'publishpress-authors'),
                    'description'  => 'This action is very helpful if you\'re installing PublishPress Authors on an existing WordPress site. This action finds all the users in a role and creates author profiles for them. You can choose the roles using the "Automatically create author profiles" setting.',
                    'button_label' => __('Create missed authors from role', 'publishpress-authors'),
                ],

                'sync_post_author' => [
                    'title'       => __('Update author field on posts', 'publishpress-authors'),
                    'description' => 'This action is useful if you\'re updating PublishPress Authors from versions lower or equals than 3.7.4. This action can help compatibility with some 3rd party themes and plugins.',
                    'button_link' => '',
                    'after'       => '<div id="publishpress-authors-sync-post-authors"></div>',
                ],

                'sync_author_slug' => [
                    'title'       => __('Synchronize Author slugs to User logins', 'publishpress-authors'),
                    'description' => 'For compatibility with PublishPress Permissions, each Author\'s slug needs to match their User login.',
                    'button_link' => '',
                    'after'       => '<div id="publishpress-authors-sync-author-slug"></div>',
                ],
            ];

            /**
             * @param array $actions
             */
            $actions = apply_filters('pp_authors_maintenance_actions', $actions);

            if (isset($GLOBALS['coauthors_plus']) && !empty($GLOBALS['coauthors_plus'])) {
                $actions['copy_coauthor_plus_data'] = [
                    'title'       => __('Copy Co-Authors Plus Data', 'publishpress-authors'),
                    'description' => 'This action will copy the authors from the plugin Co-Authors Plus allowing you to migrate to PublishPress Authors without losing any data. This action can be run multiple times.',
                    'button_link' => '',
                    'after'       => '<div id="publishpress-authors-coauthors-migration"></div>',
                ];
            }

            $actions['delete_mapped_authors'] = [
                'title'        => __('Delete Mapped Authors', 'publishpress-authors'),
                'description'  => 'This action can reset the PublishPress Authors data before using other maintenance options. It will delete all author profiles that are mapped to a WordPress user account. This will not delete the WordPress user accounts, but any links between the posts and multiple authors will be lost.',
                'button_label' => __('Delete all authors mapped to users', 'publishpress-authors'),
                'button_icon'  => 'dashicons-warning',
            ];

            $actions['delete_guest_authors'] = [
                'title'        => __('Delete Guest Authors', 'publishpress-authors'),
                'description'  => 'This action can reset the PublishPress Authors data before using other maintenance options. Guest authors are author profiles that are not mapped to a WordPress user account. This action will delete all guest authors.',
                'button_label' => __('Delete all guest authors', 'publishpress-authors'),
                'button_icon'  => 'dashicons-warning',
            ];

            echo '<div id="ppma_maintenance_settings">';

            echo '<p class="ppma_warning">' . __(
                    'Please be careful clicking these buttons. Before clicking, we recommend taking a site backup in case anything goes wrong.',
                    'publishpress-authors'
                );
            echo '</p>';

            foreach ($actions as $actionName => $actionInfo) {
                if (isset($actionInfo['button_link'])) {
                    $link = $actionInfo['button_link'];
                } else {
                    $link = sprintf($base_link, $actionName);
                }

                echo '<div class="ppma_maintenance_action_wrapper">';
                echo '<h4>' . $actionInfo['title'] . '</h4>';
                echo '<p class="ppma_settings_field_description">' . $actionInfo['description'] . '</p>';

                if (!empty($link)) {
                    echo '<a href="' . $link . '" class="button - secondary button - danger ppma_maintenance_button" id="' . $actionName . '">';

                    if (isset($actionInfo['button_icon'])) {
                        echo '<span class="dashicons ' . $actionInfo['button_icon'] . '"></span>';
                    }

                    echo $actionInfo['button_label'] . '</a>';
                }

                if (isset($actionInfo['after'])) {
                    echo $actionInfo['after'];
                }

                echo '</div>';
            }

            echo '</div>';
        }

        /**
         * Validate data entered by the user
         *
         * @param array $new_options New values that have been entered by the user
         *
         * @return array $new_options Form values after they've been sanitized
         */
        public function validate_module_settings($new_options, $module_name)
        {
            if ($module_name !== 'multiple_authors') {
                return $new_options;
            }

            // Whitelist validation for the post type options
            if (!isset($new_options['post_types'])) {
                $new_options['post_types'] = [];
            }

            $new_options['post_types'] = $this->clean_post_type_options(
                $new_options['post_types'],
                $this->module->post_type_support
            );

            if (!isset($new_options['append_to_content'])) {
                $new_options['append_to_content'] = 'no';
            }

            if (!isset($new_options['author_for_new_users']) || !is_array($new_options['author_for_new_users'])) {
                $new_options['author_for_new_users'] = [];
            }

            if (!isset($new_options['show_email_link'])) {
                $new_options['show_email_link'] = 'no';
            }

            if (!isset($new_options['show_site_link'])) {
                $new_options['show_site_link'] = 'no';
            }

            if (!isset($new_options['username_in_search_field'])) {
                $new_options['username_in_search_field'] = 'no';
            }

            if (isset($new_options['layout'])) {
                /**
                 * Filter the list of available layouts.
                 */
                $layouts = apply_filters('pp_multiple_authors_author_layouts', []);

                if (!array_key_exists($new_options['layout'], $layouts)) {
                    $new_options['layout'] = Utils::getDefaultLayout();
                }
            }

            /**
             * @param array $newOptions
             * @param string $moduleName
             *
             * @return array
             */
            return apply_filters('pp_authors_validate_module_settings', $new_options, $module_name);
        }

        /**
         * @param array $tabs
         *
         * @return array
         */
        public function settings_tab($tabs)
        {
            $tabs = array_merge(
                $tabs,
                [
                    '#ppma-tab-general'     => __('General', 'publishpress-authors'),
                    '#ppma-tab-display'     => __('Display', 'publishpress-authors'),
                    '#ppma-tab-maintenance' => __('Maintenance', 'publishpress-authors'),
                ]
            );

            return $tabs;
        }

        public function theAuthorPostsLink($link)
        {
            $newLink   = '';
            $postID    = get_the_id();
            $isArchive = empty($postID) && Util::isAuthor();
            $authors   = get_multiple_authors($postID, true, $isArchive);

            foreach ($authors as $author) {
                if (!empty($newLink)) {
                    $newLink .= ', ';
                }

                $newLink .= '<a href="' . $author->link . '" title="' . $author->display_name
                    . '" rel="author" itemprop="author" itemscope="itemscope" itemtype="https://schema.org/Person">'
                    . $author->display_name . '</a>';
            }

            return $newLink;
        }

        /**
         * Filters the list of receivers in the notification workflows provided
         * by the improved notifications add-on.
         *
         * @param array $receivers
         * @param int $workflowPostID
         * @param array $args
         *
         * @return array
         */
        public function filter_workflow_receiver_post_authors($receivers, $workflowPostID, $args)
        {
            if (!function_exists('get_multiple_authors')) {
                include_once PP_AUTHORS_SRC_PATH . 'functions/template-tags.php';
            }

            $authors = get_multiple_authors($args['params']['post_id']);

            if (!empty($authors)) {
                foreach ($authors as $author) {
                    if (!$author->is_guest() && !in_array($author->user_id, $receivers)) {
                        $receivers[] = $author->user_id;
                    }

                    if ($author->is_guest() && !empty($author->user_email)) {
                        $receivers[] = $author->user_email;
                    }
                }
            }

            return $receivers;
        }

        /**
         * Over hide some strings for Authors.
         *
         * @param string $translation Translated text.
         * @param string $text Text to translate.
         * @param string $domain Text domain. Unique identifier for retrieving translated strings.
         *
         * @return string
         */
        public function filter_get_text($translation, $text, $domain)
        {
            global $pagenow;

            if (!in_array($pagenow, ['edit-tags.php', 'term.php'])) {
                return $translation;
            }

            $taxonomy = isset($_GET['taxonomy']) ? $_GET['taxonomy'] : null;

            if ('author' !== $taxonomy) {
                return $translation;
            }

            // The description of the field Name
            if ('default' === $domain && 'The name is how it appears on your site.' === $translation) {
                $translation = __(
                    'This is how the author’s name will appears on your site.',
                    'publishpress-authors'
                );
            }

            // The name of field Slug, convert to Author URL
            if (isset($_GET['taxonomy']) && 'author' === $_GET['taxonomy']) {
                if ('default' === $domain) {
                    if ('Slug' === $translation) {
                        $translation = __('Author URL', 'publishpress-authors');
                    }

                    if ('The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.' === $translation) {
                        $translation = __(
                            'This forms part of the URL for the author’s profile page. If you choose a Mapped User, this URL is taken from the user’s account and can not be changed.',
                            'publishpress-authors'
                        );
                    }
                }
            }

            return $translation;
        }

        /**
         * @param $classes
         *
         * @return mixed
         */
        public function filter_body_class($classes)
        {
            if (Util::isAuthor()) {
                if ($brokenItem = array_search('author-', $classes)) {
                    unset($classes[$brokenItem]);
                }

                $authors = get_multiple_authors(0, true, true);

                if (!empty($authors)) {
                    $author = $authors[0];

                    $classes[] = (is_object($author) && $author->is_guest()) ? 'guest-author' : 'not-guest-author';
                }
            }

            return $classes;
        }

        /**
         * @param int $id
         * @return false|Author
         */
        private function get_author_by_id($id)
        {
            $author = false;

            if (empty($id)) {
                return false;
            }

            if (!is_numeric($id)) {
                $author = Author::get_by_term_slug($id);
            } elseif ($id > 0) {
                $author = Author::get_by_user_id($id);
            } else {
                $author = Author::get_by_term_id($id);
            }

            return $author;
        }

        /**
         * @param $meta_key
         * @param $value
         * @param $author_id
         *
         * @return mixed
         */
        private function get_author_meta($meta_key, $value, $author_id)
        {
            $author = $this->get_author_by_id($author_id);

            if (is_object($author)) {
                $value = $author->get_meta($meta_key);
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_headline($value, $user_id)
        {
            // Try to fix the headline for the guest authors page
            if (empty($value) && empty($user_id) && is_archive() && Util::isAuthor()) {
                $authors = get_multiple_authors(0, true, true);

                if (!empty($authors)) {
                    $author = $authors[0];

                    $value = $author->display_name;
                }
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_aim($value, $user_id)
        {
            $author_meta = $this->get_author_meta('aim', $value, $user_id);

            if (!is_null($author_meta)) {
                $value = $author_meta;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_description($value, $user_id)
        {
            $author_meta = $this->get_author_meta('description', $value, $user_id);

            if (!is_null($author_meta)) {
                $value = $author_meta;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_display_name($value, $user_id)
        {
            $author = $this->get_author_by_id($user_id);


            if (is_object($author)) {
                $value = $author->display_name;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_first_name($value, $user_id)
        {
            $author = $this->get_author_by_id($user_id);

            if (is_object($author)) {
                $value = $author->first_name;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_last_name($value, $user_id)
        {
            $author = $this->get_author_by_id($user_id);

            if (is_object($author)) {
                $value = $author->last_name;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_ID($value, $user_id)
        {
            $author = $this->get_author_by_id($user_id);

            if (is_object($author)) {
                $value = $author->ID;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_jabber($value, $user_id)
        {
            $author_meta = $this->get_author_meta('jabber', $value, $user_id);

            if (!is_null($author_meta)) {
                $value = $author_meta;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_nickname($value, $user_id)
        {
            $author_meta = $this->get_author_meta('nickname', $value, $user_id);

            if (!is_null($author_meta)) {
                $value = $author_meta;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_user_description($value, $user_id)
        {
            $author_meta = $this->get_author_meta('user_description', $value, $user_id);

            if (!is_null($author_meta)) {
                $value = $author_meta;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_user_email($value, $user_id)
        {
            $author = $this->get_author_by_id($user_id);

            if (is_object($author)) {
                $value = $author->user_email;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_user_nicename($value, $user_id)
        {
            $author = $this->get_author_by_id($user_id);

            if (is_object($author)) {
                $value = $author->user_nicename;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_user_url($value, $user_id)
        {
            $author = $this->get_author_by_id($user_id);

            if (is_object($author)) {
                $value = $author->user_url;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_yim($value, $user_id)
        {
            $author_meta = $this->get_author_meta('yim', $value, $user_id);

            if (!is_null($author_meta)) {
                $value = $author_meta;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_facebook($value, $user_id)
        {
            $author_meta = $this->get_author_meta('facebook', $value, $user_id);

            if (!is_null($author_meta)) {
                $value = $author_meta;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_twitter($value, $user_id)
        {
            $author_meta = $this->get_author_meta('twitter', $value, $user_id);

            if (!is_null($author_meta)) {
                $value = $author_meta;
            }

            return $value;
        }

        /**
         * @param string $value The value of the metadata.
         * @param int $user_id The user ID for the value.
         *
         * @return mixed
         */
        public function filter_author_metadata_instagram($value, $user_id)
        {
            $author_meta = $this->get_author_meta('instagram', $value, $user_id);

            if (!is_null($author_meta)) {
                $value = $author_meta;
            }

            return $value;
        }

        /**
         * @param array $args Arguments passed to get_avatar_data(), after processing.
         * @param mixed $id_or_email The Gravatar to retrieve. Accepts a user ID, Gravatar MD5 hash,
         *                           user email, WP_User object, WP_Post object, or WP_Comment object.
         */
        public function filter_pre_get_avatar_data($args, $id_or_email)
        {
            // Stop if they are looking for the default avatar, otherwise we can start an infinity loop when get_avatar_data is called again.
            if (empty($id_or_email)) {
                return $args;
            }

            $termId = 0;

            if (is_numeric($id_or_email)) {
                $id_or_email = (int)$id_or_email;

                if ($id_or_email < 0) {
                    $termId = $id_or_email * -1;
                } else {
                    $author = Author::get_by_user_id($id_or_email);

                    if (is_object($author)) {
                        $termId = $author->term_id;
                    }
                }
            } else {
                if ($id_or_email instanceof WP_Comment) {
                    $id_or_email = $id_or_email->comment_author_email;
                }

                $termId = Author_Utils::get_author_term_id_by_email($id_or_email);
            }

            if (!empty($termId)) {
                $url = Author_Utils::get_avatar_url($termId);

                if (!empty($url)) {
                    $args['url'] = $url;
                }
            }

            return $args;
        }

        /**
         * Handle the action to reset author therms.
         * Remove all authors and regenerate based on posts' authors and the setting to automatically create authors
         * for specific roles.
         */
        public function handle_maintenance_task()
        {
            $actions = [
                'delete_mapped_authors',
                'delete_guest_authors',
                'create_post_authors',
                'create_role_authors',
                'copy_coauthor_plus_data',
                'sync_post_author',
                'sync_author_slug',
            ];

            if (!isset($_GET['ppma_action']) || isset($_GET['author_term_reset_notice'])
                || !in_array($_GET['ppma_action'], $actions, true)
            ) {
                return;
            }


            $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
            if (!wp_verify_nonce($nonce, 'multiple_authors_maintenance')) {
                wp_redirect(
                    admin_url('/admin.php?page=ppma-modules-settings&author_term_reset_notice=fail'),
                    301
                );

                return;
            }

            try {
                $action = sanitize_key($_GET['ppma_action']);

                do_action('multiple_authors_' . $action);

                wp_redirect(
                    admin_url('/admin.php?page=ppma-modules-settings&author_term_reset_notice=success'),
                    301
                );
            } catch (Exception $e) {
                wp_redirect(
                    admin_url('/admin.php?page=ppma-modules-settings&author_term_reset_notice=fail'),
                    301
                );
            }
        }

        public function action_delete_mapped_authors()
        {
            global $wpdb;

            $query = "
                SELECT tt . term_id
                FROM {$wpdb->term_taxonomy} AS tt
                WHERE
                tt . taxonomy = 'author'
                AND (SELECT COUNT(*)
                    FROM {$wpdb->termmeta} AS tm
                    WHERE tm . term_id = tt . term_id AND tm . meta_key = 'user_id'
                    ) > 0";

            $terms = $wpdb->get_results($query);

            if (!empty($terms)) {
                foreach ($terms as $term) {
                    wp_delete_term($term->term_id, 'author');
                }
            }
        }

        public function action_delete_guest_authors()
        {
            global $wpdb;

            $query = "
                SELECT tt . term_id
                FROM {$wpdb->term_taxonomy} AS tt
                WHERE
                    tt . taxonomy = 'author'
                    AND (
                        (SELECT tm . meta_value
                        FROM {$wpdb->termmeta} AS tm
                        WHERE tt . term_id = tm . term_id AND tm . meta_key = 'user_id'
                        ) = 0
                        OR (SELECT tm . meta_value
                            FROM {$wpdb->termmeta} AS tm
                            WHERE tt . term_id = tm . term_id AND tm . meta_key = 'user_id') IS null
                    )";

            $terms = $wpdb->get_results($query);

            if (!empty($terms)) {
                foreach ($terms as $term) {
                    wp_delete_term($term->term_id, 'author');
                }
            }
        }

        public function action_create_post_authors()
        {
            // Do not execute the post_author migration to post terms if Co-Authors Plus is activated.
            // The user need to manually run the Co-Authors migration task before running this again.
            if (!$this->isCoAuthorsPlusActivated()) {
                Installer::convert_post_author_into_taxonomy();
                Installer::add_author_term_for_posts();
            }
        }

        private function isCoAuthorsPlusActivated()
        {
            return (isset($GLOBALS['coauthors_plus']) && !empty($GLOBALS['coauthors_plus']));
        }


        public function action_create_role_authors()
        {
            // Create authors for users in the taxonomies selected for automatic creation of authors.
            $legacyPlugin = Factory::getLegacyPlugin();

            $roles = (array)$legacyPlugin->modules->multiple_authors->options->author_for_new_users;

            // Check if we have any role selected to create an author for the new user.
            if (!empty($roles)) {
                // Get users from roles
                $args  = [
                    'role__in' => $roles,
                ];
                $users = get_users($args);

                if (!empty($users)) {
                    foreach ($users as $user) {
                        // Create author for this user
                        Author::create_from_user($user->ID);
                    }
                }
            }
        }

        public function action_copy_coauthor_plus_data()
        {
            if (!class_exists('CoAuthorsIterator')) {
                return [];
            }

            $i      = new CoAuthorsIterator();
            $output .= $separators['before'];
            $i->iterate();

            do {
                $author_text = '';

                if ('tag' === $type) {
                    $author_text = $tag($tag_args);
                } elseif ('field' === $type && isset($i->current_author->$tag)) {
                    $author_text = $i->current_author->$tag;
                } elseif ('callback' === $type && is_callable($tag)) {
                    $author_text = call_user_func($tag, $i->current_author);
                }

                // Fallback to user_nicename if we get something empty
                if (empty($author_text)) {
                    $author_text = $i->current_author->user_nicename;
                }

                // Append separators
                if ($i->count() - $i->position == 1) { // last author or only author
                    $output .= $author_text;
                } elseif ($i->count() - $i->position == 2) { // second to last
                    $output .= $author_text . $separators['betweenLast'];
                } else {
                    $output .= $author_text . $separators['between'];
                }
            } while ($i->iterate());

            // Co-Authors sometimes don't have a taxonomy term for the author, but uses the post_author value instead.
            Installer::convert_post_author_into_taxonomy();
            Installer::add_author_term_for_posts();
        }

        /**
         *
         */
        public function handle_maintenance_task_notice()
        {
            if (!isset($_GET['author_term_reset_notice'])) {
                return;
            }

            if ($_GET['author_term_reset_notice'] === 'fail') {
                echo '<div class="notice notice - error is - dismissible">';
                echo '<p>' . __(
                        'Error. Author terms could not be reseted.',
                        'publishpress-authors'
                    ) . '</p>';
                echo '</div>';

                return;
            }

            if ($_GET['author_term_reset_notice'] === 'success') {
                echo '<div class="notice notice - success is - dismissible">';
                echo '<p>' . __('Maintenance completed successfully.', 'publishpress-authors') . '</p>';
                echo '</div>';

                return;
            }
        }

        /**
         * Fix the upload of media for posts when the user is a secondary author and can't edit others' posts.
         *
         * @param $caps
         * @param $cap
         * @param $user_id
         * @param $args
         *
         * @return mixed
         */
        public function filter_map_meta_cap($caps, $cap, $user_id, $args)
        {
            if (in_array($cap, ['edit_post', 'edit_others_posts']) && in_array('edit_others_posts', $caps, true)) {
                if (isset($args[0])) {
                    $post_id = (int)$args[0];

                    // Check if the user is an author for the current post
                    if (is_multiple_author_for_post($user_id, $post_id)) {
                        foreach ($caps as &$item) {
                            // If he is an author for this post we should only check edit_posts.
                            if ($item === 'edit_others_posts') {
                                $item = 'edit_posts';
                            }
                        }
                    }

                    $caps = apply_filters('pp_authors_filter_map_meta_cap', $caps, $cap, $user_id, $post_id);
                }
            }

            return $caps;
        }

        /**
         * @param bool $isAuthor
         * @param int $userId
         * @param int $postId
         *
         * @return bool
         */
        public function filter_is_author_of_post($isAuthor, $userId, $postId)
        {
            $postAuthors = get_multiple_authors($postId, false);
            $userId      = (int)$userId;

            if (empty($userId)) {
                return false;
            }

            foreach ($postAuthors as $author) {
                if ((int)$author->user_id === $userId) {
                    return true;
                }
            }

            return false;
        }

        public function filter_post_authors_names($names, $postID)
        {
            $names = [];

            $authors = get_multiple_authors($postID, false, false);

            foreach ($authors as $author) {
                $names[] = $author->display_name;
            }

            return $names;
        }

        public function admin_enqueue_scripts()
        {
            if (isset($_GET['page']) && $_GET['page'] === 'ppma-modules-settings') {
                wp_enqueue_script(
                    'multiple-authors-settings',
                    PP_AUTHORS_ASSETS_URL . 'js/settings.js',
                    ['jquery'],
                    PP_AUTHORS_VERSION
                );

                if (!empty($_REQUEST['ppma_tab'])) {
                    wp_localize_script('multiple-authors-settings', 'ppmaSettings', [
                        'tab' => 'ppma-tab-' . sanitize_key($_REQUEST['ppma_tab']),
                        'runScript' => !empty($_REQUEST['ppma_maint']) ? sanitize_key($_REQUEST['ppma_maint']) : '',
                    ]);
                }

                wp_enqueue_script(
                    'publishpress-authors-sync-post-author',
                    PP_AUTHORS_URL . 'src/assets/js/sync-post-author.min.js',
                    [
                        'react',
                        'react-dom',
                        'jquery',
                        'multiple-authors-settings',
                        'wp-element',
                        'wp-hooks',
                        'wp-i18n',
                    ],
                    PP_AUTHORS_VERSION
                );

                wp_localize_script(
                    'publishpress-authors-sync-post-author',
                    'ppmaSyncPostAuthor',
                    [
                        'nonce'     => wp_create_nonce('sync_post_author'),
                        'chunkSize' => PUBLISHPRESS_AUTHORS_SYNC_POST_AUTHOR_CHUNK_SIZE,
                    ]
                );

                wp_enqueue_script(
                    'publishpress-authors-sync-author-slug',
                    PP_AUTHORS_URL . '/src/assets/js/sync-author-slug.min.js',
                    [
                        'react',
                        'react-dom',
                        'jquery',
                        'multiple-authors-settings',
                        'wp-element',
                        'wp-hooks',
                        'wp-i18n',
                    ],
                    PP_AUTHORS_VERSION
                );

                wp_localize_script(
                    'publishpress-authors-sync-author-slug',
                    'ppmaSyncAuthorSlug',
                    [
                        'nonce'     => wp_create_nonce('sync_author_slug'),
                        'chunkSize' => PUBLISHPRESS_AUTHORS_SYNC_AUTHOR_SLUG_CHUNK_SIZE,
                    ]
                );

                wp_enqueue_style(
                    'publishpress-authors-data-migration-box',
                    PP_AUTHORS_URL . 'src/modules/multiple-authors/assets/css/data-migration-box.css',
                    false,
                    PP_AUTHORS_VERSION
                );

                if ($this->isCoAuthorsPlusActivated()) {
                    wp_enqueue_script(
                        'publishpress-authors-coauthors-migration',
                        PP_AUTHORS_URL . 'src/assets/js/coauthors-migration.min.js',
                        [
                            'react',
                            'react-dom',
                            'jquery',
                            'multiple-authors-settings',
                            'wp-element',
                            'wp-hooks',
                            'wp-i18n',
                        ],
                        PP_AUTHORS_VERSION
                    );

                    wp_localize_script(
                        'publishpress-authors-coauthors-migration',
                        'ppmaCoAuthorsMigration',
                        [
                            'nonce' => wp_create_nonce('migrate_coauthors'),
                        ]
                    );
                }

                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script(
                    'ppauthors-color-picker',
                    PP_AUTHORS_ASSETS_URL . 'js/color-picker.js',
                    ['wp-color-picker'],
                    false,
                    true
                );
            }
        }

        /**
         * @return bool
         */
        public function migrate_legacy_settings()
        {
            if (get_option('publishpress_multiple_authors_settings_migrated_3_0_0')) {
                return false;
            }

            $legacyOptions = get_option('publishpress_multiple_authors_options');
            if (!empty($legacyOptions)) {
                update_option('multiple_authors_multiple_authors_options', $legacyOptions);
            }

            update_option('publishpress_multiple_authors_settings_migrated_3_0_0', 1);
        }

        /**
         * Customize/fix the author byline output for the GeneratePress theme.
         *
         * @param $output
         *
         * @return false|string
         */
        public function generatepress_author_output($output)
        {
            global $post;

            $layout = apply_filters('pp_multiple_authors_generatepress_box_layout', 'inline');

            ob_start();
            do_action('pp_multiple_authors_show_author_box', false, $layout, false, true, $post->ID);

            $output = ob_get_clean();

            return $output;
        }

        private function getTotalOfNotMigratedCoAuthors()
        {
            $terms = get_terms(
                [
                    'taxonomy'   => 'author',
                    'hide_empty' => false,
                    'number'     => 0,
                    'meta_query' => [
                        [
                            'key'     => 'ppma-migrated',
                            'compare' => 'NOT EXISTS',
                        ],
                    ],
                ]
            );

            return count($terms);
        }

        public function getCoauthorsMigrationData()
        {
            if (!wp_verify_nonce($_GET['nonce'], 'migrate_coauthors')) {
                wp_send_json_error(null, 403);
            }

            // nonce: migrate_coauthors
            wp_send_json(
                [
                    'total' => $this->getTotalOfNotMigratedCoAuthors(),
                ]
            );
        }

        private function getCoAuthorGuestAuthorBySlug($slug)
        {
            $posts = get_posts(
                [
                    'name'        => $slug,
                    'post_type'   => 'guest-author',
                    'post_status' => 'publish',
                ]
            );

            if (!empty($posts)) {
                return $posts[0];
            }

            return false;
        }

        public function getSyncPostAuthorData()
        {
            global $wpdb;

            if (!wp_verify_nonce($_GET['nonce'], 'sync_post_author')) {
                wp_send_json_error(null, 403);
            }

            $postTypes = array_values(Util::get_post_types_for_module($this->module));
            $postTypes = '"' . implode('","', $postTypes) . '"';

            $result = $wpdb->get_results(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ({$postTypes}) AND post_status NOT IN ('trash')",
                ARRAY_N
            );

            $postIds = array_map(
                function ($value) {
                    return (int)$value[0];
                },
                $result
            );

            set_transient('publishpress_authors_sync_post_author_ids', $postIds, 24 * 60 * 60);

            // nonce: migrate_coauthors
            wp_send_json(
                [
                    'total' => count($postIds),
                ]
            );
        }

        public function getSyncAuthorSlugData()
        {
            global $wpdb;

            if (!wp_verify_nonce($_GET['nonce'], 'sync_author_slug')) {
                wp_send_json_error(null, 403);
            }

            $authorsToUpdate = Utils::detect_author_slug_mismatch();

            set_transient('publishpress_authors_sync_author_slug_ids', $authorsToUpdate, 24 * 60 * 60);

            // nonce: migrate_coauthors
            wp_send_json(
                [
                    'total' => count($authorsToUpdate),
                ]
            );
        }

        private function getCoAuthorUserAuthorBySlug($slug)
        {
            return get_user_by('slug', $slug);
        }

        public function migrateCoAuthorsData()
        {
            if (!wp_verify_nonce($_GET['nonce'], 'migrate_coauthors')) {
                wp_send_json_error(null, 403);
            }

            $keyForNotMigrated = 'ppma-migrated';

            $termsToMigrate = get_terms(
                [
                    'taxonomy'   => 'author',
                    'hide_empty' => false,
                    'number'     => 5,
                    'meta_query' => [
                        [
                            'key'     => $keyForNotMigrated,
                            'compare' => 'NOT EXISTS',
                        ],
                    ],
                ]
            );

            if (!empty($termsToMigrate)) {
                foreach ($termsToMigrate as $term) {
                    $author = $this->getCoAuthorGuestAuthorBySlug($term->slug);

                    if (!empty($author)) {
                        // Guest author
                        if ($author instanceof WP_Post) {
                            $name        = get_post_meta($author->ID, 'cap-display_name', true);
                            $firstName   = get_post_meta($author->ID, 'cap-first_name', true);
                            $lastName    = get_post_meta($author->ID, 'cap-last_name', true);
                            $email       = get_post_meta($author->ID, 'cap-user_email', true);
                            $url         = get_post_meta($author->ID, 'cap-website', true);
                            $description = get_post_meta($author->ID, 'cap-description', true);

                            update_term_meta($term->term_id, 'first_name', $firstName);
                            update_term_meta($term->term_id, 'last_name', $lastName);
                            update_term_meta($term->term_id, 'user_email', $email);
                            update_term_meta($term->term_id, 'user_url', $url);
                            update_term_meta($term->term_id, 'description', $description);
                            wp_update_term(
                                $term->term_id,
                                'author',
                                [
                                    'name' => $name,
                                    'slug' => str_replace('cap-', '', $term->slug),
                                ]
                            );

                            $avatar = get_post_meta($author->ID, '_thumbnail_id', true);
                            if (!empty($avatar)) {
                                update_term_meta($term->term_id, 'avatar', $avatar);
                            }
                        }
                    } else {
                        $author = $this->getCoAuthorUserAuthorBySlug(str_replace('cap-', '', $term->slug));

                        // User author
                        if ($author instanceof WP_User) {
                            $description = get_user_meta($author->ID, 'description', true);

                            update_term_meta($term->term_id, 'first_name', $author->first_name);
                            update_term_meta($term->term_id, 'last_name', $author->last_name);
                            update_term_meta($term->term_id, 'user_email', $author->user_email);
                            update_term_meta($term->term_id, 'user_url', $author->user_url);
                            update_term_meta($term->term_id, 'description', $description);
                            update_term_meta($term->term_id, 'user_id', $author->ID);

                            wp_update_term(
                                $term->term_id,
                                'author',
                                [
                                    'name' => $author->display_name,
                                    'slug' => str_replace('cap-', '', $author->user_nicename),
                                ]
                            );
                        }
                    }

                    update_term_meta($term->term_id, $keyForNotMigrated, 1);
                }
            }

            // nonce: migrate_coauthors
            wp_send_json(
                [
                    'success' => true,
                    'total'   => $this->getTotalOfNotMigratedCoAuthors(),
                ]
            );
        }

        public function syncPostAuthor()
        {
            if (!wp_verify_nonce($_GET['nonce'], 'sync_post_author')) {
                wp_send_json_error(null, 403);
            }

            $postIdsToSync = get_transient('publishpress_authors_sync_post_author_ids');

            $totalMigrated = 0;

            if (!empty($postIdsToSync)) {
                $chunkSize = isset($_GET['chunkSize']) ? (int)$_GET['chunkSize'] : 10;

                reset($postIdsToSync);
                for ($i = 0; $i < $chunkSize; $i++) {
                    $postId = (int)current($postIdsToSync);

                    if (!empty($postId)) {
                        $authors = get_multiple_authors($postId);
                        Utils::sync_post_author_column($postId, $authors);
                        $totalMigrated++;
                    }
                    unset($postIdsToSync[key($postIdsToSync)]);
                }
            }

            set_transient('publishpress_authors_sync_post_author_ids', $postIdsToSync, 24 * 60 * 60);

            wp_send_json(
                [
                    'success'       => true,
                    'totalMigrated' => $totalMigrated,
                ]
            );
        }

        public function syncAuthorSlug()
        {
            if (!wp_verify_nonce($_GET['nonce'], 'sync_author_slug')) {
                wp_send_json_error(null, 403);
            }

            $termToSync = get_transient('publishpress_authors_sync_author_slug_ids');

            $totalMigrated = 0;

            if (!empty($termToSync)) {
                $chunkSize = isset($_GET['chunkSize']) ? (int)$_GET['chunkSize'] : 10;

                reset($termToSync);
                for ($i = 0; $i < $chunkSize; $i++) {
                    $term = current($termToSync);

                    if (!empty($term)) {
                        Utils::sync_author_slug_to_user_nicename([$term]);
                        $totalMigrated++;
                    }
                    unset($termToSync[key($termToSync)]);
                }
            }

            set_transient('publishpress_authors_sync_author_slug_ids', $termToSync, 24 * 60 * 60);

            wp_send_json(
                [
                    'success'       => true,
                    'totalMigrated' => $totalMigrated,
                ]
            );
        }

        public function deactivateCoAuthorsPlus()
        {
            if (!wp_verify_nonce($_GET['nonce'], 'migrate_coauthors')) {
                wp_send_json_error(null, 403);
            }

            deactivate_plugins('co-authors-plus/co-authors-plus.php');

            // nonce: migrate_coauthors
            wp_send_json(
                [
                    'deactivated' => true,
                ]
            );
        }

        public function finishCoAuthorsMigration()
        {
            if (!wp_verify_nonce($_GET['nonce'], 'migrate_coauthors')) {
                wp_send_json_error(null, 403);
            }

            // Co-Authors sometimes don't have a taxonomy term for the author, but uses the post_author value instead.
            Installer::convert_post_author_into_taxonomy();
            Installer::add_author_term_for_posts();

            do_action('publishpress_authors_flush_cache');

            // nonce: migrate_coauthors
            wp_send_json(
                [
                    'success' => true,
                ]
            );
        }

        public function finishSyncPostAuthor()
        {
            if (!wp_verify_nonce($_GET['nonce'], 'sync_post_author')) {
                wp_send_json_error(null, 403);
            }

            delete_transient('publishpress_authors_sync_post_author_ids');

            do_action('publishpress_authors_flush_cache');

            wp_send_json(
                [
                    'success' => true,
                ]
            );
        }

        public function finishSyncAuthorSlug()
        {
            if (!wp_verify_nonce($_GET['nonce'], 'sync_author_slug')) {
                wp_send_json_error(null, 403);
            }

            delete_transient('publishpress_authors_sync_author_slug_ids');

            update_option('publishpress_multiple_authors_usernicename_sync', 1);

            do_action('publishpress_authors_flush_cache');

            wp_send_json(
                [
                    'success' => true,
                ]
            );
        }

        public function handle_deleted_user($id)
        {
            // Check if we have an author for the user
            $author = $this->get_author_by_id($id);

            if (false !== $author) {
                Author::convert_into_guest_author($author->term_id);
            }
        }

        public function coauthorsMigrationNotice()
        {
            global $pagenow;

            if (!isset($GLOBALS['coauthors_plus']) || empty($GLOBALS['coauthors_plus'])) {
                return;
            }

            $requirements = [
                (isset($_GET['page']) && $_GET['page'] === 'ppma-modules-settings') ? 1 : 0,
                ($pagenow === 'edit-tags.php' && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'author') ? 1 : 0
            ];

            if (array_sum($requirements) === 0) {
                return;
            }

            if (!current_user_can('manage_options')) {
                return;
            }

            if (get_option('publishpress_authors_dismiss_coauthors_migration_notice') == 1) {
                return;
            }

            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php _e('It looks like you have Co-Authors Plus installed.', 'publishpress-authors'); ?>
                    <a href="https://publishpress.com/knowledge-base/co-authors-plus/"><?php _e(
                            'Please click here and read this guide!',
                            'publishpress-authors'
                        ); ?></a>
                    |
                    <a href="<?php echo add_query_arg(['action' => 'dismiss_coauthors_migration_notice']); ?>"><?php _e(
                            'Dismiss',
                            'publishpress-authors'
                        ); ?></a>
                </p>
            </div>
            <?php
        }

        public function dismissCoAuthorsMigrationNotice()
        {
            if (!isset($_GET['action']) || $_GET['action'] !== 'dismiss_coauthors_migration_notice') {
                return;
            }

            update_option('publishpress_authors_dismiss_coauthors_migration_notice', 1);
        }

        public function permissionsSyncNotice($args = [])
        {
            global $pagenow;

            // Only request the script if also running a PublishPress Permissions version which supports posts query integration
            if (!defined('PRESSPERMIT_VERSION') || version_compare(constant('PRESSPERMIT_VERSION'), '3.4-alpha', '<') || defined('PRESSPERMIT_DISABLE_AUTHORS_JOIN')) {
                return;
            }

            // Display the notice on Authors and Permissions plugin screens
            $is_pp_plugin_page = (isset($_GET['page']) && in_array($_GET['page'], ['ppma-modules-settings', 'presspermit-settings', 'presspermit-groups']))
            || ('edit-tags.php' == $pagenow && !empty($_REQUEST['taxonomy']) && ('author' == $_REQUEST['taxonomy']));

            $requirements = [
                in_array($pagenow, ['plugins.php', 'edit.php', 'edit-tags.php']),
                $is_pp_plugin_page,
            ];

            // Only display the notice on specified admin pages
            if (array_sum($requirements) === 0) {
                return;
            }

            // This request is launching Sync script directly
            if (!empty($_REQUEST['ppma_maint']) && ('ppma_maint=sync-user-login' == $_REQUEST['ppma_maint'])) {
                return;
            }

            // Sync script already run
            if (get_option('publishpress_multiple_authors_usernicename_sync')) {
                return;
            }

            // Notice is non-dismissible on Authors and Permissions plugin screens
            $ignore_dismissal = $is_pp_plugin_page || !empty($args['ignore_dismissal']);

            // This notice is not forced, and has been dismissed
            if (!$ignore_dismissal && get_option('publishpress_authors_dismiss_permissions_sync_notice') == 1) {
                return;
            }

            // User cannot run this script
            if (!current_user_can('ppma_manage_authors')) {
                return;
            }

            ?>
            <div class="updated">
                <p>
                    <?php _e('PublishPress Authors needs a database update for Permissions integration.', 'publishpress-authors'); ?>
                    &nbsp;<a href="<?php echo admin_url('admin.php?page=ppma-modules-settings&ppma_tab=maintenance&ppma_maint=sync-user-login#publishpress-authors-sync-author-slug');?>"><?php _e(
                            'Click to run the update now',
                            'publishpress-authors'
                        ); ?></a>
                    <?php if (!$ignore_dismissal):?>
                    &nbsp;|&nbsp;
                    <a href="<?php echo add_query_arg(['action' => 'dismiss_permissions_sync_notice']); ?>"><?php _e(
                            'Dismiss',
                            'publishpress-authors'
                        ); ?></a>
                    <?php endif;?>
                </p>
            </div>
            <?php
        }

        public function dismissPermissionsSyncNotice()
        {
            if (!isset($_GET['action']) || $_GET['action'] !== 'dismiss_permissions_sync_notice') {
                return;
            }

            update_option('publishpress_authors_dismiss_permissions_sync_notice', 1);
        }

        /**
         * @param $results
         * @param $searchText
         */
        public function publishpressSearchAuthors($results, $searchText)
        {
            $authors = Admin_Ajax::get_possible_authors_for_search($searchText);

            if (!empty($authors)) {
                $results = [];

                foreach ($authors as $author) {
                    $results[] = [
                        'id'   => $author['term'] * -1,
                        'text' => $author['text'],
                    ];
                }
            }

            return $results;
        }

        public function publishpressAuthorCanEditPosts($canEdit, $authorId)
        {
            try {
                if ($authorId > 0) {
                    $author = Author::get_by_user_id($authorId);
                    $user   = $author->get_user_object();

                    if (is_object($user)) {
                        $canEdit = $user->has_cap('edit_posts');
                    }
                } else {
                    $author = Author::get_by_term_id($authorId * -1);
                    $user   = $author->get_user_object();

                    if (is_object($user)) {
                        $canEdit = $author->is_guest() ? true : $user->has_cap('edit_posts');
                    }
                }
            } catch (Exception $e) {
            }

            return $canEdit;
        }

        public function publishpressCalendarAfterCreatePost($postId, $postAuthorId)
        {
            $author = null;
            if ($postAuthorId > 0) {
                $author = Author::get_by_user_id($postAuthorId);
            } else {
                $author = Author::get_by_term_id(abs($postAuthorId));
            }

            if (!empty($author)) {
                Utils::set_post_authors($postId, [$author]);

                do_action('publishpress_authors_flush_cache');
            }
        }

        public function publishpressCalendarDefaultAuthor($defaultAuthor)
        {
            $default_author_setting = isset($this->module->options->default_author_for_new_posts) ?
                $this->module->options->default_author_for_new_posts : '';

            if (!empty($default_author_setting)) {
                $defaultAuthor = -$default_author_setting;
            }

            return $defaultAuthor;
        }

        public function publishpressAuthorFilterSelectedOption($option, $authorId)
        {
            if ($authorId < 0) {
                $author = Author::get_by_term_id($authorId);
                $option = '<option value="' . esc_attr($authorId) . '" selected="selected">' . esc_html($author->display_name) . '</option>';
            }

            return $option;
        }

        public function publishpressPostQueryArgs($args)
        {
            // Add support for guest authors in the post query
            $selectedPostTypes = array_values(Util::get_post_types_for_module($this->module));

            if (isset($args['author']) && $args['author'] < 0) {
                if (isset($args['tax_query'])) {
                    $args['tax_query']['relation'] = 'AND';
                }

                $authorId = abs($args['author']);
                unset($args['author']);

                $args['tax_query'][] = [
                    'taxonomy' => 'author',
                    'field' => 'id',
                    'terms' => [$authorId],
                ];
            }

            return $args;
        }

        public function publishpressContentOverviewAuthorColumn($authorName, $post)
        {
            $selectedPostTypes = array_values(Util::get_post_types_for_module($this->module));

            if (in_array($post->post_type, $selectedPostTypes)) {
                $authors = get_multiple_authors($post->ID);

                $authorNamesArray = [];
                foreach ($authors as $author)
                {
                    $authorNamesArray[] = $author->display_name;
                }

                $authorName = implode(', ', $authorNamesArray);
            }

            return $authorName;
        }

        public function actionSetPostAuthors($postId, $authors)
        {
            Utils::set_post_authors($postId, $authors);

            do_action('publishpress_authors_flush_cache');
        }

        public function userProfileUpdate($userId, $oldUserData)
        {
            $author = Author::get_by_user_id($userId);

            if (is_object($author) && !is_wp_error($author)) {
                $user = get_user_by('id', $userId);

                global $wpdb, $wp_rewrite;

                $wpdb->update($wpdb->terms, ['slug' => $user->user_nicename], ['term_id' => $author->term_id]);

                if (is_object($wp_rewrite)) {
                    $wp_rewrite->flush_rules();
                }
            }
        }
    }
}
