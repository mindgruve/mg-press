<?php

/**
 * MgPressAssets class
 *
 * Asset helper for templates: add stylesheets and scripts to header.
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

defined('ABSPATH') or die();

if(!class_exists('MgPressAssets')) {
    class MgPressAssets
    {

        /**
         * Queue of stylesheets to register
         * @var array
         */
        private $stylesheets = array();

        /**
         * Queue of styles to register
         * @var array
         */
        private $styles = array();

        /**
         * Queue of script files to register
         * @var array
         */
        private $scriptFiles = array();

        /**
         * Queue of script to register
         * @var array
         */
        private $scripts = array();

        /**
         * List of used stylesheet handles
         * @var array
         */
        private $usedStylesheetHandles = array();

        /**
         * List of used style handles
         * @var array
         */
        private $usedStyleHandles = array();

        /**
         * List of used script file handles
         * @var array
         */
        private $usedScriptFileHandles = array();

        /**
         * List of used script handles
         * @var array
         */
        private $usedScriptHandles = array();

        /**
         * Stylesheet priority counter
         * @var int
         */
        private $stylesheetsDefaultPriority = 0;

        /**
         * Styles priority counter
         * @var int
         */
        private $stylesDefaultPriority = 0;

        /**
         * Script files priority counter
         * @var int
         */
        private $scriptFilesDefaultPriority = 0;

        /**
         * Scripts priority counter
         * @var int
         */
        private $scriptsDefaultPriority = 0;

        /**
         * Initialize MgPressAssets class.
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        public function init()
        {

            // Create timber functions to: add stylesheet, add style, add script file, localize script
            add_filter('timber/twig/functions', function(\Twig_Environment $twig) {
                $twig->addFunction(new Timber\Twig_Function('add_stylesheet', array($this, 'addStylesheet')));
                $twig->addFunction(new Timber\Twig_Function('add_style', array($this, 'addStyle')));
                $twig->addFunction(new Timber\Twig_Function('add_script_file', array($this, 'addScriptFile')));
                $twig->addFunction(new Timber\Twig_Function('add_script', array($this, 'addScript')));
                $twig->addFunction(new Timber\Twig_Function('localize_script', array($this, 'localizeScript')));
                return $twig;
            });

            // register stylesheets with WordPress
            add_action('wp_enqueue_scripts', array($this, 'registerStyles'));
            add_action('wp_footer', array($this, 'registerStyles'));

            // print styles in output
            add_action('wp_head', array($this, 'printStyles'));
            add_action('wp_print_footer_scripts', array($this, 'printStyles'));

            // register script files with WordPress
            add_action('wp_enqueue_scripts', array($this, 'registerScriptFiles'));
            add_action('wp_footer', array($this, 'registerScriptFiles'));

            // print scripts in output
            add_action('wp_head', array($this, 'printScripts'));
            add_action('wp_print_footer_scripts', array($this, 'printScripts'));
        }

        /**
         * Add Stylesheet to HTML Output.
         *
         * @since MGPress 1.0
         *
         * @param string  $handle        (required) A unique string ID to ensure the resource is loaded only once.
         * @param string  $src           (required) The public path to the resource.
         * @param array   $deps          Array of handles of any stylesheets that this stylesheet depends on.
         * @param string  $ver           String specifying the stylesheet version number, if it has one.
         * @param string  $media         String specifying the media for which this stylesheet has been defined. [Ex: 'all', 'screen', 'handheld', 'print']
         * @param bool    $http2         True: load resource immediately in HTML; False: register resource with WordPress to load in the header or footer. Default is False.
         * @param bool    $inline        True: print the contents of the file directly in HTML; False: load the file as an external resource. Default is False.
         * @param array   $environments  Array of environments to restrict resource loading to, default is to load in all environments. ['dev', 'test', 'prod']
         * @param string  $group         A group ID to aid resource grouping; assets can share groups.
         * @param int     $priority      A priority weight given to the resource used during sorting when registered with WordPress: higher numbers will load sooner. Default is 0, loading by FIFO.
         * @return null
         */
        function addStylesheet(
            $handle,
            $src,
            $deps = array(),
            $ver = null,
            $media = null,
            $http2 = false,
            $inline = false,
            $environments = null,
            $group = null,
            $priority = 0
        ) {

            // add stylesheet to environment flag
            $addToEnv = (!is_array($environments) || in_array(ENVIRONMENT, $environments)) ? true : false;

            // basic sanity checks
            if ($addToEnv && is_string($src) && !empty($src)) {

                // get production file
                $srcProd = $this->getProdFile($src, $ver);

                // support HTTP2
                if (($http2 && (defined('HTTP2') && HTTP2)) && !in_array($handle, $this->usedStylesheetHandles)) {
                    echo "<link rel='stylesheet' id='".$handle."-css'  href='".$srcProd['src'].'?ver='.$srcProd['ver']."' type='text/css' media='".$media."' />";
                    $this->usedStylesheetHandles[] = $handle;
                    return;

                // print file contents inline
                } elseif ($inline && !in_array($handle, $this->usedStylesheetHandles)) {
                    if ($parsedUrl = parse_url($src)) {

                        /* @todo: Using WP_CONTENT_DIR below may not work if directory structure is different... */

                        if ($inlinePath = realpath(WP_CONTENT_DIR . '/..' . $parsedUrl['path'])) {
                            $contents = file_get_contents($inlinePath);
                            echo "\n<style type='text/css' media='" . $media . "'>" . $contents . "</style>\n";
                            $this->usedStyleHandles[] = $handle;
                            return;
                        }
                    }

                // regular head or footer loading
                } elseif (is_int($priority)) {
                    if ($handle && isset($this->stylesheets[$handle])) {
                        if ($this->stylesheets[$handle][1] <= $priority) {
                            $this->stylesheets[$handle] = array($srcProd['src'], $priority, $this->stylesheetsDefaultPriority++, $deps, $srcProd['ver'], $media, $group);
                        }
                    } else {
                        $this->stylesheets[$handle ? $handle : uniqid()] = array($srcProd['src'], $priority, $this->stylesheetsDefaultPriority++, $deps, $srcProd['ver'], $media, $group);
                    }
                }
            }

            // support grouping
            if (class_exists('MgAssetHelper') && $group) {
                if (!isset(MgAssetHelper::$stylesheetGroups[$group])) {
                    MgAssetHelper::$stylesheetGroups[$group] = array();
                }
                MgAssetHelper::$stylesheetGroups[$group][] = $handle;
            }
        }

        /**
         * Add Style to HTML Output.
         *
         * @since MGPress 1.0
         *
         * @param string  $handle        (required) A unique string ID to ensure the style is output only once.
         * @param string  $src           (required) String of CSS styles to output. [Ex: "p { color: #ff0000; }"]
         * @param string  $media         String specifying the media for which this stylesheet has been defined. [Ex: 'all', 'screen', 'handheld', 'print']
         * @param bool    $http2         True: print styles immediately in HTML; False: register styles with WordPress to load in the header or footer. Default is False.
         * @param array   $environments  Array of environments to restrict style output to, default is to output in all environments. ['dev', 'test', 'prod']
         * @param int     $priority      A priority weight given to the resource used during sorting when registered with WordPress: higher numbers will load sooner. Default is 0, loading by FIFO.
         * @return null
         */
        function addStyle(
            $handle,
            $src,
            $media = null,
            $http2 = false,
            $environments = null,
            $priority = 0
        ) {

            // add style to environment flag
            $addToEnv = (!is_array($environments) || in_array(ENVIRONMENT, $environments)) ? true : false;

            // basic sanity checks
            if ($addToEnv && is_string($src) && !empty($src)) {

                // support HTTP2
                if (($http2 && (defined('HTTP2') && HTTP2)) && !in_array($handle, $this->usedStyleHandles)) {
                    echo "\n<style type='text/css' media='" . $media . "'>" . $src . "</style>\n";
                    $this->usedStyleHandles[] = $handle;
                    return;

                // regular head or footer loading
                } elseif (is_int($priority)) {
                    if ($handle && isset($this->styles[$handle])) {
                        if ($this->styles[$handle][1] <= $priority) {
                            $this->styles[$handle] = array($src, $priority, $this->stylesDefaultPriority++, $media);
                        }
                    } else {
                        $this->styles[$handle ? $handle : uniqid()] = array($src, $priority, $this->stylesDefaultPriority++, $media);
                    }
                }
            }
        }

        /**
         * Add Script File to HTML Output.
         *
         * @since MGPress 1.0
         *
         * @param string  $handle        (required) A unique string ID to ensure the resource is loaded only once.
         * @param string  $src           (required) The public path to the resource.
         * @param array   $deps          An array of registered script handles this script depends on.
         * @param string  $ver           String specifying script version number, if it has one, which is added to the URL as a query string for cache busting purposes.
         * @param bool    $http2         True: load resource immediately in HTML; False: register resource with WordPress to load in the header or footer. Default is False.
         * @param bool    $inline        True: print the contents of the file directly in HTML; False: load the file as an external resource. Default is False.
         * @param array   $environments  Array of environments to restrict resource loading to, default is to load in all environments. ['dev', 'test', 'prod']
         * @param string  $group         A group ID to aid resource grouping; assets can share groups.
         * @param int     $priority      A priority weight given to the resource used during sorting when registered with WordPress: higher numbers will load sooner. Default is 0, loading by FIFO.
         * @return null
         */
        function addScriptFile(
            $handle,
            $src,
            $deps = array(),
            $ver = null,
            $http2 = false,
            $inline = false,
            $environments = null,
            $group = null,
            $priority = 0
        ) {

            // add stylesheet to environment flag
            $addToEnv = (!is_array($environments) || in_array(ENVIRONMENT, $environments)) ? true : false;

            // basic sanity checks
            if ($addToEnv && is_string($src) && !empty($src)) {

                // get production file
                $srcProd = $this->getProdFile($src, $ver);

                // support HTTP2
                if (($http2 && (defined('HTTP2') && HTTP2)) && !in_array($handle, $this->usedScriptFileHandles)) {
                    echo "\n<script type='text/javascript' src='".$srcProd['src']."?ver=".$srcProd['ver']."'></script>\n";
                    $this->usedScriptFileHandles[] = $handle;
                    return;

                    // print file contents inline
                } elseif ($inline && !in_array($handle, $this->usedScriptFileHandles)) {
                    if ($parsedUrl = parse_url($src)) {

                        /* @todo: Using WP_CONTENT_DIR below may not work if directory structure is different... */

                        if ($inlinePath = realpath(WP_CONTENT_DIR . '/..' . $parsedUrl['path'])) {
                            $contents = file_get_contents($inlinePath);
                            echo "\n<script type='text/javascript'>\n" . $contents . "\n</script>\n";
                            $this->usedScriptFileHandles[] = $handle;
                            return;
                        }
                    }

                    // regular head or footer loading
                } elseif (is_int($priority)) {
                    if ($handle && isset($this->scriptFiles[$handle])) {
                        if ($this->scriptFiles[$handle][1] <= $priority) {
                            $this->scriptFiles[$handle] = array($srcProd['src'], $priority, $this->scriptFilesDefaultPriority++, $deps, $srcProd['ver'], $group);
                        }
                    } else {
                        $this->scriptFiles[$handle ? $handle : uniqid()] = array($srcProd['src'], $priority, $this->scriptFilesDefaultPriority++, $deps, $srcProd['ver'], $group);
                    }
                }
            }
        }

        /**
         * Add Script to HTML Output.
         *
         * @since MGPress 1.0
         *
         * @param string  $handle        (required) A unique string ID to ensure the script is output only once.
         * @param string  $src           (required) String of JavaScript to output. [Ex: "var foo = 'bar';"]
         * @param bool    $http2         True: print script immediately in HTML; False: register script with WordPress to load in the header or footer. Default is False.
         * @param array   $environments  Array of environments to restrict script output to, default is to output in all environments. ['dev', 'test', 'prod']
         * @param int     $priority      A priority weight given to the resource used during sorting when registered with WordPress: higher numbers will load sooner. Default is 0, loading by FIFO.
         * @return null
         */
        function addScript(
            $handle,
            $src,
            $http2 = false,
            $environments = null,
            $priority = 0
        ) {

            // add style to environment flag
            $addToEnv = (!is_array($environments) || in_array(ENVIRONMENT, $environments)) ? true : false;

            // basic sanity checks
            if ($addToEnv && is_string($src) && !empty($src)) {

                // support HTTP2
                if (($http2 && (defined('HTTP2') && HTTP2)) && !in_array($handle, $this->usedScriptHandles)) {
                    echo "\n<script type='text/javascript'>\n" . $src . "\n</script>\n";
                    $this->usedScriptHandles[] = $handle;
                    return;

                    // regular head or footer loading
                } elseif (is_int($priority)) {
                    if ($handle && isset($this->scripts[$handle])) {
                        if ($this->scripts[$handle][1] <= $priority) {
                            $this->scripts[$handle] = array($src, $priority, $this->scriptsDefaultPriority++);
                        }
                    } else {
                        $this->scripts[$handle ? $handle : uniqid()] = array($src, $priority, $this->scriptsDefaultPriority++);
                    }
                }
            }
        }

        /**
         * Localize Script.
         *
         * FYI: this function is used by MG to configure require.js
         *
         * @since MGPress 1.0
         *
         * @param string  $handle  (required) The registered script handle you are attaching the data for.
         * @param string  $name    (required) The name of the variable which will contain the data.
         * @param array   $data    (required) The data itself.
         * @return null
         */
        function localizeScript($handle, $name, $data)
        {
            wp_localize_script($handle, $name, $data);
        }

        /**
         * Register Stylesheets with WordPress, ordered by priority
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        function registerStyles()
        {

            // register stylesheets in queue
            if (is_array($this->stylesheets) && count($this->stylesheets)) {

                // sort by priority and loop over
                uasort($this->stylesheets, array($this, 'sortByPriority'));
                foreach ($this->stylesheets as $handle => $stylesheet) {

                    // skip stylesheets with used handles
                    if (in_array($handle, $this->usedStylesheetHandles)) {
                        continue;
                    }

                    // hook into WordPress
                    wp_register_style($handle, $stylesheet[0], $stylesheet[3], $stylesheet[4], $stylesheet[5]);
                    wp_enqueue_style($handle);

                    // clean up
                    $this->usedStylesheetHandles[] = $handle;
                    unset($this->stylesheets[$handle]);
                }
            }
        }

        /**
         * Print Styles, ordered by priority.
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        function printStyles()
        {

            // print styles in queue
            if (is_array($this->styles) && count($this->styles)) {

                // sort by priority and loop over
                uasort($this->styles, array($this, 'sortByPriority'));
                foreach ($this->styles as $handle => $style) {

                    // skip styles with used handles
                    if (in_array($handle, $this->usedStyleHandles)) {
                        continue;
                    }

                    // print style
                    echo "<style type='text/css' media='" . $style[3] . "'>\n" . $style[0] . "\n</style>\n";

                    // clean up
                    $this->usedStyleHandles[] = $handle;
                    unset($this->styles[$handle]);
                }
            }
        }

        /**
         * Register script files with WordPress, ordered by priority.
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        function registerScriptFiles()
        {

            // register script files in queue
            if (is_array($this->scriptFiles) && count($this->scriptFiles)) {

                // sort by priority and loop over
                uasort($this->scriptFiles, array($this, 'sortByPriority'));
                foreach ($this->scriptFiles as $handle => $scriptFile) {

                    // skip stylesheets with used handles
                    if (in_array($handle, $this->usedScriptFileHandles)) {
                        continue;
                    }

                    // hook into WordPress
                    wp_register_script($handle, $scriptFile[0], $scriptFile[3], $scriptFile[4]);
                    wp_enqueue_script($handle);

                    // clean up
                    $this->usedScriptFileHandles[] = $handle;
                    unset($this->scriptFiles[$handle]);
                }
            }
        }

        /**
         * Print Scripts, ordered by priority.
         *
         * @since MGPress 1.0
         *
         * @return null
         */
        function printScripts()
        {

            // print scripts in queue
            if (is_array($this->scripts) && count($this->scripts)) {

                // print opening script tag
                echo "\n<script type='text/javascript'>";

                // sort by priority and loop over
                uasort($this->scripts, array($this, 'sortByPriority'));
                foreach ($this->scripts as $handle => $script) {

                    // skip scripts with used handles
                    if (in_array($handle, $this->usedScriptHandles)) {
                        continue;
                    }

                    // print script
                    echo "\n" . $script[0];

                    // clean up
                    $this->usedScriptHandles[] = $handle;
                    unset($this->scripts[$handle]);
                }

                // print closing script tag
                echo "\n</script>\n";
            }
        }

        /**
         * Get Minified Production File.
         *
         * @since MGPress 1.0
         *
         * @param $src
         * @param string $ver
         * @return array
         */
        function getProdFile($src, $ver = '')
        {

            // pull prod asset
            $path = parse_url($src, PHP_URL_PATH);
            $file = $_SERVER['DOCUMENT_ROOT'].$path;
            $ext = pathinfo($file, PATHINFO_EXTENSION);

            $prodFile = '';
            $prodSrc = '';
            switch ($ext) {
                case 'js' :
                    if (!preg_match('/\.min\.js$/', $file)) {
                        $prodFile = preg_replace('/(\.built\.js$|\.js$)/', '.min.js', $file);
                        $prodSrc = preg_replace('/(\.built\.js$|\.js$)/', '.min.js', $src);
                    }
                    break;
                case 'css' :
                    if (!preg_match('/\.min\.css$/', $file)) {
                        $prodFile = preg_replace('/\.css$/', '.min.css', $file);
                        $prodSrc = preg_replace('/\.css$/', '.min.css', $src);
                    }
                    break;
            }

            $hash = $ver;
            $prodPath = realpath($prodFile);
            if ($prodPath && file_exists($prodPath)) {
                $src = $prodSrc;
                $hash = md5_file($prodPath);
            }

            return array('src'=>$src, 'ver'=>$hash);
        }

        /**
        * Sort by Priority.
         *
        * @since MGPress 1.0
         *
        * @param array $a
        * @param array $b
        * @return int
        */
        private static function sortByPriority(array $a, array $b)
        {
            if ($a[1] == $b[1]) {
                if ($a[2] == $b[2]) {
                    return 0;
                }
                return ($a[2] < $b[2]) ? -1 : 1;
            }
            return ($a[1] > $b[1]) ? -1 : 1;
        }
    }

    $MgPressAssets = new MgPressAssets();
    $MgPressAssets->init();
}
