<?php get_header(); ?>
<div class="content-container">
    <main class="blog-feed" id="infinite-scroll-container">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <a href="<?php the_permalink(); ?>" class="post-link no-underline">
                <article class="post" data-post-id="<?php echo get_the_ID(); ?>">
                    <h2 class="post-title"> <?php the_title(); ?> </h2>
                    <?php get_template_part('templates/components/post-date'); ?>
                    <p class="post-description"> <?php the_excerpt(); ?> </p>
                    <div class="parametric-brick-holdr">
                        <div class="parametric-brick">
                            <div class="parametric-brick__face"></div>
                            <div class="parametric-brick__side parametric-brick__side--left"></div>
                            <div class="parametric-brick__side parametric-brick__side--top"></div>
                            <div class="parametric-brick__content">READ MORE</div>
                            <div class="parametric-brick__corners"></div>
                            <div class="parametric-brick__corners-bottom"></div>
                        </div>
                    </div>
                </article>
            </a>
        <?php endwhile; endif; ?>
    </main>
    <div id="loading-spinner" class="hidden">
        <div class="spinner"></div>
    </div>
</div>
<?php get_footer(); ?>