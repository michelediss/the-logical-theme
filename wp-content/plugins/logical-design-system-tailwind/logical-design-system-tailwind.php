<?php
/**
 * Plugin Name: Logical Design System Tailwind
 * Description: Tailwind-based replica of Logical Design System with JSON-driven tokens and compile workflow.
 * Version: 0.1.0
 * Plugin URI: https://github.com/michelediss/the-logical-theme
 * Author: Michele Paolino
 * Author URI: https://michelepaolino.com
 */

if (!defined('ABSPATH')) {
    exit;
}

const LDS_TW_VERSION = '0.1.0';
const LDS_TW_LOCK_KEY = 'lds_tw_compile_lock';
const LDS_TW_CRON_HOOK = 'lds_tw_nightly_compile';

function lds_tw_plugin_dir()
{
    return plugin_dir_path(__FILE__);
}

function lds_tw_plugin_url()
{
    return plugin_dir_url(__FILE__);
}

function lds_tw_theme_dir()
{
    return get_stylesheet_directory();
}

function lds_tw_theme_url()
{
    return get_stylesheet_directory_uri();
}

function lds_tw_theme_config_path()
{
    return lds_tw_theme_dir() . '/assets/json/lds-input.json';
}

function lds_tw_plugin_config_path()
{
    return lds_tw_plugin_dir() . 'config/lds-input.json';
}

function lds_tw_theme_output_css_path()
{
    return lds_tw_theme_dir() . '/assets/css/lds-style.css';
}

function lds_tw_theme_output_min_css_path()
{
    return lds_tw_theme_dir() . '/assets/css/lds-style.min.css';
}

function lds_tw_log($message)
{
    $uploads = wp_upload_dir();
    $dir = trailingslashit($uploads['basedir']) . 'lds-tw';
    if (!is_dir($dir)) {
        wp_mkdir_p($dir);
    }

    $line = '[' . gmdate('Y-m-d H:i:s') . ' UTC] ' . (string) $message . "\n";
    @file_put_contents($dir . '/build.log', $line, FILE_APPEND);
}

function lds_tw_read_json($theme_path, $plugin_path)
{
    $path = file_exists($theme_path) ? $theme_path : $plugin_path;
    if (!file_exists($path)) {
        throw new RuntimeException('JSON config not found: ' . $path);
    }

    $raw = file_get_contents($path);
    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Invalid JSON in ' . $path . ': ' . json_last_error_msg());
    }

    return array($data, $path);
}

function lds_tw_validate_input_json($data, $path)
{
    if (!is_array($data)) {
        throw new RuntimeException('Invalid JSON structure in ' . $path . ': root must be an object.');
    }

    $keys = array('baseSettings', 'baseColors', 'palette', 'colorVariations', 'breakpoints', 'containerMaxWidths', 'font');
    foreach ($keys as $key) {
        if (isset($data[$key]) && !is_array($data[$key])) {
            throw new RuntimeException('Invalid ' . $key . ' in ' . $path . ': must be an object.');
        }
    }

    if (isset($data['font']['imports']) && !is_array($data['font']['imports'])) {
        throw new RuntimeException('Invalid font.imports in ' . $path . ': must be an array.');
    }

    if (isset($data['font']['classes']) && !is_array($data['font']['classes'])) {
        throw new RuntimeException('Invalid font.classes in ' . $path . ': must be an object.');
    }
}

function lds_tw_color_normalize_hex($hex)
{
    $hex = strtolower(trim((string) $hex));
    $hex = ltrim($hex, '#');

    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    if (!preg_match('/^[0-9a-f]{6}$/', $hex)) {
        return null;
    }

    return '#' . $hex;
}

function lds_tw_color_hex_to_rgb($hex)
{
    $hex = lds_tw_color_normalize_hex($hex);
    if ($hex === null) {
        return null;
    }

    return array(
        hexdec(substr($hex, 1, 2)),
        hexdec(substr($hex, 3, 2)),
        hexdec(substr($hex, 5, 2)),
    );
}

function lds_tw_color_rgb_to_hex($r, $g, $b)
{
    $r = max(0, min(255, (int) round($r)));
    $g = max(0, min(255, (int) round($g)));
    $b = max(0, min(255, (int) round($b)));

    return sprintf('#%02x%02x%02x', $r, $g, $b);
}

function lds_tw_mix_colors($color_a, $color_b, $percent_a)
{
    $rgb_a = lds_tw_color_hex_to_rgb($color_a);
    $rgb_b = lds_tw_color_hex_to_rgb($color_b);

    if ($rgb_a === null || $rgb_b === null) {
        return $color_b;
    }

    $w = max(0, min(100, (float) $percent_a)) / 100;

    return lds_tw_color_rgb_to_hex(
        ($rgb_a[0] * $w) + ($rgb_b[0] * (1 - $w)),
        ($rgb_a[1] * $w) + ($rgb_b[1] * (1 - $w)),
        ($rgb_a[2] * $w) + ($rgb_b[2] * (1 - $w))
    );
}

function lds_tw_generate_tailwind_shades($base_color, $variation)
{
    $white = isset($variation['white']) ? (float) $variation['white'] : null;
    $light = isset($variation['light']) ? (float) $variation['light'] : null;
    $dark = isset($variation['dark']) ? (float) $variation['dark'] : null;
    $black = isset($variation['black']) ? (float) $variation['black'] : null;

    $white = $white === null ? 90.0 : $white;
    $light = $light === null ? 60.0 : $light;
    $dark = $dark === null ? 60.0 : $dark;
    $black = $black === null ? 90.0 : $black;

    return array(
        '50' => lds_tw_mix_colors('#ffffff', $base_color, $white),
        '100' => lds_tw_mix_colors('#ffffff', $base_color, $white * 0.8),
        '200' => lds_tw_mix_colors('#ffffff', $base_color, $light * 0.8),
        '300' => lds_tw_mix_colors('#ffffff', $base_color, $light),
        '400' => lds_tw_mix_colors('#ffffff', $base_color, $light * 0.5),
        '500' => $base_color,
        '600' => lds_tw_mix_colors('#000000', $base_color, $dark * 0.5),
        '700' => lds_tw_mix_colors('#000000', $base_color, $dark),
        '800' => lds_tw_mix_colors('#000000', $base_color, $black * 0.8),
        '900' => lds_tw_mix_colors('#000000', $base_color, $black),
        '950' => lds_tw_mix_colors('#000000', $base_color, min(98, $black + 8)),
    );
}

function lds_tw_generate_typography_scale($ratio)
{
    $ratio = $ratio > 0 ? $ratio : 1.2;
    return array(
        'xs' => round(1 / pow($ratio, 2), 3),
        'sm' => round(1 / pow($ratio, 1), 3),
        'base' => 1,
        'lg' => round(pow($ratio, 1), 3),
        'xl' => round(pow($ratio, 2), 3),
        '2xl' => round(pow($ratio, 3), 3),
        '3xl' => round(pow($ratio, 4), 3),
        '4xl' => round(pow($ratio, 5), 3),
        '5xl' => round(pow($ratio, 6), 3),
        '6xl' => round(pow($ratio, 7), 3),
        '7xl' => round(pow($ratio, 8), 3),
        '8xl' => round(pow($ratio, 9), 3),
        '9xl' => round(pow($ratio, 10), 3),
    );
}

function lds_tw_to_css_value($value)
{
    if (is_numeric($value)) {
        return (string) $value;
    }
    return trim((string) $value);
}

function lds_tw_escape_class($class_name)
{
    $escaped = str_replace(':', '\\:', $class_name);
    if (preg_match('/^[0-9]/', $escaped) === 1) {
        $escaped = '\\3' . $escaped[0] . ' ' . substr($escaped, 1);
    }
    return $escaped;
}

function lds_tw_parse_font_import($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    if (preg_match('#^https?://#i', $value)) {
        return "@import url('" . esc_url_raw($value) . "');";
    }

    $prefix = 'font-pairing-list/_';
    if (strpos($value, $prefix) === 0) {
        $name = substr($value, strlen($prefix));
        $parts = explode('_+_', $name);
        $families = array();
        foreach ($parts as $part) {
            $font_name = str_replace('_', ' ', $part);
            $families[] = 'family=' . rawurlencode($font_name) . ':wght@400;700';
        }
        if (!empty($families)) {
            return "@import url('https://fonts.googleapis.com/css2?" . implode('&', $families) . "&display=swap');";
        }
    }

    return null;
}

function lds_tw_generate_tokens_css($data)
{
    $base_settings = isset($data['baseSettings']) && is_array($data['baseSettings']) ? $data['baseSettings'] : array();
    $base_size = isset($base_settings['baseSize']) ? (float) $base_settings['baseSize'] : 16.0;
    $ratio = isset($base_settings['r']) ? (float) $base_settings['r'] : 1.2;
    $increment = isset($base_settings['incrementFactor']) ? (float) $base_settings['incrementFactor'] : 1.01;

    $base_colors = isset($data['baseColors']) && is_array($data['baseColors']) ? $data['baseColors'] : array();
    $black = lds_tw_color_normalize_hex(isset($base_colors['black']) ? $base_colors['black'] : '#1e201f');
    $gray = lds_tw_color_normalize_hex(isset($base_colors['gray']) ? $base_colors['gray'] : '#666666');
    $white = lds_tw_color_normalize_hex(isset($base_colors['white']) ? $base_colors['white'] : '#fcfcfe');

    $palette = isset($data['palette']) && is_array($data['palette']) ? $data['palette'] : array();
    if (empty($palette)) {
        $palette = array(
            'primary' => '#f05252',
            'secondary' => '#c27803',
            'gray' => $gray,
            'black' => $black,
            'white' => $white,
        );
    }

    $variations = isset($data['colorVariations']) && is_array($data['colorVariations']) ? $data['colorVariations'] : array();
    $breakpoints = isset($data['breakpoints']) && is_array($data['breakpoints']) ? $data['breakpoints'] : array(
        'null' => 0,
        'sm' => '576px',
        'md' => '768px',
        'lg' => '1024px',
        'xl' => '1280px',
    );

    $css = array();

    $font = isset($data['font']) && is_array($data['font']) ? $data['font'] : array();
    $font_imports = isset($font['imports']) && is_array($font['imports']) ? $font['imports'] : array();
    foreach ($font_imports as $font_import) {
        $import_line = lds_tw_parse_font_import($font_import);
        if ($import_line !== null) {
            $css[] = $import_line;
        }
    }

    $css[] = ':root {';
    $css[] = '  --lds-base-size: ' . $base_size . ';';
    $css[] = '  --lds-ratio: ' . $ratio . ';';
    $css[] = '  --lds-increment-factor: ' . $increment . ';';

    $rounded = isset($data['rounded']) ? (int) ((bool) $data['rounded']) : 1;
    if ($rounded === 0) {
        $css[] = '  --lds-border-radius: 0;';
    }

    $palette_shades = array();
    foreach ($palette as $name => $color) {
        $normalized = lds_tw_color_normalize_hex($color);
        if ($normalized === null) {
            continue;
        }
        $variation = isset($variations[$name]) && is_array($variations[$name]) ? $variations[$name] : array();
        $palette_shades[$name] = lds_tw_generate_tailwind_shades($normalized, $variation);
    }

    foreach ($palette_shades as $name => $shades) {
        foreach ($shades as $shade => $value) {
            $css[] = '  --' . $name . '-' . $shade . ': ' . $value . ';';
        }
        $css[] = '  --' . $name . ': ' . $shades['500'] . ';';
    }

    $typo_scale = lds_tw_generate_typography_scale($ratio);
    foreach ($typo_scale as $size_name => $size_value) {
        $css[] = '  --text-' . $size_name . ': ' . $size_value . 'rem;';
    }

    $css[] = '}';

    $css[] = 'html { font-size: ' . $base_size . 'px; }';
    $bp_keys = array_keys($breakpoints);
    foreach ($bp_keys as $index => $bp_key) {
        if ($bp_key === 'null') {
            continue;
        }
        $bp_value = lds_tw_to_css_value($breakpoints[$bp_key]);
        $size = $base_size * pow($increment, $index + 1);
        $size = round($size, 3);
        $css[] = '@media (min-width: ' . $bp_value . ') { html { font-size: ' . $size . 'px; } }';
    }

    if (isset($font['classes']) && is_array($font['classes'])) {
        foreach ($font['classes'] as $selector => $rules) {
            if (!is_array($rules)) {
                continue;
            }
            $css[] = $selector . ' {';
            foreach ($rules as $prop => $val) {
                $css[] = '  ' . trim((string) $prop) . ': ' . lds_tw_to_css_value($val) . ';';
            }
            $css[] = '}';
        }
    }

    if ($rounded === 0) {
        $css[] = '.rounded, .rounded-1, .rounded-2, .rounded-3, .rounded-4, .rounded-5, .rounded-pill { border-radius: 0 !important; }';
    }

    return implode("\n", $css) . "\n";
}

function lds_tw_build_palette_shades($data)
{
    $base_colors = isset($data['baseColors']) && is_array($data['baseColors']) ? $data['baseColors'] : array();
    $black = lds_tw_color_normalize_hex(isset($base_colors['black']) ? $base_colors['black'] : '#1e201f');
    $gray = lds_tw_color_normalize_hex(isset($base_colors['gray']) ? $base_colors['gray'] : '#666666');
    $white = lds_tw_color_normalize_hex(isset($base_colors['white']) ? $base_colors['white'] : '#fcfcfe');

    $palette = isset($data['palette']) && is_array($data['palette']) ? $data['palette'] : array();
    if (empty($palette)) {
        $palette = array(
            'primary' => '#f05252',
            'secondary' => '#c27803',
            'gray' => $gray,
            'black' => $black,
            'white' => $white,
        );
    }

    $variations = isset($data['colorVariations']) && is_array($data['colorVariations']) ? $data['colorVariations'] : array();
    $palette_shades = array();

    foreach ($palette as $name => $color) {
        $normalized = lds_tw_color_normalize_hex($color);
        if ($normalized === null) {
            continue;
        }
        $variation = isset($variations[$name]) && is_array($variations[$name]) ? $variations[$name] : array();
        $palette_shades[(string) $name] = lds_tw_generate_tailwind_shades($normalized, $variation);
    }

    return $palette_shades;
}

function lds_tw_generate_runtime_tailwind_config($data)
{
    $breakpoints = isset($data['breakpoints']) && is_array($data['breakpoints']) ? $data['breakpoints'] : array();
    $containers = isset($data['containerMaxWidths']) && is_array($data['containerMaxWidths']) ? $data['containerMaxWidths'] : array();
    $base_settings = isset($data['baseSettings']) && is_array($data['baseSettings']) ? $data['baseSettings'] : array();
    $ratio = isset($base_settings['r']) ? (float) $base_settings['r'] : 1.2;

    $screens = array();
    foreach ($breakpoints as $key => $value) {
        if ($key === 'null') {
            continue;
        }
        $screens[(string) $key] = lds_tw_to_css_value($value);
    }

    $container_screens = array();
    foreach ($containers as $key => $value) {
        if (isset($screens[$key])) {
            $container_screens[(string) $key] = lds_tw_to_css_value($value);
        }
    }

    $colors = lds_tw_build_palette_shades($data);
    $font_scale = lds_tw_generate_typography_scale($ratio);
    $font_sizes = array();
    foreach ($font_scale as $k => $v) {
        $font_sizes[$k] = array($v . 'rem', array('lineHeight' => '1.2'));
    }

    $color_names = array_keys($colors);
    $safe_names = array();
    foreach ($color_names as $name) {
        $safe_names[] = preg_quote((string) $name, '/');
    }
    $color_pattern = empty($safe_names) ? 'primary|secondary' : implode('|', $safe_names);
    $shade_pattern = '50|100|200|300|400|500|600|700|800|900|950';
    $utility_pattern = 'bg|text|decoration|border|outline|shadow|inset-shadow|ring|inset-ring|accent|caret|fill|stroke';

    $variants = array_keys($screens);
    $inset_safelist = array();
    $shades = array('50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950');
    foreach ($color_names as $name) {
        foreach ($shades as $shade) {
            $base_inset_shadow = 'inset-shadow-' . $name . '-' . $shade;
            $base_inset_ring = 'inset-ring-' . $name . '-' . $shade;
            $inset_safelist[] = $base_inset_shadow;
            $inset_safelist[] = $base_inset_ring;
            foreach ($variants as $variant) {
                $inset_safelist[] = $variant . ':' . $base_inset_shadow;
                $inset_safelist[] = $variant . ':' . $base_inset_ring;
            }
        }
    }
    $inset_safelist = array_values(array_unique($inset_safelist));

    $json_flags = JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT;
    $screens_json = json_encode($screens, $json_flags);
    $container_json = json_encode($container_screens, $json_flags);
    $colors_with_primitives = array_merge(
        array(
            'transparent' => 'transparent',
            'current' => 'currentColor',
            'inherit' => 'inherit',
        ),
        $colors
    );
    $colors_full_json = json_encode($colors_with_primitives, $json_flags);
    $font_json = json_encode($font_sizes, $json_flags);
    $variants_json = json_encode($variants, $json_flags);
    $inset_safelist_json = json_encode($inset_safelist, $json_flags);

    return <<<JS
export default {
  content: [
    '../../themes/logical-theme/*.php',
    '../../themes/logical-theme/templates/**/*.php',
    '../../themes/logical-theme/template-parts/**/*.php',
    '../../themes/logical-theme/src/**/*.{js,jsx}'
  ],
  safelist: [
    {
      pattern: new RegExp('^({$utility_pattern})-({$color_pattern})(-({$shade_pattern}))?$'),
      variants: {$variants_json}
    },
    {
      pattern: new RegExp('^text-(xs|sm|base|lg|xl|2xl|3xl|4xl|5xl|6xl|7xl|8xl|9xl)$'),
      variants: {$variants_json}
    },
    ...{$inset_safelist_json}
  ],
  theme: {
    screens: {$screens_json},
    container: {
      screens: {$container_json}
    },
    colors: {$colors_full_json},
    extend: {
      fontSize: {$font_json}
    },
    ringColor: ({ theme }) => ({
      ...theme('colors')
    }),
    ringOffsetColor: ({ theme }) => ({
      ...theme('colors')
    }),
    borderColor: ({ theme }) => ({
      ...theme('colors')
    }),
    outlineColor: ({ theme }) => ({
      ...theme('colors')
    }),
    textColor: ({ theme }) => ({
      ...theme('colors')
    }),
    backgroundColor: ({ theme }) => ({
      ...theme('colors')
    }),
    decorationColor: ({ theme }) => ({
      ...theme('colors')
    }),
    fill: ({ theme }) => ({
      ...theme('colors')
    }),
    stroke: ({ theme }) => ({
      ...theme('colors')
    }),
    caretColor: ({ theme }) => ({
      ...theme('colors')
    }),
    accentColor: ({ theme }) => ({
      ...theme('colors')
    }),
    boxShadowColor: ({ theme }) => ({
      ...theme('colors')
    })
  },
  plugins: [
    function({ matchUtilities, theme }) {
      const flattenColors = (input, prefix = '') => {
        return Object.entries(input || {}).reduce((acc, [key, value]) => {
          const token = prefix ? (prefix + '-' + key) : key;
          if (typeof value === 'string') {
            acc[token] = value;
            return acc;
          }
          if (value && typeof value === 'object') {
            Object.assign(acc, flattenColors(value, token));
          }
          return acc;
        }, {});
      };

      const colorValues = flattenColors(theme('colors'));

      matchUtilities(
        {
          'inset-shadow': (value) => ({
            '--tw-shadow-color': value,
            '--tw-shadow': 'inset 0 2px 4px 0 var(--tw-shadow-color)',
            'box-shadow':
              'var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)'
          })
        },
        {
          values: colorValues,
          type: ['color']
        }
      );

      matchUtilities(
        {
          'inset-ring': (value) => ({
            '--tw-inset-ring-color': value,
            '--tw-inset-ring-shadow': 'inset 0 0 0 1px var(--tw-inset-ring-color)',
            'box-shadow':
              'var(--tw-inset-ring-shadow), var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow, 0 0 #0000)'
          })
        },
        {
          values: colorValues,
          type: ['color']
        }
      );
    }
  ]
};
JS;
}

function lds_tw_minify_css($css)
{
    $css = preg_replace('!/\*.*?\*/!s', '', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/\s*([{}:;,>])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return trim((string) $css);
}

function lds_tw_find_node_command()
{
    $bins = array('/opt/homebrew/bin/node', '/usr/local/bin/node', '/usr/bin/node', 'node');
    foreach ($bins as $bin) {
        if (is_executable($bin)) {
            return escapeshellarg($bin);
        }
    }

    return 'node';
}

function lds_tw_exec_available()
{
    if (!function_exists('exec')) {
        return false;
    }

    $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
    return !in_array('exec', $disabled, true);
}

function lds_tw_run_node_build()
{
    if (!lds_tw_exec_available()) {
        return array(false, 'exec disabled');
    }

    $node = lds_tw_find_node_command();
    $script = escapeshellarg(lds_tw_plugin_dir() . 'scripts/build-from-json.mjs');
    $plugin_dir = escapeshellarg(lds_tw_plugin_dir());
    $theme_dir = escapeshellarg(lds_tw_theme_dir());
    $theme_config = escapeshellarg(lds_tw_theme_config_path());
    $plugin_config = escapeshellarg(lds_tw_plugin_config_path());
    $theme_css = escapeshellarg(lds_tw_theme_output_css_path());
    $theme_css_min = escapeshellarg(lds_tw_theme_output_min_css_path());
    $env_path = 'PATH=/usr/local/bin:/opt/homebrew/bin:/usr/bin:/bin';
    $cmd = $env_path . ' ' . $node . ' ' . $script . ' ' . $plugin_dir . ' ' . $theme_dir . ' ' . $theme_config . ' ' . $plugin_config . ' ' . $theme_css . ' ' . $theme_css_min;

    $lines = array();
    $code = 1;
    @exec($cmd . ' 2>&1', $lines, $code);

    if ($code === 0) {
        $output = implode("\n", $lines);
        $decoded = json_decode($output, true);
        if (is_array($decoded) && isset($decoded['ok']) && $decoded['ok']) {
            return array(true, $decoded);
        }
        return array(true, array(
            'ok' => true,
            'message' => 'Compilation completed using node builder.',
            'compiler_note' => 'tailwindcss',
        ));
    }

    $message = !empty($lines) ? implode("\n", $lines) : 'Tailwind build failed';
    lds_tw_log($message);

    return array(false, $message);
}

function lds_tw_handle_compilation()
{
    if (get_transient(LDS_TW_LOCK_KEY)) {
        throw new RuntimeException('Another LDS Tailwind compilation is already running.');
    }
    set_transient(LDS_TW_LOCK_KEY, 1, 5 * MINUTE_IN_SECONDS);

    $start = microtime(true);

    try {
        list($build_ok, $build_result) = lds_tw_run_node_build();
        if (!$build_ok) {
            throw new RuntimeException((string) $build_result);
        }

        $elapsed = (int) round((microtime(true) - $start) * 1000);
        $message = isset($build_result['message']) ? (string) $build_result['message'] : ('Compilation completed in ' . $elapsed . ' ms using tailwindcss.');
        $compiler_note = isset($build_result['compiler_note']) ? (string) $build_result['compiler_note'] : 'tailwindcss';

        return array(
            'ok' => true,
            'message' => $message,
            'compiler_note' => $compiler_note,
        );
    } finally {
        delete_transient(LDS_TW_LOCK_KEY);
    }
}

function lds_tw_handle_compilation_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized.', 403);
    }

    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'lds_tw_compilation_nonce')) {
        wp_send_json_error('Invalid nonce.', 403);
    }

    try {
        $result = lds_tw_handle_compilation();
        wp_send_json_success($result['message']);
    } catch (Throwable $e) {
        lds_tw_log('Compile error: ' . $e->getMessage());
        wp_send_json_error('Compilation failed: ' . $e->getMessage(), 500);
    }
}
add_action('wp_ajax_lds_tw_compile', 'lds_tw_handle_compilation_ajax');

function lds_tw_add_compile_button($wp_admin_bar)
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $wp_admin_bar->add_node(array(
        'id' => 'compile_lds_tw',
        'title' => 'Compile LDS Tailwind',
        'href' => '#',
        'meta' => array('class' => 'compile-lds-tw'),
    ));
}
add_action('admin_bar_menu', 'lds_tw_add_compile_button', 999);

function lds_tw_enqueue_admin_script($hook_suffix)
{
    if (!current_user_can('manage_options')) {
        return;
    }

    wp_enqueue_script(
        'lds-tw-compile-script',
        lds_tw_plugin_url() . 'admin/compile.js',
        array('jquery'),
        LDS_TW_VERSION,
        true
    );

    wp_localize_script('lds-tw-compile-script', 'ldsTw', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('lds_tw_compilation_nonce'),
    ));
}
add_action('admin_enqueue_scripts', 'lds_tw_enqueue_admin_script');

function lds_tw_enqueue_styles()
{
    $path = lds_tw_theme_output_min_css_path();
    if (!file_exists($path)) {
        return;
    }

    wp_enqueue_style(
        'logical-design-system-tailwind-styles-min',
        lds_tw_theme_url() . '/assets/css/lds-style.min.css',
        array(),
        (string) filemtime($path)
    );
}
add_action('wp_enqueue_scripts', 'lds_tw_enqueue_styles', 20);

function lds_tw_schedule_compilation()
{
    if (!wp_next_scheduled(LDS_TW_CRON_HOOK)) {
        wp_schedule_event(strtotime('04:00:00'), 'daily', LDS_TW_CRON_HOOK);
    }
}
add_action('wp', 'lds_tw_schedule_compilation');

function lds_tw_run_nightly_compilation()
{
    try {
        lds_tw_handle_compilation();
    } catch (Throwable $e) {
        lds_tw_log('Nightly compile error: ' . $e->getMessage());
    }
}
add_action(LDS_TW_CRON_HOOK, 'lds_tw_run_nightly_compilation');

function lds_tw_copy_default_config_to_theme()
{
    $source = lds_tw_plugin_config_path();
    $destination = lds_tw_theme_config_path();
    $destination_dir = dirname($destination);

    if (!is_dir($destination_dir)) {
        wp_mkdir_p($destination_dir);
    }

    if (!file_exists($destination) && file_exists($source)) {
        copy($source, $destination);
    }
}

function lds_tw_activate_plugin()
{
    lds_tw_copy_default_config_to_theme();
    lds_tw_schedule_compilation();
}
register_activation_hook(__FILE__, 'lds_tw_activate_plugin');

function lds_tw_deactivate_plugin()
{
    $timestamp = wp_next_scheduled(LDS_TW_CRON_HOOK);
    if ($timestamp) {
        wp_unschedule_event($timestamp, LDS_TW_CRON_HOOK);
    }
}
register_deactivation_hook(__FILE__, 'lds_tw_deactivate_plugin');
