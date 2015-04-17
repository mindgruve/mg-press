<?php

// prepare data
$data                = Timber::get_context();
$data['posts']       = Timber::get_posts();
$data['pagination']  = Timber::get_pagination();
$data['date_format'] = get_option('date_format');
$data['time_format'] = get_option('time_format');


// prepare template array
$templates = array();

if (is_tax()) {

    $data['term'] = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));

    $templates[] = 'listing/list-taxonomy-' . $data['term']->taxonomy . '.html.twig';
    $templates[] = 'listing/list-taxonomy.html.twig';

} elseif (is_category()) {

    $data['category'] = get_category(get_query_var('cat'));

    $templates[] = 'listing/list-category-' . $data['category']->slug . '.html.twig';
    $templates[] = 'listing/list-category.html.twig';

} elseif (is_tag()) {

    $data['tag'] = get_queried_object();

    $templates[] = 'listing/list-tag-' . $data['tag']->name . '.html.twig';
    $templates[] = 'listing/list-tag.html.twig';

} elseif (is_author()) {

    $data['author'] = get_queried_object();

    $templates[] = 'listing/list-author-' . $data['author']->user_nicename . '.html.twig';
    $templates[] = 'listing/list-author.html.twig';

} elseif (is_date()) {

    if (is_year()) {
        $data['date'] = get_the_time('Y');
    } else {
        $data['date'] = get_the_time('F Y');
    }

    $templates[] = 'listing/list-date.html.twig';

} elseif (is_post_type_archive()) {

    $data['post_type'] = get_post_type_object(get_query_var('post_type'));

    $templates[] = 'listing/list-' . $data['post_type']->name . '.html.twig';

}

$templates[] = 'listing/list.html.twig';

// render template
Timber::render($templates, $data);
