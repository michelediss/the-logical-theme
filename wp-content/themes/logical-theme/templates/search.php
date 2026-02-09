<?php get_header(); ?>

<section id="search-results" class="py-5" aria-labelledby="search-results-title">
    <div class="container px-3 px-lg-0">
        <div class="row g-4 g-lg-5 justify-content-center">
            <div class="col-12 col-lg-10 col-xl-8">
                <h1 id="search-results-title" class="heading text-lg text-secondary mb-3 text-start">
                    Risultati per: <?php echo esc_html(get_search_query()); ?>
                </h1>
            </div>
        </div>

        <?php if (have_posts()) : ?>
            <div class="row g-4 g-lg-5 justify-content-center">
                <div class="col-12 col-lg-10 col-xl-8">
                    <div class="row row-cols-2 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php while (have_posts()) : the_post(); ?>
                            <article <?php post_class('col'); ?>>
                                <div class="h-100">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <a href="<?php the_permalink(); ?>" class="d-block ratio ratio-16x9">
                                            <?php the_post_thumbnail('large', array('class' => 'w-100 h-100 object-fit-cover rounded-4 shadow')); ?>
                                        </a>
                                    <?php endif; ?>
                                    <p class="paragraph text-xs text-uppercase text-gray mt-3 mb-1">
                                        <?php echo esc_html(get_the_date()); ?>
                                    </p>
                                    <h2 class="heading text-base mt-1 mb-0">
                                        <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                                            <?php the_title(); ?>
                                        </a>
                                    </h2>
                                </div>
                            </article>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <?php get_template_part('template-parts/pagination'); ?>
        <?php else : ?>
            <div class="row g-4 g-lg-5 justify-content-center">
                <div class="col-12 col-lg-10 col-xl-8">
                    <p class="paragraph text-lg text-dark mb-0">
                        Nessun risultato trovato. Prova con un'altra ricerca.
                    </p>
                    <?php get_search_form(); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
