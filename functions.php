<?php

/**
 * MGPressFunctions class
 *
 * Checks minimum requirements to run theme, loads and initializes theme functionality.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MGPressFunctions')) {
    class MGPressFunctions
    {

        /**
         * Location for MgPressRequirements file
         * @var string
         */
        public static $requirementsInclude = '/lib/requirements.php';

        /**
         * Locations for all other theme include files
         * @var array
         */
        public static $themeIncludes = array(
            '/lib/errors.php',           // Error handling
            '/custom/*.php',             // Custom scripts
            '/lib/site.php',             // Site
            '/lib/menus.php',            // Menus
            '/lib/widgets.php',          // Widget areas
            '/lib/cleanup.php',          // Cleanup
            '/lib/assets.php',           // Assets
            '/lib/comments.php',         // Comments
            '/lib/templates.php',        // Templates
            '/lib/styleguide.php',       // Style Guide
            '/lib/activation.php',       // Theme activation
        );

        /**
         * Initialize MGPressFunctions class
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        public static function init()
        {

            // check Requirements
            require_once locate_template(self::$requirementsInclude);
            if (!MgPressRequirements::checkRequirements()) {
                return false;
            }

            // load includes
            foreach(self::$themeIncludes as $filePattern) {
                foreach (glob(get_stylesheet_directory() . '/' . $filePattern) as $filepath) {
                    require_once $filepath;
                }
            }
            unset($filePattern, $filepath);
        }
    }

    MGPressFunctions::init();
}
