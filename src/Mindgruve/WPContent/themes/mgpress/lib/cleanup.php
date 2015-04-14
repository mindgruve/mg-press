<?php

/**
 * Clean up wp_head()
 */

// Cleanup wp_head()
function theme_head_cleanup() {

  // Originally from http://wpengineer.com/1438/wordpress-header/
  remove_action('wp_head', 'feed_links', 2);
  remove_action('wp_head', 'feed_links_extra', 3);
  remove_action('wp_head', 'rsd_link');
  remove_action('wp_head', 'wlwmanifest_link');
  remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
  remove_action('wp_head', 'wp_generator');
  remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
}

// Add cleanup action
add_action('init', 'theme_head_cleanup');

// Remove the WordPress version from RSS feeds
add_filter('the_generator', '__return_false');
