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

if (defined('LOGICAL_THEME_ENABLE_LEGACY_CONTENT_JSON') && LOGICAL_THEME_ENABLE_LEGACY_CONTENT_JSON) {
    $legacy_file = trailingslashit(get_stylesheet_directory()) . 'inc/content-json-legacy.php';
    if (file_exists($legacy_file)) {
        require_once $legacy_file;
    }
}
