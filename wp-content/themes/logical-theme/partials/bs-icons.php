<?php
/**
 * Bootstrap Icons for ACF select field "bs_icon".
 * PHP 8+, ACF Pro, WordPress 6+
 */

if (!defined('ABSPATH')) {
    exit;
}

const LOGICAL_BS_ICON_FIELD_NAME = 'icon';
const LOGICAL_BS_ICON_FIELD_KEY = 'field_6988e0d8a5ed0';
const LOGICAL_BS_ICON_TRANSIENT  = 'logical_bs_icons_v1';

/**
 * Sanitize slug to avoid injection/XSS.
 */
function logical_bs_icon_sanitize_slug(string $slug): string
{
    $slug = preg_replace('/\.svg$/i', '', $slug);
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug);

    // Fix common bad input like "arrow-rightsvg" from filenames.
    if (str_ends_with($slug, 'svg') && !str_ends_with($slug, '-svg')) {
        $slug = substr($slug, 0, -3);
    }

    return $slug;
}

/**
 * Turn "arrow-right" into "Arrow Right".
 */
function logical_bs_icon_label(string $slug): string
{
    $slug  = logical_bs_icon_sanitize_slug($slug);
    $label = str_replace(['-', '_'], ' ', $slug);
    return ucwords($label);
}

/**
 * Load icons from JSON with cache.
 * Returns array of slugs.
 */
function logical_bs_icon_load_icons(): array
{
    static $static_cache = null;

    if (is_array($static_cache)) {
        return $static_cache;
    }

    $json_path = get_stylesheet_directory() . '/assets/bootstrap-icons/icons.json';
    $mtime     = is_file($json_path) ? (int) filemtime($json_path) : 0;

    $cached = get_transient(LOGICAL_BS_ICON_TRANSIENT);
    if (is_array($cached) && isset($cached['mtime'], $cached['icons']) && $cached['mtime'] === $mtime) {
        $static_cache = $cached['icons'];
        return $static_cache;
    }

    if (!is_readable($json_path)) {
        $static_cache = [];
        return $static_cache;
    }

    $raw  = file_get_contents($json_path);
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        $static_cache = [];
        return $static_cache;
    }

    $icons = [];
    foreach ($data as $slug) {
        if (!is_string($slug)) {
            continue;
        }
        $sanitized = logical_bs_icon_sanitize_slug($slug);
        if ($sanitized !== '') {
            $icons[] = $sanitized;
        }
    }

    $icons = array_values(array_unique($icons));

    $static_cache = $icons;
    set_transient(
        LOGICAL_BS_ICON_TRANSIENT,
        [
            'mtime' => $mtime,
            'icons' => $icons,
        ],
        12 * HOUR_IN_SECONDS
    );

    return $static_cache;
}

/**
 * Populate ACF select field "bs_icon".
 * Using field name keeps it working inside groups, repeater, flexible and clones.
 */
function logical_acf_populate_bs_icon_field(array $field): array
{
    $icons = logical_bs_icon_load_icons();

    $choices = ['' => 'Seleziona icona'];
    foreach ($icons as $slug) {
        $choices[$slug] = logical_bs_icon_label($slug);
    }

    if (count($choices) === 1) {
        $choices = ['' => 'Nessuna icona disponibile'];
    }

    $field['choices'] = $choices;
    return $field;
}
add_filter('acf/load_field/name=' . LOGICAL_BS_ICON_FIELD_NAME, 'logical_acf_populate_bs_icon_field');
add_filter('acf/load_field/key=' . LOGICAL_BS_ICON_FIELD_KEY, 'logical_acf_populate_bs_icon_field');

/**
 * Admin enqueue: only on ACF input screens.
 */
function logical_acf_bs_icons_admin_assets(): void
{
    $css_path = get_stylesheet_directory() . '/assets/bootstrap-icons/bootstrap-icons.css';
    $js_path  = get_stylesheet_directory() . '/assets/js/acf-bs-icon-preview.js';
    $css_uri  = get_stylesheet_directory_uri() . '/assets/bootstrap-icons/bootstrap-icons.css';
    $js_uri   = get_stylesheet_directory_uri() . '/assets/js/acf-bs-icon-preview.js';

    if (is_file($css_path)) {
        wp_enqueue_style('bootstrap-icons', $css_uri, [], (string) filemtime($css_path));
    }

    if (is_file($js_path)) {
        wp_enqueue_script('logical-acf-bs-icon-preview', $js_uri, [], (string) filemtime($js_path), true);
    }
}
add_action('acf/input/admin_enqueue_scripts', 'logical_acf_bs_icons_admin_assets');

/**
 * Frontend enqueue (simple + advanced).
 * Simple: always enqueue.
 * Advanced: allow override via filter.
 */
function logical_bs_icons_should_enqueue(): bool
{
    $default = true;
    return (bool) apply_filters('logical_bs_icons_should_enqueue', $default);
}

function logical_bs_icons_frontend_assets(): void
{
    if (!logical_bs_icons_should_enqueue()) {
        return;
    }

    $css_path = get_stylesheet_directory() . '/assets/bootstrap-icons/bootstrap-icons.css';
    $css_uri  = get_stylesheet_directory_uri() . '/assets/bootstrap-icons/bootstrap-icons.css';

    if (!is_file($css_path)) {
        return;
    }

    if (wp_style_is('bootstrap-icons', 'enqueued')) {
        wp_dequeue_style('bootstrap-icons');
        wp_deregister_style('bootstrap-icons');
    }

    wp_enqueue_style('bootstrap-icons', $css_uri, [], (string) filemtime($css_path));
}
add_action('wp_enqueue_scripts', 'logical_bs_icons_frontend_assets', 20);

/**
 * Helper to render icon HTML safely.
 */
function logical_bs_icon_html(string $slug, string $label = ''): string
{
    $slug = logical_bs_icon_sanitize_slug($slug);
    if ($slug === '') {
        return '';
    }

    $icon = '<i class="bi bi-' . esc_attr($slug) . '" aria-hidden="true"></i>';

    if ($label !== '') {
        $icon .= '<span class="screen-reader-text">' . esc_html($label) . '</span>';
    }

    return $icon;
}
