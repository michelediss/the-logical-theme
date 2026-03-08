<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns the curated block groups used by the theme.
 */
function the_logical_theme_curated_block_groups(): array
{
    return [
        'content' => [
            'blocks' => [
                'core/group',
                'core/columns',
                'core/column',
                'core/spacer',
                'core/separator',
                'core/heading',
                'core/paragraph',
                'core/list',
                'core/list-item',
                'core/quote',
                'core/details',
                'core/image',
                'core/gallery',
                'core/cover',
                'core/media-text',
                'core/buttons',
                'core/button',
                'core/accordion',
                'core/accordion-item',
                'core/accordion-heading',
                'core/accordion-panel',
                'core/social-links',
                'core/social-link',
                'core/search',
            ],
        ],
        'theme' => [
            'blocks' => [
                'core/navigation',
                'core/navigation-link',
                'core/navigation-submenu',
                'core/home-link',
                'core/template-part',
                'core/site-logo',
                'core/site-title',
                'core/site-tagline',
            ],
        ],
        'dynamic' => [
            'blocks' => [
                'core/query',
                'core/post-template',
                'core/query-title',
                'core/query-total',
                'core/query-no-results',
                'core/query-pagination',
                'core/query-pagination-previous',
                'core/query-pagination-numbers',
                'core/query-pagination-next',
                'core/post-title',
                'core/post-content',
                'core/post-excerpt',
                'core/post-date',
                'core/post-featured-image',
                'core/post-terms',
                'core/post-navigation-link',
            ],
        ],
    ];
}

/**
 * Returns the full curated block list.
 */
function the_logical_theme_allowed_blocks(): array
{
    $allowed_blocks = [];

    foreach (the_logical_theme_curated_block_groups() as $group) {
        $allowed_blocks = array_merge($allowed_blocks, $group['blocks']);
    }

    $allowed_blocks = array_merge($allowed_blocks, the_logical_theme_get_custom_block_names());

    return array_values(array_unique($allowed_blocks));
}

add_filter('allowed_block_types_all', function ($allowed_blocks, $editor_context) {
    return the_logical_theme_allowed_blocks();
}, 10, 2);
