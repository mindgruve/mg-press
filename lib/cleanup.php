<?php

/**
 * MGPressCleanup class
 *
 * Removes unnecessary includes/data in the header.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MGPressCleanup')) {
    class MGPressCleanup
    {

        /**
         * Initialize MGPressCleanup class.
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        public static function init()
        {

            // Cleanup wp_head()
            add_action('init', function() {

                // Originally from http://wpengineer.com/1438/wordpress-header/
                remove_action('wp_head', 'feed_links', 2);
                remove_action('wp_head', 'feed_links_extra', 3);
                remove_action('wp_head', 'rsd_link');
                remove_action('wp_head', 'wlwmanifest_link');
                remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
                remove_action('wp_head', 'wp_generator');
                remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

                // Disable WP Emoji
                remove_action('wp_head', 'print_emoji_detection_script', 7);
                remove_action('wp_print_styles', 'print_emoji_styles');
            });

            // Remove the WordPress version from RSS feeds
            add_filter('the_generator', '__return_false');

            // add rewrite rule for search page URL
            add_action('template_redirect', array('MGPressCleanup', 'searchUrlRewrite'));
        }

        /**
         * Search URL rewrite: '?s=TERM' => '/search/TERM'
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        public static function searchUrlRewrite()
        {
            if (is_search() && isset($_GET['s'])) {
                wp_redirect(home_url("/search/").urlencode(get_query_var('s')));
                exit();
            }
        }
    }

    MGPressCleanup::init();
}
