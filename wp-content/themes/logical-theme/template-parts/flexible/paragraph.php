<?php
require_once get_template_directory() . '/template-parts/flexible/helpers.php';

$row = get_query_var('logical_flexible_row');
$row = is_array($row) ? $row : [];

$pretitle = isset($row['pretitle']) ? (string) $row['pretitle'] : '';
$title = isset($row['title']) ? (string) $row['title'] : '';
$text = isset($row['text']) ? (string) $row['text'] : '';
$image = isset($row['image']) && is_array($row['image']) ? $row['image'] : [];
$options = isset($row['options']) && is_array($row['options']) ? $row['options'] : [];

$layout = strtolower((string) ($options['layout'] ?? 'left'));
if (!in_array($layout, ['left', 'right', 'center'], true)) {
    $layout = 'left';
}

$show_separator = strtolower((string) ($options['separator'] ?? 'off')) === 'on';
$cta = isset($row['cta']) && is_array($row['cta']) ? logical_flexible_cta($row['cta']) : ['text' => '', 'url' => '', 'target' => ''];

$image_url = isset($image['url']) ? (string) $image['url'] : '';
$image_alt = isset($image['alt']) ? (string) $image['alt'] : '';

$section_class = logical_flexible_section_class($row, 'white');
$container_class = logical_flexible_container_class($row, 'container');

$text_col_class = 'col-12 col-lg-6';
$image_col_class = 'col-12 col-lg-6';

if ($layout === 'center') {
    $text_col_class = 'col-12 col-lg-8 mx-auto text-center';
}
?>
<section class="paragraph-section py-5 <?php echo esc_attr($section_class); ?>">
    <div class="<?php echo esc_attr($container_class); ?>">
        <div class="row g-4 align-items-center">
            <?php if ($layout === 'right' && $image_url): ?>
                <div class="<?php echo esc_attr($image_col_class); ?>">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt ?: $title); ?>" class="w-100 h-auto rounded-4">
                </div>
            <?php endif; ?>

                <div class="<?php echo esc_attr($text_col_class); ?>">
                <?php if ($pretitle !== ''): ?>
                    <p class="pretitle heading text-sm text-uppercase mb-2"><?php echo esc_html($pretitle); ?></p>
                <?php endif; ?>

                <?php if ($title !== ''): ?>
                    <h2 class="title heading text-xl mb-3"><?php echo esc_html($title); ?></h2>
                <?php endif; ?>

                <?php if ($show_separator): ?>
                    <div class="divider rounded-pill mb-3 <?php echo $layout === 'center' ? 'mx-auto' : ''; ?>"></div>
                <?php endif; ?>

                <?php if ($text !== ''): ?>
                    <div class="text paragraph text-base mb-4"><?php echo wp_kses_post($text); ?></div>
                <?php endif; ?>

                <?php if ($cta['text'] !== '' && $cta['url'] !== ''): ?>
                    <?php
                    $target_attr = $cta['target'] !== '' ? ' target="' . esc_attr($cta['target']) . '"' : '';
                    $rel_attr = $cta['target'] === '_blank' ? ' rel="noopener noreferrer"' : '';
                    ?>
                    <a href="<?php echo esc_url($cta['url']); ?>" class="button heading text-uppercase rounded-pill text-decoration-none px-4 py-2"<?php echo $target_attr . $rel_attr; ?>>
                        <?php echo esc_html($cta['text']); ?>
                    </a>
                <?php endif; ?>
            </div>

            <?php if ($layout === 'left' && $image_url): ?>
                <div class="<?php echo esc_attr($image_col_class); ?>">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt ?: $title); ?>" class="w-100 h-auto rounded-4">
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
