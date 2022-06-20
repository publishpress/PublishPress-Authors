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
use stdClass;
use WP_Error;

/**
 * Utility methods for managing authors
 *
 * Based on Bylines.
 *
 * @package MultipleAuthors\Classes
 *
 */
class Utils
{
    const USER_BY_SLUG_CACHE_GROUP = 'publishpress-authors-user-by-slug';

    /**
     * @var array
     */
    protected static $supported_post_types = [];

    /**
     * @var array
     */
    protected static $pages_whitelist = [
        'post.php',
        'post-new.php',
        'edit.php',
        'edit-tags.php',
        'term.php',
        'admin.php',
    ];

    /**
     * @var array
     */
    private static $enabledPostTypes = null;

    /**
     * @var string
     */
    protected static $defaultLayout = null;

    /**
     * Convert co-authors to authors on a post.
     *
     * Errors if the post already has authors. To re-convert, remove authors
     * from the post.
     *
     * @param int $post_id ID for the post to convert.
     *
     * @return object|WP_Error Result object if successful; WP_Error on error.
     */
    public static function convert_post_coauthors($post_id)
    {
        if (! function_exists('get_coauthors')) {
            return new WP_Error(
                'authors_missing_cap',
                __('Co-Authors Plus must be installed and active.', 'publishpress-authors')
            );
        }
        $post = get_post($post_id);
        if (! $post) {
            return new WP_Error('authors_missing_post', "Invalid post: {$post_id}");
        }
        $authors = get_the_terms($post_id, 'author');
        if ($authors && ! is_wp_error($authors)) {
            return new WP_Error('authors_post_has_authors', "Post {$post_id} already has authors.");
        }
        $authors = [];
        $result = new stdClass();
        $result->created = 0;
        $result->existing = 0;
        $result->post_id = 0;
        $coauthors = get_coauthors($post_id);
        foreach ($coauthors as $coauthor) {
            switch ($coauthor->type) {
                case 'wpuser':
                    $author = Author::get_by_user_id($coauthor->ID);
                    if ($author) {
                        $authors[] = $author;
                        $result->existing++;
                    } else {
                        $author = Author::create_from_user($coauthor->ID);
                        if (is_wp_error($author)) {
                            return $author;
                        }
                        $authors[] = $author;
                        $result->created++;
                    }
                    break;
                case 'guest-author':
                    $author = Author::get_by_term_slug($coauthor->user_nicename);
                    if ($author) {
                        $authors[] = $author;
                        $result->existing++;
                    } else {
                        $args = [
                            'display_name' => $coauthor->display_name,
                            'slug' => $coauthor->user_nicename,
                        ];
                        $author = Author::create($args);
                        if (is_wp_error($author)) {
                            return $author;
                        }
                        $ignored = [
                            'ID',
                            'display_name',
                            'user_nicename',
                            'user_login',
                        ];
                        foreach ($coauthor as $key => $value) {
                            if (in_array($key, $ignored, true)) {
                                continue;
                            }
                            if ('linked_account' === $key) {
                                $key = 'user_id';
                                $user = get_user_by('login', $value);
                                $value = $user ? $user->ID : '';
                            }
                            if ('' !== $value) {
                                update_term_meta($author->term_id, $key, $value);
                            }
                        }
                        $authors[] = $author;
                        $result->created++;
                    }
                    break;
            } // End switch().
        } // End foreach().
        if (empty($authors) || count($coauthors) !== count($authors)) {
            return new WP_Error(
                'authors_post_missing_coauthors',
                "Failed to convert some authors for post {$post_id}."
            );
        }

        Utils::set_post_authors($post_id, $authors);

        do_action('publishpress_authors_flush_cache');

        return $result;
    }

    /**
     * Set the authors for a post
     *
     * @param int $postId ID for the post to modify.
     * @param array $authors Bylines to set on the post.
     * @param bool $syncPostAuthor
     * @param int $fallbackUserId User ID for using as the author in case no author or if only guests are selected
     */
    public static function set_post_authors($postId, $authors, $syncPostAuthor = true, $fallbackUserId = null)
    {
        static::set_post_authors_name_meta($postId, $authors);

        if ($syncPostAuthor) {
            static::sync_post_author_column($postId, $authors, $fallbackUserId);
        }

        $authors = wp_list_pluck($authors, 'term_id');
        wp_set_object_terms($postId, $authors, 'author');
    }

    public static function detect_author_slug_mismatch()
    {
        global $wpdb;

        $results = $wpdb->get_results(
            "SELECT t.term_id, u.user_nicename
                FROM $wpdb->terms AS t 
                INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
                INNER JOIN $wpdb->termmeta AS tm ON tm.term_id = tt.term_id
                INNER JOIN $wpdb->users AS u ON u.ID = tm.meta_value
                WHERE
                    tt.taxonomy = 'author'
                    AND tm.meta_key = 'user_id'
                    AND u.user_nicename != t.slug"
        );

        return $results;
    }

    public static function sync_author_slug_to_user_nicename($authors = false)
    {
        global $wpdb;

        if (false === $authors) {
            $authors = static::detect_author_slug_mismatch();
        }

        foreach ($authors as $row) {
            $wpdb->update($wpdb->terms, ['slug' => $row->user_nicename], ['term_id' => $row->term_id]);
        }
    }

    /**
     * @param int $postId ID for the post to modify.
     * @param array $authors Bylines to set on the post.
     * @param int|null $fallbackUserId User ID for using as the author in case no author or if only guests are selected
     */
    public static function sync_post_author_column($postId, $authors, $fallbackUserId = null)
    {
        $functionSetPostAuthor = function ($postId, $authorId) {
            global $wpdb;

            // Avoid corrupting the post_author with an empty value.
            if (empty((int)$authorId)) {
                return false;
            }

            $wpdb->update(
                $wpdb->posts,
                [
                    'post_author' => (int)$authorId,
                ],
                [
                    'ID' => $postId,
                ]
            );
            clean_post_cache($postId);

            return true;
        };

        $postAuthorHasChanged = false;
        if (!empty($authors)) {
            foreach ($authors as $author) {
                if (
                    ! is_object($author)
                    || is_wp_error($author)
                    || empty($author)
                ) {
                    continue;
                }

                $isGuest = (method_exists($author, 'is_guest') && $author->is_guest());

                if ($isGuest) {
                    continue;
                }

                if ($functionSetPostAuthor($postId, $author->user_id)) {
                    $postAuthorHasChanged = true;
                }

                break;
            }
        }

        if (! $postAuthorHasChanged) {
            $fallbackUserId = (int)$fallbackUserId;

            if (! empty($fallbackUserId)) {
                $functionSetPostAuthor($postId, $fallbackUserId);
            }

            // Check if the post has any author set. If not an existent author, create one and set the author term.
            $post = get_post($postId);

            if (empty($authors) && ! empty($post->post_author)) {
                $author = Author::get_by_user_id($post->post_author);

                if (empty($author)) {
                    $author = Author::create_from_user($post->post_author);
                }

                if (is_object($author) && ! is_wp_error($author)) {
                    Utils::set_post_authors($postId, [$author], false);
                }
            } elseif ($fallbackUserId !== (int)$post->post_author || empty($fallbackUserId)) {
                $functionSetPostAuthor($postId, get_current_user_id());
            }
        }
    }

    /**
     * Save a metadata with the post authors' name. Add compatibility to
     * Yoast for using in the custom title, and other 3rd party plugins.
     *
     * @param $post_id
     * @param $authors
     */
    public static function set_post_authors_name_meta($post_id, $authors)
    {
        if (! is_array($authors)) {
            $authors = [];
        }

        $metadata = 'ppma_authors_name';

        if (empty($authors)) {
            delete_post_meta($post_id, $metadata);
        } else {
            $names = [];

            foreach ($authors as $author) {
                if (! is_object($author) && is_numeric($author)) {
                    $author = Author::get_by_term_id($author);
                }

                if (is_object($author)) {
                    $names[] = $author->name;
                }
            }

            if (! empty($names)) {
                $names = implode(', ', $names);

                update_post_meta($post_id, $metadata, $names);
            }
        }
    }

    /**
     * Helper to only add javascript to necessary pages. Avoids bloat in admin.
     *
     * @return bool
     */
    public static function is_valid_page()
    {
        global $pagenow;


        $valid = (bool)in_array($pagenow, self::$pages_whitelist);

        if (! $valid) {
            return false;
        }

        if (in_array($pagenow, ['edit-tags.php', 'term.php'])) {
            $taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : null;

            if ('author' !== $taxonomy) {
                return false;
            }
        } elseif (in_array($pagenow, ['admin.php'])) {
            if (isset($_GET['page']) && $_GET['page'] === 'ppma-modules-settings' && isset($_GET['module']) && $_GET['module'] === 'multiple-authors-settings') {
                return true;
            }
        } else {
            return self::is_post_type_enabled() && self::current_user_can_set_authors();
        }

        return true;
    }

    /**
     * Whether or not PublishPress Authors is enabled for this post type
     * Must be called after init
     *
     * @param string $postType The name of the post type we're considering
     *
     * @return bool Whether or not it's enabled
     * @since 3.0
     *
     */
    public static function is_post_type_enabled($postType = null)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        if (empty(self::$supported_post_types)) {
            self::$supported_post_types = self::get_post_types_that_support_authors();
        }

        if (empty($postType)) {
            $postType = Util::getCurrentPostType();
        }

        $isSupported = (bool)in_array($postType, self::$supported_post_types);

        if (! $isSupported) {
            return false;
        }

        $enabledPostTypes = self::get_enabled_post_types();

        return (bool)in_array($postType, $enabledPostTypes);
    }

    public static function get_enabled_post_types()
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        if (empty(self::$enabledPostTypes)) {
            self::$enabledPostTypes = Util::get_post_types_for_module($legacyPlugin->modules->multiple_authors);
        }

        return self::$enabledPostTypes;
    }

    private static function get_post_types_to_force_authors_support()
    {
        $postTypesToForceAuthorsSupport = [
            // LearnPress.
            'lp_course',
            'lp_lesson',
            'lp_quiz',
            // WooCommerce.
            'product',
        ];

        $postTypesToForceAuthorsSupport = apply_filters(
            'publishpress_authors_post_types_to_force_author_support',
            $postTypesToForceAuthorsSupport
        );

        return $postTypesToForceAuthorsSupport;
    }

    /**
     * Returns a list of post types which supports authors.
     */
    public static function get_post_types_that_support_authors()
    {
        if (empty(self::$supported_post_types)) {
            // Get the post types which supports authors
            $post_types_with_authors = array_values(get_post_types());
            // Get post types which doesn't support authors, but should support Multiple Authors.
            $thirdPartySupport = self::get_post_types_to_force_authors_support();


            foreach ($post_types_with_authors as $key => $name) {
                // Ignore some 3rd party post types.
                if (in_array($name, $thirdPartySupport)) {
                    continue;
                }

                if (! post_type_supports($name, 'author') || in_array($name, ['revision', 'attachment'])) {
                    unset($post_types_with_authors[$key]);
                }
            }

            /**
             * @depreacted
             *
             * @param array $post_types_with_authors Post types that support authors.
             */
            self::$supported_post_types = apply_filters_deprecated(
                'coauthors_supported_post_types',
                [$post_types_with_authors],
                '3.5.0',
                'publishpress_authors_supported_post_types'
            );

            /**
             * Modify post types that use authors.
             *
             * @depreacted
             *
             * @param array $post_types_with_authors Post types that support authors.
             */
            self::$supported_post_types = apply_filters_deprecated(
                'authors_post_types',
                [self::$supported_post_types],
                '3.5.0',
                'publishpress_authors_supported_post_types'
            );

            /**
             * Modify post types that use authors.
             *
             * @param array $post_types_with_authors Post types that support authors.
             */
            self::$supported_post_types = apply_filters(
                'publishpress_authors_supported_post_types',
                self::$supported_post_types
            );
        }


        return self::$supported_post_types;
    }

    /**
     * Checks to see if the current user can set authors or not
     *
     * @param null $post
     *
     * @return bool|mixed|void
     */
    public static function current_user_can_set_authors($post = null)
    {
        if (empty($post)) {
            $post = get_post();
            if (empty($post)) {
                if (isset($_GET['post'])) {
                    $post = get_post((int)$_GET['post']);
                } else {
                    return false;
                }
            }
        }

        if (empty($post)) {
            return false;
        }

        $post_type = $post->post_type;

        if (empty($post_type)) {
            return false;
        }

        $current_user = wp_get_current_user();

        if (empty($current_user)) {
            return false;
        }
        // Super admins can do anything
        if (function_exists('is_super_admin') && is_super_admin()) {
            return true;
        }

        $taxonomy = get_taxonomy('author');
        if ($taxonomy !== false && current_user_can($taxonomy->cap->assign_terms)) {
            $can_set_authors = true;
        } else {
            $can_set_authors = isset($current_user->allcaps['edit_others_posts']) ? $current_user->allcaps['edit_others_posts'] : false;
        }

        return apply_filters('coauthors_plus_edit_authors', $can_set_authors);
    }

    /**
     * Written because WP is_plugin_active() requires plugin folder in arg
     *
     * @param string $check_plugin_file
     *
     * @return bool|mixed
     */
    public static function isPluginActive($check_plugin_file)
    {
        if (! is_multisite()) {
            $plugins = (array)get_option('active_plugins');
            foreach ($plugins as $plugin_file) {
                if (false !== strpos($plugin_file, $check_plugin_file)) {
                    return $plugin_file;
                }
            }
        } else {
            $plugins = (array)get_site_option('active_sitewide_plugins');

            // network activated plugin names are array keys
            foreach (array_keys($plugins) as $plugin_file) {
                if (false !== strpos($plugin_file, $check_plugin_file)) {
                    return $plugin_file;
                }
            }
        }

        return false;
    }

    public static function getAuthorTaxonomyPostTypes()
    {
        $taxonomy = get_taxonomy('author');
        $postTypes = $taxonomy->object_type;

        if (($keyToUnset = array_search('customize_changeset', $postTypes)) !== false) {
            unset($postTypes[$keyToUnset]);
        }

        return $postTypes;
    }

    public static function isBylineInstalled()
    {
        return function_exists('byline');
    }

    public static function isBylinesInstalled()
    {
        return defined('BYLINES_VERSION') || class_exists('\\Bylines\\Objects\\Byline');
    }

    public static function isDebugActivated()
    {
        return isset($_GET['authors_debug']) && (int)$_GET['authors_debug'] === 1;
    }

    public static function isDiviInstalled()
    {
        if (! function_exists('et_get_theme_version')) {
            return false;
        }

        if (! defined('ET_CORE')) {
            return false;
        }

        if (version_compare(et_get_theme_version(), '4.4.4', '<')) {
            return false;
        }

        return true;
    }

    public static function isEditflowInstalled()
    {
        return defined('EDIT_FLOW_VERSION') && defined('EDIT_FLOW_ROOT');
    }

    public static function isElementorInstalled()
    {
        // For now we only integrate with the Pro version because the Free one doesn't have the posts modules.

        if (! defined('ELEMENTOR_PRO_VERSION')) {
            return false;
        }

        if (version_compare(ELEMENTOR_PRO_VERSION, '2.9.3', '<')) {
            return false;
        }

        $abort = false;

        $requiredClasses = [
            '\\ElementorPro\\Modules\\Posts\\Skins\\Skin_Base',
            '\\ElementorPro\\Modules\\Posts\\Skins\\Skin_Cards',
            '\\ElementorPro\\Modules\\Posts\\Skins\\Skin_Classic',
            '\\ElementorPro\\Modules\\Posts\\Skins\\Skin_Full_Content',
            '\\ElementorPro\\Modules\\ThemeBuilder\\Skins\\Posts_Archive_Skin_Cards',
            '\\ElementorPro\\Modules\\ThemeBuilder\\Skins\\Posts_Archive_Skin_Classic',
            '\\ElementorPro\\Modules\\ThemeBuilder\\Skins\\Posts_Archive_Skin_Full_Content',
        ];

        foreach ($requiredClasses as $className) {
            if (! class_exists($className)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                        sprintf(
                            '[PublishPress Authors] Elementor module did not find the class %s',
                            $className
                        )
                    );
                }

                $abort = true;
            }
        }

        $requiredTraits = [
            '\\ElementorPro\\Modules\\ThemeBuilder\\Skins\\Posts_Archive_Skin_Base',
            '\\ElementorPro\\Modules\\Posts\\Skins\\Skin_Content_Base',
        ];

        foreach ($requiredTraits as $traitName) {
            if (! trait_exists($traitName)) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                        sprintf(
                            '[PublishPress Authors] Elementor module did not find the trait %s',
                            $traitName
                        )
                    );
                }

                $abort = true;
            }
        }

        if ($abort) {
            return false;
        }

        return true;
    }

    public static function isPolylangInstalled()
    {
        return defined('POLYLANG_BASENAME');
    }

    public static function isGenesisInstalled()
    {
        return function_exists('genesis_autoload_register');
    }

    public static function isUltimateMemberInstalled()
    {
        return class_exists('UM_Functions');
    }

    public static function isCompatibleYoastSeoInstalled()
    {
        if (! defined('WPSEO_VERSION')) {
            return false;
        }

        if (! defined('WPSEO_FILE')) {
            return false;
        }

        if (! class_exists('Yoast\\WP\\SEO\\Config\\Schema_IDs')) {
            return false;
        }

        if (version_compare(WPSEO_VERSION, '14.1', '<')) {
            if (! get_transient('publishpress_authors_not_compatible_yoast_warning')) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log( // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                        sprintf(
                            '[PublishPress Authors] %s %s - %s. %s',
                            __METHOD__,
                            'detected a not supported version of the Yoast SEO plugin',
                            WPSEO_VERSION,
                            'It requires 13.4.1 or later. Please, update it'
                        )
                    );
                }

                set_transient('publishpress_authors_not_compatible_yoast_warning', true, 24 * 60 * 60 * 2);
            }

            return false;
        }

        return true;
    }

    public static function getDefaultLayout()
    {
        if (! is_null(self::$defaultLayout)) {
            return self::$defaultLayout;
        }

        self::$defaultLayout = apply_filters('pp_multiple_authors_default_layout', 'boxed');

        return self::$defaultLayout;
    }

    public static function isWPEngineInstalled()
    {
        return class_exists('WpeCommon');
    }

    public static function getUserBySlug($slug)
    {
        $found = null;
        $user = wp_cache_get($slug, static::USER_BY_SLUG_CACHE_GROUP, false, $found);
        if (false === $user && false !== $found) {
            $user = get_user_by('slug', $slug);

            wp_cache_add($slug, $user, static::USER_BY_SLUG_CACHE_GROUP);
        }

        if (is_wp_error($user)) {
            $user = false;
        }

        return $user;
    }

    public static function isTheSEOFrameworkInstalled()
    {
        return defined('THE_SEO_FRAMEWORK_VERSION');
    }

    public static function isAuthorOfPost($postId, $author)
    {
        $postAuthors = get_post_authors($postId);
        if (empty($postAuthors)) {
            return false;
        }

        if (is_numeric($author)) {
            $author = Author::get_by_id($author);
        } elseif (is_string($author)) {
            $author = Author::get_by_term_slug($author);
        }

        if (! is_object($author)) {
            return false;
        }

        foreach ($postAuthors as $postAuthor) {
            if ($postAuthor->ID === $author->ID) {
                return true;
            }
        }

        return false;
    }

    public static function sanitizeArray($array)
    {
        $sanitizedArray = [];

        foreach ($array as $key => $value) {
            $key = sanitize_key($key);

            if (is_array($value)) {
                $sanitizedArray[$key] = self::sanitizeArray($value);
                continue;
            }

            $sanitizedArray[$key] = sanitize_text_field($value);
        }

        return $sanitizedArray;
    }

    /**
     * Helper function to check if template exist in theme/child theme.
     * We couldn't use wordpress locate_template() as it support theme compact which load 
     * default template for files like sidebar.php even if it doesn't exist in theme
     * 
     * @param array $template
     * @return mixed
     */
    public static function authors_locate_template($template_names)
    {
        $template = false;

        foreach ((array) $template_names as $template_name) {
            if (!$template_name ) {
                continue;
            }
            if (file_exists(STYLESHEETPATH . '/' . $template_name)) {
                $template = STYLESHEETPATH . '/' . $template_name;
                break;
            } elseif (file_exists(TEMPLATEPATH . '/' . $template_name)) {
                $template = TEMPLATEPATH . '/' . $template_name;
                break;
            }
        }
        
        return $template;
    }

    /**
     * Get article excerpt
     *
     * @param integer $limit
     * @param string $source
     * @param boolean $echo
     * @param boolean $read_more_link
     * @return string
     */
    public static function ppma_article_excerpt($limit, $source = null, $echo = false, $read_more_link = false) 
    {

        $excerpt = $source == "content" ? get_the_content() : get_the_excerpt();
        $excerpt = preg_replace(" (\[.*?\])",'',$excerpt);
        $excerpt = strip_shortcodes($excerpt);
        $excerpt = wp_strip_all_tags($excerpt);
        $excerpt = substr($excerpt, 0, $limit);
        $excerpt = substr($excerpt, 0, strripos($excerpt, " "));
        $excerpt = trim(preg_replace('/\s+/', ' ', $excerpt));
        if (!empty(trim($excerpt))) {
            $excerpt .= '... ';
        }
        if ($read_more_link) {
            $excerpt .= '<a class="read-more" href="'. esc_url(get_permalink()) .'">'. esc_html__('Read more.', 'publishpress-authors') .'</a>';
        }

        if ($echo) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo $excerpt;
        } else {
            return $excerpt;
        }
    }

    /**
     * Check if current active theme is block theme/support full site editing
     *
     * @return bool
     */
    public static function ppma_is_block_theme() 
    {

        $is_block_theme = false;

        if (function_exists('wp_is_block_theme') 
            && function_exists('block_template_part') 
            && wp_is_block_theme()
        ) {
            $is_block_theme = true;
        }

        return $is_block_theme;
    }

    /**
     * Retreive block theme header
     *
     * @return string
     */
    public static function ppma_get_block_theme_header() 
    {

        $block_theme_header = '';

        if (self::ppma_is_block_theme()) {
            $header_template_part = get_block_template(get_stylesheet() . '//header', 'wp_template_part');
            if ($header_template_part && isset($header_template_part->content)) {
                $block_theme_header = do_blocks($header_template_part->content);
            }
        }

        return $block_theme_header;
    }

    /**
     * Retreive block theme footer
     *
     * @return string
     */
    public static function ppma_get_block_theme_footer() 
    {

        $block_theme_footer = '';

        if (self::ppma_is_block_theme()) {
            $footer_template_part = get_block_template(get_stylesheet() . '//footer', 'wp_template_part');
            if ($footer_template_part && isset($footer_template_part->content)) {
                $block_theme_footer = do_blocks($footer_template_part->content);
            }
        }

        return $block_theme_footer;
    }

    /**
     * Format block theme header
     *
     * @return void
     */
    public static function ppma_format_block_theme_header() 
    {
        $fse_header = self::ppma_get_block_theme_header();
        $fse_footer = self::ppma_get_block_theme_footer();//we need to get footer as well before wp_head() call to enable fse css generator
        ?> 
        <!doctype html>
        <html <?php language_attributes(); ?>>
        <head>
             <meta charset="<?php bloginfo('charset'); ?>">
             <?php wp_head(); ?>
        </head>
        <body <?php body_class(); ?>>
        <?php wp_body_open(); ?>
        <div class="wp-site-blocks">
        <?php echo $fse_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
    }

    /**
     * Format block theme footer
     *
     * @return void
     */
    public static function ppma_format_block_theme_footer() 
    {
        $fse_footer = self::ppma_get_block_theme_footer();
        ?>
        </div>
        <?php echo $fse_footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        wp_footer();
        ?>
            </body>
        </html>
        <?php
    }
}
