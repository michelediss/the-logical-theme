<?php
/** 
* Plugin Name: Logical Design System
* Description: Finally a logical design system: color palette & typographic scale generator + 50 Google font pairings
* Version: 0.1.0
* Plugin URI: https://github.com/michelediss/the-logical-theme
* Author: Michele Paolino
* Author URI: https://michelepaolino.com
*/

// Include the SCSSPHP library using the correct path
require_once plugin_dir_path(__FILE__) . 'libs/scssphp/scss.inc.php';

use ScssPhp\ScssPhp\Compiler;
function lds_read_json($theme_path, $plugin_path) {
    $path = file_exists($theme_path) ? $theme_path : $plugin_path;
    if (!file_exists($path)) {
        throw new \RuntimeException('JSON config not found: ' . $path);
    }
    $json = file_get_contents($path);
    $data = json_decode($json, true);
    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        throw new \RuntimeException('Invalid JSON in ' . $path . ': ' . json_last_error_msg());
    }
    return $data;
}

function lds_scss_key($key) {
    if ($key === 'null') {
        return 'null';
    }
    if (preg_match('/^[a-zA-Z_-][a-zA-Z0-9_-]*$/', $key)) {
        return $key;
    }
    return '"' . str_replace('"', '\"', $key) . '"';
}

function lds_scss_value($value) {
    if (is_array($value)) {
        return lds_scss_map($value);
    }
    if (is_int($value) || is_float($value)) {
        return $value;
    }
    if ($value === null) {
        return 'null';
    }
    if ($value === 'null') {
        return 'null';
    }
    if (is_string($value)) {
        if (preg_match('/^-?\d+(\.\d+)?(px|rem|em|%|vh|vw)?$/', $value)) {
            return $value;
        }
        if (preg_match('/^#([0-9a-fA-F]{3,8})$/', $value)) {
            return $value;
        }
        if (preg_match('/^(rgb|rgba|hsl|hsla)\(.+\)$/', $value)) {
            return $value;
        }
        if (preg_match('/^[a-zA-Z_-][a-zA-Z0-9_-]*$/', $value)) {
            return $value;
        }
        if (strpos($value, '"') !== false || strpos($value, "'") !== false) {
            return $value;
        }
        if (strpos($value, ',') !== false) {
            return $value;
        }
        if (preg_match('/^[a-zA-Z_-][a-zA-Z0-9_-]*\\(.+\\)$/', $value)) {
            return $value;
        }
    }
    return '"' . str_replace('"', '\"', (string)$value) . '"';
}

function lds_scss_map($map) {
    $lines = array();
    foreach ($map as $k => $v) {
        $lines[] = '  ' . lds_scss_key((string)$k) . ': ' . lds_scss_value($v);
    }
    return "(\n" . implode(",\n", $lines) . "\n)";
}

function lds_validate_input_json($data, $path) {
    if (!is_array($data)) {
        throw new \RuntimeException('Invalid JSON structure in ' . $path . ': root must be an object.');
    }
    if (isset($data['baseSettings']) && !is_array($data['baseSettings'])) {
        throw new \RuntimeException('Invalid baseSettings in ' . $path . ': must be an object.');
    }
    if (isset($data['baseSettings']) && is_array($data['baseSettings'])) {
        $is_flat = isset($data['baseSettings']['baseSize']) || isset($data['baseSettings']['r']) || isset($data['baseSettings']['incrementFactor']);
        if ($is_flat) {
            if (isset($data['baseSettings']['baseSize']) && !is_numeric($data['baseSettings']['baseSize'])) {
                throw new \RuntimeException('Invalid baseSettings.baseSize in ' . $path . ': must be numeric.');
            }
            if (isset($data['baseSettings']['r']) && !is_numeric($data['baseSettings']['r'])) {
                throw new \RuntimeException('Invalid baseSettings.r in ' . $path . ': must be numeric.');
            }
            if (isset($data['baseSettings']['incrementFactor']) && !is_numeric($data['baseSettings']['incrementFactor'])) {
                throw new \RuntimeException('Invalid baseSettings.incrementFactor in ' . $path . ': must be numeric.');
            }
        }
    }
    if (isset($data['baseColors']) && !is_array($data['baseColors'])) {
        throw new \RuntimeException('Invalid baseColors in ' . $path . ': must be an object.');
    }
    if (isset($data['bootstrap']) && !is_array($data['bootstrap'])) {
        throw new \RuntimeException('Invalid bootstrap in ' . $path . ': must be an object.');
    }
    if (isset($data['bootstrap']['themeColors']) && !is_array($data['bootstrap']['themeColors'])) {
        throw new \RuntimeException('Invalid bootstrap.themeColors in ' . $path . ': must be an object.');
    }
    if (isset($data['colorVariations']) && !is_array($data['colorVariations'])) {
        throw new \RuntimeException('Invalid colorVariations in ' . $path . ': must be an object.');
    }
    if (isset($data['breakpoints']) && !is_array($data['breakpoints'])) {
        throw new \RuntimeException('Invalid breakpoints in ' . $path . ': must be an object.');
    }
    if (isset($data['containerMaxWidths']) && !is_array($data['containerMaxWidths'])) {
        throw new \RuntimeException('Invalid containerMaxWidths in ' . $path . ': must be an object.');
    }
    if (isset($data['font']) && !is_array($data['font'])) {
        throw new \RuntimeException('Invalid font in ' . $path . ': must be an object.');
    }
    if (isset($data['font']['imports']) && !is_array($data['font']['imports'])) {
        throw new \RuntimeException('Invalid font.imports in ' . $path . ': must be an array.');
    }
    if (isset($data['font']['classes']) && !is_array($data['font']['classes'])) {
        throw new \RuntimeException('Invalid font.classes in ' . $path . ': must be an object.');
    }
    if (isset($data['rounded']) && !is_bool($data['rounded']) && !is_int($data['rounded'])) {
        throw new \RuntimeException('Invalid rounded in ' . $path . ': must be a boolean or 0/1.');
    }
}

function lds_generate_input_scss($data) {
    $out = array();

    if (isset($data['baseSettings'])) {
        $base = $data['baseSettings'];
        $flat = array();

        // New shape: baseSettings = { baseSize, r, incrementFactor }.
        if (isset($base['baseSize']) || isset($base['r']) || isset($base['incrementFactor'])) {
            if (isset($base['baseSize'])) {
                $flat['baseSize'] = $base['baseSize'];
            }
            if (isset($base['r'])) {
                $flat['r'] = $base['r'];
            }
            if (isset($base['incrementFactor'])) {
                $flat['incrementFactor'] = $base['incrementFactor'];
            }
        // Legacy shape fallback: use paragraph as canonical global scale.
        } elseif (isset($base['paragraph']) && is_array($base['paragraph'])) {
            if (isset($base['paragraph']['baseSize'])) {
                $flat['baseSize'] = $base['paragraph']['baseSize'];
            }
            if (isset($base['paragraph']['r'])) {
                $flat['r'] = $base['paragraph']['r'];
            }
            if (isset($base['paragraph']['incrementFactor'])) {
                $flat['incrementFactor'] = $base['paragraph']['incrementFactor'];
            }
        }

        if (!isset($flat['baseSize'])) {
            $flat['baseSize'] = 16;
        }
        if (!isset($flat['r'])) {
            $flat['r'] = 1.2;
        }
        if (!isset($flat['incrementFactor'])) {
            $flat['incrementFactor'] = 1.01;
        }

        $out[] = '$BaseSettings: ' . lds_scss_map($flat) . ';';
    }

    if (isset($data['rounded'])) {
        $rounded = (int) $data['rounded'];
        $out[] = '$lds-rounded: ' . ($rounded === 0 ? '0' : '1') . ';';
        if ($rounded === 0) {
            $out[] = '$border-radius: 0;';
            $out[] = '$border-radius-sm: 0;';
            $out[] = '$border-radius-lg: 0;';
            $out[] = '$border-radius-xl: 0;';
            $out[] = '$border-radius-xxl: 0;';
            $out[] = '$border-radius-pill: 0;';
        }
    }

    if (isset($data['baseColors']['black'])) {
        $out[] = '$black: ' . lds_scss_value($data['baseColors']['black']) . ';';
    }
    if (isset($data['baseColors']['gray'])) {
        $out[] = '$gray: ' . lds_scss_value($data['baseColors']['gray']) . ';';
        // Ensure Bootstrap gray-500 matches the design-system gray
        $out[] = '$gray-500: $gray;';
    }
    if (isset($data['baseColors']['white'])) {
        $out[] = '$white: ' . lds_scss_value($data['baseColors']['white']) . ';';
    }

    if (isset($data['bootstrap']['primary'])) {
        $out[] = '$primary: ' . lds_scss_value($data['bootstrap']['primary']) . ';';
    }
    if (isset($data['bootstrap']['secondary'])) {
        $out[] = '$secondary: ' . lds_scss_value($data['bootstrap']['secondary']) . ';';
    }
    if (isset($data['bootstrap']['gray-500']) && !isset($data['baseColors']['gray'])) {
        $out[] = '$gray-500: ' . lds_scss_value($data['bootstrap']['gray-500']) . ';';
    }
    $base_theme_colors = array();
    if (isset($data['bootstrap']['primary'])) {
        $base_theme_colors['primary'] = '$primary';
    }
    if (isset($data['bootstrap']['secondary'])) {
        $base_theme_colors['secondary'] = '$secondary';
    }
    if (isset($data['baseColors']['black'])) {
        $base_theme_colors['black'] = '$black';
    }
    if (isset($data['baseColors']['white'])) {
        $base_theme_colors['white'] = '$white';
    }
    if (!empty($base_theme_colors)) {
        $lines = array();
        foreach ($base_theme_colors as $k => $v) {
            $lines[] = '  ' . lds_scss_key($k) . ': ' . $v;
        }
        $out[] = '$theme-colors: (' . "\n" . implode(",\n", $lines) . "\n" . ');';
    }
    if (isset($data['bootstrap']['themeColors']) && is_array($data['bootstrap']['themeColors']) && !empty($data['bootstrap']['themeColors'])) {
        $out[] = '$theme-colors: map-merge($theme-colors, ' . lds_scss_map($data['bootstrap']['themeColors']) . ');';
    }

    if (isset($data['colorVariations'])) {
        $out[] = '$my-colors-variations: ' . lds_scss_map($data['colorVariations']) . ';';
    }

    if (isset($data['breakpoints'])) {
        $out[] = '$grid-breakpoints: ' . lds_scss_map($data['breakpoints']) . ';';
    }
    if (isset($data['containerMaxWidths'])) {
        $out[] = '$container-max-widths: ' . lds_scss_map($data['containerMaxWidths']) . ';';
    }

    return implode("\n\n", $out) . "\n";
}

function lds_generate_font_scss($data) {
    $out = array();

    $font = isset($data['font']) && is_array($data['font']) ? $data['font'] : array();

    if (!empty($font['imports']) && is_array($font['imports'])) {
        foreach ($font['imports'] as $import) {
            if (!is_string($import)) {
                continue;
            }
            if (preg_match('/^https?:\\/\\//', $import)) {
                $out[] = "@import url('" . $import . "');";
            } else {
                $out[] = "@import '" . $import . "';";
            }
        }
    }

    if (!empty($font['classes']) && is_array($font['classes'])) {
        foreach ($font['classes'] as $selector => $rules) {
            if (!is_array($rules)) {
                continue;
            }
            $out[] = $selector . ' {';
            foreach ($rules as $prop => $val) {
                $out[] = '  ' . $prop . ': ' . lds_scss_value($val) . ';';
            }
            $out[] = '}';
        }
    }

    return implode("\n", $out) . "\n";
}

// Function to add the "Compile SCSS" button to the wpadminbar
function add_compile_button($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }

    $args = array(
        'id'    => 'compile_scss',
        'title' => 'Compile SCSS',
        'href'  => '#',
        'meta'  => array(
            'class' => 'compile-scss-class',
            'title' => 'Compile SCSS'
        )
    );
    $wp_admin_bar->add_node($args);
}
add_action('admin_bar_menu', 'add_compile_button', 999);

// Function to handle SCSS compilation via AJAX
function handle_scss_compilation_ajax() {
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'scss_compilation_nonce')) {
        $error_message = 'SCSS AJAX nonce failed.';
        wp_send_json_error($error_message, 403);
    }

    handle_scss_compilation();
}
add_action('wp_ajax_compile_scss', 'handle_scss_compilation_ajax');

// Function to handle SCSS compilation and integration of plugin CSS
function handle_scss_compilation() {
    // Define SCSS paths
    $plugin_sass_path    = plugin_dir_path(__FILE__) . 'scss/';
    $theme_sass_path     = get_stylesheet_directory() . '/assets/scss/';
    $theme_json_path     = get_stylesheet_directory() . '/assets/json/';
    $bootstrap_sass_path = $plugin_sass_path . 'bootstrap/scss/';

    // Path to main.scss file in the plugin
    $main_scss_path = $plugin_sass_path . 'main.scss';

    try {
        // Build SCSS from JSON configs (theme overrides plugin defaults)
        $input_theme_path = $theme_json_path . 'lds-input.json';
        $input_plugin_path = $plugin_sass_path . 'input/lds-input.json';
        $input_json = lds_read_json($input_theme_path, $input_plugin_path);
        lds_validate_input_json($input_json, file_exists($input_theme_path) ? $input_theme_path : $input_plugin_path);
        $generated_scss = lds_generate_input_scss($input_json) . "\n" . lds_generate_font_scss($input_json);

        // Read the content of main.scss
        $main_scss = $generated_scss . "\n" . file_get_contents($main_scss_path);

        // Compile SCSS (prefer Dart Sass, fallback to scssphp)
        $compile_result = lds_compile_scss($main_scss, array($theme_sass_path, $plugin_sass_path, $bootstrap_sass_path));
        $compiled_css = $compile_result['css'];

        // Include plugin CSS
        $plugin_css_files = get_option('lds_plugin_css_files', array());

        $plugins_css = '';

        foreach ($plugin_css_files as $css_file) {
            $src = $css_file['src'];

            // Get the physical path of the CSS file
            if (strpos($src, 'http') !== false) {
                $css_path = str_replace(site_url(), ABSPATH, $src);
            } else {
                $css_path = ABSPATH . ltrim($src, '/');
            }

            // Check if the file exists
            if (file_exists($css_path)) {
                // Read the content of the CSS file
                $css_content = file_get_contents($css_path);
                // Add the CSS content to the variable
                $plugins_css .= $css_content . "\n";
            }
        }

        // Destination path for the compiled CSS
        $output_css_dir     = get_stylesheet_directory() . '/assets/css/';
        $compiled_css_path  = $output_css_dir . 'lds-style.css';

        // Create the folder if it doesn't exist
        if (!is_dir($output_css_dir)) {
            mkdir($output_css_dir, 0755, true);
        }

        // Append the plugin CSS to the compiled CSS
        $final_css = $compiled_css . "\n" . $plugins_css;

        // Save the combined CSS to the destination path
        if (file_put_contents($compiled_css_path, $final_css) === false) {
            return;
        }

        // Minify the combined CSS without re-compiling SCSS to avoid long runtimes
        $minified_final_css = lds_minify_css($final_css);

        $minified_css_path = $output_css_dir . 'lds-style.min.css';

        // Save the combined minified CSS
        if (file_put_contents($minified_css_path, $minified_final_css) === false) {
            return;
        }

        // If the call is via AJAX, send a success response
        if (defined('DOING_AJAX') && DOING_AJAX) {
            $message = 'Compilation completed in ' . $compile_result['time_ms'] . ' ms using ' . $compile_result['compiler'] . '.';
            if (!empty($compile_result['note'])) {
                $message .= ' ' . $compile_result['note'];
            }
            wp_send_json_success($message);
        }
    } catch (\ScssPhp\ScssPhp\Exception\CompilerException $e) {
        $error_message = 'SCSS compiler error: ' . $e->getMessage();
        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_send_json_error($error_message);
        }
    } catch (\Throwable $e) {
        $error_message = 'SCSS general error (' . get_class($e) . '): ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        if (defined('DOING_AJAX') && DOING_AJAX) {
            wp_send_json_error($error_message);
        }
    }
}

// Simple, fast CSS minifier to avoid recompiling SCSS twice
function lds_minify_css($css) {
    $css = preg_replace('!/\*.*?\*/!s', '', $css);
    $css = preg_replace('/\s+/', ' ', $css);
    $css = preg_replace('/\s*([{}:;,>])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return trim($css);
}

function lds_find_sass_binary() {
    if (defined('LDS_SASS_BIN') && is_string(LDS_SASS_BIN) && LDS_SASS_BIN !== '') {
        return LDS_SASS_BIN;
    }

    $paths = array(
        '/usr/local/bin/sass',
        '/opt/homebrew/bin/sass',
        '/usr/bin/sass',
    );
    foreach ($paths as $path) {
        if (is_executable($path)) {
            return $path;
        }
    }

    if (function_exists('shell_exec')) {
        $candidates = array('sass', 'dart-sass');
        foreach ($candidates as $bin) {
            $path = trim((string)@shell_exec('command -v ' . escapeshellarg($bin) . ' 2>/dev/null'));
            if ($path !== '') {
                return $path;
            }
        }
    }

    return null;
}

function lds_compile_scss($main_scss, $import_paths) {
    $start = microtime(true);
    $sass_bin = lds_find_sass_binary();
    $exec_disabled = false;
    if (!function_exists('exec')) {
        $exec_disabled = true;
    } else {
        $disabled = array_map('trim', explode(',', (string)ini_get('disable_functions')));
        if (in_array('exec', $disabled, true)) {
            $exec_disabled = true;
        }
    }

    if ($sass_bin && !$exec_disabled) {
        $tmp_dir = wp_upload_dir()['basedir'] . '/lds-scss';
        if (!is_dir($tmp_dir)) {
            @mkdir($tmp_dir, 0755, true);
        }
        $input_path = $tmp_dir . '/lds-input.scss';
        $output_path = $tmp_dir . '/lds-output.css';
        file_put_contents($input_path, $main_scss);

        $load_paths = array();
        foreach ($import_paths as $p) {
            if (is_dir($p)) {
                $load_paths[] = '--load-path=' . escapeshellarg($p);
            }
        }
        $flags = '--quiet --quiet-deps --silence-deprecation=import --silence-deprecation=global-builtin';
        // Ensure common paths are available for node-based Sass binaries.
        $env_path = 'PATH=/usr/local/bin:/opt/homebrew/bin:/usr/bin:/bin';
        $sass_cmd = escapeshellcmd($sass_bin);
        $use_node = false;

        // If sass is a node-based script (#!/usr/bin/env node), run it explicitly with node.
        if (is_readable($sass_bin)) {
            $fh = fopen($sass_bin, 'r');
            if ($fh) {
                $first_line = fgets($fh);
                fclose($fh);
                if ($first_line && strpos($first_line, '/usr/bin/env node') !== false) {
                    $use_node = true;
                }
            }
        }

        if ($use_node) {
            $node_candidates = array('/usr/local/bin/node', '/opt/homebrew/bin/node', '/usr/bin/node', '/bin/node');
            $node_bin = null;
            foreach ($node_candidates as $candidate) {
                if (is_executable($candidate)) {
                    $node_bin = $candidate;
                    break;
                }
            }
            if ($node_bin) {
                $sass_cmd = escapeshellcmd($node_bin) . ' ' . escapeshellarg($sass_bin);
            }
        }

        $cmd = $env_path . ' ' . $sass_cmd . ' ' . $flags . ' ' . implode(' ', $load_paths) . ' ' . escapeshellarg($input_path) . ' ' . escapeshellarg($output_path);
        $out = array();
        @exec($cmd . ' 2>&1', $out, $code);
        if (file_exists($output_path)) {
            $css = file_get_contents($output_path);
            return array(
                'css' => $css,
                'compiler' => basename($sass_bin),
                'time_ms' => (int)round((microtime(true) - $start) * 1000),
            );
        }
    }

    // Fallback to scssphp
    $scss = new Compiler();
    $scss->setImportPaths($import_paths);
    $css = $scss->compileString($main_scss)->getCss();
    $note = '';
    if ($exec_disabled) {
        $note = 'Note: exec is disabled in PHP, so Dart Sass cannot be used.';
    } elseif ($sass_bin) {
        $detail = '';
        if (!empty($out)) {
            $detail = trim((string)$out[0]);
            $log_path = wp_upload_dir()['basedir'] . '/lds-scss/sass-error.log';
            @file_put_contents($log_path, implode("\n", $out) . "\n", FILE_APPEND);
            $detail = ' (' . $detail . ')';
        }
        $note = 'Note: Dart Sass failed, using scssphp instead.' . $detail . ' See uploads/lds-scss/sass-error.log.';
    } else {
        $note = 'Note: Dart Sass not found, using scssphp instead.';
    }
    return array(
        'css' => $css,
        'compiler' => 'scssphp',
        'time_ms' => (int)round((microtime(true) - $start) * 1000),
        'note' => $note,
    );
}

// Function to collect plugin CSS during page loading
function lds_collect_plugin_css() {
    global $wp_styles;

    // Initialize the array for plugin CSS
    $plugin_css_files = array();

    // Iterate over all enqueued styles
    foreach ($wp_styles->queue as $handle) {
        $style = $wp_styles->registered[$handle];
        $src   = $style->src;

        // Check if the CSS comes from a plugin
        if (strpos($src, plugins_url()) !== false) {
            // Add the CSS to the list
            $plugin_css_files[] = array(
                'handle' => $handle,
                'src'    => $src,
            );
        }
    }

    // Save the list in an option
    update_option('lds_plugin_css_files', $plugin_css_files);
}
add_action('wp_enqueue_scripts', 'lds_collect_plugin_css', 1000);

// Function to deregister plugin CSS
function lds_deregister_plugin_styles() {
    $plugin_css_files = get_option('lds_plugin_css_files', array());

    foreach ($plugin_css_files as $css_file) {
        $handle = $css_file['handle'];
        wp_deregister_style($handle);
    }
}
add_action('wp_enqueue_scripts', 'lds_deregister_plugin_styles', 9999);

// Function to enqueue the compiled and minified CSS in WordPress
function swell_scales_enqueue_styles() {
    $min_css_url  = get_stylesheet_directory_uri() . '/assets/css/lds-style.min.css';
    $min_css_path = get_stylesheet_directory() . '/assets/css/lds-style.min.css';

    if (file_exists($min_css_path)) {
        wp_enqueue_style('logical-design-system-styles-min', $min_css_url, array(), filemtime($min_css_path));
    }
}
add_action('wp_enqueue_scripts', 'swell_scales_enqueue_styles', 20);

// Plugin activation function
function lds_plugin_activation() {
    $source_file      = plugin_dir_path(__FILE__) . 'scss/input/lds-input.json';
    $destination_dir  = get_stylesheet_directory() . '/assets/json/';
    $destination_file = $destination_dir . 'lds-input.json';

    if (!file_exists($source_file)) {
        return;
    }

    if (!is_dir($destination_dir) && !mkdir($destination_dir, 0755, true)) {
        return;
    }

    if (!file_exists($destination_file)) {
        copy($source_file, $destination_file);
    }
}

// Function to recursively copy directories and files without overwriting existing files
function recursive_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);

    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            if (is_dir($srcPath)) {
                recursive_copy($srcPath, $dstPath);
            } else {
                if (!file_exists($dstPath)) {
                    copy($srcPath, $dstPath);
                }
            }
        }
    }
    closedir($dir);
}
register_activation_hook(__FILE__, 'lds_plugin_activation');

// Function to enqueue JavaScript for AJAX handling
function enqueue_scss_compilation_script() {
    wp_enqueue_script('scss_compilation_script', plugin_dir_url(__FILE__) . 'scss-compilation.js', array('jquery'), null, true);

    wp_localize_script('scss_compilation_script', 'scss_compilation', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('scss_compilation_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'enqueue_scss_compilation_script');

// Function to dequeue CSS files of parent and child themes
function dequeue_child_theme_styles() {
    wp_dequeue_style('logical-theme-style');
    wp_dequeue_style('logical-theme-child-style');
}
add_action('wp_enqueue_scripts', 'dequeue_child_theme_styles', 20);

// Scheduling compilation via WP-Cron
function schedule_scss_compilation() {
    if (!wp_next_scheduled('nightly_scss_compilation')) {
        wp_schedule_event(strtotime('04:00:00'), 'daily', 'nightly_scss_compilation');
    }
}
add_action('wp', 'schedule_scss_compilation');

// Hook to run SCSS compilation every night at 4:00 AM
add_action('nightly_scss_compilation', 'handle_scss_compilation');

// Function to remove the WP-Cron event upon plugin deactivation
function lds_plugin_deactivation() {
    $timestamp = wp_next_scheduled('nightly_scss_compilation');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'nightly_scss_compilation');
    }
}
register_deactivation_hook(__FILE__, 'lds_plugin_deactivation');
