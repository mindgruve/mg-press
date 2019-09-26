<?php

/**
 * The template for displaying all single pages
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
$context         = Timber::get_context();
$post            = Timber::query_post();
$context['post'] = $post;

// custom template
$templateName = $post->meta('_wp_page_template') ? $post->meta('_wp_page_template') : null;

// controller (optional)
if (!is_null($templateName)) {
    $controller = dirname(__FILE__) . '/controllers/' . $templateName . '.php';
    if ($templateName && file_exists($controller)) {
        include_once($controller);
    }
}

// determine templates
if (post_password_required($post->ID)) {
    $templates = array('detail/password.twig');
} else {

    $templates = array(
        'detail/page-' . $post->slug . '.twig',
        'detail/page-' . $post->ID . '.twig',
        'detail/page.twig',
        'index.twig'
    );

    // site front page template
    if (is_front_page()) {
        array_unshift($templates, 'detail/front-page.twig');
    }

    // add template if being used
    if (!is_null($templateName)) {
        array_unshift($templates, 'template/' . $templateName . '.twig');
    }
}

// render views
Timber::render($templates, $context);
