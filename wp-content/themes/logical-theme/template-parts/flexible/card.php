<?php
require_once get_template_directory() . '/template-parts/flexible/helpers.php';

$row = get_query_var('logical_flexible_row');
$row = is_array($row) ? $row : [];

$cards = isset($row['card_repeater']) && is_array($row['card_repeater']) ? $row['card_repeater'] : [];
if (empty($cards)) {
    return;
}

$section_class = logical_flexible_section_class($row, 'white');
$container_class = logical_flexible_container_class($row, 'container');
?>
<section class="card-section py-5 <?php echo esc_attr($section_class); ?>">
    <div class="<?php echo esc_attr($container_class); ?>">
        <div class="row g-4">
            <?php foreach ($cards as $card): ?>
                <?php
                if (!is_array($card)) {
                    continue;
                }

                $title = isset($card['title']) ? (string) $card['title'] : '';
                $text = isset($card['text']) ? (string) $card['text'] : '';
                $mode = strtolower((string) ($card['options'] ?? 'icon'));
                $icon = isset($card['icon']) ? (string) $card['icon'] : '';
                $image = isset($card['image']) && is_array($card['image']) ? $card['image'] : [];
                $image_url = isset($image['url']) ? (string) $image['url'] : '';
                $image_alt = isset($image['alt']) ? (string) $image['alt'] : '';
                $cta = isset($card['cta']) && is_array($card['cta']) ? logical_flexible_cta($card['cta']) : ['text' => '', 'url' => '', 'target' => ''];
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <article class="h-100 border rounded-4 p-4">
                        <?php if ($mode === 'image' && $image_url !== ''): ?>
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($image_alt ?: $title); ?>" class="mb-3 rounded-3 w-100 h-auto">
                        <?php elseif ($icon !== ''): ?>
                            <i class="bi bi-<?php echo esc_attr($icon); ?> heading text-xl d-inline-block mb-3"></i>
                        <?php endif; ?>

                        <?php if ($title !== ''): ?>
                            <h3 class="heading mb-2"><?php echo esc_html($title); ?></h3>
                        <?php endif; ?>

                        <?php if ($text !== ''): ?>
                            <p class="paragraph mb-3"><?php echo esc_html($text); ?></p>
                        <?php endif; ?>

                        <?php if ($cta['text'] !== '' && $cta['url'] !== ''): ?>
                            <?php
                            $target_attr = $cta['target'] !== '' ? ' target="' . esc_attr($cta['target']) . '"' : '';
                            $rel_attr = $cta['target'] === '_blank' ? ' rel="noopener noreferrer"' : '';
                            ?>
                            <a href="<?php echo esc_url($cta['url']); ?>" class="button heading text-uppercase rounded-pill text-decoration-none px-3 py-1"<?php echo $target_attr . $rel_attr; ?>>
                                <?php echo esc_html($cta['text']); ?>
                            </a>
                        <?php endif; ?>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

