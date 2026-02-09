<?php
$related_posts = array();
$recent_posts = get_posts(array(
    'post_type'           => 'post',
    'posts_per_page'      => 9,
    'post__not_in'        => array(get_the_ID()),
    'ignore_sticky_posts' => true,
));

if (!empty($recent_posts)) {
    shuffle($recent_posts);
    $related_posts = array_slice($recent_posts, 0, 3);
}
?>

<?php if (!empty($related_posts)) : ?>
    <section id="related-articles" class="pb-5" aria-labelledby="related-articles-title">
        <div class="container px-3 px-lg-0">
            <div class="row g-4 g-lg-5 justify-content-center">
                <div class="col-12 col-lg-10 col-xl-8">
                    <h2 id="related-articles-title" class="heading text-lg text-secondary mb-3 text-start">Articoli correlati</h2>
                </div>
            </div>

            <div class="row g-4 g-lg-5 justify-content-center">
                <div class="col-12 col-lg-10 col-xl-8">
                    <div class="row row-cols-2 row-cols-md-2 row-cols-lg-3 g-4">
                        <?php foreach ($related_posts as $index => $post) : setup_postdata($post); ?>
                            <?php $related_item_classes = $index === 2 ? 'col d-none d-lg-block' : 'col'; ?>
                            <article <?php post_class($related_item_classes); ?>>
                                <div class="h-100">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <a href="<?php the_permalink(); ?>" class="d-block ratio ratio-16x9">
                                            <?php the_post_thumbnail('large', array('class' => 'w-100 h-100 object-fit-cover rounded-4 shadow')); ?>
                                        </a>
                                    <?php endif; ?>
                                    <p class="paragraph text-xs text-uppercase text-gray mt-3 mb-1">
                                        <?php echo esc_html(get_the_date('', $post)); ?>
                                    </p>
                                    <h3 class="heading text-sm mt-1 mb-0">
                                        <a href="<?php the_permalink(); ?>" class="text-decoration-none">
                                            <?php the_title(); ?>
                                        </a>
                                    </h3>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php wp_reset_postdata(); ?>
<?php endif; ?>
