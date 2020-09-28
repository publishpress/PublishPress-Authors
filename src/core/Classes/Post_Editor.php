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
                // add_action('bulk_edit_custom_box', [__CLASS__, 'add_author_bulk_quick_edit_custom_box'], 10, 2);
                add_action('quick_edit_custom_box', [__CLASS__, 'add_author_bulk_quick_edit_custom_box'], 10, 2);
            }
        }
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
                        <span class="title">Post Author</span>
                    </label>
                    <?php
                    $authors = multiple_authors_get_all_authors();
                    echo self::get_rendered_authors_selection($authors, false);
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
        $new_columns = [];
        if (!Utils::is_post_type_enabled()) {
            return $columns;
        }

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
     * @param string $column  Name of the column.
     * @param int    $post_id ID of the post being rendered.
     */
    public static function action_manage_posts_custom_column($column, $post_id)
    {
        if ('authors' === $column) {
            $authors     = get_multiple_authors($post_id, true, false, true);
            $post_type   = get_post_type($post_id);
            $authors_str = [];
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
                    $url           = add_query_arg(array_map('rawurlencode', $args), admin_url('edit.php'));
                    $authors_str[] = '<a href="' . esc_url($url) . '">' . esc_html($author->display_name) . '</a>';
                }
            }

            if (empty($authors_str)) {
                $authors_str[] = '<span aria-hidden="true">—</span><span class="screen-reader-text">' . __(
                        'No author',
                        'publishpress-authors'
                    ) . '</span>';
            }

            echo implode(', ', $authors_str);
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

        $authors = get_multiple_authors();

        echo self::get_rendered_authors_selection($authors, false);
    }

    /**
     * Get rendered authors selection.
     */
    public static function get_rendered_authors_selection($authors, $showAvatars = true)
    {
        $classes = [
            'authors-list',
        ];
        if (current_user_can(get_taxonomy('author')->cap->assign_terms)) {
            $classes[] = 'authors-current-user-can-assign';
        }
        ?>
        <ul class="<?php echo(implode(' ', $classes)); ?>">
            <?php
            if (!empty($authors)) {
                foreach ($authors as $author) {
                    $display_name = $author->display_name;
                    $term         = is_a($author, 'WP_User') ? 'u' . $author->ID : $author->term_id;

                    $args = [
                        'display_name' => $display_name,
                        'term'         => $term,
                    ];

                    if ($showAvatars) {
                        $args['avatar'] = $author->get_avatar(20);
                    }

                    echo self::get_rendered_author_partial($args);
                }
            }
            ?>
        </ul>
        <?php
        wp_nonce_field('authors-save', 'authors-save');

        if (current_user_can(get_taxonomy('author')->cap->assign_terms)) {
            ?>
            <select data-nonce="<?php echo esc_attr(wp_create_nonce('authors-search')); ?>"
                    class="authors-select2 authors-search"
                    data-placeholder="<?php esc_attr_e('Search for an author', 'authors'); ?>" style="width: 100%">
                <option></option>
            </select>
            <script type="text/html" id="tmpl-authors-author-partial">
                <?php
                echo self::get_rendered_author_partial(
                    [
                        'display_name' => '{{ data.display_name }}',
                        'term'         => '{{ data.term }}',
                    ]
                );
                ?>
            </script>
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
        ];
        $args     = array_merge($defaults, $args);
        ob_start();
        ?>
        <li>
            <span class="author-remove">
                <span class="dashicons dashicons-no-alt"></span>
            </span>
            <?php if (!empty($args['avatar'])) : ?>
                <?php echo $args['avatar']; ?>
            <?php endif; ?>
            <span class="display-name"><?php echo wp_kses_post($args['display_name']); ?></span>
            <input type="hidden" name="authors[]" value="<?php echo esc_attr($args['term']); ?>">
        </li>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle saving of the Author meta box
     *
     * @param int     $post_id ID for the post being saved.
     * @param WP_Post $post    Object for the post being saved.
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
            || !wp_verify_nonce($_POST['authors-save'], 'authors-save')
            || !current_user_can(get_taxonomy('author')->cap->assign_terms)
        ) {
            return;
        }

        $selected_authors = isset($_POST['authors']) ? $_POST['authors'] : [];
        $authors       = [];
        foreach ($selected_authors as $selected_author) {
            if (is_numeric($selected_author)) {
                $authors[] = Author::get_by_term_id($selected_author);
            } elseif ('u' === $selected_author[0]) {
                $user_id = (int)substr($selected_author, 1);
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

        Utils::set_post_authors($post_id, $authors);

        if (empty($authors)) {
            $wpdb->update(
                $wpdb->posts,
                [
                    'post_author' => get_current_user_id(),
                ],
                [
                    'ID' => $post_id,
                ]
            );
            clean_post_cache($post_id);
        }
    }

    /**
     * Assign a author term when a post is initially created
     *
     * @param int     $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @param bool    $update  Whether this is an update.
     */
    public static function action_save_post_set_initial_author($post_id, $post, $update)
    {
        if ($update) {
            return;
        }

        if (!in_array($post->post_type, Content_Model::get_author_supported_post_types(), true)) {
            return;
        }

        $default_author = false;

        $legacyPlugin = Factory::getLegacyPlugin();
        $default_author_setting = isset($legacyPlugin->modules->multiple_authors->options->default_author_for_new_posts) ?
            $legacyPlugin->modules->multiple_authors->options->default_author_for_new_posts : '';

        if (!empty($default_author_setting)) {
            $default_author = Author::get_by_term_id($default_author_setting);
        } elseif ($post->post_author) {
            $default_author = Author::get_by_user_id($post->post_author);
        }

        /**
         * Filter the default author assigned to the post.
         *
         * @param mixed $default_author Default author, as calculated by plugin.
         * @param WP_Post $post Post object.
         */
        $default_author = apply_filters('authors_default_author', $default_author, $post);
        if ($default_author) {
            Utils::set_post_authors($post_id, [$default_author]);
        }
    }
}
