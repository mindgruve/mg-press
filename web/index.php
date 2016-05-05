<?php
/**
 * Caching Adapter
 *
 *
 * @package WordPress
 */

include_once(__DIR__ . '/../vendor/autoload.php');

use Symfony\Component\HttpKernel\HttpCache\Store;
use Mindgruve\ReverseProxy\CachedReverseProxy;
use Mindgruve\ReverseProxy\Adapters\WordPressAdapter;

$store = new Store(dirname(__FILE__) . '/wp/wp-content/cache');
$reverseProxy = new CachedReverseProxy(
    new WordPressAdapter(
        function () {

            // Bootstrap Wordpress
            require(dirname(__FILE__) . '/wp/wp-blog-header.php');

        }
        , 900, $store
    )
);
$reverseProxy->run();
