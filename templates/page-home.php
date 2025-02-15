<?php get_header(); ?>

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

<!-- Paragraph Section + Image (2 Columns) -->
<?php echo spacer(3.5); ?>

<section class="container">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h2 class="heading text-3xl">About Us</h2>
            <p class="paragraph text-base">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel risus nec
                leo interdum tincidunt. Sed eu magna ac eros malesuada consectetur.</p>
            <p class="paragraph text-base">Vivamus sit amet magna ac magna vehicula feugiat non sed lectus. Suspendisse
                vitae libero nec ligula fermentum facilisis et sit amet dolor.</p>
                <?php echo primary_button('Learn More', 'https://example.com', ' ', 'btn-primary btn-lg text-light rounded-pill px-4', true); ?>
        </div>
        <div class="col-md-6 mt-5 mt-md-0">
        <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" alt="About us image" class="img-fluid rounded">
        </div>
    </div>
</section>

<?php echo spacer(3.5); ?>

<!-- Call to Action -->
<section class="cta-section text-center text-white bg-primary">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto py-5">
                <h2 class="heading text-3xl">Ready to Start?</h2>
                <?php echo spacer(.5); ?>
                <p class="paragraph text-base">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod
                    tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud
                    exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in
                    reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint
                    occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                <?php echo spacer(.5); ?>
                <?php echo primary_button('Learn More', 'https://example.com', ' ', 'btn-light btn-lg text-primary rounded-pill px-4', true); ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
