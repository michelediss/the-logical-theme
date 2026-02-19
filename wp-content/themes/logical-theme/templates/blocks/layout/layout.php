<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($rows) || !is_array($rows) || count($rows) === 0) {
    return;
}
?>
<section class="logical-layout-block">
<?php foreach ($rows as $row) :
    if (!is_array($row)) {
        continue;
    }

    $row_settings = isset($row['settings']) && is_array($row['settings']) ? $row['settings'] : array();
    $row_container = isset($row_settings['container']) ? sanitize_key((string) $row_settings['container']) : 'default';
    $row_gap = function_exists('logical_theme_find_gap_value')
        ? logical_theme_find_gap_value(isset($row_settings['gap']) ? $row_settings['gap'] : 'md')
        : '1.25rem';
    $row_align = function_exists('logical_theme_find_align_items_value')
        ? logical_theme_find_align_items_value(isset($row_settings['alignY']) ? $row_settings['alignY'] : 'stretch')
        : 'stretch';
    $row_surface = function_exists('logical_theme_sanitize_surface_color_slug')
        ? logical_theme_sanitize_surface_color_slug(isset($row_settings['backgroundColor']) ? $row_settings['backgroundColor'] : '')
        : '';

    $container_class = 'container';
    if ($row_container === 'wide') {
        $container_class = 'container logical-layout-container-wide';
    } elseif ($row_container === 'full') {
        $container_class = 'logical-layout-container-full';
    }

    $row_classes = array('logical-layout-row', 'logical-theme-color-surface');
    if ($row_surface !== '') {
        $row_classes[] = 'has-surface-color';
        $row_classes[] = 'has-' . sanitize_html_class($row_surface) . '-background-color';
    }

    $row_style = sprintf('--logical-row-gap:%s;--logical-row-align:%s;', esc_attr($row_gap), esc_attr($row_align));
    $row_data_attr = $row_surface !== '' ? sprintf(' data-surface-color="%s"', esc_attr($row_surface)) : '';
    ?>
    <div class="<?php echo esc_attr($container_class); ?>">
      <div class="<?php echo esc_attr(implode(' ', $row_classes)); ?>" style="<?php echo esc_attr($row_style); ?>"<?php echo $row_data_attr; ?>>
        <?php
        $columns = isset($row['columns']) && is_array($row['columns']) ? $row['columns'] : array();
        foreach ($columns as $column) :
            if (!is_array($column)) {
                continue;
            }

            $column_settings = isset($column['settings']) && is_array($column['settings']) ? $column['settings'] : array();
            $desktop = max(1, min(12, isset($column_settings['desktop']) ? (int) $column_settings['desktop'] : 12));
            $tablet = max(1, min(12, isset($column_settings['tablet']) ? (int) $column_settings['tablet'] : 12));
            $mobile = max(1, min(12, isset($column_settings['mobile']) ? (int) $column_settings['mobile'] : 12));
            $column_align = function_exists('logical_theme_find_align_items_value')
                ? logical_theme_find_align_items_value(isset($column_settings['alignY']) ? $column_settings['alignY'] : 'stretch')
                : 'stretch';

            $column_style = sprintf(
                '--logical-col-mobile:%1$d;--logical-col-tablet:%2$d;--logical-col-desktop:%3$d;--logical-col-align:%4$s;',
                $mobile,
                $tablet,
                $desktop,
                esc_attr($column_align)
            );
            ?>
            <div class="logical-layout-col" style="<?php echo esc_attr($column_style); ?>">
              <?php
              $items = isset($column['items']) && is_array($column['items']) ? $column['items'] : array();
              foreach ($items as $item) {
                  if (!is_array($item) || !isset($item['type'])) {
                      continue;
                  }

                  $item_type = sanitize_key((string) $item['type']);
                  if ($item_type === 'paragraph' && function_exists('logical_theme_render_layout_item_paragraph')) {
                      echo logical_theme_render_layout_item_paragraph($item, $row_surface);
                      continue;
                  }

                  if ($item_type === 'embed' && function_exists('logical_theme_render_layout_item_embed')) {
                      echo logical_theme_render_layout_item_embed($item);
                  }
              }
              ?>
            </div>
        <?php endforeach; ?>
      </div>
    </div>
<?php endforeach; ?>
</section>
