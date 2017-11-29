<?php

/**
 * MGPressWidgets class
 *
 * Initialize and register Widget Areas (sidebars) with Wordpress and Timber.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MGPressWidgets')) {
    class MGPressWidgets
    {

        /**
         * Widgets to register and add to Timber context
         * @var array
         */
        protected static $widgets;

        /**
         * Initialize MGPressWidgets class.
         *
         * @since MGPress 1.0
         *
         * @param array $widgets
         * @return null
         */
        public static function init(array $widgets = null)
        {
            if (is_array($widgets) && count($widgets)) {

                // store widgets locally
                self::$widgets = $widgets;

                // add widget initialize action
                add_action('widgets_init', array('MGPressWidgets', 'themeWidgetsInit'));

                // add widget areas to context
                add_filter('timber_context', array('MGPressWidgets', 'addToContext'));
            }
        }

        /**
         * Initialize widget areas with WordPress.
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        public static function themeWidgetsInit()
        {

            // loop over widgets and register
            if (is_array(self::$widgets) && count(self::$widgets)) {
                foreach (self::$widgets as $widget) {
                    if (is_array($widget)) {
                        register_sidebar($widget);
                    }
                }
            }
        }

        /**
         * Add widget areas to Twig context.
         *
         * @since MGPress 1.0
         *
         * @param array $context
         * @return array
         */
        public static function addToContext(array $context)
        {

            // loop over widgets and add to Timber context
            if (is_array(self::$widgets) && count(self::$widgets)) {
                foreach (self::$widgets as $contextId => $widget) {
                    if (is_array($widget) && isset($widget['id'])) {
                        $context[$contextId] = Timber::get_widgets($widget['id']);
                    }
                }
            }
            return $context;
        }
    }

    MGPressWidgets::init(isset($MgPressSettings['widgets']) ? $MgPressSettings['widgets'] : null);
}
