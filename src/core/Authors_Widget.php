<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       3.4.0
 */

namespace MultipleAuthors;

use MultipleAuthors\Classes\Utils;
use WP_Widget;

class Authors_Widget extends WP_Widget
{
    /**
     * Sets up the widgets name etc
     */
    public function __construct()
    {
        $this->title = esc_html__('Authors List', 'publishpress-authors');
        parent::__construct(
            'multiple_authors_list_widget',
            $this->title,
            array(
                'classname'   => 'multiple_authors_authors-list_widget',
                'description' => esc_html__(
                    'Display authors list.',
                    'publishpress-authors'
                ),
            )
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget($args, $instance)
    {
        $instance = wp_parse_args(
            (array)$instance,
            array(
                'title' => esc_html($this->title),
            )
        );

        /** This filter is documented in core/src/wp-includes/default-widgets.php */
        $title = apply_filters(
            'widget_title',
            isset($instance['title']) ? esc_html($instance['title']) : '',
            $instance,
            $this->id_base
        );

        $output = '';

        $output .= $this->get_author_box_markup($args, $instance);
        if (!empty($output)) {
            if (isset($args['before_widget'])) {
                echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }

            if (!isset($instance['show_title']) || true === $instance['show_title']) {
                echo sprintf(
                    '%s<h2 class="widget-title">%s</h2>%s',
                    isset($args['before_title']) ? $args['before_title'] : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    esc_html(apply_filters('widget_title', $title)), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    isset($args['after_title']) ? $args['after_title'] : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                );
            }

            echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

            if (isset($args['after_widget'])) {
                echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            }
        }
    }

    /**
     * Outputs the options form on admin
     *
     * @param array $instance The widget options
     */
    public function form($instance)
    {
        $legacyPlugin = Factory::getLegacyPlugin();

        $instance = wp_parse_args(
            (array)$instance,
            array(
                'title'      => esc_html($this->title),
                'layout'     => esc_html($legacyPlugin->modules->multiple_authors->options->layout),
                'show_empty' => true
            )
        );

        $title     = esc_html($instance['title']);
        $layout    = esc_html($instance['layout']);
        $showEmpty = isset($instance['show_empty']) && (bool)$instance['show_empty'];
        $context   = array(
            'labels'  => array(
                'title'      => esc_html__('Title', 'publishpress-authors'),
                'layout'     => esc_html__('Layout', 'publishpress-authors'),
                'show_empty' => esc_html__(
                    'Display All Authors (including those who have not written any posts)',
                    'publishpress-authors'
                )
            ),
            'ids'     => array(
                'title'      => esc_html($this->get_field_id('title')),
                'layout'     => esc_html($this->get_field_id('layout')),
                'show_empty' => esc_html($this->get_field_id('show_empty'))
            ),
            'names'   => array(
                'title'      => esc_html($this->get_field_name('title')),
                'layout'     => esc_html($this->get_field_name('layout')),
                'show_empty' => esc_html($this->get_field_name('show_empty'))
            ),
            'values'  => array(
                'title'      => esc_html($title),
                'layout'     => esc_html($layout),
                'show_empty' => $showEmpty
            ),
            'layouts' => apply_filters('pp_multiple_authors_author_layouts', array()),
        );

        $container = Factory::get_container();

        echo $container['twig']->render('authors-list-widget-form.twig', $context); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Processing widget options on save
     *
     * @param array $new_instance The new options
     * @param array $old_instance The previous options
     */
    public function update($new_instance, $old_instance)
    {
        $instance = array();

        $instance['title']      = sanitize_text_field($new_instance['title']);
        $instance['layout']     = sanitize_text_field($new_instance['layout']);
        $instance['show_empty'] = isset($new_instance['show_empty']) ? (bool)$new_instance['show_empty'] : false;
        $layouts                = apply_filters('pp_multiple_authors_author_layouts', array());

        if (!array_key_exists($instance['layout'], $layouts)) {
            $instance['layout'] = Utils::getDefaultLayout();
        }

        return $instance;
    }


    /**
     * Get HTML markdown
     *
     * @param array $args The args.
     * @param array $instance The object instance.
     *
     * @return string $html The html.
     */
    private function get_author_box_markup(
        $args,
        $instance,
        $target = 'widget'
    ) {
        $html = '';

        $legacyPlugin = Factory::getLegacyPlugin();

        if (apply_filters('publishpress_authors_load_style_in_frontend', PUBLISHPRESS_AUTHORS_LOAD_STYLE_IN_FRONTEND)) {
            wp_enqueue_style('dashicons');
            wp_enqueue_style(
                'multiple-authors-widget-css',
                PP_AUTHORS_ASSETS_URL . 'css/multiple-authors-widget.css',
                false,
                PP_AUTHORS_VERSION,
                'all'
            );
        }

        if (!function_exists('multiple_authors')) {
            require_once PP_AUTHORS_BASE_PATH . 'functions/template-tags.php';
        }

        $css_class = '';
        if (!empty($target)) {
            $css_class = 'multiple-authors-target-' . str_replace('_', '-', $target);
        }

        $title = isset($instance['title']) ? esc_html($instance['title']) : '';

        $layout = isset($instance['layout']) ? $instance['layout'] : null;
        if (empty($layout)) {
            $layout = isset($legacyPlugin->modules->multiple_authors->options->layout)
                ? $legacyPlugin->modules->multiple_authors->options->layout : Utils::getDefaultLayout();
        }

        if (empty($color_scheme)) {
            $color_scheme = isset($legacyPlugin->modules->multiple_authors->options->color_scheme)
                ? $legacyPlugin->modules->multiple_authors->options->color_scheme : '#655997';
        }

        $show_email = isset($legacyPlugin->modules->multiple_authors->options->show_email_link)
            ? 'yes' === $legacyPlugin->modules->multiple_authors->options->show_email_link : true;

        $show_site = isset($legacyPlugin->modules->multiple_authors->options->show_site_link)
            ? 'yes' === $legacyPlugin->modules->multiple_authors->options->show_site_link : true;

        $showEmpty = isset($instance['show_empty']) ? $instance['show_empty'] : false;

        $args = [
            'show_title'   => false,
            'css_class'    => esc_attr($css_class),
            'title'        => $title,
            'authors'      => multiple_authors_get_all_authors(array('hide_empty' => !$showEmpty)),
            'target'       => $target,
            'item_class'   => 'author url fn',
            'layout'       => $layout,
            'color_scheme' => $color_scheme,
            'show_email'   => $show_email,
            'show_site'    => $show_site
        ];

        /**
         * Filter the author box arguments before sending to the renderer.
         *
         * @param array $args
         */
        $args = apply_filters('pp_multiple_authors_authors_list_box_args', $args);

        /**
         * Filter the author box HTML code, allowing to use custom rendered layouts.
         *
         * @param string $html
         * @param array $args
         */
        $html = apply_filters('pp_multiple_authors_authors_list_box_html', null, $args);

        return $html;
    }

}
