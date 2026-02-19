<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('logical_theme_vite_dist_path')) {
    function logical_theme_vite_dist_path()
    {
        return trailingslashit(get_stylesheet_directory()) . 'dist';
    }
}

if (!function_exists('logical_theme_vite_dist_uri')) {
    function logical_theme_vite_dist_uri()
    {
        return trailingslashit(get_stylesheet_directory_uri()) . 'dist';
    }
}

if (!function_exists('logical_theme_vite_manifest_path')) {
    function logical_theme_vite_manifest_path()
    {
        return trailingslashit(logical_theme_vite_dist_path()) . 'manifest.json';
    }
}

if (!function_exists('logical_theme_vite_get_manifest')) {
    function logical_theme_vite_get_manifest()
    {
        static $manifest_cache = null;
        if (is_array($manifest_cache)) {
            return $manifest_cache;
        }

        $manifest_path = logical_theme_vite_manifest_path();
        if (!file_exists($manifest_path)) {
            $manifest_cache = array();
            return $manifest_cache;
        }

        $mtime = (string) filemtime($manifest_path);
        $cache_key = 'manifest:' . $mtime;
        $cached = wp_cache_get($cache_key, 'logical-theme-vite');
        if (is_array($cached)) {
            $manifest_cache = $cached;
            return $manifest_cache;
        }

        $raw_manifest = file_get_contents($manifest_path);
        $decoded = json_decode(is_string($raw_manifest) ? $raw_manifest : '', true);
        if (!is_array($decoded)) {
            $manifest_cache = array();
            return $manifest_cache;
        }

        $manifest_cache = $decoded;
        wp_cache_set($cache_key, $manifest_cache, 'logical-theme-vite', 600);
        return $manifest_cache;
    }
}

if (!function_exists('logical_theme_vite_get_env')) {
    function logical_theme_vite_get_env($key)
    {
        $value = getenv($key);
        if (is_string($value) && trim($value) !== '') {
            return trim($value);
        }

        if (isset($_SERVER[$key]) && is_string($_SERVER[$key]) && trim($_SERVER[$key]) !== '') {
            return trim($_SERVER[$key]);
        }

        if (isset($_ENV[$key]) && is_string($_ENV[$key]) && trim($_ENV[$key]) !== '') {
            return trim($_ENV[$key]);
        }

        return '';
    }
}

if (!function_exists('logical_theme_vite_dev_server_url')) {
    function logical_theme_vite_dev_server_url()
    {
        static $cached_url = null;
        if ($cached_url !== null) {
            return $cached_url;
        }

        $url = logical_theme_vite_get_env('VITE_DEV_SERVER_URL');
        if ($url === '') {
            $cached_url = '';
            return $cached_url;
        }

        $cached_url = untrailingslashit($url);
        return $cached_url;
    }
}

if (!function_exists('logical_theme_vite_is_dev_server_available')) {
    function logical_theme_vite_is_dev_server_available()
    {
        static $cached_result = null;
        if (is_bool($cached_result)) {
            return $cached_result;
        }

        $base_url = logical_theme_vite_dev_server_url();
        if ($base_url === '') {
            $cached_result = false;
            return $cached_result;
        }

        $response = wp_remote_get($base_url . '/@vite/client', array(
            'timeout' => 1,
            'sslverify' => false,
            'redirection' => 0,
        ));
        if (is_wp_error($response)) {
            $cached_result = false;
            return $cached_result;
        }

        $status = (int) wp_remote_retrieve_response_code($response);
        $cached_result = $status >= 200 && $status < 500;
        return $cached_result;
    }
}

if (!function_exists('logical_theme_vite_scan_source_entries')) {
    function logical_theme_vite_scan_source_entries()
    {
        static $source_map = null;
        if (is_array($source_map)) {
            return $source_map;
        }

        $source_map = array();
        $entries_root = trailingslashit(get_stylesheet_directory()) . 'src/entries';
        if (is_dir($entries_root)) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($entries_root, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file_info) {
                if (!$file_info instanceof SplFileInfo || !$file_info->isFile()) {
                    continue;
                }

                $extension = strtolower($file_info->getExtension());
                if (!in_array($extension, array('js', 'ts'), true)) {
                    continue;
                }

                $key = pathinfo($file_info->getFilename(), PATHINFO_FILENAME);
                $relative = str_replace('\\', '/', substr($file_info->getPathname(), strlen(get_stylesheet_directory()) + 1));
                if (!isset($source_map[$key])) {
                    $source_map[$key] = $relative;
                }
            }
        }

        $editor_path = trailingslashit(get_stylesheet_directory()) . 'src/editor.js';
        if (file_exists($editor_path)) {
            $source_map['editor'] = 'src/editor.js';
        }

        return $source_map;
    }
}

if (!function_exists('logical_theme_vite_string_starts_with')) {
    function logical_theme_vite_string_starts_with($value, $prefix)
    {
        return is_string($value) && is_string($prefix) && $prefix !== '' && strpos($value, $prefix) === 0;
    }
}

if (!function_exists('logical_theme_vite_normalize_handle_suffix')) {
    function logical_theme_vite_normalize_handle_suffix($value)
    {
        return sanitize_title(str_replace('/', '-', (string) $value));
    }
}

if (!function_exists('logical_theme_vite_collect_frontend_block_names')) {
    function logical_theme_vite_collect_frontend_block_names($blocks, &$names)
    {
        if (!is_array($blocks)) {
            return;
        }

        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }

            $block_name = isset($block['blockName']) ? (string) $block['blockName'] : '';
            if ($block_name !== '') {
                $normalized = sanitize_title(str_replace('/', '-', strtolower($block_name)));
                if ($normalized !== '') {
                    $names[$normalized] = true;
                }
            }

            if (isset($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                logical_theme_vite_collect_frontend_block_names($block['innerBlocks'], $names);
            }
        }
    }
}

if (!function_exists('logical_theme_vite_get_first_enqueued_style_handle')) {
    function logical_theme_vite_get_first_enqueued_style_handle()
    {
        global $logical_theme_vite_style_handles;
        if (!is_array($logical_theme_vite_style_handles) || empty($logical_theme_vite_style_handles)) {
            return '';
        }

        return (string) $logical_theme_vite_style_handles[0];
    }
}

if (!function_exists('logical_theme_vite_enqueue_manifest_entry_recursive')) {
    function logical_theme_vite_enqueue_manifest_entry_recursive($entry_key, $manifest, &$state, $args = array())
    {
        if (isset($state['visiting'][$entry_key])) {
            return isset($state['script_handles'][$entry_key]) ? (string) $state['script_handles'][$entry_key] : '';
        }

        if (isset($state['script_handles'][$entry_key])) {
            return (string) $state['script_handles'][$entry_key];
        }

        if (!isset($manifest[$entry_key]) || !is_array($manifest[$entry_key])) {
            return '';
        }

        $entry = $manifest[$entry_key];
        $state['visiting'][$entry_key] = true;

        $deps = array();
        $imports = isset($entry['imports']) && is_array($entry['imports']) ? $entry['imports'] : array();
        foreach ($imports as $import_key) {
            $import_handle = logical_theme_vite_enqueue_manifest_entry_recursive((string) $import_key, $manifest, $state, $args);
            if ($import_handle !== '') {
                $deps[] = $import_handle;
            }
        }

        $css_files = isset($entry['css']) && is_array($entry['css']) ? $entry['css'] : array();
        foreach ($css_files as $css_relative_path) {
            $css_relative_path = ltrim((string) $css_relative_path, '/');
            if ($css_relative_path === '') {
                continue;
            }

            $style_handle = 'logical-theme-vite-css-' . substr(md5($css_relative_path), 0, 10);
            if (isset($state['style_handles'][$style_handle])) {
                continue;
            }

            $css_file_path = trailingslashit(logical_theme_vite_dist_path()) . $css_relative_path;
            $version = file_exists($css_file_path) ? (string) filemtime($css_file_path) : null;
            wp_enqueue_style(
                $style_handle,
                trailingslashit(logical_theme_vite_dist_uri()) . $css_relative_path,
                array(),
                $version
            );

            $state['style_handles'][$style_handle] = true;
            global $logical_theme_vite_style_handles;
            if (!is_array($logical_theme_vite_style_handles)) {
                $logical_theme_vite_style_handles = array();
            }
            if (!in_array($style_handle, $logical_theme_vite_style_handles, true)) {
                $logical_theme_vite_style_handles[] = $style_handle;
            }
        }

        $script_handle = '';
        $file = isset($entry['file']) ? ltrim((string) $entry['file'], '/') : '';
        if ($file !== '' && preg_match('/\.m?js$/', $file)) {
            $script_handle = 'logical-theme-vite-js-' . logical_theme_vite_normalize_handle_suffix($entry_key);
            if (!isset($state['script_handles'][$entry_key])) {
                $script_file_path = trailingslashit(logical_theme_vite_dist_path()) . $file;
                $version = file_exists($script_file_path) ? (string) filemtime($script_file_path) : null;
                $script_deps = !empty($args['deps']) && is_array($args['deps'])
                    ? array_values(array_unique(array_merge($deps, $args['deps'])))
                    : array_values(array_unique($deps));

                wp_enqueue_script(
                    $script_handle,
                    trailingslashit(logical_theme_vite_dist_uri()) . $file,
                    $script_deps,
                    $version,
                    true
                );
                wp_script_add_data($script_handle, 'type', 'module');
                $state['script_handles'][$entry_key] = $script_handle;
            }
        }

        unset($state['visiting'][$entry_key]);
        return $script_handle;
    }
}

if (!function_exists('logical_theme_vite_enqueue_entry')) {
    function logical_theme_vite_enqueue_entry($entry_key, $args = array())
    {
        $entry_key = sanitize_key((string) $entry_key);
        if ($entry_key === '') {
            return false;
        }

        $args = is_array($args) ? $args : array();
        static $state = null;
        if (!is_array($state)) {
            $state = array(
                'visiting' => array(),
                'script_handles' => array(),
                'style_handles' => array(),
                'dev_client_loaded' => false,
            );
        }

        if (logical_theme_vite_is_dev_server_available()) {
            $source_map = logical_theme_vite_scan_source_entries();
            if (!isset($source_map[$entry_key])) {
                return false;
            }

            $dev_base = logical_theme_vite_dev_server_url();
            if ($dev_base === '') {
                return false;
            }

            $client_handle = 'logical-theme-vite-dev-client';
            if (!$state['dev_client_loaded']) {
                wp_enqueue_script(
                    $client_handle,
                    $dev_base . '/@vite/client',
                    array(),
                    null,
                    true
                );
                wp_script_add_data($client_handle, 'type', 'module');
                $state['dev_client_loaded'] = true;
            }

            $module_path = '/' . ltrim((string) $source_map[$entry_key], '/');
            $script_handle = 'logical-theme-vite-dev-js-' . logical_theme_vite_normalize_handle_suffix($entry_key);
            $deps = array($client_handle);
            if (!empty($args['deps']) && is_array($args['deps'])) {
                $deps = array_values(array_unique(array_merge($deps, $args['deps'])));
            }

            wp_enqueue_script(
                $script_handle,
                $dev_base . $module_path,
                $deps,
                null,
                true
            );
            wp_script_add_data($script_handle, 'type', 'module');

            return true;
        }

        $manifest = logical_theme_vite_get_manifest();
        if (empty($manifest)) {
            return false;
        }

        $script_handle = logical_theme_vite_enqueue_manifest_entry_recursive($entry_key, $manifest, $state, $args);
        return $script_handle !== '';
    }
}

if (!function_exists('logical_theme_vite_enqueue_prefix')) {
    function logical_theme_vite_enqueue_prefix($prefix, $args = array())
    {
        $prefix = strtolower(trim((string) $prefix));
        if ($prefix === '') {
            return 0;
        }

        $manifest = logical_theme_vite_get_manifest();
        $entry_keys = array();

        foreach ($manifest as $key => $entry) {
            if (!is_array($entry) || empty($entry['isEntry']) || !logical_theme_vite_string_starts_with((string) $key, $prefix)) {
                continue;
            }
            $entry_keys[] = (string) $key;
        }

        if (logical_theme_vite_is_dev_server_available()) {
            $source_map = logical_theme_vite_scan_source_entries();
            foreach ($source_map as $key => $source_path) {
                if (!logical_theme_vite_string_starts_with((string) $key, $prefix)) {
                    continue;
                }
                if (!in_array($key, $entry_keys, true)) {
                    $entry_keys[] = $key;
                }
            }
        }

        $count = 0;
        foreach ($entry_keys as $entry_key) {
            if (logical_theme_vite_enqueue_entry($entry_key, $args)) {
                $count++;
            }
        }

        return $count;
    }
}

if (!function_exists('logical_theme_vite_get_active_template_slug')) {
    function logical_theme_vite_get_active_template_slug()
    {
        $template_path = isset($GLOBALS['logical_theme_active_template'])
            ? (string) $GLOBALS['logical_theme_active_template']
            : '';

        if ($template_path !== '') {
            return sanitize_title(pathinfo(basename($template_path), PATHINFO_FILENAME));
        }

        $queried_id = get_queried_object_id();
        if ($queried_id > 0) {
            $page_template = get_page_template_slug($queried_id);
            if (is_string($page_template) && $page_template !== '') {
                return sanitize_title(pathinfo(basename($page_template), PATHINFO_FILENAME));
            }
        }

        return '';
    }
}

if (!function_exists('logical_theme_vite_enqueue_frontend_context')) {
    function logical_theme_vite_enqueue_frontend_context()
    {
        if (is_admin()) {
            return;
        }

        logical_theme_vite_enqueue_prefix('theme-');

        $template_slug = logical_theme_vite_get_active_template_slug();
        if ($template_slug !== '') {
            logical_theme_vite_enqueue_prefix('temp-' . $template_slug . '-');
        }

        if (is_page()) {
            $post = get_queried_object();
            if ($post instanceof WP_Post && is_string($post->post_name) && $post->post_name !== '') {
                logical_theme_vite_enqueue_prefix('page-' . sanitize_title($post->post_name) . '-');
            }
        }

        if (is_singular()) {
            $post = get_queried_object();
            if ($post instanceof WP_Post && is_string($post->post_content) && $post->post_content !== '') {
                $blocks = parse_blocks($post->post_content);
                $block_names = array();
                logical_theme_vite_collect_frontend_block_names($blocks, $block_names);
                foreach (array_keys($block_names) as $block_name) {
                    logical_theme_vite_enqueue_prefix('block-' . $block_name . '-');
                }
            }
        }
    }
}

if (!function_exists('logical_theme_capture_active_template')) {
    function logical_theme_capture_active_template($template)
    {
        $GLOBALS['logical_theme_active_template'] = is_string($template) ? $template : '';
        return $template;
    }
}
add_filter('template_include', 'logical_theme_capture_active_template', 1000);
