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

use Symfony\Component\Yaml\Yaml;

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

            add_filter( 'timber/twig', function( \Twig_Environment $twig ) {
                $twig->addFunction( new Timber\Twig_Function( 'twigTemplateExists', array('MGPressStyleGuide', 'twigTemplateExists') ) );
                return $twig;
            } );
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

                // add section to context (ie: `typography`)
                $context['section'] = $section;

                // parse yaml to build the menu system... this drives the whole framework
                $menu = file_get_contents(__DIR__ . '/../views/style-guide/style-guide.yml');
                $menu = Yaml::parse($menu);

                // add the menu to the context
                $context['menu'] = [];

                // loop through menu array and build it from simple Yaml to a sweet array
                foreach ($menu as $value) {
                    $title = key($value[0]);
                    $id = sanitize_title($title);
                    $children = [];

                    foreach ($value[0][$title] as $item) {
                        $children[] = ['id' => sanitize_title($item), 'name' => $item];
                    }

                    $context['menu'][$id] = ['id' => sanitize_title($id), 'name' => $title, 'children' => $children];
                }

                // add the section title to the context
                $context['section_title'] = key($menu[$section][0]);

                // render views
                Timber::render(array('style-guide/' . $section . '.twig', 'exception/404.twig'), $context, false, \Timber\Loader::CACHE_NONE);

                exit;
            }
        }

        public static function twigTemplateExists($filename)
        {
            return file_exists(get_template_directory(). '/views/' . $filename);
        }

    }

    MGPressStyleGuide::init();
}
