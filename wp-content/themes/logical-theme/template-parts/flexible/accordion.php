<?php
require_once get_template_directory() . '/template-parts/flexible/helpers.php';

$row = get_query_var('logical_flexible_row');
$row = is_array($row) ? $row : [];

$title = isset($row['title']) ? (string) $row['title'] : '';
$text = isset($row['text']) ? (string) $row['text'] : '';
$elements = isset($row['accordion_element']) && is_array($row['accordion_element']) ? $row['accordion_element'] : [];

if ($title === '' && $text === '' && empty($elements)) {
    return;
}

$section_class = logical_flexible_section_class($row, 'white');
$container_class = logical_flexible_container_class($row, 'container');
$accordion_id = 'acf-accordion-' . wp_generate_uuid4();
?>
<section class="accordion-section py-5 <?php echo esc_attr($section_class); ?>">
    <div class="<?php echo esc_attr($container_class); ?>">
        <?php if ($title !== ''): ?>
            <h2 class="heading mb-3"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>

        <?php if ($text !== ''): ?>
            <p class="paragraph mb-4"><?php echo esc_html($text); ?></p>
        <?php endif; ?>

        <?php if (!empty($elements)): ?>
            <div class="accordion" id="<?php echo esc_attr($accordion_id); ?>">
                <?php foreach ($elements as $index => $element): ?>
                    <?php
                    if (!is_array($element)) {
                        continue;
                    }

                    $item_title = isset($element['title']) ? (string) $element['title'] : '';
                    $item_text = isset($element['text']) ? (string) $element['text'] : '';
                    $heading_id = $accordion_id . '-heading-' . $index;
                    $collapse_id = $accordion_id . '-collapse-' . $index;
                    ?>
                    <div class="accordion-item">
                        <h3 class="accordion-header" id="<?php echo esc_attr($heading_id); ?>">
                            <button class="accordion-button <?php echo $index > 0 ? 'collapsed' : ''; ?> heading" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo esc_attr($collapse_id); ?>" aria-expanded="<?php echo $index === 0 ? 'true' : 'false'; ?>" aria-controls="<?php echo esc_attr($collapse_id); ?>">
                                <?php echo esc_html($item_title); ?>
                            </button>
                        </h3>
                        <div id="<?php echo esc_attr($collapse_id); ?>" class="accordion-collapse collapse <?php echo $index === 0 ? 'show' : ''; ?>" aria-labelledby="<?php echo esc_attr($heading_id); ?>" data-bs-parent="#<?php echo esc_attr($accordion_id); ?>">
                            <div class="accordion-body paragraph">
                                <?php echo nl2br(esc_html($item_text)); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

