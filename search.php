<?php

/**
 * Search results page
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
$context          = Timber::get_context();
$context['posts'] = new Timber\PostQuery();

// render views
Timber::render(
    array(
        'list/search.twig',
        'list/post.twig',
        'list/single.twig',
        'index.twig'
    ),
    $context
);
