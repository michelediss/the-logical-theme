<?php get_header(); ?>

<?php get_template_part('template-parts/hero/hero-post-carousel') ?>

<?php echo spacer(3.5); ?>

<?php
$page_id = get_option('page_on_front');

// Recupera i campi About
$about = get_field('about', $page_id);
$about_title = is_array($about) ? ($about['title'] ?? '') : '';
$about_subtitle = is_array($about) ? ($about['subtitle'] ?? '') : '';
$about_text = is_array($about) ? ($about['text'] ?? '') : '';
$about_image = is_array($about) ? ($about['image']['url'] ?? '') : '';

// Recupera i campi CTA
$cta = get_field('cta', $page_id);
$cta_title = is_array($cta) ? ($cta['title'] ?? '') : '';
$cta_subtitle = is_array($cta) ? ($cta['subtitle'] ?? '') : '';
$cta_text = is_array($cta) ? ($cta['text'] ?? '') : '';
?>

<!-- About Section -->
<?php if ($about_title || $about_subtitle || $about_text || $about_image): ?>
    <section class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <?php if ($about_subtitle): ?>
                    <p class="paragraph text-base"><?php echo esc_html($about_subtitle); ?></p>
                <?php endif; ?>

                <?php if ($about_title): ?>
                    <h2 class="heading text-3xl"><?php echo esc_html($about_title); ?></h2>
                <?php endif; ?>

                <?php echo divider(4, 'bg-secondary rounded-pill my-4', 15); ?>

                <?php if ($about_text): ?>
                    <?php echo wp_kses_post($about_text); ?>
                <?php endif; ?>

                <?php echo primary_button('Learn More', 'https://example.com', ' ', 'bg-primary btn-lg text-primary-white rounded-pill px-4 py-2', true); ?>
            </div>

            <div class="col-md-6 mt-5 mt-md-0">
                <?php if ($about_image): ?>
                    <img src="<?php echo esc_url($about_image); ?>" alt="About us image" class="img-fluid rounded">
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php echo spacer(3.5); ?>

<!-- Call to Action Section -->
<?php if ($cta_title || $cta_subtitle || $cta_text): ?>
    <section class="cta-section text-center text-white bg-primary">
        <div class="container">
            <div class="row">
                <div class="col-md-8 mx-auto py-5">
                    <?php if ($cta_title): ?>
                        <h2 class="heading text-3xl"><?php echo esc_html($cta_title); ?></h2>
                    <?php endif; ?>

                    <?php echo spacer(0.5); ?>

                    <?php if ($cta_text): ?>
                        <?php echo wp_kses_post($cta_text); ?>
                    <?php endif; ?>

                    <?php echo spacer(0.5); ?>

                    <?php echo primary_button('Learn More', 'https://example.com', ' ', 'btn-light btn-lg text-primary rounded-pill px-4', true); ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>



<?php get_footer(); ?>