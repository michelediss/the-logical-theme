<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('LOGICAL_THEME_CONTENT_JSON_META_KEY')) {
    define('LOGICAL_THEME_CONTENT_JSON_META_KEY', '_logical_content_json');
}

if (!function_exists('logical_theme_get_block_spec')) {
    function logical_theme_get_block_spec($block_name)
    {
        static $cache = array();
        $block_name = sanitize_key((string) $block_name);
        if ($block_name === '') {
            return null;
        }

        if (array_key_exists($block_name, $cache)) {
            return $cache[$block_name];
        }

        $path = trailingslashit(get_stylesheet_directory()) . 'templates/blocks/' . $block_name . '/' . $block_name . '.json';
        if (!file_exists($path)) {
            $cache[$block_name] = null;
            return null;
        }

        $raw = file_get_contents($path);
        if (!is_string($raw) || trim($raw) === '') {
            $cache[$block_name] = null;
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            $cache[$block_name] = null;
            return null;
        }

        $cache[$block_name] = $decoded;
        return $cache[$block_name];
    }
}

if (!function_exists('logical_theme_sanitize_data_from_spec')) {
    function logical_theme_sanitize_data_from_spec($data, $spec)
    {
        if (!is_array($data)) {
            $data = array();
        }
        if (!is_array($spec) || !isset($spec['fields']) || !is_array($spec['fields'])) {
            return array();
        }

        $sanitized = array();
        foreach ($spec['fields'] as $field) {
            if (!is_array($field) || !isset($field['key']) || !is_string($field['key'])) {
                continue;
            }

            $key = sanitize_key($field['key']);
            if ($key === '') {
                continue;
            }

            $default = isset($field['default']) && is_string($field['default']) ? $field['default'] : '';
            $value = array_key_exists($key, $data) ? (string) $data[$key] : $default;
            $sanitize = isset($field['sanitize']) && is_string($field['sanitize']) ? $field['sanitize'] : 'text';

            if ($sanitize === 'html') {
                $sanitized[$key] = wp_kses_post($value);
            } else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }

        return $sanitized;
    }
}

if (!function_exists('logical_theme_content_json_allowed_section_types')) {
    function logical_theme_content_json_allowed_section_types()
    {
        return array('paragraph');
    }
}

if (!function_exists('logical_theme_get_theme_palette_entries')) {
    function logical_theme_get_theme_palette_entries()
    {
        static $cache = null;
        if (is_array($cache)) {
            return $cache;
        }

        $cache = array();
        $theme_json_path = trailingslashit(get_stylesheet_directory()) . 'theme.json';
        if (!file_exists($theme_json_path)) {
            return $cache;
        }

        $raw = file_get_contents($theme_json_path);
        if (!is_string($raw) || trim($raw) === '') {
            return $cache;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $cache;
        }

        $palette = isset($decoded['settings']['color']['palette']) && is_array($decoded['settings']['color']['palette'])
            ? $decoded['settings']['color']['palette']
            : array();

        foreach ($palette as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $slug = isset($entry['slug']) ? sanitize_key((string) $entry['slug']) : '';
            if ($slug === '') {
                continue;
            }

            $cache[] = array(
                'slug' => $slug,
                'name' => isset($entry['name']) ? sanitize_text_field((string) $entry['name']) : $slug,
            );
        }

        return $cache;
    }
}

if (!function_exists('logical_theme_get_theme_palette_slugs')) {
    function logical_theme_get_theme_palette_slugs()
    {
        $entries = logical_theme_get_theme_palette_entries();
        $slugs = array();

        foreach ($entries as $entry) {
            if (!is_array($entry) || !isset($entry['slug'])) {
                continue;
            }
            $slugs[] = (string) $entry['slug'];
        }

        return array_values(array_unique(array_filter($slugs, function ($slug) {
            return is_string($slug) && $slug !== '';
        })));
    }
}

if (!function_exists('logical_theme_sanitize_surface_color_slug')) {
    function logical_theme_sanitize_surface_color_slug($slug)
    {
        $slug = sanitize_key((string) $slug);
        if ($slug === '') {
            return '';
        }

        $allowed = logical_theme_get_theme_palette_slugs();
        if (!in_array($slug, $allowed, true)) {
            return '';
        }

        return $slug;
    }
}

if (!function_exists('logical_theme_get_color_context_map')) {
    function logical_theme_get_color_context_map()
    {
        static $cache = null;
        if (is_array($cache)) {
            return $cache;
        }

        $cache = array();
        $map_path = trailingslashit(get_stylesheet_directory()) . 'config/color-context-map.json';
        if (!file_exists($map_path)) {
            return $cache;
        }

        $raw = file_get_contents($map_path);
        if (!is_string($raw) || trim($raw) === '') {
            return $cache;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return $cache;
        }

        $roles = array('body', 'heading', 'eyebrow', 'muted');
        foreach ($decoded as $surface_slug => $surface_map) {
            $surface_slug = sanitize_key((string) $surface_slug);
            if ($surface_slug === '' || !is_array($surface_map)) {
                continue;
            }

            $sanitized_map = array();
            foreach ($roles as $role) {
                $role_slug = isset($surface_map[$role]) ? sanitize_key((string) $surface_map[$role]) : '';
                if ($role_slug !== '') {
                    $sanitized_map[$role] = $role_slug;
                }
            }

            if (!empty($sanitized_map)) {
                $cache[$surface_slug] = $sanitized_map;
            }
        }

        return $cache;
    }
}

if (!function_exists('logical_theme_content_json_sanitize_paragraph_data')) {
    function logical_theme_content_json_sanitize_paragraph_data($data)
    {
        if (!is_array($data)) {
            $data = array();
        }

        $spec = logical_theme_get_block_spec('paragraph');
        $base = is_array($spec)
            ? logical_theme_sanitize_data_from_spec($data, $spec)
            : array(
                'pretitle' => isset($data['pretitle']) ? sanitize_text_field((string) $data['pretitle']) : '',
                'title' => isset($data['title']) ? sanitize_text_field((string) $data['title']) : '',
                'text' => isset($data['text']) ? wp_kses_post((string) $data['text']) : '',
            );

        $image = isset($data['image']) && is_array($data['image']) ? $data['image'] : array();
        $base['image'] = array(
            'id' => isset($image['id']) ? (int) $image['id'] : 0,
            'src' => isset($image['src']) ? esc_url_raw((string) $image['src']) : '',
            'alt' => isset($image['alt']) ? sanitize_text_field((string) $image['alt']) : '',
        );

        return $base;
    }
}

if (!function_exists('logical_theme_content_json_sanitize_paragraph_settings')) {
    function logical_theme_content_json_sanitize_paragraph_settings($settings)
    {
        if (!is_array($settings)) {
            $settings = array();
        }

        $spec = logical_theme_get_block_spec('paragraph');
        $default_variant = '1';
        $allowed_variants = array('1', '2');

        if (is_array($spec)) {
            if (isset($spec['defaultVariant']) && is_string($spec['defaultVariant']) && trim($spec['defaultVariant']) !== '') {
                $default_variant = trim($spec['defaultVariant']);
            }

            if (isset($spec['variants']) && is_array($spec['variants'])) {
                $allowed_variants = array();
                foreach ($spec['variants'] as $variant) {
                    if (is_array($variant) && isset($variant['value']) && is_string($variant['value'])) {
                        $allowed_variants[] = trim($variant['value']);
                    } elseif (is_string($variant)) {
                        $allowed_variants[] = trim($variant);
                    }
                }
                $allowed_variants = array_values(array_filter(array_unique($allowed_variants), function ($value) {
                    return is_string($value) && $value !== '';
                }));
                if (count($allowed_variants) === 0) {
                    $allowed_variants = array($default_variant);
                }
            }
        }

        $variant = isset($settings['variant']) ? trim((string) $settings['variant']) : $default_variant;
        if (!in_array($variant, $allowed_variants, true)) {
            $variant = $default_variant;
        }

        $background_color = isset($settings['backgroundColor'])
            ? logical_theme_sanitize_surface_color_slug($settings['backgroundColor'])
            : '';

        return array(
            'variant' => $variant,
            'backgroundColor' => $background_color,
        );
    }
}

if (!function_exists('logical_theme_content_json_sanitize_section')) {
    function logical_theme_content_json_sanitize_section($section, $index)
    {
        if (!is_array($section)) {
            return new WP_Error('logical_theme_invalid_section', sprintf(__('Section %d must be an object.', 'wp-logical-theme'), $index));
        }

        $id = isset($section['id']) ? sanitize_key((string) $section['id']) : '';
        if ($id === '') {
            return new WP_Error('logical_theme_invalid_section_id', sprintf(__('Section %d is missing a valid id.', 'wp-logical-theme'), $index));
        }

        $type = isset($section['type']) ? sanitize_key((string) $section['type']) : '';
        if (!in_array($type, logical_theme_content_json_allowed_section_types(), true)) {
            return new WP_Error('logical_theme_invalid_section_type', sprintf(__('Section %d has unsupported type.', 'wp-logical-theme'), $index));
        }

        return array(
            'id' => $id,
            'type' => 'paragraph',
            'data' => logical_theme_content_json_sanitize_paragraph_data(isset($section['data']) ? $section['data'] : array()),
            'settings' => logical_theme_content_json_sanitize_paragraph_settings(isset($section['settings']) ? $section['settings'] : array()),
        );
    }
}

if (!function_exists('logical_theme_normalize_content_json')) {
    function logical_theme_normalize_content_json($raw)
    {
        if (!is_string($raw)) {
            return new WP_Error('logical_theme_invalid_content_json', __('Content JSON must be a string.', 'wp-logical-theme'));
        }

        $raw = trim($raw);
        if ($raw === '') {
            return new WP_Error('logical_theme_invalid_content_json', __('Content JSON cannot be empty.', 'wp-logical-theme'));
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('logical_theme_invalid_content_json', __('Content JSON is not valid JSON.', 'wp-logical-theme'));
        }

        if (!isset($decoded['version']) || (string) $decoded['version'] !== '2.0') {
            return new WP_Error('logical_theme_invalid_content_json_version', __('Content JSON version must be 2.0.', 'wp-logical-theme'));
        }

        if (!array_key_exists('sections', $decoded) || !is_array($decoded['sections'])) {
            return new WP_Error('logical_theme_invalid_content_json_sections', __('Content JSON sections must be an array.', 'wp-logical-theme'));
        }

        $sections = array();
        foreach ($decoded['sections'] as $index => $section) {
            $sanitized_section = logical_theme_content_json_sanitize_section($section, (int) $index + 1);
            if (is_wp_error($sanitized_section)) {
                return $sanitized_section;
            }
            $sections[] = $sanitized_section;
        }

        $normalized = array(
            'version' => '2.0',
            'sections' => $sections,
        );

        $encoded = wp_json_encode($normalized, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($encoded) || $encoded === '') {
            return new WP_Error('logical_theme_invalid_content_json_encoding', __('Unable to encode content JSON.', 'wp-logical-theme'));
        }

        return array(
            'decoded' => $normalized,
            'encoded' => $encoded,
        );
    }
}

if (!function_exists('logical_theme_register_content_json_meta')) {
    function logical_theme_register_content_json_meta()
    {
        register_post_meta('page', LOGICAL_THEME_CONTENT_JSON_META_KEY, array(
            'type' => 'string',
            'single' => true,
            'show_in_rest' => true,
            'auth_callback' => 'logical_theme_content_json_meta_auth_callback',
            'sanitize_callback' => 'logical_theme_content_json_meta_sanitize_callback',
        ));
    }
}
add_action('init', 'logical_theme_register_content_json_meta');

if (!function_exists('logical_theme_content_json_meta_auth_callback')) {
    function logical_theme_content_json_meta_auth_callback($allowed, $meta_key, $post_id)
    {
        return current_user_can('edit_post', (int) $post_id);
    }
}

if (!function_exists('logical_theme_content_json_meta_sanitize_callback')) {
    function logical_theme_content_json_meta_sanitize_callback($meta_value)
    {
        $normalized = logical_theme_normalize_content_json($meta_value);
        if (is_array($normalized) && isset($normalized['encoded'])) {
            return $normalized['encoded'];
        }

        return '';
    }
}

if (!function_exists('logical_theme_validate_rest_content_json_meta')) {
    function logical_theme_validate_rest_content_json_meta($prepared_post, $request)
    {
        $meta = $request->get_param('meta');
        if (!is_array($meta) || !array_key_exists(LOGICAL_THEME_CONTENT_JSON_META_KEY, $meta)) {
            return $prepared_post;
        }

        $normalized = logical_theme_normalize_content_json($meta[LOGICAL_THEME_CONTENT_JSON_META_KEY]);
        if (is_wp_error($normalized) || !isset($normalized['encoded'])) {
            $message = is_wp_error($normalized)
                ? $normalized->get_error_message()
                : __('Invalid content JSON.', 'wp-logical-theme');

            return new WP_Error(
                'logical_theme_invalid_content_json',
                $message,
                array('status' => 400)
            );
        }

        $meta[LOGICAL_THEME_CONTENT_JSON_META_KEY] = $normalized['encoded'];
        $request->set_param('meta', $meta);

        return $prepared_post;
    }
}
add_filter('rest_pre_insert_page', 'logical_theme_validate_rest_content_json_meta', 10, 2);

if (!function_exists('logical_theme_export_content_json_on_save')) {
    function logical_theme_export_content_json_on_save($post_id, $post)
    {
        if (!($post instanceof WP_Post)) {
            return;
        }

        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $raw_json = get_post_meta($post_id, LOGICAL_THEME_CONTENT_JSON_META_KEY, true);
        $normalized = logical_theme_normalize_content_json($raw_json);
        if (is_wp_error($normalized) || !isset($normalized['decoded'])) {
            return;
        }

        $slug = sanitize_title((string) $post->post_name);
        if ($slug === '') {
            $slug = 'page-' . (string) $post_id;
        }

        $target_dir = trailingslashit(get_stylesheet_directory()) . 'assets/json';
        if (!wp_mkdir_p($target_dir)) {
            return;
        }

        $pretty_json = wp_json_encode(
            $normalized['decoded'],
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        if (!is_string($pretty_json) || $pretty_json === '') {
            return;
        }

        $target_file = trailingslashit($target_dir) . $slug . '.json';
        file_put_contents($target_file, $pretty_json . PHP_EOL, LOCK_EX);
    }
}
add_action('save_post_page', 'logical_theme_export_content_json_on_save', 10, 2);
