<?php
// includes/custom-posts.php

// Register Custom Post Type: Snippets
function register_snippet_post_type() {
    $args = array(
        "label"               => "Snippets",
        "labels"             => array(
            'name'               => 'Snippets',
            'singular_name'      => 'Snippet',
            'menu_name'          => 'Snippets',
            'add_new'           => 'Add New',
            'add_new_item'      => 'Add New Snippet',
            'edit_item'         => 'Edit Snippet',
            'new_item'          => 'New Snippet',
            'view_item'         => 'View Snippet',
            'search_items'      => 'Search Snippets',
            'not_found'         => 'No snippets found',
            'not_found_in_trash'=> 'No snippets found in trash'
        ),
        "public"            => true,
        "has_archive"       => true,
        "show_in_rest"      => true,
        "supports"          => array("title", "editor", "excerpt", "thumbnail"),
        "menu_icon"         => "dashicons-editor-code",
        "rewrite"           => array("slug" => "snippets"),
        "taxonomies"        => array("snippet_category"),
        "menu_position"     => 5,
        "capability_type"   => "post",
    );
    register_post_type("snippet", $args);
}
add_action("init", "register_snippet_post_type", 9);

// Register Taxonomy: snippet_category
function register_snippet_category_taxonomy() {
    register_taxonomy('snippet_category', ['snippet'], [
        'hierarchical'      => false,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => ['slug' => 'snippet-category'],
        'labels'            => [
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
add_action('init', 'register_snippet_category_taxonomy', 10);

// AJAX handler for updating snippets
function update_snippet() {
    // Nonce verification
    if (!check_ajax_referer('snippet_nonce', 'security', false)) {
        wp_send_json_error(['message' => 'Nonce verification failed']);
    }

    // Validation
    if (!current_user_can('edit_posts') || !isset($_POST['post_id'], $_POST['content'], $_POST['title'])) {
        wp_send_json_error(['message' => 'Unauthorized or missing parameters']);
    }

    $post_id = intval($_POST['post_id']);
    $bg_color = isset($_POST['bg_color']) ? sanitize_hex_color($_POST['bg_color']) : '';
    $content = sanitize_textarea_field($_POST['content']);
    $title = sanitize_text_field($_POST['title']);
    $slug = sanitize_title($title);

    // Update meta first
    update_post_meta($post_id, 'snippet_bg_color', $bg_color);

    // Update post
    $updated_post = wp_update_post([
        'ID'           => $post_id,
        'post_title'   => $title,
        'post_name'    => $slug,
        'post_content' => $content
    ], true);

    if (is_wp_error($updated_post)) {
        wp_send_json_error(['message' => 'Error updating snippet']);
    }

    wp_send_json_success([
        'message' => 'Snippet updated successfully',
        'new_permalink' => get_permalink($post_id)
    ]);
}
add_action('wp_ajax_update_snippet', 'update_snippet');