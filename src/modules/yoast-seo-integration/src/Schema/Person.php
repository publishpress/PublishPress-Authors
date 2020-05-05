<?php
/**
 * @package PublishPress Authors
 * @author  PublishPress
 *
 * Copyright (C) 2020 PublishPress
 * Copyright (C) 2020 Yoast SEO
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

namespace PPAuthors\YoastSEO\Schema;

use MultipleAuthors\Classes\Authors_Iterator;
use MultipleAuthors\Classes\Legacy\Util;
use MultipleAuthors\Classes\Objects\Author as AuthorObject;
use WP_User;
use WPSEO_Graph_Piece;
use WPSEO_Image_Utils;
use WPSEO_Schema_Context;
use WPSEO_Schema_IDs;
use WPSEO_Schema_Image;
use WPSEO_Schema_Utils;

/**
 * Returns schema Article data.
 *
 * Based on the class \WPSEO_Schema_Article, from Yoast SEO Premium.
 */
class Person implements WPSEO_Graph_Piece
{
    /**
     * The Schema type we use for this class.
     *
     * @var string[]
     */
    protected $type = [
        'Person',
        'Organization',
    ];
    /**
     * The hash used for images.
     *
     * @var string
     */
    protected $image_hash;
    /**
     * A value object with context variables.
     *
     * @var WPSEO_Schema_Context
     */
    private $context;
    /**
     * Array of the social profiles we display for a Person.
     *
     * @var string[]
     */
    private $social_profiles = [
        'facebook',
        'instagram',
        'linkedin',
        'pinterest',
        'twitter',
        'myspace',
        'youtube',
        'soundcloud',
        'tumblr',
        'wikipedia',
    ];

    /**
     * WPSEO_Schema_Person constructor.
     *
     * @param WPSEO_Schema_Context $context A value object with context variables.
     */
    public function __construct(WPSEO_Schema_Context $context)
    {
        $this->image_hash = WPSEO_Schema_IDs::PERSON_LOGO_HASH;
        $this->context    = $context;
    }

    /**
     * Determine whether we should return Person schema.
     *
     * @return bool
     */
    public function is_needed()
    {
        if (($this->context->site_represents === 'person') || Util::isAuthor()) {
            return true;
        }

        return false;
    }

    /**
     * Returns Person Schema data.
     *
     * @return bool|array Person data on success, false on failure.
     */
    public function generate()
    {
        $user_id = $this->determine_user_id();
        if (!$user_id) {
            return false;
        }

        $data = $this->build_person_data($user_id);

        return $data;
    }

    /**
     * Determines a User ID for the Person data.
     *
     * @return bool|int User ID or false upon return.
     */
    protected function determine_user_id()
    {
        $user_id = $this->context->site_user_id;

        /**
         * Filter: 'wpseo_schema_person_user_id' - Allows filtering of user ID used for person output.
         *
         * @api int|bool $user_id The user ID currently determined.
         */
        return apply_filters('wpseo_schema_person_user_id', $user_id);
    }

    /**
     * Builds our array of Schema Person data for a given user ID.
     *
     * @param int $user_id The user ID to use.
     *
     * @return array An array of Schema Person data.
     */
    protected function build_person_data($user_id)
    {
        if ($user_id > 0) {
            $user_data = get_userdata($user_id);
            $personId  = WPSEO_Schema_Utils::get_user_schema_id($user_id, $this->context);
        } else {
            $user_data = AuthorObject::get_by_term_id($user_id);
            $personId  = $this->context->site_url . WPSEO_Schema_IDs::PERSON_HASH . wp_hash($user_data->ID);
        }

        $data = [
            '@type' => $this->type,
            '@id'   => $personId,
            'name'  => $user_data->display_name,
        ];

        $data = $this->add_image($data, $user_data);

        if (!empty($description)) {
            $data['description'] = $description;
        }

        if (is_numeric($user_id)) {
            $social_profiles = $this->get_social_profiles($user_id);
            if (is_array($social_profiles)) {
                $data['sameAs'] = $social_profiles;
            }
        } else {
            // @todo: refactor this since authors don't have social profiles by default
        }

        return $data;
    }

    /**
     * Returns an ImageObject for the persons avatar.
     *
     * @param array $data The Person schema.
     * @param WP_User $user_data User data.
     *
     * @return array $data The Person schema.
     */
    protected function add_image($data, $user_data)
    {
        $schema_id = $this->context->site_url . $this->image_hash;
        $data      = $this->set_image_from_options($data, $schema_id);

        if (!isset($data['image'])) {
            $data = $this->set_image_from_avatar($data, $user_data, $schema_id);
        }

        if (is_array($this->type) && in_array('Organization', $this->type, true)) {
            $data['logo'] = ['@id' => $schema_id];
        }

        return $data;
    }

    /**
     * Generate the person image from our settings.
     *
     * @param array $data The Person schema.
     * @param string $schema_id The string used in the `@id` for the schema.
     *
     * @return array    $data      The Person schema.
     */
    private function set_image_from_options($data, $schema_id)
    {
        if ($this->context->site_represents !== 'person') {
            return $data;
        }
        $person_logo_id = WPSEO_Image_Utils::get_attachment_id_from_settings('person_logo');

        if ($person_logo_id) {
            $image         = new WPSEO_Schema_Image($schema_id);
            $data['image'] = $image->generate_from_attachment_id($person_logo_id, $data['name']);
        }

        return $data;
    }

    /**
     * Generate the person logo from gravatar.
     *
     * @param array $data The Person schema.
     * @param WP_User $user_data User data.
     * @param string $schema_id The string used in the `@id` for the schema.
     *
     * @return array    $data      The Person schema.
     */
    private function set_image_from_avatar($data, $user_data, $schema_id)
    {
        // If we don't have an image in our settings, fall back to an avatar, if we're allowed to.
        $show_avatars = get_option('show_avatars');
        if (!$show_avatars) {
            return $data;
        }

        if (method_exists($user_data, 'get_avatar_url')) {
            $url = $user_data->get_avatar_url(96);

            if (is_array($url)) {
                $url = $url['url'];
            }
        } else {
            $url = get_avatar_url($user_data->user_email);
        }

        if (empty($url)) {
            return $data;
        }

        $schema_image  = new WPSEO_Schema_Image($schema_id);
        $data['image'] = $schema_image->simple_image_object($url, $user_data->display_name);

        return $data;
    }

    /**
     * Retrieve a list of social profile URLs for Person.
     *
     * @param int $user_id User ID.
     *
     * @return string[] $output A list of social profiles.
     */
    protected function get_social_profiles($user_id)
    {
        /**
         * Filter: 'wpseo_schema_person_social_profiles' - Allows filtering of social profiles per user.
         *
         * @param int $user_id The current user we're grabbing social profiles for.
         *
         * @api string[] $social_profiles The array of social profiles to retrieve. Each should be a user meta field
         *                                key. As they are retrieved using the WordPress function `get_the_author_meta`.
         */
        $social_profiles = apply_filters('wpseo_schema_person_social_profiles', $this->social_profiles, $user_id);
        $output          = [];
        foreach ($social_profiles as $profile) {
            $social_url = $this->url_social_site($profile, $user_id);
            if ($social_url) {
                $output[] = $social_url;
            }
        }

        return $output;
    }

    /**
     * Returns an author's social site URL.
     *
     * @param string $social_site The social site to retrieve the URL for.
     * @param mixed $user_id The user ID to use function outside of the loop.
     *
     * @return string
     */
    protected function url_social_site($social_site, $user_id = false)
    {
        $url = get_the_author_meta($social_site, $user_id);

        if (!empty($url)) {
            switch ($social_site) {
                case 'twitter':
                    $url = 'https://twitter.com/' . $url;
                    break;
            }
        }

        return $url;
    }

    /**
     * @return mixed
     */
    protected function getPostAuthor()
    {
        // We can only show one author for now - a limitation from the https://schema.org/Article schema.
        $authorsIterator = new Authors_Iterator($this->context->id, false);
        $authorsIterator->iterate();

        return $authorsIterator->current_author;
    }
}
