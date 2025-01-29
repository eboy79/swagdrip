<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

// Load core files
require_once get_template_directory() . '/includes/setup.php';
require_once get_template_directory() . '/includes/custom-posts.php';
require_once get_template_directory() . '/includes/helpers.php';

add_action('template_redirect', function() {
   if(is_cart()) {
       error_log('Current template: ' . get_page_template());
       error_log('Is WooCommerce? ' . (is_woocommerce() ? 'yes' : 'no'));
       error_log('Current page: ' . get_queried_object()->post_name);
   }
});