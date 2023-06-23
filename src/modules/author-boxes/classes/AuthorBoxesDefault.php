<?php

/**
 * @package     MultipleAuthorBoxes
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthorBoxes;

/**
 * Author boxes Ajax
 *
 * @package MultipleAuthorBoxes\Classes
 *
 */
class AuthorBoxesDefault
{

    /**
     * Get default template list.
     */
    public static function getAuthorBoxesDefaultList()
    {
        $defaultAuthorBoxes = [
            'author_boxes_boxed' => __('Boxed', 'publishpress-authors'),
            'author_boxes_centered' => __('Centered', 'publishpress-authors'),
            'author_boxes_inline' => __('Inline', 'publishpress-authors'),
            'author_boxes_inline_avatar' => __('Inline with Avatars', 'publishpress-authors'),
            'author_boxes_simple_list' => __('Simple List', 'publishpress-authors'),
            //'author_boxes_authors_index'  => __('Authors Index', 'publishpress-authors'),
            //'author_boxes_authors_recent' => __('Authors Recent', 'publishpress-authors'),
        ];

        return $defaultAuthorBoxes;
    }

    /**
     * Get a specific default author boxes
     *
     * @param string $default_slug
     * @return array
     */
    public static function getAuthorBoxesDefaultData($default_slug)
    {
        $editor_datas = [];

        //add boxed
        $editor_datas['author_boxes_boxed']         = self::getAuthorBoxesBoxedEditorData();
        //add centered
        $editor_datas['author_boxes_centered']      = self::getAuthorBoxesCenteredEditorData();
        //add inline
        $editor_datas['author_boxes_inline']        = self::getAuthorBoxesInlineEditorData();
        //add inline avatar
        $editor_datas['author_boxes_inline_avatar'] = self::getAuthorBoxesInlineAvatarEditorData();
        //add simple list
        $editor_datas['author_boxes_simple_list']   = self::getAuthorBoxesSimpleListEditorData();

        if (array_key_exists($default_slug, $editor_datas)) {
            return $editor_datas[$default_slug];
        }

        return false;
    }

    /**
     * Boxed editor data
     *
     * @return array
     */
    public static function getAuthorBoxesBoxedEditorData()
    {
        $editor_data = [];
        //title default
        $editor_data['show_title'] = 1;
        $editor_data['title_text'] = esc_html__('Author');
        $editor_data['title_text_plural'] = esc_html__('Authors', 'publishpress-authors');
        $editor_data['title_html_tag'] = 'h2';
        $editor_data['box_tab_custom_wrapper_class'] = 'pp-multiple-authors-layout-boxed';
        //avatar default
        $editor_data['avatar_show'] = 1;
        $editor_data['avatar_size'] = 80;
        $editor_data['avatar_border_radius'] = 50;
        //name default
        $editor_data['name_show'] = 1;
        $editor_data['name_html_tag'] = 'div';
        //bio default
        $editor_data['author_bio_show'] = 1;
        $editor_data['author_bio_html_tag'] = 'p';
        //meta default
        $editor_data['meta_show'] = 1;
        $editor_data['meta_view_all_show'] = 1;
        $editor_data['meta_email_show'] = 1;
        $editor_data['meta_site_link_show'] = 1;
        $editor_data['meta_html_tag'] = 'p';
        $editor_data['meta_background_color'] = '#655997';
        $editor_data['meta_color'] = '#ffffff';
        $editor_data['meta_link_hover_color'] = '#ffffff';
        //recent posts default
        $editor_data['author_recent_posts_title_show'] = 1;
        $editor_data['author_recent_posts_empty_show'] = 1;
        $editor_data['author_recent_posts_limit'] = 5;
        $editor_data['author_recent_posts_orderby'] = 'date';
        $editor_data['author_recent_posts_order'] = 'DESC';
        $editor_data['author_recent_posts_html_tag'] = 'div';
        $editor_data['author_recent_posts_title_border_bottom_style'] = 'dotted';
        //box layout default
        $editor_data['box_layout_border_style'] = 'solid';
        $editor_data['box_layout_shadow_horizontal_offset'] = 10;
        $editor_data['box_layout_shadow_vertical_offset'] = 10;
        $editor_data['box_layout_shadow_blur'] = 0;
        $editor_data['box_layout_shadow_speed'] = 0;
        $editor_data['box_layout_color'] = '#3c434a';

        $editor_data = self::addEditorDataDefaultValues($editor_data);

        return $editor_data;
    }

    /**
     * Centered editor data
     *
     * @return array
     */
    public static function getAuthorBoxesCenteredEditorData()
    {
        $editor_data = [];
        //title default
        $editor_data['show_title'] = 1;
        $editor_data['title_text'] = esc_html__('Author');
        $editor_data['title_text_plural'] = esc_html__('Authors', 'publishpress-authors');
        $editor_data['title_html_tag'] = 'h2';
        $editor_data['box_tab_custom_wrapper_class'] = 'pp-multiple-authors-layout-centered';
        //avatar default
        $editor_data['avatar_show'] = 1;
        $editor_data['avatar_size'] = 80;
        $editor_data['avatar_border_radius'] = 50;
        //name default
        $editor_data['name_show'] = 1;
        $editor_data['name_html_tag'] = 'div';
        //bio default
        $editor_data['author_bio_show'] = 1;
        $editor_data['author_bio_html_tag'] = 'p';
        //meta default
        $editor_data['meta_show'] = 1;
        $editor_data['meta_view_all_show'] = 1;
        $editor_data['meta_email_show'] = 1;
        $editor_data['meta_site_link_show'] = 1;
        $editor_data['meta_html_tag'] = 'p';
        $editor_data['meta_background_color'] = '#655997';
        $editor_data['meta_color'] = '#ffffff';
        $editor_data['meta_link_hover_color'] = '#ffffff';
        //recent posts default
        $editor_data['author_recent_posts_title_show'] = 1;
        $editor_data['author_recent_posts_empty_show'] = 1;
        $editor_data['author_recent_posts_limit'] = 5;
        $editor_data['author_recent_posts_orderby'] = 'date';
        $editor_data['author_recent_posts_order'] = 'DESC';
        $editor_data['author_recent_posts_html_tag'] = 'div';
        $editor_data['author_recent_posts_title_border_bottom_style'] = 'dotted';
        $editor_data['author_recent_posts_alignment'] = 'left';
        //box layout default
        $editor_data['box_layout_border_style'] = 'solid';
        $editor_data['box_layout_shadow_horizontal_offset'] = 10;
        $editor_data['box_layout_shadow_vertical_offset'] = 10;
        $editor_data['box_layout_shadow_blur'] = 0;
        $editor_data['box_layout_shadow_speed'] = 0;
        $editor_data['box_layout_color'] = '#3c434a';

        $editor_data = self::addEditorDataDefaultValues($editor_data);

        return $editor_data;
    }

    /**
     * Inline editor data
     *
     * @return array
     */
    public static function getAuthorBoxesInlineEditorData()
    {
        $editor_data = [];
        //title default
        $editor_data['title_text'] = esc_html__('Author');
        $editor_data['title_text_plural'] = esc_html__('Authors', 'publishpress-authors');
        $editor_data['title_html_tag'] = 'h2';
        $editor_data['box_tab_custom_wrapper_class'] = 'pp-multiple-authors-layout-inline';
        //avatar default
        $editor_data['avatar_size'] = 80;
        $editor_data['avatar_border_radius'] = 50;
        //name default
        $editor_data['name_show'] = 1;
        $editor_data['name_html_tag'] = 'div';
        //bio default
        $editor_data['author_bio_html_tag'] = 'p';
        //meta default
        $editor_data['meta_view_all_show'] = 1;
        $editor_data['meta_email_show'] = 1;
        $editor_data['meta_site_link_show'] = 1;
        $editor_data['meta_html_tag'] = 'p';
        $editor_data['meta_background_color'] = '#655997';
        $editor_data['meta_color'] = '#ffffff';
        $editor_data['meta_link_hover_color'] = '#ffffff';
        //recent posts default
        $editor_data['author_recent_posts_title_show'] = 1;
        $editor_data['author_recent_posts_empty_show'] = 1;
        $editor_data['author_recent_posts_limit'] = 5;
        $editor_data['author_recent_posts_orderby'] = 'date';
        $editor_data['author_recent_posts_order'] = 'DESC';
        $editor_data['author_recent_posts_html_tag'] = 'div';
        $editor_data['author_recent_posts_title_border_bottom_style'] = 'dotted';
        $editor_data['author_recent_posts_alignment'] = 'left';
        //box layout default
        $editor_data['box_tab_layout_author_separator'] = ', ';
        $editor_data['box_layout_border_style'] = 'none';
        $editor_data['box_layout_shadow_horizontal_offset'] = 10;
        $editor_data['box_layout_shadow_vertical_offset'] = 10;
        $editor_data['box_layout_shadow_blur'] = 0;
        $editor_data['box_layout_shadow_speed'] = 0;
        $editor_data['box_layout_color'] = '#3c434a';
        //default css
        $editor_data['box_tab_custom_css'] = '.pp-multiple-authors-layout-inline ul.pp-multiple-authors-boxes-ul {
            display: flex;
        }

        .pp-multiple-authors-layout-inline ul.pp-multiple-authors-boxes-ul li {
            margin-right: 10px
        }';

        $editor_data = self::addEditorDataDefaultValues($editor_data);

        return $editor_data;
    }

    /**
     * Inline with avatar editor data
     *
     * @return array
     */
    public static function getAuthorBoxesInlineAvatarEditorData()
    {
        $editor_data = [];
        //title default
        $editor_data['title_text'] = esc_html__('Author');
        $editor_data['title_text_plural'] = esc_html__('Authors', 'publishpress-authors');
        $editor_data['title_html_tag'] = 'h2';
        $editor_data['box_tab_custom_wrapper_class'] = 'pp-multiple-authors-layout-inline';
        //avatar default
        $editor_data['avatar_show'] = 1;
        $editor_data['avatar_size'] = 30;
        $editor_data['avatar_border_radius'] = 0;
        //name default
        $editor_data['name_show'] = 1;
        $editor_data['name_html_tag'] = 'div';
        //bio default
        $editor_data['author_bio_html_tag'] = 'p';
        //meta default
        $editor_data['meta_view_all_show'] = 1;
        $editor_data['meta_email_show'] = 1;
        $editor_data['meta_site_link_show'] = 1;
        $editor_data['meta_html_tag'] = 'p';
        $editor_data['meta_background_color'] = '#655997';
        $editor_data['meta_color'] = '#ffffff';
        $editor_data['meta_link_hover_color'] = '#ffffff';
        //recent posts default
        $editor_data['author_recent_posts_title_show'] = 1;
        $editor_data['author_recent_posts_empty_show'] = 1;
        $editor_data['author_recent_posts_limit'] = 5;
        $editor_data['author_recent_posts_orderby'] = 'date';
        $editor_data['author_recent_posts_order'] = 'DESC';
        $editor_data['author_recent_posts_html_tag'] = 'div';
        $editor_data['author_recent_posts_title_border_bottom_style'] = 'dotted';
        $editor_data['author_recent_posts_alignment'] = 'left';
        //box layout default
        $editor_data['box_tab_layout_author_separator'] = ', ';
        $editor_data['box_layout_border_style'] = 'none';
        $editor_data['box_layout_shadow_horizontal_offset'] = 10;
        $editor_data['box_layout_shadow_vertical_offset'] = 10;
        $editor_data['box_layout_shadow_blur'] = 0;
        $editor_data['box_layout_shadow_speed'] = 0;
        $editor_data['box_layout_color'] = '#3c434a';
        //default css
        $editor_data['box_tab_custom_css'] = '.pp-multiple-authors-layout-inline ul.pp-multiple-authors-boxes-ul {
            display: flex;
        }

        .pp-multiple-authors-layout-inline ul.pp-multiple-authors-boxes-ul li {
            margin-right: 10px
        }
        .pp-multiple-authors-layout-inline ul.pp-multiple-authors-boxes-ul li.has-avatar .pp-author-boxes-avatar,
        .pp-multiple-authors-layout-inline ul.pp-multiple-authors-boxes-ul li.has-avatar .pp-author-boxes-avatar-details {
            display: inline-block;
        }';

        $editor_data = self::addEditorDataDefaultValues($editor_data);

        return $editor_data;
    }

    /**
     * Simple list editor data
     *
     * @return array
     */
    public static function getAuthorBoxesSimpleListEditorData()
    {
        $editor_data = [];
        //title default
        $editor_data['title_text'] = esc_html__('Author');
        $editor_data['title_text_plural'] = esc_html__('Authors', 'publishpress-authors');
        $editor_data['title_html_tag'] = 'h2';
        $editor_data['box_tab_custom_wrapper_class'] = 'pp-multiple-authors-layout-simple_list';
        //avatar default
        $editor_data['avatar_show'] = 1;
        $editor_data['avatar_size'] = 35;
        $editor_data['avatar_border_radius'] = 0;
        //name default
        $editor_data['name_show'] = 1;
        $editor_data['name_html_tag'] = 'div';
        //bio default
        $editor_data['author_bio_html_tag'] = 'p';
        //meta default
        $editor_data['meta_view_all_show'] = 1;
        $editor_data['meta_email_show'] = 1;
        $editor_data['meta_site_link_show'] = 1;
        $editor_data['meta_html_tag'] = 'p';
        $editor_data['meta_background_color'] = '#655997';
        $editor_data['meta_color'] = '#ffffff';
        $editor_data['meta_link_hover_color'] = '#ffffff';
        //recent posts default
        $editor_data['author_recent_posts_title_show'] = 1;
        $editor_data['author_recent_posts_empty_show'] = 1;
        $editor_data['author_recent_posts_limit'] = 5;
        $editor_data['author_recent_posts_orderby'] = 'date';
        $editor_data['author_recent_posts_order'] = 'DESC';
        $editor_data['author_recent_posts_html_tag'] = 'div';
        $editor_data['author_recent_posts_title_border_bottom_style'] = 'dotted';
        $editor_data['author_recent_posts_alignment'] = 'left';
        //box layout default
        $editor_data['box_layout_border_style'] = 'solid';
        $editor_data['box_layout_border_color'] = '#999999';
        $editor_data['box_layout_shadow_horizontal_offset'] = 10;
        $editor_data['box_layout_shadow_vertical_offset'] = 10;
        $editor_data['box_layout_shadow_blur'] = 0;
        $editor_data['box_layout_shadow_speed'] = 0;
        $editor_data['box_layout_color'] = '#3c434a';
        //default css
        $editor_data['box_tab_custom_css'] = '.pp-multiple-authors-boxes-wrapper.pp-multiple-authors-layout-simple_list .pp-multiple-authors-boxes-ul li {
            border-left: none !important;
            border-right: none !important;
        }';

        $editor_data = self::addEditorDataDefaultValues($editor_data);

        return $editor_data;
    }

    /**
     * Add editor data default values 
     *
     * @param array $editor_data
     * @return void
     */
    public static function addEditorDataDefaultValues($editor_data) {
        $profile_fields   = apply_filters('multiple_authors_author_fields', [], false);
        $social_fields   = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube'];

        foreach ($profile_fields as $key => $data) {
            if ($data['type'] === 'url' && !in_array($key, $social_fields)) {
                $editor_data['profile_fields_' . $key . '_html_tag'] = 'a';
                $editor_data['profile_fields_' . $key . '_display']  = 'value';
                $editor_data['profile_fields_' . $key . '_color']    = '#655997';
            }
        }
        return $editor_data;
    }
}
