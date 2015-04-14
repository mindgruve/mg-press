<?php

// prepare data
$data = Timber::get_context();

// render template
Timber::render('pages/404.html.twig', $data);
