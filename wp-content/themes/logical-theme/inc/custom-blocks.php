<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('logical_theme_custom_block_types')) {
    function logical_theme_custom_block_types()
    {
        return array('paragraph');
    }
}

if (!function_exists('logical_theme_get_custom_block_names')) {
    function logical_theme_get_custom_block_names()
    {
        return array('logical-theme/paragraph');
    }
}

if (!function_exists('logical_theme_get_content_json_sections')) {
    function logical_theme_get_content_json_sections($post_id)
    {
        $post_id = (int) $post_id;
        if ($post_id <= 0) {
            return array();
        }

        $raw_json = get_post_meta($post_id, LOGICAL_THEME_CONTENT_JSON_META_KEY, true);
        if (!is_string($raw_json) || trim($raw_json) === '') {
            return array();
        }

        $decoded = json_decode($raw_json, true);
        if (!is_array($decoded) || !isset($decoded['sections']) || !is_array($decoded['sections'])) {
            return array();
        }

        return $decoded['sections'];
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

if (!function_exists('logical_theme_render_custom_content_block')) {
    function logical_theme_render_custom_content_block($attributes, $content, $block)
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

        $html = logical_theme_custom_render_paragraph($resolved_data, $resolved_settings, $surface_color);
        if ($html === '') {
            return '';
        }

        return $html;
    }
}

if (!function_exists('logical_theme_register_custom_content_blocks')) {
    function logical_theme_register_custom_content_blocks()
    {
        register_block_type('logical-theme/paragraph', array(
            'api_version' => 2,
            'render_callback' => 'logical_theme_render_custom_content_block',
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
