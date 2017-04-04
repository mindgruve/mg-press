<?php

// prepare data context
$data = Timber::get_context();
$data['post'] = new TimberPost();
$data['comment_form'] = TimberHelper::get_comment_form();
$data['date_format'] = get_option('date_format');
$data['time_format'] = get_option('time_format');


$controller = dirname(__FILE__).'/controllers/detail/'.get_post_type().'.php';
if (file_exists($controller)) {
    include_once($controller);
}

// render template
Timber::render(
    array(
        'detail/'.get_post_type().'.html.twig',
        'detail/page.html.twig',
    ),
    $data
);
