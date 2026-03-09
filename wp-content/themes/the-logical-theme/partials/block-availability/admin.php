<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Registers the setting used to persist block availability controls.
 */
function the_logical_theme_register_block_availability_setting(): void
{
    register_setting(
        'the_logical_theme_block_availability',
        the_logical_theme_block_availability_option_name(),
        [
            'type' => 'array',
            'sanitize_callback' => 'the_logical_theme_sanitize_block_availability_settings',
            'default' => [
                'enabled_categories' => the_logical_theme_get_default_enabled_block_categories(),
                'allowed_blocks' => the_logical_theme_get_default_allowed_block_map(),
            ],
        ]
    );
}
add_action('admin_init', 'the_logical_theme_register_block_availability_setting');

/**
 * Adds the block availability page under Appearance.
 */
function the_logical_theme_add_block_availability_page(): void
{
    add_theme_page(
        __('Block Availability', 'the-logical-theme'),
        __('Block Availability', 'the-logical-theme'),
        'manage_options',
        'the-logical-theme-block-availability',
        'the_logical_theme_render_block_availability_page'
    );
}
add_action('admin_menu', 'the_logical_theme_add_block_availability_page');

/**
 * Enqueues admin-only assets for the block availability screen.
 */
function the_logical_theme_enqueue_block_availability_assets(string $hook_suffix): void
{
    if ('appearance_page_the-logical-theme-block-availability' !== $hook_suffix) {
        return;
    }

    $script_path = get_theme_file_path('assets/js/block-availability-admin.js');
    $style_path = get_theme_file_path('assets/css/admin/block-availability-admin.css');
    $script_version = file_exists($script_path) ? (string) filemtime($script_path) : TLT_VERSION;
    $style_version = file_exists($style_path) ? (string) filemtime($style_path) : TLT_VERSION;

    wp_enqueue_script(
        'the-logical-theme-block-availability-admin',
        get_theme_file_uri('assets/js/block-availability-admin.js'),
        [],
        $script_version,
        true
    );
    wp_add_inline_script(
        'the-logical-theme-block-availability-admin',
        'window.theLogicalThemeBlockAvailability = ' . wp_json_encode([
            'activeLabel' => __('active', 'the-logical-theme'),
        ]) . ';',
        'before'
    );

    wp_enqueue_style(
        'the-logical-theme-block-availability-admin',
        get_theme_file_uri('assets/css/admin/block-availability-admin.css'),
        [],
        $style_version
    );
}
add_action('admin_enqueue_scripts', 'the_logical_theme_enqueue_block_availability_assets');

/**
 * Marks the current request as a block availability save request.
 */
function the_logical_theme_mark_block_availability_save_request(): void
{
    $GLOBALS['the_logical_theme_block_availability_run_exports_on_shutdown'] = true;
}

/**
 * Detects explicit saves from options.php so exports also run when values do not change.
 */
function the_logical_theme_detect_block_availability_save_request(): void
{
    if (! is_admin()) {
        return;
    }

    if ('POST' !== strtoupper($_SERVER['REQUEST_METHOD'] ?? '')) {
        return;
    }

    $optionPage = isset($_POST['option_page']) ? sanitize_text_field(wp_unslash($_POST['option_page'])) : '';

    if ('the_logical_theme_block_availability' !== $optionPage) {
        return;
    }

    the_logical_theme_mark_block_availability_save_request();
}
add_action('admin_init', 'the_logical_theme_detect_block_availability_save_request', 5);

/**
 * Runs registry and whitelist exports after block availability settings are saved.
 */
function the_logical_theme_maybe_run_block_availability_exports(): void
{
    if (empty($GLOBALS['the_logical_theme_block_availability_run_exports_on_shutdown'])) {
        return;
    }

    try {
        $registryPath = the_logical_theme_export_block_registry_json();
        the_logical_theme_export_whitelisted_blocks_markdown($registryPath);
        delete_transient('the_logical_theme_block_availability_export_error');
    } catch (Throwable $throwable) {
        set_transient(
            'the_logical_theme_block_availability_export_error',
            $throwable->getMessage(),
            MINUTE_IN_SECONDS * 5
        );
    }
}
add_action('shutdown', 'the_logical_theme_maybe_run_block_availability_exports', 20);

add_action(
    'update_option_' . 'the_logical_theme_block_availability_settings',
    'the_logical_theme_mark_block_availability_save_request',
    10,
    0
);
add_action(
    'add_option_' . 'the_logical_theme_block_availability_settings',
    'the_logical_theme_mark_block_availability_save_request',
    10,
    0
);

/**
 * Renders the inline script that powers the live block filter.
 */
function the_logical_theme_render_block_availability_inline_script(): void
{
    ?>
    <script>
        if (typeof window.theLogicalThemeInitBlockAvailabilitySearch === 'function') {
            window.theLogicalThemeInitBlockAvailabilitySearch();
        }
    </script>
    <?php
}

/**
 * Returns the visible admin categories in their desired order.
 */
function the_logical_theme_get_block_availability_page_sections(): array
{
    $catalog = the_logical_theme_get_block_availability_admin_categories();
    $ordered_sections = [];

    foreach (['core', 'blog', 'woocommerce', 'third_party', 'custom'] as $category_key) {
        if (isset($catalog[$category_key])) {
            $ordered_sections[$category_key] = $catalog[$category_key];
        }
    }

    return $ordered_sections;
}

/**
 * Renders the controls for a single block category card.
 */
function the_logical_theme_render_block_availability_category_card(
    string $category_key,
    array $category,
    array $settings
): void {
    $allowed_blocks = $settings['allowed_blocks'][$category_key] ?? [];
    $allowed_lookup = array_fill_keys($allowed_blocks, true);
    $blocks = $category['blocks'];
    $is_toggleable = 'blog' === $category_key;
    $is_enabled = the_logical_theme_is_block_category_enabled($category_key, $settings);
    $active_count = count($allowed_blocks);
    $total_count = count($blocks);
    ?>
    <section class="tlt-block-availability-card" data-category="<?php echo esc_attr($category_key); ?>" data-role="block-card">
        <header class="tlt-block-availability-card__header">
            <div>
                <div class="tlt-block-availability-card__eyebrow"><?php echo esc_html(strtoupper($category_key)); ?></div>
                <h2><?php echo esc_html($category['label']); ?></h2>
                <p><?php echo esc_html($category['description']); ?></p>
            </div>
            <div class="tlt-block-availability-card__meta">
                <span class="tlt-block-availability-card__count" data-role="block-count">
                    <?php
                    printf(
                        /* translators: 1: active blocks count, 2: total blocks count */
                        esc_html__('%1$d / %2$d active', 'the-logical-theme'),
                        $active_count,
                        $total_count
                    );
                    ?>
                </span>
                <?php if ($is_toggleable) : ?>
                    <label class="tlt-block-availability-toggle">
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr(the_logical_theme_block_availability_option_name()); ?>[enabled_categories][blog]"
                            value="1"
                            <?php checked($is_enabled); ?>
                            data-role="category-toggle"
                        >
                        <span><?php esc_html_e('Enable category', 'the-logical-theme'); ?></span>
                    </label>
                <?php elseif ('core' === $category_key) : ?>
                    <span class="tlt-block-availability-badge is-fixed"><?php esc_html_e('Always on', 'the-logical-theme'); ?></span>
                <?php elseif ('woocommerce' === $category_key) : ?>
                    <span class="tlt-block-availability-badge"><?php esc_html_e('Plugin detected', 'the-logical-theme'); ?></span>
                <?php elseif ('third_party' === $category_key) : ?>
                    <span class="tlt-block-availability-badge"><?php esc_html_e('Plugin blocks', 'the-logical-theme'); ?></span>
                <?php else : ?>
                    <span class="tlt-block-availability-badge"><?php esc_html_e('Separate area', 'the-logical-theme'); ?></span>
                <?php endif; ?>
            </div>
        </header>

        <div class="tlt-block-availability-card__controls">
            <label class="tlt-block-availability-search">
                <span class="screen-reader-text">
                    <?php
                    printf(
                        /* translators: %s: category label */
                        esc_html__('Filter %s blocks', 'the-logical-theme'),
                        $category['label']
                    );
                    ?>
                </span>
                <input
                    type="search"
                    placeholder="<?php esc_attr_e('Filter blocks…', 'the-logical-theme'); ?>"
                    data-role="block-search"
                >
            </label>
        </div>

        <div class="tlt-block-availability-list" data-role="block-list" <?php disabled(! $is_enabled, true, true); ?>>
            <input
                type="hidden"
                name="<?php echo esc_attr(the_logical_theme_block_availability_option_name()); ?>[submitted_categories][]"
                value="<?php echo esc_attr($category_key); ?>"
            >
            <?php foreach ($blocks as $block_name) : ?>
                <label class="tlt-block-availability-item" data-role="block-item">
                    <input
                        type="checkbox"
                        name="<?php echo esc_attr(the_logical_theme_block_availability_option_name()); ?>[allowed_blocks][<?php echo esc_attr($category_key); ?>][]"
                        value="<?php echo esc_attr($block_name); ?>"
                        <?php checked(isset($allowed_lookup[$block_name])); ?>
                    >
                    <span class="tlt-block-availability-item__check" aria-hidden="true"></span>
                    <span class="tlt-block-availability-item__content">
                        <strong data-role="search-text"><?php echo esc_html($block_name); ?></strong>
                    </span>
                </label>
            <?php endforeach; ?>
            <p class="tlt-block-availability-list__empty" data-role="empty-state" hidden>
                <?php esc_html_e('No blocks match this filter.', 'the-logical-theme'); ?>
            </p>
        </div>
    </section>
    <?php
}

/**
 * Renders the block availability admin page.
 */
function the_logical_theme_render_block_availability_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $settings = the_logical_theme_get_block_availability_settings();
    $sections = the_logical_theme_get_block_availability_page_sections();

    if (isset($_GET['settings-updated']) && 'true' === $_GET['settings-updated']) {
        add_settings_error(
            'the_logical_theme_block_availability',
            'the-logical-theme-block-availability-saved',
            __('Block availability updated.', 'the-logical-theme'),
            'updated'
        );
    }

    $exportError = get_transient('the_logical_theme_block_availability_export_error');

    if (is_string($exportError) && $exportError !== '') {
        add_settings_error(
            'the_logical_theme_block_availability',
            'the-logical-theme-block-availability-export-error',
            sprintf(
                /* translators: %s: export error message */
                __('Block export failed: %s', 'the-logical-theme'),
                $exportError
            ),
            'error'
        );
        delete_transient('the_logical_theme_block_availability_export_error');
    }

    ?>
    <div class="wrap tlt-block-availability-page">
        <?php settings_errors('the_logical_theme_block_availability'); ?>
        <form method="post" action="options.php">
            <?php settings_fields('the_logical_theme_block_availability'); ?>
            <div class="tlt-block-availability-page__hero">
                <div>
                    <h1><?php esc_html_e('Block availability', 'the-logical-theme'); ?></h1>
                    <p>
                        <?php esc_html_e('Control which core, blog, WooCommerce, third-party, and custom blocks remain available in the editor without mixing categories.', 'the-logical-theme'); ?>
                    </p>
                    <?php submit_button(__('Save block availability', 'the-logical-theme'), 'primary', 'submit', false); ?>
                </div>
                <div class="tlt-block-availability-page__summary">
                    <span class="tlt-block-availability-badge is-fixed"><?php esc_html_e('Core always enabled', 'the-logical-theme'); ?></span>
                    <?php if (! the_logical_theme_has_woocommerce()) : ?>
                        <span class="tlt-block-availability-badge"><?php esc_html_e('WooCommerce not installed', 'the-logical-theme'); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="tlt-block-availability-grid">
                <?php foreach ($sections as $category_key => $category) : ?>
                    <?php the_logical_theme_render_block_availability_category_card($category_key, $category, $settings); ?>
                <?php endforeach; ?>
            </div>
        </form>
        <?php the_logical_theme_render_block_availability_inline_script(); ?>
    </div>
    <?php
}
