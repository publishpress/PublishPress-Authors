<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthors\Classes;

use MultipleAuthors\Classes\Legacy\Util;
use MultipleAuthors\Classes\Objects\Author;
use MultipleAuthors\Factory;

/**
 * Class Author_Editor
 *
 * Based on Bylines' class Byline_Editor.
 *
 * @package MultipleAuthors\Classes
 */
class Author_Editor
{
    /**
     * Customize the term table to look more like the users table.
     *
     * @param array $columns Columns to render in the list table.
     *
     * @return array
     */
    public static function filter_manage_edit_author_columns($columns)
    {
        // Reserve the description for internal use.
        if (isset($columns['description'])) {
            unset($columns['description']);
        }

        // Add our own columns too.
        $new_columns = [];
        foreach ($columns as $key => $title) {
            if ('name' === $key) {
                $new_columns['author_name']       = __('Name', 'publishpress-authors');
                $new_columns['author_user_email'] = __('Email', 'publishpress-authors');
            } else {
                $new_columns[$key] = $title;
            }
        }

        if (isset($new_columns['posts'])) {
            $new_columns['posts'] = sprintf(
                '%s <i class="dashicons dashicons-info-outline" title="%s"></i>',
                __('Posts', 'publishpress-authors'),
                sprintf(
                    __('Published posts of the following post types: %s', 'publishpress-authors'),
                    implode(', ', Utils::getAuthorTaxonomyPostTypes())
                )
            );
        }

        return $new_columns;
    }

    /**
     * Set our custom name column as the primary column
     *
     * @return string
     */
    public static function filter_list_table_primary_column()
    {
        return 'author_name';
    }

    /**
     * Render and return custom column
     *
     * @param string $retval Value being returned.
     * @param string $column_name Name of the column.
     * @param int $term_id Term ID.
     */
    public static function filter_manage_author_custom_column($retval, $column_name, $term_id)
    {
        if ('author_name' === $column_name) {
            $author = Author::get_by_term_id($term_id);

            $retval = $author->get_avatar(32);
            $retval .= '<strong><a class="row-title" aria-label="' . $author->display_name . '" href="' . get_edit_term_link(
                    $author->term_id,
                    'author'
                ) . '">' . $author->display_name . '</a>';

            if (!$author->is_guest()) {
                $retval .= ' — <span class="post-state">' . __('User', 'publishpress-authors') . '</span>';
            } else {
                $retval .= ' — <span class="post-state">' . __(
                        'Guest Author',
                        'publishpress-authors'
                    ) . '</span>';
            }

            $retval .= '</strong>';

            // Inline edit data (quick edit)
            $retval .= '<div class="hidden" id="inline_' . $term_id . '">';
            $retval .= '<div class="name">' . $author->display_name . '</div>';
            $retval .= '<div class="slug">' . $author->slug . '</div>';
            $retval .= '<div class="parent">0</div></div>';
        } elseif ('author_user_email' === $column_name) {
            $author = Author::get_by_term_id($term_id);
            if ($author->user_email) {
                $retval = '<a href="' . esc_url('mailto:' . $author->user_email) . '">' . esc_html(
                        $author->user_email
                    ) . '</a>';
            }
        }

        return $retval;
    }

    /**
     * Add "Create author" and "Edit author" links for users
     *
     * @param array $actions Existing user action links.
     * @param WP_User $user User object.
     *
     * @return array
     */
    public static function filter_user_row_actions($actions, $user)
    {
        if (is_network_admin()
            || !current_user_can(get_taxonomy('author')->cap->manage_terms)) {
            return $actions;
        }

        // Over hide the string Edit
        if (isset($actions['edit'])) {
            $actions['edit'] = str_replace(
                '>Edit<',
                '>' . __('Edit User', 'publishpress-authors') . '<',
                $actions['edit']
            );
        }

        $new_actions = [];
        $author      = Author::get_by_user_id($user->ID);
        if ($author) {
            $link                       = get_edit_term_link($author->term_id, 'author');
            $new_actions['edit-author'] = '<a href="' . esc_url($link) . '">' . esc_html__(
                    'Edit Author',
                    'publishpress-authors'
                ) . '</a>';
        } else {
            $args                         = [
                'action'  => 'author_create_from_user',
                'user_id' => $user->ID,
                'nonce'   => wp_create_nonce('author_create_from_user' . $user->ID),
            ];
            $link                         = add_query_arg(
                array_map('rawurlencode', $args),
                admin_url('admin-ajax.php')
            );
            $new_actions['create-author'] = '<a href="' . esc_url($link) . '">' . esc_html__(
                    'Create Author',
                    'publishpress-authors'
                ) . '</a>';
        }

        return $new_actions + $actions;
    }

    /**
     * Add "Edit user" links for authors mapped to user
     *
     * @param array $actions Existing user action links.
     * @param WP_Term $author_term Author term object.
     *
     * @return array
     */
    public static function filter_author_row_actions($actions, $author_term)
    {
        if (is_network_admin()
            || !current_user_can('edit_users')) {
            return $actions;
        }

        if (isset($actions['inline hide-if-no-js'])) {
            unset($actions['inline hide-if-no-js']);
        }

        // Over hide the string Edit
        if (isset($actions['edit'])) {
            $actions['edit'] = str_replace(
                '>Edit<',
                '>' . __('Edit Author Profile', 'publishpress-authors') . '<',
                $actions['edit']
            );
        }

        $author = Author::get_by_term_id($author_term->term_id);

        $new_actions = [];

        if (!empty($author->user_id)) {
            $link                     = get_edit_user_link($author->user_id);
            $new_actions['edit-user'] = '<a href="' . esc_url($link) . '">' . esc_html__(
                    'Edit User',
                    'publishpress-authors'
                ) . '</a>';
        }

        return $new_actions + $actions;
    }

    /**
     * Render fields for the author profile editor
     *
     * @param WP_Term $term Author term being edited.
     */
    public static function action_author_edit_form_fields($term)
    {
        $author = Author::get_by_term_id($term->term_id);

        /**
         * Filter the fields on the Author's profile.
         *
         * @param array $fields
         * @param Author $author
         *
         * @return array
         */
        $fields = apply_filters('multiple_authors_author_fields', self::get_fields($author), $author);

        foreach ($fields as $key => $args) {
            $args['key']   = $key;
            $args['value'] = $author->$key;
            echo self::get_rendered_author_partial($args);
        }

        wp_nonce_field('author-edit', 'author-edit-nonce');
    }

    /**
     * Get the fields to be rendered in the author editor
     *
     * @param Author $author Author to be rendered.
     *
     * @return array
     */
    public static function get_fields($author)
    {
        $fields = [
            'user_id'     => [
                'label'    => __('Mapped User', 'publishpress-authors'),
                'type'     => 'ajax_user_select',
                'sanitize' => 'intval',
            ],
            'first_name'  => [
                'label' => __('First Name', 'publishpress-authors'),
                'type'  => 'text',
            ],
            'last_name'   => [
                'label' => __('Last Name', 'publishpress-authors'),
                'type'  => 'text',
            ],
            'user_email'  => [
                'label'       => __('Email', 'publishpress-authors'),
                'type'        => 'email',
                'description' => __(
                    'To show the avatar from the Mapped User, enter the same email address as the Mapped User. <br> To show the avatar for a Guest Author, enter the email for their Gravatar account.',
                    'publishpress-authors'
                ),
            ],
            'avatar'      => [
                'label'    => __('Custom Avatar', 'publishpress-authors'),
                'type'     => 'image',
                'sanitize' => 'intval',
            ],
            'user_url'    => [
                'label'    => __('Website', 'publishpress-authors'),
                'type'     => 'url',
                'sanitize' => 'esc_url_raw',
            ],
            'description' => [
                'label'    => __('Biographical Info', 'publishpress-authors'),
                'type'     => 'textarea',
                'sanitize' => 'wp_filter_post_kses',
            ],
        ];

        /**
         * Customize fields presented in the author editor.
         *
         * @param array $fields Existing fields to display.
         * @param Author $author Author to be rendered.
         */
        $fields = apply_filters('authors_editor_fields', $fields, $author);

        return $fields;
    }

    /**
     * Get a rendered field partial
     *
     * @param array $args Arguments to render in the partial.
     */
    private static function get_rendered_author_partial($args)
    {
        $defaults = [
            'type'        => 'text',
            'value'       => '',
            'label'       => '',
            'description' => '',
        ];
        $args     = array_merge($defaults, $args);
        $key      = 'authors-' . $args['key'];
        ob_start();
        ?>
        <tr class="<?php echo esc_attr('form-field term-' . $key . '-wrap'); ?>">
            <th scope="row">
                <?php if (!empty($args['label'])) : ?>
                    <label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($args['label']); ?></label>
                <?php endif; ?>
            </th>
            <td>
                <?php
                if ('image' === $args['type']) :
                    $author_image = wp_get_attachment_image_url($args['value'], 'thumbnail');
                    ?>
                    <div class="author-image-field-wrapper">
                        <div class="author-image-field-container">
                            <?php if ($author_image) : ?>
                                <img src="<?php echo esc_url($author_image); ?>" alt=""/>
                            <?php endif; ?>
                        </div>
                        <p class="hide-if-no-js">
                            <a class="select-author-image-field <?php echo $author_image ? 'hidden' : ''; ?>" href="#">
                                <?php _e('Select image', 'publishpress-authors'); ?>
                            </a>
                            <a class="delete-author-image-field <?php echo !$author_image ? 'hidden' : ''; ?>"
                               href="#">
                                <?php _e('Remove this image', 'publishpress-authors'); ?>
                            </a>
                        </p>
                        <input name="<?php echo esc_attr($key); ?>" class="author-image-field-id" type="hidden"
                               value="<?php echo esc_attr($args['value']); ?>"/>
                    </div>
                <?php elseif ('textarea' === $args['type']) : ?>
                    <textarea
                            name="<?php echo esc_attr($key); ?>"><?php echo esc_textarea($args['value']); ?></textarea>
                <?php
                elseif ('ajax_user_select' === $args['type']) :
                    $user = !empty($args['value']) ? get_user_by('id', $args['value']) : false;
                    ?>
                    <select data-nonce="<?php echo esc_attr(wp_create_nonce('authors-user-search')); ?>"
                            placeholder="<?php esc_attr_e('Select a user', 'publishpress-authors'); ?>"
                            class="authors-select2-user-select" name="<?php echo esc_attr($key); ?>" style="width: 95%">
                        <option></option>
                        <?php if ($user) : ?>
                            <option value="<?php echo (int)$user->ID; ?>"
                                    selected="selected"><?php echo esc_html($user->display_name); ?></option>
                        <?php endif; ?>
                    </select>
                <?php elseif ('wysiwyg' === $args['type']) : ?>
                    <?php wp_editor($args['value'], $key, []); ?>
                <?php else : ?>
                    <input name="<?php echo esc_attr($key); ?>" type="<?php echo esc_attr($args['type']); ?>"
                           value="<?php echo esc_attr($args['value']); ?>"/>
                <?php endif; ?>

                <?php if (isset($args['description'])) : ?>
                    <p class="description"><?php echo $args['description']; ?></p>
                <?php endif; ?>
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle saving of term meta
     *
     * @param int $term_id ID for the term being edited.
     */
    public static function action_edited_author($term_id)
    {
        if (empty($_POST['author-edit-nonce'])
            || !wp_verify_nonce($_POST['author-edit-nonce'], 'author-edit')) {
            return;
        }
        $author = Author::get_by_term_id($term_id);

        foreach (self::get_fields($author) as $key => $args) {
            if (!isset($_POST['authors-' . $key])) {
                continue;
            }
            $sanitize = isset($args['sanitize']) ? $args['sanitize'] : 'sanitize_text_field';
            update_term_meta($term_id, $key, $sanitize($_POST['authors-' . $key]));
        }

        // If there is a mapper user, make sure the author url (slug) is the same of the user.
        if (isset($_POST['authors-user_id']) && !empty($_POST['authors-user_id'])) {
            $user_id = (int)$_POST['authors-user_id'];

            $user = get_user_by('id', $user_id);

            if (!is_a($user, 'WP_User')) {
                return;
            }

            // Do they have the same slug and nicename?
            if ($author->slug !== $user->user_nicename) {
                global $wpdb;

                $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE {$wpdb->terms} SET slug=%s WHERE term_id = %d",
                        $user->user_nicename,
                        $term_id
                    )
                );
            }
        }
    }

    /**
     * @param $user_id
     */
    public static function action_user_register($user_id)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        if (!isset($legacyPlugin->modules->multiple_authors)) {
            error_log(
                sprintf(
                    '[PublishPress Authors] Warning: Module multiple_authors not loaded. %s [user_id="%s"]',
                    __METHOD__,
                    $user_id
                )
            );
            return;
        }

        $roles = (array)$legacyPlugin->modules->multiple_authors->options->author_for_new_users;

        // Check if we have any role selected to create an author for the new user.
        if (empty($roles)) {
            return;
        }

        $user = get_user_by('ID', $user_id);

        if (!empty(array_intersect($roles, $user->roles))) {
            // Create author for this user
            Author::create_from_user($user_id);
        }
    }

    public static function action_new_form_tag()
    {
        // Close the form tag.
        echo '>';

        ?>
        <div class="form-field term-user_id-wrap">
        <label for="tag-user-id"><?php echo __('Mapped User (optional)', 'publishpress-authors'); ?></label>
        <?php
        echo static::get_rendered_author_partial(
            [
                'type'        => 'ajax_user_select',
                'value'       => '',
                'key'         => 'new',
                'description' => __(
                    'You don’t have to choose a Mapped User. Leave this choice blank and you can create a Guest Author with no WordPress account.',
                    'publishpress-authors'
                ),
            ]
        );

        // It is missing the end of the tag by purpose, because there is a hardcoded > after the action is called.
        echo '</div';
    }

    /**
     * Filter the term data before add to the database. Used to make sure authors and mapped users have the same name
     * and slug when inserting.
     *
     * @param $data
     * @param $taxonomy
     * @param $args
     *
     * @return array
     */
    public static function filter_insert_term_data($data, $taxonomy, $args)
    {
        if ($taxonomy !== 'author' || !isset($args['authors-new']) || empty($args['authors-new'])) {
            return $data;
        }

        $user_id = (int)$args['authors-new'];

        $user = get_user_by('id', $user_id);

        $data['slug'] = $user->user_nicename;

        return $data;
    }

    /**
     * Called after create an author to check if we need to get properties from user, in case we have a mapped user.
     *
     * @param $term_id
     */
    public static function action_created_author($term_id)
    {
        if (!isset($_POST['authors-new']) || empty($_POST['authors-new'])) {
            return;
        }

        Author::update_author_from_user($term_id, $_POST['authors-new']);
    }

    /**
     * Add bulk actions to the list of authors.
     *
     * @param $bulk_actions
     *
     * @return array
     */
    public static function filter_author_bulk_actions($bulk_actions)
    {
        $bulk_actions['update_mapped_author_data'] = __(
            'Update data from mapped user',
            'publishpress-authors'
        );
        $bulk_actions['convert_into_guest_author'] = __(
            'Convert into guest author',
            'publishpress-authors'
        );
        $bulk_actions['update_post_count'] = __(
            'Update post count',
            'publishpress-authors'
        );

        return $bulk_actions;
    }

    /**
     * Handle bulk actions from authors.
     *
     * @param string $redirect_to
     * @param string $do_action
     * @param array $terms_ids
     *
     * @return mixed
     */
    public static function handle_author_bulk_actions($redirect_to, $do_action, $terms_ids)
    {
        $bulkActions = [
            'update_mapped_author_data',
            'convert_into_guest_author',
            'update_post_count',
        ];

        if (empty($terms_ids) || !in_array($do_action, $bulkActions, true)) {
            return $redirect_to;
        }

        $updated = 0;

        foreach ($terms_ids as $term_id) {
            if ($do_action === 'update_mapped_author_data') {
                $author = Author::get_by_term_id($term_id);

                if (empty($author->user_id)) {
                    continue;
                }

                Author::update_author_from_user($term_id, $author->user_id);
            } elseif ($do_action === 'convert_into_guest_author') {
                Author::convert_into_guest_author($term_id);
            } elseif ($do_action === 'update_post_count') {
                wp_update_term_count($term_id, 'author');
            }

            $updated++;
        }

        $redirect_to = add_query_arg('bulk_update_author', $updated, $redirect_to);

        return $redirect_to;
    }

    /**
     * Show admin notices
     */
    public static function admin_notices()
    {
        if (!empty($_REQUEST['bulk_update_author'])) {
            $count = (int)$_REQUEST['bulk_update_author'];

            echo '<div id="message" class="updated fade">';

            if (empty($count)) {
                __('No authors were updated', 'publishpress-authors');
            } else {
                printf(__('Updated %d authors', 'publishpress-authors'), $count);
            }

            echo '</div>';
        }
    }
}
