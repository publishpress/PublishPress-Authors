<?php

/**
 * @package   MultipleAuthors
 * @author    PublishPress <help@publishpress.com>
 * @copyright Copyright (C) 2018 PublishPress. All rights reserved.
 * @license   GPLv2 or later
 * @since     1.1.0
 */

namespace MultipleAuthors\Classes;

use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Factory;
use WP_Post;
use WP_REST_Response;


/**
 * Class Post_Editor
 *
 * Based on Byline' class Post_Editor.
 *
 * @package MultipleAuthors\Classes
 */
class Post_Editor
{
    /**
     * Register callbacks for managing custom columns
     */
    public static function action_admin_init()
    {
        foreach (Content_Model::get_author_supported_post_types() as $post_type) {
            if (Utils::is_post_type_enabled($post_type)) {
                add_filter("manage_{$post_type}_posts_columns", [__CLASS__, 'filter_manage_posts_columns']);
                add_action(
                    "manage_{$post_type}_posts_custom_column",
                    [__CLASS__, 'action_manage_posts_custom_column'],
                    10,
                    2
                );
            }
        }

        add_action('bulk_edit_custom_box', [__CLASS__, 'add_author_bulk_quick_edit_custom_box'], 10, 2);
        add_action('quick_edit_custom_box', [__CLASS__, 'add_author_bulk_quick_edit_custom_box'], 10, 2);
        add_action('wp_ajax_save_bulk_edit_authors', [__CLASS__, 'save_bulk_edit_authors'], 10, 2);
    }

    /**
     * Add author quick edit custom box.
     */
    public static function add_author_bulk_quick_edit_custom_box($column_name, $post_type)
    {
        if (Utils::is_post_type_enabled($post_type) && $column_name === 'authors') {
            ?>
            <fieldset class="inline-edit-col-left">
                <div class="inline-edit-col">
                    <label style="display: inline-flex">
                        <span class="title">Authors</span>
                    </label>
                    <?php
                    echo self::get_rendered_authors_selection([], false, true); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    ?>
                </div>
            </fieldset>
            <?php
        }
    }

    /**
     * Filter post columns to include the Author column
     *
     * @param array $columns All post columns with their titles.
     *
     * @return array
     */
    public static function filter_manage_posts_columns($columns)
    {
        if (!Utils::is_post_type_enabled()) {
            return $columns;
        }

        $new_columns = [];

        foreach ($columns as $key => $value) {
            if ('author' === $key) {
                $key   = 'authors';
                $value = __('Authors', 'publishpress-authors');
            }

            $new_columns[$key] = $value;
        }

        return $new_columns;
    }

    /**
     * Render the authors for a post in the table
     *
     * @param string $column Name of the column.
     * @param int $post_id ID of the post being rendered.
     */
    public static function action_manage_posts_custom_column($column, $post_id)
    {
        if ('authors' === $column) {
            // We need to ignore the cache for following call when this method were called after saved the post in a
            // quick edit operation, otherwise the authors column will show old values.
            $authors = get_post_authors($post_id, true);

            $post_type = get_post_type($post_id);
            $post      = get_post($post_id);

            $authors_str         = [];
            $showedPostAuthorUser = false;

            foreach ($authors as $author) {
                if (is_a($author, 'WP_User')) {
                    $author = Author::get_by_user_id($author->ID);
                }

                if (is_object($author)) {
                    $args = [
                        'author_name' => $author->slug,
                    ];
                    if ('post' !== $post_type) {
                        $args['post_type'] = $post_type;
                    }
                    $url = add_query_arg(array_map('rawurlencode', $args), admin_url('edit.php'));

                    $classes = [
                        'author_name'
                    ];

                    if ($author->user_id == $post->post_author) {
                        $classes[]           = 'author_in_post';
                        $showedPostAuthorUser = true;
                    }

                    $authors_str[] = sprintf(
                        '<a href="%s" data-author-term-id="%d" data-author-slug="%s" data-author-display-name="%s" data-author-is-guest="%s" class="%s">%s</a>',
                        esc_url($url),
                        esc_attr($author->term_id),
                        esc_attr($author->slug),
                        esc_attr($author->display_name),
                        esc_attr($author->is_guest() ? 1 : 0),
                        esc_attr(implode(' ', $classes)),
                        esc_html($author->display_name)
                    );
                }
            }

            if (empty($authors_str)) {
                $authors_str[] = sprintf(
                    '<span class="current-post-author-warning">%s</span>',
                    esc_html__('No author term', 'publishpress-authors')
                );
            }

            echo implode(', ', $authors_str); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

            if (!$showedPostAuthorUser) {
                if (empty($post->post_author)) {
                    echo sprintf('<span class="current-post-author-warning">[%s]</span>', esc_html__('"post_author" is empty', 'publishpress-authors'));
                } else {
                    $user = get_user_by('ID', $post->post_author);

                    if (is_a($user, 'WP_User')) {
                        echo sprintf('<span style="display:none;" class="current-post-author-off">[%s]</span>', esc_html($user->display_name));
                    }
                }
            }
        }
    }

    /**
     * Deregister the author meta box, and register Author meta boxes
     */
    public static function action_add_meta_boxes_late()
    {
        if (!Utils::is_valid_page()) {
            return;
        }

        $supportedPostTypes = Content_Model::get_author_supported_post_types();

        foreach ($supportedPostTypes as $post_type) {
            remove_meta_box('authordiv', $post_type, 'normal');
            // @todo only register meta box when user can assign authors
            add_meta_box(
                'authors',
                __('Authors', 'publishpress-authors'),
                [__CLASS__, 'render_authors_metabox'],
                $post_type,
                'side',
                'default'
            );
        }
    }

    /**
     * Render the Author meta box.
     */
    public static function render_authors_metabox()
    {
        if (!Utils::is_valid_page()) {
            return;
        }

        $authors = get_post_authors();

        echo self::get_rendered_authors_selection($authors, false);  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Get rendered authors selection.
     */
    public static function get_rendered_authors_selection($authors, $showAvatars = true, $bulkEdit = false)
    {
        $classes = [
            'authors-list',
        ];
        if (current_user_can(get_taxonomy('author')->cap->assign_terms)) {
            $classes[] = 'authors-current-user-can-assign';
        }
        ?>
        <ul class="<?php echo esc_attr(implode(' ', $classes)); ?>">
            <?php
            if (!empty($authors)) {
                foreach ($authors as $author) {
                    if (!is_object($author) || is_wp_error($author)) {
                        continue;
                    }

                    $display_name = $author->display_name;
                    $term         = is_a($author, 'WP_User') ? 'u' . $author->ID : $author->term_id;

                    $isGuest = 0;
                    if (is_a($author, Author::class)) {
                        $isGuest = $author->is_guest() ? 1 : 0;
                    }

                    $args = [
                        'display_name' => $display_name,
                        'term'         => $term,
                        'is_guest'     => $isGuest,
                    ];

                    if ($showAvatars) {
                        $args['avatar'] = $author->get_avatar(20);
                    }

                    echo self::get_rendered_author_partial($args);  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                }
            }
            ?>
        </ul>
        <?php
        wp_nonce_field('authors-save', 'authors-save');

        if (current_user_can(get_taxonomy('author')->cap->assign_terms)) {
            ?>
            <select data-nonce="<?php
            echo esc_attr(wp_create_nonce('authors-search')); ?>"
                    id="publishpress-authors-author-select"
                    class="authors-select2 authors-search"
                    data-placeholder="<?php
                    esc_attr_e('Search for an author', 'publishpress-authors'); ?>" style="width: 100%">
                <option></option>
            </select>
            <script type="text/html" id="tmpl-authors-author-partial">
                <?php
                echo self::get_rendered_author_partial(  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    [
                        'display_name' => '{{ data.display_name }}',
                        'term'         => '{{ data.id }}',
                        'is_guest'     => '{{ data.is_guest }}',
                    ]
                );
                ?>
            </script>
            <?php
            $post         = get_post();
            $userAuthor   = get_user_by('ID', $post->post_author);
            $postAuthorId = $post->post_author;

            $legacyPlugin           = Factory::getLegacyPlugin();
            $fallbackAuthor         = isset($legacyPlugin->modules->multiple_authors->options->fallback_user_for_guest_post) ?
                (int)$legacyPlugin->modules->multiple_authors->options->fallback_user_for_guest_post : 0;
    
            if ($fallbackAuthor > 0) {
                $postAuthorId = $fallbackAuthor;
                $userAuthor   = Author::get_by_user_id($postAuthorId);

            }
            ?>
            <?php if (!$bulkEdit) : ?>
                <div class="ppma-authors-display-option-wrapper">
                    <input name="ppma_save_disable_author_box" type="hidden" value="1" />
                    <input name="ppma_disable_author_box" 
                            id="ppma_disable_author_box" 
                            value="1" 
                            type="checkbox"
                            <?php checked((int)get_post_meta($post->ID, 'ppma_disable_author_box', true), 1); ?>
                        />
                    <label for="ppma_disable_author_box">
                        <?php echo esc_html_e('Disable post author box display?', 'publishpress-authors'); ?>
                    </label>
                </div>
            <?php endif; ?>
            <div style="display: none">
                <div id="publishpress-authors-user-author-wrapper">
                    <hr>
                    <label for="publishpress-authors-user-author-select"><?php
                        echo esc_html__(
                            'This option is showing because you do not have a WordPress user selected as an author. For some tasks, it can be helpful to have a user selected here. This user will not be visible on the front of your site.',
                            'publishpress-authors'
                        ); ?></label>
                    <select id="publishpress-authors-user-author-select" data-nonce="<?php
                    echo esc_attr(wp_create_nonce('authors-user-search')); ?>"
                            class="authors-select2 authors-user-search"
                            data-placeholder="<?php
                            esc_attr_e('Search for an user', 'publishpress-authors'); ?>" style="width: 100%"
                            name="fallback_author_user">
                        <option value="<?php echo (int)$postAuthorId; ?>">
                            <?php echo is_object($userAuthor) ? esc_html($userAuthor->display_name) : ''; ?>
                        </option>
                    </select>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Add author filter to admin post filter
     *
     * @return void
     */
    public static function post_author_filter_field()
    {
        $post_type = isset($_GET['post_type']) ? sanitize_key($_GET['post_type']) : 'post';
        
        if (Utils::is_post_type_enabled($post_type)) {

            $userAuthor = false;
            $authorSlug = false;
            if (isset($_GET['author_name'])) {
                $authorSlug = sanitize_key($_GET['author_name']);
            } elseif (isset($_GET['ppma_author'])) {
                $authorSlug = sanitize_key($_GET['ppma_author']);
            }

            if ($authorSlug) {
                $userAuthor = Author::get_by_term_slug($authorSlug);
                if (!$userAuthor) {
                    $userAuthor = get_user_by('slug', $authorSlug);
                }
            }
            ?>
            <select data-nonce="<?php
                echo esc_attr(wp_create_nonce('authors-user-search')); ?>"
                    class="authors-select2 authors-user-slug-search"
                    data-placeholder="<?php
                    esc_attr_e('All Authors', 'publishpress-authors'); ?>" style="width: 150px"
                    name="author_name">
                    <?php if ($userAuthor && is_object($userAuthor)) : ?>
                        <option value="<?php echo esc_attr($userAuthor->user_nicename); ?>">
                            <?php echo esc_html($userAuthor->display_name); ?>
                        </option>
                <?php else : ?>
                    <option></option>
                <?php endif; ?>
            </select>
            <?php
        }
    }

    /**
     * Get a rendered author partial
     *
     * @param array $args Arguments to render in the partial.
     */
    private static function get_rendered_author_partial($args = [])
    {
        $defaults = [
            'display_name' => '',
            'avatar'       => '',
            'term'         => '',
            'is_guest'     => 0,
        ];

        $args     = array_merge($defaults, $args);
        ob_start();
        ?>
        <li id="publishpress-authors-author-<?php
        echo esc_attr($args['term']); ?>" data-term-id="<?php
        echo esc_attr($args['term']); ?>" data-is-guest="<?php
        echo esc_attr($args['is_guest']); ?>" class="ui-sortable-handle publishpress-authors-author">
            <span class="author-remove">
                <span class="dashicons dashicons-no-alt"></span>
            </span>
            <?php
            if (!empty($args['avatar'])) : ?>
                <?php
                echo $args['avatar'];  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <?php
            endif; ?>
            <span class="display-name"><?php echo esc_html($args['display_name']); ?></span>
            <input type="hidden" name="authors[]" value="<?php echo esc_attr($args['term']); ?>">
        </li>
        <?php
        return ob_get_clean();
    }

    /**
     * Save bulk edit authors via ajax
     */
    public static function save_bulk_edit_authors()
    {
        if (!isset($_POST['post_ids'])) {
            return;
        }

        $post_ids = array_map('sanitize_key', $_POST['post_ids']);
        if (!isset($_POST['bulkEditNonce'])
            || !wp_verify_nonce(sanitize_key($_POST['bulkEditNonce']), 'bulk-edit-nonce')
            || !current_user_can(get_taxonomy('author')->cap->assign_terms)
        ) {
            return;
        }

        $firstPost = get_post($post_ids[0]);
        if (!Utils::is_post_type_enabled($firstPost->post_type)) {
            return;
        }

        $authors = isset($_POST['authors_ids']) ? array_map('sanitize_text_field', $_POST['authors_ids']) : [];
        $authors = self::remove_dirty_authors_from_authors_arr($authors);

        $fallbackUserId = isset($_POST['fallback_author_user']) ? (int)$_POST['fallback_author_user'] : null;

        if (!empty($post_ids) && !empty($authors)) {
            foreach ($post_ids as $post_id) {
                Utils::set_post_authors($post_id, $authors, true, $fallbackUserId);
            }

            do_action('publishpress_authors_flush_cache');
        }

        wp_send_json_success(true, 200);
    }

    /**
     * Handle saving of the Author meta box
     *
     * @param int $post_id ID for the post being saved.
     * @param WP_Post $post Object for the post being saved.
     *
     * @return mixed
     */
    public static function action_save_post_authors_metabox($post_id, $post)
    {
        global $wpdb;

        if (!in_array($post->post_type, Content_Model::get_author_supported_post_types(), true)) {
            return;
        }

        if (
            !isset($_POST['authors-save'])
            || !wp_verify_nonce(sanitize_key($_POST['authors-save']), 'authors-save')
            || !current_user_can(get_taxonomy('author')->cap->assign_terms)
        ) {
            return;
        }

        $authors = isset($_POST['authors']) ? Utils::sanitizeArray($_POST['authors']) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $authors = self::remove_dirty_authors_from_authors_arr($authors);

        $fallbackUserId = isset($_POST['fallback_author_user']) ? (int)$_POST['fallback_author_user'] : null;
        $disableAuthorBox = isset($_POST['ppma_disable_author_box']) ? (int)$_POST['ppma_disable_author_box'] : 0;

        Utils::set_post_authors($post_id, $authors, true, $fallbackUserId);
        if (isset($_POST['ppma_save_disable_author_box']) && (int)$_POST['ppma_save_disable_author_box'] > 0) {
            update_post_meta($post_id, 'ppma_disable_author_box', $disableAuthorBox);
        }

        do_action('publishpress_authors_flush_cache');
    }

    /**
     * Remove dirty authors from authors array
     *
     * @access private
     *
     * @param array $authors_arr The authors array that should
     *                           be filtered from dirty authors.
     *
     * @return array The filtered authors array
     */
    private static function remove_dirty_authors_from_authors_arr($authors_arr)
    {
        $dirty_authors = $authors_arr;
        $authors       = [];
        foreach ($dirty_authors as $dirty_author) {
            if (is_numeric($dirty_author)) {
                $authors[] = Author::get_by_term_id($dirty_author);
            } elseif ('u' === $dirty_author[0]) {
                $user_id = (int)substr($dirty_author, 1);
                $author  = Author::get_by_user_id($user_id);
                if (!$author) {
                    $author = Author::create_from_user($user_id);
                    if (is_wp_error($author)) {
                        continue;
                    }
                }
                $authors[] = $author;
            }
        }
        return $authors;
    }

    /**
     * Assign a author term when a post is initially created
     *
     * @param int $post_id Post ID.
     * @param WP_Post $post Post object.
     * @param bool $update Whether this is an update.
     */
    public static function action_save_post_set_initial_author($post_id, $post, $update)
    {
        if ($update) {
            return;
        }

        if (!in_array($post->post_type, Content_Model::get_author_supported_post_types(), true)) {
            return;
        }

        $defaultAuthor = false;

        $legacyPlugin           = Factory::getLegacyPlugin();
        $defaultAuthorSetting = isset($legacyPlugin->modules->multiple_authors->options->default_author_for_new_posts) ?
            $legacyPlugin->modules->multiple_authors->options->default_author_for_new_posts : '';

        if (!empty($defaultAuthorSetting)) {
            $defaultAuthor = Author::get_by_term_id($defaultAuthorSetting);
        } elseif ($post->post_author) {
            $defaultAuthor = Author::get_by_user_id($post->post_author);
        }

        /**
         * Filter the default author assigned to the post.
         *
         * @param mixed $defaultAuthor Default author, as calculated by plugin.
         * @param WP_Post $post Post object.
         */
        $defaultAuthor = apply_filters('authors_default_author', $defaultAuthor, $post);

        if (empty($defaultAuthor) || ! is_object($defaultAuthor)) {
            return;
        }

        /*
         * If the user can't edit others posts, we shouldn't set another user as author for the post.
         * That could bring back a bug that blocks them to create new posts.
         */
        if (! current_user_can('edit_others_posts') && ! $defaultAuthor->is_guest()) {
            return;
        }

        Utils::set_post_authors($post_id, [$defaultAuthor]);

        do_action('publishpress_authors_flush_cache');
    }

    public static function remove_core_author_field()
    {
        $postTypes = Content_Model::get_author_supported_post_types();

        foreach ($postTypes as $postType) {
            if (Utils::is_post_type_enabled($postType)) {
                add_filter("rest_prepare_{$postType}", [__CLASS__, 'rest_remove_action_assign_author']);
            }
        }
    }

    /**
     * Filters the post data for a REST API response removing the
     * `wp:action-assign-author` rel from the response so the
     * default post author control doesn't get shown on the block
     * editor post editing screen.
     *
     * Based on code from humanmade/authorship.
     *
     * @param WP_REST_Response $response
     *
     * @return WP_REST_Response
     */
    public static function rest_remove_action_assign_author($response)
    {
        $links = $response->get_links();

        if (isset($links['https://api.w.org/action-assign-author'])) {
            $response->remove_link('https://api.w.org/action-assign-author');
        }

        return $response;
    }
}
