<?php

/**
 * MgPressRequirements
 *
 * Technical requirements for the MgPress theme
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

class MgPressRequirements
{

    /**
     * Minimum version of PHP required
     * @var string
     */
    private static $minimumPhpVersion = '5.3.3';

    /**
     * Class names used in theme, cannot already exist or will conflict
     * @var array
     */
    private static $classes = array(
        'MGPressThemeActivation',
        'MgPressAssets',
        'MGPressCleanup',
        'MGPressComments',
        'MGPressMenus',
        'MGPressSite',
        'MGPressTemplates',
        'MGPressWhoops',
        'MGPressWidgets',
    );

    /**
     * Class names required to already exist from WordPress core or plugins
     * @var array
     */
    private static $classDepenencies = array(
        'WP_Date_Query'           => 3.7, // don't really use this class, but Timber needs at least WP 3.7
    );

    /**
     * Functions required to already exist from WordPress core or plugins
     * @var array
     */
    private static $functionDepenencies = array(
        'add_filter'              => 0.7,
        'add_action'              => 1.2,
        'remove_action'           => 1.2,
        'is_search'               => 1.5,
        'get_query_var'           => 1.5,
        'load_theme_textdomain'   => 1.5,
        'get_template_directory'  => 1.5,
        'wp_redirect'             => 1.5,
        'wp_register_script'      => 2.1,
        'wp_enqueue_script'       => 2.1,
        'register_sidebar'        => 2.2,
        'wp_register_style'       => 2.6,
        'wp_enqueue_style'        => 2.6,
        'get_comments'            => 2.7,
        'add_theme_support'       => 2.9,
        'flush_rewrite_rules'     => 3.0,
        'home_url'                => 3.0,
        'site_url'                => 3.0,

    );

    /**
     * Actions required to already exist from WordPress core
     * @var array
     */
    private static $actionDependencies = array(
        'wp_footer'               => 1.5,
        'wp_head'                 => 1.5,
        'template_redirect'       => 1.5,
        'widgets_init'            => 2.2,
        'wp_enqueue_scripts'      => 2.8,
        'wp_print_footer_scripts' => 2.8,
        'after_switch_theme'      => 3.3,

    );

    /**
     * Plugins that need to be installed and activated for theme to function
     * @var array
     */
    private static $pluginDepenencies = array(
        'Timber' => array('Timber', 'https://wordpress.org/plugins/timber-library/'),
    );

    /**
     * Check Requirements.
     *
     * @since MGPress 1.0
     *
     * @return boolean
     */
    public static function checkRequirements()
    {
        if (function_exists('add_action')) {

            // check minimum PHP requirements
            if (version_compare(phpversion(), self::$minimumPhpVersion) < 0) {
                add_action('admin_notices', array(MgPressRequirements, 'adminErrorNoticePhp'));
                return false;
            }

            // check class name conflicts
            if (count(self::$classes)) {
                foreach (self::$classes as $class) {
                    if (class_exists($class)) {
                        add_action('admin_notices', array(MgPressRequirements, 'adminErrorNoticeClassConflict'));
                        return false;
                    }
                }
            }

            // check class dependencies
            if (count(self::$classDepenencies)) {
                foreach (self::$classDepenencies as $class => $version) {
                    if (!class_exists($class)) {
                        add_action('admin_notices', array('MgPressRequirements', 'adminErrorNoticeUpgradeWordpress'));
                        return false;
                    }
                }
            }

            // check function dependencies
            if (count(self::$functionDepenencies)) {
                foreach (self::$functionDepenencies as $function => $version) {
                    if (!function_exists($function)) {
                        add_action('admin_notices', array('MgPressRequirements', 'adminErrorNoticeUpgradeWordpress'));
                        return false;
                    }
                }
            }

            // check action dependencies
            if (function_exists('has_action') && count(self::$actionDependencies)) {
                foreach (self::$actionDependencies as $action => $version) {
                    if (!has_action($action)) {
                        add_action('admin_notices', array('MgPressRequirements', 'adminErrorNoticeUpgradeWordpress'));
                        return false;
                    }
                }
            }

            // check plugin dependencies
            if (count(self::$pluginDepenencies)) {
                foreach (self::$pluginDepenencies as $class => $data ) {
                    if (!class_exists($class)) {
                        add_action('admin_notices', array('MgPressRequirements', 'adminErrorNoticeInstallPlugins'));
                        add_filter('template_include', array('MgPressRequirements', 'publicErrorMissingPlugins'));
                        return false;
                    }
                }
            }
        } else {
            return false;
        }

        return true;
    }

    /* ERROR MESSAGES */

    /**
     * Admin Error Notice PHP.
     *
     * @since MGPress 1.0
     *
     * @return null
     */
    public static function adminErrorNoticePhp()
    {
        echo "<div class='error'><p>" . __("The 'MG Press' theme requires at least version "
                . self::$minimumPhpVersion . " of PHP. Your version is " . phpversion() . ". Please update PHP and try again.")
            . "</p></div>\n";
    }

    /**
     * Admin Error Notice Class Conflict.
     *
     * @since MGPress 1.0
     *
     * @return null
     */
    public static function adminErrorNoticeClassConflict()
    {
        echo "<div class='error'><p>" . __("The 'MG Press' theme has found a naming conflict. "
                . "Try disabling other plugins to see if the conflict resolves.")
            . "</p></div>\n";
    }

    /**
     * Admin Error Notice Upgrade Wordpress.
     *
     * @since MGPress 1.0
     *
     * @return null
     */
    public static function adminErrorNoticeUpgradeWordpress()
    {
        $versions = array_merge(
            self::$classes,
            self::$classDepenencies,
            self::$functionDepenencies,
            self::$actionDependencies
        );
        arsort($versions);
        echo "<div class='error'><p>" . __("The 'MG Press' theme requires at least version "
                . sprintf("%01.1f", reset($versions)) . " of Wordpress. Your version is " . get_bloginfo('version') . ". "
                . "Please update WordPress and try again")
            . "</p></div>\n";
    }

    /**
     * Admin Error Notice Install Plugins.
     *
     * @since MGPress 1.0
     *
     * @return null
     */
    public static function adminErrorNoticeInstallPlugins()
    {
        echo "<div class='error'><p>" . __("The 'MG Press' theme requires the following plugins to be installed and activated:") . "</p><ul>";
        foreach (self::$pluginDepenencies as $class => $data) {
            echo "<li><a href='" . $data[1] . "' target='_blank'>" . $data[0] . "</a></li>";
        }
        echo "</ul></div>\n";
    }

    /**
     * Public Error Missing Plugins.
     *
     * @since MGPress 1.0
     *
     * @return string
     */
    public static function publicErrorMissingPlugins()
    {
        return get_stylesheet_directory() . '/assets/html/no-timber.html';
    }
}
