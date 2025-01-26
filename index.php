<?php get_header(); ?>

<div class="content-container">
    <main class="blog-feed">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article class="post">
                <h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <p class="post-description"><?php the_excerpt(); ?></p>
            </article>
        <?php endwhile; else : ?>
            <p>No posts found.</p>
        <?php endif; ?>
    </main>
</div>

<?php get_footer(); ?>
