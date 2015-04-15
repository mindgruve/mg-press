<?php

/**
 * Theme activation
 */

// Theme Activation Actions
function theme_activation_action() {

    // flush URL rewrite rules cache
    flush_rewrite_rules();
}

// Apply theme activation action
add_action('after_switch_theme', 'theme_activation_action');
