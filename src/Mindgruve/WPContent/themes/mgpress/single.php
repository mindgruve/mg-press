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
        'content/detail-' . get_post_type() . '.html.twig',
        'content/detail.html.twig',
    ),
    $data
);
