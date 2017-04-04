<?php

// prepare data
$data                = Timber::get_context();
$data['posts']       = Timber::get_posts();
$data['pagination']  = Timber::get_pagination();
$data['date_format'] = get_option('date_format');
$data['time_format'] = get_option('time_format');
// prepare template array
$templates = array();

$is_category_page = false;

if (is_tax()) {

    $data['category'] = get_category(get_query_var('cat'));

    $templates[] = 'list/taxonomy-' . $data['category']->slug . '.html.twig';
    $templates[] = 'list/taxonomy.html.twig';
    $templates[] = 'list/post.html.twig';

} elseif (is_category()) {

    $data['category'] = get_category(get_query_var('cat'));

    $templates[] = 'list/category-' . $data['category']->slug . '.html.twig';
    $templates[] = 'list/post.html.twig';
    $templates[] = 'list/category.html.twig';

} elseif (is_tag()) {

    $data['tag'] = get_queried_object();

    $templates[] = 'list/tag-' . $data['tag']->name . '.html.twig';
    $templates[] = 'list/tag.html.twig';

} elseif (is_author()) {

    $data['author'] = get_queried_object();

    $templates[] = 'list/author-' . $data['author']->user_nicename . '.html.twig';
    $templates[] = 'list/author.html.twig';

} elseif (is_date()) {

    if (is_year()) {
        $data['date'] = get_the_time('Y');
    } else {
        $data['date'] = get_the_time('F Y');
    }

    $templates[] = 'list/date.html.twig';

} elseif (is_post_type_archive()) {

    $data['post_type'] = get_post_type_object(get_query_var('post_type'));

    $templates[] = 'list/' . $data['post_type']->name . '.html.twig';

};

if (isset($data['term']->taxonomy) && $data['term']->taxonomy == 'product_cat'){
    $templates[] = 'list/categories.html.twig';
}
$templates[] = 'list/page.html.twig';

$templateName = isset($data['post_type']) ? $data['post_type']->name : '';
$controller = dirname(__FILE__) . '/controllers/archive/' . $templateName . '.php';
if ($templateName && file_exists($controller)) {
    include_once($controller);
}

//var_dump($templates); exit;
// render template
Timber::render($templates, $data);
