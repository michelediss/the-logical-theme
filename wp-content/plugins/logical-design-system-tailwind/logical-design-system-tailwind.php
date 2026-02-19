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
    return lds_tw_theme_dir() . '/theme.json';
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

    $palette_colors = array();
    foreach ($palette as $name => $color) {
        $normalized = lds_tw_color_normalize_hex($color);
        if ($normalized === null) {
            continue;
        }
        $palette_colors[$name] = $normalized;
    }

    foreach ($palette_colors as $name => $value) {
        $css[] = '  --' . $name . ': ' . $value . ';';
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

function lds_tw_build_palette_colors($data)
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

    $palette_colors = array();

    foreach ($palette as $name => $color) {
        $normalized = lds_tw_color_normalize_hex($color);
        if ($normalized === null) {
            continue;
        }
        $palette_colors[(string) $name] = $normalized;
    }

    return $palette_colors;
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

    $colors = lds_tw_build_palette_colors($data);
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
    $utility_pattern = 'bg|text|decoration|border|outline|shadow|inset-shadow|ring|inset-ring|accent|caret|fill|stroke';

    $variants = array_keys($screens);
    $inset_safelist = array();
    foreach ($color_names as $name) {
        $base_inset_shadow = 'inset-shadow-' . $name;
        $base_inset_ring = 'inset-ring-' . $name;
        $inset_safelist[] = $base_inset_shadow;
        $inset_safelist[] = $base_inset_ring;
        foreach ($variants as $variant) {
            $inset_safelist[] = $variant . ':' . $base_inset_shadow;
            $inset_safelist[] = $variant . ':' . $base_inset_ring;
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
      pattern: new RegExp('^({$utility_pattern})-({$color_pattern})$'),
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
    $theme_css = escapeshellarg(lds_tw_theme_output_css_path());
    $theme_css_min = escapeshellarg(lds_tw_theme_output_min_css_path());
    $env_path = 'PATH=/usr/local/bin:/opt/homebrew/bin:/usr/bin:/bin';
    $cmd = $env_path . ' ' . $node . ' ' . $script . ' ' . $plugin_dir . ' ' . $theme_dir . ' ' . $theme_config . ' ' . $theme_css . ' ' . $theme_css_min;

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

function lds_tw_register_rest_routes()
{
    register_rest_route('lds-tw/v1', '/tokens', array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'callback' => function () {
                try {
                    $theme_json = lds_tw_read_theme_json();
                    $state = lds_tw_theme_json_to_ui_state($theme_json);
                    return rest_ensure_response(array(
                        'state' => $state,
                    ));
                } catch (Throwable $e) {
                    return new WP_Error('lds_tw_tokens_read_failed', $e->getMessage(), array('status' => 500));
                }
            },
        ),
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'callback' => function (WP_REST_Request $request) {
                try {
                    $payload = $request->get_json_params();
                    $theme_json = lds_tw_read_theme_json();
                    $next = lds_tw_apply_ui_state_to_theme_json($theme_json, $payload);
                    lds_tw_write_theme_json($next);

                    return rest_ensure_response(array(
                        'ok' => true,
                        'state' => lds_tw_theme_json_to_ui_state($next),
                    ));
                } catch (Throwable $e) {
                    lds_tw_log('Save via REST error: ' . $e->getMessage());
                    return new WP_Error('lds_tw_tokens_save_failed', $e->getMessage(), array('status' => 400));
                }
            },
        ),
    ));
}
add_action('rest_api_init', 'lds_tw_register_rest_routes');

function lds_tw_default_theme_json()
{
    return array(
        '$schema' => 'https://schemas.wp.org/trunk/theme.json',
        'version' => 3,
        'settings' => array(
            'color' => array(
                'palette' => array(),
                'defaultPalette' => false,
            ),
            'layout' => array(
                'contentSize' => '1140px',
                'wideSize' => '1440px',
            ),
            'typography' => array(),
            'custom' => array(
                'lds' => array(
                    'breakpoints' => array(
                        'null' => 0,
                        'sm' => '576px',
                        'md' => '768px',
                        'lg' => '1024px',
                        'xl' => '1280px',
                        '2xl' => '1600px',
                        '3xl' => '1920px',
                        '4xl' => '2560px',
                        '5xl' => '3840px',
                    ),
                    'containerMaxWidths' => array(
                        'sm' => '540px',
                        'md' => '720px',
                        'lg' => '960px',
                        'xl' => '1140px',
                        '2xl' => '1440px',
                        '3xl' => '1680px',
                        '4xl' => '1920px',
                        '5xl' => '2560px',
                    ),
                    'baseSettings' => array(
                        'baseSize' => 16,
                        'r' => 1.2,
                        'incrementFactor' => 1.01,
                    ),
                ),
            ),
        ),
        'styles' => array(
            'typography' => array(
                'fontSize' => '16px',
            ),
        ),
    );
}

function lds_tw_read_theme_json()
{
    $path = lds_tw_theme_config_path();
    if (!file_exists($path)) {
        return lds_tw_default_theme_json();
    }

    $raw = file_get_contents($path);
    $data = json_decode((string) $raw, true);
    if (!is_array($data)) {
        throw new RuntimeException('Invalid theme.json: ' . json_last_error_msg());
    }

    return $data;
}

function lds_tw_font_pairing_dir()
{
    return WP_PLUGIN_DIR . '/logical-design-system/scss/font-pairing-list';
}

function lds_tw_font_name_from_token($token)
{
    $raw = trim(str_replace('_', ' ', (string) $token));
    return preg_replace('/\s+/', ' ', $raw);
}

function lds_tw_font_slug($name, $fallback = 'secondary')
{
    $slug = sanitize_title($name);
    return $slug !== '' ? $slug : $fallback;
}

function lds_tw_get_font_pairings()
{
    $dir = lds_tw_font_pairing_dir();
    if (!is_dir($dir)) {
        return array();
    }

    $files = glob($dir . '/*.scss');
    if (!is_array($files)) {
        return array();
    }

    $pairings = array();
    foreach ($files as $file) {
        $base = basename($file);
        if (!preg_match('/^_(.+)_\+_(.+)\.scss$/', $base, $matches)) {
            continue;
        }

        $first = lds_tw_font_name_from_token($matches[1]);
        $second = lds_tw_font_name_from_token($matches[2]);
        $id = $base;
        $pairings[] = array(
            'id' => $id,
            'label' => $first . ' + ' . $second,
            'first' => $first,
            'second' => $second,
            'import' => 'font-pairing-list/' . substr($base, 0, -5),
        );
    }

    usort($pairings, function ($a, $b) {
        return strcmp($a['label'], $b['label']);
    });

    return $pairings;
}

function lds_tw_detect_selected_pairing($theme_json, $pairings)
{
    $families = isset($theme_json['settings']['typography']['fontFamilies']) && is_array($theme_json['settings']['typography']['fontFamilies'])
        ? $theme_json['settings']['typography']['fontFamilies']
        : array();

    if (count($families) < 2) {
        return isset($pairings[0]['id']) ? $pairings[0]['id'] : '';
    }

    $first = isset($families[0]['name']) ? (string) $families[0]['name'] : '';
    $second = isset($families[1]['name']) ? (string) $families[1]['name'] : '';
    $needle = '_' . str_replace(' ', '_', $first) . '_+_' . str_replace(' ', '_', $second) . '.scss';

    foreach ($pairings as $pairing) {
        if ($pairing['id'] === $needle) {
            return $pairing['id'];
        }
    }

    return isset($pairings[0]['id']) ? $pairings[0]['id'] : '';
}

function lds_tw_theme_json_to_ui_state($theme_json)
{
    $palette_entries = isset($theme_json['settings']['color']['palette']) && is_array($theme_json['settings']['color']['palette'])
        ? $theme_json['settings']['color']['palette']
        : array();

    $palette = array();
    foreach ($palette_entries as $entry) {
        if (!is_array($entry)) {
            continue;
        }
        $slug = isset($entry['slug']) ? sanitize_title($entry['slug']) : '';
        $color = isset($entry['color']) ? lds_tw_color_normalize_hex($entry['color']) : null;
        if ($slug === '' || $color === null) {
            continue;
        }
        $palette[] = array('slug' => $slug, 'color' => $color);
    }

    if (empty($palette)) {
        $palette = array(
            array('slug' => 'black', 'color' => '#1e201f'),
            array('slug' => 'white', 'color' => '#fcfcfe'),
            array('slug' => 'primary', 'color' => '#f05252'),
            array('slug' => 'secondary', 'color' => '#c27803'),
        );
    }

    $breakpoints = isset($theme_json['settings']['custom']['lds']['breakpoints']) && is_array($theme_json['settings']['custom']['lds']['breakpoints'])
        ? $theme_json['settings']['custom']['lds']['breakpoints']
        : array();
    $containers = isset($theme_json['settings']['custom']['lds']['containerMaxWidths']) && is_array($theme_json['settings']['custom']['lds']['containerMaxWidths'])
        ? $theme_json['settings']['custom']['lds']['containerMaxWidths']
        : array();
    $base_settings = isset($theme_json['settings']['custom']['lds']['baseSettings']) && is_array($theme_json['settings']['custom']['lds']['baseSettings'])
        ? $theme_json['settings']['custom']['lds']['baseSettings']
        : array();

    $pairings = lds_tw_get_font_pairings();
    $selected_pairing = lds_tw_detect_selected_pairing($theme_json, $pairings);
    $content_size = isset($containers['xl']) ? (string) $containers['xl'] : '1140px';
    $wide_size = isset($containers['2xl']) ? (string) $containers['2xl'] : $content_size;

    return array(
        'palette' => array_values($palette),
        'breakpoints' => $breakpoints,
        'containerMaxWidths' => $containers,
        'layout' => array(
            'contentSize' => $content_size,
            'wideSize' => $wide_size,
        ),
        'baseSettings' => array(
            'baseSize' => isset($base_settings['baseSize']) ? (float) $base_settings['baseSize'] : 16.0,
            'r' => isset($base_settings['r']) ? (float) $base_settings['r'] : 1.2,
            'incrementFactor' => isset($base_settings['incrementFactor']) ? (float) $base_settings['incrementFactor'] : 1.01,
        ),
        'fontPairing' => $selected_pairing,
        'fontPairings' => $pairings,
    );
}

function lds_tw_apply_ui_state_to_theme_json($theme_json, $state)
{
    if (!is_array($state)) {
        throw new RuntimeException('Invalid payload.');
    }

    $palette_rows = isset($state['palette']) && is_array($state['palette']) ? $state['palette'] : array();
    $palette = array();
    $seen = array();
    foreach ($palette_rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $slug = isset($row['slug']) ? sanitize_title((string) $row['slug']) : '';
        $color = isset($row['color']) ? lds_tw_color_normalize_hex($row['color']) : null;
        if ($slug === '' || $color === null || isset($seen[$slug])) {
            continue;
        }
        $seen[$slug] = true;
        $palette[] = array(
            'slug' => $slug,
            'name' => ucwords(str_replace(array('-', '_'), ' ', $slug)),
            'color' => $color,
        );
    }

    if (empty($palette)) {
        throw new RuntimeException('Palette cannot be empty.');
    }

    $breakpoints = isset($state['breakpoints']) && is_array($state['breakpoints']) ? $state['breakpoints'] : array();
    $containers = isset($state['containerMaxWidths']) && is_array($state['containerMaxWidths']) ? $state['containerMaxWidths'] : array();
    $base_settings = isset($state['baseSettings']) && is_array($state['baseSettings']) ? $state['baseSettings'] : array();
    $base_size = isset($base_settings['baseSize']) ? (float) $base_settings['baseSize'] : 16.0;
    $ratio = isset($base_settings['r']) ? (float) $base_settings['r'] : 1.2;
    $increment = isset($base_settings['incrementFactor']) ? (float) $base_settings['incrementFactor'] : 1.01;

    if ($base_size <= 0 || $ratio <= 0 || $increment <= 0) {
        throw new RuntimeException('baseSize, r and incrementFactor must be positive numbers.');
    }

    $pairings = lds_tw_get_font_pairings();
    $pairing_id = isset($state['fontPairing']) ? (string) $state['fontPairing'] : '';
    $pairing = null;
    foreach ($pairings as $candidate) {
        if ($candidate['id'] === $pairing_id) {
            $pairing = $candidate;
            break;
        }
    }

    $font_families = isset($theme_json['settings']['typography']['fontFamilies']) && is_array($theme_json['settings']['typography']['fontFamilies'])
        ? $theme_json['settings']['typography']['fontFamilies']
        : array();

    if (!empty($pairings)) {
        if ($pairing === null) {
            $pairing = $pairings[0];
        }

        $font_families = array(
            array(
                'slug' => 'primary',
                'name' => $pairing['first'],
                'fontFamily' => $pairing['first'] . ', ui-sans-serif, system-ui, sans-serif',
            ),
            array(
                'slug' => lds_tw_font_slug($pairing['second'], 'secondary'),
                'name' => $pairing['second'],
                'fontFamily' => $pairing['second'] . ', ui-sans-serif, system-ui, sans-serif',
            ),
        );
    }

    $content_size = isset($containers['xl']) ? trim((string) $containers['xl']) : '1140px';
    $wide_size = isset($containers['2xl']) ? trim((string) $containers['2xl']) : $content_size;
    if ($content_size === '') {
        $content_size = '1140px';
    }
    if ($wide_size === '') {
        $wide_size = $content_size;
    }

    if (!isset($theme_json['settings']) || !is_array($theme_json['settings'])) {
        $theme_json['settings'] = array();
    }
    if (!isset($theme_json['settings']['color']) || !is_array($theme_json['settings']['color'])) {
        $theme_json['settings']['color'] = array();
    }
    if (!isset($theme_json['settings']['layout']) || !is_array($theme_json['settings']['layout'])) {
        $theme_json['settings']['layout'] = array();
    }
    if (!isset($theme_json['settings']['typography']) || !is_array($theme_json['settings']['typography'])) {
        $theme_json['settings']['typography'] = array();
    }
    if (!isset($theme_json['settings']['custom']) || !is_array($theme_json['settings']['custom'])) {
        $theme_json['settings']['custom'] = array();
    }
    if (!isset($theme_json['settings']['custom']['lds']) || !is_array($theme_json['settings']['custom']['lds'])) {
        $theme_json['settings']['custom']['lds'] = array();
    }
    if (!isset($theme_json['styles']) || !is_array($theme_json['styles'])) {
        $theme_json['styles'] = array();
    }
    if (!isset($theme_json['styles']['typography']) || !is_array($theme_json['styles']['typography'])) {
        $theme_json['styles']['typography'] = array();
    }

    $theme_json['settings']['color']['palette'] = $palette;
    $theme_json['settings']['color']['defaultPalette'] = false;
    $theme_json['settings']['layout']['contentSize'] = $content_size;
    $theme_json['settings']['layout']['wideSize'] = $wide_size;
    $theme_json['settings']['typography']['fontFamilies'] = $font_families;
    $theme_json['settings']['custom']['lds']['breakpoints'] = $breakpoints;
    $theme_json['settings']['custom']['lds']['containerMaxWidths'] = $containers;
    $theme_json['settings']['custom']['lds']['baseSettings'] = array(
        'baseSize' => $base_size,
        'r' => $ratio,
        'incrementFactor' => $increment,
    );
    $theme_json['styles']['typography']['fontFamily'] = 'var(--wp--preset--font-family--primary)';

    return $theme_json;
}

function lds_tw_write_theme_json($theme_json)
{
    $encoded = wp_json_encode($theme_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($encoded) || $encoded === '') {
        throw new RuntimeException('Could not encode theme.json.');
    }

    $result = file_put_contents(lds_tw_theme_config_path(), $encoded . "\n");
    if ($result === false) {
        throw new RuntimeException('Could not write theme.json.');
    }
}

function lds_tw_render_theme_tokens_page()
{
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized', 403);
    }
    ?>
    <div class="wrap">
      <h1>LDS Theme Tokens</h1>
      <p>Global token settings with live preview and asynchronous compile.</p>
      <div id="lds-tw-theme-tokens-root"></div>
    </div>
    <?php
}

function lds_tw_register_theme_tokens_page()
{
    add_theme_page(
        'LDS Theme Tokens',
        'LDS Theme Tokens',
        'manage_options',
        'lds-tw-theme-tokens',
        'lds_tw_render_theme_tokens_page'
    );
}
add_action('admin_menu', 'lds_tw_register_theme_tokens_page');

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

    if ($hook_suffix === 'appearance_page_lds-tw-theme-tokens') {
        $admin_ui_path = lds_tw_plugin_dir() . 'admin/theme-tokens-ui.js';
        wp_enqueue_script(
            'lds-tw-theme-tokens-admin',
            lds_tw_plugin_url() . 'admin/theme-tokens-ui.js',
            array(),
            file_exists($admin_ui_path) ? (string) filemtime($admin_ui_path) : LDS_TW_VERSION,
            true
        );
        wp_localize_script('lds-tw-theme-tokens-admin', 'ldsTwTokensUi', array(
            'restBase' => esc_url_raw(rest_url('lds-tw/v1')),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
    }
}
add_action('admin_enqueue_scripts', 'lds_tw_enqueue_admin_script');

function lds_tw_enqueue_block_editor_assets()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $runtime_preview_path = lds_tw_plugin_dir() . 'admin/theme-preview-runtime.js';
    wp_enqueue_script(
        'lds-tw-theme-preview-runtime',
        lds_tw_plugin_url() . 'admin/theme-preview-runtime.js',
        array(),
        file_exists($runtime_preview_path) ? (string) filemtime($runtime_preview_path) : LDS_TW_VERSION,
        true
    );

    $editor_ui_path = lds_tw_plugin_dir() . 'admin/theme-tokens-editor.js';
    wp_enqueue_script(
        'lds-tw-theme-tokens-editor',
        lds_tw_plugin_url() . 'admin/theme-tokens-editor.js',
        array('wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-api-fetch', 'wp-data', 'lds-tw-theme-preview-runtime'),
        file_exists($editor_ui_path) ? (string) filemtime($editor_ui_path) : LDS_TW_VERSION,
        true
    );

    wp_localize_script('lds-tw-theme-tokens-editor', 'ldsTwTokensEditor', array(
        'restBase' => esc_url_raw(rest_url('lds-tw/v1')),
        'nonce' => wp_create_nonce('wp_rest'),
    ));
}
add_action('enqueue_block_editor_assets', 'lds_tw_enqueue_block_editor_assets');

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

function lds_tw_activate_plugin()
{
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
