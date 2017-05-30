<?php

// prepare data
$data = Timber::get_context();

// render template
Timber::render('exception/404.html.twig', $data);
