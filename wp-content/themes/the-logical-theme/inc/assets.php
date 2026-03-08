<?php

declare(strict_types=1);

/**
 * Resolves and enqueues either the Vite development server assets or the built production bundles.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns the configured Vite development server URL.
 */
function the_logical_theme_get_vite_dev_server(): string
{
    return (string) apply_filters('the_logical_theme_vite_dev_server', 'http://localhost:5173');
}

/**
 * Detects whether the development server is available in the current environment.
 */
function the_logical_theme_should_use_vite_dev_server(): bool
{
    if (! wp_get_environment_type() || wp_get_environment_type() === 'production') {
        return false;
    }

    $response = wp_remote_get(
        the_logical_theme_get_vite_dev_server() . '/@vite/client',
        [
            'timeout' => 0.2,
            'sslverify' => false,
        ]
    );

    return ! is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200;
}

/**
 * Reads and caches the Vite manifest used to resolve built asset filenames.
 */
function the_logical_theme_get_vite_manifest(): array
{
    static $manifest = null;

    if (null !== $manifest) {
        return $manifest;
    }

    $manifest_path = get_theme_file_path('assets/.vite/manifest.json');

    if (! file_exists($manifest_path)) {
        $manifest = [];
        return $manifest;
    }

    $decoded = json_decode((string) file_get_contents($manifest_path), true);
    $manifest = is_array($decoded) ? $decoded : [];

    return $manifest;
}

/**
 * Resolves a script entry to either a built asset or a source file URI.
 */
function the_logical_theme_get_theme_script_uri(string $entry): string
{
    $manifest = the_logical_theme_get_vite_manifest();

    if (isset($manifest[$entry]['file'])) {
        return get_theme_file_uri('assets/' . $manifest[$entry]['file']);
    }

    return get_theme_file_uri($entry);
}

/**
 * Resolves a script entry to either a built asset or a source file path.
 */
function the_logical_theme_get_theme_script_path(string $entry): string
{
    $manifest = the_logical_theme_get_vite_manifest();

    if (isset($manifest[$entry]['file'])) {
        return get_theme_file_path('assets/' . $manifest[$entry]['file']);
    }

    return get_theme_file_path($entry);
}

/**
 * Resolves a style entry to either a built asset or a source file URI.
 */
function the_logical_theme_get_theme_style_uri(string $entry): string
{
    $manifest = the_logical_theme_get_vite_manifest();

    if (isset($manifest[$entry]['file'])) {
        return get_theme_file_uri('assets/' . $manifest[$entry]['file']);
    }

    return get_theme_file_uri($entry);
}

/**
 * Resolves a style entry to either a built asset or a source file path.
 */
function the_logical_theme_get_theme_style_path(string $entry): string
{
    $manifest = the_logical_theme_get_vite_manifest();

    if (isset($manifest[$entry]['file'])) {
        return get_theme_file_path('assets/' . $manifest[$entry]['file']);
    }

    return get_theme_file_path($entry);
}

/**
 * Registers a Vite-driven script handle for later enqueueing.
 */
function the_logical_theme_register_vite_script(string $handle, string $entry, array $dependencies = []): void
{
    if (the_logical_theme_should_use_vite_dev_server()) {
        $server = untrailingslashit(the_logical_theme_get_vite_dev_server());
        wp_register_script($handle, $server . '/' . ltrim($entry, '/'), $dependencies, null, true);
        return;
    }

    $manifest = the_logical_theme_get_vite_manifest();

    if (! isset($manifest[$entry]['file'])) {
        return;
    }

    $script_uri = get_theme_file_uri('assets/' . $manifest[$entry]['file']);
    $script_path = get_theme_file_path('assets/' . $manifest[$entry]['file']);
    $version = file_exists($script_path) ? (string) filemtime($script_path) : TLT_VERSION;

    wp_register_script($handle, $script_uri, $dependencies, $version, true);
}

/**
 * Associates a registered theme script with translation JSON files.
 */
function the_logical_theme_set_script_translations(string $handle): void
{
    if (! function_exists('wp_set_script_translations')) {
        return;
    }

    wp_set_script_translations($handle, 'the-logical-theme', the_logical_theme_languages_path());
}

/**
 * Registers a Vite-driven style handle for later enqueueing.
 */
function the_logical_theme_register_vite_style(string $handle, string $entry, array $dependencies = []): void
{
    if (the_logical_theme_should_use_vite_dev_server()) {
        $server = untrailingslashit(the_logical_theme_get_vite_dev_server());
        wp_register_style($handle, $server . '/' . ltrim($entry, '/'), $dependencies, null);
        return;
    }

    $manifest = the_logical_theme_get_vite_manifest();

    if (! isset($manifest[$entry]['file'])) {
        return;
    }

    $style_uri = get_theme_file_uri('assets/' . $manifest[$entry]['file']);
    $style_path = get_theme_file_path('assets/' . $manifest[$entry]['file']);
    $version = file_exists($style_path) ? (string) filemtime($style_path) : TLT_VERSION;

    wp_register_style($handle, $style_uri, $dependencies, $version);
}

/**
 * Enqueues the front-end JavaScript and CSS entrypoints.
 */
function the_logical_theme_enqueue_vite_assets(): void
{
    if (the_logical_theme_should_use_vite_dev_server()) {
        $server = untrailingslashit(the_logical_theme_get_vite_dev_server());

        wp_enqueue_script('the-logical-theme-vite-client', $server . '/@vite/client', [], null, true);
        wp_enqueue_script('the-logical-theme-app', $server . '/src/js/app.js', [], null, true);
        wp_enqueue_style('the-logical-theme-style', $server . '/src/css/app.css', [], null);
        return;
    }

    $manifest = the_logical_theme_get_vite_manifest();

    if (isset($manifest['src/js/app.js']['file'])) {
        $entry = $manifest['src/js/app.js'];
        $js_uri = get_theme_file_uri('assets/' . $entry['file']);
        $js_path = get_theme_file_path('assets/' . $entry['file']);
        $version = file_exists($js_path) ? (string) filemtime($js_path) : TLT_VERSION;

        wp_enqueue_script('the-logical-theme-app', $js_uri, [], $version, true);

        foreach (($entry['css'] ?? []) as $css_file) {
            $css_uri = get_theme_file_uri('assets/' . $css_file);
            $css_path = get_theme_file_path('assets/' . $css_file);
            $css_version = file_exists($css_path) ? (string) filemtime($css_path) : $version;
            wp_enqueue_style('the-logical-theme-style-' . md5($css_file), $css_uri, [], $css_version);
        }
    }

    if (isset($manifest['src/css/app.css']['file'])) {
        $css_file = $manifest['src/css/app.css']['file'];
        $css_uri = get_theme_file_uri('assets/' . $css_file);
        $css_path = get_theme_file_path('assets/' . $css_file);
        $version = file_exists($css_path) ? (string) filemtime($css_path) : TLT_VERSION;
        wp_enqueue_style('the-logical-theme-style', $css_uri, [], $version);
    }
}
