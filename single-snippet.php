<?php get_header(); ?>

<main>
    <article>
        <h1><?php the_title(); ?></h1>
        <pre><code><?php echo esc_html(get_the_content()); ?></code></pre>
    </article>
</main>

<?php get_footer(); ?>
