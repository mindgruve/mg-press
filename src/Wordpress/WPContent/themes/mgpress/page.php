<?php

// prepare data context
$data                 = Timber::get_context();
$data['page']         = new TimberPost();
$data['comment_form'] = TimberHelper::get_comment_form();
$data['date_format']  = get_option('date_format');
$data['time_format']  = get_option('time_format');

$templateName = $data['page']->get_field('_wp_page_template') ? $data['page']->get_field('_wp_page_template') : null;
$controller = dirname(__FILE__) . '/controllers/' . $templateName . '.php';
if ($templateName && file_exists($controller)) {
    include_once($controller);
}

Timber::render(
    array(
        $templateName . '.html.twig',
        'pages/default.html.twig'
    ),
    $data
);
