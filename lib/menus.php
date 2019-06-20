<?php

/**
 * MGPressMenus class
 *
 * Add menus to Timber context.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MGPressMenus')) {
    class MGPressMenus
    {

        /**
         * Menus to add to Timber context
         * @var array
         */
        protected static $menus;

        /**
         * Initialize MGPressMenus class.
         *
         * @since MGPress 1.0
         *
         * @param array $menus
         * @return null
         */
        public static function init(array $menus = null)
        {
            if (is_array($menus) && count($menus)) {

                // store menus locally
                self::$menus = $menus;

                // add Twig filter for context
                add_filter('timber_context', array('MGPressMenus', 'addToContext'));
            }
        }

        /**
         * Add menus to Timber context.
         *
         * @since MGPress 1.0
         *
         * @param array $context
         * @return array
         */
        public static function addToContext(array $context)
        {

            // add menus to Timber context
            if (count(self::$menus)) {
                $context['menus'] = array();
                foreach (self::$menus as $menu) {
                    $context['menus'][$menu] = new TimberMenu($menu);
                }
            }

            return $context;
        }
    }

    MGPressMenus::init(isset($MgPressSettings['menus']) ? $MgPressSettings['menus'] : null);
}
