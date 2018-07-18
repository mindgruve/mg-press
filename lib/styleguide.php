<?php

/**
 * MGPressStyleGuide class
 *
 * Create and manage a web style guide using real twig templates.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.1
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MGPressStyleGuide')) {
    class MGPressStyleGuide
    {

        /**
         * Initialize MGPressStyleGuide class.
         *
         * @since MGPress 1.1
         *
         * @return null
         */
        public static function init()
        {
            self::registerFilters();
        }

        /**
         * Register Filters
         *
         * @since MgPress 1.1
         *
         * @return null
         */
        public static function registerFilters()
        {

            // register URL params
            add_filter('query_vars', array('MGPressStyleGuide', 'registerQueryVars'));

            // URL rewrite rules
            add_filter('category_rewrite_rules', array('MGPressStyleGuide', 'rewriteRulesFilter'));

            // add controllers
            add_filter('template_redirect', array('MGPressStyleGuide', 'styleGuideAction'));
        }

        /**
         * Register Query Vars
         *
         * @since MgPress 1.1
         *
         * @param array $vars
         * @return array
         */
        public static function registerQueryVars($vars) {
            $vars[] = 'section';
            return $vars;
        }

        /**
         * Rewrite Rules Filter
         *
         * @since MgPress 1.1
         *
         * @param array $rules
         * @return array
         */
        public static function rewriteRulesFilter($rules)
        {
            $rules['^style-guide/([^/]*)/?'] = 'index.php?pagename=mg_style_guide&section=$matches[1]';
            $rules['^style-guide$'] = 'index.php?pagename=mg_style_guide';
            return $rules;
        }

        /**
         * Style guide controller.
         *
         * @since MgPress 1.1
         *
         * @return null
         */
        public static function styleGuideAction()
        {

            // check if on mg_style_guide page
            if (get_query_var('pagename') == 'mg_style_guide') {

                // get section
                $section = get_query_var('section', null) ? get_query_var('section') : 'index';

                // set context
                $context  = Timber::get_context();

                // render views
                Timber::render(array('style-guide/' . $section . '.twig', 'exception/404.twig'), $context, false, \Timber\Loader::CACHE_NONE);

                exit;
            }
        }

    }

    MGPressStyleGuide::init();
}
