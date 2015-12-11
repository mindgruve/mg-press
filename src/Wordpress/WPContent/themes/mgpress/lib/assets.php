<?php

/**
 * Assets
 */

$timberStylesheets = array();
$timberStylesheetsPriorityRegister = 0;

/**
 * Add Stylesheet
 *
 * @param string $handle
 * @param string $src
 * @param array $deps
 * @param string $ver
 * @param string $media
 * @param string $group
 */
function add_stylesheet($handle, $src, $deps = array(), $ver = null, $media = null, $priority = 0, $group = null) {

//    wp_register_style($handle, $src, $deps, $ver, $media);
//    wp_enqueue_style($handle);

    global $timberStylesheets;
    global $timberStylesheetsPriorityRegister;

    // add files to registry for later processing
    if (!isset($timberStylesheets[$handle]) || $timberStylesheets[$handle][1] <= $priority) {
        $timberStylesheets[$handle] = array($handle, $src, $priority, $timberStylesheetsPriorityRegister++, $deps,
            $ver, $media, $group);
    }

    // support grouping
    if (class_exists('MgAssetHelper')) {
        if (!isset(MgAssetHelper::$stylesheetGroups[$group])) {
            MgAssetHelper::$stylesheetGroups[$group] = array();
        }
        MgAssetHelper::$stylesheetGroups[$group][] = $handle;
    }
}

// Create a timber function wrapper for add stylesheet
TimberHelper::function_wrapper('add_stylesheet', null, false);

/**
 * Add Script File
 *
 * @param string $handle
 * @param string $src
 * @param array $deps
 * @param string $ver
 * @param boolean $in_footer
 * @param string $group
 */
function add_script_file($handle, $src, $deps = array(), $ver = null, $in_footer = true, $group = null) {
    wp_register_script($handle, $src, $deps, $ver, $in_footer);
    wp_enqueue_script($handle);

    // support grouping
    if (class_exists('MgAssetHelper')) {
        if (!isset(MgAssetHelper::$scriptGroups[$group])) {
            MgAssetHelper::$scriptGroups[$group] = array();
        }
        MgAssetHelper::$scriptGroups[$group][] = $handle;
    }
}

// Create a timber function wrapper for add script file
TimberHelper::function_wrapper('add_script_file', null, false);

/**
 * Localize Script
 * FYI: this function is used by MG to configure require.js
 *
 * @param string $handle
 * @param string $name
 * @param array $data
 */
function localize_script($handle, $name, $data) {
    wp_localize_script($handle, $name, $data);
}

// Create a timber function wrapper for add script file
TimberHelper::function_wrapper('localize_script', null, false);


// Theme scripts
function theme_scripts()
{

    // jQuery is loaded using the same method from HTML5 Boilerplate:
    // Grab Google CDN's latest jQuery with a protocol relative URL; fallback to local if offline
    // It's kept in the header instead of footer to avoid conflicts with plugins.
    if (!is_admin() && !in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) )) {
        wp_deregister_script('jquery');
        add_filter('script_loader_src', 'theme_jquery_local_fallback', 10, 2);
    }
}

// Add enqueue scripts action
//add_action('wp_enqueue_scripts', 'theme_scripts', 110);
add_action('wp_loaded', 'theme_scripts', 110);

// http://wordpress.stackexchange.com/a/12450
function theme_jquery_local_fallback($src, $handle = null)
{
    static $add_jquery_fallback = false;

    if ($add_jquery_fallback) {
        echo '<script>window.jQuery || document.write(\'<script src="' . get_template_directory_uri()
            . '/assets/javascript/vendor/jquery.js"><\/script>\')</script>' . "\n";
        $add_jquery_fallback = false;
    }

    if ($handle === 'jquery') {
        $add_jquery_fallback = true;
    }

    return $src;
}

add_action('wp_head', 'theme_jquery_local_fallback');

// load styles from registry in prioritized order
function timber_enqueue_styles() {

    global $timberStylesheets;

    usort($timberStylesheets, 'sortScriptsByPriority');

    if (count($timberStylesheets)) {
        foreach ($timberStylesheets as $key => $timberStylesheet) {
            wp_register_style($timberStylesheet[0], $timberStylesheet[1], $timberStylesheet[4], $timberStylesheet[5],
                $timberStylesheet[6]);
            wp_enqueue_style($timberStylesheet[0]);
            unset($timberStylesheets[$key]);
        }
    }
}
add_action('wp_enqueue_scripts', 'timber_enqueue_styles');
add_action('wp_footer', 'timber_enqueue_styles');


/**
 * Sort by Priority
 *
 * @param array $a
 * @param array $b
 * @return int
 */
function sortScriptsByPriority(array $a, array $b)
{
   if ($a[2] == $b[2]) {
       if ($a[3] == $b[3]) {
           return 0;
       }
       return ($a[3] < $b[3]) ? -1 : 1;
   }
   return ($a[2] > $b[2]) ? -1 : 1;
}
