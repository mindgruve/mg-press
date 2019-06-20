<?php

/**
 * MGPressErrors class
 *
 * PHP error handling for the development environment.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MGPressErrors')) {
    class MGPressErrors
    {

        /**
         * Initialize MGPressErrors class.
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        public static function init()
        {
            if(defined('ENVIRONMENT')
                && ENVIRONMENT == 'dev'
                && class_exists('\Whoops\Run')
                && !is_admin()
            ) {
                $whoops = new \Whoops\Run;
                $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
                $whoops->register();
            }
        }
    }

    MGPressErrors::init();
}
