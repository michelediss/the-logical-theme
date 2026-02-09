<?php
global $wp_query;

$pagination_args = wp_parse_args($args ?? array(), array(
    'font_size_class'        => 'heading text-base',
    'text_color_class'       => 'text-gray',
    'bg_color_class'         => 'bg-white',
    'border_color_class'     => 'border-gray',
    'border_width_class'     => 'border-2',
    'hover_text_color_class' => 'hover-text-white',
    'hover_bg_color_class'   => 'hover-bg-primary',
    'hover_border_color_class' => 'hover-border-primary',
    'roundness_class'        => 'rounded-pill',
    'item_padding_class'     => 'px-0',
    'item_size'              => '2.25rem',
    'current_text_color_class' => 'text-white',
    'current_bg_color_class'   => 'bg-primary',
    'current_border_color_class' => 'border-primary',
));

$pagination_links = paginate_links(array(
    'mid_size'  => 1,
    'end_size'  => 1,
    'type'      => 'array',
    'current'   => max(1, get_query_var('paged')),
    'total'     => $wp_query->max_num_pages,
    'prev_text' => '←',
    'next_text' => '→',
));

if (empty($pagination_links)) {
    return;
}

$page_link_classes = esc_attr(trim(
    'page-link d-inline-flex align-items-center justify-content-center ' .
    $pagination_args['item_padding_class'] . ' ' .
    $pagination_args['font_size_class'] . ' ' .
    $pagination_args['text_color_class'] . ' ' .
    $pagination_args['bg_color_class'] . ' ' .
    $pagination_args['border_color_class'] . ' ' .
    $pagination_args['border_width_class'] . ' ' .
    $pagination_args['hover_text_color_class'] . ' ' .
    $pagination_args['hover_bg_color_class'] . ' ' .
    $pagination_args['hover_border_color_class'] . ' ' .
    $pagination_args['roundness_class']
));

$page_link_current_classes = esc_attr(trim(
    'page-link d-inline-flex align-items-center justify-content-center ' .
    $pagination_args['item_padding_class'] . ' ' .
    $pagination_args['font_size_class'] . ' ' .
    $pagination_args['current_text_color_class'] . ' ' .
    $pagination_args['current_bg_color_class'] . ' ' .
    $pagination_args['current_border_color_class'] . ' ' .
    $pagination_args['border_width_class'] . ' ' .
    $pagination_args['roundness_class']
));

$page_link_style = '';
if (!empty($pagination_args['item_size'])) {
    $page_link_style = ' style="min-width:' . esc_attr($pagination_args['item_size']) . ';height:' . esc_attr($pagination_args['item_size']) . ';"';
}
?>
<div class="row mt-5">
    <div class="col d-flex justify-content-center">
        <nav class="d-flex justify-content-center" aria-label="<?php echo esc_attr__('Paginazione', 'logical-theme-child'); ?>">
            <ul class="pagination mb-0 gap-2">
                <?php foreach ($pagination_links as $link) : ?>
                    <?php
                    $is_current = strpos($link, 'current') !== false;
                    $is_dots = strpos($link, 'dots') !== false;
                    $is_prev = strpos($link, 'prev') !== false;
                    $is_next = strpos($link, 'next') !== false;
                    $label = trim(wp_strip_all_tags($link));
                    $content = ($is_prev || $is_next) ? ($is_prev ? '←' : '→') : $label;
                    $aria_label = '';

                    if ($is_prev) {
                        $aria_label = esc_attr__('Precedente', 'logical-theme-child');
                    } elseif ($is_next) {
                        $aria_label = esc_attr__('Successivo', 'logical-theme-child');
                    }
                    ?>

                    <?php if ($is_dots) : ?>
                        <li class="page-item disabled" aria-hidden="true">
                            <span class="<?php echo $page_link_classes; ?>"<?php echo $page_link_style; ?>>…</span>
                        </li>
                        <?php continue; ?>
                    <?php endif; ?>

                    <?php if (preg_match('/href=["\']([^"\']+)["\']/', $link, $matches)) : ?>
                        <li class="page-item<?php echo $is_current ? ' active' : ''; ?>">
                            <a class="<?php echo $is_current ? $page_link_current_classes : $page_link_classes; ?>" href="<?php echo esc_url($matches[1]); ?>"<?php echo $page_link_style; ?><?php echo $is_current ? ' aria-current="page"' : ''; ?><?php echo $aria_label ? ' aria-label="' . $aria_label . '"' : ''; ?>>
                                <?php echo esc_html($content); ?>
                            </a>
                        </li>
                    <?php else : ?>
                        <li class="page-item active" aria-current="page">
                            <span class="<?php echo $page_link_current_classes; ?>"<?php echo $page_link_style; ?>><?php echo esc_html($content); ?></span>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>
</div>
