<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('logical_theme_custom_block_types')) {
    function logical_theme_custom_block_types()
    {
        return array('layout', 'row', 'column', 'pretitle', 'title', 'text', 'image', 'button');
    }
}

if (!function_exists('logical_theme_get_custom_block_names')) {
    function logical_theme_get_custom_block_names()
    {
        return array(
            'logical-theme/layout',
            'logical-theme/row',
            'logical-theme/column',
            'logical-theme/pretitle',
            'logical-theme/title',
            'logical-theme/text',
            'logical-theme/image',
            'logical-theme/button',
        );
    }
}

if (!function_exists('logical_theme_register_custom_content_blocks')) {
    function logical_theme_register_custom_content_blocks()
    {
        register_block_type('logical-theme/layout', array(
            'api_version' => 2,
            'render_callback' => 'logical_theme_render_layout_block',
            'attributes' => array(
                'layoutVersion' => array(
                    'type' => 'string',
                    'default' => '4.0',
                ),
            ),
            'supports' => array(
                'html' => false,
                'color' => array(
                    'text' => true,
                    'background' => true,
                    'link' => true,
                ),
                'typography' => array(
                    'fontSize' => true,
                    'lineHeight' => true,
                    'fontFamily' => true,
                ),
                'spacing' => array(
                    'margin' => true,
                    'padding' => true,
                ),
            ),
        ));

        register_block_type('logical-theme/row', array(
            'api_version' => 2,
            'render_callback' => 'logical_theme_render_row_block',
            'attributes' => array(
                'rowId' => array('type' => 'string', 'default' => ''),
                'container' => array('type' => 'string', 'default' => 'default'),
                'gap' => array('type' => 'string', 'default' => 'md'),
                'alignY' => array('type' => 'string', 'default' => 'stretch'),
                'backgroundColor' => array('type' => 'string', 'default' => ''),
            ),
            'supports' => array(
                'html' => false,
            ),
        ));

        register_block_type('logical-theme/column', array(
            'api_version' => 2,
            'render_callback' => 'logical_theme_render_column_block',
            'attributes' => array(
                'columnId' => array('type' => 'string', 'default' => ''),
                'desktop' => array('type' => 'number', 'default' => 12),
                'tablet' => array('type' => 'number', 'default' => 12),
                'mobile' => array('type' => 'number', 'default' => 12),
                'alignY' => array('type' => 'string', 'default' => 'stretch'),
            ),
            'supports' => array(
                'html' => false,
            ),
        ));

        register_block_type('logical-theme/pretitle', array(
            'api_version' => 2,
            'render_callback' => 'logical_theme_render_pretitle_block',
            'ancestor' => array('logical-theme/column'),
            'attributes' => array(
                'itemId' => array('type' => 'string', 'default' => ''),
                'text' => array('type' => 'string', 'default' => ''),
            ),
            'supports' => array(
                'html' => false,
                'inserter' => true,
            ),
        ));

        register_block_type('logical-theme/title', array(
            'api_version' => 2,
            'render_callback' => 'logical_theme_render_title_block',
            'ancestor' => array('logical-theme/column'),
            'attributes' => array(
                'itemId' => array('type' => 'string', 'default' => ''),
                'text' => array('type' => 'string', 'default' => ''),
                'level' => array('type' => 'string', 'default' => 'h2'),
            ),
            'supports' => array(
                'html' => false,
                'inserter' => true,
            ),
        ));

        register_block_type('logical-theme/text', array(
            'api_version' => 2,
            'render_callback' => 'logical_theme_render_text_block',
            'ancestor' => array('logical-theme/column'),
            'attributes' => array(
                'itemId' => array('type' => 'string', 'default' => ''),
                'text' => array('type' => 'string', 'default' => ''),
            ),
            'supports' => array(
                'html' => false,
                'inserter' => true,
            ),
        ));

        register_block_type('logical-theme/image', array(
            'api_version' => 2,
            'render_callback' => 'logical_theme_render_image_block',
            'ancestor' => array('logical-theme/column'),
            'attributes' => array(
                'itemId' => array('type' => 'string', 'default' => ''),
                'id' => array('type' => 'number', 'default' => 0),
                'src' => array('type' => 'string', 'default' => ''),
                'alt' => array('type' => 'string', 'default' => ''),
            ),
            'supports' => array(
                'html' => false,
                'inserter' => true,
            ),
        ));

        register_block_type('logical-theme/button', array(
            'api_version' => 2,
            'render_callback' => 'logical_theme_render_button_block',
            'ancestor' => array('logical-theme/column'),
            'attributes' => array(
                'itemId' => array('type' => 'string', 'default' => ''),
                'label' => array('type' => 'string', 'default' => ''),
                'url' => array('type' => 'string', 'default' => ''),
                'variant' => array('type' => 'string', 'default' => 'primary'),
                'target' => array('type' => 'string', 'default' => '_self'),
            ),
            'supports' => array(
                'html' => false,
                'inserter' => true,
            ),
        ));
    }
}
add_action('init', 'logical_theme_register_custom_content_blocks');

if (!function_exists('logical_theme_render_layout_block')) {
    function logical_theme_render_layout_block($attributes, $content)
    {
        $wrapper_attributes = get_block_wrapper_attributes(array('class' => 'logical-layout-block'));
        return sprintf('<div %1$s>%2$s</div>', $wrapper_attributes, (string) $content);
    }
}

if (!function_exists('logical_theme_render_row_block')) {
    function logical_theme_render_row_block($attributes, $content)
    {
        $container = isset($attributes['container']) ? sanitize_key((string) $attributes['container']) : 'default';
        if (!in_array($container, array('default', 'wide', 'full'), true)) {
            $container = 'default';
        }

        $gap = isset($attributes['gap']) ? sanitize_key((string) $attributes['gap']) : 'md';
        $gap_map = array('none' => '0', 'sm' => '0.75rem', 'md' => '1.25rem', 'lg' => '2rem');
        if (!isset($gap_map[$gap])) {
            $gap = 'md';
        }

        $align = isset($attributes['alignY']) ? sanitize_key((string) $attributes['alignY']) : 'stretch';
        if (!in_array($align, array('start', 'center', 'end', 'stretch'), true)) {
            $align = 'stretch';
        }

        $surface = function_exists('logical_theme_sanitize_surface_color_slug')
            ? logical_theme_sanitize_surface_color_slug(isset($attributes['backgroundColor']) ? $attributes['backgroundColor'] : '')
            : '';

        $container_class = 'container';
        if ($container === 'wide') {
            $container_class = 'container logical-layout-container-wide';
        } elseif ($container === 'full') {
            $container_class = 'container logical-layout-container-full';
        }

        $section_classes = array('logical-layout-section', 'logical-theme-color-surface');
        if ($surface !== '') {
            $section_classes[] = 'has-surface-color';
            $section_classes[] = 'has-' . sanitize_html_class($surface) . '-background-color';
        }

        $row_style = sprintf(
            '--logical-row-gap:%1$s;--logical-row-align:%2$s;',
            esc_attr($gap_map[$gap]),
            esc_attr($align)
        );
        $row_data = $surface !== '' ? sprintf(' data-surface-color="%s"', esc_attr($surface)) : '';

        return sprintf(
            '<section class="%1$s"%2$s><div class="%3$s"><div class="logical-layout-row" style="%4$s">%5$s</div></div></section>',
            esc_attr(implode(' ', $section_classes)),
            $row_data,
            esc_attr($container_class),
            esc_attr($row_style),
            (string) $content
        );
    }
}

if (!function_exists('logical_theme_render_column_block')) {
    function logical_theme_render_column_block($attributes, $content)
    {
        $desktop = max(1, min(12, isset($attributes['desktop']) ? (int) $attributes['desktop'] : 12));
        $tablet = max(1, min(12, isset($attributes['tablet']) ? (int) $attributes['tablet'] : 12));
        $mobile = max(1, min(12, isset($attributes['mobile']) ? (int) $attributes['mobile'] : 12));
        $align = isset($attributes['alignY']) ? sanitize_key((string) $attributes['alignY']) : 'stretch';
        if (!in_array($align, array('start', 'center', 'end', 'stretch'), true)) {
            $align = 'stretch';
        }

        $style = sprintf(
            '--logical-col-mobile:%1$d;--logical-col-tablet:%2$d;--logical-col-desktop:%3$d;--logical-col-align:%4$s;',
            $mobile,
            $tablet,
            $desktop,
            esc_attr($align)
        );

        return sprintf('<div class="logical-layout-col" style="%1$s">%2$s</div>', esc_attr($style), (string) $content);
    }
}

if (!function_exists('logical_theme_render_pretitle_block')) {
    function logical_theme_render_pretitle_block($attributes)
    {
        $text = isset($attributes['text']) ? sanitize_text_field((string) $attributes['text']) : '';
        if ($text === '') {
            return '';
        }
        return sprintf('<span class="logical-layout-pretitle text-sm font-semibold uppercase logical-color-eyebrow">%s</span>', esc_html($text));
    }
}

if (!function_exists('logical_theme_render_title_block')) {
    function logical_theme_render_title_block($attributes)
    {
        $text = isset($attributes['text']) ? sanitize_text_field((string) $attributes['text']) : '';
        if ($text === '') {
            return '';
        }
        $level = isset($attributes['level']) ? sanitize_key((string) $attributes['level']) : 'h2';
        if (!in_array($level, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'), true)) {
            $level = 'h2';
        }
        return sprintf('<%1$s class="logical-layout-title text-3xl font-bold logical-color-heading">%2$s</%1$s>', $level, esc_html($text));
    }
}

if (!function_exists('logical_theme_render_text_block')) {
    function logical_theme_render_text_block($attributes)
    {
        $text = isset($attributes['text']) ? (string) $attributes['text'] : '';
        if (trim($text) === '') {
            return '';
        }
        return sprintf('<div class="logical-layout-text logical-color-body">%s</div>', wp_kses_post($text));
    }
}

if (!function_exists('logical_theme_render_image_block')) {
    function logical_theme_render_image_block($attributes)
    {
        $src = isset($attributes['src']) ? esc_url((string) $attributes['src']) : '';
        if ($src === '') {
            return '';
        }
        $alt = isset($attributes['alt']) ? sanitize_text_field((string) $attributes['alt']) : '';
        return sprintf('<img src="%1$s" alt="%2$s" class="logical-layout-image h-auto w-full rounded-lg object-cover" />', $src, esc_attr($alt));
    }
}

if (!function_exists('logical_theme_render_button_block')) {
    function logical_theme_render_button_block($attributes)
    {
        $label = isset($attributes['label']) ? sanitize_text_field((string) $attributes['label']) : '';
        $url = isset($attributes['url']) ? esc_url((string) $attributes['url']) : '';
        if ($label === '' || $url === '') {
            return '';
        }

        $variant = isset($attributes['variant']) ? sanitize_key((string) $attributes['variant']) : 'primary';
        $variant_classes = array(
            'primary' => 'border-primary bg-primary text-light',
            'secondary' => 'border-secondary bg-secondary text-light',
            'outline' => 'border-primary bg-transparent text-primary',
            'link' => 'border-transparent bg-transparent p-0 text-primary underline underline-offset-4',
        );
        if (!isset($variant_classes[$variant])) {
            $variant = 'primary';
        }

        $target = isset($attributes['target']) && (string) $attributes['target'] === '_blank' ? '_blank' : '_self';
        $target_attr = $target === '_blank' ? ' target="_blank"' : '';
        $rel_attr = $target === '_blank' ? ' rel="noopener noreferrer"' : '';
        $class_attr = sprintf('logical-layout-button inline-flex items-center justify-center rounded-lg border px-5 py-3 text-sm font-semibold %s', $variant_classes[$variant]);

        return sprintf(
            '<a href="%1$s" class="%2$s"%3$s%4$s>%5$s</a>',
            $url,
            esc_attr($class_attr),
            $target_attr,
            $rel_attr,
            esc_html($label)
        );
    }
}

if (!function_exists('logical_theme_filter_allowed_block_types')) {
    function logical_theme_filter_allowed_block_types($allowed_block_types, $editor_context)
    {
        $allowed = logical_theme_get_custom_block_names();

        $registry = WP_Block_Type_Registry::get_instance();
        foreach ($registry->get_all_registered() as $name => $definition) {
            if (strpos($name, 'core/embed') === 0 || strpos($name, 'core-embed/') === 0) {
                $allowed[] = $name;
            }
        }

        return array_values(array_unique($allowed));
    }
}
add_filter('allowed_block_types_all', 'logical_theme_filter_allowed_block_types', 10, 2);
