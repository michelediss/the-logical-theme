<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

final class The_Logical_Maintenance_Plugin
{
    public const VERSION = '0.1.0';
    public const OPTION_NAME = 'the_logical_maintenance_settings';
    public const TEXT_DOMAIN = 'the-logical-maintenance';
    public const QUERY_ARG = 'tl_maintenance_bypass';
    public const COOKIE_NAME = 'tl_maintenance_bypass';
    public const SETTINGS_SLUG = 'the-logical-maintenance';
    public const ACTION_SAVE = 'the_logical_maintenance_save_settings';
    public const ACTION_REGENERATE = 'the_logical_maintenance_regenerate_bypass';
    public const NOTICE_ARG = 'the_logical_maintenance_notice';
    public const NOTICE_SAVED = 'saved';
    public const NOTICE_REGENERATED = 'regenerated';
    public const NOTICE_MISSING_PAGE = 'missing-page';

    private static ?self $instance = null;

    private string $plugin_file;

    private bool $render_maintenance_template = false;

    private bool $maintenance_status_sent = false;

    private ?WP_Post $maintenance_page = null;

    private function __construct(string $plugin_file)
    {
        $this->plugin_file = $plugin_file;
        $this->register_hooks();
    }

    public static function instance(?string $plugin_file = null): self
    {
        if (self::$instance === null) {
            if ($plugin_file === null) {
                throw new RuntimeException('Plugin file is required on first boot.');
            }

            self::$instance = new self($plugin_file);
        }

        return self::$instance;
    }

    public static function activate(): void
    {
        $settings = get_option(self::OPTION_NAME, []);
        if (! is_array($settings)) {
            $settings = [];
        }

        $settings = array_merge(self::default_settings(), $settings);
        if (! is_string($settings['bypass_token']) || $settings['bypass_token'] === '') {
            $settings['bypass_token'] = self::generate_bypass_token();
        }

        $settings['enabled'] = ! empty($settings['enabled']);
        $settings['bypass_ttl_days'] = max(1, absint($settings['bypass_ttl_days']));

        update_option(self::OPTION_NAME, $settings, false);
    }

    private static function default_settings(): array
    {
        return [
            'enabled' => false,
            'bypass_token' => self::generate_bypass_token(),
            'bypass_ttl_days' => 14,
        ];
    }

    private static function generate_bypass_token(): string
    {
        return wp_generate_password(48, false, false);
    }

    private function register_hooks(): void
    {
        add_action('init', [$this, 'load_textdomain']);
        add_action('admin_menu', [$this, 'register_settings_page']);
        add_action('admin_post_' . self::ACTION_SAVE, [$this, 'handle_settings_save']);
        add_action('admin_post_' . self::ACTION_REGENERATE, [$this, 'handle_regenerate_bypass']);
        add_action('template_redirect', [$this, 'handle_bypass_link'], 0);
        add_action('template_redirect', [$this, 'maybe_prepare_maintenance_template'], 1);
        add_filter('template_include', [$this, 'filter_template_include'], 99);
        add_filter('redirect_canonical', [$this, 'disable_canonical_redirects_while_rendering'], 10, 2);
        add_filter('wp_robots', [$this, 'filter_wp_robots']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
    }

    public function load_textdomain(): void
    {
        load_plugin_textdomain(
            self::TEXT_DOMAIN,
            false,
            dirname(plugin_basename($this->plugin_file)) . '/languages'
        );
    }

    public function register_settings_page(): void
    {
        add_options_page(
            __('Maintenance', self::TEXT_DOMAIN),
            __('Maintenance', self::TEXT_DOMAIN),
            'manage_options',
            self::SETTINGS_SLUG,
            [$this, 'render_settings_page']
        );
    }

    public function handle_settings_save(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to manage maintenance settings.', self::TEXT_DOMAIN));
        }

        check_admin_referer(self::ACTION_SAVE);

        $raw_settings = wp_unslash($_POST['the_logical_maintenance'] ?? []);
        $saved_settings = $this->sanitize_settings(is_array($raw_settings) ? $raw_settings : []);

        update_option(self::OPTION_NAME, $saved_settings, false);

        wp_safe_redirect($this->get_settings_page_url(self::NOTICE_SAVED));
        exit;
    }

    public function handle_regenerate_bypass(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to manage maintenance settings.', self::TEXT_DOMAIN));
        }

        check_admin_referer(self::ACTION_REGENERATE);

        $settings = $this->get_settings();
        $settings['bypass_token'] = self::generate_bypass_token();

        update_option(self::OPTION_NAME, $settings, false);

        wp_safe_redirect($this->get_settings_page_url(self::NOTICE_REGENERATED));
        exit;
    }

    private function sanitize_settings(array $raw_settings): array
    {
        $defaults = self::default_settings();
        $current = $this->get_settings();

        return [
            'enabled' => ! empty($raw_settings['enabled']),
            'bypass_token' => is_string($current['bypass_token']) && $current['bypass_token'] !== ''
                ? $current['bypass_token']
                : $defaults['bypass_token'],
            'bypass_ttl_days' => max(1, absint($current['bypass_ttl_days'] ?? $defaults['bypass_ttl_days'])),
        ];
    }

    private function get_settings(): array
    {
        $saved = get_option(self::OPTION_NAME, []);
        $defaults = self::default_settings();

        if (! is_array($saved)) {
            update_option(self::OPTION_NAME, $defaults, false);

            return $defaults;
        }

        $settings = array_merge($defaults, $saved);
        $settings['enabled'] = ! empty($settings['enabled']);
        $settings['bypass_token'] = is_string($settings['bypass_token']) ? trim($settings['bypass_token']) : '';
        $settings['bypass_ttl_days'] = max(1, absint($settings['bypass_ttl_days']));

        if ($settings['bypass_token'] === '') {
            $settings['bypass_token'] = self::generate_bypass_token();
            update_option(self::OPTION_NAME, $settings, false);
        }

        return $settings;
    }

    public function handle_bypass_link(): void
    {
        if (! $this->is_frontend_request()) {
            return;
        }

        $token = isset($_GET[self::QUERY_ARG]) ? sanitize_text_field(wp_unslash((string) $_GET[self::QUERY_ARG])) : '';
        if ($token === '') {
            return;
        }

        $settings = $this->get_settings();
        if (! hash_equals($settings['bypass_token'], $token)) {
            return;
        }

        $expires = time() + ($settings['bypass_ttl_days'] * DAY_IN_SECONDS);
        $cookie_value = $this->get_bypass_cookie_value($settings['bypass_token']);
        $cookie_args = [
            'expires' => $expires,
            'path' => COOKIEPATH ?: '/',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        setcookie(self::COOKIE_NAME, $cookie_value, $cookie_args);
        $_COOKIE[self::COOKIE_NAME] = $cookie_value;

        nocache_headers();
        wp_safe_redirect(remove_query_arg(self::QUERY_ARG));
        exit;
    }

    public function maybe_prepare_maintenance_template(): void
    {
        if (! $this->is_frontend_request()) {
            return;
        }

        if ($this->is_maintenance_page_request()) {
            $this->render_maintenance_template = true;
        }

        if (! $this->should_block_request()) {
            return;
        }

        $this->render_maintenance_template = true;
        $this->send_maintenance_headers();
    }

    public function filter_template_include(string $template): string
    {
        if (! $this->render_maintenance_template) {
            return $template;
        }

        return $this->plugin_path('templates/maintenance-template.php');
    }

    public function disable_canonical_redirects_while_rendering($redirect_url, string $requested_url)
    {
        unset($requested_url);

        if ($this->render_maintenance_template) {
            return false;
        }

        return $redirect_url;
    }

    public function filter_wp_robots(array $robots): array
    {
        if (! $this->maintenance_status_sent) {
            return $robots;
        }

        $robots['noindex'] = true;

        return $robots;
    }

    public function enqueue_frontend_assets(): void
    {
        if (! $this->render_maintenance_template) {
            return;
        }

        wp_enqueue_style(
            'the-logical-maintenance',
            plugins_url('assets/css/maintenance.css', $this->plugin_file),
            [],
            self::VERSION
        );
    }

    public function render_settings_page(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $settings = $this->get_settings();
        $maintenance_page = $this->get_maintenance_page();
        $maintenance_link = $maintenance_page instanceof WP_Post ? get_permalink($maintenance_page) : '';
        $bypass_link = $this->get_bypass_url();
        $notice = isset($_GET[self::NOTICE_ARG]) ? sanitize_key((string) $_GET[self::NOTICE_ARG]) : '';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Maintenance', self::TEXT_DOMAIN); ?></h1>
            <p><?php esc_html_e('Enable a site-wide maintenance response powered by the Gutenberg content of the fixed Maintenance page.', self::TEXT_DOMAIN); ?></p>

            <?php $this->render_admin_notice($notice, $maintenance_page); ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <input type="hidden" name="action" value="<?php echo esc_attr(self::ACTION_SAVE); ?>">
                <?php wp_nonce_field(self::ACTION_SAVE); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><?php esc_html_e('Status', self::TEXT_DOMAIN); ?></th>
                            <td>
                                <label for="the-logical-maintenance-enabled">
                                    <input
                                        id="the-logical-maintenance-enabled"
                                        type="checkbox"
                                        name="the_logical_maintenance[enabled]"
                                        value="1"
                                        <?php checked($settings['enabled']); ?>
                                    >
                                    <?php esc_html_e('Enable maintenance mode', self::TEXT_DOMAIN); ?>
                                </label>
                                <p class="description">
                                    <?php esc_html_e('When enabled, public frontend HTML requests are replaced with the Maintenance page and return HTTP 503.', self::TEXT_DOMAIN); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Source page', self::TEXT_DOMAIN); ?></th>
                            <td>
                                <p><code>maintenance</code></p>
                                <?php if ($maintenance_link !== '') : ?>
                                    <p><a href="<?php echo esc_url($maintenance_link); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($maintenance_link); ?></a></p>
                                <?php else : ?>
                                    <p class="description"><?php esc_html_e('The required page was not found or is not published.', self::TEXT_DOMAIN); ?></p>
                                <?php endif; ?>
                                <p class="description"><?php esc_html_e('The plugin always uses the page with slug Maintenance / maintenance. No page selector is exposed here.', self::TEXT_DOMAIN); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Bypass link', self::TEXT_DOMAIN); ?></th>
                            <td>
                                <input
                                    id="the-logical-maintenance-bypass-link"
                                    type="text"
                                    class="regular-text code"
                                    readonly
                                    value="<?php echo esc_attr($bypass_link); ?>"
                                >
                                <p>
                                    <button type="button" class="button" data-copy-target="the-logical-maintenance-bypass-link"><?php esc_html_e('Copy link', self::TEXT_DOMAIN); ?></button>
                                </p>
                                <p class="description">
                                    <?php
                                    printf(
                                        /* translators: %d: number of days. */
                                        esc_html__('Visitors using this link receive a bypass cookie valid for %d days.', self::TEXT_DOMAIN),
                                        (int) $settings['bypass_ttl_days']
                                    );
                                    ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php esc_html_e('Automatic access', self::TEXT_DOMAIN); ?></th>
                            <td>
                                <p><?php esc_html_e('Administrators bypass maintenance automatically when logged in. External users can access the site only through the bypass link.', self::TEXT_DOMAIN); ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(__('Save Maintenance Settings', self::TEXT_DOMAIN)); ?>
            </form>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top: 1rem;">
                <input type="hidden" name="action" value="<?php echo esc_attr(self::ACTION_REGENERATE); ?>">
                <?php wp_nonce_field(self::ACTION_REGENERATE); ?>
                <?php submit_button(__('Regenerate bypass token', self::TEXT_DOMAIN), 'secondary', 'submit', false); ?>
            </form>
        </div>

        <script>
            document.addEventListener('click', function (event) {
                var button = event.target.closest('[data-copy-target]');
                if (!button) {
                    return;
                }

                var input = document.getElementById(button.getAttribute('data-copy-target'));
                if (!input) {
                    return;
                }

                input.focus();
                input.select();

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(input.value);
                    return;
                }

                document.execCommand('copy');
            });
        </script>
        <?php
    }

    private function render_admin_notice(string $notice, ?WP_Post $maintenance_page): void
    {
        if ($notice === self::NOTICE_SAVED) {
            $this->render_notice_message(__('Maintenance settings saved.', self::TEXT_DOMAIN), 'updated');
        }

        if ($notice === self::NOTICE_REGENERATED) {
            $this->render_notice_message(__('Bypass token regenerated.', self::TEXT_DOMAIN), 'updated');
        }

        if (! $maintenance_page instanceof WP_Post) {
            $this->render_notice_message(
                __('The required page with slug "maintenance" is missing or not published. Maintenance mode will not block the site until that page exists.', self::TEXT_DOMAIN),
                'error'
            );
        }
    }

    private function render_notice_message(string $message, string $type): void
    {
        printf(
            '<div class="%1$s"><p>%2$s</p></div>',
            esc_attr('notice notice-' . $type),
            esc_html($message)
        );
    }

    private function is_frontend_request(): bool
    {
        if (is_admin() || wp_doing_ajax() || wp_doing_cron()) {
            return false;
        }

        if ((defined('REST_REQUEST') && REST_REQUEST) || (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)) {
            return false;
        }

        if (defined('WP_CLI') && WP_CLI) {
            return false;
        }

        return true;
    }

    private function should_block_request(): bool
    {
        $settings = $this->get_settings();
        if (! $settings['enabled']) {
            return false;
        }

        if (! $this->get_maintenance_page() instanceof WP_Post) {
            return false;
        }

        if ($this->is_user_allowed_without_maintenance()) {
            return false;
        }

        if ($this->has_valid_bypass_cookie()) {
            return false;
        }

        if (is_feed() || is_robots() || is_favicon() || is_trackback() || is_preview()) {
            return false;
        }

        return true;
    }

    private function is_user_allowed_without_maintenance(): bool
    {
        return is_user_logged_in() && current_user_can('manage_options');
    }

    private function has_valid_bypass_cookie(): bool
    {
        $cookie = isset($_COOKIE[self::COOKIE_NAME]) ? sanitize_text_field(wp_unslash((string) $_COOKIE[self::COOKIE_NAME])) : '';
        if ($cookie === '') {
            return false;
        }

        $settings = $this->get_settings();

        return hash_equals($this->get_bypass_cookie_value($settings['bypass_token']), $cookie);
    }

    private function get_bypass_cookie_value(string $token): string
    {
        return hash_hmac('sha256', $token, wp_salt('auth'));
    }

    private function send_maintenance_headers(): void
    {
        if ($this->maintenance_status_sent) {
            return;
        }

        status_header(503);
        nocache_headers();
        header('X-Robots-Tag: noindex', true);

        $this->maintenance_status_sent = true;
    }

    private function is_maintenance_page_request(): bool
    {
        $maintenance_page = $this->get_maintenance_page();
        if (! $maintenance_page instanceof WP_Post) {
            return false;
        }

        return is_page($maintenance_page->ID);
    }

    public function get_maintenance_page(): ?WP_Post
    {
        if ($this->maintenance_page instanceof WP_Post) {
            return $this->maintenance_page;
        }

        $page = get_page_by_path('maintenance', OBJECT, 'page');
        if (! $page instanceof WP_Post || $page->post_status !== 'publish') {
            return null;
        }

        $this->maintenance_page = $page;

        return $this->maintenance_page;
    }

    public function get_maintenance_page_content(): string
    {
        $maintenance_page = $this->get_maintenance_page();
        if (! $maintenance_page instanceof WP_Post) {
            return '';
        }

        global $post;

        $previous_post = $post instanceof WP_Post ? $post : null;
        $post = $maintenance_page;
        setup_postdata($maintenance_page);

        $content = apply_filters('the_content', (string) $maintenance_page->post_content);

        if ($previous_post instanceof WP_Post) {
            $post = $previous_post;
            setup_postdata($previous_post);
        } else {
            wp_reset_postdata();
        }

        return (string) $content;
    }

    public function is_rendering_maintenance_template(): bool
    {
        return $this->render_maintenance_template;
    }

    public function is_maintenance_response(): bool
    {
        return $this->maintenance_status_sent;
    }

    public function get_bypass_url(): string
    {
        $settings = $this->get_settings();

        return add_query_arg(self::QUERY_ARG, rawurlencode($settings['bypass_token']), home_url('/'));
    }

    public function plugin_path(string $path = ''): string
    {
        $base_path = dirname($this->plugin_file);

        if ($path === '') {
            return $base_path;
        }

        return $base_path . '/' . ltrim($path, '/');
    }

    private function get_settings_page_url(string $notice = ''): string
    {
        $url = admin_url('options-general.php?page=' . self::SETTINGS_SLUG);

        if ($notice === '') {
            return $url;
        }

        return add_query_arg(self::NOTICE_ARG, $notice, $url);
    }
}
