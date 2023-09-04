<?php

/**
 * @package     MultipleAuthorBoxes
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.1.0
 */

namespace MultipleAuthorBoxes;

use MultipleAuthors\Classes\Author_Editor;
use MA_Author_Boxes;

/**
 * Author boxes Editor Fields
 *
 * @package MultipleAuthorBoxes\Classes
 *
 */
class AuthorBoxesEditorFields
{

    /**
     * Add title fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getTitleFields($fields, $post) 
    {
        $fields['show_title'] = [
            'label'       => esc_html__('Show Title', 'publishpress-authors'),
            'description' => '',
            'type'        => 'checkbox',
            'sanitize'    => 'sanitize_text_field',
            'tab'         => 'title',
        ];
        $fields['title_text'] = [
            'label'       => esc_html__('Title Text (Single)', 'publishpress-authors'),
            'type'        => 'text',
            'sanitize'    => 'sanitize_text_field',
            'tab'         => 'title',
        ];
        $fields['title_text_plural'] = [
            'label'       => esc_html__('Title Text (Plural)', 'publishpress-authors'),
            'type'        => 'text',
            'sanitize'    => 'sanitize_text_field',
            'tab'         => 'title',
        ];
        $fields['title_bottom_space'] = [
            'label'    => esc_html__('Bottom space', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'title',
        ];
        $fields['title_size'] = [
            'label'    => esc_html__('Size', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'title',
        ];
        $fields['title_line_height'] = [
            'label'    => esc_html__('Line Height (px)', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'title',
        ];
        $fields['title_weight'] = [
            'label'    => esc_html__('Weight', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''        => esc_html__('Default', 'publishpress-authors'),
                'normal'  => esc_html__('Normal', 'publishpress-authors'),
                'bold'    => esc_html__('Bold', 'publishpress-authors'),
                '100'     => esc_html__('100 - Thin', 'publishpress-authors'),
                '200'     => esc_html__('200 - Extra light', 'publishpress-authors'),
                '300'     => esc_html__('300 - Light', 'publishpress-authors'),
                '400'     => esc_html__('400 - Normal', 'publishpress-authors'),
                '500'     => esc_html__('500 - Medium', 'publishpress-authors'),
                '600'     => esc_html__('600 - Semi bold', 'publishpress-authors'),
                '700'     => esc_html__('700 - Bold', 'publishpress-authors'),
                '800'     => esc_html__('800 - Extra bold', 'publishpress-authors'),
                '900'     => esc_html__('900 - Black', 'publishpress-authors')
            ],
            'tab'      => 'title',
        ];
        $fields['title_transform'] = [
            'label'    => esc_html__('Transform', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''            => esc_html__('Default', 'publishpress-authors'),
                'uppercase'   => esc_html__('Uppercase', 'publishpress-authors'),
                'lowercase'   => esc_html__('Lowercase', 'publishpress-authors'),
                'capitalize'  => esc_html__('Capitalize', 'publishpress-authors'),
                'none'        => esc_html__('Normal', 'publishpress-authors')
            ],
            'tab'      => 'title',
        ];
        $fields['title_style'] = [
            'label'    => esc_html__('Style', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''         => esc_html__('Default', 'publishpress-authors'),
                'none'     => esc_html__('Normal', 'publishpress-authors'),
                'italic'   => esc_html__('Italic', 'publishpress-authors'),
                'oblique'  => esc_html__('Oblique', 'publishpress-authors')
            ],
            'tab'      => 'title',
        ];
        $fields['title_decoration'] = [
            'label'    => esc_html__('Decoration', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''             => esc_html__('Default', 'publishpress-authors'),
                'underline'    => esc_html__('Underline', 'publishpress-authors'),
                'overline'     => esc_html__('Overline', 'publishpress-authors'),
                'line-through' => esc_html__('Line Through', 'publishpress-authors'),
                'none'         => esc_html__('Normal', 'publishpress-authors')
            ],
            'tab'      => 'title',
        ];
        $fields['title_alignment'] = [
            'label'    => esc_html__('Alignment', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''        => esc_html__('Default', 'publishpress-authors'),
                'left'    => esc_html__('Left', 'publishpress-authors'),
                'center'  => esc_html__('Center', 'publishpress-authors'),
                'right'   => esc_html__('Right', 'publishpress-authors'),
                'justify' => esc_html__('Justify', 'publishpress-authors')
            ],
            'tab'      => 'title',
        ];
        $fields['title_color'] = [
            'label'    => esc_html__('Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'title',
        ];
        $fields['title_html_tag'] = [
            'label'    => esc_html__('HTML Tag', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                'h1'   => esc_html__('H1', 'publishpress-authors'),
                'h2'   => esc_html__('H2', 'publishpress-authors'),
                'h3'   => esc_html__('H3', 'publishpress-authors'),
                'h4'   => esc_html__('H4', 'publishpress-authors'),
                'h5'   => esc_html__('H5', 'publishpress-authors'),
                'h6'   => esc_html__('H6', 'publishpress-authors'),
                'div'  => esc_html__('div', 'publishpress-authors'),
                'span' => esc_html__('span', 'publishpress-authors'),
                'p'    => esc_html__('p', 'publishpress-authors')
            ],
            'tab'      => 'title',
        ];

        return $fields;
    }

    /**
     * Add avatar fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getAvatarFields($fields, $post) 
    {
        $fields['avatar_show'] = [
            'label'       => esc_html__('Show Avatar', 'publishpress-authors'),
            'type'        => 'checkbox',
            'sanitize'    => 'absint',
            'tab'         => 'avatar',
        ];
        $fields['avatar_size'] = [
            'label'    => esc_html__('Avatar size (px)', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'avatar',
        ];
        $fields['avatar_border_style'] = [
            'label'    => esc_html__('Border Style', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                'none'   => esc_html__('None', 'publishpress-authors'),
                'dotted' => esc_html__('Dotted', 'publishpress-authors'),
                'dashed' => esc_html__('Dashed', 'publishpress-authors'),
                'solid'  => esc_html__('Solid', 'publishpress-authors'),
                'double' => esc_html__('Double', 'publishpress-authors'),
                'groove' => esc_html__('Groove', 'publishpress-authors'),
                'ridge'  => esc_html__('Ridge', 'publishpress-authors'),
                'inset'  => esc_html__('Inset', 'publishpress-authors'),
                'outset' => esc_html__('Outset', 'publishpress-authors')
            ],
            'tab'      => 'avatar',
        ];
        $fields['avatar_border_width'] = [
            'label'    => esc_html__('Border Width', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'avatar',
        ];
        $fields['avatar_border_color'] = [
            'label'    => esc_html__('Border Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'avatar',
        ];
        $fields['avatar_border_radius'] = [
            'label'      => esc_html__('Border Radius (%)', 'publishpress-authors'),
            'type'       => 'number',
            'min'        => '0',
            'max'        => '100',
            'sanitize'   => 'intval',
            'tab'        => 'avatar',
        ];

        return $fields;
    }

    /**
     * Add name fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getNameFields($fields, $post) 
    {
        $fields['name_show'] = [
            'label'       => esc_html__('Show Display Name', 'publishpress-authors'),
            'type'        => 'checkbox',
            'sanitize'    => 'absint',
            'tab'         => 'name',
        ];
        $fields['name_size'] = [
            'label'    => esc_html__('Size', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'name',
        ];
        $fields['name_line_height'] = [
            'label'    => esc_html__('Line Height (px)', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'name',
        ];
        $fields['name_weight'] = [
            'label'    => esc_html__('Weight', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''        => esc_html__('Default', 'publishpress-authors'),
                'normal'  => esc_html__('Normal', 'publishpress-authors'),
                'bold'    => esc_html__('Bold', 'publishpress-authors'),
                '100'     => esc_html__('100 - Thin', 'publishpress-authors'),
                '200'     => esc_html__('200 - Extra light', 'publishpress-authors'),
                '300'     => esc_html__('300 - Light', 'publishpress-authors'),
                '400'     => esc_html__('400 - Normal', 'publishpress-authors'),
                '500'     => esc_html__('500 - Medium', 'publishpress-authors'),
                '600'     => esc_html__('600 - Semi bold', 'publishpress-authors'),
                '700'     => esc_html__('700 - Bold', 'publishpress-authors'),
                '800'     => esc_html__('800 - Extra bold', 'publishpress-authors'),
                '900'     => esc_html__('900 - Black', 'publishpress-authors')
            ],
            'tab'      => 'name',
        ];
        $fields['name_transform'] = [
            'label'    => esc_html__('Transform', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''            => esc_html__('Default', 'publishpress-authors'),
                'uppercase'   => esc_html__('Uppercase', 'publishpress-authors'),
                'lowercase'   => esc_html__('Lowercase', 'publishpress-authors'),
                'capitalize'  => esc_html__('Capitalize', 'publishpress-authors'),
                'none'        => esc_html__('Normal', 'publishpress-authors')
            ],
            'tab'      => 'name',
        ];
        $fields['name_style'] = [
            'label'    => esc_html__('Style', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''         => esc_html__('Default', 'publishpress-authors'),
                'none'     => esc_html__('Normal', 'publishpress-authors'),
                'italic'   => esc_html__('Italic', 'publishpress-authors'),
                'oblique'  => esc_html__('Oblique', 'publishpress-authors')
            ],
            'tab'      => 'name',
        ];
        $fields['name_decoration'] = [
            'label'    => esc_html__('Decoration', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''             => esc_html__('Default', 'publishpress-authors'),
                'underline'    => esc_html__('Underline', 'publishpress-authors'),
                'overline'     => esc_html__('Overline', 'publishpress-authors'),
                'line-through' => esc_html__('Line Through', 'publishpress-authors'),
                'none'         => esc_html__('Normal', 'publishpress-authors')
            ],
            'tab'      => 'name',
        ];
        $fields['name_alignment'] = [
            'label'    => esc_html__('Alignment', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''        => esc_html__('Default', 'publishpress-authors'),
                'left'    => esc_html__('Left', 'publishpress-authors'),
                'center'  => esc_html__('Center', 'publishpress-authors'),
                'right'   => esc_html__('Right', 'publishpress-authors'),
                'justify' => esc_html__('Justify', 'publishpress-authors')
            ],
            'tab'      => 'name',
        ];
        $fields['name_color'] = [
            'label'    => esc_html__('Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'name',
        ];
        $fields['name_html_tag'] = [
            'label'    => esc_html__('HTML Tag', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                'h1'   => esc_html__('H1', 'publishpress-authors'),
                'h2'   => esc_html__('H2', 'publishpress-authors'),
                'h3'   => esc_html__('H3', 'publishpress-authors'),
                'h4'   => esc_html__('H4', 'publishpress-authors'),
                'h5'   => esc_html__('H5', 'publishpress-authors'),
                'h6'   => esc_html__('H6', 'publishpress-authors'),
                'div'  => esc_html__('div', 'publishpress-authors'),
                'span' => esc_html__('span', 'publishpress-authors'),
                'p'    => esc_html__('p', 'publishpress-authors')
            ],
            'tab'      => 'name',
        ];

        return $fields;
    }

    /**
     * Add meta fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getMetaFields($fields, $post) 
    {
        $fields['meta_show'] = [
            'label'       => esc_html__('Show Meta Information', 'publishpress-authors'),
            'type'        => 'checkbox',
            'sanitize'    => 'absint',
            'tab'         => 'meta',
        ];
        $fields['meta_view_all_show'] = [
            'label'       => esc_html__('Show "View all posts" link', 'publishpress-authors'),
            'type'        => 'checkbox',
            'sanitize'    => 'absint',
            'tab'         => 'meta',
        ];
        $fields['meta_email_show'] = [
            'label'       => esc_html__('Show Email field', 'publishpress-authors'),
            'type'        => 'checkbox',
            'sanitize'    => 'absint',
            'tab'         => 'meta',
        ];
        $fields['meta_site_link_show'] = [
            'label'    => esc_html__('Show Website field', 'publishpress-authors'),
            'type'     => 'checkbox',
            'sanitize' => 'absint',
            'tab'      => 'meta',
        ];
        $fields['meta_size'] = [
            'label'    => esc_html__('Size', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'meta',
        ];
        $fields['meta_line_height'] = [
            'label'    => esc_html__('Line Height (px)', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'meta',
        ];
        $fields['meta_weight'] = [
            'label'    => esc_html__('Weight', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''        => esc_html__('Default', 'publishpress-authors'),
                'normal'  => esc_html__('Normal', 'publishpress-authors'),
                'bold'    => esc_html__('Bold', 'publishpress-authors'),
                '100'     => esc_html__('100 - Thin', 'publishpress-authors'),
                '200'     => esc_html__('200 - Extra light', 'publishpress-authors'),
                '300'     => esc_html__('300 - Light', 'publishpress-authors'),
                '400'     => esc_html__('400 - Normal', 'publishpress-authors'),
                '500'     => esc_html__('500 - Medium', 'publishpress-authors'),
                '600'     => esc_html__('600 - Semi bold', 'publishpress-authors'),
                '700'     => esc_html__('700 - Bold', 'publishpress-authors'),
                '800'     => esc_html__('800 - Extra bold', 'publishpress-authors'),
                '900'     => esc_html__('900 - Black', 'publishpress-authors')
            ],
            'tab'      => 'meta',
        ];
        $fields['meta_transform'] = [
            'label'    => esc_html__('Transform', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''            => esc_html__('Default', 'publishpress-authors'),
                'uppercase'   => esc_html__('Uppercase', 'publishpress-authors'),
                'lowercase'   => esc_html__('Lowercase', 'publishpress-authors'),
                'capitalize'  => esc_html__('Capitalize', 'publishpress-authors'),
                'none'        => esc_html__('Normal', 'publishpress-authors')
            ],
            'tab'      => 'meta',
        ];
        $fields['meta_style'] = [
            'label'    => esc_html__('Style', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''         => esc_html__('Default', 'publishpress-authors'),
                'none'     => esc_html__('Normal', 'publishpress-authors'),
                'italic'   => esc_html__('Italic', 'publishpress-authors'),
                'oblique'  => esc_html__('Oblique', 'publishpress-authors')
            ],
            'tab'      => 'meta',
        ];
        $fields['meta_decoration'] = [
            'label'    => esc_html__('Decoration', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''             => esc_html__('Default', 'publishpress-authors'),
                'underline'    => esc_html__('Underline', 'publishpress-authors'),
                'overline'     => esc_html__('Overline', 'publishpress-authors'),
                'line-through' => esc_html__('Line Through', 'publishpress-authors'),
                'none'         => esc_html__('Normal', 'publishpress-authors')
            ],
            'tab'      => 'meta',
        ];
        $fields['meta_alignment'] = [
            'label'    => esc_html__('Alignment', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''        => esc_html__('Default', 'publishpress-authors'),
                'left'    => esc_html__('Left', 'publishpress-authors'),
                'center'  => esc_html__('Center', 'publishpress-authors'),
                'right'   => esc_html__('Right', 'publishpress-authors'),
                'justify' => esc_html__('Justify', 'publishpress-authors')
            ],
            'tab'      => 'meta',
        ];
        $fields['meta_color'] = [
            'label'    => esc_html__('Meta Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'meta',
        ];
        $fields['meta_background_color'] = [
            'label'    => esc_html__('Meta Background Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'meta',
        ];
        $fields['meta_link_hover_color'] = [
            'label'    => esc_html__('Meta Link Hover Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'meta',
        ];
        $fields['meta_html_tag'] = [
            'label'    => esc_html__('HTML Tag', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                'h1'   => esc_html__('H1', 'publishpress-authors'),
                'h2'   => esc_html__('H2', 'publishpress-authors'),
                'h3'   => esc_html__('H3', 'publishpress-authors'),
                'h4'   => esc_html__('H4', 'publishpress-authors'),
                'h5'   => esc_html__('H5', 'publishpress-authors'),
                'h6'   => esc_html__('H6', 'publishpress-authors'),
                'div'  => esc_html__('div', 'publishpress-authors'),
                'span' => esc_html__('span', 'publishpress-authors'),
                'p'    => esc_html__('p', 'publishpress-authors')
            ],
            'tab'      => 'meta',
        ];

        return $fields;
    }

    /**
     * Add profile fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getProfileFields($fields, $post) 
    {
        $profile_fields   = MA_Author_Boxes::get_profile_fields($post->ID);
        $index = 0;
        foreach ($profile_fields as $key => $data) {
            if (!in_array($key, MA_Author_Boxes::AUTHOR_BOXES_EXCLUDED_FIELDS)) {
                $index++;
                $fields['profile_fields_' . $key . '_header'] = [
                    'label'       => $data['label'],
                    'index'       => $index,
                    'type'        => 'profile_header',
                    'sanitize'    => 'sanitize_text_field',
                    'tab_name'    => $key,
                    'tab'         => 'profile_fields',
                ];
                $fields['profile_fields_show_' . $key] = [
                    'label'       => sprintf(esc_html__('Show %1s', 'publishpress-authors'), $data['label']),
                    'type'        => 'checkbox',
                    'sanitize'    => 'absint',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'tab'         => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_display_position'] = [
                    'label'       => esc_html__('Show After', 'publishpress-authors'),
                    'type'        => 'select',
                    'sanitize'    => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'options'  => [
                        'meta'  => esc_html__('Meta Row', 'publishpress-authors'),
                        'name' => esc_html__('Name Row', 'publishpress-authors'),
                        'bio'    => esc_html__('Biographical Info Row', 'publishpress-authors')
                    ],
                    'tab'         => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_html_tag'] = [
                    'label'    => esc_html__('HTML Tag', 'publishpress-authors'),
                    'description' => esc_html__('\'span\' will display as an inline element and \'div\' will display as a block element. To make this display into a link, select \'link\' and enter the first part of the URL into the \'Prefix\' field.', 'publishpress-authors'),
                    'type'     => 'select',
                    'sanitize' => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'options'  => [
                        'span' => esc_html__('span', 'publishpress-authors'),
                        'a'    => esc_html__('link', 'publishpress-authors'),
                        'div'  => esc_html__('div', 'publishpress-authors'),
                        'p'    => esc_html__('p', 'publishpress-authors'),
                        'h1'   => esc_html__('H1', 'publishpress-authors'),
                        'h2'   => esc_html__('H2', 'publishpress-authors'),
                        'h3'   => esc_html__('H3', 'publishpress-authors'),
                        'h4'   => esc_html__('H4', 'publishpress-authors'),
                        'h5'   => esc_html__('H5', 'publishpress-authors'),
                        'h6'   => esc_html__('H6', 'publishpress-authors')
                    ],
                    'tab'      => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_value_prefix'] = [
                    'label'       => esc_html__('Value Prefix', 'publishpress-authors'),
                    'description' => esc_html__('This is useful when linking to an email, URL, or phone number. For example, \'mailto:\', \'https://\' or \'tel:\' can be added as the prefix.', 'publishpress-authors'),
                    'type'        => 'text',
                    'sanitize'    => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'tab'         => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_display'] = [
                    'label'    => esc_html__('Display', 'publishpress-authors'),
                    'type'     => 'select',
                    'sanitize' => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'options'  => [
                        'icon_prefix_value_suffix'   => esc_html__('Field Icon + Prefix + Value + Suffix', 'publishpress-authors'),
                        'value' => esc_html__('Field Value', 'publishpress-authors'),
                        'prefix'    => esc_html__('Field Prefix', 'publishpress-authors'),
                        'suffix'    => esc_html__('Field Suffix', 'publishpress-authors'),
                        'icon'     => esc_html__('Field Icon', 'publishpress-authors'),
                        'prefix_value_suffix'   => esc_html__('Field Prefix + Value + Suffix', 'publishpress-authors')
                    ],
                    'tab'      => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_display_prefix'] = [
                    'label'       => esc_html__('Display Prefix', 'publishpress-authors'),
                    'type'        => 'text',
                    'sanitize'    => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'tab'         => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_display_suffix'] = [
                    'label'       => esc_html__('Display Suffix', 'publishpress-authors'),
                    'type'        => 'text',
                    'sanitize'    => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'tab'         => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_before_display_prefix'] = [
                    'label'       => esc_html__('Before Display Prefix', 'publishpress-authors'),
                    'type'        => 'text',
                    'sanitize'    => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'tab'         => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_after_display_suffix'] = [
                    'label'       => esc_html__('After Display Suffix', 'publishpress-authors'),
                    'type'        => 'text',
                    'sanitize'    => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'tab'         => 'profile_fields',
                ];

                $field_description = sprintf(esc_html__('You can use icons from Dashicons and Font Awesome. %1s %2sClick here for documentation%3s.', 'publishpress-authors'), '<br />', '<a href="https://publishpress.com/knowledge-base/author-fields-icons/" target="blank">', '</a>');

                $fields['profile_fields_' . $key . '_display_icon'] = [
                    'label'       => esc_html__('Display Icon', 'publishpress-authors'),
                    'description' => $field_description,
                    'type'        => 'text',
                    'sanitize'    => ['stripslashes_deep', 'wp_kses_post'],
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'tab'         => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_display_icon_size'] = [
                    'label'    => esc_html__('Display Icon Size', 'publishpress-authors'),
                    'type'     => 'number',
                    'sanitize' => 'intval',
                    'tabbed'   => 1,
                    'tab_name' => $key,
                    'tab'      => 'profile_fields',
                ];

                $fields['profile_fields_' . $key . '_display_icon_background_color'] = [
                    'label'    => esc_html__('Display Icon Background Color', 'publishpress-authors'),
                    'type'     => 'color',
                    'sanitize' => 'sanitize_text_field',
                    'tabbed'   => 1,
                    'tab_name' => $key,
                    'tab'      => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_display_icon_border_radius'] = [
                    'label'      => esc_html__('Display Icon Border Radius (%)', 'publishpress-authors'),
                    'type'       => 'number',
                    'min'        => '0',
                    'max'        => '100',
                    'sanitize'   => 'intval',
                    'tabbed'     => 1,
                    'tab_name'   => $key,
                    'tab'        => 'profile_fields',
                ];

                $fields['profile_fields_' . $key . '_size'] = [
                    'label'    => esc_html__('Size', 'publishpress-authors'),
                    'type'     => 'number',
                    'sanitize' => 'intval',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'tab'      => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_line_height'] = [
                    'label'    => esc_html__('Line Height (px)', 'publishpress-authors'),
                    'type'     => 'number',
                    'sanitize' => 'intval',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'tab'      => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_weight'] = [
                    'label'    => esc_html__('Weight', 'publishpress-authors'),
                    'type'     => 'select',
                    'sanitize' => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'options'  => [
                        ''        => esc_html__('Default', 'publishpress-authors'),
                        'normal'  => esc_html__('Normal', 'publishpress-authors'),
                        'bold'    => esc_html__('Bold', 'publishpress-authors'),
                        '100'     => esc_html__('100 - Thin', 'publishpress-authors'),
                        '200'     => esc_html__('200 - Extra light', 'publishpress-authors'),
                        '300'     => esc_html__('300 - Light', 'publishpress-authors'),
                        '400'     => esc_html__('400 - Normal', 'publishpress-authors'),
                        '500'     => esc_html__('500 - Medium', 'publishpress-authors'),
                        '600'     => esc_html__('600 - Semi bold', 'publishpress-authors'),
                        '700'     => esc_html__('700 - Bold', 'publishpress-authors'),
                        '800'     => esc_html__('800 - Extra bold', 'publishpress-authors'),
                        '900'     => esc_html__('900 - Black', 'publishpress-authors')
                    ],
                    'tab'      => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_transform'] = [
                    'label'    => esc_html__('Transform', 'publishpress-authors'),
                    'type'     => 'select',
                    'sanitize' => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'options'  => [
                        ''            => esc_html__('Default', 'publishpress-authors'),
                        'uppercase'   => esc_html__('Uppercase', 'publishpress-authors'),
                        'lowercase'   => esc_html__('Lowercase', 'publishpress-authors'),
                        'capitalize'  => esc_html__('Capitalize', 'publishpress-authors'),
                        'none'        => esc_html__('Normal', 'publishpress-authors')
                    ],
                    'tab'      => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_style'] = [
                    'label'    => esc_html__('Style', 'publishpress-authors'),
                    'type'     => 'select',
                    'sanitize' => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'options'  => [
                        ''         => esc_html__('Default', 'publishpress-authors'),
                        'none'     => esc_html__('Normal', 'publishpress-authors'),
                        'italic'   => esc_html__('Italic', 'publishpress-authors'),
                        'oblique'  => esc_html__('Oblique', 'publishpress-authors')
                    ],
                    'tab'      => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_decoration'] = [
                    'label'    => esc_html__('Decoration', 'publishpress-authors'),
                    'type'     => 'select',
                    'sanitize' => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'options'  => [
                        ''             => esc_html__('Default', 'publishpress-authors'),
                        'underline'    => esc_html__('Underline', 'publishpress-authors'),
                        'overline'     => esc_html__('Overline', 'publishpress-authors'),
                        'line-through' => esc_html__('Line Through', 'publishpress-authors'),
                        'none'         => esc_html__('Normal', 'publishpress-authors')
                    ],
                    'tab'      => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_alignment'] = [
                    'label'    => esc_html__('Alignment', 'publishpress-authors'),
                    'type'     => 'select',
                    'sanitize' => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'options'  => [
                        ''        => esc_html__('Default', 'publishpress-authors'),
                        'left'    => esc_html__('Left', 'publishpress-authors'),
                        'center'  => esc_html__('Center', 'publishpress-authors'),
                        'right'   => esc_html__('Right', 'publishpress-authors'),
                        'justify' => esc_html__('Justify', 'publishpress-authors')
                    ],
                    'tab'      => 'profile_fields',
                ];
                $fields['profile_fields_' . $key . '_color'] = [
                    'label'    => esc_html__('Color', 'publishpress-authors'),
                    'type'     => 'color',
                    'sanitize' => 'sanitize_text_field',
                    'tabbed'      => 1,
                    'tab_name'    => $key,
                    'tab'      => 'profile_fields',
                ];
            }
        }

        return $fields;
    }

    /**
     * Add bio fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getBioFields($fields, $post) 
    {
        $fields['author_bio_show'] = [
            'label'       => esc_html__('Show Biographical Info', 'publishpress-authors'),
            'type'        => 'checkbox',
            'sanitize'    => 'absint',
            'tab'         => 'author_bio',
        ];
        $fields['author_bio_limit'] = [
            'label'    => esc_html__('Character Limit', 'publishpress-authors'),
            'min'      => 0,
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'author_bio',
        ];
        $fields['author_bio_size'] = [
            'label'    => esc_html__('Size', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'author_bio',
        ];
        $fields['author_bio_line_height'] = [
            'label'    => esc_html__('Line Height (px)', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'author_bio',
        ];
        $fields['author_bio_weight'] = [
            'label'    => esc_html__('Weight', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''        => esc_html__('Default', 'publishpress-authors'),
                'normal'  => esc_html__('Normal', 'publishpress-authors'),
                'bold'    => esc_html__('Bold', 'publishpress-authors'),
                '100'     => esc_html__('100 - Thin', 'publishpress-authors'),
                '200'     => esc_html__('200 - Extra light', 'publishpress-authors'),
                '300'     => esc_html__('300 - Light', 'publishpress-authors'),
                '400'     => esc_html__('400 - Normal', 'publishpress-authors'),
                '500'     => esc_html__('500 - Medium', 'publishpress-authors'),
                '600'     => esc_html__('600 - Semi bold', 'publishpress-authors'),
                '700'     => esc_html__('700 - Bold', 'publishpress-authors'),
                '800'     => esc_html__('800 - Extra bold', 'publishpress-authors'),
                '900'     => esc_html__('900 - Black', 'publishpress-authors')
            ],
            'tab'      => 'author_bio',
        ];
        $fields['author_bio_transform'] = [
            'label'    => esc_html__('Transform', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''            => esc_html__('Default', 'publishpress-authors'),
                'uppercase'   => esc_html__('Uppercase', 'publishpress-authors'),
                'lowercase'   => esc_html__('Lowercase', 'publishpress-authors'),
                'capitalize'  => esc_html__('Capitalize', 'publishpress-authors'),
                'none'        => esc_html__('Normal', 'publishpress-authors')
            ],
            'tab'      => 'author_bio',
        ];
        $fields['author_bio_style'] = [
            'label'    => esc_html__('Style', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''         => esc_html__('Default', 'publishpress-authors'),
                'none'     => esc_html__('Normal', 'publishpress-authors'),
                'italic'   => esc_html__('Italic', 'publishpress-authors'),
                'oblique'  => esc_html__('Oblique', 'publishpress-authors')
            ],
            'tab'      => 'author_bio',
        ];
        $fields['author_bio_decoration'] = [
            'label'    => esc_html__('Decoration', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''             => esc_html__('Default', 'publishpress-authors'),
                'underline'    => esc_html__('Underline', 'publishpress-authors'),
                'overline'     => esc_html__('Overline', 'publishpress-authors'),
                'line-through' => esc_html__('Line Through', 'publishpress-authors'),
                'none'         => esc_html__('Normal', 'publishpress-authors')
            ],
            'tab'      => 'author_bio',
        ];
        $fields['author_bio_alignment'] = [
            'label'    => esc_html__('Alignment', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''        => esc_html__('Default', 'publishpress-authors'),
                'left'    => esc_html__('Left', 'publishpress-authors'),
                'center'  => esc_html__('Center', 'publishpress-authors'),
                'right'   => esc_html__('Right', 'publishpress-authors'),
                'justify' => esc_html__('Justify', 'publishpress-authors')
            ],
            'tab'      => 'author_bio',
        ];
        $fields['author_bio_color'] = [
            'label'    => esc_html__('Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'author_bio',
        ];

        $fields['author_bio_html_tag'] = [
            'label'    => esc_html__('HTML Tag', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                'h1'   => esc_html__('H1', 'publishpress-authors'),
                'h2'   => esc_html__('H2', 'publishpress-authors'),
                'h3'   => esc_html__('H3', 'publishpress-authors'),
                'h4'   => esc_html__('H4', 'publishpress-authors'),
                'h5'   => esc_html__('H5', 'publishpress-authors'),
                'h6'   => esc_html__('H6', 'publishpress-authors'),
                'div'  => esc_html__('div', 'publishpress-authors'),
                'span' => esc_html__('span', 'publishpress-authors'),
                'p'    => esc_html__('p', 'publishpress-authors')
            ],
            'tab'      => 'author_bio',
        ];

        return $fields;
    }

    /**
     * Add recent posts fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getRecentPostsFields($fields, $post) 
    {
        $fields['author_recent_posts_show'] = [
            'label'       => esc_html__('Show Recent Posts', 'publishpress-authors'),
            'type'        => 'checkbox',
            'sanitize'    => 'absint',
            'tab'         => 'author_recent_posts',
        ];
        $fields['author_recent_posts_title_show'] = [
            'label'       => esc_html__('Show Recent Title', 'publishpress-authors'),
            'type'        => 'checkbox',
            'sanitize'    => 'absint',
            'tab'         => 'author_recent_posts',
        ];
        $fields['author_recent_posts_empty_show'] = [
            'label'       => esc_html__('Show Even if No Recent Post', 'publishpress-authors'),
            'type'        => 'checkbox',
            'sanitize'    => 'absint',
            'tab'         => 'author_recent_posts',
        ];
        $fields['author_recent_posts_title_color'] = [
            'label'    => esc_html__('Recent Post Title Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_title_border_bottom_style'] = [
            'label'    => esc_html__('Recent Post Title Border Bottom Style', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                'none'   => esc_html__('None', 'publishpress-authors'),
                'dotted' => esc_html__('Dotted', 'publishpress-authors'),
                'dashed' => esc_html__('Dashed', 'publishpress-authors'),
                'solid'  => esc_html__('Solid', 'publishpress-authors'),
                'double' => esc_html__('Double', 'publishpress-authors'),
                'groove' => esc_html__('Groove', 'publishpress-authors'),
                'ridge'  => esc_html__('Ridge', 'publishpress-authors'),
                'inset'  => esc_html__('Inset', 'publishpress-authors'),
                'outset' => esc_html__('Outset', 'publishpress-authors')
            ],
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_title_border_width'] = [
            'label'    => esc_html__('Recent Post Title Border Width', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_title_border_color'] = [
            'label'    => esc_html__('Recent Post Title Border Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_limit'] = [
            'label'    => esc_html__('Limit', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_orderby'] = [
            'label'    => esc_html__('Order By', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                'date'          => esc_html__('Date', 'publishpress-authors'),
                'modified'       => esc_html__('Modified date', 'publishpress-authors'),
                'title'         => esc_html__('Title', 'publishpress-authors'),
                'ID'            => esc_html__('ID', 'publishpress-authors'),
                'comment_count' => esc_html__('Number of comments', 'publishpress-authors'),
                'rand'          => esc_html__('Random', 'publishpress-authors')
            ],
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_order'] = [
            'label'    => esc_html__('Order', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                'ASC'  => esc_html__('Ascending', 'publishpress-authors'),
                'DESC' => esc_html__('Descending', 'publishpress-authors')
            ],
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_size'] = [
            'label'    => esc_html__('Size', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_line_height'] = [
            'label'    => esc_html__('Line Height (px)', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_weight'] = [
            'label'    => esc_html__('Weight', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''        => esc_html__('Default', 'publishpress-authors'),
                'normal'  => esc_html__('Normal', 'publishpress-authors'),
                'bold'    => esc_html__('Bold', 'publishpress-authors'),
                '100'     => esc_html__('100 - Thin', 'publishpress-authors'),
                '200'     => esc_html__('200 - Extra light', 'publishpress-authors'),
                '300'     => esc_html__('300 - Light', 'publishpress-authors'),
                '400'     => esc_html__('400 - Normal', 'publishpress-authors'),
                '500'     => esc_html__('500 - Medium', 'publishpress-authors'),
                '600'     => esc_html__('600 - Semi bold', 'publishpress-authors'),
                '700'     => esc_html__('700 - Bold', 'publishpress-authors'),
                '800'     => esc_html__('800 - Extra bold', 'publishpress-authors'),
                '900'     => esc_html__('900 - Black', 'publishpress-authors')
            ],
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_transform'] = [
            'label'    => esc_html__('Transform', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''            => esc_html__('Default', 'publishpress-authors'),
                'uppercase'   => esc_html__('Uppercase', 'publishpress-authors'),
                'lowercase'   => esc_html__('Lowercase', 'publishpress-authors'),
                'capitalize'  => esc_html__('Capitalize', 'publishpress-authors'),
                'none'        => esc_html__('Normal', 'publishpress-authors')
            ],
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_style'] = [
            'label'    => esc_html__('Style', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''         => esc_html__('Default', 'publishpress-authors'),
                'none'     => esc_html__('Normal', 'publishpress-authors'),
                'italic'   => esc_html__('Italic', 'publishpress-authors'),
                'oblique'  => esc_html__('Oblique', 'publishpress-authors')
            ],
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_decoration'] = [
            'label'    => esc_html__('Decoration', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''             => esc_html__('Default', 'publishpress-authors'),
                'underline'    => esc_html__('Underline', 'publishpress-authors'),
                'overline'     => esc_html__('Overline', 'publishpress-authors'),
                'line-through' => esc_html__('Line Through', 'publishpress-authors'),
                'none'         => esc_html__('Normal', 'publishpress-authors')
            ],
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_alignment'] = [
            'label'    => esc_html__('Alignment', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                ''        => esc_html__('Default', 'publishpress-authors'),
                'left'    => esc_html__('Left', 'publishpress-authors'),
                'center'  => esc_html__('Center', 'publishpress-authors'),
                'right'   => esc_html__('Right', 'publishpress-authors'),
                'justify' => esc_html__('Justify', 'publishpress-authors')
            ],
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_color'] = [
            'label'    => esc_html__('Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_icon_color'] = [
            'label'    => esc_html__('Icon Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'author_recent_posts',
        ];
        $fields['author_recent_posts_html_tag'] = [
            'label'    => esc_html__('HTML Tag', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                'h1'   => esc_html__('H1', 'publishpress-authors'),
                'h2'   => esc_html__('H2', 'publishpress-authors'),
                'h3'   => esc_html__('H3', 'publishpress-authors'),
                'h4'   => esc_html__('H4', 'publishpress-authors'),
                'h5'   => esc_html__('H5', 'publishpress-authors'),
                'h6'   => esc_html__('H6', 'publishpress-authors'),
                'div'  => esc_html__('div', 'publishpress-authors'),
                'span' => esc_html__('span', 'publishpress-authors'),
                'p'    => esc_html__('p', 'publishpress-authors')
            ],
            'tab'      => 'author_recent_posts',
        ];

        return $fields;
    }

    /**
     * Add box layout fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getBoxLayoutFields($fields, $post) 
    {

        $fields['box_tab_layout_prefix'] = [
            'label'       => esc_html__('Row Prefix', 'publishpress-authors'),
            'description' => esc_html__('Enter the text that should be added before authors. This field accepts basic HTML.', 'publishpress-authors'),
            'placeholder' => '',
            'type'     => 'text',
            'sanitize' => ['stripslashes_deep', 'wp_kses_post'],
            'tab'      => 'box_layout',
        ];
        $fields['box_tab_layout_suffix'] = [
            'label'       => esc_html__('Row Suffix', 'publishpress-authors'),
            'description' => esc_html__('Enter the text that should be added after authors. This field accepts basic HTML.', 'publishpress-authors'),
            'placeholder' => '',
            'type'     => 'text',
            'sanitize' => ['stripslashes_deep', 'wp_kses_post'],
            'tab'      => 'box_layout',
        ];
        $fields['box_tab_layout_author_separator'] = [
            'label'       => esc_html__('Author Separator', 'publishpress-authors'),
            'description' => esc_html__('You can specify a separator such as \',\' to separate authors. This field accepts basic HTML.', 'publishpress-authors'),
            'placeholder' => '',
            'type'     => 'text',
            'sanitize' => ['stripslashes_deep', 'wp_kses_post'],
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_margin_top'] = [
            'label'    => esc_html__('Margin Top', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_margin_bottom'] = [
            'label'    => esc_html__('Margin Bottom', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_margin_left'] = [
            'label'    => esc_html__('Margin Left', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_margin_right'] = [
            'label'    => esc_html__('Margin Right', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_padding_top'] = [
            'label'    => esc_html__('Padding Top', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_padding_bottom'] = [
            'label'    => esc_html__('Padding Bottom', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_padding_left'] = [
            'label'    => esc_html__('Padding Left', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_padding_right'] = [
            'label'    => esc_html__('Padding Right', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_border_width'] = [
            'label'    => esc_html__('Border Width (px)', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_border_style'] = [
            'label'    => esc_html__('Border Style', 'publishpress-authors'),
            'type'     => 'select',
            'sanitize' => 'sanitize_text_field',
            'options'  => [
                'none'   => esc_html__('None', 'publishpress-authors'),
                'dotted' => esc_html__('Dotted', 'publishpress-authors'),
                'dashed' => esc_html__('Dashed', 'publishpress-authors'),
                'solid'  => esc_html__('Solid', 'publishpress-authors'),
                'rand'   => esc_html__('Random', 'publishpress-authors'),
                'double' => esc_html__('Double', 'publishpress-authors'),
                'groove' => esc_html__('Groove', 'publishpress-authors'),
                'ridge'  => esc_html__('Ridge', 'publishpress-authors'),
                'inset'  => esc_html__('Inset', 'publishpress-authors'),
                'outset' => esc_html__('Outset', 'publishpress-authors')
            ],
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_border_color'] = [
            'label'    => esc_html__('Border color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_box_width'] = [
            'label'      => esc_html__('Box Width (%)', 'publishpress-authors'),
            'type'       => 'number',
            'min'        => '0',
            'max'        => '100',
            'show_input' => true,
            'sanitize'   => 'intval',
            'tab'        => 'box_layout',
        ];
        $fields['box_layout_border_radius'] = [
            'label'    => esc_html__('Border radius (px)', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_background_color'] = [
            'label'    => esc_html__('Background Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_color'] = [
            'label'    => esc_html__('Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_shadow_color'] = [
            'label'    => esc_html__('Shadow Color', 'publishpress-authors'),
            'type'     => 'color',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_shadow_horizontal_offset'] = [
            'label'    => esc_html__('Shadow Horizontal Offset', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_shadow_vertical_offset'] = [
            'label'    => esc_html__('Shadow Vertical Offset', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_shadow_blur'] = [
            'label'    => esc_html__('Shadow Blur', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];
        $fields['box_layout_shadow_speed'] = [
            'label'    => esc_html__('Shadow spread', 'publishpress-authors'),
            'type'     => 'number',
            'sanitize' => 'intval',
            'tab'      => 'box_layout',
        ];

        return $fields;
    }

    /**
     * Add custom css fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getCustomCssFields($fields, $post) 
    {
        $fields['box_tab_custom_css'] = [
            'label'       => esc_html__('Custom CSS', 'publishpress-authors'),
            'placeholder' => esc_html__('Add Custom CSS styles here...', 'publishpress-authors'),
            'type'        => 'textarea',
            'sanitize'    => 'sanitize_textarea_field',
            'tab'         => 'custom_css',
        ];

        $fields['box_tab_custom_wrapper_class'] = [
            'label'       => esc_html__('Layout Wrapper Class Name', 'publishpress-authors'),
            'description' => esc_html__('You can use multiple class names. Leave a space between each class.', 'publishpress-authors'),
            'placeholder' => esc_html__('Enter class name without dot(.)', 'publishpress-authors'),
            'type'     => 'text',
            'sanitize' => 'sanitize_text_field',
            'tab'      => 'custom_css',
        ];

        return $fields;
    }

    /**
     * Add export fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getExportFields($fields, $post) 
    {
        $fields['export_action'] = [
            'label'       => esc_html__('Export', 'publishpress-authors'),
            'type'     => 'export_action',
            'tab'      => 'export',
            'readonly' => true,
        ];

        return $fields;
    }

    /**
     * Add import fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getImportFields($fields, $post) 
    {
        
        $fields['import_action'] = [
            'label'       => esc_html__('Import', 'publishpress-authors'),
            'type'     => 'import_action',
            'tab'      => 'import',
        ];

        return $fields;
    }

    /**
     * Add download template fields to the author boxes editor.
     *
     * @param array $fields Existing fields to display.
     * @param WP_Post $post object.
     */
    public static function getGenerateTemplateFields($fields, $post) 
    {

        $fields['template_action'] = [
            'label'       => esc_html__('Generate Theme Template', 'publishpress-authors'),
            'type'     => 'template_action',
            'tab'      => 'generate_template',
            'readonly' => true,
        ];

        return $fields;
    }

}
