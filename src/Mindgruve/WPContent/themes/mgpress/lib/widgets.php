<?php

/**
 * Register widget areas
 */

// Initialize widget areas
function theme_widgets_init() {
  // Sidebars
  register_sidebar(array(
    'name'          => __('Sidebar', 'theme'),
    'id'            => 'sidebar-primary',
    'before_widget' => '<section class="widget %1$s %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3>',
    'after_title'   => '</h3>',
  ));

  // add more widget areas here...


}

// Add widget initialize action
add_action('widgets_init', 'theme_widgets_init');

// Create a timber function wrapper for dynamic sidebars
TimberHelper::function_wrapper('dynamic_sidebar', array('sidebar'), true);
