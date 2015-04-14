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

    $templates[] = 'content/list-taxonomy-' . $data['term']->taxonomy . '.html.twig';
    $templates[] = 'content/list-taxonomy.html.twig';

} elseif (is_category()) {

    $data['category'] = get_category(get_query_var('cat'));

    $templates[] = 'content/list-category-' . $data['category']->slug . '.html.twig';
    $templates[] = 'content/list-category.html.twig';

} elseif (is_tag()) {

    $data['tag'] = get_queried_object();

    $templates[] = 'content/list-tag-' . $data['tag']->name . '.html.twig';
    $templates[] = 'content/list-tag.html.twig';

} elseif (is_author()) {

    $data['author'] = get_queried_object();

    $templates[] = 'content/list-author-' . $data['author']->user_nicename . '.html.twig';
    $templates[] = 'content/list-author.html.twig';

} elseif (is_date()) {

    if (is_year()) {
        $data['date'] = get_the_time('Y');
    } else {
        $data['date'] = get_the_time('F Y');
    }

    $templates[] = 'content/list-date.html.twig';

} elseif (is_post_type_archive()) {

    $data['post_type'] = get_post_type_object(get_query_var('post_type'));

    $templates[] = 'content/list-' . $data['post_type']->name . '.html.twig';

}

$templates[] = 'content/list.html.twig';

// render template
Timber::render($templates, $data);
