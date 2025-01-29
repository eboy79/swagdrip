<?php
// includes/helpers.php

// Custom excerpt from first paragraph
function custom_first_paragraph_excerpt($excerpt) {
    $content = get_the_content();
    preg_match('/<p>(.*?)<\/p>/s', $content, $matches);
    
    if (isset($matches[1])) {
        return $matches[1] . '';
    }
    return $excerpt;
}
add_filter('the_excerpt', 'custom_first_paragraph_excerpt');

// Custom excerpt more link
function custom_excerpt_more($more) {
    return ' <a href="' . get_permalink() . '" class="read-more">→</a>';
}
add_filter('excerpt_more', 'custom_excerpt_more');

// Custom body class
function add_custom_body_class($classes) {
    if (is_single() && get_the_ID() == 48) {
        $classes[] = 'zoomer-post';
    }
    return $classes;
}
add_filter('body_class', 'add_custom_body_class');

// Custom title with yellow halo
function custom_title_with_yellow_halo($title) {
    if (is_single() && get_the_ID() == 6) {
        $title = preg_replace('/\bWordPress\b/', '<span class="yellow-halo">WordPress</span>', $title);
    }
    return $title;
}
add_filter('the_title', 'custom_title_with_yellow_halo');