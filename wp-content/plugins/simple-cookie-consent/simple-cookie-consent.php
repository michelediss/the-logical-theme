<?php
/**
 * Plugin Name: Simple Cookie Consent
 * Description: Cookie consent banner, Gutenberg blocks, and cookie registry management.
 * Version: 0.2.0
 * Plugin URI: https://github.com/michelediss/the-logical-theme
 * Author: Michele Paolino
 * Author URI: https://michelepaolino.com
 * Text Domain: simple-cookie-consent
 * Domain Path: /languages
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/includes/class-simple-cookie-consent-plugin.php';

function simple_cookie_consent(): Simple_Cookie_Consent_Plugin
{
    return Simple_Cookie_Consent_Plugin::instance(__FILE__);
}

simple_cookie_consent();

if (! function_exists('wp_add_cookie_info')) {
    function wp_add_cookie_info($name, $service, $category, $duration, $description, $first_party = false, $personal = false, $non_eu = false)
    {
        Simple_Cookie_Consent_Plugin::fallback_add_cookie_info(
            (string) $name,
            (string) $service,
            (string) $category,
            (string) $duration,
            (string) $description,
            (bool) $first_party,
            (bool) $personal,
            (bool) $non_eu
        );

        return true;
    }
}

if (! function_exists('wp_get_cookie_info')) {
    function wp_get_cookie_info()
    {
        return Simple_Cookie_Consent_Plugin::fallback_get_cookie_info();
    }
}

function simple_cookie_consent_blocked_script(string $category, string $inline_js): void
{
    $category = sanitize_key($category);
    echo '<script type="text/plain" data-wp-consent-category="' . esc_attr($category) . '">' . $inline_js . '</script>';
}

function simple_cookie_consent_render_embed($provider_key, $payload, $message = '', $button_label = ''): string
{
    if (! $payload) {
        return '';
    }

    $provider_key = sanitize_key((string) ($provider_key ?: 'generic'));
    $message = $message ?: __('To view this content you need to accept marketing cookies.', 'simple-cookie-consent');
    $button_label = $button_label ?: __('Update settings', 'simple-cookie-consent');

    ob_start();
    ?>
    <div class="simple-cookie-consent-embed simple-cookie-consent-embed--<?php echo esc_attr($provider_key); ?>" data-simple-cookie-consent-embed="<?php echo esc_attr($provider_key); ?>" data-simple-cookie-consent-blocked="marketing" data-simple-cookie-consent-embed-html="<?php echo esc_attr($payload); ?>">
        <div class="simple-cookie-consent-embed__placeholder" role="status">
            <p><?php echo esc_html($message); ?></p>
            <button type="button" class="scc-button scc-button--secondary" data-simple-cookie-consent-open="1"><?php echo esc_html($button_label); ?></button>
        </div>
        <div class="simple-cookie-consent-embed__body" hidden aria-live="polite"></div>
    </div>
    <?php
    return (string) ob_get_clean();
}
