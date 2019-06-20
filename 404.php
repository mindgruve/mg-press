<?php

/**
 * The 404 Not Found error page
 *
 * https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

// set context
$context = Timber::get_context();

// render views
Timber::render(
    array(
        'exception/404.twig',
        'index.twig',
    ),
    $context
);
