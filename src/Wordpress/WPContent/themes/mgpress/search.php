<?php

// prepare data
$data                = Timber::get_context();
$data['posts']       = Timber::get_posts();
$data['pagination']  = $data['posts'] ? Timber::get_pagination() : null; // wordpress pagination seems broken for empty search results
$data['date_format'] = get_option('date_format');
$data['time_format'] = get_option('time_format');

// render template
Timber::render(
    array(
        'pages/search.html.twig',
        'content/list.html.twig',
    ),
    $data
);
