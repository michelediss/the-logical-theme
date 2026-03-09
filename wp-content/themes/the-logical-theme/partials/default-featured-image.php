<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns the option name used to store the theme default featured image.
 */
function the_logical_theme_default_featured_image_option_name(): string
{
    return 'the_logical_theme_default_featured_image_id';
}

/**
 * Returns the legacy option name from the previous plugin implementation.
 */
function the_logical_theme_legacy_default_featured_image_option_name(): string
{
    return 'pap_default_featured_image_id';
}

/**
 * Reads the configured default featured image, with legacy fallback.
 */
function the_logical_theme_get_default_featured_image_id(): int
{
    $option_name = the_logical_theme_default_featured_image_option_name();
    $attachment_id = absint(get_option($option_name, 0));

    if ($attachment_id > 0) {
        return $attachment_id;
    }

    return absint(get_option(the_logical_theme_legacy_default_featured_image_option_name(), 0));
}

/**
 * Registers the setting used by the theme to store a fallback featured image.
 */
function the_logical_theme_register_default_featured_image_setting(): void
{
    register_setting(
        'the_logical_theme_default_featured_image',
        the_logical_theme_default_featured_image_option_name(),
        [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 0,
        ]
    );
}
add_action('admin_init', 'the_logical_theme_register_default_featured_image_setting');

/**
 * Migrates the old plugin option into the theme option once.
 */
function the_logical_theme_maybe_migrate_default_featured_image_setting(): void
{
    $option_name = the_logical_theme_default_featured_image_option_name();
    $legacy_option_name = the_logical_theme_legacy_default_featured_image_option_name();

    if (false !== get_option($option_name, false)) {
        return;
    }

    $legacy_attachment_id = absint(get_option($legacy_option_name, 0));

    if ($legacy_attachment_id < 1) {
        return;
    }

    add_option($option_name, $legacy_attachment_id);
}
add_action('admin_init', 'the_logical_theme_maybe_migrate_default_featured_image_setting', 5);

/**
 * Adds the theme admin page under Appearance.
 */
function the_logical_theme_add_default_featured_image_page(): void
{
    add_theme_page(
        __('Default Featured Image', 'the-logical-theme'),
        __('Default Featured Image', 'the-logical-theme'),
        'manage_options',
        'the-logical-theme-default-featured-image',
        'the_logical_theme_render_default_featured_image_page'
    );
}
add_action('admin_menu', 'the_logical_theme_add_default_featured_image_page');

/**
 * Enqueues admin assets for the theme featured image screen only.
 */
function the_logical_theme_enqueue_default_featured_image_assets(string $hook_suffix): void
{
    if ('appearance_page_the-logical-theme-default-featured-image' !== $hook_suffix) {
        return;
    }

    $script_path = get_theme_file_path('assets/js/default-featured-image-admin.js');
    $style_path = get_theme_file_path('assets/css/admin/default-featured-image-admin.css');
    $script_version = file_exists($script_path) ? (string) filemtime($script_path) : TLT_VERSION;
    $style_version = file_exists($style_path) ? (string) filemtime($style_path) : TLT_VERSION;

    wp_enqueue_media();
    wp_enqueue_script(
        'the-logical-theme-default-featured-image-admin',
        get_theme_file_uri('assets/js/default-featured-image-admin.js'),
        ['jquery'],
        $script_version,
        true
    );
    wp_localize_script(
        'the-logical-theme-default-featured-image-admin',
        'theLogicalThemeDefaultFeaturedImage',
        [
            'frameTitle' => __('Select the default featured image', 'the-logical-theme'),
            'chooseButton' => __('Use this image', 'the-logical-theme'),
            'removeConfirm' => __('Remove the default featured image?', 'the-logical-theme'),
            'placeholderText' => __('No image selected', 'the-logical-theme'),
        ]
    );

    wp_enqueue_style(
        'the-logical-theme-default-featured-image-admin',
        get_theme_file_uri('assets/css/admin/default-featured-image-admin.css'),
        [],
        $style_version
    );
}
add_action('admin_enqueue_scripts', 'the_logical_theme_enqueue_default_featured_image_assets');

/**
 * Renders the theme settings page for the fallback featured image.
 */
function the_logical_theme_render_default_featured_image_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $attachment_id = the_logical_theme_get_default_featured_image_id();
    ?>
    <div class="wrap tlt-default-featured-image-settings">
        <h1><?php esc_html_e('Default featured image', 'the-logical-theme'); ?></h1>
        <p><?php esc_html_e('Used automatically for posts and pages that are saved without a featured image.', 'the-logical-theme'); ?></p>

        <form method="post" action="options.php">
            <?php settings_fields('the_logical_theme_default_featured_image'); ?>
            <div class="tlt-default-featured-image-field">
                <div class="tlt-default-featured-image-preview">
                    <?php
                    if ($attachment_id > 0) {
                        echo wp_get_attachment_image($attachment_id, 'medium');
                    } else {
                        echo '<div class="tlt-default-featured-image-placeholder">' .
                            esc_html__('No image selected', 'the-logical-theme') .
                            '</div>';
                    }
                    ?>
                </div>

                <div class="tlt-default-featured-image-panel">
                    <input
                        type="hidden"
                        id="tlt-default-featured-image-id"
                        name="<?php echo esc_attr(the_logical_theme_default_featured_image_option_name()); ?>"
                        value="<?php echo esc_attr((string) $attachment_id); ?>"
                    >

                    <div class="tlt-default-featured-image-actions">
                        <button type="button" class="button button-primary" id="tlt-default-featured-image-select">
                            <?php esc_html_e('Choose from media library', 'the-logical-theme'); ?>
                        </button>
                        <button type="button" class="button" id="tlt-default-featured-image-remove" <?php disabled($attachment_id < 1); ?>>
                            <?php esc_html_e('Remove', 'the-logical-theme'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <?php submit_button(__('Save image', 'the-logical-theme')); ?>
        </form>
    </div>
    <?php
}

/**
 * Applies the configured fallback featured image on supported post types.
 */
function the_logical_theme_assign_default_featured_image(int $post_id, WP_Post $post): void
{
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (wp_is_post_revision($post_id) || 'revision' === $post->post_type) {
        return;
    }

    if ('auto-draft' === $post->post_status) {
        return;
    }

    if (! post_type_supports($post->post_type, 'thumbnail')) {
        return;
    }

    if (get_post_thumbnail_id($post_id)) {
        return;
    }

    $default_image_id = the_logical_theme_get_default_featured_image_id();

    if ($default_image_id < 1) {
        return;
    }

    set_post_thumbnail($post_id, $default_image_id);
}
add_action('save_post', 'the_logical_theme_assign_default_featured_image', 20, 2);
