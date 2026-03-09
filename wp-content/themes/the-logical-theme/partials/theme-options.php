<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns the option name used by the Theme Options screen.
 */
function the_logical_theme_theme_options_option_name(): string
{
    return 'the_logical_theme_options';
}

/**
 * Returns the supported image MIME types for upload controls.
 *
 * @return array<string, array{label: string, mime: string, extensions: string}>
 */
function the_logical_theme_get_upload_image_limit_choices(): array
{
    return [
        'image/jpeg' => [
            'label' => __('JPG', 'the-logical-theme'),
            'mime' => 'image/jpeg',
            'extensions' => 'jpg|jpeg|jpe',
        ],
        'image/png' => [
            'label' => __('PNG', 'the-logical-theme'),
            'mime' => 'image/png',
            'extensions' => 'png',
        ],
        'image/webp' => [
            'label' => __('WebP', 'the-logical-theme'),
            'mime' => 'image/webp',
            'extensions' => 'webp',
        ],
    ];
}

/**
 * Returns the default Theme Options values.
 *
 * @return array{
 *     disable_jquery: bool,
 *     disable_comments: bool,
 *     upload_image_limits: array{
 *         enabled: bool,
 *         enabled_mimes: string[],
 *         max_bytes_by_mime: array<string, int>
 *     }
 * }
 */
function the_logical_theme_get_default_theme_options(): array
{
    $choices = the_logical_theme_get_upload_image_limit_choices();
    $enabled_mimes = array_keys($choices);
    $max_bytes_by_mime = [];

    foreach ($enabled_mimes as $mime) {
        $max_bytes_by_mime[$mime] = 5 * MB_IN_BYTES;
    }

    return [
        'disable_jquery' => false,
        'disable_comments' => false,
        'upload_image_limits' => [
            'enabled' => false,
            'enabled_mimes' => $enabled_mimes,
            'max_bytes_by_mime' => $max_bytes_by_mime,
        ],
    ];
}

/**
 * Returns the current Theme Options with defaults merged in.
 *
 * @return array{
 *     disable_jquery: bool,
 *     disable_comments: bool,
 *     upload_image_limits: array{
 *         enabled: bool,
 *         enabled_mimes: string[],
 *         max_bytes_by_mime: array<string, int>
 *     }
 * }
 */
function the_logical_theme_get_theme_options(): array
{
    $defaults = the_logical_theme_get_default_theme_options();
    $saved = get_option(the_logical_theme_theme_options_option_name(), []);

    if (! is_array($saved)) {
        return $defaults;
    }

    $saved_upload_limits = is_array($saved['upload_image_limits'] ?? null)
        ? $saved['upload_image_limits']
        : [];

    return [
        'disable_jquery' => ! empty($saved['disable_jquery']),
        'disable_comments' => ! empty($saved['disable_comments']),
        'upload_image_limits' => [
            'enabled' => ! empty($saved_upload_limits['enabled']),
            'enabled_mimes' => array_values(array_filter(
                is_array($saved_upload_limits['enabled_mimes'] ?? null) ? $saved_upload_limits['enabled_mimes'] : [],
                'is_string'
            )),
            'max_bytes_by_mime' => array_map(
                'absint',
                is_array($saved_upload_limits['max_bytes_by_mime'] ?? null)
                    ? $saved_upload_limits['max_bytes_by_mime']
                    : $defaults['upload_image_limits']['max_bytes_by_mime']
            ),
        ],
    ];
}

/**
 * Registers the Theme Options setting.
 */
function the_logical_theme_register_theme_options_setting(): void
{
    register_setting(
        'the_logical_theme_theme_options',
        the_logical_theme_theme_options_option_name(),
        [
            'type' => 'array',
            'sanitize_callback' => 'the_logical_theme_sanitize_theme_options',
            'default' => the_logical_theme_get_default_theme_options(),
        ]
    );
}
add_action('admin_init', 'the_logical_theme_register_theme_options_setting');

/**
 * Sanitizes the Theme Options payload.
 *
 * @param mixed $raw_value
 * @return array{
 *     disable_jquery: bool,
 *     disable_comments: bool,
 *     upload_image_limits: array{
 *         enabled: bool,
 *         enabled_mimes: string[],
 *         max_bytes_by_mime: array<string, int>
 *     }
 * }
 */
function the_logical_theme_sanitize_theme_options($raw_value): array
{
    $defaults = the_logical_theme_get_default_theme_options();
    $choices = the_logical_theme_get_upload_image_limit_choices();
    $value = is_array($raw_value) ? $raw_value : [];
    $upload_limits = is_array($value['upload_image_limits'] ?? null) ? $value['upload_image_limits'] : [];
    $selected_mimes = array_values(array_intersect(
        array_keys($choices),
        array_map(
            'strval',
            is_array($upload_limits['enabled_mimes'] ?? null) ? $upload_limits['enabled_mimes'] : []
        )
    ));

    $sanitized = [
        'disable_jquery' => ! empty($value['disable_jquery']),
        'disable_comments' => ! empty($value['disable_comments']),
        'upload_image_limits' => [
            'enabled' => ! empty($upload_limits['enabled']),
            'enabled_mimes' => $selected_mimes,
            'max_bytes_by_mime' => $defaults['upload_image_limits']['max_bytes_by_mime'],
        ],
    ];

    foreach ($choices as $mime => $choice) {
        $raw_kb = $upload_limits['max_kb_by_mime'][$mime] ?? null;
        $raw_kb = is_scalar($raw_kb) ? (string) $raw_kb : '';
        $max_kb = max(0, (int) round((float) str_replace(',', '.', $raw_kb)));

        if ($max_kb > 0) {
            $sanitized['upload_image_limits']['max_bytes_by_mime'][$mime] = $max_kb * KB_IN_BYTES;
            continue;
        }

        if ($sanitized['upload_image_limits']['enabled'] && in_array($mime, $selected_mimes, true)) {
            add_settings_error(
                'the_logical_theme_theme_options',
                'the-logical-theme-theme-options-invalid-size-' . md5($mime),
                sprintf(
                    /* translators: %s: MIME label. */
                    __('The maximum upload size for %s must be greater than zero.', 'the-logical-theme'),
                    $choice['label']
                ),
                'error'
            );
        }
    }

    if ($sanitized['upload_image_limits']['enabled'] && [] === $selected_mimes) {
        add_settings_error(
            'the_logical_theme_theme_options',
            'the-logical-theme-theme-options-no-mime',
            __('Select at least one image format when upload image limits are enabled.', 'the-logical-theme'),
            'error'
        );
        $sanitized['upload_image_limits']['enabled'] = false;
    }

    return $sanitized;
}

/**
 * Adds the Theme Options screen under Settings.
 */
function the_logical_theme_add_theme_options_page(): void
{
    add_options_page(
        __('Theme Options', 'the-logical-theme'),
        __('Theme Options', 'the-logical-theme'),
        'manage_options',
        'the-logical-theme-options',
        'the_logical_theme_render_theme_options_page'
    );
}
add_action('admin_menu', 'the_logical_theme_add_theme_options_page');

/**
 * Renders one image MIME row for the upload limits section.
 *
 * @param array{label: string, mime: string, extensions: string} $choice
 * @param array{
 *     enabled: bool,
 *     enabled_mimes: string[],
 *     max_bytes_by_mime: array<string, int>
 * } $settings
 */
function the_logical_theme_render_upload_image_limit_row(array $choice, array $settings): void
{
    $option_name = the_logical_theme_theme_options_option_name();
    $mime = $choice['mime'];
    $is_enabled = in_array($mime, $settings['enabled_mimes'], true);
    $max_bytes = absint($settings['max_bytes_by_mime'][$mime] ?? 0);
    $max_kb = $max_bytes > 0 ? (int) ceil($max_bytes / KB_IN_BYTES) : 0;
    ?>
    <tr>
        <th scope="row"><?php echo esc_html($choice['label']); ?></th>
        <td>
            <fieldset>
                <label>
                    <input
                        type="checkbox"
                        name="<?php echo esc_attr($option_name); ?>[upload_image_limits][enabled_mimes][]"
                        value="<?php echo esc_attr($mime); ?>"
                        <?php checked($is_enabled); ?>
                    >
                    <?php
                    printf(
                        /* translators: %s: MIME label. */
                        esc_html__('Allow %s uploads', 'the-logical-theme'),
                        esc_html($choice['label'])
                    );
                    ?>
                </label>
                <p class="description">
                    <?php
                    printf(
                        /* translators: 1: MIME type, 2: extension list. */
                        esc_html__('MIME: %1$s. Extensions: %2$s.', 'the-logical-theme'),
                        esc_html($mime),
                        esc_html($choice['extensions'])
                    );
                    ?>
                </p>
                <label>
                    <span class="screen-reader-text">
                        <?php
                        printf(
                            /* translators: %s: MIME label. */
                            esc_html__('Maximum upload size for %s in KB', 'the-logical-theme'),
                            esc_html($choice['label'])
                        );
                        ?>
                    </span>
                    <input
                        type="number"
                        class="small-text"
                        min="1"
                        step="1"
                        name="<?php echo esc_attr($option_name); ?>[upload_image_limits][max_kb_by_mime][<?php echo esc_attr($mime); ?>]"
                        value="<?php echo esc_attr((string) $max_kb); ?>"
                    >
                    <span><?php esc_html_e('KB max size', 'the-logical-theme'); ?></span>
                </label>
            </fieldset>
        </td>
    </tr>
    <?php
}

/**
 * Renders the Theme Options admin page.
 */
function the_logical_theme_render_theme_options_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $settings = the_logical_theme_get_theme_options();
    $choices = the_logical_theme_get_upload_image_limit_choices();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Theme Options', 'the-logical-theme'); ?></h1>
        <p>
            <?php esc_html_e('Control global theme behaviors for frontend dependencies, comments, and image upload rules.', 'the-logical-theme'); ?>
        </p>

        <?php settings_errors('the_logical_theme_theme_options'); ?>

        <form method="post" action="options.php">
            <?php settings_fields('the_logical_theme_theme_options'); ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('Disable jQuery', 'the-logical-theme'); ?></th>
                        <td>
                            <label>
                                <input
                                    type="checkbox"
                                    name="<?php echo esc_attr(the_logical_theme_theme_options_option_name()); ?>[disable_jquery]"
                                    value="1"
                                    <?php checked($settings['disable_jquery']); ?>
                                >
                                <?php esc_html_e('Disable WordPress jQuery on the public frontend.', 'the-logical-theme'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('This leaves wp-admin and other administrative screens untouched.', 'the-logical-theme'); ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Disable comments', 'the-logical-theme'); ?></th>
                        <td>
                            <label>
                                <input
                                    type="checkbox"
                                    name="<?php echo esc_attr(the_logical_theme_theme_options_option_name()); ?>[disable_comments]"
                                    value="1"
                                    <?php checked($settings['disable_comments']); ?>
                                >
                                <?php esc_html_e('Disable comments and pingbacks site-wide.', 'the-logical-theme'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('This closes comments on the frontend, blocks new submissions, and hides comment management entrypoints in admin.', 'the-logical-theme'); ?>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2><?php esc_html_e('Upload image limits', 'the-logical-theme'); ?></h2>
            <p><?php esc_html_e('Enable custom limits for JPG, PNG, and WebP uploads. Size values are stored per format.', 'the-logical-theme'); ?></p>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable upload image limits', 'the-logical-theme'); ?></th>
                        <td>
                            <label>
                                <input
                                    type="checkbox"
                                    name="<?php echo esc_attr(the_logical_theme_theme_options_option_name()); ?>[upload_image_limits][enabled]"
                                    value="1"
                                    <?php checked($settings['upload_image_limits']['enabled']); ?>
                                >
                                <?php esc_html_e('Restrict allowed image formats and enforce per-format file size limits.', 'the-logical-theme'); ?>
                            </label>
                            <p class="description">
                                <?php esc_html_e('When disabled, WordPress falls back to its normal upload rules.', 'the-logical-theme'); ?>
                            </p>
                        </td>
                    </tr>
                    <?php foreach ($choices as $choice) : ?>
                        <?php the_logical_theme_render_upload_image_limit_row($choice, $settings['upload_image_limits']); ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php submit_button(__('Save Theme Options', 'the-logical-theme')); ?>
        </form>
    </div>
    <?php
}

/**
 * Disables jQuery on the public frontend when requested.
 */
function the_logical_theme_maybe_disable_frontend_jquery(): void
{
    $settings = the_logical_theme_get_theme_options();

    if (! $settings['disable_jquery'] || is_admin()) {
        return;
    }

    wp_dequeue_script('jquery');
    wp_deregister_script('jquery');
    wp_dequeue_script('jquery-core');
    wp_deregister_script('jquery-core');
    wp_dequeue_script('jquery-migrate');
    wp_deregister_script('jquery-migrate');
}
add_action('wp_enqueue_scripts', 'the_logical_theme_maybe_disable_frontend_jquery', 100);

/**
 * Returns whether comments are disabled in Theme Options.
 */
function the_logical_theme_are_comments_disabled(): bool
{
    $settings = the_logical_theme_get_theme_options();

    return $settings['disable_comments'];
}

/**
 * Removes comment support from registered post types when comments are disabled.
 */
function the_logical_theme_maybe_remove_comment_support(): void
{
    if (! the_logical_theme_are_comments_disabled()) {
        return;
    }

    foreach (get_post_types_by_support('comments') as $post_type) {
        remove_post_type_support($post_type, 'comments');
    }

    foreach (get_post_types_by_support('trackbacks') as $post_type) {
        remove_post_type_support($post_type, 'trackbacks');
    }
}
add_action('init', 'the_logical_theme_maybe_remove_comment_support', 100);

/**
 * Forces comments and pings closed when comments are disabled.
 */
function the_logical_theme_filter_comment_status(bool $open): bool
{
    if (the_logical_theme_are_comments_disabled()) {
        return false;
    }

    return $open;
}
add_filter('comments_open', 'the_logical_theme_filter_comment_status', 20);
add_filter('pings_open', 'the_logical_theme_filter_comment_status', 20);

/**
 * Empties any rendered comments when comments are disabled.
 *
 * @param array<int, WP_Comment> $comments
 * @return array<int, WP_Comment>
 */
function the_logical_theme_filter_comments_array(array $comments): array
{
    if (the_logical_theme_are_comments_disabled()) {
        return [];
    }

    return $comments;
}
add_filter('comments_array', 'the_logical_theme_filter_comments_array', 20);

/**
 * Sets default comment and ping statuses to closed.
 */
function the_logical_theme_filter_default_discussion_status(string $status): string
{
    if (the_logical_theme_are_comments_disabled()) {
        return 'closed';
    }

    return $status;
}
add_filter('pre_option_default_comment_status', 'the_logical_theme_filter_default_discussion_status');
add_filter('pre_option_default_ping_status', 'the_logical_theme_filter_default_discussion_status');

/**
 * Blocks new comment submissions when comments are disabled.
 *
 * @param array<string, mixed> $comment_data
 * @return array<string, mixed>
 */
function the_logical_theme_block_comment_submission(array $comment_data): array
{
    if (! the_logical_theme_are_comments_disabled()) {
        return $comment_data;
    }

    wp_die(
        esc_html__('Comments are disabled by Theme Options.', 'the-logical-theme'),
        esc_html__('Comments disabled', 'the-logical-theme'),
        ['response' => 403]
    );
}
add_filter('preprocess_comment', 'the_logical_theme_block_comment_submission');

/**
 * Removes comments entrypoints from admin navigation.
 */
function the_logical_theme_hide_comment_admin_menus(): void
{
    if (! the_logical_theme_are_comments_disabled()) {
        return;
    }

    remove_menu_page('edit-comments.php');
    remove_submenu_page('options-general.php', 'options-discussion.php');
}
add_action('admin_menu', 'the_logical_theme_hide_comment_admin_menus', 100);

/**
 * Removes the comments node from the admin bar.
 */
function the_logical_theme_hide_comment_admin_bar_node(WP_Admin_Bar $admin_bar): void
{
    if (! the_logical_theme_are_comments_disabled()) {
        return;
    }

    $admin_bar->remove_node('comments');
}
add_action('admin_bar_menu', 'the_logical_theme_hide_comment_admin_bar_node', 100);

/**
 * Redirects direct comment admin pages when comments are disabled.
 */
function the_logical_theme_redirect_comment_admin_pages(): void
{
    if (! the_logical_theme_are_comments_disabled()) {
        return;
    }

    global $pagenow;

    if (in_array($pagenow, ['edit-comments.php', 'comment.php', 'options-discussion.php'], true)) {
        wp_safe_redirect(admin_url());
        exit;
    }

    remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
}
add_action('admin_init', 'the_logical_theme_redirect_comment_admin_pages');

/**
 * Redirects comment feeds when comments are disabled.
 */
function the_logical_theme_redirect_comment_feeds(): void
{
    if (! the_logical_theme_are_comments_disabled() || ! is_comment_feed()) {
        return;
    }

    wp_safe_redirect(home_url('/'), 302);
    exit;
}
add_action('template_redirect', 'the_logical_theme_redirect_comment_feeds');

/**
 * Returns whether custom upload image limits are enabled.
 */
function the_logical_theme_are_upload_image_limits_enabled(): bool
{
    $settings = the_logical_theme_get_theme_options();

    return $settings['upload_image_limits']['enabled'];
}

/**
 * Returns the configured upload image limits settings.
 *
 * @return array{
 *     enabled: bool,
 *     enabled_mimes: string[],
 *     max_bytes_by_mime: array<string, int>
 * }
 */
function the_logical_theme_get_upload_image_limits_settings(): array
{
    $settings = the_logical_theme_get_theme_options();

    return $settings['upload_image_limits'];
}

/**
 * Returns the helper URL suggested when an image exceeds the max upload size.
 */
function the_logical_theme_get_image_compression_service_url(): string
{
    return 'https://compressjpeg.com/';
}

/**
 * Restricts supported image MIME types when custom upload limits are enabled.
 *
 * @param array<string, string> $mimes
 * @return array<string, string>
 */
function the_logical_theme_filter_upload_mimes(array $mimes): array
{
    if (! the_logical_theme_are_upload_image_limits_enabled()) {
        return $mimes;
    }

    $settings = the_logical_theme_get_upload_image_limits_settings();
    $choices = the_logical_theme_get_upload_image_limit_choices();
    $selected = array_fill_keys($settings['enabled_mimes'], true);

    foreach ($choices as $mime => $choice) {
        unset($mimes[$choice['extensions']]);

        if (isset($selected[$mime])) {
            $mimes[$choice['extensions']] = $mime;
        }
    }

    return $mimes;
}
add_filter('upload_mimes', 'the_logical_theme_filter_upload_mimes');

/**
 * Enforces per-MIME upload sizes for supported image formats.
 *
 * @param array<string, mixed> $file
 * @return array<string, mixed>
 */
function the_logical_theme_filter_upload_image_size(array $file): array
{
    if (! the_logical_theme_are_upload_image_limits_enabled() || ! empty($file['error'])) {
        return $file;
    }

    $choices = the_logical_theme_get_upload_image_limit_choices();
    $settings = the_logical_theme_get_upload_image_limits_settings();
    $selected = array_fill_keys($settings['enabled_mimes'], true);
    $file_name = is_scalar($file['name'] ?? null) ? (string) $file['name'] : '';
    $tmp_name = is_scalar($file['tmp_name'] ?? null) ? (string) $file['tmp_name'] : '';
    $detected_type = wp_check_filetype_and_ext($tmp_name, $file_name);
    $mime = is_string($detected_type['type'] ?? null) && '' !== $detected_type['type']
        ? $detected_type['type']
        : (is_scalar($file['type'] ?? null) ? (string) $file['type'] : '');

    if (! isset($choices[$mime])) {
        return $file;
    }

    if (! isset($selected[$mime])) {
        $file['error'] = sprintf(
            /* translators: %s: MIME label. */
            __('%s uploads are disabled by Theme Options.', 'the-logical-theme'),
            $choices[$mime]['label']
        );
        return $file;
    }

    $max_bytes = absint($settings['max_bytes_by_mime'][$mime] ?? 0);

    if ($max_bytes < 1) {
        return $file;
    }

    $file_size = absint($file['size'] ?? 0);

    if ($file_size <= $max_bytes) {
        return $file;
    }

    $file['error'] = sprintf(
        /* translators: 1: MIME label, 2: maximum size, 3: helper service URL. */
        __('%1$s files must not exceed %2$s. Use this service to reduce the image size: %3$s', 'the-logical-theme'),
        $choices[$mime]['label'],
        size_format($max_bytes),
        the_logical_theme_get_image_compression_service_url()
    );

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'the_logical_theme_filter_upload_image_size');
