<?php

declare(strict_types=1);

/**
 * Registers the theme block pattern category and loads all pattern definitions.
 */

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Creates the custom pattern category used by this theme.
 */
function the_logical_theme_register_pattern_category(): void
{
    register_block_pattern_category(
        'the-logical-theme',
        ['label' => __('The Logical Theme', 'the-logical-theme')]
    );
}
add_action('init', 'the_logical_theme_register_pattern_category');

/**
 * Loads every PHP pattern file from the theme patterns directory.
 */
function the_logical_theme_register_patterns(): void
{
    $pattern_files = glob(get_theme_file_path('patterns/*.php'));

    if (false === $pattern_files) {
        return;
    }

    foreach ($pattern_files as $pattern_file) {
        require_once $pattern_file;
    }
}
add_action('init', 'the_logical_theme_register_patterns', 20);
