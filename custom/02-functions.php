<?php

/**
 * Theme Functions
 *
 * This is where you can add your own custom functions.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @since       MGPress 1.0
 * @author
 */

defined('ABSPATH') or die();

/** EXAMPLES */

//// Unify frontend jQuery
//add_action('wp_enqueue_scripts', function() {
//    if (!is_admin()) {
//        wp_deregister_script('jquery');
//        wp_register_script('jquery', get_stylesheet_directory_uri().'/assets/build/vendor.min.js', false);
//        wp_enqueue_script('jquery');
//    }
//});
//
//// add ACF options page to admin dashboard
//if( function_exists('acf_add_options_page') ) {
//    acf_add_options_page('Theme Settings');
//}
