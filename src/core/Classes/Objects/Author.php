<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthors\Classes\Objects;

use MultipleAuthors\Classes\Author_Utils;
use WP_Error;

/**
 * Representation of an individual author.
 * @property int $ID
 * @property string $slug
 * @property string $nickname
 * @property string $description
 * @property string $user_description
 * @property string $first_name
 * @property string $user_firstname
 * @property string $last_name
 * @property string $user_lastname
 * @property string $user_nicename
 * @property string $user_email
 * @property string $user_url
 * @property string $display_name
 * @property string $link
 * @property string $user_id
 */
class Author
{

    /**
     * ID for the correlated term.
     *
     * @var int
     */
    private $term_id;

    /**
     * @var \WP_Term
     */
    private $term;

    /**
     * Instantiate a new author object
     *
     * Authors are always fetched by static fetchers.
     *
     * @param WP_Term|int $term ID for the correlated term or the term instance.
     */
    private function __construct($term)
    {
        if ($term instanceof \WP_Term) {
            $this->term    = $term;
            $this->term_id = $term->term_id;
        } else {
            $this->term_id = (int)$term;
        }

        $this->term_id = abs($this->term_id);
    }

    private function getTerm()
    {
        if (empty($this->term)) {
            $this->term = get_term($this->term_id, 'author');
        }

        return $this->term;
    }

    /**
     * Create a new author object from an existing WordPress user.
     *
     * @param WP_User|int $user WordPress user to clone.
     *
     * @return Author|WP_Error
     */
    public static function create_from_user($user)
    {
        if (is_int($user)) {
            $user = get_user_by('id', $user);
        }
        if (!is_a($user, 'WP_User')) {
            error_log(
                sprintf(
                    '[PublishPress Authors] The method %s found that the expected user doesn\'t exist: %s',
                    __METHOD__,
                    maybe_serialize($user)
                )
            );
            return false;
        }
        $existing = self::get_by_user_id($user->ID);
        if ($existing) {
            error_log(
                sprintf(
                    '[PublishPress Authors] The method %s tried to create an author that already exists for the user: %s',
                    __METHOD__,
                    maybe_serialize($user)
                )
            );
            return false;
        }
        $author = self::create(
            [
                'display_name' => $user->display_name,
                'slug'         => $user->user_nicename,
            ]
        );

        if (is_wp_error($author)) {
            error_log(
                sprintf('[PublishPress Authors] The method %s found an error trying to create an author', __METHOD__)
            );
            return false;
        }

        self::update_author_from_user($author->term_id, $user->ID);

        return $author;
    }

    /**
     * Get a author object based on its user id.
     *
     * @param int $user_id ID for the author's user.
     *
     * @return Author|false
     */
    public static function get_by_user_id($user_id)
    {
        global $wpdb;

        $term_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT te.term_id
				 FROM {$wpdb->termmeta} AS te
				 LEFT JOIN {$wpdb->term_taxonomy} AS ta ON (te.term_id = ta.term_id)
				 WHERE  ta.taxonomy = 'author' AND meta_key=%s",
                'user_id_' . $user_id
            )
        );

        if (!$term_id) {
            return false;
        }

        return new Author($term_id);
    }

    /**
     * Create a new author object
     *
     * @param array $args Arguments with which to create the new object.
     *
     * @return Author|false
     */
    public static function create($args)
    {
        if (empty($args['slug'])) {
            error_log(sprintf('[PublishPress Authors] The method %s is missing the slug in the arguments', __METHOD__));
            return false;
        }
        if (empty($args['display_name'])) {
            error_log(
                sprintf('[PublishPress Authors] The method %s is missing the display_name in the arguments', __METHOD__)
            );
            return false;
        }

        $termData = wp_insert_term(
            $args['display_name'],
            'author',
            [
                'slug' => $args['slug'],
            ]
        );

        if (is_wp_error($termData)) {
            error_log(
                sprintf('[PublishPress Authors] %s %s', $termData->get_error_message(), __METHOD__)
            );

            return false;
        }

        return new Author($termData['term_id']);
    }

    /**
     * Update the author's data based on the user's data.
     *
     * @param $term_id
     * @param $user_id
     */
    public static function update_author_from_user($term_id, $user_id)
    {
        $user = get_user_by('id', (int)$user_id);

        if (empty($user) || is_wp_error($user)) {
            return;
        }

        wp_update_term(
            $term_id,
            'author',
            [
                'slug' => $user->user_nicename,
            ]
        );

        // Clone applicable user fields.
        $user_fields = [
            'first_name',
            'last_name',
            'user_email',
            'user_login',
            'user_url',
            'description',
        ];
        update_term_meta($term_id, 'user_id', $user->ID);
        foreach ($user_fields as $field) {
            update_term_meta($term_id, $field, $user->$field);
        }
    }

    /**
     * Remove the link between the author and user. Convert into a guest author.
     *
     * @param $term_id
     */
    public static function convert_into_guest_author($term_id)
    {
        delete_term_meta($term_id, 'user_id');
        delete_term_meta($term_id, 'user_id');
    }

    /**
     * Get a author object based on its term id.
     *
     * @param int $term_id ID for the author term.
     *
     * @return Author|false
     */
    public static function get_by_term_id($term_id)
    {
        return new Author($term_id);
    }

    /**
     * Get a author object based on its term slug.
     *
     * @param string $slug Slug for the author term.
     *
     * @return Author|false
     */
    public static function get_by_term_slug($slug)
    {
        $term = get_term_by('slug', $slug, 'author');
        if (!$term || is_wp_error($term)) {
            return false;
        }

        return new Author($term);
    }


    /**
     * @param $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        $properties = get_object_vars($this);

        $properties['link']          = true;
        $properties['user_nicename'] = true;
        $properties['name']          = true;
        $properties['slug']          = true;
        $properties['user_email']    = true;
        $properties['description']   = true;
        $properties['user_url']      = true;
        $properties['url']           = true;
        $properties['user_id']       = true;
        $properties['ID']            = true;
        $properties['first_name']    = true;
        $properties['last_name']     = true;

        // Short circuit to only trigger the filter for additional fields if the property is not already defined.
        // Save resources and avoid infinity loops on some queries that check is $query->is_author.
        if (array_key_exists($name, $properties)) {
            return true;
        }

        /**
         * Filter the author's properties.
         *
         * @param array $properties
         *
         * @return array
         */
        $properties = apply_filters('pp_multiple_authors_author_properties', $properties);

        return array_key_exists($name, $properties);
    }

    /**
     * Get an object attribute.
     *
     * @param string $attribute Attribute name.
     *
     * @return mixed
     */
    public function __get($attribute)
    {
        // Underscore prefix means protected.
        if ('_' === $attribute[0]) {
            return null;
        }

        if ('ID' === $attribute) {
            // Negative IDs represents the term ID for guest authors and positive IDs represents the user ID, as expected.
            if ($this->is_guest()) {
                return abs($this->term_id) * -1;
            } else {
                return $this->user_id;
            }
        }

        if ('user_url' === $attribute) {
            $url = $this->get_meta('user_url');

            if (empty($url) && !$this->is_guest()) {
                $user = $this->get_user_object();

                return $user->user_url;
            }
        }

        if ('first_name' === $attribute) {
            if (!$this->is_guest()) {
                return get_user_meta($this->user_id, 'first_name', true);
            }
        }

        if ('term_id' === $attribute) {
            return $this->term_id;
        }

        if ('link' === $attribute) {
            $user_id = get_term_meta($this->term_id, 'user_id', true);

            // Is a user mapped to this author?
            if (!empty($user_id)) {
                return get_author_posts_url($user_id);
            }

            return get_term_link($this->term_id, 'author');
        }

        // These two fields are actually on the Term object.
        if ('display_name' === $attribute) {
            $attribute = 'name';
        }

        if ('user_nicename' === $attribute) {
            $attribute = 'slug';
        }

        if (in_array($attribute, ['name', 'slug'], true)) {
            return get_term_field($attribute, $this->term_id, 'author', 'raw');
        }

        $return = get_term_meta($this->term_id, $attribute, true);


        if (is_null($return)) {
            return apply_filters('pp_multiple_authors_author_attribute', null, $this->term_id, $attribute);
        }

        return $return;
    }

    /**
     * @param int $size
     *
     * @return string|array
     */
    public function get_avatar_url($size = 96)
    {
        if ($this->has_custom_avatar()) {
            $url = $this->get_custom_avatar_url($size);
        } else {
            $url = get_avatar_url($this->user_email, $size);
        }

        return $url;
    }

    /**
     * @param string $key
     * @param bool $single
     *
     * @return mixed
     */
    public function get_meta($key, $single = true)
    {
        $meta = Author_Utils::get_author_meta($this->term_id, $key, $single);

        if ($this->is_guest()) {
            return $meta;
        } elseif (empty($meta) && '0' !== (string)$meta) {
            $meta = get_user_meta($this->user_id, $key, $single);
        }

        return $meta;
    }

    /**
     * @return bool
     */
    public function has_custom_avatar()
    {
        return Author_Utils::author_has_custom_avatar($this->term_id);
    }

    /**
     * @param int $size
     *
     * @return array
     */
    protected function get_custom_avatar_url($size = 96)
    {
        $avatar_attachment_id = get_term_meta($this->term_id, 'avatar', true);

        // Get the avatar from the attachments.
        $url   = '';
        $url2x = '';
        if (!empty($avatar_attachment_id)) {
            $url   = wp_get_attachment_image_url($avatar_attachment_id, $size);
            $url2x = wp_get_attachment_image_url($avatar_attachment_id, $size * 2);
        }

        // Check if it should return the default avatar.
        if (empty($url)) {
            $avatar_data = get_avatar_data(0);
            $url         = $avatar_data['url'];
            $url2x       = $avatar_data['url'] . '2x';
        }

        return [
            'url'   => $url,
            'url2x' => $url2x,
        ];
    }

    /**
     * For guest authors, returns the custom avatar, if set. If not set, the WordPress' default profile picture.
     * For user mapped authors, returns the custom avatar if set. If not set, returns the user's avatar.
     *
     * @param int $size
     *
     * @return string
     */
    public function get_avatar($size = 96)
    {
        /**
         * Filters whether to retrieve the avatar early.
         *
         * Passing a non-null value will effectively short-circuit get_avatar(), passing
         * the value through the {@see 'multiple_authors_get_avatar'} filter and returning early.
         *
         * @param string $avatar HTML for the author's avatar. Default null.
         * @param Author $author The author's instance.
         * @param int $size The size of the avatar.
         *
         * @since 2.2.1
         *
         */
        $avatar = apply_filters('multiple_authors_pre_get_avatar', null, $this, $size);

        if (!is_null($avatar)) {
            /** This filter is documented in core/Classes/Objects/Author.php */
            return apply_filters('multiple_authors_get_avatar', $avatar, $this, $size);
        }

        if ($this->has_custom_avatar()) {
            $avatar = $this->get_custom_avatar($size);
        } else {
            $avatar = get_avatar($this->user_email, $size);
        }

        /**
         * Filters the avatar to retrieve.
         *
         * @param string $avatar HTML for the author's avatar.
         * @param Author $author The author's instance.
         * @param int $size The size of the avatar.
         *
         * @since 2.2.1
         *
         */
        return apply_filters('multiple_authors_get_avatar', $avatar, $this, $size);
    }

    /**
     * Return's the custom avatar set for the author, or the default user profile image set in site settings.
     *
     * @param int $size
     *
     * @return string
     */
    protected function get_custom_avatar($size = 96)
    {
        $urls = $this->get_custom_avatar_url($size);

        $class = [
            'multiple_authors_guest_author_avatar',
            'avatar',
        ];

        $alt = '';

        // Build the HTML tag.
        $avatar = sprintf(
            "<img alt='%s' src='%s' srcset='%s' class='%s' height='%d' width='%d'/>",
            esc_attr($alt),
            esc_url($urls['url']),
            esc_url($urls['url2x']),
            esc_attr(join(' ', $class)),
            (int)$size,
            (int)$size
        );

        return $avatar;
    }

    /**
     * @param $metaKey
     * @param $single
     *
     * @return mixed
     *
     * @deprecated 3.2.5-beta.6
     */
    public function meta($metaKey, $single = true)
    {
        return $this->get_meta($metaKey, $single);
    }

    /**
     * @param $metaKey
     * @param $single
     *
     * @return mixed
     *
     * @deprecated 3.2.5-beta.6
     */
    public function user_meta($metaKey, $single = true)
    {
        $metaValue = null;

        if (!$this->is_guest()) {
            $metaValue = get_user_meta($this->user_id, $metaKey, $single);
        }

        return $metaValue;
    }

    /**
     * @return bool|\WP_User
     */
    public function get_user_object()
    {
        if ($this->is_guest()) {
            return false;
        }

        return get_user_by('ID', $this->user_id);
    }

    /**
     * Returns true if the author is a guest author.
     *
     * @return bool
     */
    public function is_guest()
    {
        return empty($this->user_id);
    }

    public static function get_by_email($emailAddress)
    {
        $authorTermId = Author_Utils::get_author_term_id_by_email($emailAddress);

        if (empty($authorTermId)) {
            return false;
        }

        return self::get_by_term_id($authorTermId);
    }
}
