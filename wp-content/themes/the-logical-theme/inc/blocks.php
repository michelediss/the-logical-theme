<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns all custom block metadata files located in the theme.
 *
 * Standard for future blocks:
 * - blocks/<slug>/block.json
 * - blocks/<slug>/render.php for dynamic markup
 * - src/js/blocks/<slug>/editor.js
 * - src/js/blocks/<slug>/view.js
 * - src/css/blocks/<slug>.css
 */
function the_logical_theme_get_custom_block_metadata_files(): array
{
    $files = glob(get_theme_file_path('blocks/*/block.json')) ?: [];
    sort($files);

    return $files;
}

/**
 * Returns the custom block names declared by the theme metadata.
 */
function the_logical_theme_get_custom_block_names(): array
{
    $names = [];

    foreach (the_logical_theme_get_custom_block_metadata_files() as $metadata_file) {
        $decoded = json_decode((string) file_get_contents($metadata_file), true);

        if (is_array($decoded) && ! empty($decoded['name']) && is_string($decoded['name'])) {
            $names[] = $decoded['name'];
        }
    }

    return array_values(array_unique($names));
}

/**
 * Registers the shared block assets resolved through the theme Vite pipeline.
 */
function the_logical_theme_register_block_assets(): void
{
    the_logical_theme_register_vite_style(
        'the-logical-theme-blocks-style',
        'src/css/blocks.css'
    );

    the_logical_theme_register_vite_script(
        'the-logical-theme-blocks-view',
        'src/js/blocks/view.js',
        []
    );

    the_logical_theme_register_vite_script(
        'the-logical-theme-blocks-editor',
        'src/js/blocks/editor.js',
        [
            'wp-blocks',
            'wp-block-editor',
            'wp-components',
            'wp-element',
            'wp-i18n',
            'wp-server-side-render',
        ]
    );
}
add_action('init', 'the_logical_theme_register_block_assets');

/**
 * Builds a render callback from a block-local render template.
 */
function the_logical_theme_get_block_render_callback(string $render_file): callable
{
    return static function (array $attributes = [], string $content = '', ?WP_Block $block = null) use ($render_file): string {
        if (! file_exists($render_file)) {
            return '';
        }

        ob_start();
        require $render_file;

        return (string) ob_get_clean();
    };
}

/**
 * Registers all custom theme blocks discovered in the blocks directory.
 */
function the_logical_theme_register_custom_blocks(): void
{
    foreach (the_logical_theme_get_custom_block_metadata_files() as $metadata_file) {
        $block_dir = dirname($metadata_file);
        $render_file = $block_dir . '/render.php';
        $args = [];

        if (file_exists($render_file)) {
            $args['render_callback'] = the_logical_theme_get_block_render_callback($render_file);
        }

        register_block_type($block_dir, $args);
    }
}
add_action('init', 'the_logical_theme_register_custom_blocks');
