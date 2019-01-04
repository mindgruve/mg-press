<?php

/**
 * Theme Settings
 *
 * This is where you can add your own custom settings.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @since       MGPress 1.0
 * @author
 */

defined('ABSPATH') or die();

// MG Press settings
$MgPressSettings = [

    // add theme support features here (https://developer.wordpress.org/reference/functions/add_theme_support/)
    'theme_support' => [
        ['post-formats'],
        ['post-thumbnails'],
        ['menus'],
        ['html5', ['comment-list', 'comment-form', 'search-form', 'gallery', 'caption']],
    ],

    // add available menus here
    'menus' => [
        'primary_navigation',
        'secondary_navigation',
        'social',
    ],

    // add widgets (sidebars) to register here
    'widgets' => [

        /**
         * key: timber context id
         * value: array of register_sidebar() args (https://codex.wordpress.org/Function_Reference/register_sidebar)
         */

        'sidebar_primary' => [
            'name'          => __('Sidebar', 'theme'),
            'id'            => 'sidebar-primary',
            'before_widget' => '<section class="widget %1$s %2$s">',
            'after_widget'  => '</section>',
            'before_title'  => '<h3>',
            'after_title'   => '</h3>',
        ],
    ],

    // comment settings
    'comments' => [
        'comments_on' => true, // global switch
        'flash_messages' => [
            'success' => "Thank you for your comment.",          // set to null to disable message
            'pending' => "Your comment is pending moderation.",  // set to null to disable message
        ],
    ],

    // block settings
    'blocks' => [
        'acf_pro'                    => true, // flexible content block system requires ACF Pro
        'flexible_layout_post_types' => [],   // array of (custom) post types to use flexible content blocks on
    ],

    // WP admin settings
    'admin' => [

        // Argument is an array of post types for which to enable the admin order column
        'order_column' => [],

        // Argument is an array of post types for which to enable the taxonomies columns
        'taxonomy_column' => [],
    ],
];
