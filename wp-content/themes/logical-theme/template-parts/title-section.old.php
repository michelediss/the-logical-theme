<?php
$defaults = [
    'title' => get_the_title(),
    'image_url' => has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : ltc_upload_url('/2024/11/default-hero.jpg'),
    'image_alt' => '',
    'section_class' => 'mt-4',
    'wrapper_class' => 'container px-3 px-lg-0',
    'media_class' => 'position-relative rounded-5 overflow-hidden shadow-lg',
    'height' => '55vh',
    'height_desktop' => '55vh',
    'height_tablet' => '45vh',
    'height_mobile' => '35vh',
    'section_style' => '',
    'overlay_style' => 'background: rgba(115, 0, 0, .7);',
    'inner_class' => 'position-relative d-flex justify-content-center align-items-center h-100',
    'content_class' => 'py-4',
    'title_id' => '',
    'title_class' => 'w-100 text-center heading text-xl text-md-2xl text-light',
    'subtitle' => '',
    'subtitle_class' => 'paragraph text-sm text-light mb-3 text-uppercase',
];
$args = wp_parse_args($args ?? [], $defaults);
$height_desktop = $args['height_desktop'] ? $args['height_desktop'] : $args['height'];
$height_tablet = $args['height_tablet'] ? $args['height_tablet'] : $args['height'];
$height_mobile = $args['height_mobile'] ? $args['height_mobile'] : $args['height'];
$media_style = $args['section_style'] ? $args['section_style'] : '';
$height_vars = [];
if ($height_desktop) {
    $height_vars[] = '--title-section-height-desktop: ' . $height_desktop . ';';
}
if ($height_tablet) {
    $height_vars[] = '--title-section-height-tablet: ' . $height_tablet . ';';
}
if ($height_mobile) {
    $height_vars[] = '--title-section-height-mobile: ' . $height_mobile . ';';
}
if ($height_vars) {
    $media_style = trim($media_style . ' ' . implode(' ', $height_vars));
}
$title_id = $args['title_id'] ? $args['title_id'] : '';
$image_alt = $args['image_alt'] ? $args['image_alt'] : $args['title'];
$media_class = trim($args['media_class'] . ' title-section-media');
?>
<section class="<?php echo esc_attr($args['section_class']); ?>"<?php echo $title_id ? ' aria-labelledby="' . esc_attr($title_id) . '"' : ''; ?>>
    <div class="<?php echo esc_attr($args['wrapper_class']); ?>">
        <div class="<?php echo esc_attr($media_class); ?>"<?php echo $media_style ? ' style="' . esc_attr($media_style) . '"' : ''; ?>>
            <img src="<?php echo esc_url($args['image_url']); ?>" alt="<?php echo esc_attr($image_alt); ?>" class="w-100 h-100 object-fit-cover">
            <div class="overlay position-absolute top-0 start-0 w-100 h-100" style="<?php echo esc_attr($args['overlay_style']); ?>">
                <div class="<?php echo esc_attr($args['inner_class']); ?>">
                    <div class="<?php echo esc_attr($args['content_class']); ?>">
                        <?php if ($args['subtitle']) : ?>
                            <p class="<?php echo esc_attr($args['subtitle_class']); ?>"><?php echo esc_html($args['subtitle']); ?></p>
                        <?php endif; ?>
                        <h1<?php echo $title_id ? ' id="' . esc_attr($title_id) . '"' : ''; ?> class="<?php echo esc_attr($args['title_class']); ?>">
                            <?php echo esc_html($args['title']); ?>
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
