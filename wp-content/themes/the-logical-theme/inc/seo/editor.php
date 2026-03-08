<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns whether the current editor screen supports the SEO sidebar panel.
 */
function the_logical_theme_seo_is_supported_editor_screen(): bool
{
    if (! is_admin() || ! function_exists('get_current_screen')) {
        return false;
    }

    $screen = get_current_screen();

    if (! $screen instanceof WP_Screen || $screen->base !== 'post') {
        return false;
    }

    return the_logical_theme_seo_is_supported_post_type((string) $screen->post_type);
}

/**
 * Enqueues the block editor SEO panel assets.
 */
function the_logical_theme_seo_enqueue_editor_assets(): void
{
    if (! the_logical_theme_seo_is_supported_editor_screen()) {
        return;
    }

    $script_handle = 'the-logical-theme-seo-editor';
    $script_uri = the_logical_theme_get_theme_script_uri('src/js/editor-seo.js');
    $script_path = the_logical_theme_get_theme_script_path('src/js/editor-seo.js');
    $version = file_exists($script_path) ? (string) filemtime($script_path) : TLT_VERSION;

    wp_enqueue_script(
        $script_handle,
        $script_uri,
        ['wp-components', 'wp-compose', 'wp-data', 'wp-edit-post', 'wp-element', 'wp-i18n', 'wp-plugins'],
        $version,
        true
    );

    wp_add_inline_script(
        $script_handle,
        'window.theLogicalThemeSeoEditor = ' . wp_json_encode([
            'siteName' => get_bloginfo('name'),
            'siteDescription' => get_bloginfo('description', 'display'),
            'homeUrl' => home_url('/'),
        ]) . ';',
        'before'
    );
}
add_action('enqueue_block_editor_assets', 'the_logical_theme_seo_enqueue_editor_assets', 20);
