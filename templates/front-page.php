<?php get_header(); ?>

<?php get_template_part( 'template-parts/hero-home/hero-carousel-post' ); ?>


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
<section class="cta-section text-center text-white">
    <div class="container">
        <div class="row">
            <div class="col-10 px-4 mx-auto py-5 rounded-4 bg-primary d-flex justify-content-between align-items-center">
                <h2 class="heading text-3xl text-white d-inline">Lorem ipsum dolo sit amet</h2>
                <?php echo primary_button('Learn More', 'https://example.com', ' ', 'bg-primary-white btn text-primary rounded-3 px-4 d-inline paragraph text-lg', true); ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
