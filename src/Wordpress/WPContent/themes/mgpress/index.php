<?php

// prepare data context
$data                = Timber::get_context();
$data['post']       = Timber::get_post();
$data['pagination']  = Timber::get_pagination();
$data['date_format'] = get_option('date_format');
$data['time_format'] = get_option('time_format');

$templateName = get_page_template_slug($data['post']->ID);
$controller = dirname(__FILE__) . '/controllers/list/' . $templateName . '.php';
if ($templateName && file_exists($controller)) {
    include_once($controller);
}

Timber::render(
    array(
        'list/post.html.twig',
        'list/page.html.twig'
    ),
    $data
);
