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
        // Get all snippet categories ordered by post count
        $categories = get_terms(array(
            'taxonomy'   => 'snippet_category',
            'hide_empty' => false,
            'orderby'    => 'count',
            'order'      => 'DESC',
        ));

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
                        $post_id = get_the_ID();
                        $post_content = trim(get_post_field('post_content', $post_id));
                        $post_title = get_the_title();

                        echo "<div class='snippet' data-id='{$post_id}' data-expanded='false'>";
                        
                        // Snippet Title with inline edit
                        echo "<div class='snippet-title'>";
                        echo "<span class='title-display' data-id='{$post_id}'>{$post_title}</span>";

                        if (current_user_can('edit_posts')) {
                            echo " <span class='edit-snippet' onclick='toggleEdit(this)' data-id='{$post_id}' title='Edit Snippet'>&#9998;</span>";
                        }
                        echo "</div>"; 

                        // Snippet Content (Editable)
                        echo "<pre class='snippet-content' data-id='{$post_id}' onclick='copySnippet(this)'><code>" . esc_html($post_content ?: 'No content available.') . "</code></pre>";

                        echo "</div>"; // Close snippet
                    }
                    echo "</div>"; 
                } else {
                    echo "<p class='no-snippets'>No snippets found in this category.</p>";
                }
                wp_reset_postdata();
                echo "</div>"; 
            }
        } else {
            echo "<p class='no-categories'>No snippet categories found.</p>";
        }
        ?>
    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".snippet").forEach(checkOverflow);
});

function copySnippet(element) {
    if (element.closest(".snippet").querySelector(".edit-textarea, .edit-title")) return;

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

function toggleEdit(icon) {
    let snippetContainer = icon.closest('.snippet');
    let postId = icon.getAttribute("data-id");
    let codeBlock = snippetContainer.querySelector("code");
    let titleBlock = snippetContainer.querySelector(".title-display");

    let isEditing = icon.getAttribute("data-editing") === "true";

    if (!isEditing) {
        // Convert title to input field
        let titleInput = document.createElement("input");
        titleInput.value = titleBlock.textContent;
        titleInput.classList.add("edit-title");
        titleInput.setAttribute("data-id", postId);
        titleBlock.replaceWith(titleInput);

        // Convert content to textarea
        let textarea = document.createElement("textarea");
        textarea.value = codeBlock.textContent;
        textarea.classList.add("edit-textarea");
        textarea.setAttribute("data-id", postId);
        textarea.style.width = "100%";
        textarea.style.height = codeBlock.clientHeight + "px";

        codeBlock.replaceWith(textarea);
        icon.innerHTML = "💾"; 
        icon.setAttribute("data-editing", "true");

        textarea.addEventListener("input", () => checkOverflow(snippetContainer));

    } else {
        let updatedTitle = snippetContainer.querySelector(".edit-title").value;
        let updatedContent = snippetContainer.querySelector(".edit-textarea").value;

        let formData = new FormData();
        formData.append('action', 'update_snippet');
        formData.append('post_id', postId);
        formData.append('title', updatedTitle);
        formData.append('content', updatedContent);

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let newTitle = document.createElement("span");
                newTitle.textContent = updatedTitle;
                newTitle.classList.add("title-display");
                newTitle.setAttribute("data-id", postId);

                let newCodeBlock = document.createElement("code");
                newCodeBlock.textContent = updatedContent;
                newCodeBlock.setAttribute("onclick", "copySnippet(this)");

                snippetContainer.querySelector(".edit-title").replaceWith(newTitle);
                snippetContainer.querySelector(".edit-textarea").replaceWith(newCodeBlock);
                icon.innerHTML = "✎";
                icon.setAttribute("data-editing", "false");

                checkOverflow(snippetContainer);

                // Show updated permalink
                alert("Updated! New permalink: " + data.new_permalink);
            } else {
                alert("Error saving snippet!");
            }
        });
    }
}

function checkOverflow(snippetContainer) {
    let snippetContent = snippetContainer.querySelector(".snippet-content") || snippetContainer.querySelector(".edit-textarea");
    if (!snippetContent) return;

    let expandBtn = snippetContainer.querySelector(".expand-btn");

    let contentHeight = snippetContent.scrollHeight;
    let containerWidth = snippetContainer.clientWidth;

    if (contentHeight > containerWidth * 1.2) {
        snippetContainer.classList.add("expandable");

        if (!expandBtn) {
            expandBtn = document.createElement("span");
            expandBtn.classList.add("expand-btn");
            expandBtn.innerHTML = "&#9662;";
            expandBtn.onclick = function () {
                toggleExpand(expandBtn);
            };
            snippetContainer.appendChild(expandBtn);
        }
    } else {
        snippetContainer.classList.remove("expandable");
        if (expandBtn) {
            expandBtn.remove();
        }
    }
}

function toggleExpand(button) {
    const snippet = button.closest(".expandable");
    snippet.classList.toggle("expanded");
    button.innerHTML = snippet.classList.contains("expanded") ? "&#9652;" : "&#9662;";
}
</script>

<?php get_footer(); ?>
