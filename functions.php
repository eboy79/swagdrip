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

    if (!is_admin()) {
        wp_deregister_script('jquery');
    }

    wp_enqueue_script('main-js', get_template_directory_uri() . '/dist/main.min.js', [], filemtime(get_template_directory() . '/dist/main.min.js'), true);
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


// Register Custom Post Type: Snippets
function register_snippet_post_type() {
    $args = array(
        "label"  => "Snippets",
        "public" => true,
        "show_in_rest" => true,
        "supports" => array("title", "editor"),
        "menu_icon" => "dashicons-editor-code",
        "rewrite" => array("slug" => "snippets"),
    );
    register_post_type("snippet", $args);
}
add_action("init", "register_snippet_post_type", 9); // Ensures it runs before taxonomies

function register_snippet_category_taxonomy() {
    register_taxonomy('snippet_category', ['snippet'], [ // <-- Ensure 'snippet' is here
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'query_var'             => true,
        'rewrite'               => ['slug' => 'snippet-category'],
        'show_in_rest'          => true,
        'labels'                => [
            'name'              => __('Snippet Categories', 'textdomain'),
            'singular_name'     => __('Snippet Category', 'textdomain'),
            'search_items'      => __('Search Snippet Categories', 'textdomain'),
            'all_items'         => __('All Snippet Categories', 'textdomain'),
            'edit_item'         => __('Edit Snippet Category', 'textdomain'),
            'update_item'       => __('Update Snippet Category', 'textdomain'),
            'add_new_item'      => __('Add New Snippet Category', 'textdomain'),
            'new_item_name'     => __('New Snippet Category Name', 'textdomain'),
            'menu_name'         => __('Snippet Categories', 'textdomain'),
        ]
    ]);
}
add_action('init', 'register_snippet_category_taxonomy');

function update_snippet() {
    if (!current_user_can('edit_posts') || !isset($_POST['post_id'], $_POST['content'], $_POST['title'])) {
        wp_send_json_error(['message' => 'Unauthorized or missing parameters']);
    }

    $post_id = intval($_POST['post_id']);
    $content = sanitize_textarea_field($_POST['content']); // Allow multiple lines
    $title = sanitize_text_field($_POST['title']); // Sanitize new title
    $slug = sanitize_title($title); // Generate new permalink slug

    // Update the post
    $updated_post = wp_update_post([
        'ID'           => $post_id,
        'post_title'   => $title,
        'post_name'    => $slug, // Update slug
        'post_content' => $content
    ], true);

    if (is_wp_error($updated_post)) {
        wp_send_json_error(['message' => 'Error updating snippet']);
    } else {
        // Get new permalink
        $new_permalink = get_permalink($post_id);

        wp_send_json_success([
            'message' => 'Snippet updated successfully',
            'new_permalink' => $new_permalink
        ]);
    }
}
add_action('wp_ajax_update_snippet', 'update_snippet');
