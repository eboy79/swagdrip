<?php get_header(); ?>

<div class="content-container">
    <main class="blog-feed">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <article class="post">
                <h2 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                
                <?php if (has_post_thumbnail()) : ?>
                    <a href="<?php the_permalink(); ?>">
                        <div class="post-thumbnail">
                            <?php the_post_thumbnail('large'); ?>
                        </div>
                    </a>
                <?php endif; ?>
                
                <div class="post-content">
                    <?php the_content(); ?>
                </div>
            </article>
        <?php endwhile; else : ?>
            <p>No posts found.</p>
        <?php endif; ?>
    </main>
</div>

<?php get_footer(); ?>