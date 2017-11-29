<?php

/**
 * The template for displaying all single attachments
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

// get mimetype
list($mimetype, $subtype) = explode('/', get_post_mime_type($post->ID));

// render views
if (post_password_required($post->ID)) {
    Timber::render('detail/password.twig', $context);
} else {
    Timber::render(
        array(
            'detail/' . $mimetype . '-' . $subtype . '.twig',
            'detail/' . $subtype . '.twig',
            'detail/' . $mimetype . '.twig',
            'detail/attachment.twig',
            'detail/post.twig',
            'detail/single.twig',
            'index.twig'
        ),
        $context
    );
}
