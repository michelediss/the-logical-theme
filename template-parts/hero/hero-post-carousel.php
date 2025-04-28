<!-- Hero Slider Full Screen with Featured Posts -->
<?php
// Query per ottenere i post in evidenza
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => 5, // Numero massimo di slide
    'post_status'    => 'publish',
    'meta_key'       => 'post_sticky',
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => array(
        array(
            'key'     => '_sticky',
            'compare' => 'EXISTS'
        )
    )
);
$featured_posts = new WP_Query($args);
?>

<div id="heroSlider" class="carousel slide hero-slider overflow-hidden" data-bs-ride="carousel">
    <div class="carousel-inner h-100">
        <?php if ($featured_posts->have_posts()): $first = true; ?>
            <?php while ($featured_posts->have_posts()): $featured_posts->the_post(); ?>
                <?php $image_url = get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>
                
                <div class="carousel-item position-relative h-100 <?php echo $first ? 'active' : ''; ?>">
                    <img src="<?php echo esc_url($image_url); ?>" class="d-block w-100 h-100 object-fit-cover" alt="<?php the_title(); ?>" loading="lazy">
                    <!-- Overlay -->
                    <div class="overlay position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0, 0, 0, 0.65);"></div>
                    <div class="carousel-caption d-flex flex-column align-items-center justify-content-center h-100 text-white text-center position-absolute top-50 start-50 translate-middle w-50">
                        <h1 class="heading text-3xl"><?php the_title(); ?></h1>
                        <?php echo spacer(0.5); ?>
                        <p class="paragraph text-base d-none d-md-block"><?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?></p>
                        <?php echo spacer(0.5); ?>
                        <?php echo primary_button('Read More', get_permalink(), '', 'btn-primary btn-lg text-light rounded-pill px-4', true); ?>
                    </div>
                </div>
                <?php $first = false; ?>
            <?php endwhile; wp_reset_postdata(); ?>
        <?php else: ?>
            <p class="text-center text-white">No featured posts found.</p>
        <?php endif; ?>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>