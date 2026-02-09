<?php
/*
Plugin Name: Logical Design System Preview
Description: Live preview offcanvas editor for Logical Design System JSON with apply + build pipeline integration.
Version: 0.1.0
Author: Michele Paolino
*/

if (!defined('ABSPATH')) {
    exit;
}

const LDS_PREVIEW_NONCE = 'lds_preview_nonce';

function lds_preview_can_use_editor() {
    return current_user_can('manage_options');
}

function lds_preview_theme_json_path() {
    return trailingslashit(get_stylesheet_directory()) . 'assets/scss/lds-input.json';
}

function lds_preview_plugin_json_path() {
    return trailingslashit(WP_PLUGIN_DIR) . 'logical-design-system/scss/input/lds-input.json';
}

function lds_preview_read_json_file($path) {
    if (!file_exists($path)) {
        return null;
    }

    $json = file_get_contents($path);
    if ($json === false) {
        return null;
    }

    $decoded = json_decode($json, true);
    if (!is_array($decoded)) {
        return null;
    }

    return $decoded;
}

function lds_preview_get_current_config() {
    $theme = lds_preview_read_json_file(lds_preview_theme_json_path());
    if (is_array($theme)) {
        return $theme;
    }

    $plugin = lds_preview_read_json_file(lds_preview_plugin_json_path());
    if (is_array($plugin)) {
        return $plugin;
    }

    return array();
}

function lds_preview_get_font_pairings() {
    $dir = trailingslashit(WP_PLUGIN_DIR) . 'logical-design-system/scss/font-pairing-list/';
    if (!is_dir($dir)) {
        return array();
    }

    $pairs = array();
    $files = glob($dir . '_*.scss');
    if (!is_array($files)) {
        return array();
    }

    sort($files);
    foreach ($files as $file) {
        $base = basename($file, '.scss'); // e.g. _Titillium_Web_+_Dosis
        $import = 'font-pairing-list/' . $base;
        $label = str_replace('_', ' ', ltrim($base, '_'));
        $imports = array();
        $classes = array();

        $content = file_get_contents($file);
        if (is_string($content) && $content !== '') {
            if (preg_match_all("/@import\\s+url\\((['\"]?)([^'\")]+)\\1\\)\\s*;/i", $content, $import_matches, PREG_SET_ORDER)) {
                foreach ($import_matches as $m) {
                    $url = trim($m[2]);
                    if (preg_match('/^https?:\\/\\//i', $url) && stripos($url, 'No font link available') === false) {
                        $imports[] = $url;
                    }
                }
            }

            if (preg_match_all('/([^\\{\\}]+)\\{([^\\}]*)\\}/m', $content, $rule_matches, PREG_SET_ORDER)) {
                foreach ($rule_matches as $rule) {
                    $selector = trim($rule[1]);
                    $body = trim($rule[2]);
                    if ($selector === '' || $body === '') {
                        continue;
                    }
                    $declarations = array();
                    $lines = explode(';', $body);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if ($line === '' || strpos($line, ':') === false) {
                            continue;
                        }
                        list($prop, $val) = array_map('trim', explode(':', $line, 2));
                        if ($prop !== '' && $val !== '') {
                            $declarations[$prop] = $val;
                        }
                    }
                    if (!empty($declarations)) {
                        $classes[$selector] = $declarations;
                    }
                }
            }
        }

        $pairs[] = array(
            'value' => $import,
            'label' => $label,
            'imports' => $imports,
            'classes' => $classes,
        );
    }

    return $pairs;
}

function lds_preview_validate_config($config, $path_label) {
    if (!is_array($config)) {
        return new WP_Error('invalid_config', 'Configurazione non valida: root JSON non oggetto.');
    }

    if (function_exists('lds_validate_input_json')) {
        try {
            lds_validate_input_json($config, $path_label);
        } catch (\Throwable $e) {
            return new WP_Error('invalid_config', $e->getMessage());
        }
    }

    return true;
}

function lds_preview_save_theme_config($config) {
    $theme_json_path = lds_preview_theme_json_path();
    $theme_scss_dir = dirname($theme_json_path);

    if (!is_dir($theme_scss_dir) && !wp_mkdir_p($theme_scss_dir)) {
        return new WP_Error('mkdir_failed', 'Impossibile creare directory assets/scss del child theme.');
    }

    $encoded = wp_json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (!is_string($encoded)) {
        return new WP_Error('encode_failed', 'Impossibile serializzare JSON.');
    }

    $written = file_put_contents($theme_json_path, $encoded . "\n");
    if ($written === false) {
        return new WP_Error('write_failed', 'Impossibile salvare lds-input.json nel child theme.');
    }

    return true;
}

function lds_preview_minify_css_local($css) {
    $css = preg_replace('!/\*.*?\*/!s', '', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/\s*([{}:;,>])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return trim((string)$css);
}

function lds_preview_build_with_lds_pipeline() {
    if (!function_exists('lds_read_json') || !function_exists('lds_generate_input_scss') || !function_exists('lds_generate_font_scss') || !function_exists('lds_compile_scss')) {
        return new WP_Error('lds_missing', 'Plugin Logical Design System non attivo o API build non disponibili.');
    }

    $plugin_sass_path = trailingslashit(WP_PLUGIN_DIR) . 'logical-design-system/scss/';
    $theme_sass_path = trailingslashit(get_stylesheet_directory()) . 'assets/scss/';
    $bootstrap_sass_path = $plugin_sass_path . 'bootstrap/scss/';
    $main_scss_path = $plugin_sass_path . 'main.scss';

    if (!file_exists($main_scss_path)) {
        return new WP_Error('missing_main_scss', 'main.scss non trovato nel plugin Logical Design System.');
    }

    try {
        $input_theme_path = $theme_sass_path . 'lds-input.json';
        $input_plugin_path = $plugin_sass_path . 'input/lds-input.json';
        $input_json = lds_read_json($input_theme_path, $input_plugin_path);

        $validation = lds_preview_validate_config($input_json, file_exists($input_theme_path) ? $input_theme_path : $input_plugin_path);
        if (is_wp_error($validation)) {
            return $validation;
        }

        $generated_scss = lds_generate_input_scss($input_json) . "\n" . lds_generate_font_scss($input_json);
        $main_scss = $generated_scss . "\n" . file_get_contents($main_scss_path);

        $compile_result = lds_compile_scss($main_scss, array($theme_sass_path, $plugin_sass_path, $bootstrap_sass_path));
        if (!is_array($compile_result) || !isset($compile_result['css'])) {
            return new WP_Error('compile_failed', 'Compilazione LDS non riuscita.');
        }

        $compiled_css = (string)$compile_result['css'];

        $plugin_css_files = get_option('lds_plugin_css_files', array());
        $plugins_css = '';

        if (is_array($plugin_css_files)) {
            foreach ($plugin_css_files as $css_file) {
                if (!is_array($css_file) || empty($css_file['src'])) {
                    continue;
                }

                $src = $css_file['src'];
                $css_path = strpos($src, 'http') !== false
                    ? str_replace(site_url(), ABSPATH, $src)
                    : ABSPATH . ltrim($src, '/');

                if (file_exists($css_path)) {
                    $content = file_get_contents($css_path);
                    if ($content !== false) {
                        $plugins_css .= $content . "\n";
                    }
                }
            }
        }

        $output_css_dir = trailingslashit(get_stylesheet_directory()) . 'assets/css/';
        if (!is_dir($output_css_dir) && !wp_mkdir_p($output_css_dir)) {
            return new WP_Error('output_dir_failed', 'Impossibile creare directory assets/css del child theme.');
        }

        $final_css = $compiled_css . "\n" . $plugins_css;

        $compiled_css_path = $output_css_dir . 'lds-style.css';
        if (file_put_contents($compiled_css_path, $final_css) === false) {
            return new WP_Error('write_css_failed', 'Impossibile scrivere lds-style.css.');
        }

        $minifier = function_exists('lds_minify_css') ? 'lds_minify_css' : 'lds_preview_minify_css_local';
        $minified = call_user_func($minifier, $final_css);
        $minified_css_path = $output_css_dir . 'lds-style.min.css';
        if (file_put_contents($minified_css_path, $minified) === false) {
            return new WP_Error('write_min_failed', 'Impossibile scrivere lds-style.min.css.');
        }

        $message = sprintf(
            'Build completata in %d ms con %s.',
            isset($compile_result['time_ms']) ? (int)$compile_result['time_ms'] : 0,
            isset($compile_result['compiler']) ? (string)$compile_result['compiler'] : 'compiler sconosciuto'
        );

        if (!empty($compile_result['note'])) {
            $message .= ' ' . $compile_result['note'];
        }

        return array(
            'message' => $message,
            'css_path' => $compiled_css_path,
            'min_path' => $minified_css_path,
        );
    } catch (\Throwable $e) {
        return new WP_Error('build_exception', $e->getMessage());
    }
}

function lds_preview_enqueue_assets() {
    if (is_admin() || !lds_preview_can_use_editor()) {
        return;
    }

    wp_enqueue_style(
        'logical-design-system-preview',
        plugin_dir_url(__FILE__) . 'assets/lds-preview.css',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/lds-preview.css')
    );

    wp_enqueue_script(
        'logical-design-system-preview',
        plugin_dir_url(__FILE__) . 'assets/lds-preview.js',
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/lds-preview.js'),
        true
    );

    wp_localize_script('logical-design-system-preview', 'ldsPreviewConfig', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce(LDS_PREVIEW_NONCE),
        'pipelineReady' => function_exists('lds_compile_scss') && function_exists('lds_generate_input_scss'),
        'initialConfig' => lds_preview_get_current_config(),
        'fontPairs' => lds_preview_get_font_pairings(),
    ));
}
add_action('wp_enqueue_scripts', 'lds_preview_enqueue_assets', 1001);

function lds_preview_ajax_get_config() {
    if (!lds_preview_can_use_editor()) {
        wp_send_json_error('Permessi insufficienti.', 403);
    }

    check_ajax_referer(LDS_PREVIEW_NONCE, 'nonce');

    wp_send_json_success(array(
        'config' => lds_preview_get_current_config(),
    ));
}
add_action('wp_ajax_lds_preview_get_config', 'lds_preview_ajax_get_config');

function lds_preview_ajax_apply() {
    if (!lds_preview_can_use_editor()) {
        wp_send_json_error('Permessi insufficienti.', 403);
    }

    check_ajax_referer(LDS_PREVIEW_NONCE, 'nonce');

    $raw = isset($_POST['config']) ? wp_unslash($_POST['config']) : '';
    $config = json_decode((string)$raw, true);

    $validation = lds_preview_validate_config($config, 'live-editor');
    if (is_wp_error($validation)) {
        wp_send_json_error($validation->get_error_message(), 400);
    }

    $saved = lds_preview_save_theme_config($config);
    if (is_wp_error($saved)) {
        wp_send_json_error($saved->get_error_message(), 500);
    }

    $build = lds_preview_build_with_lds_pipeline();
    if (is_wp_error($build)) {
        wp_send_json_error($build->get_error_message(), 500);
    }

    wp_send_json_success($build);
}
add_action('wp_ajax_lds_preview_apply', 'lds_preview_ajax_apply');
