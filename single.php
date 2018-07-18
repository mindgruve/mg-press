<?php
/**
 * The template for displaying all single posts
 *
 * https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0.1
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
$controller = dirname(__FILE__).'/controllers/detail-'.$post->post_type.'.php';
if (file_exists($controller)) {
    include_once($controller);
}

// determine templates
if (post_password_required($post->ID)) {
    $templates = array('detail/password.twig');
} else {

    $templates = array(
        'detail/' . $post->post_type . '-' . $post->slug . '.twig',
        'detail/' . $post->post_type . '.twig',
        'detail/post.twig',
        'detail/single.twig',
        'index.twig'
    );

    // add template if being used
    if (!is_null($templateName)) {
        array_unshift($templates, 'template/' . $templateName . '.twig');
    }
}

// render views
Timber::render($templates, $context);
