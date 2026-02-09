<?php
/**
* Plugin Name: Cloudflare Turnstile Login 
* Description: Protects WordPress login with Cloudflare Turnstile, storing keys in a custom database table.
* Version: 0.1.0
* Plugin URI: https://github.com/michelediss/the-logical-theme
* Author: Michele Paolino
* Author URI: https://michelepaolino.com
*/

if (!defined('ABSPATH')) {
    exit; // Impedisce l'accesso diretto
}

global $wpdb;
define('CFT_TABLE_NAME', $wpdb->prefix . 'cf_turnstile_keys');

/**
 * 1. ATTIVAZIONE: Creazione/Aggiornamento tabella nel DB
 */
function cft_plugin_activate() {
    global $wpdb;
    $table_name = CFT_TABLE_NAME;
    $charset_collate = $wpdb->get_charset_collate();

    // Aggiunta la colonna 'is_active' (tinyint 1 = true, 0 = false)
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

    // Inseriamo la riga iniziale se non esiste
    $row_exists = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    if ($row_exists == 0) {
        $wpdb->insert(
            $table_name,
            array(
                'site_key' => '',
                'secret_key' => '',
                'is_active' => 1, // Attivo di default
                'updated_at' => current_time('mysql')
            )
        );
    }
}
register_activation_hook(__FILE__, 'cft_plugin_activate');

/**
 * 2. HELPERS: Funzioni DB
 */
function cft_get_config() {
    global $wpdb;
    $table_name = CFT_TABLE_NAME;
    // Recuperiamo anche lo stato is_active
    return $wpdb->get_row("SELECT site_key, secret_key, is_active FROM $table_name WHERE id = 1");
}

/**
 * 3. ADMIN: Pagina di configurazione (PROTETTA)
 */
function cft_add_admin_menu() {
    add_options_page(
        'Turnstile Login', 
        'Turnstile Login', 
        'manage_options', 
        'cf-turnstile-login', 
        'cft_options_page'
    );
}
add_action('admin_menu', 'cft_add_admin_menu');

function cft_options_page() {
    // Controllo Ruolo Administrator
    $current_user = wp_get_current_user();
    if ( ! in_array( 'administrator', (array) $current_user->roles ) ) {
        wp_die( __('Accesso negato. Questa pagina è riservata esclusivamente agli amministratori.', 'cf-turnstile') );
    }

    global $wpdb;
    $table_name = CFT_TABLE_NAME;

    // Salvataggio dati
    if (isset($_POST['cft_submit']) && check_admin_referer('cft_save_keys_action', 'cft_nonce_field')) {
        $site_key = sanitize_text_field($_POST['site_key']);
        $secret_key = sanitize_text_field($_POST['secret_key']);
        // Checkbox: se non è settato nell'array $_POST, significa che è stato deselezionato (quindi 0)
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
        echo '<div class="updated"><p>Impostazioni salvate con successo.</p></div>';
    }

    $config = cft_get_config();
    ?>
    <div class="wrap">
        <h2>Configurazione Cloudflare Turnstile</h2>
        
        <form method="post" action="">
            <?php wp_nonce_field('cft_save_keys_action', 'cft_nonce_field'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Stato Turnstile</th>
                    <td>
                        <label for="is_active">
                            <input type="checkbox" name="is_active" id="is_active" value="1" <?php checked(1, $config->is_active); ?> />
                            Attiva la protezione sul login
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
            <?php submit_button('Salva Impostazioni', 'primary', 'cft_submit'); ?>
        </form>
    </div>
    <?php
}

/**
 * 4. FRONTEND: Script e Widget
 */
function cft_login_script() {
    $config = cft_get_config();
    // Se disattivato o chiavi mancanti, non caricare JS
    if (empty($config->site_key) || $config->is_active == 0) return;

    echo '<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>';
}
add_action('login_head', 'cft_login_script');

function cft_login_form() {
    $config = cft_get_config();
    
    // Se disattivato o chiavi mancanti, non mostrare widget
    if (empty($config->site_key) || empty($config->secret_key) || $config->is_active == 0) {
        return;
    }

    echo '<div class="cf-turnstile" data-sitekey="' . esc_attr($config->site_key) . '" data-theme="light" style="margin-bottom: 10px;"></div>';
}
add_action('login_form', 'cft_login_form');

/**
 * 5. BACKEND: Verifica del Token
 */
function cft_authenticate_check($user, $password) {
    if (is_wp_error($user)) return $user;

    $config = cft_get_config();

    // SE IL PLUGIN È DISATTIVATO DALLA CHECKBOX, SALTA IL CONTROLLO
    if ($config->is_active == 0) {
        return $user;
    }

    // Fallback se chiavi vuote
    if (empty($config->site_key) || empty($config->secret_key)) return $user;

    if (!isset($_POST['cf-turnstile-response'])) {
        return new WP_Error('turnstile_error', __('<b>Errore</b>: Verifica di sicurezza mancante.', 'cf-turnstile'));
    }

    $token = $_POST['cf-turnstile-response'];
    $ip = $_SERVER['REMOTE_ADDR'];

    $verify_url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $data = array(
        'secret' => $config->secret_key,
        'response' => $token,
        'remoteip' => $ip
    );

    $response = wp_remote_post($verify_url, array('body' => $data));

    if (is_wp_error($response)) {
        return new WP_Error('turnstile_error', __('Errore connessione Cloudflare.', 'cf-turnstile'));
    }

    $result = json_decode(wp_remote_retrieve_body($response));

    if (!$result->success) {
        return new WP_Error('turnstile_error', __('Verifica fallita. Riprova.', 'cf-turnstile'));
    }

    return $user;
}
add_filter('wp_authenticate_user', 'cft_authenticate_check', 10, 2);
