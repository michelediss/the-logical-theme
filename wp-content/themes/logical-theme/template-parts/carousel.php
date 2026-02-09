<?php
// Carousel based on latest blog posts.

$defaults = [
    'section_class' => 'container hero-slider overflow-hidden mt-4 px-3 px-lg-0',
    'section_style' => '',
    'height' => '55vh',
    'height_desktop' => '55vh',
    'height_tablet' => '45vh',
    'height_mobile' => '35vh',
];
$args = wp_parse_args($args ?? [], $defaults);
$height_desktop = $args['height_desktop'] ? $args['height_desktop'] : $args['height'];
$height_tablet = $args['height_tablet'] ? $args['height_tablet'] : $args['height'];
$height_mobile = $args['height_mobile'] ? $args['height_mobile'] : $args['height'];
$section_style = $args['section_style'] ? $args['section_style'] : '';
$height_vars = [];
if ($height_desktop) {
    $height_vars[] = '--carousel-height-desktop: ' . $height_desktop . ';';
}
if ($height_tablet) {
    $height_vars[] = '--carousel-height-tablet: ' . $height_tablet . ';';
}
if ($height_mobile) {
    $height_vars[] = '--carousel-height-mobile: ' . $height_mobile . ';';
}
if ($height_vars) {
    $section_style = trim($section_style . ' ' . implode(' ', $height_vars));
}

$slides = get_posts([
    'post_type'           => 'post',
    'posts_per_page'      => 3,
    'post_status'         => 'publish',
    'orderby'             => 'date',
    'order'               => 'DESC',
    'ignore_sticky_posts' => true,
    'suppress_filters'    => true,
]);

if (empty($slides)) {
    return;
}
?>
<div class="<?php echo esc_attr($args['section_class']); ?>" <?php echo $section_style ? ' style="' . esc_attr($section_style) . '"' : ''; ?>>
    <div class="swiper h-100 rounded-4" data-hero-swiper>
        <div class="swiper-wrapper h-100">
            <?php foreach ($slides as $index => $post): ?>
                <?php setup_postdata($post); ?>
                <div class="swiper-slide h-100 position-relative">
                    <?php
                    $image_url = get_the_post_thumbnail_url($post, 'large');
                    if (!$image_url) {
                        $image_url = get_template_directory_uri() . '/assets/images/placeholder-hero.jpg';
                    }
                    $image_alt = get_post_meta(get_post_thumbnail_id($post), '_wp_attachment_image_alt', true);
                    ?>
                    <img src="<?php echo esc_url($image_url); ?>" class="d-block w-100 h-100 object-fit-cover" alt="<?php echo esc_attr($image_alt ?: get_the_title($post)); ?>">
                    <div class="hero-carousel-overlay position-absolute top-0 start-0 w-100 h-100"></div>


                    <div class="hero-carousel-caption position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center text-white text-start">
                                <div style="max-width: 70%" class="d-flex flex-column align-items-start justify-content-center">
                                    <p class="hero-carousel-date paragraph text-uppercase text-sm mb-2 text-start"><?php echo esc_html(get_the_date('', $post)); ?></p>
                                    <h1 style="line-height: 1.15" class="heading text-lg text-md-xl text-lg-2xl hero-carousel-title mb-4"><?php echo esc_html(get_the_title($post)); ?></h1>
                                    <?php
                                    echo logical_primary_button(
                                        esc_html__('Leggi di più', 'logical-theme-child'),
                                        get_permalink($post),
                                        'hero-carousel-button',
                                        sprintf(
                                            /* translators: %s: post title */
                                            esc_html__('Leggi di più: %s', 'logical-theme-child'),
                                            get_the_title($post)
                                        ),
                                        false,
                                        'px-4 py-1',
                                        'text-sm',
                                        'text-white',
                                        'bg-primary',
                                        'border-primary',
                                        'hover-text-white',
                                        'hover-bg-transparent',
                                        'hover-border-primary'
                                    );
                                    ?>
                                </div>
                    </div>


                </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($slides) > 1): ?>
            <div class="swiper-button-prev" aria-label="<?php echo esc_attr__('Precedente', 'logical-theme-child'); ?>"></div>
            <div class="swiper-button-next" aria-label="<?php echo esc_attr__('Successivo', 'logical-theme-child'); ?>"></div>
            <div class="swiper-pagination"></div>
        <?php endif; ?>
    </div>
</div>
<?php
wp_reset_postdata();
?>
