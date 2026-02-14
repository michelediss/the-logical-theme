<?php
require_once get_template_directory() . '/template-parts/flexible/helpers.php';

$row = get_query_var('logical_flexible_row');
$row = is_array($row) ? $row : [];

$images = isset($row['gallery']) && is_array($row['gallery']) ? $row['gallery'] : [];
if (empty($images)) {
    return;
}

$mode = strtolower((string) ($row['options'] ?? 'carousel'));
$mode = in_array($mode, ['carousel', 'grid'], true) ? $mode : 'carousel';

$section_class = logical_flexible_section_class($row, 'white');
$container_class = logical_flexible_container_class($row, 'container');

$id = 'acf-gallery-' . wp_generate_uuid4();
?>
<section class="gallery-section py-5 <?php echo esc_attr($section_class); ?>">
    <div class="<?php echo esc_attr($container_class); ?>">
        <?php if ($mode === 'grid'): ?>
            <div class="row g-3">
                <?php foreach ($images as $image): ?>
                    <?php
                    if (!is_array($image)) {
                        continue;
                    }
                    $url = isset($image['url']) ? (string) $image['url'] : '';
                    $alt = isset($image['alt']) ? (string) $image['alt'] : '';
                    if ($url === '') {
                        continue;
                    }
                    ?>
                    <div class="col-6 col-lg-3">
                        <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" class="w-100 h-100 object-fit-cover rounded-4">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div id="<?php echo esc_attr($id); ?>" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner rounded-4 overflow-hidden">
                    <?php foreach ($images as $index => $image): ?>
                        <?php
                        if (!is_array($image)) {
                            continue;
                        }
                        $url = isset($image['url']) ? (string) $image['url'] : '';
                        $alt = isset($image['alt']) ? (string) $image['alt'] : '';
                        if ($url === '') {
                            continue;
                        }
                        ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <img src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>" class="d-block w-100" style="max-height: 65vh; object-fit: cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (count($images) > 1): ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo esc_attr($id); ?>" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo esc_html__('Precedente', 'logical-theme-child'); ?></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#<?php echo esc_attr($id); ?>" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo esc_html__('Successivo', 'logical-theme-child'); ?></span>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

