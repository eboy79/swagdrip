<?php
// Theme Setup
function swagdrip_setup() {
    add_theme_support('menus');
    add_theme_support('custom-logo');

    register_nav_menus([
        'primary' => __('Primary Menu', 'swagdrip'),
    ]);
}
add_action('after_setup_theme', 'swagdrip_setup');

function custom_first_paragraph_excerpt($excerpt) {
    // Get the content of the post
    $content = get_the_content();
    
    // Match the first paragraph in the content
    preg_match('/<p>(.*?)<\/p>/s', $content, $matches);
    
    // If a paragraph is found, return it as the excerpt
    if (isset($matches[1])) {
        return $matches[1] . ''; // Append "..." to the excerpt
    }
    
    // If no paragraph is found, return the default excerpt
    return $excerpt;
}
add_filter('the_excerpt', 'custom_first_paragraph_excerpt');


function custom_excerpt_more($more) {
    return ' <a href="' . get_permalink() . '" class="read-more">→</a>';
}
add_filter('excerpt_more', 'custom_excerpt_more');


// Disable Heartbeat API (Frontend Only)
function swagdrip_disable_heartbeat() {
    if (!is_admin()) {
        wp_deregister_script('heartbeat');
    }
}
add_action('init', 'swagdrip_disable_heartbeat');

// Enqueue Styles & Scripts
function swagdrip_enqueue_assets() {
    wp_enqueue_style('theme-style', get_template_directory_uri() . '/dist/main.min.css', [], '1.0');

    // Remove jQuery only on frontend, keep it in admin panel
    if (!is_admin()) {
        wp_deregister_script('jquery');
    }

    wp_enqueue_script('main-js', get_template_directory_uri() . '/dist/main.min.js', [], false, true);
}
add_action('wp_enqueue_scripts', 'swagdrip_enqueue_assets');

// Remove WordPress Bloat
function swagdrip_cleanup() {
    // Remove emojis
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');

    // Remove WordPress oEmbed
    remove_action('wp_head', 'wp_oembed_add_host_js');

    // Remove WP generator tag
    remove_action('wp_head', 'wp_generator');

    // Remove extra meta tags
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');

    // Remove Gutenberg Block Library CSS
    wp_dequeue_style('wp-block-library');
}
add_action('init', 'swagdrip_cleanup');

// Allow WebP Uploads
function allow_webp_upload($mimes) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter('upload_mimes', 'allow_webp_upload');

function add_custom_body_class($classes) {
    if (is_single() && get_the_ID() == 48) {
        $classes[] = 'zoomer-post';  // Add custom class for post ID 48
    }
    return $classes;
}
add_filter('body_class', 'add_custom_body_class');


function custom_title_with_yellow_halo($title) {
    if (is_single() && get_the_ID() == 6) {
        // Wrap 'WordPress' with a span tag
        $title = preg_replace('/\bWordPress\b/', '<span class="yellow-halo">WordPress</span>', $title);
    }
    return $title;
}
add_filter('the_title', 'custom_title_with_yellow_halo');


?>
