<?php
/**
 * Template Name: Snippets Grid
 * Template Post Type: page
 */

get_header(); ?>

<main>
    <div class="snippets-container">
        <h1 class="page-title"><?php the_title(); ?></h1>

        <?php
        // Get all snippet categories
        $categories = get_terms( array(
            'taxonomy'   => 'snippet_category',
            'hide_empty' => false,  // Show categories even if empty
            'orderby'    => 'count', // Order by post count
            'order'      => 'DESC',  // Descending order
        ) );

        if (!empty($categories)) {
            foreach ($categories as $category) {
                echo "<div class='snippet-category'>";
                echo "<h2 class='snippet-category-title'>{$category->name}</h2>";
                
                // Query posts in this category
                $args = array(
                    'post_type'      => 'snippet',
                    'posts_per_page' => -1,
                    
                    'tax_query'      => array(
                        array(
                            'taxonomy' => 'snippet_category',
                            'field'    => 'slug',
                            'terms'    => $category->slug,
                        ),
                    ),
                );

                $query = new WP_Query($args);

                if ($query->have_posts()) {
                    echo "<div class='snippets-grid'>";
                    while ($query->have_posts()) {
                        $query->the_post();
                        $post_content = trim(get_post_field('post_content', get_the_ID()));
                        $needs_expand = strlen($post_content) > 300; // Adjust length threshold if needed
                        
                        echo "<div class='snippet" . ($needs_expand ? " expandable" : "") . "' data-expanded='false' onclick='copySnippet(event, this)'>";
                        
                        // Snippet title with edit button
                        echo "<div class='snippet-title'>" . get_the_title();
                        if (current_user_can('edit_posts')) {
                            $edit_link = get_edit_post_link(get_the_ID());
                            echo " <a href='{$edit_link}' class='edit-snippet' title='Edit Snippet'>&#9998;</a>"; // Pencil icon ✎
                        }
                        echo "</div>"; // Close snippet-title
                        
                        // Snippet Content
                        echo "<pre class='snippet-content'><code onclick='copySnippet(event, this)'>" . esc_html($post_content ?: 'No content available.') . "</code></pre>";

                        // Expand button if needed
                        if ($needs_expand) {
                            echo "<span class='expand-btn' onclick='toggleExpand(event, this)'>&#9662;</span>";
                        }

                        echo "</div>"; // Close snippet
                    }
                    echo "</div>"; // Close snippets-grid
                } else {
                    echo "<p class='no-snippets'>No snippets found in this category.</p>";
                }
                wp_reset_postdata();
                echo "</div>"; // Close snippet-category
            }
        } else {
            echo "<p class='no-categories'>No snippet categories found.</p>";
        }
        ?>
    </div>
</main>

<script>
function copySnippet(event, element) {
    // Prevent copy on expand button click
    if (event.target.classList.contains('expand-btn')) return;

    const snippet = element.closest(".snippet");
    const codeBlock = snippet.querySelector("code");
    
    const textArea = document.createElement("textarea");
    textArea.value = codeBlock.textContent;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand("copy");
    document.body.removeChild(textArea);
    
    snippet.classList.add("copied");
    setTimeout(() => snippet.classList.remove("copied"), 1000);
}

function toggleExpand(event, button) {
    event.stopPropagation(); // Prevent triggering copySnippet
    const snippet = button.closest(".expandable");
    snippet.classList.toggle("expanded");
    button.innerHTML = snippet.classList.contains("expanded") ? "&#9652;" : "&#9662;";
}
</script>


<?php get_footer(); ?>
