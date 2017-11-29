<?php

/**
 * MGPressThemeActivation class
 *
 * Actions to take when the theme is activated: flush the URL rewrite rules cache.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MGPressThemeActivation')) {
    class MGPressThemeActivation
    {

        /**
         * Initialize MGPressThemeActivation class.
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        public static function init()
        {

            // Apply theme activation action
            add_action('after_switch_theme', function() {

                // flush URL rewrite rules cache
                flush_rewrite_rules();
            });
        }
    }

    MGPressThemeActivation::init();
}
