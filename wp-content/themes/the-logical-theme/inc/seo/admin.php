<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Adds the SEO settings page under Settings.
 */
function the_logical_theme_seo_settings_menu(): void
{
    add_options_page(
        __('SEO Settings', 'the-logical-theme'),
        __('SEO', 'the-logical-theme'),
        'manage_options',
        'the-logical-theme-seo',
        'the_logical_theme_seo_render_settings_page'
    );
}
add_action('admin_menu', 'the_logical_theme_seo_settings_menu');

/**
 * Returns the current robots.txt contents.
 */
function the_logical_theme_seo_get_robots_txt_contents(): string
{
    $robots_path = ABSPATH . 'robots.txt';

    if (! file_exists($robots_path)) {
        return '';
    }

    return (string) file_get_contents($robots_path);
}

/**
 * Persists robots.txt contents.
 */
function the_logical_theme_seo_update_robots_txt_contents(string $content): void
{
    the_logical_theme_seo_write_root_file(ABSPATH . 'robots.txt', $content);
}

/**
 * Handles SEO settings persistence.
 */
function the_logical_theme_seo_handle_settings_submission(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    if (
        ! isset($_POST['the_logical_theme_seo_settings_nonce'])
        || ! wp_verify_nonce(
            (string) $_POST['the_logical_theme_seo_settings_nonce'],
            'the_logical_theme_seo_save_settings'
        )
    ) {
        return;
    }

    the_logical_theme_seo_update_robots_txt_contents(
        sanitize_textarea_field(wp_unslash((string) ($_POST['robots_content'] ?? '')))
    );

    update_option('minimal_seo_noindex', isset($_POST['seo_noindex']) ? '1' : '0');
    update_option('minimal_seo_nofollow', isset($_POST['seo_nofollow']) ? '1' : '0');

    if (isset($_POST['regenerate_sitemap'])) {
        the_logical_theme_seo_generate_sitemap();
    }

    add_settings_error(
        'the-logical-theme-seo',
        'the-logical-theme-seo-saved',
        __('SEO settings saved.', 'the-logical-theme'),
        'updated'
    );
}

/**
 * Handles the SEO settings page output.
 */
function the_logical_theme_seo_render_settings_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        the_logical_theme_seo_handle_settings_submission();
    }

    settings_errors('the-logical-theme-seo');

    $robots_content = the_logical_theme_seo_get_robots_txt_contents();
    $noindex = get_option('minimal_seo_noindex', '0');
    $nofollow = get_option('minimal_seo_nofollow', '0');
    $sitemap_url = home_url('/sitemap.xml');
    $sitemap_exists = file_exists(ABSPATH . 'sitemap.xml');
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('SEO Settings', 'the-logical-theme'); ?></h1>

        <form method="post">
            <h2><?php esc_html_e('Robots.txt', 'the-logical-theme'); ?></h2>
            <textarea name="robots_content" style="width:100%;height:300px;"><?php echo esc_textarea($robots_content); ?></textarea>

            <h2><?php esc_html_e('Global Robots Meta', 'the-logical-theme'); ?></h2>
            <p>
                <label><input type="checkbox" name="seo_noindex" value="1" <?php checked($noindex, '1'); ?>> <?php esc_html_e('Set entire site to noindex', 'the-logical-theme'); ?></label>
            </p>
            <p>
                <label><input type="checkbox" name="seo_nofollow" value="1" <?php checked($nofollow, '1'); ?>> <?php esc_html_e('Set entire site to nofollow', 'the-logical-theme'); ?></label>
            </p>

            <h2><?php esc_html_e('XML Sitemap', 'the-logical-theme'); ?></h2>
            <?php if ($sitemap_exists) : ?>
                <p>
                    <?php esc_html_e('Sitemap available at:', 'the-logical-theme'); ?>
                    <a href="<?php echo esc_url($sitemap_url); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($sitemap_url); ?></a>
                </p>
            <?php else : ?>
                <p><strong><?php esc_html_e('Sitemap not generated yet.', 'the-logical-theme'); ?></strong></p>
            <?php endif; ?>
            <p>
                <label><input type="checkbox" name="regenerate_sitemap" value="1"> <?php esc_html_e('Regenerate sitemap now', 'the-logical-theme'); ?></label>
            </p>

            <?php wp_nonce_field('the_logical_theme_seo_save_settings', 'the_logical_theme_seo_settings_nonce'); ?>
            <p><input type="submit" class="button button-primary" value="<?php esc_attr_e('Save SEO Settings', 'the-logical-theme'); ?>"></p>
        </form>
    </div>
    <?php
}
