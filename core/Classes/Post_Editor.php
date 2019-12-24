<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthors\Classes;

use MultipleAuthors\Classes\Objects\Author;

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
                add_action("manage_{$post_type}_posts_custom_column", [__CLASS__, 'action_manage_posts_custom_column'],
                    10, 2);
            }
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
        foreach ($columns as $key => $title) {
            $new_columns[$key] = $title;
            if ('title' === $key) {
                $new_columns['authors'] = __('Authors', 'publishpress-authors');
            }

            if ('author' === $key) {
                unset($new_columns[$key]);
            }
        }

        return $new_columns;
    }

    /**
     * Render the authors for a post in the table
     *
     * @param string  $column  Name of the column.
     * @param integer $post_id ID of the post being rendered.
     */
    public static function action_manage_posts_custom_column($column, $post_id)
    {
        if ('authors' !== $column) {
            return;
        }
        $authors     = get_multiple_authors($post_id);
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
            $authors_str[] = '<span aria-hidden="true">—</span><span class="screen-reader-text">' . __('No author',
                    'publishpress-authors') . '</span>';
        }

        echo implode(', ', $authors_str);
    }

    /**
     * Deregister the author meta box, and register Author meta boxes
     */
    public static function action_add_meta_boxes_late()
    {
        if ( ! Utils::is_valid_page()) {
            return;
        }

        $supportedPostTypes = Content_Model::get_author_supported_post_types();

        foreach ($supportedPostTypes as $post_type) {

            remove_meta_box('authordiv', $post_type, 'normal');
            // @todo only register meta box when user can assign authors
            add_meta_box('authors', __('Authors', 'publishpress-authors'),
                [__CLASS__, 'render_authors_metabox'], $post_type, 'side', 'default');
        }

    }

    /**
     * Render the Author meta box.
     */
    public static function render_authors_metabox()
    {
        if ( ! Utils::is_valid_page()) {
            return;
        }

        $authors = get_multiple_authors();

        $classes = [
            'authors-list',
        ];
        if (current_user_can(get_taxonomy('author')->cap->assign_terms)) {
            $classes[] = 'authors-current-user-can-assign';
        }
        ?>
        <ul class="<?php echo esc_attr(implode(' ', $classes)); ?>">
            <?php
            if ( ! empty($authors)) {
                foreach ($authors as $author) {
                    $display_name = $author->display_name;
                    $term         = is_a($author, 'WP_User') ? 'u' . $author->ID : $author->term_id;
                    echo self::get_rendered_author_partial([
                        'display_name' => $display_name,
                        'avatar'       => $author->get_avatar(20),
                        'term'         => $term,
                    ]);
                }
            }
            ?>
        </ul>
        <?php wp_nonce_field('authors-save', 'authors-save'); ?>
        <?php if (current_user_can(get_taxonomy('author')->cap->assign_terms)) : ?>
        <select data-nonce="<?php echo esc_attr(wp_create_nonce('authors-search')); ?>"
                class="authors-select2 authors-search"
                data-placeholder="<?php esc_attr_e('Search for an author', 'authors'); ?>" style="width: 100%">
            <option></option>
        </select>
        <script type="text/html" id="tmpl-authors-author-partial">
            <?php
            echo self::get_rendered_author_partial([
                'display_name' => '{{ data.display_name }}',
                'avatar'       => '{{{ data.avatar }}}',
                'term'         => '{{ data.term }}',
            ]);
            ?>
        </script>
    <?php
    endif;
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
            <span class="author-remove"><span class="dashicons dashicons-no-alt"></span></span>
            <?php echo $args['avatar']; ?>
            <span class="display-name"><?php echo wp_kses_post($args['display_name']); ?></span>
            <input type="hidden" name="authors[]" value="<?php echo esc_attr($args['term']); ?>">
        </li>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle saving of the Author meta box
     *
     * @param integer $post_id ID for the post being saved.
     * @param WP_Post $post    Object for the post being saved.
     */
    public static function action_save_post_authors_metabox($post_id, $post)
    {
        global $wpdb;

        if ( ! in_array($post->post_type, Content_Model::get_author_supported_post_types(), true)) {
            return;
        }

        if ( ! isset($_POST['authors-save'])
             || ! wp_verify_nonce($_POST['authors-save'], 'authors-save')
             || ! current_user_can(get_taxonomy('author')->cap->assign_terms)) {
            return;
        }

        $dirty_authors = isset($_POST['authors']) ? $_POST['authors'] : [];
        $authors       = [];
        foreach ($dirty_authors as $dirty_author) {
            if (is_numeric($dirty_author)) {
                $authors[] = Author::get_by_term_id($dirty_author);
            } elseif ('u' === $dirty_author[0]) {
                $user_id = (int)substr($dirty_author, 1);
                $author  = Author::get_by_user_id($user_id);
                if ( ! $author) {
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
                    'post_author' => 0,
                ], [
                    'ID' => $post_id,
                ]
            );
            clean_post_cache($post_id);
        }
    }

    /**
     * Assign a author term when a post is initially created
     *
     * @param integer $post_id Post ID.
     * @param WP_Post $post    Post object.
     * @param boolean $update  Whether this is an update.
     */
    public static function action_save_post_set_initial_author($post_id, $post, $update)
    {
        if ($update) {
            return;
        }
        if ( ! in_array($post->post_type, Content_Model::get_author_supported_post_types(), true)) {
            return;
        }

        $default_author = false;
        if ($post->post_author) {
            $default_author = Author::get_by_user_id($post->post_author);
        }

        /**
         * Filter the default author assigned to the post.
         *
         * @param mixed   $default_author Default author, as calculated by plugin.
         * @param WP_Post $post           Post object.
         */
        $default_author = apply_filters('authors_default_author', $default_author, $post);
        if ($default_author) {
            Utils::set_post_authors($post_id, [$default_author]);
        }
    }

}
