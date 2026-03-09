<?php

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Returns the option name used to store privacy controller details.
 */
function the_logical_theme_privacy_controller_option_name(): string
{
    return 'privacy_controller_data';
}

/**
 * Returns the supported privacy controller fields grouped for admin rendering.
 *
 * @return array<string, array<string, string>>
 */
function the_logical_theme_get_privacy_controller_field_groups(): array
{
    return [
        'identity' => [
            'label' => __('Identity', 'the-logical-theme'),
            'fields' => [
                'display_name' => __('Display name', 'the-logical-theme'),
                'nome' => __('First name', 'the-logical-theme'),
                'cognome' => __('Last name', 'the-logical-theme'),
                'ragione_sociale' => __('Company name', 'the-logical-theme'),
                'piva' => __('VAT number', 'the-logical-theme'),
                'codice_fiscale' => __('Tax code', 'the-logical-theme'),
            ],
        ],
        'contacts' => [
            'label' => __('Contacts', 'the-logical-theme'),
            'fields' => [
                'email' => __('Email', 'the-logical-theme'),
                'pec' => __('PEC', 'the-logical-theme'),
                'telefono' => __('Phone', 'the-logical-theme'),
            ],
        ],
        'address' => [
            'label' => __('Address', 'the-logical-theme'),
            'fields' => [
                'indirizzo' => __('Street address', 'the-logical-theme'),
                'citta' => __('City', 'the-logical-theme'),
                'provincia' => __('Province', 'the-logical-theme'),
                'cap' => __('Postal code', 'the-logical-theme'),
                'nazione' => __('Country', 'the-logical-theme'),
            ],
        ],
        'dpo' => [
            'label' => __('DPO', 'the-logical-theme'),
            'fields' => [
                'dpo_nome' => __('DPO name', 'the-logical-theme'),
                'dpo_email' => __('DPO email', 'the-logical-theme'),
            ],
        ],
    ];
}

/**
 * Returns the flat list of supported privacy controller field keys.
 *
 * @return array<int, string>
 */
function the_logical_theme_get_privacy_controller_supported_keys(): array
{
    $keys = [];

    foreach (the_logical_theme_get_privacy_controller_field_groups() as $group) {
        foreach (array_keys($group['fields']) as $field_key) {
            $keys[] = $field_key;
        }
    }

    return $keys;
}

/**
 * Returns the default privacy controller data structure.
 *
 * @return array<string, string>
 */
function the_logical_theme_get_default_privacy_controller_data(): array
{
    return array_fill_keys(the_logical_theme_get_privacy_controller_supported_keys(), '');
}

/**
 * Returns the current privacy controller data with defaults merged in.
 *
 * @return array<string, string>
 */
function the_logical_theme_get_privacy_controller_data(): array
{
    $defaults = the_logical_theme_get_default_privacy_controller_data();
    $saved = get_option(the_logical_theme_privacy_controller_option_name(), []);

    if (! is_array($saved)) {
        return $defaults;
    }

    foreach ($defaults as $key => $default_value) {
        $saved[$key] = is_scalar($saved[$key] ?? null) ? (string) $saved[$key] : $default_value;
    }

    return $saved + $defaults;
}

/**
 * Returns one privacy controller value by key.
 */
function the_logical_theme_get_privacy_controller_value(string $key): string
{
    if (! in_array($key, the_logical_theme_get_privacy_controller_supported_keys(), true)) {
        return '';
    }

    $data = the_logical_theme_get_privacy_controller_data();

    return (string) ($data[$key] ?? '');
}

/**
 * Registers the privacy controller setting.
 */
function the_logical_theme_register_privacy_controller_setting(): void
{
    register_setting(
        'the_logical_theme_privacy_controller_data',
        the_logical_theme_privacy_controller_option_name(),
        [
            'type' => 'array',
            'sanitize_callback' => 'the_logical_theme_sanitize_privacy_controller_data',
            'default' => the_logical_theme_get_default_privacy_controller_data(),
        ]
    );
}
add_action('admin_init', 'the_logical_theme_register_privacy_controller_setting');

/**
 * Sanitizes the privacy controller payload.
 *
 * @param mixed $raw_value
 * @return array<string, string>
 */
function the_logical_theme_sanitize_privacy_controller_data($raw_value): array
{
    $defaults = the_logical_theme_get_default_privacy_controller_data();
    $value = is_array($raw_value) ? $raw_value : [];
    $sanitized = $defaults;

    foreach ($defaults as $key => $default_value) {
        $raw_field_value = is_scalar($value[$key] ?? null) ? (string) $value[$key] : $default_value;

        switch ($key) {
            case 'email':
            case 'pec':
            case 'dpo_email':
                $sanitized[$key] = sanitize_email($raw_field_value);
                break;

            case 'indirizzo':
                $sanitized[$key] = sanitize_textarea_field($raw_field_value);
                break;

            default:
                $sanitized[$key] = sanitize_text_field($raw_field_value);
                break;
        }
    }

    return $sanitized;
}

/**
 * Adds the privacy controller settings page under Settings.
 */
function the_logical_theme_add_privacy_controller_page(): void
{
    add_options_page(
        __('Data Controller Details', 'the-logical-theme'),
        __('Privacy Data', 'the-logical-theme'),
        'manage_options',
        'the-logical-theme-privacy-controller-data',
        'the_logical_theme_render_privacy_controller_page'
    );
}
add_action('admin_menu', 'the_logical_theme_add_privacy_controller_page');

/**
 * Renders one privacy controller field.
 */
function the_logical_theme_render_privacy_controller_field(string $key, string $label, string $value): void
{
    $option_name = the_logical_theme_privacy_controller_option_name();
    $is_textarea = 'indirizzo' === $key;
    ?>
    <tr>
        <th scope="row">
            <label for="tlt-privacy-controller-<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label>
        </th>
        <td>
            <?php if ($is_textarea) : ?>
                <textarea
                    class="large-text"
                    rows="4"
                    id="tlt-privacy-controller-<?php echo esc_attr($key); ?>"
                    name="<?php echo esc_attr($option_name); ?>[<?php echo esc_attr($key); ?>]"
                ><?php echo esc_textarea($value); ?></textarea>
            <?php else : ?>
                <input
                    type="text"
                    class="regular-text"
                    id="tlt-privacy-controller-<?php echo esc_attr($key); ?>"
                    name="<?php echo esc_attr($option_name); ?>[<?php echo esc_attr($key); ?>]"
                    value="<?php echo esc_attr($value); ?>"
                >
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

/**
 * Renders the privacy controller settings page.
 */
function the_logical_theme_render_privacy_controller_page(): void
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $data = the_logical_theme_get_privacy_controller_data();
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('Data Controller Details', 'the-logical-theme'); ?></h1>
        <p>
            <?php esc_html_e('Fill in the site-wide privacy details and reuse them in pages with the shortcode [privacy key="..."].', 'the-logical-theme'); ?>
        </p>

        <?php settings_errors('the_logical_theme_privacy_controller_data'); ?>

        <form method="post" action="options.php">
            <?php settings_fields('the_logical_theme_privacy_controller_data'); ?>

            <?php foreach (the_logical_theme_get_privacy_controller_field_groups() as $group) : ?>
                <h2><?php echo esc_html($group['label']); ?></h2>
                <table class="form-table" role="presentation">
                    <tbody>
                        <?php foreach ($group['fields'] as $key => $label) : ?>
                            <?php the_logical_theme_render_privacy_controller_field($key, $label, $data[$key] ?? ''); ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>

            <?php submit_button(__('Save Privacy Data', 'the-logical-theme')); ?>
        </form>
    </div>
    <?php
}

/**
 * Normalizes a phone number for safe tel links.
 */
function the_logical_theme_normalize_privacy_phone_href(string $phone): string
{
    $normalized = preg_replace('/(?!^\+)[^\d]/', '', trim($phone));

    if (! is_string($normalized) || '' === $normalized) {
        return '';
    }

    if (str_starts_with($normalized, '00')) {
        $normalized = '+' . substr($normalized, 2);
    }

    return preg_match('/^\+?\d+$/', $normalized) === 1 ? $normalized : '';
}

/**
 * Renders the privacy shortcode output for one field.
 */
function the_logical_theme_render_privacy_shortcode_value(string $key, string $value): string
{
    if ('' === $value) {
        return '';
    }

    switch ($key) {
        case 'email':
        case 'pec':
        case 'dpo_email':
            $email = sanitize_email($value);

            if ('' === $email) {
                return '';
            }

            return sprintf(
                '<a href="%1$s">%2$s</a>',
                esc_url('mailto:' . $email),
                esc_html($email)
            );

        case 'telefono':
            $href = the_logical_theme_normalize_privacy_phone_href($value);

            if ('' === $href) {
                return esc_html($value);
            }

            return sprintf(
                '<a href="%1$s">%2$s</a>',
                esc_url('tel:' . $href),
                esc_html($value)
            );

        case 'indirizzo':
            return nl2br(esc_html($value));

        default:
            return esc_html($value);
    }
}

/**
 * Handles the [privacy key="..."] shortcode.
 *
 * @param array<string, mixed> $atts
 */
function the_logical_theme_privacy_shortcode(array $atts = []): string
{
    $attributes = shortcode_atts(
        [
            'key' => '',
        ],
        $atts,
        'privacy'
    );

    $key = is_scalar($attributes['key']) ? sanitize_key((string) $attributes['key']) : '';

    if (! in_array($key, the_logical_theme_get_privacy_controller_supported_keys(), true)) {
        return '';
    }

    return the_logical_theme_render_privacy_shortcode_value(
        $key,
        the_logical_theme_get_privacy_controller_value($key)
    );
}
add_shortcode('privacy', 'the_logical_theme_privacy_shortcode');
