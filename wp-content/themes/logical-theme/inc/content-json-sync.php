<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('LOGICAL_THEME_CONTENT_JSON_SYNC_REPORT_OPTION')) {
    define('LOGICAL_THEME_CONTENT_JSON_SYNC_REPORT_OPTION', 'logical_theme_content_json_last_report');
}

if (!function_exists('logical_theme_content_json_sync_lock')) {
    function logical_theme_content_json_sync_lock($set = null)
    {
        static $counter = 0;

        if ($set === null) {
            return $counter > 0;
        }

        if ($set) {
            $counter++;
        } else {
            $counter = max(0, $counter - 1);
        }

        return $counter > 0;
    }
}

if (!function_exists('logical_theme_content_json_result_template')) {
    function logical_theme_content_json_result_template($operation)
    {
        return array(
            'operation' => (string) $operation,
            'ran_at' => gmdate('c'),
            'summary' => array(
                'processed' => 0,
                'updated' => 0,
                'created' => 0,
                'skipped' => 0,
                'error' => 0,
            ),
            'items' => array(),
        );
    }
}

if (!function_exists('logical_theme_content_json_result_add_item')) {
    function logical_theme_content_json_result_add_item(&$result, $item)
    {
        if (!is_array($result) || !isset($result['summary']) || !is_array($result['summary'])) {
            return;
        }

        $status = isset($item['status']) ? sanitize_key((string) $item['status']) : 'skipped';
        if (!isset($result['summary'][$status])) {
            $status = 'skipped';
        }

        $result['items'][] = $item;
        $result['summary']['processed']++;
        $result['summary'][$status]++;
    }
}

if (!function_exists('logical_theme_content_json_collect_source_files')) {
    function logical_theme_content_json_collect_source_files()
    {
        $dir = logical_theme_content_json_get_assets_json_dir();
        if (!is_dir($dir)) {
            return array();
        }

        $files = glob(trailingslashit($dir) . '*.json');
        if (!is_array($files)) {
            return array();
        }

        sort($files);
        return $files;
    }
}

if (!function_exists('logical_theme_content_json_make_title_from_slug')) {
    function logical_theme_content_json_make_title_from_slug($slug)
    {
        $slug = sanitize_title((string) $slug);
        if ($slug === '') {
            return __('Imported Content', 'wp-logical-theme');
        }

        $title = str_replace(array('-', '_'), ' ', $slug);
        return ucwords($title);
    }
}

if (!function_exists('logical_theme_content_json_resolve_published_post_by_slug')) {
    function logical_theme_content_json_resolve_published_post_by_slug($slug)
    {
        $slug = sanitize_title((string) $slug);
        if ($slug === '') {
            return array('status' => 'error', 'post_id' => 0, 'message' => __('Invalid slug.', 'wp-logical-theme'));
        }

        $ids = get_posts(array(
            'post_type' => array('page', 'post'),
            'post_status' => array('publish', 'private', 'draft', 'pending', 'future'),
            'name' => $slug,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
            'suppress_filters' => false,
        ));

        if (!is_array($ids) || count($ids) === 0) {
            return array('status' => 'missing', 'post_id' => 0, 'message' => __('No published content found for slug.', 'wp-logical-theme'));
        }

        if (count($ids) > 1) {
            $status_priority = array(
                'publish' => 1,
                'private' => 2,
                'draft' => 3,
                'pending' => 4,
                'future' => 5,
            );
            $type_priority = array(
                'page' => 1,
                'post' => 2,
            );

            usort($ids, function ($a, $b) use ($status_priority, $type_priority) {
                $post_a = get_post((int) $a);
                $post_b = get_post((int) $b);
                if (!($post_a instanceof WP_Post) || !($post_b instanceof WP_Post)) {
                    return ((int) $a) <=> ((int) $b);
                }

                $sa = isset($status_priority[$post_a->post_status]) ? $status_priority[$post_a->post_status] : 99;
                $sb = isset($status_priority[$post_b->post_status]) ? $status_priority[$post_b->post_status] : 99;
                if ($sa !== $sb) {
                    return $sa <=> $sb;
                }

                $ta = isset($type_priority[$post_a->post_type]) ? $type_priority[$post_a->post_type] : 99;
                $tb = isset($type_priority[$post_b->post_type]) ? $type_priority[$post_b->post_type] : 99;
                if ($ta !== $tb) {
                    return $ta <=> $tb;
                }

                return ((int) $a) <=> ((int) $b);
            });
        }

        return array('status' => 'found', 'post_id' => (int) $ids[0], 'message' => '');
    }
}

if (!function_exists('logical_theme_content_json_build_post_content_from_sections')) {
    function logical_theme_content_json_build_post_content_from_sections(array $sections)
    {
        $blocks = array();

        foreach ($sections as $section) {
            if (!is_array($section)) {
                continue;
            }

            $section_id = isset($section['id']) ? sanitize_key((string) $section['id']) : '';
            $section_type = isset($section['type']) ? sanitize_key((string) $section['type']) : '';
            if ($section_id === '' || $section_type === '') {
                continue;
            }

            $block_name = 'logical-theme/' . $section_type;
            $blocks[] = array(
                'blockName' => $block_name,
                'attrs' => array(
                    'sectionId' => $section_id,
                    'sectionType' => $section_type,
                    'data' => isset($section['data']) && is_array($section['data']) ? $section['data'] : array(),
                    'settings' => isset($section['settings']) && is_array($section['settings']) ? $section['settings'] : array(),
                ),
                'innerBlocks' => array(),
                'innerHTML' => '',
                'innerContent' => array(),
            );
        }

        return serialize_blocks($blocks);
    }
}

if (!function_exists('logical_theme_content_json_build_block_from_layout_item')) {
    function logical_theme_content_json_build_block_from_layout_item($item)
    {
        if (!is_array($item)) {
            return null;
        }

        $type = isset($item['type']) ? sanitize_key((string) $item['type']) : '';
        $id = isset($item['id']) ? sanitize_key((string) $item['id']) : '';
        $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : array();

        if ($type === 'pretitle') {
            return array(
                'blockName' => 'logical-theme/pretitle',
                'attrs' => array(
                    'itemId' => $id,
                    'text' => isset($data['text']) ? (string) $data['text'] : '',
                ),
                'innerBlocks' => array(),
                'innerHTML' => '',
                'innerContent' => array(),
            );
        }

        if ($type === 'title') {
            $level = isset($data['level']) ? (string) $data['level'] : 'h2';
            if (!in_array($level, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'), true)) {
                $level = 'h2';
            }
            return array(
                'blockName' => 'logical-theme/title',
                'attrs' => array(
                    'itemId' => $id,
                    'text' => isset($data['text']) ? (string) $data['text'] : '',
                    'level' => $level,
                ),
                'innerBlocks' => array(),
                'innerHTML' => '',
                'innerContent' => array(),
            );
        }

        if ($type === 'text') {
            return array(
                'blockName' => 'logical-theme/text',
                'attrs' => array(
                    'itemId' => $id,
                    'text' => isset($data['text']) ? (string) $data['text'] : '',
                ),
                'innerBlocks' => array(),
                'innerHTML' => '',
                'innerContent' => array(),
            );
        }

        if ($type === 'image') {
            return array(
                'blockName' => 'logical-theme/image',
                'attrs' => array(
                    'itemId' => $id,
                    'id' => isset($data['id']) ? (int) $data['id'] : 0,
                    'src' => isset($data['src']) ? (string) $data['src'] : '',
                    'alt' => isset($data['alt']) ? (string) $data['alt'] : '',
                ),
                'innerBlocks' => array(),
                'innerHTML' => '',
                'innerContent' => array(),
            );
        }

        if ($type === 'button') {
            return array(
                'blockName' => 'logical-theme/button',
                'attrs' => array(
                    'itemId' => $id,
                    'label' => isset($data['label']) ? (string) $data['label'] : '',
                    'url' => isset($data['url']) ? (string) $data['url'] : '',
                    'variant' => isset($data['variant']) ? (string) $data['variant'] : 'primary',
                    'target' => isset($data['target']) ? (string) $data['target'] : '_self',
                ),
                'innerBlocks' => array(),
                'innerHTML' => '',
                'innerContent' => array(),
            );
        }

        if ($type === 'embed') {
            return array(
                'blockName' => 'core/embed',
                'attrs' => array(
                    'url' => isset($data['url']) ? (string) $data['url'] : '',
                    'providerNameSlug' => isset($data['provider']) ? (string) $data['provider'] : '',
                ),
                'innerBlocks' => array(),
                'innerHTML' => '',
                'innerContent' => array(),
            );
        }

        return null;
    }
}

if (!function_exists('logical_theme_content_json_build_post_content_from_layout')) {
    function logical_theme_content_json_build_post_content_from_layout(array $layout)
    {
        $rows = array();
        foreach ($layout as $row) {
            if (!is_array($row)) {
                continue;
            }

            $row_id = isset($row['id']) ? sanitize_key((string) $row['id']) : '';
            $row_settings = isset($row['settings']) && is_array($row['settings']) ? $row['settings'] : array();
            $columns = isset($row['columns']) && is_array($row['columns']) ? $row['columns'] : array();

            $row_inner_blocks = array();
            foreach ($columns as $column) {
                if (!is_array($column)) {
                    continue;
                }

                $col_id = isset($column['id']) ? sanitize_key((string) $column['id']) : '';
                $col_settings = isset($column['settings']) && is_array($column['settings']) ? $column['settings'] : array();
                $items = isset($column['items']) && is_array($column['items']) ? $column['items'] : array();

                $item_blocks = array();
                foreach ($items as $item) {
                    $item_block = logical_theme_content_json_build_block_from_layout_item($item);
                    if (is_array($item_block)) {
                        $item_blocks[] = $item_block;
                    }
                }

                $row_inner_blocks[] = array(
                    'blockName' => 'logical-theme/column',
                    'attrs' => array(
                        'columnId' => $col_id,
                        'desktop' => isset($col_settings['desktop']) ? (int) $col_settings['desktop'] : 12,
                        'tablet' => isset($col_settings['tablet']) ? (int) $col_settings['tablet'] : 12,
                        'mobile' => isset($col_settings['mobile']) ? (int) $col_settings['mobile'] : 12,
                        'alignY' => isset($col_settings['alignY']) ? (string) $col_settings['alignY'] : 'stretch',
                    ),
                    'innerBlocks' => $item_blocks,
                    'innerHTML' => '',
                    'innerContent' => array(),
                );
            }

            $rows[] = array(
                'blockName' => 'logical-theme/row',
                'attrs' => array(
                    'rowId' => $row_id,
                    'container' => isset($row_settings['container']) ? (string) $row_settings['container'] : 'default',
                    'gap' => isset($row_settings['gap']) ? (string) $row_settings['gap'] : 'md',
                    'alignY' => isset($row_settings['alignY']) ? (string) $row_settings['alignY'] : 'stretch',
                    'backgroundColor' => isset($row_settings['backgroundColor']) ? (string) $row_settings['backgroundColor'] : '',
                ),
                'innerBlocks' => $row_inner_blocks,
                'innerHTML' => '',
                'innerContent' => array(),
            );
        }

        $layout_block = array(
            'blockName' => 'logical-theme/layout',
            'attrs' => array(
                'layoutVersion' => '3.0',
            ),
            'innerBlocks' => $rows,
            'innerHTML' => '',
            'innerContent' => array(),
        );

        return serialize_blocks(array($layout_block));
    }
}

if (!function_exists('logical_theme_content_json_build_post_content_from_decoded')) {
    function logical_theme_content_json_build_post_content_from_decoded(array $decoded)
    {
        $version = isset($decoded['version']) ? (string) $decoded['version'] : '';

        if ($version === '3.0') {
            $layout = isset($decoded['layout']) && is_array($decoded['layout']) ? $decoded['layout'] : array();
            return logical_theme_content_json_build_post_content_from_layout($layout);
        }

        $sections = isset($decoded['sections']) && is_array($decoded['sections']) ? $decoded['sections'] : array();
        return logical_theme_content_json_build_post_content_from_sections($sections);
    }
}

if (!function_exists('logical_theme_content_json_build_layout_placeholder_content')) {
    function logical_theme_content_json_build_layout_placeholder_content()
    {
        return "<!-- wp:logical-theme/layout /-->";
    }
}

if (!function_exists('logical_theme_content_json_import_from_files')) {
    function logical_theme_content_json_import_from_files($opts = array())
    {
        $options = wp_parse_args(is_array($opts) ? $opts : array(), array(
            'create_missing' => true,
            'create_post_type' => 'page',
        ));

        $result = logical_theme_content_json_result_template('import');
        $files = logical_theme_content_json_collect_source_files();
        $create_post_type = in_array($options['create_post_type'], array('page', 'post'), true) ? $options['create_post_type'] : 'page';

        logical_theme_content_json_sync_lock(true);
        try {
            foreach ($files as $file_path) {
                $slug = sanitize_title((string) pathinfo((string) $file_path, PATHINFO_FILENAME));
                $base_item = array(
                    'phase' => 'import',
                    'slug' => $slug,
                    'post_id' => 0,
                );

                if ($slug === '') {
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'status' => 'error',
                        'message' => __('Invalid filename slug.', 'wp-logical-theme'),
                    )));
                    continue;
                }

                $raw_json = file_get_contents($file_path);
                if (!is_string($raw_json) || trim($raw_json) === '') {
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'status' => 'error',
                        'message' => __('File is empty or unreadable.', 'wp-logical-theme'),
                    )));
                    continue;
                }

                $normalized = logical_theme_normalize_content_json($raw_json);
                if (is_wp_error($normalized) || !isset($normalized['decoded'], $normalized['encoded'])) {
                    $message = is_wp_error($normalized) ? $normalized->get_error_message() : __('Invalid content JSON.', 'wp-logical-theme');
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'status' => 'error',
                        'message' => $message,
                    )));
                    continue;
                }

                $resolved = logical_theme_content_json_resolve_published_post_by_slug($slug);
                $post_id = 0;
                $created = false;

                if ($resolved['status'] === 'ambiguous') {
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'status' => 'skipped',
                        'message' => $resolved['message'],
                    )));
                    continue;
                }

                if ($resolved['status'] === 'missing') {
                    if (empty($options['create_missing'])) {
                        logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                            'status' => 'skipped',
                            'message' => __('No matching content and auto-create disabled.', 'wp-logical-theme'),
                        )));
                        continue;
                    }

                    $created_id = wp_insert_post(array(
                        'post_type' => $create_post_type,
                        'post_status' => 'publish',
                        'post_name' => $slug,
                        'post_title' => logical_theme_content_json_make_title_from_slug($slug),
                        'post_content' => '',
                    ), true);

                    if (is_wp_error($created_id)) {
                        logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                            'status' => 'error',
                            'message' => $created_id->get_error_message(),
                        )));
                        continue;
                    }

                    $post_id = (int) $created_id;
                    $created = true;
                } else {
                    $post_id = (int) $resolved['post_id'];
                }

                if ($post_id <= 0) {
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'status' => 'error',
                        'message' => __('Could not resolve target content.', 'wp-logical-theme'),
                    )));
                    continue;
                }

                $decoded_version = isset($normalized['decoded']['version']) ? (string) $normalized['decoded']['version'] : '';
                // For v3 layout we store a minimal dynamic layout block placeholder.
                // The render callback then reads rows from meta and outputs the full layout.
                $next_content = $decoded_version === '3.0'
                    ? logical_theme_content_json_build_layout_placeholder_content()
                    : logical_theme_content_json_build_post_content_from_decoded($normalized['decoded']);

                $current_meta = get_post_meta($post_id, LOGICAL_THEME_CONTENT_JSON_META_KEY, true);
                $current_content = (string) get_post_field('post_content', $post_id);
                $meta_changed = ((string) $current_meta !== (string) $normalized['encoded']);
                $content_changed = ($current_content !== $next_content);

                if ($meta_changed) {
                    update_post_meta($post_id, LOGICAL_THEME_CONTENT_JSON_META_KEY, $normalized['encoded']);
                }

                if ($content_changed) {
                    $update = wp_update_post(array(
                        'ID' => $post_id,
                        'post_content' => $next_content,
                    ), true);
                    if (is_wp_error($update)) {
                        logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                            'post_id' => $post_id,
                            'status' => 'error',
                            'message' => $update->get_error_message(),
                        )));
                        continue;
                    }
                }

                if ($created) {
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'post_id' => $post_id,
                        'status' => 'created',
                        'message' => __('Content created and updated from file.', 'wp-logical-theme'),
                    )));
                } elseif ($meta_changed || $content_changed) {
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'post_id' => $post_id,
                        'status' => 'updated',
                        'message' => __('Content updated from file.', 'wp-logical-theme'),
                    )));
                } else {
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'post_id' => $post_id,
                        'status' => 'skipped',
                        'message' => __('No changes needed.', 'wp-logical-theme'),
                    )));
                }
            }
        } finally {
            logical_theme_content_json_sync_lock(false);
        }

        return $result;
    }
}

if (!function_exists('logical_theme_content_json_export_to_files')) {
    function logical_theme_content_json_export_to_files($opts = array())
    {
        $result = logical_theme_content_json_result_template('export');
        $target_dir = logical_theme_content_json_get_assets_json_dir();
        if (!wp_mkdir_p($target_dir)) {
            logical_theme_content_json_result_add_item($result, array(
                'phase' => 'export',
                'slug' => '',
                'post_id' => 0,
                'status' => 'error',
                'message' => __('Cannot create assets/json directory.', 'wp-logical-theme'),
            ));
            return $result;
        }

        $ids = get_posts(array(
            'post_type' => array('page', 'post'),
            'post_status' => array('publish'),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
            'suppress_filters' => false,
        ));

        if (!is_array($ids)) {
            return $result;
        }

        logical_theme_content_json_sync_lock(true);
        try {
            foreach ($ids as $post_id_raw) {
                $post_id = (int) $post_id_raw;
                $post = get_post($post_id);
                if (!($post instanceof WP_Post)) {
                    continue;
                }

                $slug = sanitize_title((string) $post->post_name);
                if ($slug === '') {
                    $slug = $post->post_type . '-' . (string) $post_id;
                }

                $base_item = array(
                    'phase' => 'export',
                    'slug' => $slug,
                    'post_id' => $post_id,
                );

                $raw_json = get_post_meta($post_id, LOGICAL_THEME_CONTENT_JSON_META_KEY, true);
                $normalized = logical_theme_normalize_content_json($raw_json);
                if (is_wp_error($normalized) || !isset($normalized['decoded'])) {
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'status' => 'skipped',
                        'message' => __('Missing or invalid meta JSON; skipped.', 'wp-logical-theme'),
                    )));
                    continue;
                }

                $pretty_json = wp_json_encode(
                    $normalized['decoded'],
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                );
                if (!is_string($pretty_json) || $pretty_json === '') {
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'status' => 'error',
                        'message' => __('Could not encode JSON for export.', 'wp-logical-theme'),
                    )));
                    continue;
                }

                $target_file = trailingslashit($target_dir) . $slug . '.json';
                $current_file = file_exists($target_file) ? file_get_contents($target_file) : '';
                $next_file = $pretty_json . PHP_EOL;
                if (is_string($current_file) && $current_file === $next_file) {
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'status' => 'skipped',
                        'message' => __('File already up to date.', 'wp-logical-theme'),
                    )));
                    continue;
                }

                if (false === file_put_contents($target_file, $next_file, LOCK_EX)) {
                    logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                        'status' => 'error',
                        'message' => __('Failed writing JSON file.', 'wp-logical-theme'),
                    )));
                    continue;
                }

                logical_theme_content_json_result_add_item($result, array_merge($base_item, array(
                    'status' => file_exists($target_file) && $current_file !== '' ? 'updated' : 'created',
                    'message' => __('File exported from DB.', 'wp-logical-theme'),
                )));
            }
        } finally {
            logical_theme_content_json_sync_lock(false);
        }

        return $result;
    }
}

if (!function_exists('logical_theme_content_json_bidirectional_sync')) {
    function logical_theme_content_json_bidirectional_sync($opts = array())
    {
        $import = logical_theme_content_json_import_from_files($opts);
        $export = logical_theme_content_json_export_to_files($opts);

        $result = logical_theme_content_json_result_template('sync');
        $result['summary'] = array(
            'processed' => ((int) $import['summary']['processed']) + ((int) $export['summary']['processed']),
            'updated' => ((int) $import['summary']['updated']) + ((int) $export['summary']['updated']),
            'created' => ((int) $import['summary']['created']) + ((int) $export['summary']['created']),
            'skipped' => ((int) $import['summary']['skipped']) + ((int) $export['summary']['skipped']),
            'error' => ((int) $import['summary']['error']) + ((int) $export['summary']['error']),
        );
        $result['items'] = array_merge(
            isset($import['items']) && is_array($import['items']) ? $import['items'] : array(),
            isset($export['items']) && is_array($export['items']) ? $export['items'] : array()
        );
        $result['phases'] = array(
            'import' => $import,
            'export' => $export,
        );

        return $result;
    }
}

if (!function_exists('logical_theme_content_json_admin_store_report')) {
    function logical_theme_content_json_admin_store_report($report)
    {
        if (!is_array($report)) {
            return;
        }
        update_option(LOGICAL_THEME_CONTENT_JSON_SYNC_REPORT_OPTION, $report, false);
    }
}

if (!function_exists('logical_theme_content_json_admin_redirect_url')) {
    function logical_theme_content_json_admin_redirect_url()
    {
        return add_query_arg(array(
            'page' => 'logical-theme-content-json',
            'logical_theme_content_json_notice' => '1',
        ), admin_url('tools.php'));
    }
}

if (!function_exists('logical_theme_content_json_require_manage_options')) {
    function logical_theme_content_json_require_manage_options()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to run this action.', 'wp-logical-theme'));
        }
    }
}

if (!function_exists('logical_theme_content_json_handle_admin_import')) {
    function logical_theme_content_json_handle_admin_import()
    {
        logical_theme_content_json_require_manage_options();
        check_admin_referer('logical_theme_content_json_import');

        $report = logical_theme_content_json_import_from_files(array(
            'create_missing' => true,
            'create_post_type' => 'page',
        ));
        logical_theme_content_json_admin_store_report($report);

        wp_safe_redirect(logical_theme_content_json_admin_redirect_url());
        exit;
    }
}
add_action('admin_post_logical_theme_content_json_import', 'logical_theme_content_json_handle_admin_import');

if (!function_exists('logical_theme_content_json_handle_admin_export')) {
    function logical_theme_content_json_handle_admin_export()
    {
        logical_theme_content_json_require_manage_options();
        check_admin_referer('logical_theme_content_json_export');

        $report = logical_theme_content_json_export_to_files();
        logical_theme_content_json_admin_store_report($report);

        wp_safe_redirect(logical_theme_content_json_admin_redirect_url());
        exit;
    }
}
add_action('admin_post_logical_theme_content_json_export', 'logical_theme_content_json_handle_admin_export');

if (!function_exists('logical_theme_content_json_handle_admin_sync')) {
    function logical_theme_content_json_handle_admin_sync()
    {
        logical_theme_content_json_require_manage_options();
        check_admin_referer('logical_theme_content_json_sync');

        $report = logical_theme_content_json_bidirectional_sync(array(
            'create_missing' => true,
            'create_post_type' => 'page',
        ));
        logical_theme_content_json_admin_store_report($report);

        wp_safe_redirect(logical_theme_content_json_admin_redirect_url());
        exit;
    }
}
add_action('admin_post_logical_theme_content_json_sync', 'logical_theme_content_json_handle_admin_sync');

if (!function_exists('logical_theme_content_json_render_admin_page')) {
    function logical_theme_content_json_render_admin_page()
    {
        logical_theme_content_json_require_manage_options();

        $report = get_option(LOGICAL_THEME_CONTENT_JSON_SYNC_REPORT_OPTION, array());
        $summary = isset($report['summary']) && is_array($report['summary']) ? $report['summary'] : array();
        $items = isset($report['items']) && is_array($report['items']) ? $report['items'] : array();
        $ran_at = isset($report['ran_at']) ? (string) $report['ran_at'] : '';
        $operation = isset($report['operation']) ? (string) $report['operation'] : '';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Content JSON Sync', 'wp-logical-theme'); ?></h1>
            <p><?php esc_html_e('Sync between assets/json files and published posts/pages.', 'wp-logical-theme'); ?></p>

            <div style="display:flex;gap:12px;flex-wrap:wrap;margin:16px 0;">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="logical_theme_content_json_import" />
                    <?php wp_nonce_field('logical_theme_content_json_import'); ?>
                    <?php submit_button(__('Import (Files → DB)', 'wp-logical-theme'), 'secondary', 'submit', false); ?>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="logical_theme_content_json_export" />
                    <?php wp_nonce_field('logical_theme_content_json_export'); ?>
                    <?php submit_button(__('Export (DB → Files)', 'wp-logical-theme'), 'secondary', 'submit', false); ?>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="logical_theme_content_json_sync" />
                    <?php wp_nonce_field('logical_theme_content_json_sync'); ?>
                    <?php submit_button(__('Sync Bidirectional', 'wp-logical-theme'), 'primary', 'submit', false); ?>
                </form>
            </div>

            <?php if (isset($_GET['logical_theme_content_json_notice']) && is_array($report) && !empty($report)) : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php
                        printf(
                            /* translators: 1: operation, 2: timestamp */
                            esc_html__('Operation "%1$s" completed at %2$s.', 'wp-logical-theme'),
                            esc_html($operation !== '' ? $operation : 'n/a'),
                            esc_html($ran_at !== '' ? $ran_at : 'n/a')
                        );
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!empty($summary)) : ?>
                <h2><?php esc_html_e('Summary', 'wp-logical-theme'); ?></h2>
                <table class="widefat striped" style="max-width:720px;">
                    <thead>
                    <tr>
                        <th><?php esc_html_e('Processed', 'wp-logical-theme'); ?></th>
                        <th><?php esc_html_e('Updated', 'wp-logical-theme'); ?></th>
                        <th><?php esc_html_e('Created', 'wp-logical-theme'); ?></th>
                        <th><?php esc_html_e('Skipped', 'wp-logical-theme'); ?></th>
                        <th><?php esc_html_e('Errors', 'wp-logical-theme'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?php echo esc_html((string) (int) ($summary['processed'] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) (int) ($summary['updated'] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) (int) ($summary['created'] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) (int) ($summary['skipped'] ?? 0)); ?></td>
                        <td><?php echo esc_html((string) (int) ($summary['error'] ?? 0)); ?></td>
                    </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($items)) : ?>
                <h2 style="margin-top:24px;"><?php esc_html_e('Details', 'wp-logical-theme'); ?></h2>
                <table class="widefat striped">
                    <thead>
                    <tr>
                        <th><?php esc_html_e('Phase', 'wp-logical-theme'); ?></th>
                        <th><?php esc_html_e('Slug', 'wp-logical-theme'); ?></th>
                        <th><?php esc_html_e('Post ID', 'wp-logical-theme'); ?></th>
                        <th><?php esc_html_e('Status', 'wp-logical-theme'); ?></th>
                        <th><?php esc_html_e('Message', 'wp-logical-theme'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item) : ?>
                        <tr>
                            <td><?php echo esc_html(isset($item['phase']) ? (string) $item['phase'] : ''); ?></td>
                            <td><?php echo esc_html(isset($item['slug']) ? (string) $item['slug'] : ''); ?></td>
                            <td><?php echo esc_html(isset($item['post_id']) ? (string) (int) $item['post_id'] : ''); ?></td>
                            <td><?php echo esc_html(isset($item['status']) ? (string) $item['status'] : ''); ?></td>
                            <td><?php echo esc_html(isset($item['message']) ? (string) $item['message'] : ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }
}

if (!function_exists('logical_theme_content_json_register_admin_page')) {
    function logical_theme_content_json_register_admin_page()
    {
        add_management_page(
            __('Content JSON', 'wp-logical-theme'),
            __('Content JSON', 'wp-logical-theme'),
            'manage_options',
            'logical-theme-content-json',
            'logical_theme_content_json_render_admin_page'
        );
    }
}
add_action('admin_menu', 'logical_theme_content_json_register_admin_page');
