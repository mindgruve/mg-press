<?php

namespace Mindgruve\MgPress;

use Symfony\Component\Filesystem\Filesystem;
use Composer\Script\Event;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;

class ComposerScripts
{

    /**
     * @param Event $e
     */
    static public function updateWordpress(Event $e)
    {
        self::symlinkWordpressFiles($e);
    }

    /**
     * @param Event $e
     */
    static public function installWordpress(Event $e)
    {
        self::symlinkWordpressFiles($e);
    }


    /**
     * Symlink Wordpress Folders
     * web/wp/wp-content/plugins -> plugins
     * web/wp/wp-contnet/themes  -> themes
     *
     * @param Event $e
     */
    static public function symlinkWordpressFiles(Event $e)
    {
        $fs = new Filesystem();
        $io = $e->getIO();
        $finder = new Finder();
        $extra = $e->getComposer()->getPackage()->getExtra();
        $muPlugins = isset($extra['wp-mu-plugins']) ? $extra['wp-mu-plugins'] : array();

        try {

            // symlink wordpress content folder
            $contentPath = __DIR__ . '/../../../web/wp/wp-content';
            $fs->remove(array($contentPath));
            $newContentPath = '../../src/Wordpress/WPContent';
            $fs->symlink($newContentPath, $contentPath);
            $io->write('<info>Symlinked Wordpress Plugin and Themes</info>');

            // symlink wordpress vendor plugins
            if (!$fs->exists(__DIR__ . '/../../../vendor/wpackagist/')) {
                $fs->mkdir(__DIR__ . '/../../../vendor/wpackagist/plugins/');
            }

            $finder->directories()->in(__DIR__ . '/../../../vendor/wpackagist/plugins/')->depth('== 0');
            foreach ($finder as $directory) {

                $name = $directory->getFilename();
                if (in_array($name, $muPlugins)) {
                    $targetPath = __DIR__ . '/../../../src/Wordpress/WPContent/mu-plugins';
                    $relPath = $fs->makePathRelative($directory, $targetPath);
                } else {
                    $targetPath = __DIR__ . '/../../../src/Wordpress/WPContent/plugins';
                    $relPath = $fs->makePathRelative($directory, $targetPath);
                }
                $fs->symlink($relPath, $targetPath . '/' . $name);
            }
            $io->write('<info>Symlinked Wordpress Vendor Plugins</info>');


            // autogenerating mu-plugin init
            foreach ($muPlugins as $muPlugin) {
                $file = __DIR__ . '/../../../src/Wordpress/WPContent/mu-plugins/' . $muPlugin . '/' . $muPlugin . '.php';
                $data = self::get_file_data($file);
                $data['plugin_name'] = $muPlugin;
                $loader = new \Twig_Loader_Filesystem(__DIR__);
                $twig = new \Twig_Environment($loader);
                $rendered = $twig->render('mu-plugin-init.php.twig', $data);
                file_put_contents(
                    __DIR__ . '/../../../src/Wordpress/WPContent/mu-plugins/init-' . $muPlugin . '.php',
                    $rendered
                );
                $twig->loadTemplate('mu-plugin-init.php.twig');
            }

            // symlink wordpress vendor themes
            if (!$fs->exists(__DIR__ . '/../../../vendor/wpackagist/themes/')) {
                $fs->mkdir(__DIR__ . '/../../../vendor/wpackagist/themes/');
            }

            // copy wp-config file.
            $fs->copy(__DIR__ . '/wp-config.php.tmp', __DIR__ . '/../../../web/wp/wp-config.php');
            $io->write('<info>Copied Wordpress Config Page</info>');

            /**
             * Create wpConfig.yml
             */

            // copy config dist file
            $config = __DIR__ . '/../../../config/wpConfig.yml';
            $configDist = __DIR__ . '/../../../config/wpConfig.yml.dist';

            $parser = new Parser();
            $configDistValues = $parser->parse(file_get_contents($configDist));

            $io->write('<info>Creating config...</info>');

            if ($fs->exists($configDist) && !$fs->exists($config)) {
                $finalConfig = array();

            } else {
                $finalConfig = $parser->parse(file_get_contents($config));
            }

            /**
             * Generate API
             */
            $generate = $io->ask('<question>Do you want to regenerate Wordpress security tokens</question> (y/n)?');
            if($generate == 'y'){
                $wpApi = file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/');
                preg_match_all('/define\(\'(.*)\'\,(.*)\'(.*)\'\);/', $wpApi, $matches);
                $wpApiKeys = $matches[1];
                $wpApiValues = $matches[3];

                foreach ($wpApiKeys as $index => $key) {
                    if (isset($configDistValues['parameters'][$key])) {
                        $finalConfig['parameters'][$key] = $wpApiValues[$index];
                    }
                }
            }

            foreach ($configDistValues['parameters'] as $key => $value) {
                if (!isset($finalConfig['parameters'][$key])) {

                    if ($value === true) {
                        $defaultValue = 'true';
                    } elseif ($value === false) {
                        $defaultValue = 'false';
                    } elseif(is_null($value)){
                        $defaultValue = 'null';
                    } else {
                        $defaultValue = '\''.$value.'\'';
                    }

                    $repliedValue = $io->ask('<question>' . $key . '</question> (<comment>' . $defaultValue . '</comment>)  ');
                    if ($repliedValue) {
                        $value = $repliedValue;
                    }
                    $finalConfig['parameters'][$key] = $value;
                }
            }

            file_put_contents($config, "# This file is auto-generated during the composer install\n" . Yaml::dump($finalConfig, 99));

        } catch (\Exception $e) {
            echo $e->getMessage();

            $io->write(
                '<error>Unable to symlink the plugin and theme directory for WordPress.  You may have to manually symlink these folders</error>'
            );
        }
    }

    public static function upgradeWordpressDB(Event $e)
    {
        $io = $e->getIO();

        $_SERVER = array(
            "HTTP_HOST" => 'localhost',
            "SCRIPT_FILENAME" => realpath(__DIR__ . '/../../../web/wp/wp-admin/includes/upgrade.php'),
            "SCRIPT_NAME" => '/wp/wp-admin/includes/upgrade.php',
            "PHP_SELF" => '/wp/wp-admin/includes/upgrade.php',
            "REQUEST_URI" => "/",
            "REQUEST_METHOD" => "GET"
        );

        include_once(__DIR__ . '/../../../web/wp/wp-load.php');
        include_once(__DIR__ . '/../../../web/wp/wp-admin/includes/upgrade.php');

        $io->write('<info>Starting to upgrade WordPress Database</info>');
        try {
            \wp_upgrade();
        } catch (\Exception $e) {
            $io->write('<error>Unable to upgrade wordpress</error>');
            return;
        }
        $io->write('<info>WordPress Upgrade Complete!</info>');
    }


    /**
     * Read Comments from Plugin
     * https://core.trac.wordpress.org/browser/tags/4.1/src/wp-admin/includes/plugin.php
     * @param $file
     * @param string $context
     * @return array
     */
    public static function get_file_data($file, $context = '')
    {
        $default_headers = array(
            'Name' => 'Plugin Name',
            'PluginURI' => 'Plugin URI',
            'Version' => 'Version',
            'Description' => 'Description',
            'Author' => 'Author',
            'AuthorURI' => 'Author URI',
            'TextDomain' => 'Text Domain',
            'DomainPath' => 'Domain Path',
            'Network' => 'Network',
            // Site Wide Only is deprecated in favor of Network.
            '_sitewide' => 'Site Wide Only',
        );


        // We don't need to write to the file, so just open for reading.
        $fp = fopen($file, 'r');

        // Pull only the first 8kiB of the file in.
        $file_data = fread($fp, 8192);

        // PHP will close file handle, but we are good citizens.
        fclose($fp);

        // Make sure we catch CR-only line endings.
        $file_data = str_replace("\r", "\n", $file_data);

        foreach ($default_headers as $field => $regex) {
            if (preg_match('/^[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $file_data, $match) && $match[1]) {
                $default_headers[$field] = $match[1];
            } else {
                $default_headers[$field] = '';
            }
        }

        return $default_headers;
    }
}
