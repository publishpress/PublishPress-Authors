<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       3.4.0
 */

namespace MultipleAuthors;

use MultipleAuthors\Classes\Author_Editor;
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
                'title'          => esc_html($this->title),
                'layout'         => esc_html($legacyPlugin->modules->multiple_authors->options->layout),
                'show_empty'     => true,
                'search_box'     => false,
                'limit_per_page' => '',
                'authors'        => '',
                'order'          => 'asc',
                'orderby'        => 'name',
                'search_field'    => 'first_name,last_name'
            )
        );

        $showEmpty      = isset($instance['show_empty']) && (bool)$instance['show_empty'];
        $searchBox      = isset($instance['search_box']) && (bool)$instance['search_box'];
        $title          = esc_html($instance['title']);
        $layout         = esc_html($instance['layout']);
        $limitPerPage   = esc_html($instance['limit_per_page']);
        $authors        = esc_html($instance['authors']);
        $order          = esc_html($instance['order']);
        $orderBy        = esc_html($instance['orderby']);
        $search_field    = esc_html($instance['search_field']);

        $context   = array(
            'labels'  => array(
                'title'      => esc_html__('Title', 'publishpress-authors'),
                'layout'     => esc_html__('Layout', 'publishpress-authors'),
                'show_empty' => esc_html__(
                    'Display All Authors (including those who have not written any posts)',
                    'publishpress-authors'
                ),
                'search_box'     => esc_html__('Enable author\'s search box', 'publishpress-authors'),
                'limit_per_page' => esc_html__('Limits per page', 'publishpress-authors'),
                'authors'        => esc_html__('Authors', 'publishpress-authors'),
                'order'          => esc_html__('Order', 'publishpress-authors'),
                'orderby'        => esc_html__('Order by', 'publishpress-authors'),
                'search_field'    => esc_html__('Author\'s search box field (Seperate multiple fields by comma(\',\'))', 'publishpress-authors')
            ),
            'ids'     => array(
                'title'          => esc_html($this->get_field_id('title')),
                'layout'         => esc_html($this->get_field_id('layout')),
                'show_empty'     => esc_html($this->get_field_id('show_empty')),
                'search_box'     => esc_html($this->get_field_id('search_box')),
                'search_field'    => esc_html($this->get_field_id('search_field')),
                'limit_per_page' => esc_html($this->get_field_id('limit_per_page')),
                'authors'        => esc_html($this->get_field_id('authors')),
                'order'          => esc_html($this->get_field_id('order')),
                'orderby'        => esc_html($this->get_field_id('orderby'))
            ),
            'names'   => array(
                'title'          => esc_html($this->get_field_name('title')),
                'layout'         => esc_html($this->get_field_name('layout')),
                'show_empty'     => esc_html($this->get_field_name('show_empty')),
                'search_box'     => esc_html($this->get_field_name('search_box')),
                'search_field'    => esc_html($this->get_field_name('search_field')),
                'limit_per_page' => esc_html($this->get_field_name('limit_per_page')),
                'authors'        => esc_html($this->get_field_name('authors')),
                'order'          => esc_html($this->get_field_name('order')),
                'orderby'        => esc_html($this->get_field_name('orderby'))
            ),
            'values'  => array(
                'title'          => esc_html($title),
                'layout'         => esc_html($layout),
                'show_empty'     => $showEmpty,
                'search_box'     => $searchBox,
                'search_field'    => $search_field,
                'limit_per_page' => $limitPerPage,
                'authors'        => $authors,
                'order'          => $order,
                'orderby'        => $orderBy
            ),
            'options'  => array(
                'authors'        => [
                    ''          => esc_html__('All Authors', 'publishpress-authors'),
                    'guests'    => esc_html__('Guest Authors', 'publishpress-authors'),
                    'users'     => esc_html__('Users Authors', 'publishpress-authors')
                ],
                'order'           => [
                    'asc'       => esc_html__('Ascending', 'publishpress-authors'),
                    'desc'      => esc_html__('Descending', 'publishpress-authors')
                ],
                'orderby'         => [
                    'name'       => esc_html__('Name', 'publishpress-authors'),
                    'count'      => esc_html__('Post Counts', 'publishpress-authors')
                ]
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

        $instance['title']          = sanitize_text_field($new_instance['title']);
        $instance['layout']         = sanitize_text_field($new_instance['layout']);
        $instance['limit_per_page'] = isset($new_instance['limit_per_page']) ? sanitize_text_field($new_instance['limit_per_page']) : '';
        $instance['authors']        = isset($new_instance['authors']) ? sanitize_text_field($new_instance['authors']) : '';
        $instance['order']          = isset($new_instance['order']) ? sanitize_text_field($new_instance['order']) : '';
        $instance['orderby']        = isset($new_instance['orderby']) ? sanitize_text_field($new_instance['orderby']) : '';
        $instance['show_empty']     = isset($new_instance['show_empty']) ? (bool)$new_instance['show_empty'] : false;
        $instance['search_field']    = isset($new_instance['search_field']) ? sanitize_text_field($new_instance['search_field']) : '';
        $instance['search_box']     = isset($new_instance['search_box']) ? (bool)$new_instance['search_box'] : false;
        $layouts                    = apply_filters('pp_multiple_authors_author_layouts', array());

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

            if (isset($instance['authors_recent_col']) && (int)$instance['authors_recent_col'] > 0) {
                $column_width = ((100-8)/(int)$instance['authors_recent_col']);
                $inline_style = '@media (min-width: 768px) {
                    .pp-multiple-authors-recent .ppma-col-md-3 {
                        -webkit-box-flex: 0;
                        -webkit-flex: 0 0 '.$column_width.'%;
                        -moz-box-flex: 0;
                        -ms-flex: 0 0 '.$column_width.'%;
                        flex: 0 0 '.$column_width.'%;
                        max-width: '.$column_width.'%;
                    }
                }';
                wp_add_inline_style('multiple-authors-widget-css', $inline_style);
            }

            //load font awesome assets if enable
            $load_font_awesome = isset($legacyPlugin->modules->multiple_authors->options->load_font_awesome)
            ? 'yes' === $legacyPlugin->modules->multiple_authors->options->load_font_awesome : true;

            if ($load_font_awesome) {
                wp_enqueue_style(
                    'multiple-authors-fontawesome',
                    PP_AUTHORS_ASSETS_URL . 'lib/fontawesome/css/fontawesome.min.css',
                    false,
                    PP_AUTHORS_VERSION,
                    'all'
                );
    
                wp_enqueue_script(
                    'multiple-authors-fontawesome',
                    PP_AUTHORS_ASSETS_URL . 'lib/fontawesome/js/fontawesome.min.js',
                    ['jquery'],
                    PP_AUTHORS_VERSION
                );
            }
        }
    
        wp_enqueue_script(
            'multiple-authors-widget',
            PP_AUTHORS_ASSETS_URL . 'js/multiple-authors-widget.js',
            ['jquery'],
            PP_AUTHORS_VERSION
        );

        if (!function_exists('multiple_authors')) {
            require_once PP_AUTHORS_BASE_PATH . 'functions/template-tags.php';
        }

        $css_class = '';
        if (!empty($target)) {
            $css_class = 'multiple-authors-target-' . str_replace('_', '-', $target);
        }

        if (isset($instance['authors_recent_col']) && (int)$instance['authors_recent_col'] > 0) {
            $css_class .= ' multiple-authors-col-' . (int)$instance['authors_recent_col'] . '';
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

        if (isset($instance['limit_per_page']) && (int)$instance['limit_per_page'] > 0 && !isset($instance['page'])) {
            $instance['page'] = (get_query_var('paged')) ? get_query_var('paged') : 1;
        }

        $author_results   = multiple_authors_get_all_authors(array('hide_empty' => !$showEmpty), $instance);
        if (isset($author_results['page'])) {
            $authors     = $author_results['authors'];
            $total_terms = $author_results['total'];
            $per_page    = $author_results['per_page'];
            $page        = $author_results['page'];
            $pages       = ceil($total_terms/$per_page);
            $pagination  = paginate_links(
                [
                    'current' => $page,
                    'total' => ceil($total_terms / $per_page)
                ]
            );

        } else {
            $authors    = $author_results;
            $pagination = false;
        }

        $filter_fields = false;
        if (isset($instance['search_field']) && !empty($instance['search_field'])) {
            $valid_fields         = Author_Editor::get_fields(false);
            $search_field_options = explode(',', $instance['search_field']);
            $search_field_options = array_map('trim', $search_field_options);
            $filter_fields = [];
            $filter_fields[''] = esc_html__('Default Search', 'publishpress-authors');
            foreach ($search_field_options as $search_field_option) {
                if (isset($valid_fields[$search_field_option])) {
                    $filter_fields[$search_field_option] = $valid_fields[$search_field_option]['label'];
                }
            }

        }

        $args = [
            'show_title'   => false,
            'css_class'    => esc_attr($css_class),
            'title'        => $title,
            'authors'      => $authors,
            'results'      => $authors,
            'pagination'   => $pagination,
            'all_text'     => esc_html__('All Authors', 'publishpress-authors'),
            'no_post_text' => esc_html__('No recent posts from this author', 'publishpress-authors'),
            'target'       => $target,
            'item_class'   => 'author url fn',
            'layout'       => $layout,
            'color_scheme' => $color_scheme,
            'show_email'   => $show_email,
            'show_site'    => $show_site,
            'shortcode'    => $instance,
            'template_options' => [
                'filter_fields'        => $filter_fields,
                'search_placeholder' => esc_html__('Search Box', 'publishpress-authors'),
                'search_query'       => isset($_GET['seach_query']) ? esc_attr($_GET['seach_query']) : '',
                'selected_option'    => isset($_GET['search_field']) ? esc_attr($_GET['search_field']) : '',
                'search_submit'      => esc_html__('Search', 'publishpress-authors')
            ]
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
