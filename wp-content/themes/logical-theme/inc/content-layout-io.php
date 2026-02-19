<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('LOGICAL_THEME_LAYOUT_VNEXT_VERSION')) {
    define('LOGICAL_THEME_LAYOUT_VNEXT_VERSION', '4.0');
}

if (!function_exists('logical_theme_layout_io_assets_dir')) {
    function logical_theme_layout_io_assets_dir()
    {
        return trailingslashit(get_stylesheet_directory()) . 'assets/json';
    }
}

if (!function_exists('logical_theme_layout_io_resolve_source_file')) {
    function logical_theme_layout_io_resolve_source_file($slug)
    {
        $slug = sanitize_title((string) $slug);
        if ($slug === '') {
            return '';
        }

        $candidates = array(
            trailingslashit(get_stylesheet_directory()) . 'assets/json/' . $slug . '.json',
        );

        $template_dir = get_template_directory();
        if (is_string($template_dir) && $template_dir !== get_stylesheet_directory()) {
            $candidates[] = trailingslashit($template_dir) . 'assets/json/' . $slug . '.json';
        }

        foreach ($candidates as $file) {
            if (file_exists($file)) {
                return $file;
            }
        }

        return '';
    }
}

if (!function_exists('logical_theme_layout_io_build_block')) {
    function logical_theme_layout_io_build_block($name, $attrs = array(), $inner_blocks = array())
    {
        $inner_blocks = is_array($inner_blocks) ? $inner_blocks : array();
        $inner_content = array();
        if (!empty($inner_blocks)) {
            $inner_content = array_fill(0, count($inner_blocks), null);
        }

        return array(
            'blockName' => (string) $name,
            'attrs' => is_array($attrs) ? $attrs : array(),
            'innerBlocks' => $inner_blocks,
            'innerHTML' => '',
            'innerContent' => $inner_content,
        );
    }
}

if (!function_exists('logical_theme_layout_io_find_layout_block')) {
    function logical_theme_layout_io_find_layout_block($blocks)
    {
        if (!is_array($blocks)) {
            return null;
        }

        foreach ($blocks as $block) {
            if (!is_array($block)) {
                continue;
            }

            $name = isset($block['blockName']) ? (string) $block['blockName'] : '';
            if ($name === 'logical-theme/layout') {
                return $block;
            }

            $nested = logical_theme_layout_io_find_layout_block(isset($block['innerBlocks']) ? $block['innerBlocks'] : array());
            if (is_array($nested)) {
                return $nested;
            }
        }

        return null;
    }
}

if (!function_exists('logical_theme_layout_io_read_item')) {
    function logical_theme_layout_io_read_item($item_block, $index)
    {
        $name = isset($item_block['blockName']) ? (string) $item_block['blockName'] : '';
        $attrs = isset($item_block['attrs']) && is_array($item_block['attrs']) ? $item_block['attrs'] : array();
        $fallback_id = 'item_' . (string) max(1, (int) $index);
        $item_id = isset($attrs['itemId']) ? sanitize_key((string) $attrs['itemId']) : '';
        if ($item_id === '') {
            $item_id = $fallback_id;
        }

        if ($name === 'logical-theme/pretitle') {
            return array(
                'id' => $item_id,
                'type' => 'pretitle',
                'data' => array('text' => isset($attrs['text']) ? sanitize_text_field((string) $attrs['text']) : ''),
                'settings' => array(),
            );
        }

        if ($name === 'logical-theme/title') {
            $level = isset($attrs['level']) ? sanitize_key((string) $attrs['level']) : 'h2';
            if (!in_array($level, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'), true)) {
                $level = 'h2';
            }

            return array(
                'id' => $item_id,
                'type' => 'title',
                'data' => array(
                    'text' => isset($attrs['text']) ? sanitize_text_field((string) $attrs['text']) : '',
                    'level' => $level,
                ),
                'settings' => array(),
            );
        }

        if ($name === 'logical-theme/text') {
            return array(
                'id' => $item_id,
                'type' => 'text',
                'data' => array('text' => isset($attrs['text']) ? wp_kses_post((string) $attrs['text']) : ''),
                'settings' => array(),
            );
        }

        if ($name === 'logical-theme/image') {
            return array(
                'id' => $item_id,
                'type' => 'image',
                'data' => array(
                    'id' => isset($attrs['id']) ? (int) $attrs['id'] : 0,
                    'src' => isset($attrs['src']) ? esc_url_raw((string) $attrs['src']) : '',
                    'alt' => isset($attrs['alt']) ? sanitize_text_field((string) $attrs['alt']) : '',
                ),
                'settings' => array(),
            );
        }

        if ($name === 'logical-theme/button') {
            $variant = isset($attrs['variant']) ? sanitize_key((string) $attrs['variant']) : 'primary';
            if (!in_array($variant, array('primary', 'secondary', 'outline', 'link'), true)) {
                $variant = 'primary';
            }
            $target = isset($attrs['target']) && (string) $attrs['target'] === '_blank' ? '_blank' : '_self';
            return array(
                'id' => $item_id,
                'type' => 'button',
                'data' => array(
                    'label' => isset($attrs['label']) ? sanitize_text_field((string) $attrs['label']) : '',
                    'url' => isset($attrs['url']) ? esc_url_raw((string) $attrs['url']) : '',
                    'variant' => $variant,
                    'target' => $target,
                ),
                'settings' => array(),
            );
        }

        if ($name === 'core/embed') {
            return array(
                'id' => $item_id,
                'type' => 'embed',
                'data' => array(
                    'url' => isset($attrs['url']) ? esc_url_raw((string) $attrs['url']) : '',
                    'provider' => isset($attrs['providerNameSlug']) ? sanitize_key((string) $attrs['providerNameSlug']) : '',
                ),
                'settings' => array(),
            );
        }

        return null;
    }
}

if (!function_exists('logical_theme_layout_io_extract_vnext_from_content')) {
    function logical_theme_layout_io_extract_vnext_from_content($post_content)
    {
        $blocks = parse_blocks((string) $post_content);
        $layout_block = logical_theme_layout_io_find_layout_block($blocks);
        if (!is_array($layout_block)) {
            return array(
                'version' => LOGICAL_THEME_LAYOUT_VNEXT_VERSION,
                'layout' => array(),
            );
        }

        $rows = array();
        $inner_rows = isset($layout_block['innerBlocks']) && is_array($layout_block['innerBlocks']) ? $layout_block['innerBlocks'] : array();
        foreach ($inner_rows as $row_index => $row_block) {
            if (!is_array($row_block) || (isset($row_block['blockName']) ? (string) $row_block['blockName'] : '') !== 'logical-theme/row') {
                continue;
            }

            $row_attrs = isset($row_block['attrs']) && is_array($row_block['attrs']) ? $row_block['attrs'] : array();
            $row_id = isset($row_attrs['rowId']) ? sanitize_key((string) $row_attrs['rowId']) : '';
            if ($row_id === '') {
                $row_id = 'row_' . (string) max(1, (int) $row_index + 1);
            }

            $columns = array();
            $inner_columns = isset($row_block['innerBlocks']) && is_array($row_block['innerBlocks']) ? $row_block['innerBlocks'] : array();
            foreach ($inner_columns as $col_index => $column_block) {
                if (!is_array($column_block) || (isset($column_block['blockName']) ? (string) $column_block['blockName'] : '') !== 'logical-theme/column') {
                    continue;
                }

                $col_attrs = isset($column_block['attrs']) && is_array($column_block['attrs']) ? $column_block['attrs'] : array();
                $column_id = isset($col_attrs['columnId']) ? sanitize_key((string) $col_attrs['columnId']) : '';
                if ($column_id === '') {
                    $column_id = 'col_' . (string) max(1, (int) $col_index + 1);
                }

                $items = array();
                $inner_items = isset($column_block['innerBlocks']) && is_array($column_block['innerBlocks']) ? $column_block['innerBlocks'] : array();
                foreach ($inner_items as $item_index => $item_block) {
                    $mapped = logical_theme_layout_io_read_item($item_block, (int) $item_index + 1);
                    if (is_array($mapped)) {
                        $items[] = $mapped;
                    }
                }

                $columns[] = array(
                    'id' => $column_id,
                    'type' => 'column',
                    'settings' => array(
                        'desktop' => max(1, min(12, isset($col_attrs['desktop']) ? (int) $col_attrs['desktop'] : 12)),
                        'tablet' => max(1, min(12, isset($col_attrs['tablet']) ? (int) $col_attrs['tablet'] : 12)),
                        'mobile' => max(1, min(12, isset($col_attrs['mobile']) ? (int) $col_attrs['mobile'] : 12)),
                        'alignY' => isset($col_attrs['alignY']) ? sanitize_key((string) $col_attrs['alignY']) : 'stretch',
                    ),
                    'items' => $items,
                );
            }

            $rows[] = array(
                'id' => $row_id,
                'type' => 'row',
                'settings' => array(
                    'container' => isset($row_attrs['container']) ? sanitize_key((string) $row_attrs['container']) : 'default',
                    'gap' => isset($row_attrs['gap']) ? sanitize_key((string) $row_attrs['gap']) : 'md',
                    'alignY' => isset($row_attrs['alignY']) ? sanitize_key((string) $row_attrs['alignY']) : 'stretch',
                    'backgroundColor' => isset($row_attrs['backgroundColor']) ? sanitize_key((string) $row_attrs['backgroundColor']) : '',
                ),
                'columns' => $columns,
            );
        }

        return array(
            'version' => LOGICAL_THEME_LAYOUT_VNEXT_VERSION,
            'layout' => $rows,
        );
    }
}

if (!function_exists('logical_theme_layout_io_build_item_block')) {
    function logical_theme_layout_io_build_item_block($item, $item_index)
    {
        if (!is_array($item)) {
            return null;
        }

        $type = isset($item['type']) ? sanitize_key((string) $item['type']) : '';
        $id = isset($item['id']) ? sanitize_key((string) $item['id']) : '';
        if ($id === '') {
            $id = 'item_' . (string) max(1, (int) $item_index);
        }

        $data = isset($item['data']) && is_array($item['data']) ? $item['data'] : array();

        if ($type === 'pretitle') {
            return logical_theme_layout_io_build_block('logical-theme/pretitle', array(
                'itemId' => $id,
                'text' => isset($data['text']) ? sanitize_text_field((string) $data['text']) : '',
            ));
        }

        if ($type === 'title') {
            $level = isset($data['level']) ? sanitize_key((string) $data['level']) : 'h2';
            if (!in_array($level, array('h1', 'h2', 'h3', 'h4', 'h5', 'h6'), true)) {
                $level = 'h2';
            }

            return logical_theme_layout_io_build_block('logical-theme/title', array(
                'itemId' => $id,
                'text' => isset($data['text']) ? sanitize_text_field((string) $data['text']) : '',
                'level' => $level,
            ));
        }

        if ($type === 'text') {
            return logical_theme_layout_io_build_block('logical-theme/text', array(
                'itemId' => $id,
                'text' => isset($data['text']) ? wp_kses_post((string) $data['text']) : '',
            ));
        }

        if ($type === 'image') {
            return logical_theme_layout_io_build_block('logical-theme/image', array(
                'itemId' => $id,
                'id' => isset($data['id']) ? (int) $data['id'] : 0,
                'src' => isset($data['src']) ? esc_url_raw((string) $data['src']) : '',
                'alt' => isset($data['alt']) ? sanitize_text_field((string) $data['alt']) : '',
            ));
        }

        if ($type === 'button') {
            $variant = isset($data['variant']) ? sanitize_key((string) $data['variant']) : 'primary';
            if (!in_array($variant, array('primary', 'secondary', 'outline', 'link'), true)) {
                $variant = 'primary';
            }

            return logical_theme_layout_io_build_block('logical-theme/button', array(
                'itemId' => $id,
                'label' => isset($data['label']) ? sanitize_text_field((string) $data['label']) : '',
                'url' => isset($data['url']) ? esc_url_raw((string) $data['url']) : '',
                'variant' => $variant,
                'target' => (isset($data['target']) && (string) $data['target'] === '_blank') ? '_blank' : '_self',
            ));
        }

        if ($type === 'embed') {
            return logical_theme_layout_io_build_block('core/embed', array(
                'url' => isset($data['url']) ? esc_url_raw((string) $data['url']) : '',
                'providerNameSlug' => isset($data['provider']) ? sanitize_key((string) $data['provider']) : '',
            ));
        }

        return null;
    }
}

if (!function_exists('logical_theme_layout_io_build_blocks_from_layout')) {
    function logical_theme_layout_io_build_blocks_from_layout($layout)
    {
        $rows_input = is_array($layout) ? $layout : array();
        $row_blocks = array();

        foreach ($rows_input as $row_index => $row) {
            if (!is_array($row)) {
                continue;
            }

            $row_settings = isset($row['settings']) && is_array($row['settings']) ? $row['settings'] : array();
            $row_id = isset($row['id']) ? sanitize_key((string) $row['id']) : '';
            if ($row_id === '') {
                $row_id = 'row_' . (string) max(1, (int) $row_index + 1);
            }

            $column_blocks = array();
            $columns_input = isset($row['columns']) && is_array($row['columns']) ? $row['columns'] : array();
            foreach ($columns_input as $col_index => $column) {
                if (!is_array($column)) {
                    continue;
                }

                $column_settings = isset($column['settings']) && is_array($column['settings']) ? $column['settings'] : array();
                $column_id = isset($column['id']) ? sanitize_key((string) $column['id']) : '';
                if ($column_id === '') {
                    $column_id = 'col_' . (string) max(1, (int) $col_index + 1);
                }

                $item_blocks = array();
                $items_input = isset($column['items']) && is_array($column['items']) ? $column['items'] : array();
                foreach ($items_input as $item_index => $item) {
                    $item_block = logical_theme_layout_io_build_item_block($item, (int) $item_index + 1);
                    if (is_array($item_block)) {
                        $item_blocks[] = $item_block;
                    }
                }

                $column_blocks[] = logical_theme_layout_io_build_block('logical-theme/column', array(
                    'columnId' => $column_id,
                    'desktop' => max(1, min(12, isset($column_settings['desktop']) ? (int) $column_settings['desktop'] : 12)),
                    'tablet' => max(1, min(12, isset($column_settings['tablet']) ? (int) $column_settings['tablet'] : 12)),
                    'mobile' => max(1, min(12, isset($column_settings['mobile']) ? (int) $column_settings['mobile'] : 12)),
                    'alignY' => isset($column_settings['alignY']) ? sanitize_key((string) $column_settings['alignY']) : 'stretch',
                ), $item_blocks);
            }

            $row_blocks[] = logical_theme_layout_io_build_block('logical-theme/row', array(
                'rowId' => $row_id,
                'container' => isset($row_settings['container']) ? sanitize_key((string) $row_settings['container']) : 'default',
                'gap' => isset($row_settings['gap']) ? sanitize_key((string) $row_settings['gap']) : 'md',
                'alignY' => isset($row_settings['alignY']) ? sanitize_key((string) $row_settings['alignY']) : 'stretch',
                'backgroundColor' => isset($row_settings['backgroundColor']) ? sanitize_key((string) $row_settings['backgroundColor']) : '',
            ), $column_blocks);
        }

        return array(
            logical_theme_layout_io_build_block('logical-theme/layout', array(
                'layoutVersion' => LOGICAL_THEME_LAYOUT_VNEXT_VERSION,
            ), $row_blocks),
        );
    }
}

if (!function_exists('logical_theme_layout_io_export_post')) {
    function logical_theme_layout_io_export_post($post_id)
    {
        $post_id = (int) $post_id;
        if ($post_id <= 0) {
            return new WP_Error('logical_theme_layout_export_post', __('Invalid post id.', 'wp-logical-theme'));
        }

        $post = get_post($post_id);
        if (!($post instanceof WP_Post)) {
            return new WP_Error('logical_theme_layout_export_post', __('Post not found.', 'wp-logical-theme'));
        }

        $slug = sanitize_title((string) $post->post_name);
        if ($slug === '') {
            $slug = $post->post_type . '-' . (string) $post_id;
        }

        $payload = logical_theme_layout_io_extract_vnext_from_content((string) $post->post_content);
        $payload['meta'] = array(
            'postType' => (string) $post->post_type,
            'postId' => $post_id,
            'slug' => $slug,
            'exportedAt' => gmdate('c'),
        );

        $json = wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || $json === '') {
            return new WP_Error('logical_theme_layout_export_post', __('Cannot encode JSON payload.', 'wp-logical-theme'));
        }

        $target_dir = logical_theme_layout_io_assets_dir();
        if (!wp_mkdir_p($target_dir)) {
            return new WP_Error('logical_theme_layout_export_post', __('Cannot create assets/json directory.', 'wp-logical-theme'));
        }

        $target_file = trailingslashit($target_dir) . $slug . '.json';
        if (false === file_put_contents($target_file, $json . PHP_EOL, LOCK_EX)) {
            return new WP_Error('logical_theme_layout_export_post', __('Cannot write JSON file.', 'wp-logical-theme'));
        }

        return array('post_id' => $post_id, 'slug' => $slug, 'file' => $target_file);
    }
}

if (!function_exists('logical_theme_layout_io_resolve_post_by_slug')) {
    function logical_theme_layout_io_resolve_post_by_slug($slug)
    {
        $slug = sanitize_title((string) $slug);
        if ($slug === '') {
            return 0;
        }

        $ids = get_posts(array(
            'post_type' => array('page', 'post'),
            'post_status' => array('publish', 'private', 'draft', 'pending', 'future'),
            'name' => $slug,
            'posts_per_page' => 1,
            'fields' => 'ids',
        ));

        return (is_array($ids) && isset($ids[0])) ? (int) $ids[0] : 0;
    }
}

if (!function_exists('logical_theme_layout_io_title_from_slug')) {
    function logical_theme_layout_io_title_from_slug($slug)
    {
        $slug = sanitize_title((string) $slug);
        if ($slug === '') {
            return __('Imported page', 'wp-logical-theme');
        }

        $title = str_replace(array('-', '_'), ' ', $slug);
        $title = trim(preg_replace('/\s+/', ' ', (string) $title));
        if ($title === '') {
            return __('Imported page', 'wp-logical-theme');
        }

        return ucwords($title);
    }
}

if (!function_exists('logical_theme_layout_io_import_slug')) {
    function logical_theme_layout_io_import_slug($slug)
    {
        $slug = sanitize_title((string) $slug);
        if ($slug === '') {
            return new WP_Error('logical_theme_layout_import_slug', __('Invalid slug.', 'wp-logical-theme'));
        }

        $source_file = logical_theme_layout_io_resolve_source_file($slug);
        if ($source_file === '') {
            return new WP_Error('logical_theme_layout_import_slug', __('JSON file not found for slug.', 'wp-logical-theme'));
        }

        $raw = file_get_contents($source_file);
        $decoded = json_decode(is_string($raw) ? $raw : '', true);
        if (!is_array($decoded)) {
            return new WP_Error('logical_theme_layout_import_slug', __('Invalid JSON document.', 'wp-logical-theme'));
        }

        if (!isset($decoded['version']) || (string) $decoded['version'] !== LOGICAL_THEME_LAYOUT_VNEXT_VERSION) {
            return new WP_Error('logical_theme_layout_import_slug', __('Unsupported JSON version for import.', 'wp-logical-theme'));
        }

        $layout = isset($decoded['layout']) && is_array($decoded['layout']) ? $decoded['layout'] : null;
        if (!is_array($layout)) {
            return new WP_Error('logical_theme_layout_import_slug', __('JSON layout must be an array.', 'wp-logical-theme'));
        }

        $post_id = logical_theme_layout_io_resolve_post_by_slug($slug);
        if ($post_id <= 0) {
            $created = wp_insert_post(array(
                'post_type' => 'page',
                'post_status' => 'draft',
                'post_name' => $slug,
                'post_title' => logical_theme_layout_io_title_from_slug($slug),
                'post_content' => '',
            ), true);
            if (is_wp_error($created)) {
                return $created;
            }
            $post_id = (int) $created;
        }

        $blocks = logical_theme_layout_io_build_blocks_from_layout($layout);
        $content = serialize_blocks($blocks);
        $updated = wp_update_post(array(
            'ID' => $post_id,
            'post_content' => $content,
        ), true);
        if (is_wp_error($updated)) {
            return $updated;
        }

        return array('post_id' => (int) $post_id, 'slug' => $slug, 'file' => $source_file);
    }
}

if (!function_exists('logical_theme_layout_io_admin_notice')) {
    function logical_theme_layout_io_admin_notice()
    {
        if (!isset($_GET['logical_theme_layout_io_notice'])) {
            return;
        }

        $type = isset($_GET['logical_theme_layout_io_notice']) ? sanitize_key((string) $_GET['logical_theme_layout_io_notice']) : 'success';
        $msg = isset($_GET['logical_theme_layout_io_message']) ? sanitize_text_field((string) $_GET['logical_theme_layout_io_message']) : '';
        if ($msg === '') {
            return;
        }

        $class = $type === 'error' ? 'notice notice-error' : 'notice notice-success';
        echo '<div class="' . esc_attr($class) . ' is-dismissible"><p>' . esc_html($msg) . '</p></div>';
    }
}

if (!function_exists('logical_theme_layout_io_admin_page')) {
    function logical_theme_layout_io_admin_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to access this page.', 'wp-logical-theme'));
        }
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Layout JSON Import/Export', 'wp-logical-theme'); ?></h1>
            <?php logical_theme_layout_io_admin_notice(); ?>

            <p><?php esc_html_e('Source of truth is Gutenberg blocks in post_content. Use these actions to import/export JSON explicitly.', 'wp-logical-theme'); ?></p>

            <h2><?php esc_html_e('Import (JSON -> Blocks)', 'wp-logical-theme'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="logical_theme_layout_import_slug" />
                <?php wp_nonce_field('logical_theme_layout_import_slug'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="logical-theme-layout-import-slug"><?php esc_html_e('Slug', 'wp-logical-theme'); ?></label></th>
                        <td><input id="logical-theme-layout-import-slug" name="slug" type="text" class="regular-text" required /></td>
                    </tr>
                </table>
                <?php submit_button(__('Import and Replace Blocks', 'wp-logical-theme')); ?>
            </form>

            <h2><?php esc_html_e('Export (Blocks -> JSON)', 'wp-logical-theme'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:1rem;">
                <input type="hidden" name="action" value="logical_theme_layout_export_slug" />
                <?php wp_nonce_field('logical_theme_layout_export_slug'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="logical-theme-layout-export-slug"><?php esc_html_e('Slug', 'wp-logical-theme'); ?></label></th>
                        <td><input id="logical-theme-layout-export-slug" name="slug" type="text" class="regular-text" required /></td>
                    </tr>
                </table>
                <?php submit_button(__('Export Selected Slug', 'wp-logical-theme'), 'secondary'); ?>
            </form>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="logical_theme_layout_export_all" />
                <?php wp_nonce_field('logical_theme_layout_export_all'); ?>
                <?php submit_button(__('Export All Pages/Posts', 'wp-logical-theme'), 'secondary'); ?>
            </form>
        </div>
        <?php
    }
}

if (!function_exists('logical_theme_layout_io_redirect')) {
    function logical_theme_layout_io_redirect($type, $message)
    {
        $url = add_query_arg(array(
            'page' => 'logical-theme-layout-io',
            'logical_theme_layout_io_notice' => $type,
            'logical_theme_layout_io_message' => rawurlencode($message),
        ), admin_url('tools.php'));
        wp_safe_redirect($url);
        exit;
    }
}

if (!function_exists('logical_theme_layout_io_handle_import_slug')) {
    function logical_theme_layout_io_handle_import_slug()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to run this action.', 'wp-logical-theme'));
        }
        check_admin_referer('logical_theme_layout_import_slug');

        $slug = isset($_POST['slug']) ? sanitize_text_field((string) $_POST['slug']) : '';
        $result = logical_theme_layout_io_import_slug($slug);
        if (is_wp_error($result)) {
            logical_theme_layout_io_redirect('error', $result->get_error_message());
        }

        logical_theme_layout_io_redirect('success', __('Import completed.', 'wp-logical-theme'));
    }
}
add_action('admin_post_logical_theme_layout_import_slug', 'logical_theme_layout_io_handle_import_slug');

if (!function_exists('logical_theme_layout_io_handle_export_slug')) {
    function logical_theme_layout_io_handle_export_slug()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to run this action.', 'wp-logical-theme'));
        }
        check_admin_referer('logical_theme_layout_export_slug');

        $slug = isset($_POST['slug']) ? sanitize_text_field((string) $_POST['slug']) : '';
        $post_id = logical_theme_layout_io_resolve_post_by_slug($slug);
        if ($post_id <= 0) {
            logical_theme_layout_io_redirect('error', __('No page/post found with matching slug.', 'wp-logical-theme'));
        }

        $result = logical_theme_layout_io_export_post($post_id);
        if (is_wp_error($result)) {
            logical_theme_layout_io_redirect('error', $result->get_error_message());
        }

        logical_theme_layout_io_redirect('success', __('Export completed.', 'wp-logical-theme'));
    }
}
add_action('admin_post_logical_theme_layout_export_slug', 'logical_theme_layout_io_handle_export_slug');

if (!function_exists('logical_theme_layout_io_handle_export_all')) {
    function logical_theme_layout_io_handle_export_all()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to run this action.', 'wp-logical-theme'));
        }
        check_admin_referer('logical_theme_layout_export_all');

        $ids = get_posts(array(
            'post_type' => array('page', 'post'),
            'post_status' => array('publish', 'private', 'draft', 'pending', 'future'),
            'posts_per_page' => -1,
            'fields' => 'ids',
            'orderby' => 'ID',
            'order' => 'ASC',
        ));

        if (!is_array($ids) || empty($ids)) {
            logical_theme_layout_io_redirect('error', __('No posts found to export.', 'wp-logical-theme'));
        }

        $errors = 0;
        foreach ($ids as $post_id) {
            $result = logical_theme_layout_io_export_post((int) $post_id);
            if (is_wp_error($result)) {
                $errors++;
            }
        }

        if ($errors > 0) {
            logical_theme_layout_io_redirect('error', sprintf(__('Export completed with %d errors.', 'wp-logical-theme'), $errors));
        }

        logical_theme_layout_io_redirect('success', __('Export completed for all pages/posts.', 'wp-logical-theme'));
    }
}
add_action('admin_post_logical_theme_layout_export_all', 'logical_theme_layout_io_handle_export_all');

if (!function_exists('logical_theme_layout_io_register_admin_page')) {
    function logical_theme_layout_io_register_admin_page()
    {
        add_management_page(
            __('Layout JSON IO', 'wp-logical-theme'),
            __('Layout JSON IO', 'wp-logical-theme'),
            'manage_options',
            'logical-theme-layout-io',
            'logical_theme_layout_io_admin_page'
        );
    }
}
add_action('admin_menu', 'logical_theme_layout_io_register_admin_page');
