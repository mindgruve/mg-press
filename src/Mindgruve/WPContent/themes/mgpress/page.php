<?php

// prepare data context
$data                 = Timber::get_context();
$data['post']         = new TimberPost();
$data['comment_form'] = TimberHelper::get_comment_form();
$data['date_format']  = get_option('date_format');
$data['time_format']  = get_option('time_format');

// render template
Timber::render(
    array(
        'pages/' . $data['post']->post_name . '.html.twig',
        'single/detail-' . get_post_type() . '.html.twig',
        'single/detail.html.twig',
    ),
    $data
);
