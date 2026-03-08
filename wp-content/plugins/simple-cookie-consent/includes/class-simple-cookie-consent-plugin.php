<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

final class Simple_Cookie_Consent_Plugin
{
    public const VERSION = '0.2.0';
    public const REGISTRY_OPTION = 'simple_cookie_consent_registry';
    public const CONSENT_COOKIE = 'simple_cookie_consent';
    public const REST_NAMESPACE = 'simple-cookie-consent/v1';
    public const TEXT_DOMAIN = 'simple-cookie-consent';

    private static ?self $instance = null;

    /** @var array<int, array<string, mixed>> */
    private static array $fallback_cookie_store = [];

    private string $plugin_file;

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

    public static function fallback_add_cookie_info(
        string $name,
        string $service,
        string $category,
        string $duration,
        string $description,
        bool $first_party = false,
        bool $personal = false,
        bool $non_eu = false
    ): void {
        self::$fallback_cookie_store[] = [
            'cookie_name' => $name,
            'service' => $service,
            'category' => $category,
            'duration' => $duration,
            'description' => $description,
            'first_party' => $first_party,
            'personal' => $personal,
            'non_eu' => $non_eu,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function fallback_get_cookie_info(): array
    {
        return self::$fallback_cookie_store;
    }

    private function register_hooks(): void
    {
        add_action('init', [$this, 'load_textdomain']);
        add_action('init', [$this, 'maybe_migrate_theme_registry'], 5);
        add_action('init', [$this, 'maybe_upgrade_registry_entries'], 6);
        add_action('init', [$this, 'register_cookies_info'], 20);
        add_action('init', [$this, 'register_blocks']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        add_action('rest_api_init', [$this, 'register_rest_routes']);
        add_action('admin_menu', [$this, 'register_admin_page']);
        add_action('admin_post_simple_cookie_consent_add_cookie', [$this, 'handle_admin_add_cookie']);
        add_action('admin_post_simple_cookie_consent_update_cookie', [$this, 'handle_admin_update_cookie']);
        add_action('admin_post_simple_cookie_consent_delete_cookie', [$this, 'handle_admin_delete_cookie']);

        add_filter('wp_get_consent_type', [$this, 'filter_consent_type'], 10, 1);

        $plugin = plugin_basename($this->plugin_file);
        add_filter("wp_consent_api_registered_{$plugin}", '__return_true');
    }

    public function load_textdomain(): void
    {
        load_plugin_textdomain(
            self::TEXT_DOMAIN,
            false,
            dirname(plugin_basename($this->plugin_file)) . '/languages'
        );
    }

    public function maybe_migrate_theme_registry(): void
    {
        if (get_option(self::REGISTRY_OPTION, null) !== null) {
            return;
        }

        $legacy_path = trailingslashit(get_stylesheet_directory()) . 'assets/json/lcc-cookies.json';
        if (! is_readable($legacy_path)) {
            $legacy_path = trailingslashit(get_template_directory()) . 'assets/json/lcc-cookies.json';
        }

        $entries = [];
        if (is_readable($legacy_path)) {
            $raw = file_get_contents($legacy_path);
            $decoded = is_string($raw) ? json_decode($raw, true) : null;

            if (is_array($decoded)) {
                foreach ($decoded as $entry) {
                    if (! is_array($entry)) {
                        continue;
                    }

                    $sanitized = $this->sanitize_cookie_entry($entry);
                    if ($sanitized !== null) {
                        $entries[] = $sanitized;
                    }
                }
            }
        }

        if ($entries === []) {
            $entries = $this->get_default_cookie_entries();
        }

        update_option(self::REGISTRY_OPTION, $entries, false);
    }

    public function maybe_upgrade_registry_entries(): void
    {
        $current_entries = get_option(self::REGISTRY_OPTION, []);
        if (! is_array($current_entries)) {
            return;
        }

        $merged_entries = $this->merge_cookie_entries($current_entries, $this->get_default_cookie_entries());

        if (count($merged_entries) !== count($current_entries)) {
            update_option(self::REGISTRY_OPTION, $merged_entries, false);
        }
    }

    public function register_blocks(): void
    {
        wp_register_style(
            'simple-cookie-consent',
            plugins_url('assets/css/simple-cookie-consent.css', $this->plugin_file),
            [],
            self::VERSION
        );

        wp_register_script(
            'simple-cookie-consent-editor',
            plugins_url('assets/js/editor.js', $this->plugin_file),
            ['wp-block-editor', 'wp-blocks', 'wp-components', 'wp-element', 'wp-i18n', 'wp-server-side-render'],
            self::VERSION,
            true
        );

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations(
                'simple-cookie-consent-editor',
                self::TEXT_DOMAIN,
                $this->plugin_path('languages')
            );
        }

        register_block_type(
            $this->plugin_path('blocks/banner'),
            [
                'editor_script' => 'simple-cookie-consent-editor',
                'style' => 'simple-cookie-consent',
                'render_callback' => [$this, 'render_banner_block'],
            ]
        );

        register_block_type(
            $this->plugin_path('blocks/cookie-table'),
            [
                'editor_script' => 'simple-cookie-consent-editor',
                'style' => 'simple-cookie-consent',
                'render_callback' => [$this, 'render_cookie_table_block'],
            ]
        );
    }

    public function enqueue_editor_assets(): void
    {
        wp_enqueue_style('simple-cookie-consent');
    }

    public function enqueue_frontend_assets(): void
    {
        wp_enqueue_style('simple-cookie-consent');

        wp_register_script(
            'simple-cookie-consent-frontend',
            plugins_url('assets/js/frontend.js', $this->plugin_file),
            ['wp-i18n'],
            self::VERSION,
            true
        );

        if (function_exists('wp_set_script_translations')) {
            wp_set_script_translations(
                'simple-cookie-consent-frontend',
                self::TEXT_DOMAIN,
                $this->plugin_path('languages')
            );
        }

        wp_localize_script(
            'simple-cookie-consent-frontend',
            'SimpleCookieConsent',
            [
                'restUrl' => esc_url_raw(rest_url(self::REST_NAMESPACE . '/consent')),
                'restNonce' => wp_create_nonce('wp_rest'),
                'initialConsent' => $this->get_localized_consent_state(),
                'cookieDetails' => $this->get_cookie_details(),
                'cookiePolicyUrl' => esc_url(home_url('/cookie-policy/')),
            ]
        );

        wp_enqueue_script('simple-cookie-consent-frontend');
    }

    public function register_rest_routes(): void
    {
        register_rest_route(
            self::REST_NAMESPACE,
            '/consent',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'permission_callback' => '__return_true',
                'callback' => [$this, 'rest_set_consent'],
                'args' => [
                    'preferences' => ['type' => 'boolean'],
                    'statisticsAnonymous' => ['type' => 'boolean'],
                    'statistics' => ['type' => 'boolean'],
                    'marketing' => ['type' => 'boolean'],
                ],
            ]
        );
    }

    public function rest_set_consent(WP_REST_Request $request): WP_REST_Response
    {
        if (is_user_logged_in()) {
            $nonce = $request->get_header('X-WP-Nonce');
            if (! wp_verify_nonce((string) $nonce, 'wp_rest')) {
                return new WP_REST_Response(['code' => 'invalid_nonce'], 403);
            }
        }

        $preferences = (bool) $request->get_param('preferences');
        $statistics_anonymous = (bool) $request->get_param('statisticsAnonymous');
        $statistics = (bool) $request->get_param('statistics');
        $marketing = (bool) $request->get_param('marketing');

        if ($statistics) {
            $statistics_anonymous = true;
        }

        $map = [
            'functional' => 'allow',
            'preferences' => $preferences ? 'allow' : 'deny',
            'statistics-anonymous' => $statistics_anonymous ? 'allow' : 'deny',
            'statistics' => $statistics ? 'allow' : 'deny',
            'marketing' => $marketing ? 'allow' : 'deny',
        ];

        if (function_exists('wp_set_consent')) {
            foreach ($map as $category => $value) {
                wp_set_consent($category, $value);
            }
        }

        self::persist_cookie_consent($map);

        return new WP_REST_Response(['consent' => $map], 200);
    }

    public function filter_consent_type($type): string
    {
        return 'optin';
    }

    public function register_admin_page(): void
    {
        add_options_page(
            __('Simple Cookie Consent', 'simple-cookie-consent'),
            __('Cookie Registry', 'simple-cookie-consent'),
            'manage_options',
            'simple-cookie-consent',
            [$this, 'render_admin_page']
        );
    }

    public function handle_admin_add_cookie(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions.', 'simple-cookie-consent'));
        }

        check_admin_referer('simple_cookie_consent_add_cookie');

        $entry = $this->sanitize_cookie_entry($this->get_cookie_entry_from_request());
        if ($entry === null) {
            wp_safe_redirect($this->get_admin_page_url(['simple_cookie_consent_status' => 'error']));
            exit;
        }

        $entries = $this->get_registry_entries();
        $entries[] = $entry;
        $this->save_registry_entries($entries);

        wp_safe_redirect($this->get_admin_page_url(['simple_cookie_consent_status' => 'added']));
        exit;
    }

    public function handle_admin_update_cookie(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions.', 'simple-cookie-consent'));
        }

        check_admin_referer('simple_cookie_consent_update_cookie');

        $index = isset($_POST['cookie_index']) ? (int) $_POST['cookie_index'] : -1;
        $entries = $this->get_registry_entries();

        if (! isset($entries[$index])) {
            wp_safe_redirect($this->get_admin_page_url(['simple_cookie_consent_status' => 'error']));
            exit;
        }

        $entry = $this->sanitize_cookie_entry($this->get_cookie_entry_from_request());
        if ($entry === null) {
            wp_safe_redirect(
                $this->get_admin_page_url(
                    [
                        'simple_cookie_consent_status' => 'error',
                        'simple_cookie_consent_edit' => $index,
                    ]
                )
            );
            exit;
        }

        $entries[$index] = $entry;
        $this->save_registry_entries($entries);

        wp_safe_redirect($this->get_admin_page_url(['simple_cookie_consent_status' => 'updated']));
        exit;
    }

    public function handle_admin_delete_cookie(): void
    {
        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('Insufficient permissions.', 'simple-cookie-consent'));
        }

        check_admin_referer('simple_cookie_consent_delete_cookie');

        $index = isset($_POST['cookie_index']) ? (int) $_POST['cookie_index'] : -1;
        $entries = $this->get_registry_entries();

        if (! isset($entries[$index])) {
            wp_safe_redirect($this->get_admin_page_url(['simple_cookie_consent_status' => 'error']));
            exit;
        }

        unset($entries[$index]);
        $this->save_registry_entries(array_values($entries));

        wp_safe_redirect($this->get_admin_page_url(['simple_cookie_consent_status' => 'deleted']));
        exit;
    }

    public function render_admin_page(): void
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $entries = $this->get_registry_entries();
        $status = isset($_GET['simple_cookie_consent_status']) ? sanitize_key((string) $_GET['simple_cookie_consent_status']) : '';
        $edit_index = isset($_GET['simple_cookie_consent_edit']) ? (int) $_GET['simple_cookie_consent_edit'] : -1;
        $edit_entry = $entries[$edit_index] ?? null;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Cookie Registry', 'simple-cookie-consent'); ?></h1>
            <p><?php esc_html_e('Cookie definitions are stored in WordPress options and exposed to Gutenberg blocks and the consent API integration.', 'simple-cookie-consent'); ?></p>

            <?php if ($status === 'added') : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Cookie added.', 'simple-cookie-consent'); ?></p></div>
            <?php elseif ($status === 'updated') : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Cookie updated.', 'simple-cookie-consent'); ?></p></div>
            <?php elseif ($status === 'deleted') : ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Cookie deleted.', 'simple-cookie-consent'); ?></p></div>
            <?php elseif ($status === 'error') : ?>
                <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Operation failed. Check required fields and try again.', 'simple-cookie-consent'); ?></p></div>
            <?php endif; ?>

            <h2><?php esc_html_e('Registered Cookies', 'simple-cookie-consent'); ?></h2>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Name', 'simple-cookie-consent'); ?></th>
                        <th><?php esc_html_e('Service', 'simple-cookie-consent'); ?></th>
                        <th><?php esc_html_e('Category', 'simple-cookie-consent'); ?></th>
                        <th><?php esc_html_e('Duration', 'simple-cookie-consent'); ?></th>
                        <th><?php esc_html_e('Description', 'simple-cookie-consent'); ?></th>
                        <th><?php esc_html_e('First party', 'simple-cookie-consent'); ?></th>
                        <th><?php esc_html_e('Actions', 'simple-cookie-consent'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($entries === []) : ?>
                    <tr><td colspan="7"><?php esc_html_e('No cookies registered.', 'simple-cookie-consent'); ?></td></tr>
                <?php else : ?>
                    <?php foreach ($entries as $index => $entry) : ?>
                        <tr>
                            <td><?php echo esc_html((string) ($entry['name'] ?? '')); ?></td>
                            <td><?php echo esc_html((string) ($entry['service'] ?? '')); ?></td>
                            <td><code><?php echo esc_html((string) ($entry['category'] ?? '')); ?></code></td>
                            <td><?php echo esc_html((string) ($entry['duration'] ?? '')); ?></td>
                            <td><?php echo esc_html((string) ($entry['description'] ?? '')); ?></td>
                            <td><?php echo ! empty($entry['first_party']) ? 'true' : 'false'; ?></td>
                            <td>
                                <a class="button button-secondary" href="<?php echo esc_url($this->get_admin_page_url(['simple_cookie_consent_edit' => $index])); ?>">
                                    <?php esc_html_e('Edit', 'simple-cookie-consent'); ?>
                                </a>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;">
                                    <?php wp_nonce_field('simple_cookie_consent_delete_cookie'); ?>
                                    <input type="hidden" name="action" value="simple_cookie_consent_delete_cookie">
                                    <input type="hidden" name="cookie_index" value="<?php echo esc_attr((string) $index); ?>">
                                    <button type="submit" class="button button-link-delete"><?php esc_html_e('Delete', 'simple-cookie-consent'); ?></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>

            <?php if (is_array($edit_entry)) : ?>
                <h2 style="margin-top:30px;"><?php esc_html_e('Edit Cookie', 'simple-cookie-consent'); ?></h2>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('simple_cookie_consent_update_cookie'); ?>
                    <input type="hidden" name="action" value="simple_cookie_consent_update_cookie">
                    <input type="hidden" name="cookie_index" value="<?php echo esc_attr((string) $edit_index); ?>">
                    <?php $this->render_cookie_form_fields($edit_entry, 'simple-cookie-consent-edit'); ?>
                    <?php submit_button(__('Save changes', 'simple-cookie-consent')); ?>
                    <a href="<?php echo esc_url($this->get_admin_page_url()); ?>" class="button"><?php esc_html_e('Cancel', 'simple-cookie-consent'); ?></a>
                </form>
            <?php endif; ?>

            <h2 style="margin-top:30px;"><?php esc_html_e('Add Cookie', 'simple-cookie-consent'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('simple_cookie_consent_add_cookie'); ?>
                <input type="hidden" name="action" value="simple_cookie_consent_add_cookie">
                <?php $this->render_cookie_form_fields([], 'simple-cookie-consent-new'); ?>
                <?php submit_button(__('Add cookie', 'simple-cookie-consent')); ?>
            </form>
        </div>
        <?php
    }

    /**
     * @param array<string, mixed> $values
     */
    private function render_cookie_form_fields(array $values, string $id_prefix): void
    {
        ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="<?php echo esc_attr($id_prefix); ?>-name"><?php esc_html_e('Cookie name', 'simple-cookie-consent'); ?></label></th>
                <td><input id="<?php echo esc_attr($id_prefix); ?>-name" name="name" type="text" class="regular-text" required value="<?php echo esc_attr((string) ($values['name'] ?? '')); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="<?php echo esc_attr($id_prefix); ?>-service"><?php esc_html_e('Service', 'simple-cookie-consent'); ?></label></th>
                <td><input id="<?php echo esc_attr($id_prefix); ?>-service" name="service" type="text" class="regular-text" required value="<?php echo esc_attr((string) ($values['service'] ?? '')); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="<?php echo esc_attr($id_prefix); ?>-category"><?php esc_html_e('Category', 'simple-cookie-consent'); ?></label></th>
                <td>
                    <select id="<?php echo esc_attr($id_prefix); ?>-category" name="category">
                        <?php foreach ($this->get_category_options() as $category) : ?>
                            <option value="<?php echo esc_attr($category); ?>" <?php selected((string) ($values['category'] ?? ''), $category); ?>><?php echo esc_html($category); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="<?php echo esc_attr($id_prefix); ?>-duration"><?php esc_html_e('Duration', 'simple-cookie-consent'); ?></label></th>
                <td><input id="<?php echo esc_attr($id_prefix); ?>-duration" name="duration" type="text" class="regular-text" value="<?php echo esc_attr((string) ($values['duration'] ?? '')); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="<?php echo esc_attr($id_prefix); ?>-description"><?php esc_html_e('Description', 'simple-cookie-consent'); ?></label></th>
                <td><textarea id="<?php echo esc_attr($id_prefix); ?>-description" name="description" rows="4" class="large-text"><?php echo esc_textarea((string) ($values['description'] ?? '')); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('First party', 'simple-cookie-consent'); ?></th>
                <td><label><input type="checkbox" name="first_party" value="1" <?php checked(! array_key_exists('first_party', $values) || ! empty($values['first_party'])); ?>> <?php esc_html_e('First-party cookie', 'simple-cookie-consent'); ?></label></td>
            </tr>
        </table>
        <?php
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function render_banner_block(array $attributes = [], string $content = '', ?WP_Block $block = null): string
    {
        $settings_groups = [
            ['key' => 'functional', 'label' => __('Necessary', 'simple-cookie-consent'), 'supports_toggle' => false],
            ['key' => 'preferences', 'label' => __('Preferences', 'simple-cookie-consent'), 'supports_toggle' => true],
            ['key' => 'statistics-anonymous', 'label' => __('Anonymous analytics', 'simple-cookie-consent'), 'supports_toggle' => true],
            ['key' => 'statistics', 'label' => __('Analytics', 'simple-cookie-consent'), 'supports_toggle' => true],
            ['key' => 'marketing', 'label' => __('Marketing', 'simple-cookie-consent'), 'supports_toggle' => true],
        ];

        ob_start();
        ?>
        <div id="simple-cookie-consent-banner" class="wp-block-simple-cookie-consent-banner scc-banner scc-hidden" role="dialog" aria-modal="true" aria-labelledby="simple-cookie-consent-title" aria-describedby="simple-cookie-consent-description" tabindex="-1">
            <div class="scc-banner__card">
                <div class="scc-banner__header">
                    <div class="scc-banner__eyebrow"><?php esc_html_e('Privacy', 'simple-cookie-consent'); ?></div>
                    <h2 id="simple-cookie-consent-title" class="scc-banner__title"><?php esc_html_e('Cookie preferences', 'simple-cookie-consent'); ?></h2>
                </div>

                <div class="scc-banner__body">
                    <div id="simple-cookie-consent-description" class="scc-banner__description">
                        <p><?php esc_html_e('We use necessary cookies and, with your consent, optional cookies for preferences, analytics, and embedded third-party media.', 'simple-cookie-consent'); ?></p>
                        <p class="scc-banner__policy">
                            <a href="<?php echo esc_url(home_url('/cookie-policy/')); ?>">
                                <?php esc_html_e('Read the cookie policy', 'simple-cookie-consent'); ?>
                            </a>
                        </p>
                    </div>

                    <div id="simple-cookie-consent-settings" class="scc-banner__settings scc-hidden">
                        <div class="scc-banner__settings-intro">
                            <p><?php esc_html_e('Necessary cookies are always active. You can opt in to the other categories below.', 'simple-cookie-consent'); ?></p>
                        </div>

                        <?php foreach ($settings_groups as $group) : ?>
                            <div class="scc-banner__setting" data-simple-cookie-consent-cookie-row="<?php echo esc_attr($group['key']); ?>">
                                <button type="button" class="scc-banner__toggle-button" data-simple-cookie-consent-cookie-toggle="<?php echo esc_attr($group['key']); ?>" aria-expanded="false" aria-label="<?php echo esc_attr(sprintf(__('Show cookies for %s', 'simple-cookie-consent'), $group['label'])); ?>">
                                    <span aria-hidden="true">+</span>
                                </button>
                                <div class="scc-banner__setting-main">
                                    <div class="scc-banner__setting-copy">
                                        <strong><?php echo esc_html($group['label']); ?></strong>
                                        <span class="scc-banner__setting-caption">
                                            <?php echo esc_html($group['supports_toggle'] ? __('Optional category', 'simple-cookie-consent') : __('Always enabled', 'simple-cookie-consent')); ?>
                                        </span>
                                    </div>
                                    <?php if ($group['supports_toggle']) : ?>
                                        <input type="checkbox" data-simple-cookie-consent-toggle="<?php echo esc_attr($group['key']); ?>" aria-label="<?php echo esc_attr(sprintf(__('Enable %s cookies', 'simple-cookie-consent'), $group['label'])); ?>">
                                    <?php else : ?>
                                        <input type="checkbox" checked disabled aria-disabled="true" aria-label="<?php echo esc_attr(sprintf(__('%s cookies are required', 'simple-cookie-consent'), $group['label'])); ?>">
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="scc-banner__cookie-panel scc-hidden" data-simple-cookie-consent-cookie-panel="<?php echo esc_attr($group['key']); ?>">
                                <div class="scc-banner__cookie-list" data-simple-cookie-consent-cookie-list="<?php echo esc_attr($group['key']); ?>"></div>
                            </div>
                        <?php endforeach; ?>

                        <div class="scc-banner__settings-footer">
                            <div class="scc-banner__actions scc-banner__actions--settings">
                                <button type="button" class="scc-button scc-button--primary" data-simple-cookie-consent-action="save"><?php esc_html_e('Save preferences', 'simple-cookie-consent'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="scc-banner__footer">
                    <div class="scc-banner__actions">
                    <button type="button" class="scc-button scc-button--primary" data-simple-cookie-consent-action="acceptAll"><?php esc_html_e('Accept all', 'simple-cookie-consent'); ?></button>
                    <button type="button" class="scc-button scc-button--secondary" data-simple-cookie-consent-action="rejectAll"><?php esc_html_e('Reject all', 'simple-cookie-consent'); ?></button>
                    <button type="button" class="scc-button scc-button--ghost" data-simple-cookie-consent-action="openSettings"><?php esc_html_e('Settings', 'simple-cookie-consent'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function render_cookie_table_block(array $attributes = [], string $content = '', ?WP_Block $block = null): string
    {
        $layout = isset($attributes['layout']) ? sanitize_key((string) $attributes['layout']) : 'table';
        $category_filter = isset($attributes['category']) ? sanitize_key((string) $attributes['category']) : '';
        $show_category = ! isset($attributes['showCategory']) || (bool) $attributes['showCategory'];
        $show_duration = ! isset($attributes['showDuration']) || (bool) $attributes['showDuration'];

        $cookie_details = $this->get_cookie_details();
        $rows = [];

        foreach ($cookie_details as $category => $items) {
            if ($category_filter !== '' && $category_filter !== $category) {
                continue;
            }

            foreach ($items as $item) {
                $item['category'] = $category;
                $rows[] = $item;
            }
        }

        if ($rows === []) {
            return '<p>' . esc_html__('No cookies registered.', 'simple-cookie-consent') . '</p>';
        }

        ob_start();
        ?>
        <div class="wp-block-simple-cookie-consent-cookie-table scc-cookie-table scc-cookie-table--<?php echo esc_attr($layout); ?>">
            <?php if ($layout === 'cards') : ?>
                <div class="scc-cookie-table__cards">
                    <?php foreach ($rows as $row) : ?>
                        <article class="scc-cookie-card">
                            <h3 class="scc-cookie-card__title"><?php echo esc_html((string) ($row['name'] ?? '')); ?></h3>
                            <dl class="scc-cookie-card__meta">
                                <div><dt><?php esc_html_e('Service', 'simple-cookie-consent'); ?></dt><dd><?php echo esc_html((string) ($row['service'] ?? '')); ?></dd></div>
                                <?php if ($show_category) : ?>
                                    <div><dt><?php esc_html_e('Category', 'simple-cookie-consent'); ?></dt><dd><?php echo esc_html((string) ($row['category'] ?? '')); ?></dd></div>
                                <?php endif; ?>
                                <?php if ($show_duration) : ?>
                                    <div><dt><?php esc_html_e('Duration', 'simple-cookie-consent'); ?></dt><dd><?php echo esc_html((string) ($row['duration'] ?? '')); ?></dd></div>
                                <?php endif; ?>
                            </dl>
                            <?php if (! empty($row['description'])) : ?>
                                <p class="scc-cookie-card__description"><?php echo esc_html((string) $row['description']); ?></p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="scc-cookie-table__wrap">
                    <table>
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Cookie', 'simple-cookie-consent'); ?></th>
                                <th><?php esc_html_e('Service', 'simple-cookie-consent'); ?></th>
                                <?php if ($show_category) : ?>
                                    <th><?php esc_html_e('Category', 'simple-cookie-consent'); ?></th>
                                <?php endif; ?>
                                <?php if ($show_duration) : ?>
                                    <th><?php esc_html_e('Duration', 'simple-cookie-consent'); ?></th>
                                <?php endif; ?>
                                <th><?php esc_html_e('Description', 'simple-cookie-consent'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row) : ?>
                                <tr>
                                    <td><?php echo esc_html((string) ($row['name'] ?? '')); ?></td>
                                    <td><?php echo esc_html((string) ($row['service'] ?? '')); ?></td>
                                    <?php if ($show_category) : ?>
                                        <td><?php echo esc_html((string) ($row['category'] ?? '')); ?></td>
                                    <?php endif; ?>
                                    <?php if ($show_duration) : ?>
                                        <td><?php echo esc_html((string) ($row['duration'] ?? '')); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo esc_html((string) ($row['description'] ?? '')); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    public function register_cookies_info(): void
    {
        if (! function_exists('wp_add_cookie_info')) {
            return;
        }

        foreach ($this->get_registry_entries() as $entry) {
            $name = (string) ($entry['name'] ?? '');
            $service = (string) ($entry['service'] ?? '');
            $category = self::normalize_cookie_category((string) ($entry['category'] ?? ''));

            if ($name === '' || $service === '' || $category === '') {
                continue;
            }

            $duration = (string) ($entry['duration'] ?? '');
            $description = (string) ($entry['description'] ?? '');
            $first_party = array_key_exists('first_party', $entry) && $entry['first_party'] !== null
                ? (bool) $entry['first_party']
                : ! $this->is_third_party_service($service);

            wp_add_cookie_info($name, $service, $category, $duration, $description, $first_party, false, false);
        }

        do_action('simple_cookie_consent_register_cookies');
    }

    /**
     * @return array<string, array<int, array<string, string>>>
     */
    public function get_cookie_details(): array
    {
        $groups = [
            'functional' => [],
            'preferences' => [],
            'statistics-anonymous' => [],
            'statistics' => [],
            'marketing' => [],
        ];

        $items = function_exists('wp_get_cookie_info') ? wp_get_cookie_info() : self::$fallback_cookie_store;
        if (! is_array($items)) {
            return $groups;
        }

        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }

            $category = self::normalize_cookie_category((string) ($row['category'] ?? ($row['purpose'] ?? '')));
            if ($category === '') {
                continue;
            }

            $groups[$category][] = [
                'name' => (string) ($row['cookie_name'] ?? ($row['name'] ?? '')),
                'service' => (string) ($row['service'] ?? ($row['cookie_service'] ?? '')),
                'duration' => (string) ($row['duration'] ?? ($row['cookie_duration'] ?? '')),
                'description' => (string) ($row['description'] ?? ($row['cookie_description'] ?? '')),
            ];
        }

        return $groups;
    }

    /**
     * @return array<string, mixed>
     */
    private function get_cookie_entry_from_request(): array
    {
        return [
            'name' => isset($_POST['name']) ? wp_unslash($_POST['name']) : '',
            'service' => isset($_POST['service']) ? wp_unslash($_POST['service']) : '',
            'category' => isset($_POST['category']) ? wp_unslash($_POST['category']) : '',
            'duration' => isset($_POST['duration']) ? wp_unslash($_POST['duration']) : '',
            'description' => isset($_POST['description']) ? wp_unslash($_POST['description']) : '',
            'first_party' => ! empty($_POST['first_party']),
        ];
    }

    private function get_admin_page_url(array $args = []): string
    {
        $base = admin_url('options-general.php?page=simple-cookie-consent');
        return $args === [] ? $base : add_query_arg($args, $base);
    }

    /**
     * @return array<int, string>
     */
    private function get_category_options(): array
    {
        return ['functional', 'preferences', 'statistics-anonymous', 'statistics', 'marketing'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function get_default_cookie_entries(): array
    {
        return [
            [
                'name' => self::CONSENT_COOKIE,
                'service' => 'Simple Cookie Consent',
                'category' => 'functional',
                'duration' => '1 year',
                'description' => 'Stores the consent decision for each cookie category.',
                'first_party' => true,
            ],
            [
                'name' => 'wordpress_[hash]',
                'service' => 'WordPress',
                'category' => 'functional',
                'duration' => 'Session',
                'description' => 'Keeps the authenticated session active in wp-admin.',
                'first_party' => true,
            ],
            [
                'name' => 'wordpress_sec_[hash]',
                'service' => 'WordPress',
                'category' => 'functional',
                'duration' => 'Session',
                'description' => 'Keeps the authenticated session active while plugins are in use.',
                'first_party' => true,
            ],
            [
                'name' => 'wordpress_logged_in_[hash]',
                'service' => 'WordPress',
                'category' => 'functional',
                'duration' => 'Session',
                'description' => 'Stores the logged-in user after authentication.',
                'first_party' => true,
            ],
            [
                'name' => 'wordpress_test_cookie',
                'service' => 'WordPress',
                'category' => 'functional',
                'duration' => 'Session',
                'description' => 'Checks whether the browser accepts cookies.',
                'first_party' => true,
            ],
            [
                'name' => 'wp_lang',
                'service' => 'WordPress',
                'category' => 'functional',
                'duration' => 'Session',
                'description' => 'Stores the current interface language.',
                'first_party' => true,
            ],
            [
                'name' => 'wp-settings-1',
                'service' => 'WordPress',
                'category' => 'functional',
                'duration' => '1 year',
                'description' => 'Stores dashboard preferences for the current user.',
                'first_party' => true,
            ],
            [
                'name' => 'wp-settings-time-1',
                'service' => 'WordPress',
                'category' => 'functional',
                'duration' => '1 year',
                'description' => 'Stores the update timestamp for user settings.',
                'first_party' => true,
            ],
            [
                'name' => 'youtube_*',
                'service' => 'YouTube',
                'category' => 'marketing',
                'duration' => 'Up to 2 years',
                'description' => 'Used by YouTube for embedded video playback and engagement tracking.',
                'first_party' => false,
            ],
            [
                'name' => 'spotify_*',
                'service' => 'Spotify',
                'category' => 'marketing',
                'duration' => 'Session / 1 year',
                'description' => 'Allows Spotify to load embedded players and collect listening statistics.',
                'first_party' => false,
            ],
            [
                'name' => 'x.com_*',
                'service' => 'X (Twitter)',
                'category' => 'marketing',
                'duration' => 'Up to 2 years',
                'description' => 'Tracks interactions with embedded tweets and personalizes advertising content.',
                'first_party' => false,
            ],
            [
                'name' => 'facebook_*',
                'service' => 'Facebook',
                'category' => 'marketing',
                'duration' => 'Up to 2 years',
                'description' => 'Used by Facebook to measure and personalize embedded content.',
                'first_party' => false,
            ],
            [
                'name' => 'instagram_*',
                'service' => 'Instagram',
                'category' => 'marketing',
                'duration' => 'Session / 1 year',
                'description' => 'Handles embedded posts and stories and collects basic usage metrics.',
                'first_party' => false,
            ],
            [
                'name' => 'tiktok_*',
                'service' => 'TikTok',
                'category' => 'marketing',
                'duration' => 'Up to 13 months',
                'description' => 'Used by TikTok to render embedded videos and analyze engagement.',
                'first_party' => false,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function get_registry_entries(): array
    {
        $entries = get_option(self::REGISTRY_OPTION, []);
        if (! is_array($entries) || $entries === []) {
            return $this->get_default_cookie_entries();
        }

        $clean_entries = [];
        foreach ($entries as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $sanitized = $this->sanitize_cookie_entry($entry);
            if ($sanitized !== null) {
                $clean_entries[] = $sanitized;
            }
        }

        return $clean_entries === [] ? $this->get_default_cookie_entries() : $clean_entries;
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     */
    private function save_registry_entries(array $entries): void
    {
        update_option(self::REGISTRY_OPTION, array_values($entries), false);
    }

    /**
     * @param array<int, array<string, mixed>> $base_entries
     * @param array<int, array<string, mixed>> $incoming_entries
     * @return array<int, array<string, mixed>>
     */
    private function merge_cookie_entries(array $base_entries, array $incoming_entries): array
    {
        $merged = [];
        $seen = [];

        foreach (array_merge($base_entries, $incoming_entries) as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $sanitized = $this->sanitize_cookie_entry($entry);
            if ($sanitized === null) {
                continue;
            }

            $key = strtolower(
                implode('|', [
                    (string) $sanitized['name'],
                    (string) $sanitized['service'],
                    (string) $sanitized['category'],
                ])
            );

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $merged[] = $sanitized;
        }

        return $merged;
    }

    /**
     * @param array<string, mixed> $entry
     * @return array<string, mixed>|null
     */
    private function sanitize_cookie_entry(array $entry): ?array
    {
        $name = sanitize_text_field((string) ($entry['name'] ?? ''));
        $service = sanitize_text_field((string) ($entry['service'] ?? ''));
        $category = self::normalize_cookie_category((string) ($entry['category'] ?? ''));
        $duration = sanitize_text_field((string) ($entry['duration'] ?? ''));
        $description = sanitize_textarea_field((string) ($entry['description'] ?? ''));

        if ($name === '' || $service === '' || $category === '') {
            return null;
        }

        $first_party = null;
        if (array_key_exists('first_party', $entry)) {
            $first_party = (bool) $entry['first_party'];
        }

        return [
            'name' => $name,
            'service' => $service,
            'category' => $category,
            'duration' => $duration,
            'description' => $description,
            'first_party' => $first_party,
        ];
    }

    private static function normalize_cookie_category(string $value): string
    {
        $value = sanitize_key($value);
        if ($value === 'statistics_anonymous') {
            $value = 'statistics-anonymous';
        }

        $allowed = ['functional', 'preferences', 'statistics-anonymous', 'statistics', 'marketing'];
        return in_array($value, $allowed, true) ? $value : '';
    }

    private function is_third_party_service(string $service): bool
    {
        $service = strtolower(trim($service));
        $known = ['youtube', 'spotify', 'x (twitter)', 'twitter', 'facebook', 'instagram', 'tiktok'];

        return in_array($service, $known, true);
    }

    /**
     * @return array<string, mixed>
     */
    private function get_localized_consent_state(): array
    {
        $categories = [];
        foreach (['preferences', 'statistics-anonymous', 'statistics', 'marketing'] as $category) {
            $categories[$category] = self::get_consent_value($category) === 'allow';
        }

        if (! empty($categories['statistics'])) {
            $categories['statistics-anonymous'] = true;
        }

        return [
            'categories' => $categories,
            'decisionMade' => self::is_decision_made(),
        ];
    }

    private static function get_consent_value(string $category): string
    {
        if (function_exists('wp_get_consent')) {
            $value = wp_get_consent($category);
            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        $cookie_map = self::get_cookie_consent_map();
        return isset($cookie_map[$category]) && is_string($cookie_map[$category]) ? $cookie_map[$category] : '';
    }

    /**
     * @return array<string, string>
     */
    private static function get_cookie_consent_map(): array
    {
        if (empty($_COOKIE[self::CONSENT_COOKIE])) {
            return [];
        }

        $raw = wp_unslash((string) $_COOKIE[self::CONSENT_COOKIE]);
        $decoded = base64_decode($raw, true);
        if ($decoded === false) {
            return [];
        }

        $data = json_decode($decoded, true);
        if (! is_array($data)) {
            return [];
        }

        $allowed = ['functional', 'preferences', 'statistics-anonymous', 'statistics', 'marketing'];
        return array_intersect_key($data, array_flip($allowed));
    }

    private static function is_decision_made(): bool
    {
        $categories = ['preferences', 'statistics-anonymous', 'statistics', 'marketing'];
        $seen = 0;

        foreach ($categories as $category) {
            $value = self::get_consent_value($category);
            if ($value === 'allow' || $value === 'deny') {
                $seen++;
            }
        }

        return $seen > 0;
    }

    /**
     * @param array<string, string> $map
     */
    private static function persist_cookie_consent(array $map): void
    {
        $allowed = ['functional', 'preferences', 'statistics-anonymous', 'statistics', 'marketing'];
        $payload = [];

        foreach ($allowed as $key) {
            if (isset($map[$key])) {
                $payload[$key] = $map[$key] === 'allow' ? 'allow' : 'deny';
            }
        }

        if ($payload === []) {
            return;
        }

        $json = wp_json_encode($payload);
        if (! $json) {
            return;
        }

        $encoded = base64_encode($json);
        $cookie_args = [
            'expires' => time() + YEAR_IN_SECONDS,
            'path' => defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        if (defined('COOKIE_DOMAIN') && COOKIE_DOMAIN) {
            $cookie_args['domain'] = COOKIE_DOMAIN;
        }

        setcookie(self::CONSENT_COOKIE, $encoded, $cookie_args);
        $_COOKIE[self::CONSENT_COOKIE] = $encoded;
    }

    private function plugin_path(string $relative = ''): string
    {
        $base = plugin_dir_path($this->plugin_file);
        return $relative === '' ? $base : $base . ltrim($relative, '/');
    }
}
