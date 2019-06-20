<?php

/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists
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

// if using a CMS page as blog home page get that Page
if (is_home() && !is_front_page()) {
    $context['post'] = new TimberPost();
}

// determine templates
$templates = array(
    'list/post.twig',
    'list/single.twig',
    'index.twig'
);

// blog home page template
if (is_home()) {
	array_unshift($templates, 'list/home.twig');
}

// render views
Timber::render($templates, $context);
