<?php
/*
Plugin Name: Testimonial Post Type
Plugin URI: http://www.orangeroomsoftware.com/website-plugin/
Version: 2.0
Author: <a href="http://www.orangeroomsoftware.com/">Orange Room Software</a>
Description: A post type for Templates
*/

define('TESTIMONIAL_PLUGIN_URL', '/wp-content/plugins/' . basename(dirname(__FILE__)) );
define('TESTIMONIAL_PLUGIN_DIR', dirname(__FILE__));

#
# Theme supporting filters
#
add_theme_support( 'post-thumbnails' );
add_filter( 'get_the_excerpt', 'do_shortcode' );
add_filter( 'the_excerpt', 'do_shortcode' );
add_filter( 'widget_text', 'do_shortcode' );

# Testimonial Stylesheet
function ors_testimonial_template_stylesheets() {
  wp_enqueue_style('testimonial-template-style', '/wp-content/plugins/'.basename(dirname(__FILE__)).'/style.css', 'ors-testimonial', null, 'all');
}
add_action('wp_print_styles', 'ors_testimonial_template_stylesheets', 6);

# Custom post type
add_action( 'init', 'create_testimonial_post_type' );
function create_testimonial_post_type() {
  $labels = array(
    'name' => _x('Testimonials', 'post type general name'),
    'singular_name' => _x('Testimonial', 'post type singular name'),
    'add_new' => _x('Add New', 'testimonial'),
    'add_new_item' => __('Add New Testimonial'),
    'edit_item' => __('Edit Testimonial'),
    'new_item' => __('New Testimonial'),
    'view_item' => __('View Testimonial'),
    'search_items' => __('Search Testimonials'),
    'not_found' =>  __('No testimonials found'),
    'not_found_in_trash' => __('No testimonials found in Trash'),
    'parent_item_colon' => '',
    'menu_name' => 'Testimonials'

  );
  $args = array(
    'labels' => $labels,
    'public' => true,
    'publicly_queryable' => true,
    'show_ui' => true,
    'show_in_menu' => true,
    'query_var' => true,
    'capability_type' => 'post',
    'has_archive' => true,
    'hierarchical' => false,
    'menu_position' => 6,
    'supports' => array('title','editor','thumbnail','excerpt'),
    'menu_icon' => '/wp-content/plugins/'.basename(dirname(__FILE__)).'/icon.png',
    'rewrite' => array(
      'slug' => 'testimonials',
      'with_front' => false
    )
  );

  register_post_type( 'testimonial', $args );
}

add_filter( 'excerpt_length', 'ors_testimonial_excerpt_length' );
function ors_testimonial_excerpt_length( $length ) {
  if ( get_post_type() != 'testimonial' ) return $length;
  return 10;
}

/* short code */
add_shortcode( 'testimonials', 'testimonials_func' );
function testimonials_func( $atts ) {
  global $wpdb, $current_testimonial_type;

  $current_testimonial_type  = $atts['type'];
  $args  = array(
    'posts_per_page'  => (int) $atts['limit'],
    'orderby'         => 'rand',
    'post_type'       => 'testimonial',
    'post_status'     => 'publish'
  );

  $posts = get_posts($args);
  $output = '<div id="ors-testimonials" class="shortcode">';

  foreach ( $posts as $post ) {
    setup_postdata( $post );

    foreach ( get_post_custom($post->ID) as $key => $value ) {
      $custom[$key] = $value[0];
    }

    $output .= '<a href="' . get_permalink($post->ID) . '" rel="bookmark" title="' . $post->post_title . '" class="ors-testimonial">';
    $output .= "<section>&ldquo;" . $post->post_excerpt . "&rdquo;</section>";
    $output .= "<footer>&mdash;" . $post->post_title . "</footer>";
    $output .= "</a>";
  }

  $output .= "</div>";

  wp_reset_postdata();

  return $output;
}
