<?php

/**
 * The template for displaying archive lists
 *
 * https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package     WordPress
 * @subpackage  MGPress
 * @version     1.0.2
 * @since       MGPress 1.0
 * @author      kchevalier@mindgruve.com
 */

// set context
$context = Timber::get_context();
try {
    $urlArray = explode('/', trim($_SERVER["REQUEST_URI"], '/'));
    $context['post'] = Timber::query_post(end($urlArray));
} catch (\Exception $e) {
    $context['post'] = null;
}

// prepare template array
$templates = array();

if (is_tax()) {

    $context['term'] = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));
    $context['taxonomy'] = get_taxonomy(get_query_var('taxonomy'));

    // controller (optional)
    $controller = dirname(__FILE__).'/controllers/list-taxonomy-'.$context['taxonomy']->rewrite['slug'].'.php';
    if (file_exists($controller)) {
        include_once($controller);
    }


    $templates[] = 'list/taxonomy-' . $context['taxonomy']->rewrite['slug'] . '-' . $context['term']->slug . '.twig';
    $templates[] = 'list/taxonomy-' . $context['taxonomy']->rewrite['slug'] . '.twig';
    $templates[] = 'list/taxonomy.twig';

} elseif (is_category()) {

    $context['category'] = get_category(get_query_var('cat'));

    $templates[] = 'list/category-' . $context['category']->slug . '.twig';
    $templates[] = 'list/category-' . $context['category']->term_id . '.twig';
    $templates[] = 'list/category.twig';

} elseif (is_tag()) {

    $context['tag'] = get_queried_object();

    $templates[] = 'list/tag-' . $context['tag']->slug . '.twig';
    $templates[] = 'list/tag-' . $context['tag']->term_id . '.twig';
    $templates[] = 'list/tag.twig';

} elseif (is_author()) {

    $context['author'] = new TimberUser($wp_query->query_vars['author']);

    $templates[] = 'list/author-' . $context['author']->user_nicename . '.twig';
    $templates[] = 'list/author-' . $context['author']->ID . '.twig';
    $templates[] = 'list/author.twig';

} elseif (is_date()) {

    if (is_year()) {
        $context['date'] = get_the_time('Y');
    } else {
        $context['date'] = get_the_time('F Y');
    }

    $templates[] = 'list/date.twig';

} elseif (is_post_type_archive()) {

    $context['post_type'] = get_post_type_object(get_query_var('post_type'));

    // controller (optional)
    $controller = dirname(__FILE__).'/controllers/list-'.$context['post_type']->name.'.php';
    if (file_exists($controller)) {
        include_once($controller);
    }

    $templates[] = 'list/' . $context['post_type']->name . '.twig';
};

$templates[] = 'list/archive.twig';
$templates[] = 'list/post.twig';
$templates[] = 'list/single.twig';
$templates[] = 'index.twig';

// render template
Timber::render($templates, $context);
