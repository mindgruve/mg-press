<?php

/**
 * MGPressTemplates class
 *
 * Add support for custom page/post templates using Timber.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MGPressTemplates')) {
    class MGPressTemplates
    {

        /**
         * Initialize MGPressTemplates class.
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        public static function init()
        {

            // Add a filter to the Page Attributes metabox
            add_filter('theme_page_templates', array('MGPressTemplates', 'addNewTemplate'));
        }

        /**
         * Add templates to Page Attributes metabox in admin UI.
         *
         * @since MGPress 1.0
         *
         * @param array $postsTemplates
         * @return array
         */
        public static function addNewTemplate(array $postsTemplates)
        {

            $templates = array();

            // init var with template directory location
            $templatesDir = get_template_directory() . '/' .
                (is_array(MGPressSite::$dirname) ? MGPressSite::$dirname[0] : MGPressSite::$dirname)
                . '/template';

            // add custom twig templates to array
            if (is_dir($templatesDir)) {
                if ($handle = opendir($templatesDir)) {
                    while (false !== ($entry = readdir($handle))) {
                        if (preg_match('/(.*)\.twig$/', $entry, $matches)) {
                            $templateName = $matches[1];
                            $templateContents = file_get_contents($templatesDir . '/' . $entry);
                            if (preg_match('/#\s+' . 'T' . 'emplate Name:\s+(.*)/', $templateContents, $matches2)) {
                                $templateName = $matches2[1];
                            }
                            $templates[$matches[1]] = $templateName;
                        }
                    }
                    closedir($handle);
                }
            }

            // add custom templates to admin UI array
            return array_merge($postsTemplates, $templates);
        }
    }

    MGPressTemplates::init();
}
