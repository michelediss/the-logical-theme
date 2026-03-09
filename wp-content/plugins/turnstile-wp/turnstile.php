<?php
/**
* Plugin Name: Cloudflare Turnstile Login 
* Description: Protects WordPress login with Cloudflare Turnstile, storing keys in a custom database table.
* Version: 0.1.0
* Plugin URI: https://github.com/michelediss/the-logical-theme
* Author: Michele Paolino
* Author URI: https://michelepaolino.com
* Text Domain: turnstile-wp
* Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
define('CFT_TABLE_NAME', $wpdb->prefix . 'cf_turnstile_keys');
define('CFT_TEXT_DOMAIN', 'turnstile-wp');

function cft_load_textdomain() {
    load_plugin_textdomain(
        CFT_TEXT_DOMAIN,
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}
add_action('init', 'cft_load_textdomain');

/**
 * Creates or updates the plugin settings table on activation.
 */
function cft_plugin_activate() {
    global $wpdb;
    $table_name = CFT_TABLE_NAME;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        site_key tinytext NOT NULL,
        secret_key tinytext NOT NULL,
        is_active tinyint(1) DEFAULT 1 NOT NULL,
        updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    $row_exists = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    if ($row_exists == 0) {
        $wpdb->insert(
            $table_name,
            array(
                'site_key' => '',
                'secret_key' => '',
                'is_active' => 1,
                'updated_at' => current_time('mysql')
            )
        );
    }
}
register_activation_hook(__FILE__, 'cft_plugin_activate');

/**
 * Reads the stored Turnstile configuration.
 */
function cft_get_config() {
    global $wpdb;
    $table_name = CFT_TABLE_NAME;
    return $wpdb->get_row("SELECT site_key, secret_key, is_active FROM $table_name WHERE id = 1");
}

/**
 * Registers the settings page.
 */
function cft_add_admin_menu() {
    add_options_page(
        __('Turnstile Login', 'turnstile-wp'),
        __('Turnstile Login', 'turnstile-wp'),
        'manage_options', 
        'cf-turnstile-login', 
        'cft_options_page'
    );
}
add_action('admin_menu', 'cft_add_admin_menu');

function cft_options_page() {
    $current_user = wp_get_current_user();
    if ( ! in_array( 'administrator', (array) $current_user->roles ) ) {
        wp_die(__('Access denied. This page is restricted to administrators only.', 'turnstile-wp'));
    }

    global $wpdb;
    $table_name = CFT_TABLE_NAME;

    if (isset($_POST['cft_submit']) && check_admin_referer('cft_save_keys_action', 'cft_nonce_field')) {
        $site_key = sanitize_text_field(wp_unslash($_POST['site_key']));
        $secret_key = sanitize_text_field(wp_unslash($_POST['secret_key']));
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        $wpdb->update(
            $table_name,
            array(
                'site_key' => $site_key,
                'secret_key' => $secret_key,
                'is_active' => $is_active,
                'updated_at' => current_time('mysql')
            ),
            array('id' => 1)
        );
        echo '<div class="updated"><p>' . esc_html__('Settings saved successfully.', 'turnstile-wp') . '</p></div>';
    }

    $config = cft_get_config();
    ?>
    <div class="wrap">
        <h2><?php esc_html_e('Cloudflare Turnstile Configuration', 'turnstile-wp'); ?></h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('cft_save_keys_action', 'cft_nonce_field'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Turnstile Status', 'turnstile-wp'); ?></th>
                    <td>
                        <label for="is_active">
                            <input type="checkbox" name="is_active" id="is_active" value="1" <?php checked(1, $config->is_active); ?> />
                            <?php esc_html_e('Enable login protection', 'turnstile-wp'); ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Site Key</th>
                    <td><input type="text" name="site_key" value="<?php echo esc_attr($config->site_key); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Secret Key</th>
                    <td><input type="text" name="secret_key" value="<?php echo esc_attr($config->secret_key); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <?php submit_button(__('Save Settings', 'turnstile-wp'), 'primary', 'cft_submit'); ?>
        </form>
    </div>
    <?php
}

/**
 * Outputs the Turnstile API on the login screen.
 */
function cft_login_script() {
    $config = cft_get_config();
    if (empty($config->site_key) || $config->is_active == 0) return;

    echo '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
}
add_action('login_head', 'cft_login_script');

function cft_login_form() {
    $config = cft_get_config();
    
    if (empty($config->site_key) || empty($config->secret_key) || $config->is_active == 0) {
        return;
    }

    echo '<div class="cf-turnstile" data-sitekey="' . esc_attr($config->site_key) . '" data-theme="light" style="margin-bottom: 10px;"></div>';
}
add_action('login_form', 'cft_login_form');

/**
 * Validates the Turnstile token during login.
 */
function cft_authenticate_check($user, $password) {
    if (is_wp_error($user)) return $user;

    $config = cft_get_config();

    if ($config->is_active == 0) {
        return $user;
    }

    if (empty($config->site_key) || empty($config->secret_key)) return $user;

    if (!isset($_POST['cf-turnstile-response'])) {
        return new WP_Error('turnstile_error', __('<b>Error</b>: Security verification is missing.', 'turnstile-wp'));
    }

    $token = sanitize_text_field(wp_unslash($_POST['cf-turnstile-response']));
    $ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '';

    $verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = array(
        'secret' => $config->secret_key,
        'response' => $token,
        'remoteip' => $ip
    );

    $response = wp_remote_post($verify_url, array('body' => $data));

    if (is_wp_error($response)) {
        return new WP_Error('turnstile_error', __('Cloudflare connection error.', 'turnstile-wp'));
    }

    $result = json_decode(wp_remote_retrieve_body($response));

    if (!$result->success) {
        return new WP_Error('turnstile_error', __('Verification failed. Please try again.', 'turnstile-wp'));
    }

    return $user;
}
add_filter('wp_authenticate_user', 'cft_authenticate_check', 10, 2);
