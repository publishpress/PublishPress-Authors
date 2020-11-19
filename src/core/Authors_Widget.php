<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       3.4.0
 */

namespace MultipleAuthors;

use WP_Widget;

class Authors_Widget extends WP_Widget
{

    /**
     * Sets up the widgets name etc
     */
    public function __construct()
    {
        $this->title = esc_html__('Authors List', 'publishpress-authors');
        Parent::__construct(
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
                'title' => $this->title,
            )
        );

        /** This filter is documented in core/src/wp-includes/default-widgets.php */
        $title  = apply_filters('widget_title', $instance['title'], $instance, $this->id_base);
        $output = '';


        $output .= $this->get_author_box_markup($args, $instance);
        if (!empty($output)) {
            echo $args['before_widget'];
            echo $args['before_title'] . apply_filters('widget_title', $title) . $args['after_title'];
            echo $output;
            echo $args['after_widget'];
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
                'title'      => $this->title,
                'layout'     => $legacyPlugin->modules->multiple_authors->options->layout,
                'show_empty' => true
            )
        );

        $title     = strip_tags($instance['title']);
        $layout    = strip_tags($instance['layout']);
        $showEmpty = isset($instance['show_empty']) ? (bool)$instance['show_empty'] : false;
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
                'title'      => $this->get_field_id('title'),
                'layout'     => $this->get_field_id('layout'),
                'show_empty' => $this->get_field_id('show_empty')
            ),
            'names'   => array(
                'title'      => $this->get_field_name('title'),
                'layout'     => $this->get_field_name('layout'),
                'show_empty' => $this->get_field_name('show_empty')
            ),
            'values'  => array(
                'title'      => $title,
                'layout'     => $layout,
                'show_empty' => $showEmpty
            ),
            'layouts' => apply_filters('pp_multiple_authors_author_layouts', array()),
        );

        $container = Factory::get_container();

        echo $container['twig']->render('authors-list-widget-form.twig', $context);
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
            $instance['layout'] = 'simple_list';
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

        wp_enqueue_style('dashicons');
        wp_enqueue_style(
            'multiple-authors-widget-css',
            PP_AUTHORS_ASSETS_URL . 'css/multiple-authors-widget.css',
            false,
            PP_AUTHORS_VERSION,
            'all'
        );

        if (!function_exists('multiple_authors')) {
            require_once PP_AUTHORS_BASE_PATH . 'functions/template-tags.php';
        }

        $css_class = '';
        if (!empty($target)) {
            $css_class = 'multiple-authors-target-' . str_replace('_', '-', $target);
        }

        $title = $instance['title'];
        $title = esc_html($title);

        $layout = $instance['layout'];
        if (empty($layout)) {
            $layout = isset($legacyPlugin->modules->multiple_authors->options->layout)
                ? $legacyPlugin->modules->multiple_authors->options->layout : 'simple_list';
        }

        $show_email = isset($legacyPlugin->modules->multiple_authors->options->show_email_link)
            ? 'yes' === $legacyPlugin->modules->multiple_authors->options->show_email_link : true;

        $show_site = isset($legacyPlugin->modules->multiple_authors->options->show_site_link)
            ? 'yes' === $legacyPlugin->modules->multiple_authors->options->show_site_link : true;

        $showEmpty = isset($instance['show_empty']) ? $instance['show_empty'] : false;

        $args = [
            'show_title' => false,
            'css_class'  => $css_class,
            'title'      => $title,
            'authors'    => multiple_authors_get_all_authors(array('hide_empty' => !$showEmpty)),
            'target'     => $target,
            'item_class' => 'author url fn',
            'layout'     => $layout,
            'show_email' => $show_email,
            'show_site'  => $show_site
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
