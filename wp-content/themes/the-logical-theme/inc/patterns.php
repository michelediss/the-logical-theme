<?php

declare(strict_types=1);

/**
 * Registers the theme block pattern category.
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
