<?php
/**
 * Dynamic hero renderer based on ACF field `hero`.
 *
 * Handles:
 * - options: static / post-based
 * - layout.type: Mono / Split
 * - layout.width: Full / Container
 */

if (!function_exists('logical_get_hero_section_class')) {
    function logical_get_hero_section_class($section_color_value)
    {
        $allowed = ['white', 'primary-white', 'secondary-white', 'primary', 'secondary', 'black'];

        $normalize = static function ($value) use ($allowed) {
            $value = strtolower(trim((string) $value));
            return in_array($value, $allowed, true) ? $value : '';
        };

        if (is_string($section_color_value)) {
            $match = $normalize($section_color_value);
            return $match ?: 'white';
        }

        if (is_array($section_color_value)) {
            // Common clone shapes from ACF.
            $known_keys = ['section_color', 'color', 'value', 'label'];
            foreach ($known_keys as $key) {
                if (array_key_exists($key, $section_color_value)) {
                    $match = logical_get_hero_section_class($section_color_value[$key]);
                    if ($match !== 'white') {
                        return $match;
                    }
                }
            }

            // Generic recursive scan for any allowed value.
            foreach ($section_color_value as $value) {
                if (is_array($value)) {
                    $nested = logical_get_hero_section_class($value);
                    if ($nested !== 'white') {
                        return $nested;
                    }
                } else {
                    $match = $normalize($value);
                    if ($match !== '') {
                        return $match;
                    }
                }
            }
        }

        return 'white';
    }
}

if (!function_exists('get_field') || !function_exists('have_rows')) {
    return;
}

if (have_rows('hero')) {
    while (have_rows('hero')) {
        the_row();

        if (get_row_layout() !== 'hero') {
            continue;
        }

        $options = strtolower((string) get_sub_field('options'));
        $layout = get_sub_field('layout');
        $layout = is_array($layout) ? $layout : [];

        $hero_type = strtolower((string) ($layout['type'] ?? 'mono'));
        if (!in_array($hero_type, ['mono', 'split'], true)) {
            $hero_type = 'mono';
        }

        $section_color = get_sub_field('section_color');
        $section_class = logical_get_hero_section_class($section_color);

        $hero_width = strtolower((string) ($layout['width'] ?? 'full'));
        $container_class = $hero_width === 'container' ? 'container' : 'container-fluid px-0';

        if ($options === 'post-based') {
            $posts = get_posts([
                'post_type' => 'post',
                'posts_per_page' => 1,
                'post_status' => 'publish',
                'orderby' => 'date',
                'order' => 'DESC',
                'ignore_sticky_posts' => true,
                'suppress_filters' => true,
            ]);

            if (empty($posts)) {
                continue;
            }

            set_query_var('logical_hero_mode', 'post-based');
            set_query_var('logical_hero_post', $posts[0]);
            set_query_var('logical_hero_container_class', $container_class);
            set_query_var('logical_hero_section_class', $section_class);
            get_template_part('template-parts/hero/' . $hero_type);
            continue;
        }

        $static_rows = get_sub_field('static_hero');
        $static_rows = is_array($static_rows) ? $static_rows : [];

        if (empty($static_rows)) {
            continue;
        }

        foreach ($static_rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            set_query_var('logical_hero_mode', 'static');
            set_query_var('logical_hero_row', $row);
            set_query_var('logical_hero_container_class', $container_class);
            set_query_var('logical_hero_section_class', $section_class);
            get_template_part('template-parts/hero/' . $hero_type);
        }
    }
}
