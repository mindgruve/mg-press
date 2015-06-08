<?php

// prepare data
$data                = Timber::get_context();
$data['posts']       = Timber::get_posts();
$data['pagination']  = Timber::get_pagination();
$data['date_format'] = get_option('date_format');
$data['time_format'] = get_option('time_format');

// render template
Timber::render('listing/list.html.twig', $data);
