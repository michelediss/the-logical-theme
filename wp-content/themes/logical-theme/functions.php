<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('LOGICAL_THEME_ENABLE_LEGACY_CONTENT_JSON')) {
    define('LOGICAL_THEME_ENABLE_LEGACY_CONTENT_JSON', false);
}

$logical_theme_content_json_bootstrap = get_stylesheet_directory() . '/inc/content-json.php';
if (file_exists($logical_theme_content_json_bootstrap)) {
    require_once $logical_theme_content_json_bootstrap;
}
$logical_theme_layout_io_bootstrap = get_stylesheet_directory() . '/inc/content-layout-io.php';
if (file_exists($logical_theme_layout_io_bootstrap)) {
    require_once $logical_theme_layout_io_bootstrap;
}
$logical_theme_custom_blocks_bootstrap = get_stylesheet_directory() . '/inc/custom-blocks.php';
if (file_exists($logical_theme_custom_blocks_bootstrap)) {
    require_once $logical_theme_custom_blocks_bootstrap;
}

function logical_theme_setup()
{
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    register_nav_menus(array(
        'main-menu' => __('Main Menu', 'wp-logical-theme'),
        'footer-marketing' => __('Footer Marketing Menu', 'wp-logical-theme'),
    ));
}
add_action('after_setup_theme', 'logical_theme_setup');

function logical_theme_relocate_templates()
{
    $types = array(
        '404',
        'archive',
        'attachment',
        'author',
        'category',
        'date',
        'frontpage',
        'home',
        'index',
        'page',
        'search',
        'single',
        'singular',
        'tag',
        'taxonomy',
    );

    foreach ($types as $type) {
        add_filter("{$type}_template_hierarchy", function ($templates) {
            return array_map(function ($template_name) {
                return "templates/{$template_name}";
            }, $templates);
        });
    }
}
logical_theme_relocate_templates();

if (!function_exists('logical_theme_build_color_context_css')) {
    function logical_theme_build_color_context_css()
    {
        static $cache = null;
        if (is_string($cache)) {
            return $cache;
        }

        $palette_slugs = function_exists('logical_theme_get_theme_palette_slugs')
            ? logical_theme_get_theme_palette_slugs()
            : array();
        if (empty($palette_slugs)) {
            $cache = '';
            return $cache;
        }

        $map = function_exists('logical_theme_get_color_context_map')
            ? logical_theme_get_color_context_map()
            : array();
        $default_map = isset($map['default']) && is_array($map['default']) ? $map['default'] : array();

        $roles = array('body', 'heading', 'eyebrow', 'muted');
        $fallback_by_role = array(
            'body' => 'black',
            'heading' => 'primary',
            'eyebrow' => 'primary',
            'muted' => 'secondary',
        );

        $css_lines = array(
            '.logical-theme-color-surface{background-color:var(--logical-surface-bg,var(--wp--style--color--background,transparent));}',
            '.logical-theme-color-surface :where(p,li){color:var(--logical-color-body,var(--wp--style--color--text,currentColor));}',
            '.logical-theme-color-surface :where(h1,h2,h3,h4,h5,h6){color:var(--logical-color-heading,var(--logical-color-body,var(--wp--style--color--text,currentColor)));}',
            '.logical-theme-color-surface .logical-color-body{color:var(--logical-color-body,var(--wp--style--color--text,currentColor));}',
            '.logical-theme-color-surface .logical-color-heading{color:var(--logical-color-heading,var(--logical-color-body,var(--wp--style--color--text,currentColor)));}',
            '.logical-theme-color-surface .logical-color-eyebrow{color:var(--logical-color-eyebrow,var(--logical-color-heading,var(--wp--style--color--text,currentColor)));}',
            '.logical-theme-color-surface .logical-color-muted{color:var(--logical-color-muted,var(--logical-color-body,var(--wp--style--color--text,currentColor)));}',
            '.logical-theme-color-surface .logical-color-border{border-color:var(--logical-color-muted,var(--wp--style--color--text,currentColor));}',
            '.logical-layout-block[class*="-font-family"] :where(.heading,.paragraph,.logical-layout-pretitle,.logical-layout-title,.logical-layout-text,.logical-layout-button){font-family:inherit;}',
            '.logical-layout-block.has-text-color .logical-theme-color-surface :where(p,li,h1,h2,h3,h4,h5,h6,.logical-color-body,.logical-color-heading,.logical-color-eyebrow,.logical-color-muted){color:inherit;}',
        );

        $default_surface = in_array('white', $palette_slugs, true) ? 'white' : $palette_slugs[0];
        $css_lines[] = sprintf(
            '.logical-theme-color-surface{--logical-surface-bg:var(--wp--preset--color--%1$s);}',
            $default_surface
        );

        foreach ($roles as $role) {
            $role_slug = isset($default_map[$role]) ? sanitize_key((string) $default_map[$role]) : '';
            if ($role_slug === '' || !in_array($role_slug, $palette_slugs, true)) {
                $fallback_role_slug = isset($fallback_by_role[$role]) ? $fallback_by_role[$role] : '';
                $role_slug = in_array($fallback_role_slug, $palette_slugs, true) ? $fallback_role_slug : $default_surface;
            }

            $css_lines[] = sprintf(
                '.logical-theme-color-surface{--logical-color-%1$s:var(--wp--preset--color--%2$s);}',
                $role,
                $role_slug
            );
        }

        foreach ($palette_slugs as $surface_slug) {
            $surface_map = isset($map[$surface_slug]) && is_array($map[$surface_slug]) ? $map[$surface_slug] : $default_map;
            $declarations = array(
                sprintf('--logical-surface-bg:var(--wp--preset--color--%s);', $surface_slug),
            );

            foreach ($roles as $role) {
                $role_slug = isset($surface_map[$role]) ? sanitize_key((string) $surface_map[$role]) : '';
                if ($role_slug === '' || !in_array($role_slug, $palette_slugs, true)) {
                    $fallback_role_slug = isset($default_map[$role]) ? sanitize_key((string) $default_map[$role]) : '';
                    if ($fallback_role_slug === '' || !in_array($fallback_role_slug, $palette_slugs, true)) {
                        $fallback_role_slug = isset($fallback_by_role[$role]) ? $fallback_by_role[$role] : $default_surface;
                    }
                    if (!in_array($fallback_role_slug, $palette_slugs, true)) {
                        $fallback_role_slug = $default_surface;
                    }
                    $role_slug = $fallback_role_slug;
                }

                $declarations[] = sprintf('--logical-color-%1$s:var(--wp--preset--color--%2$s);', $role, $role_slug);
            }

            $css_lines[] = sprintf(
                '.logical-theme-color-surface[data-surface-color="%1$s"]{%2$s}',
                $surface_slug,
                implode('', $declarations)
            );
        }

        $cache = implode("\n", $css_lines);
        return $cache;
    }
}

function logical_theme_enqueue_assets()
{
    $style_path = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'logical-theme-style',
        get_stylesheet_uri(),
        array(),
        (string) filemtime($style_path)
    );

    $build_dir = get_stylesheet_directory() . '/assets/build';
    $build_uri = get_stylesheet_directory_uri() . '/assets/build';

    $vite_style_path = $build_dir . '/style.css';
    $inline_color_css = logical_theme_build_color_context_css();
    if (file_exists($vite_style_path)) {
        wp_enqueue_style(
            'logical-theme-vite-style',
            $build_uri . '/style.css',
            array('logical-theme-style'),
            (string) filemtime($vite_style_path)
        );
        if ($inline_color_css !== '') {
            wp_add_inline_style('logical-theme-vite-style', $inline_color_css);
        }
    } elseif ($inline_color_css !== '') {
        wp_add_inline_style('logical-theme-style', $inline_color_css);
    }

    $vite_theme_js_path = $build_dir . '/theme.js';
    if (file_exists($vite_theme_js_path)) {
        wp_enqueue_script(
            'logical-theme-vite-theme',
            $build_uri . '/theme.js',
            array(),
            (string) filemtime($vite_theme_js_path),
            true
        );
        wp_script_add_data('logical-theme-vite-theme', 'type', 'module');
    }
}
add_action('wp_enqueue_scripts', 'logical_theme_enqueue_assets');

function logical_theme_enqueue_block_editor_assets()
{
    $build_dir = get_stylesheet_directory() . '/assets/build';
    $build_uri = get_stylesheet_directory_uri() . '/assets/build';

    $vite_style_path = $build_dir . '/style.css';
    $inline_color_css = logical_theme_build_color_context_css();
    if (file_exists($vite_style_path)) {
        wp_enqueue_style(
            'logical-theme-block-editor-style',
            $build_uri . '/style.css',
            array(),
            (string) filemtime($vite_style_path)
        );
        if ($inline_color_css !== '') {
            wp_add_inline_style('logical-theme-block-editor-style', $inline_color_css);
        }
    }

    $vite_editor_js_path = $build_dir . '/editor.js';
    if (file_exists($vite_editor_js_path)) {
        wp_enqueue_script(
            'logical-theme-block-editor-script',
            $build_uri . '/editor.js',
            array(
                'wp-blocks',
                'wp-block-editor',
                'wp-components',
                'wp-data',
                'wp-element',
                'wp-i18n',
                'wp-server-side-render',
            ),
            (string) filemtime($vite_editor_js_path),
            true
        );
        wp_script_add_data('logical-theme-block-editor-script', 'type', 'module');
    }
}
add_action('enqueue_block_editor_assets', 'logical_theme_enqueue_block_editor_assets');
