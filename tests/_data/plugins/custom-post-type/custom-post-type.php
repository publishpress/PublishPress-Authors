<?php
/**
 * Plugin Name: PublishPress Custom Post Type
 * Plugin URI:  https://wordpress.org/plugins/publishpress-authors/
 * Description: PublishPress Test Plugin for creating a custom post type
 * Author:      PublishPress
 * Author URI:  https://publishpress.com
 * Version: 0.1.0
 * Text Domain: custom-post-type
 */


add_action(
    'init',
    function () {
        register_post_type(
            'book',
            [
                'label'    => 'Books',
                'supports' => ['title', 'author'],
                'public'   => true,
                'show_ui'  => true,
            ]
        );

        register_post_type(
            'motor',
            [
                'label'    => 'Motors',
                'supports' => ['title'],
                'public'   => true,
                'show_ui'  => true,
            ]
        );
    },
    1
);
