<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns the option name used to persist block availability settings.
 */
function the_logical_theme_block_availability_option_name(): string
{
    return 'the_logical_theme_block_availability_settings';
}

/**
 * Returns the curated default block groups used by the theme.
 */
function the_logical_theme_curated_block_groups(): array
{
    return [
        'core' => [
            'label' => __('Core', 'the-logical-theme'),
            'description' => __('Content and theme structure blocks always available to the theme.', 'the-logical-theme'),
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
        'blog' => [
            'label' => __('Blog', 'the-logical-theme'),
            'description' => __('Dynamic post and query blocks used for editorial content.', 'the-logical-theme'),
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
        'woocommerce' => [
            'label' => __('WooCommerce', 'the-logical-theme'),
            'description' => __('Commerce-specific blocks available only when WooCommerce is active.', 'the-logical-theme'),
            'blocks' => [],
        ],
        'third_party' => [
            'label' => __('Third-Party', 'the-logical-theme'),
            'description' => __('Blocks registered by installed plugins or external code outside the theme-managed categories.', 'the-logical-theme'),
            'blocks' => [],
        ],
        'custom' => [
            'label' => __('Custom', 'the-logical-theme'),
            'description' => __('Theme custom blocks managed separately from the curated core categories.', 'the-logical-theme'),
            'blocks' => [],
        ],
    ];
}

/**
 * Returns all registered block types keyed by name.
 */
function the_logical_theme_get_registered_block_types(): array
{
    if (! class_exists('WP_Block_Type_Registry')) {
        return [];
    }

    return WP_Block_Type_Registry::get_instance()->get_all_registered();
}

/**
 * Returns all registered block names for a namespace prefix.
 */
function the_logical_theme_get_registered_block_names_by_prefix(string $prefix): array
{
    $block_names = [];

    foreach (the_logical_theme_get_registered_block_types() as $block_name => $block_type) {
        if (! is_string($block_name) || ! str_starts_with($block_name, $prefix)) {
            continue;
        }

        $block_names[] = $block_name;
    }

    sort($block_names);

    return array_values(array_unique($block_names));
}

/**
 * Returns the registered core block names that belong in the blog category.
 */
function the_logical_theme_get_registered_blog_block_names(): array
{
    $blog_blocks = the_logical_theme_curated_block_groups()['blog']['blocks'];
    $forced_core_lookup = array_fill_keys(
        the_logical_theme_curated_block_groups()['core']['blocks'],
        true
    );

    foreach (the_logical_theme_get_registered_block_types() as $block_name => $block_type) {
        if (! is_string($block_name) || ! str_starts_with($block_name, 'core/')) {
            continue;
        }

        if (isset($forced_core_lookup[$block_name])) {
            continue;
        }

        if (method_exists($block_type, 'is_dynamic') && $block_type->is_dynamic()) {
            $blog_blocks[] = $block_name;
        }
    }

    sort($blog_blocks);

    return array_values(array_unique($blog_blocks));
}

/**
 * Returns the registered core block names that belong in the core category.
 */
function the_logical_theme_get_registered_core_block_names(): array
{
    $core_blocks = the_logical_theme_get_registered_block_names_by_prefix('core/');
    $blog_lookup = array_fill_keys(the_logical_theme_get_registered_blog_block_names(), true);

    return array_values(array_filter(
        $core_blocks,
        static fn (string $block_name): bool => ! isset($blog_lookup[$block_name])
    ));
}

/**
 * Returns whether WooCommerce is active in the current request.
 */
function the_logical_theme_has_woocommerce(): bool
{
    return class_exists('WooCommerce') || defined('WC_VERSION');
}

/**
 * Returns registered WooCommerce block names.
 */
function the_logical_theme_get_woocommerce_block_names(): array
{
    if (! the_logical_theme_has_woocommerce()) {
        return [];
    }

    return the_logical_theme_get_registered_block_names_by_prefix('woocommerce/');
}

/**
 * Returns registered third-party block names outside the theme-managed namespaces.
 */
function the_logical_theme_get_third_party_block_names(): array
{
    $block_names = [];

    foreach (the_logical_theme_get_registered_block_types() as $block_name => $block_type) {
        if (! is_string($block_name) || ! str_contains($block_name, '/')) {
            continue;
        }

        if (
            str_starts_with($block_name, 'core/')
            || str_starts_with($block_name, 'woocommerce/')
            || str_starts_with($block_name, 'custom/')
        ) {
            continue;
        }

        $block_names[] = $block_name;
    }

    sort($block_names);

    return array_values(array_unique($block_names));
}

/**
 * Returns the full block catalog grouped by category.
 */
function the_logical_theme_get_block_catalog(): array
{
    $groups = the_logical_theme_curated_block_groups();
    $groups['core']['blocks'] = the_logical_theme_get_registered_core_block_names();
    $groups['blog']['blocks'] = the_logical_theme_get_registered_blog_block_names();
    $groups['woocommerce']['blocks'] = the_logical_theme_get_woocommerce_block_names();
    $groups['third_party']['blocks'] = the_logical_theme_get_third_party_block_names();
    $groups['custom']['blocks'] = the_logical_theme_get_custom_block_names();

    return $groups;
}

/**
 * Returns the categories shown in the admin UI.
 */
function the_logical_theme_get_block_availability_admin_categories(): array
{
    $groups = the_logical_theme_get_block_catalog();

    if (! the_logical_theme_has_woocommerce()) {
        unset($groups['woocommerce']);
    }

    return $groups;
}

/**
 * Returns the enabled-by-default category flags.
 */
function the_logical_theme_get_default_enabled_block_categories(): array
{
    return [
        'blog' => true,
    ];
}

/**
 * Returns the default allowed block map derived from the runtime catalog.
 */
function the_logical_theme_get_default_allowed_block_map(): array
{
    $catalog = the_logical_theme_get_block_catalog();
    $curated_groups = the_logical_theme_curated_block_groups();
    $allowed_blocks = [
        'core' => array_values(array_intersect($catalog['core']['blocks'], $curated_groups['core']['blocks'])),
        'blog' => array_values(array_intersect($catalog['blog']['blocks'], $curated_groups['blog']['blocks'])),
        'woocommerce' => [],
        'third_party' => $catalog['third_party']['blocks'],
        'custom' => $catalog['custom']['blocks'],
    ];

    if (! empty($catalog['woocommerce']['blocks'])) {
        $allowed_blocks['woocommerce'] = $catalog['woocommerce']['blocks'];
    }

    return $allowed_blocks;
}

/**
 * Normalizes block availability settings against the runtime catalog.
 */
function the_logical_theme_normalize_block_availability_settings($value, bool $strict_submitted_categories = false): array
{
    $catalog = the_logical_theme_get_block_catalog();
    $defaults = [
        'enabled_categories' => the_logical_theme_get_default_enabled_block_categories(),
        'allowed_blocks' => the_logical_theme_get_default_allowed_block_map(),
    ];

    if (! is_array($value)) {
        return $defaults;
    }

    $sanitized = $defaults;
    $enabled_categories = isset($value['enabled_categories']) && is_array($value['enabled_categories'])
        ? $value['enabled_categories']
        : [];

    $sanitized['enabled_categories']['blog'] = ! empty($enabled_categories['blog']);

    $raw_allowed_blocks = isset($value['allowed_blocks']) && is_array($value['allowed_blocks'])
        ? $value['allowed_blocks']
        : [];
    $submitted_categories = isset($value['submitted_categories']) && is_array($value['submitted_categories'])
        ? array_map('sanitize_text_field', wp_unslash($value['submitted_categories']))
        : [];
    $submitted_lookup = array_fill_keys($submitted_categories, true);

    foreach ($catalog as $category_key => $category) {
        $category_catalog = array_values(array_unique($category['blocks']));
        $category_lookup = array_fill_keys($category_catalog, true);
        $has_submitted_category = isset($submitted_lookup[$category_key]);
        $has_saved_category = array_key_exists($category_key, $raw_allowed_blocks) && is_array($raw_allowed_blocks[$category_key]);
        $requested_blocks = $category_catalog;

        if ($strict_submitted_categories) {
            if ($has_submitted_category) {
                $requested_blocks = $has_saved_category ? $raw_allowed_blocks[$category_key] : [];
            }
        } elseif ($has_saved_category) {
            $requested_blocks = isset($raw_allowed_blocks[$category_key]) && is_array($raw_allowed_blocks[$category_key])
                ? $raw_allowed_blocks[$category_key]
                : $category_catalog;
        }

        $sanitized_blocks = [];

        foreach ($requested_blocks as $block_name) {
            if (! is_string($block_name)) {
                continue;
            }

            $normalized_block_name = sanitize_text_field(wp_unslash($block_name));

            if (isset($category_lookup[$normalized_block_name])) {
                $sanitized_blocks[] = $normalized_block_name;
            }
        }

        $sanitized['allowed_blocks'][$category_key] = array_values(array_unique($sanitized_blocks));
    }

    return $sanitized;
}

/**
 * Sanitizes persisted block availability settings.
 */
function the_logical_theme_sanitize_block_availability_settings($value): array
{
    $sanitized = the_logical_theme_normalize_block_availability_settings($value, true);

    $GLOBALS['the_logical_theme_block_availability_settings_override'] = $sanitized;
    $GLOBALS['the_logical_theme_block_availability_run_exports_on_shutdown'] = false;
    the_logical_theme_block_availability_utility_log('Sanitize callback triggered for block availability save.');

    try {
        $registryPath = the_logical_theme_export_block_registry_json();
        the_logical_theme_export_whitelisted_blocks_markdown($registryPath);
        the_logical_theme_block_availability_utility_log('Synchronous block availability exports completed successfully.');
        delete_transient('the_logical_theme_block_availability_export_error');
    } catch (Throwable $throwable) {
        the_logical_theme_block_availability_utility_log('Synchronous block availability exports failed: ' . $throwable->getMessage());
        set_transient(
            'the_logical_theme_block_availability_export_error',
            $throwable->getMessage(),
            MINUTE_IN_SECONDS * 5
        );
    }

    unset($GLOBALS['the_logical_theme_block_availability_settings_override']);

    return $sanitized;
}

/**
 * Returns normalized block availability settings.
 */
function the_logical_theme_get_block_availability_settings(): array
{
    if (
        isset($GLOBALS['the_logical_theme_block_availability_settings_override'])
        && is_array($GLOBALS['the_logical_theme_block_availability_settings_override'])
    ) {
        return $GLOBALS['the_logical_theme_block_availability_settings_override'];
    }

    $saved_settings = get_option(the_logical_theme_block_availability_option_name(), []);

    return the_logical_theme_normalize_block_availability_settings($saved_settings);
}

/**
 * Returns whether a category is enabled for the editor whitelist.
 */
function the_logical_theme_is_block_category_enabled(string $category_key, array $settings): bool
{
    if ('core' === $category_key || 'custom' === $category_key || 'third_party' === $category_key) {
        return true;
    }

    if ('blog' === $category_key) {
        return ! empty($settings['enabled_categories']['blog']);
    }

    if ('woocommerce' === $category_key) {
        return the_logical_theme_has_woocommerce();
    }

    return false;
}

/**
 * Returns the current allowed block list.
 */
function the_logical_theme_allowed_blocks(): array
{
    $catalog = the_logical_theme_get_block_catalog();
    $settings = the_logical_theme_get_block_availability_settings();
    $allowed_blocks = [];

    foreach ($catalog as $category_key => $category) {
        if (! the_logical_theme_is_block_category_enabled($category_key, $settings)) {
            continue;
        }

        $allowed_blocks = array_merge(
            $allowed_blocks,
            $settings['allowed_blocks'][$category_key] ?? []
        );
    }

    return array_values(array_unique($allowed_blocks));
}

add_filter('allowed_block_types_all', function ($allowed_blocks, $editor_context) {
    return the_logical_theme_allowed_blocks();
}, 10, 2);
