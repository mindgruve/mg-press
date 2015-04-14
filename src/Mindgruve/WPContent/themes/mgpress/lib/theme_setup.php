<?php

/**
 * Theme Setup
 */

function theme_setup() {

  /**
   * Menus
   */

  // Register wp_nav_menu() menus (http://codex.wordpress.org/Function_Reference/register_nav_menus)
  register_nav_menus(array(
    'primary_navigation' => __('Primary Navigation', 'theme'),
    'footer_navigation'  => __('Footer Navigation', 'theme'),

    // add more menus here...

  ));


  /**
   * Thumbnails
   */

  // Add post thumbnails (http://codex.wordpress.org/Post_Thumbnails)
  add_theme_support('post-thumbnails');

  // Add Post Thumb image size
  add_image_size('post-thumb', 200, 200, true);

  // add more image sizes here...



  /**
   * Translations
   */

  // Make theme available for translation
  load_theme_textdomain('theme', get_template_directory() . '/lang');

}

// add After Setup Theme action
add_action('after_setup_theme', 'theme_setup');
