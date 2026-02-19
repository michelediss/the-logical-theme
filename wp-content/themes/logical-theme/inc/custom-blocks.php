<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('logical_theme_custom_block_types')) {
    function logical_theme_custom_block_types()
    {
        return array('paragraph', 'layout');
    }
}

if (!function_exists('logical_theme_get_custom_block_names')) {
    function logical_theme_get_custom_block_names()
    {
        return array('logical-theme/paragraph', 'logical-theme/layout');
    }
}

if (!function_exists('logical_theme_get_content_json_payload')) {
    function logical_theme_get_content_json_payload($post_id)
    {
        $post_id = (int) $post_id;
        if ($post_id <= 0) {
            return null;
        }

        $raw_json = get_post_meta($post_id, LOGICAL_THEME_CONTENT_JSON_META_KEY, true);
        if (!is_string($raw_json) || trim($raw_json) === '') {
            return null;
        }

        $decoded = json_decode($raw_json, true);
        if (!is_array($decoded) || !isset($decoded['version']) || !is_string($decoded['version'])) {
            return null;
        }

        return $decoded;
    }
}

if (!function_exists('logical_theme_get_content_json_sections')) {
    function logical_theme_get_content_json_sections($post_id)
    {
        $payload = logical_theme_get_content_json_payload($post_id);
        if (!is_array($payload)) {
            return array();
        }

        if (!isset($payload['version']) || (string) $payload['version'] !== '2.0') {
            return array();
        }

        return isset($payload['sections']) && is_array($payload['sections']) ? $payload['sections'] : array();
    }
}

if (!function_exists('logical_theme_get_content_json_layout_rows')) {
    function logical_theme_get_content_json_layout_rows($post_id)
    {
        $payload = logical_theme_get_content_json_payload($post_id);
        if (!is_array($payload)) {
            return array();
        }

        if (!isset($payload['version']) || (string) $payload['version'] !== '3.0') {
            return array();
        }

        return isset($payload['layout']) && is_array($payload['layout']) ? $payload['layout'] : array();
    }
}

if (!function_exists('logical_theme_find_content_json_section')) {
    function logical_theme_find_content_json_section($post_id, $section_id, $section_type)
    {
        if (!is_string($section_id) || $section_id === '' || !is_string($section_type) || $section_type === '') {
            return null;
        }

        $sections = logical_theme_get_content_json_sections($post_id);
        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            if (!isset($section['id']) || (string) $section['id'] !== $section_id) {
                continue;
            }

            if (!isset($section['type']) || (string) $section['type'] !== $section_type) {
                continue;
            }

            return $section;
        }

        return null;
    }
}

if (!function_exists('logical_theme_find_gap_value')) {
    function logical_theme_find_gap_value($gap)
    {
        $gap = sanitize_key((string) $gap);
        $map = array(
            'none' => '0',
            'sm' => '0.75rem',
            'md' => '1.25rem',
            'lg' => '2rem',
        );

        return isset($map[$gap]) ? $map[$gap] : $map['md'];
    }
}

if (!function_exists('logical_theme_find_align_items_value')) {
    function logical_theme_find_align_items_value($align)
    {
        $align = sanitize_key((string) $align);
        $map = array(
            'start' => 'start',
            'center' => 'center',
            'end' => 'end',
            'stretch' => 'stretch',
        );

        return isset($map[$align]) ? $map[$align] : $map['stretch'];
    }
}

if (!function_exists('logical_theme_render_layout_item_paragraph')) {
    function logical_theme_render_layout_item_paragraph($item, $surface_color)
    {
        $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : array();
        $settings = isset($item['settings']) && is_array($item['settings']) ? $item['settings'] : array();
        $paragraph_surface = isset($settings['backgroundColor']) ? logical_theme_sanitize_surface_color_slug($settings['backgroundColor']) : $surface_color;

        return logical_theme_custom_render_paragraph($data, $settings, $paragraph_surface);
    }
}

if (!function_exists('logical_theme_render_layout_item_embed')) {
    function logical_theme_render_layout_item_embed($item)
    {
        $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : array();
        $url = isset($data['url']) ? esc_url((string) $data['url']) : '';
        if ($url === '') {
            return '';
        }

        $html = wp_oembed_get($url);
        if (!is_string($html) || $html === '') {
            $html = sprintf('<a href="%1$s" target="_blank" rel="noopener noreferrer">%1$s</a>', $url);
        }

        return sprintf('<div class="logical-layout-embed">%s</div>', $html);
    }
}

if (!function_exists('logical_theme_render_layout_item')) {
    function logical_theme_render_layout_item($item, $surface_color)
    {
        if (!is_array($item) || !isset($item['type'])) {
            return '';
        }

        $type = sanitize_key((string) $item['type']);
        if ($type === 'paragraph') {
            return logical_theme_render_layout_item_paragraph($item, $surface_color);
        }

        if ($type === 'embed') {
            return logical_theme_render_layout_item_embed($item);
        }

        return '';
    }
}

if (!function_exists('logical_theme_custom_render_paragraph')) {
    function logical_theme_custom_render_paragraph($data, $settings = array(), $surface_color = '')
    {
        $pretitle = isset($data['pretitle']) ? (string) $data['pretitle'] : '';
        $title = isset($data['title']) ? (string) $data['title'] : '';
        $text = isset($data['text']) ? (string) $data['text'] : '';
        $image = isset($data['image']) && is_array($data['image']) ? $data['image'] : array();
        $image_src = isset($image['src']) ? (string) $image['src'] : '';
        $image_alt = isset($image['alt']) ? (string) $image['alt'] : '';

        $variant = isset($settings['variant']) ? trim((string) $settings['variant']) : '';
        $default_variant = '1';
        if (function_exists('logical_theme_get_block_spec')) {
            $spec = logical_theme_get_block_spec('paragraph');
            if (is_array($spec) && isset($spec['defaultVariant']) && is_string($spec['defaultVariant']) && trim($spec['defaultVariant']) !== '') {
                $default_variant = trim($spec['defaultVariant']);
            }
        }
        if ($variant === '') {
            $variant = $default_variant;
        }

        if (function_exists('logical_theme_sanitize_surface_color_slug')) {
            $surface_color = logical_theme_sanitize_surface_color_slug($surface_color);
        } else {
            $surface_color = sanitize_key((string) $surface_color);
        }

        $section_classes = array('w-full', 'py-12', 'wp-block-logical-theme-paragraph', 'logical-theme-color-surface');
        if ($surface_color !== '') {
            $section_classes[] = 'has-surface-color';
            $section_classes[] = 'has-' . sanitize_html_class($surface_color) . '-background-color';
        }
        $surface_class_attr = implode(' ', $section_classes);
        $surface_data_attr = $surface_color !== ''
            ? sprintf(' data-surface-color="%s"', esc_attr($surface_color))
            : '';

        $template_file = trailingslashit(get_stylesheet_directory()) . 'templates/blocks/paragraph/variants/' . sanitize_file_name($variant) . '.php';
        if (!file_exists($template_file)) {
            $template_file = trailingslashit(get_stylesheet_directory()) . 'templates/blocks/paragraph/variants/' . sanitize_file_name($default_variant) . '.php';
        }
        if (!file_exists($template_file)) {
            return '';
        }

        ob_start();
        include $template_file;
        $output = ob_get_clean();

        return is_string($output) ? $output : '';
    }
}

if (!function_exists('logical_theme_custom_render_layout')) {
    function logical_theme_custom_render_layout($rows)
    {
        if (!is_array($rows) || count($rows) === 0) {
            return '';
        }

        $template_file = trailingslashit(get_stylesheet_directory()) . 'templates/blocks/layout/layout.php';
        if (!file_exists($template_file)) {
            return '';
        }
        ob_start();
        include $template_file;
        $output = ob_get_clean();

        return is_string($output) ? $output : '';
    }
}

if (!function_exists('logical_theme_render_paragraph_block')) {
    function logical_theme_render_paragraph_block($attributes, $content, $block)
    {
        $section_id = isset($attributes['sectionId']) ? (string) $attributes['sectionId'] : '';
        $section_type = isset($attributes['sectionType']) ? sanitize_key((string) $attributes['sectionType']) : '';
        if ($section_id === '' || $section_type !== 'paragraph') {
            return '';
        }

        $resolved_data = isset($attributes['data']) && is_array($attributes['data']) ? $attributes['data'] : array();
        $resolved_settings = isset($attributes['settings']) && is_array($attributes['settings']) ? $attributes['settings'] : array();

        $post_id = 0;
        if (is_object($block) && isset($block->context['postId'])) {
            $post_id = (int) $block->context['postId'];
        } elseif (get_the_ID()) {
            $post_id = (int) get_the_ID();
        }

        $is_editor_preview = defined('REST_REQUEST') && REST_REQUEST;
        if (!$is_editor_preview) {
            $meta_section = logical_theme_find_content_json_section($post_id, $section_id, $section_type);
            if (is_array($meta_section)) {
                $resolved_data = isset($meta_section['data']) && is_array($meta_section['data']) ? $meta_section['data'] : array();
                $resolved_settings = isset($meta_section['settings']) && is_array($meta_section['settings']) ? $meta_section['settings'] : array();
            }
        }

        $surface_color = '';
        if (function_exists('logical_theme_sanitize_surface_color_slug')) {
            $surface_color = logical_theme_sanitize_surface_color_slug(isset($resolved_settings['backgroundColor']) ? $resolved_settings['backgroundColor'] : '');
        }

        return logical_theme_custom_render_paragraph($resolved_data, $resolved_settings, $surface_color);
    }
}

if (!function_exists('logical_theme_render_layout_block')) {
    function logical_theme_render_layout_block($attributes, $content, $block)
    {
        $rows = isset($attributes['layout']) && is_array($attributes['layout']) ? $attributes['layout'] : array();

        $post_id = 0;
        if (is_object($block) && isset($block->context['postId'])) {
            $post_id = (int) $block->context['postId'];
        } elseif (get_the_ID()) {
            $post_id = (int) get_the_ID();
        }

        $is_editor_preview = defined('REST_REQUEST') && REST_REQUEST;
        if (!$is_editor_preview) {
            $meta_rows = logical_theme_get_content_json_layout_rows($post_id);
            if (is_array($meta_rows) && !empty($meta_rows)) {
                $rows = $meta_rows;
            }
        }

        return logical_theme_custom_render_layout($rows);
    }
}

if (!function_exists('logical_theme_register_custom_content_blocks')) {
    function logical_theme_register_custom_content_blocks()
    {
        register_block_type('logical-theme/paragraph', array(
            'api_version' => 2,
            'render_callback' => 'logical_theme_render_paragraph_block',
            'attributes' => array(
                'sectionId' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'sectionType' => array(
                    'type' => 'string',
                    'default' => 'paragraph',
                ),
                'data' => array(
                    'type' => 'object',
                    'default' => array(),
                ),
                'settings' => array(
                    'type' => 'object',
                    'default' => array(),
                ),
            ),
            'uses_context' => array('postId'),
            'supports' => array(
                'html' => false,
            ),
        ));

        register_block_type('logical-theme/layout', array(
            'api_version' => 2,
            'render_callback' => 'logical_theme_render_layout_block',
            'attributes' => array(
                'layout' => array(
                    'type' => 'array',
                    'default' => array(),
                ),
            ),
            'uses_context' => array('postId'),
            'supports' => array(
                'html' => false,
            ),
        ));
    }
}
add_action('init', 'logical_theme_register_custom_content_blocks');

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
