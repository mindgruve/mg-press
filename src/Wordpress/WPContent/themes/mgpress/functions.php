<?php

/**
 * Theme Includes
 */

$theme_includes = array(
  '/lib/theme_setup.php',      // Theme setup
  '/lib/activation.php',       // Theme activation
  '/lib/cleanup.php',          // Cleanup
  '/lib/assets.php',           // Assets
  '/lib/comments.php',         // Comments
  '/lib/widgets.php',          // Widget areas
  '/lib/custom.php',           // Custom scripts
  '/lib/woops_setup.php',      // Woops
  '/lib/menu_setup.php',       // Menu Setup
  '/lib/pages_setup.php',      // Pages Setup
);

foreach($theme_includes as $file){
  if(!$filepath = locate_template($file)) {
    trigger_error("Error locating `$file` for inclusion!", E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);
