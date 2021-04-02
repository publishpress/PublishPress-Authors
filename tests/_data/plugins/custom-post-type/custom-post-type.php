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
            'books',
            [
                'label'    => 'Books',
                'supports' => ['author'],
                'public'   => true,
                'show_ui'  => true,
            ]
        );
    },
    1
);
