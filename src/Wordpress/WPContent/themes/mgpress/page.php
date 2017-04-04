<?php

// prepare data context
$data                 = Timber::get_context();
$data['post']         = new TimberPost();
$data['comment_form'] = TimberHelper::get_comment_form();
$data['date_format']  = get_option('date_format');
$data['time_format']  = get_option('time_format');

$templateName = get_page_template_slug($data['post']->ID);
$controller = dirname(__FILE__) . '/controllers/' . $templateName . '.php';
if ($templateName && file_exists($controller)) {
    include_once($controller);
}

Timber::render(
    array(
        $templateName . '.html.twig',
        'detail/page.html.twig'
    ),
    $data
);
