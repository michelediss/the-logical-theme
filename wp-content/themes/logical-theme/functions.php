<?php

if (!defined('ABSPATH')) {
    exit;
}

$logical_theme_content_json_bootstrap = get_stylesheet_directory() . '/inc/content-json.php';
if (file_exists($logical_theme_content_json_bootstrap)) {
    require_once $logical_theme_content_json_bootstrap;
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
    if (file_exists($vite_style_path)) {
        wp_enqueue_style(
            'logical-theme-vite-style',
            $build_uri . '/style.css',
            array('logical-theme-style'),
            (string) filemtime($vite_style_path)
        );
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
    if (file_exists($vite_style_path)) {
        wp_enqueue_style(
            'logical-theme-block-editor-style',
            $build_uri . '/style.css',
            array(),
            (string) filemtime($vite_style_path)
        );
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
