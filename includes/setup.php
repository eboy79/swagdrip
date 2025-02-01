<?php
// Prevent direct access
if (!defined('ABSPATH')) exit;

/**
 * Theme Setup
 */
function swagdrip_setup() {
    // Basic theme support
    add_theme_support('menus');
    add_theme_support('custom-logo');
    add_theme_support('editor-styles');
    add_theme_support('post-thumbnails');
    add_theme_support('align-wide');
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('responsive-embeds');
    
    // Remove block widget editor
    remove_theme_support('widgets-block-editor');

    // Register navigation menus
    register_nav_menus([
        'primary' => __('Primary Menu', 'swagdrip'),
    ]);
}
add_action('after_setup_theme', 'swagdrip_setup');

/**
 * WooCommerce Support & Integration
 */
// Add to your setup.php
function swagdrip_woocommerce_setup() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'swagdrip_woocommerce_setup');

/**
 * WooCommerce Blocks Integration
 */
function swagdrip_enqueue_wc_blocks() {
    if (!is_cart() && !is_checkout() && !is_shop()) return;

    // Just ensure basic WC scripts are loaded
    wp_enqueue_script('wc-cart-fragments');
    wp_enqueue_script('woocommerce');
    wp_enqueue_style('woocommerce-general');
}
add_action('wp_enqueue_scripts', 'swagdrip_enqueue_wc_blocks', 20);


/**
 * Theme Assets
 */
function swagdrip_enqueue_assets() {
    $css_file = get_template_directory() . '/dist/main.min.css';
    $js_file = get_template_directory() . '/dist/main.min.js';

    if (file_exists($css_file)) {
        wp_enqueue_style('theme-style', get_template_directory_uri() . '/dist/main.min.css', [], filemtime($css_file));
    }

    if (file_exists($js_file)) {
        // Change the handle to match the localization
        wp_enqueue_script('theme-main-script', get_template_directory_uri() . '/dist/main.min.js', [], filemtime($js_file), true);
        
        // Localize with the same handle
        wp_localize_script(
            'theme-main-script', // Must match the handle above
            'mnrInfiniteScroll',
            array(
                'restUrl' => esc_url_raw(rest_url('wp/v2/posts')),
                'nonce' => wp_create_nonce('wp_rest'),
                'postsPerPage' => (int) get_option('posts_per_page')
            )
        );
    }
}
add_action('wp_enqueue_scripts', 'swagdrip_enqueue_assets');

/**
 * Additional Functionality
 */
function swagdrip_allow_webp_upload($mimes) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter('upload_mimes', 'swagdrip_allow_webp_upload');

function swagdrip_cleanup() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('init', 'swagdrip_cleanup');

function new_excerpt_more( $more ) {
    return '';
}
add_filter('excerpt_more', 'new_excerpt_more');
