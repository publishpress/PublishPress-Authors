<?php
/**
 * @package     MultipleAuthors
 * @author      PublishPress <help@publishpress.com>
 * @copyright   Copyright (C) 2018 PublishPress. All rights reserved.
 * @license     GPLv2 or later
 * @since       1.0.0
 */

namespace MultipleAuthors;   

use MultipleAuthors\Traits\Author_box;
use WP_Widget;

class Widget extends WP_Widget {

	use Author_box;

	/**
	 * Widget Title
	 * 
	 * @var string
	 * 
	 * @since 3.4.0
	 */
	protected $title;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() 
	{
		$this->title = esc_html__( 'Post Author', 'publishpress-authors' );
		parent::__construct(
			'multiple_authors_widget',
			$this->title,
			array(
				'classname'   => 'multiple_authors_widget',
				'description' => esc_html__(
					'Display a list of authors for the current post.',
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
	public function widget( $args, $instance ) 
	{
		$legacyPlugin = Factory::getLegacyPlugin();

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title' => $this->title,
			)
		);

		/** This filter is documented in core/src/wp-includes/default-widgets.php */
		$title  = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
		$output = '';

		if ( $this->should_display_author_box() ) {
			$layout = isset( $instance['layout'] ) ? $instance['layout'] : $legacyPlugin->modules->multiple_authors->options->layout;

			$output .= $this->get_author_box_markup( 'widget', false, $layout );

			if ( ! empty( $output ) ) {
				echo $args['before_widget'];
				echo $args['before_title'] . apply_filters( 'widget_title', $title ) . $args['after_title'];
				echo $output;
				echo $args['after_widget'];
			}
		}
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) 
	{
		$legacyPlugin = Factory::getLegacyPlugin();

		$instance = wp_parse_args(
			(array) $instance,
			array(
				'title'  => $this->title,
				'layout' => $legacyPlugin->modules->multiple_authors->options->layout,
			)
		);

		$title  = strip_tags( $instance['title'] );
		$layout = strip_tags( $instance['layout'] );

		$context = array(
			'labels'  => array(
				'title'  => esc_html__( 'Title', 'publishpress-authors' ),
				'layout' => esc_html__( 'Layout', 'publishpress-authors' ),
			),
			'ids'     => array(
				'title'  => $this->get_field_id( 'title' ),
				'layout' => $this->get_field_id( 'layout' ),
			),
			'names'   => array(
				'title'  => $this->get_field_name( 'title' ),
				'layout' => $this->get_field_name( 'layout' ),
			),
			'values'  => array(
				'title'  => $title,
				'layout' => $layout,
			),
			'layouts' => apply_filters( 'pp_multiple_authors_author_layouts', array() ),
		);

		$container = Factory::get_container();

		echo $container['twig']->render( 'widget-form.twig', $context );
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance )
	{
		$legacyPlugin = Factory::getLegacyPlugin();

		$instance = array();

		$instance['title']  = sanitize_text_field( $new_instance['title'] );
		$instance['layout'] = sanitize_text_field( $new_instance['layout'] );

		$layouts = apply_filters( 'pp_multiple_authors_author_layouts', array() );

		if ( ! array_key_exists( $instance['layout'], $layouts ) ) {
			$instance['layout'] = $legacyPlugin->modules->multiple_authors->options->layout;
		}

		return $instance;
	}
}
