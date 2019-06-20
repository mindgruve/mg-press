<?php

/**
 * MGPressSite class
 *
 * Sets up the theme: add theme support modules, add theme filters, add other values to the Timber context,
 * add any custom Twig extensions.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MGPressSite')) {
    class MGPressSite extends TimberSite
    {

        /**
         * Directory location(s) for Twig template files
         * @var array
         */
        public static $dirname = array('views');

        /**
         * The “WordPress address (URL)” set in Settings > General
         * @var string
         */
        public $wpurl = null;

        /**
         * Comment settings
         * @var array
         */
        public $comments = array();

        /**
         * MGPressSite constructor.
         *
         * @since MGPress 1.0
         *
         * @param array $themeSupport
         */
        function __construct(array $themeSupport = null)
        {

            // tell Timber where Twig templates are
            Timber::$dirname = self::$dirname;

            // add theme support features
            if (is_array($themeSupport) && count($themeSupport)) {
                foreach ($themeSupport as $feature) {
                    if (isset($feature[1])) {
                        add_theme_support($feature[0], $feature[1]);
                    } else {
                        add_theme_support($feature[0]);
                    }
                }
            }

            // add Twig filters
            add_filter('timber_context', array($this, 'addToContext'));
            add_filter('get_twig', array($this, 'addToTwig'));

            // make theme available for translation
            load_theme_textdomain('theme', get_template_directory() . '/lang');

            // add WordPress address to site
            $this->wpurl = site_url();

            parent::__construct();
        }

        /**
         * Add values to Timber context.
         *
         * @since MGPress 1.0
         *
         * @param array $context
         * @return array
         */
        function addToContext(array $context)
        {

            /* this is where you can add your own values to the context */

            // add this site object to Timber context
            $context['site'] = $this;

            return $context;
        }

        /**
         * Add extensions to Twig.
         *
         * @since MGPress 1.0
         *
         * @param Twig_Environment $twig
         * @return Twig_Environment
         */
        function addToTwig(\Twig_Environment $twig)
        {

            /* this is where you can add your own functions to Twig */

            /** EXAMPLES:
            $twig->addExtension( new Twig_Extension_StringLoader() );
            $twig->addFilter('myfoo', new Twig_SimpleFilter('myfoo', array($this, 'myfoo')));
            */

            return $twig;
        }
    }

    new MGPressSite(isset($MgPressSettings['theme_support']) ? $MgPressSettings['theme_support'] : null);
}
