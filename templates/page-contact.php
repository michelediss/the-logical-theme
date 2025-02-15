<?php get_header(); ?>

<?php get_header(); ?>

<!-- Sezione 1: Intestazione con immagine a tutto schermo e overlay -->
<section class="position-relative d-flex align-items-center justify-content-center" style="height: 60vh;">
  <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" alt="Hero Image" class="w-100 h-100 object-fit-cover">
  <div class="overlay position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0, 0, 0, 0.65);">
  <div class="container position-relative d-flex justify-content-start align-items-end h-100 pb-4">
    <h1 class="heading text-5xl text-light"><?php the_title(); ?></h1>
  </div>
</section>

<!-- Paragraph Section + Image (2 Columns) -->
<?php echo spacer(3.5); ?>

<section class="container">
    <div class="row align-items-center">
        <div class="col-md-8 mx-auto">
            <h2 class="heading text-3xl">Contact Us</h2>
            <p class="paragraph text-base">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel risus nec
                leo interdum tincidunt. Sed eu magna ac eros malesuada consectetur.</p>
                <?php echo do_shortcode('[contact-form-7 id="c944891" title="Contact form"]'); ?>

        </div>
    </div>
</section>

<?php echo spacer(3.5); ?>





<?php get_footer(); ?>
