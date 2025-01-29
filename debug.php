<?php
// includes/debug.php

function debug_woocommerce() {
    if (is_cart()) {
        error_log('Cart page detected');
        error_log('WC Template path: ' . WC()->template_path());
        error_log('Theme directory: ' . get_template_directory());
        error_log('Stylesheet directory: ' . get_stylesheet_directory());
    }
}
add_action('template_redirect', 'debug_woocommerce');