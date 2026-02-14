<?php
/**
 * Hero Mono layout template part.
 *
 * @var array $args
 */

$row = [];
if (isset($args['row']) && is_array($args['row'])) {
    $row = $args['row'];
} else {
    $query_row = get_query_var('logical_hero_row');
    if (is_array($query_row)) {
        $row = $query_row;
    }
}

$container_class = 'container-fluid px-0';
if (isset($args['container_class']) && is_string($args['container_class'])) {
    $container_class = $args['container_class'];
} else {
    $query_container_class = get_query_var('logical_hero_container_class');
    if (is_string($query_container_class) && $query_container_class !== '') {
        $container_class = $query_container_class;
    }
}

$section_class = 'white';
if (isset($args['section_class']) && is_string($args['section_class']) && $args['section_class'] !== '') {
    $section_class = $args['section_class'];
} else {
    $query_section_class = get_query_var('logical_hero_section_class');
    if (is_string($query_section_class) && $query_section_class !== '') {
        $section_class = $query_section_class;
    }
}

$hero_mode = (string) get_query_var('logical_hero_mode');
$hero_mode = $hero_mode !== '' ? $hero_mode : 'static';
$hero_post = get_query_var('logical_hero_post');
$hero_post = $hero_post instanceof WP_Post ? $hero_post : null;

$title = '';
$text = '';
$image = [];

$cta_text = '';
$cta_url = '';
$cta_target = '';

if ($hero_mode === 'post-based' && $hero_post instanceof WP_Post) {
    $title = get_the_title($hero_post);
    $text = get_the_excerpt($hero_post);

    $thumb_id = get_post_thumbnail_id($hero_post->ID);
    $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : '';
    $image = [
        'url' => $thumb_url ?: get_template_directory_uri() . '/assets/images/placeholder-hero.jpg',
        'alt' => $thumb_id ? get_post_meta($thumb_id, '_wp_attachment_image_alt', true) : '',
    ];

    $cta_text = esc_html__('Leggi di più', 'logical-theme-child');
    $cta_url = get_permalink($hero_post);
} else {
    $title = isset($row['title']) ? (string) $row['title'] : '';
    $text = isset($row['text']) ? (string) $row['text'] : '';
    $image = isset($row['image']) && is_array($row['image']) ? $row['image'] : [];

    if (isset($row['cta']) && is_array($row['cta'])) {
        $cta = $row['cta'];
        if (isset($cta['url']) && is_array($cta['url'])) {
            $cta_link = $cta['url'];
            $cta_text = isset($cta_link['title']) ? (string) $cta_link['title'] : '';
            $cta_url = isset($cta_link['url']) ? (string) $cta_link['url'] : '';
            $cta_target = isset($cta_link['target']) ? (string) $cta_link['target'] : '';
        } else {
            $cta_text = isset($cta['text']) ? (string) $cta['text'] : (isset($cta['title']) ? (string) $cta['title'] : '');
            $cta_url = isset($cta['url']) ? (string) $cta['url'] : (isset($cta['link']) ? (string) $cta['link'] : '');
        }
    }

    if ($cta_text === '' && isset($row['url']) && is_array($row['url'])) {
        $cta_link = $row['url'];
        $cta_text = isset($cta_link['title']) ? (string) $cta_link['title'] : '';
        $cta_url = isset($cta_link['url']) ? (string) $cta_link['url'] : '';
        $cta_target = isset($cta_link['target']) ? (string) $cta_link['target'] : '';
    }

    if ($cta_text === '' && isset($row['button_text'])) {
        $cta_text = (string) $row['button_text'];
    }
    if ($cta_url === '' && isset($row['button_url'])) {
        $cta_url = (string) $row['button_url'];
    }
}

$image_url = isset($image['url']) ? (string) $image['url'] : '';
$image_alt = isset($image['alt']) ? (string) $image['alt'] : '';

$target_attr = $cta_target ? ' target="' . esc_attr($cta_target) . '"' : '';
$rel_attr = $cta_target === '_blank' ? ' rel="noopener noreferrer"' : '';
$rounded_class = strpos($container_class, 'container-fluid') !== false ? '' : ' rounded-4';
?>
<section class="hero hero-mono py-5 <?php echo esc_attr($section_class); ?>">
    <div class="<?php echo esc_attr($container_class); ?>">
        <div class="hero-mono__media position-relative overflow-hidden hero-mono-vh<?php echo esc_attr($rounded_class); ?>">
            <?php if ($image_url): ?>
                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt ?: $title); ?>" class="hero-mono__image w-100 h-100">
                <div class="hero-mono__overlay position-absolute top-0 start-0 w-100 h-100"></div>
            <?php endif; ?>

            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center">
                <div class="container px-4 px-lg-5">
                    <div class="row">
                        <div class="col-12 col-lg-8">
                            <?php if ($title !== ''): ?>
                                <h1 class="hero-mono__title heading text-2xl mb-3"><?php echo esc_html($title); ?></h1>
                            <?php endif; ?>

                            <?php if ($text !== ''): ?>
                                <p class="hero-mono__text paragraph text-base mb-4"><?php echo esc_html($text); ?></p>
                            <?php endif; ?>

                            <?php if ($cta_text !== '' && $cta_url !== ''): ?>
                                <a href="<?php echo esc_url($cta_url); ?>" class="hero-mono__cta button heading text-sm text-uppercase rounded-pill text-decoration-none px-4 py-2"<?php echo $target_attr . $rel_attr; ?>>
                                    <?php echo esc_html($cta_text); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
