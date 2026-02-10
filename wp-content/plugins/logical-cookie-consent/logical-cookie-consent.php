<?php
/**
* Plugin Name: Logical Cookie Consent
* Description: Cookie banner
* Version: 0.1.0
* Plugin URI: https://github.com/michelediss/the-logical-theme
* Author: Michele Paolino
* Author URI: https://michelepaolino.com
 */

if (!defined('ABSPATH')) exit;

final class Logical_Cookie_Consent_WPCA {
  const VER = '0.1.0';
  const COOKIE_REGISTRY_RELATIVE_PATH = 'assets/json/lcc-cookies.json';

  private $banner_rendered = false;
  private static $fallback_cookie_store = [];

  public function __construct() {
    add_action('init', [$this, 'load_textdomain']);
    add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    add_action('wp_head', [$this, 'print_inline_styles'], PHP_INT_MAX);
    add_action('wp_enqueue_scripts', [$this, 'ensure_assets_available'], PHP_INT_MAX);

    add_action('wp_footer', [$this, 'render_banner'], 999);
    if (function_exists('wp_body_open')) {
      add_action('wp_body_open', [$this, 'render_banner'], 5);
    }

    add_action('wp_ajax_lcc_set_consent', [$this, 'ajax_set_consent']);
    add_action('wp_ajax_nopriv_lcc_set_consent', [$this, 'ajax_set_consent']);

    add_filter('wp_get_consent_type', [$this, 'set_consent_type'], 10, 1);

    // Dichiara compatibilità per WP Consent API :contentReference[oaicite:4]{index=4}
    $plugin = plugin_basename(__FILE__);
    add_filter("wp_consent_api_registered_{$plugin}", '__return_true');

    // Registrazione cookie per cookie policy :contentReference[oaicite:5]{index=5}
    add_action('init', [$this, 'register_cookies_info'], 20);

    // Shortcode tabella cookie
    add_shortcode('lcc_cookie_table', [$this, 'shortcode_cookie_table']);

    // Admin pagina gestione registro cookie (JSON nel tema).
    add_action('admin_menu', [$this, 'register_admin_page']);
    add_action('admin_post_lcc_add_cookie', [$this, 'handle_admin_add_cookie']);
    add_action('admin_post_lcc_delete_cookie', [$this, 'handle_admin_delete_cookie']);
    add_action('admin_post_lcc_update_cookie', [$this, 'handle_admin_update_cookie']);
  }

  public function load_textdomain() {
    load_plugin_textdomain('lcc', false, dirname(plugin_basename(__FILE__)) . '/languages');
  }

  private static function get_default_cookie_entries() : array {
    return [
      [
        'name' => 'lcc_consent',
        'service' => 'Logical Cookie Consent',
        'category' => 'functional',
        'duration' => '1 anno',
        'description' => 'Memorizza lo stato di consenso per ogni categoria di cookie.',
        'first_party' => true,
      ],
      [
        'name' => 'wordpress_[hash]',
        'service' => 'WordPress',
        'category' => 'functional',
        'duration' => 'Sessione',
        'description' => 'Mantiene la sessione autenticata nell’area wp-admin.',
        'first_party' => true,
      ],
      [
        'name' => 'wordpress_sec_[hash]',
        'service' => 'WordPress',
        'category' => 'functional',
        'duration' => 'Sessione',
        'description' => 'Mantiene la sessione autenticata durante l’uso dei plugin.',
        'first_party' => true,
      ],
      [
        'name' => 'wordpress_logged_in_[hash]',
        'service' => 'WordPress',
        'category' => 'functional',
        'duration' => 'Sessione',
        'description' => 'Memorizza l’utente autenticato dopo il login.',
        'first_party' => true,
      ],
      [
        'name' => 'wordpress_test_cookie',
        'service' => 'WordPress',
        'category' => 'functional',
        'duration' => 'Sessione',
        'description' => 'Verifica se il browser accetta i cookie.',
        'first_party' => true,
      ],
      [
        'name' => 'wp_lang',
        'service' => 'WordPress',
        'category' => 'functional',
        'duration' => 'Sessione',
        'description' => 'Salva la lingua corrente dell’interfaccia.',
        'first_party' => true,
      ],
      [
        'name' => 'wp-settings-1',
        'service' => 'WordPress',
        'category' => 'functional',
        'duration' => '1 anno',
        'description' => 'Conserva le preferenze personali della dashboard.',
        'first_party' => true,
      ],
      [
        'name' => 'wp-settings-time-1',
        'service' => 'WordPress',
        'category' => 'functional',
        'duration' => '1 anno',
        'description' => 'Registra il timestamp di aggiornamento delle impostazioni utente.',
        'first_party' => true,
      ],
      [
        'name' => 'youtube_*',
        'service' => 'YouTube',
        'category' => 'marketing',
        'duration' => 'Fino a 2 anni',
        'description' => 'Memorizza preferenze di riproduzione e traccia le visualizzazioni dei video incorporati.',
        'first_party' => false,
      ],
      [
        'name' => 'spotify_*',
        'service' => 'Spotify',
        'category' => 'marketing',
        'duration' => 'Sessione / 1 anno',
        'description' => 'Permette a Spotify di caricare player embed e raccogliere statistiche sull’ascolto.',
        'first_party' => false,
      ],
      [
        'name' => 'x.com_*',
        'service' => 'X (Twitter)',
        'category' => 'marketing',
        'duration' => 'Fino a 2 anni',
        'description' => 'Traccia interazioni con i tweet incorporati e personalizza i contenuti pubblicitari.',
        'first_party' => false,
      ],
      [
        'name' => 'facebook_*',
        'service' => 'Facebook',
        'category' => 'marketing',
        'duration' => 'Fino a 2 anni',
        'description' => 'Cookie usati da Facebook per misurare e personalizzare i contenuti degli embed.',
        'first_party' => false,
      ],
      [
        'name' => 'instagram_*',
        'service' => 'Instagram',
        'category' => 'marketing',
        'duration' => 'Sessione / 1 anno',
        'description' => 'Gestisce il caricamento dei post e delle storie incorporati e raccoglie metriche.',
        'first_party' => false,
      ],
      [
        'name' => 'tiktok_*',
        'service' => 'TikTok',
        'category' => 'marketing',
        'duration' => 'Fino a 13 mesi',
        'description' => 'Serve a TikTok per riprodurre i video embed e analizzare l’engagement.',
        'first_party' => false,
      ],
    ];
  }

  private function get_cookie_registry_paths() : array {
    $relative = self::COOKIE_REGISTRY_RELATIVE_PATH;
    $paths = [];

    $paths[] = trailingslashit(get_stylesheet_directory()) . $relative;

    $template_path = trailingslashit(get_template_directory()) . $relative;
    if ($template_path !== $paths[0]) {
      $paths[] = $template_path;
    }

    return $paths;
  }

  private function get_cookie_registry_read_path() : string {
    $paths = $this->get_cookie_registry_paths();

    foreach ($paths as $path) {
      if (is_readable($path)) {
        return $path;
      }
    }

    return $paths[0];
  }

  private function get_cookie_registry_write_path() : string {
    $paths = $this->get_cookie_registry_paths();
    return $paths[0];
  }

  private function ensure_cookie_registry_file() : void {
    $read_path = $this->get_cookie_registry_read_path();
    if (file_exists($read_path)) {
      return;
    }

    $write_path = $this->get_cookie_registry_write_path();
    $dir = dirname($write_path);
    if (!is_dir($dir)) {
      wp_mkdir_p($dir);
    }

    $json = wp_json_encode(
      self::get_default_cookie_entries(),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($json) {
      file_put_contents($write_path, $json . PHP_EOL, LOCK_EX);
    }
  }

  private static function normalize_registry_category($value) : string {
    $value = trim(wp_strip_all_tags((string) $value));
    $key = sanitize_key($value);

    if ($key === 'statistics_anonymous') {
      return 'statistics-anonymous';
    }

    if (in_array($key, ['third_party', 'thirdparty', 'terze_parti', 'tereze_parti'], true)) {
      return 'marketing';
    }

    $allowed = ['functional', 'preferences', 'statistics-anonymous', 'statistics', 'marketing'];
    if (in_array($key, $allowed, true)) {
      return $key;
    }

    if ($value === 'terze parti' || $value === 'tereze parti') {
      return 'marketing';
    }

    return '';
  }

  private static function sanitize_cookie_entry(array $entry) : ?array {
    $name = sanitize_text_field((string) ($entry['name'] ?? ''));
    $service = sanitize_text_field((string) ($entry['service'] ?? ''));
    $category = self::normalize_registry_category($entry['category'] ?? '');
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

  private function read_cookie_entries_from_registry() : array {
    $this->ensure_cookie_registry_file();

    $path = $this->get_cookie_registry_read_path();
    if (!is_readable($path)) {
      return self::get_default_cookie_entries();
    }

    $raw = file_get_contents($path);
    if (!is_string($raw) || trim($raw) === '') {
      return self::get_default_cookie_entries();
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
      return self::get_default_cookie_entries();
    }

    $entries = [];
    foreach ($decoded as $row) {
      if (!is_array($row)) {
        continue;
      }

      $clean = self::sanitize_cookie_entry($row);
      if ($clean !== null) {
        $entries[] = $clean;
      }
    }

    return $entries;
  }

  private function write_cookie_entries_to_registry(array $entries) : bool {
    $write_path = $this->get_cookie_registry_write_path();
    $dir = dirname($write_path);
    if (!is_dir($dir) && !wp_mkdir_p($dir)) {
      return false;
    }

    $json = wp_json_encode(
      array_values($entries),
      JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if (!$json) {
      return false;
    }

    return file_put_contents($write_path, $json . PHP_EOL, LOCK_EX) !== false;
  }

  private static function is_third_party_service(string $service) : bool {
    $service = strtolower(trim($service));
    $known = ['youtube', 'spotify', 'x (twitter)', 'twitter', 'facebook', 'instagram', 'tiktok'];
    return in_array($service, $known, true);
  }

  public function register_admin_page() : void {
    add_options_page(
      __('Logical Cookie Consent', 'lcc'),
      __('Cookie Registry', 'lcc'),
      'manage_options',
      'lcc-cookie-registry',
      [$this, 'render_admin_page']
    );
  }

  private function get_admin_page_url(array $args = []) : string {
    $base = admin_url('options-general.php?page=lcc-cookie-registry');
    return empty($args) ? $base : add_query_arg($args, $base);
  }

  public function handle_admin_add_cookie() : void {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('Permessi insufficienti.', 'lcc'));
    }

    check_admin_referer('lcc_add_cookie');

    $entry = self::sanitize_cookie_entry([
      'name' => isset($_POST['name']) ? wp_unslash($_POST['name']) : '',
      'service' => isset($_POST['service']) ? wp_unslash($_POST['service']) : '',
      'category' => isset($_POST['category']) ? wp_unslash($_POST['category']) : '',
      'duration' => isset($_POST['duration']) ? wp_unslash($_POST['duration']) : '',
      'description' => isset($_POST['description']) ? wp_unslash($_POST['description']) : '',
      'first_party' => !empty($_POST['first_party']),
    ]);

    if ($entry === null) {
      wp_safe_redirect($this->get_admin_page_url(['lcc_status' => 'error']));
      exit;
    }

    $entries = $this->read_cookie_entries_from_registry();
    $entries[] = $entry;

    $ok = $this->write_cookie_entries_to_registry($entries);
    wp_safe_redirect($this->get_admin_page_url(['lcc_status' => $ok ? 'added' : 'error']));
    exit;
  }

  public function handle_admin_delete_cookie() : void {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('Permessi insufficienti.', 'lcc'));
    }

    check_admin_referer('lcc_delete_cookie');

    $index = isset($_POST['cookie_index']) ? (int) $_POST['cookie_index'] : -1;
    $entries = $this->read_cookie_entries_from_registry();

    if (!isset($entries[$index])) {
      wp_safe_redirect($this->get_admin_page_url(['lcc_status' => 'error']));
      exit;
    }

    unset($entries[$index]);
    $ok = $this->write_cookie_entries_to_registry(array_values($entries));

    wp_safe_redirect($this->get_admin_page_url(['lcc_status' => $ok ? 'deleted' : 'error']));
    exit;
  }

  public function handle_admin_update_cookie() : void {
    if (!current_user_can('manage_options')) {
      wp_die(esc_html__('Permessi insufficienti.', 'lcc'));
    }

    check_admin_referer('lcc_update_cookie');

    $index = isset($_POST['cookie_index']) ? (int) $_POST['cookie_index'] : -1;
    $entries = $this->read_cookie_entries_from_registry();

    if (!isset($entries[$index])) {
      wp_safe_redirect($this->get_admin_page_url(['lcc_status' => 'error']));
      exit;
    }

    $entry = self::sanitize_cookie_entry([
      'name' => isset($_POST['name']) ? wp_unslash($_POST['name']) : '',
      'service' => isset($_POST['service']) ? wp_unslash($_POST['service']) : '',
      'category' => isset($_POST['category']) ? wp_unslash($_POST['category']) : '',
      'duration' => isset($_POST['duration']) ? wp_unslash($_POST['duration']) : '',
      'description' => isset($_POST['description']) ? wp_unslash($_POST['description']) : '',
      'first_party' => !empty($_POST['first_party']),
    ]);

    if ($entry === null) {
      wp_safe_redirect($this->get_admin_page_url(['lcc_status' => 'error', 'lcc_edit' => $index]));
      exit;
    }

    $entries[$index] = $entry;
    $ok = $this->write_cookie_entries_to_registry($entries);

    wp_safe_redirect($this->get_admin_page_url(['lcc_status' => $ok ? 'updated' : 'error']));
    exit;
  }

  public function render_admin_page() : void {
    if (!current_user_can('manage_options')) {
      return;
    }

    $entries = $this->read_cookie_entries_from_registry();
    $read_path = $this->get_cookie_registry_read_path();
    $write_path = $this->get_cookie_registry_write_path();
    $status = isset($_GET['lcc_status']) ? sanitize_key((string) $_GET['lcc_status']) : '';
    $edit_index = isset($_GET['lcc_edit']) ? (int) $_GET['lcc_edit'] : -1;
    $edit_entry = isset($entries[$edit_index]) ? $entries[$edit_index] : null;
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('Registro Cookie', 'lcc'); ?></h1>
      <p><?php esc_html_e('Il plugin legge prima il file del child theme e, in fallback, quello del parent theme.', 'lcc'); ?></p>
      <p>
        <strong><?php esc_html_e('File in uso:', 'lcc'); ?></strong>
        <code><?php echo esc_html($read_path); ?></code><br>
        <strong><?php esc_html_e('File in scrittura:', 'lcc'); ?></strong>
        <code><?php echo esc_html($write_path); ?></code>
      </p>

      <?php if ($status === 'added') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Cookie aggiunto.', 'lcc'); ?></p></div>
      <?php elseif ($status === 'deleted') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Cookie eliminato.', 'lcc'); ?></p></div>
      <?php elseif ($status === 'updated') : ?>
        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Cookie aggiornato.', 'lcc'); ?></p></div>
      <?php elseif ($status === 'error') : ?>
        <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Operazione non riuscita. Verifica i campi obbligatori e i permessi di scrittura.', 'lcc'); ?></p></div>
      <?php endif; ?>

      <h2><?php esc_html_e('Cookie registrati', 'lcc'); ?></h2>
      <table class="widefat striped">
        <thead>
          <tr>
            <th><?php esc_html_e('Nome', 'lcc'); ?></th>
            <th><?php esc_html_e('Servizio', 'lcc'); ?></th>
            <th><?php esc_html_e('Categoria', 'lcc'); ?></th>
            <th><?php esc_html_e('Durata', 'lcc'); ?></th>
            <th><?php esc_html_e('Descrizione', 'lcc'); ?></th>
            <th><?php esc_html_e('First party', 'lcc'); ?></th>
            <th><?php esc_html_e('Azioni', 'lcc'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($entries)) : ?>
            <tr><td colspan="7"><?php esc_html_e('Nessun cookie registrato.', 'lcc'); ?></td></tr>
          <?php else : ?>
            <?php foreach ($entries as $index => $entry) : ?>
              <tr>
                <td><?php echo esc_html((string) ($entry['name'] ?? '')); ?></td>
                <td><?php echo esc_html((string) ($entry['service'] ?? '')); ?></td>
                <td><code><?php echo esc_html((string) ($entry['category'] ?? '')); ?></code></td>
                <td><?php echo esc_html((string) ($entry['duration'] ?? '')); ?></td>
                <td><?php echo esc_html((string) ($entry['description'] ?? '')); ?></td>
                <td><?php echo !empty($entry['first_party']) ? 'true' : 'false'; ?></td>
                <td>
                  <a class="button button-secondary" href="<?php echo esc_url($this->get_admin_page_url(['lcc_edit' => $index])); ?>">
                    <?php esc_html_e('Modifica', 'lcc'); ?>
                  </a>
                  <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('lcc_delete_cookie'); ?>
                    <input type="hidden" name="action" value="lcc_delete_cookie">
                    <input type="hidden" name="cookie_index" value="<?php echo esc_attr((string) $index); ?>">
                    <button type="submit" class="button button-link-delete"><?php esc_html_e('Elimina', 'lcc'); ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>

      <?php if (is_array($edit_entry)) : ?>
        <h2 style="margin-top: 30px;"><?php esc_html_e('Modifica cookie', 'lcc'); ?></h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <?php wp_nonce_field('lcc_update_cookie'); ?>
          <input type="hidden" name="action" value="lcc_update_cookie">
          <input type="hidden" name="cookie_index" value="<?php echo esc_attr((string) $edit_index); ?>">
          <table class="form-table" role="presentation">
            <tr>
              <th scope="row"><label for="lcc-edit-name"><?php esc_html_e('Nome cookie', 'lcc'); ?></label></th>
              <td><input id="lcc-edit-name" name="name" type="text" class="regular-text" required value="<?php echo esc_attr((string) ($edit_entry['name'] ?? '')); ?>"></td>
            </tr>
            <tr>
              <th scope="row"><label for="lcc-edit-service"><?php esc_html_e('Servizio', 'lcc'); ?></label></th>
              <td><input id="lcc-edit-service" name="service" type="text" class="regular-text" required value="<?php echo esc_attr((string) ($edit_entry['service'] ?? '')); ?>"></td>
            </tr>
            <tr>
              <th scope="row"><label for="lcc-edit-category"><?php esc_html_e('Categoria', 'lcc'); ?></label></th>
              <td>
                <select id="lcc-edit-category" name="category">
                  <option value="functional" <?php selected((string) ($edit_entry['category'] ?? ''), 'functional'); ?>>functional</option>
                  <option value="preferences" <?php selected((string) ($edit_entry['category'] ?? ''), 'preferences'); ?>>preferences</option>
                  <option value="statistics-anonymous" <?php selected((string) ($edit_entry['category'] ?? ''), 'statistics-anonymous'); ?>>statistics-anonymous</option>
                  <option value="statistics" <?php selected((string) ($edit_entry['category'] ?? ''), 'statistics'); ?>>statistics</option>
                  <option value="marketing" <?php selected((string) ($edit_entry['category'] ?? ''), 'marketing'); ?>>marketing</option>
                </select>
              </td>
            </tr>
            <tr>
              <th scope="row"><label for="lcc-edit-duration"><?php esc_html_e('Durata', 'lcc'); ?></label></th>
              <td><input id="lcc-edit-duration" name="duration" type="text" class="regular-text" value="<?php echo esc_attr((string) ($edit_entry['duration'] ?? '')); ?>"></td>
            </tr>
            <tr>
              <th scope="row"><label for="lcc-edit-description"><?php esc_html_e('Descrizione', 'lcc'); ?></label></th>
              <td><textarea id="lcc-edit-description" name="description" rows="4" class="large-text"><?php echo esc_textarea((string) ($edit_entry['description'] ?? '')); ?></textarea></td>
            </tr>
            <tr>
              <th scope="row"><?php esc_html_e('First party', 'lcc'); ?></th>
              <td><label><input type="checkbox" name="first_party" value="1" <?php checked(!empty($edit_entry['first_party'])); ?>> <?php esc_html_e('Cookie di prima parte', 'lcc'); ?></label></td>
            </tr>
          </table>
          <?php submit_button(__('Salva modifiche', 'lcc')); ?>
          <a href="<?php echo esc_url($this->get_admin_page_url()); ?>" class="button"><?php esc_html_e('Annulla', 'lcc'); ?></a>
        </form>
      <?php endif; ?>

      <h2 style="margin-top: 30px;"><?php esc_html_e('Aggiungi cookie', 'lcc'); ?></h2>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('lcc_add_cookie'); ?>
        <input type="hidden" name="action" value="lcc_add_cookie">
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="lcc-name"><?php esc_html_e('Nome cookie', 'lcc'); ?></label></th>
            <td><input id="lcc-name" name="name" type="text" class="regular-text" required></td>
          </tr>
          <tr>
            <th scope="row"><label for="lcc-service"><?php esc_html_e('Servizio', 'lcc'); ?></label></th>
            <td><input id="lcc-service" name="service" type="text" class="regular-text" required></td>
          </tr>
          <tr>
            <th scope="row"><label for="lcc-category"><?php esc_html_e('Categoria', 'lcc'); ?></label></th>
            <td>
              <select id="lcc-category" name="category">
                <option value="functional">functional</option>
                <option value="preferences">preferences</option>
                <option value="statistics-anonymous">statistics-anonymous</option>
                <option value="statistics">statistics</option>
                <option value="marketing">marketing</option>
              </select>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="lcc-duration"><?php esc_html_e('Durata', 'lcc'); ?></label></th>
            <td><input id="lcc-duration" name="duration" type="text" class="regular-text"></td>
          </tr>
          <tr>
            <th scope="row"><label for="lcc-description"><?php esc_html_e('Descrizione', 'lcc'); ?></label></th>
            <td><textarea id="lcc-description" name="description" rows="4" class="large-text"></textarea></td>
          </tr>
          <tr>
            <th scope="row"><?php esc_html_e('First party', 'lcc'); ?></th>
            <td><label><input type="checkbox" name="first_party" value="1" checked> <?php esc_html_e('Cookie di prima parte', 'lcc'); ?></label></td>
          </tr>
        </table>
        <?php submit_button(__('Aggiungi cookie', 'lcc')); ?>
      </form>
    </div>
    <?php
  }

  public function enqueue_assets() {
    $deps = [];
    $lds_handle = 'logical-design-system-styles-min';
    if (wp_style_is($lds_handle, 'enqueued') || wp_style_is($lds_handle, 'registered')) {
      $deps[] = $lds_handle;
    }

    // CSS fornito dal tema: ns. handle registra solo dipendenze.
    wp_register_style('lcc', false, $deps, self::VER);
    wp_enqueue_style('lcc');
    $script_handle = 'lcc';
    $script_path = plugin_dir_path(__FILE__) . 'assets/logical-cookie-consent.js';
    wp_register_script($script_handle, false, [], self::VER, true);

    $initialConsent = $this->get_localized_consent_state();

    wp_localize_script($script_handle, 'LCC', [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce'   => wp_create_nonce('lcc_nonce'),
      'initialConsent' => $initialConsent,
      'showBannerOnLoad' => empty($initialConsent['decisionMade']),
      'cookieDetails' => $this->get_cookie_details(),
      'texts' => [
        'title' => __('Cookie', 'lcc'),
        'desc'  => __('Usiamo cookie necessari e, con il tuo consenso, cookie per preferenze, analytics e marketing.', 'lcc'),
        'acceptAll' => __('Accetta tutti', 'lcc'),
        'rejectAll' => __('Rifiuta', 'lcc'),
        'settings'  => __('Impostazioni', 'lcc'),
        'save'      => __('Salva', 'lcc'),
        'close'     => __('Chiudi', 'lcc'),

        'necessary' => __('Necessari', 'lcc'),
        'preferences' => __('Preferenze', 'lcc'),
        'statsAnon' => __('Analytics anonimi', 'lcc'),
        'analytics' => __('Analytics', 'lcc'),
        'marketing' => __('Marketing', 'lcc'),

        'cookiesTitle' => __('Dettaglio cookie', 'lcc'),
        'serviceLabel' => __('Servizio', 'lcc'),
        'durationLabel' => __('Durata', 'lcc'),
        'descriptionLabel' => __('Descrizione', 'lcc'),
        'noCookies' => __('Nessun cookie registrato per questa categoria.', 'lcc'),

        'manage' => __('Rivedi consenso cookie', 'lcc'),
        'embedBlocked' => __('Per visualizzare questo contenuto devi accettare i cookie.', 'lcc'),
      ]
    ]);

    wp_enqueue_script($script_handle);

    if (is_readable($script_path)) {
      $script_contents = file_get_contents($script_path);
      if (!empty($script_contents)) {
        wp_add_inline_script($script_handle, $script_contents);
      }
    }
  }

  public function ensure_assets_available() {
    // Alcuni temi/plugin (es. Logical Design System) deregistrano CSS dei plugin dopo il nostro enqueue.
    if (!wp_style_is('lcc', 'enqueued')) {
      wp_enqueue_style('lcc');
    }
  }

  public function print_inline_styles() {
    ?>
    <style id="lcc-inline-styles">
      .lcc-embed-blocked {
        position: relative;
        background: var(--white);
        border: 2px solid rgba(0, 0, 0, 0.08);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
      }

      .ratio > .lcc-embed-blocked {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
      }

      .lcc-embed-blocked iframe {
        display: none !important;
      }

      .lcc-embed-blocked__placeholder {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 20px;
        gap: 12px;
        background: rgba(255, 255, 255, 0.95);
        color: var(--black);
        width: 100%;
        height: 100%;
      }

      .lcc-embed-blocked__placeholder .lcc-btn {
        margin-top: 4px;
      }
    </style>
    <?php
  }

  public function set_consent_type($type) {
    // Opt-in: in assenza di scelta esplicita, categories non funzionali devono risultare false :contentReference[oaicite:6]{index=6}
    return 'optin';
  }

  private static function get_consent_value(string $category) : string {
    // Ritorna 'allow' | 'deny' | '' (se non disponibile) :contentReference[oaicite:7]{index=7}
    if (function_exists('wp_get_consent')) {
      $v = wp_get_consent($category);
      return is_string($v) ? $v : '';
    }

    $cookie_map = self::get_cookie_consent_map();
    return isset($cookie_map[$category]) && is_string($cookie_map[$category]) ? $cookie_map[$category] : '';
  }

  private static function get_cookie_consent_map() : array {
    if (empty($_COOKIE['lcc_consent'])) return [];

    $raw = wp_unslash((string) $_COOKIE['lcc_consent']);
    $decoded = base64_decode($raw, true);
    if ($decoded === false) return [];

    $data = json_decode($decoded, true);
    if (!is_array($data)) return [];

    $allowed = ['functional', 'preferences', 'statistics-anonymous', 'statistics', 'marketing'];
    return array_intersect_key($data, array_flip($allowed));
  }

  private static function is_cookie_debug_mode() : bool {
    /**
     * Imposta define('LCC_COOKIE_DEBUG', true); in wp-config.php
     * o usa il filtro 'lcc_cookie_debug_mode' per disattivare HttpOnly.
     */
    $debug = defined('LCC_COOKIE_DEBUG') && LCC_COOKIE_DEBUG;
    return (bool) apply_filters('lcc_cookie_debug_mode', $debug);
  }

  private static function persist_cookie_consent(array $map) : void {
    $allowed = ['functional', 'preferences', 'statistics-anonymous', 'statistics', 'marketing'];
    $payload = [];
    foreach ($allowed as $key) {
      if (isset($map[$key])) {
        $payload[$key] = $map[$key] === 'allow' ? 'allow' : 'deny';
      }
    }

    if (empty($payload)) return;

    $json = wp_json_encode($payload);
    if (!$json) return;

    $encoded = base64_encode($json);
    $expire = time() + YEAR_IN_SECONDS;
    $path = defined('COOKIEPATH') ? COOKIEPATH : '/';
    $domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';
    $secure = is_ssl();
    $httponly = true;

    $cookie_args = [
      'expires' => $expire,
      'path' => $path ?: '/',
      'secure' => $secure,
      'httponly' => self::is_cookie_debug_mode() ? false : $httponly,
      'samesite' => 'Lax',
    ];

    if (!empty($domain)) {
      $cookie_args['domain'] = $domain;
    }

    setcookie('lcc_consent', $encoded, $cookie_args);

    $_COOKIE['lcc_consent'] = $encoded;
  }

  private static function is_decision_made() : bool {
    // Decisione fatta quando almeno preferences/statistics-anonymous/statistics/marketing sono settati allow o deny.
    // In opt-in questo distingue “non scelto” da “rifiutato”.
    $cats = ['preferences', 'statistics-anonymous', 'statistics', 'marketing'];
    $seen = 0;
    foreach ($cats as $c) {
      $v = self::get_consent_value($c);
      if ($v === 'allow' || $v === 'deny') $seen++;
    }
    return $seen > 0;
  }

  private function get_localized_consent_state() : array {
    $cats = ['preferences', 'statistics-anonymous', 'statistics', 'marketing'];
    $state = [];

    foreach ($cats as $cat) {
      $state[$cat] = self::get_consent_value($cat) === 'allow';
    }

    if (!empty($state['statistics'])) {
      $state['statistics-anonymous'] = true;
    }

    return [
      'categories' => $state,
      'decisionMade' => self::is_decision_made(),
    ];
  }

  private static function normalize_cookie_category($value) : string {
    $value = sanitize_key((string) $value);
    if ($value === 'statistics_anonymous') {
      $value = 'statistics-anonymous';
    }

    $allowed = ['functional', 'preferences', 'statistics-anonymous', 'statistics', 'marketing'];
    return in_array($value, $allowed, true) ? $value : '';
  }

  private function get_cookie_details() : array {
    $groups = [
      'functional' => [],
      'preferences' => [],
      'statistics-anonymous' => [],
      'statistics' => [],
      'marketing' => [],
    ];

    $items = function_exists('wp_get_cookie_info') ? wp_get_cookie_info() : self::$fallback_cookie_store;
    if (!is_array($items)) {
      return $groups;
    }

    foreach ($items as $row) {
      if (!is_array($row)) continue;

      $cat = self::normalize_cookie_category($row['category'] ?? ($row['purpose'] ?? ''));
      if (!$cat) continue;

      $groups[$cat][] = [
        'name' => (string) ($row['cookie_name'] ?? ($row['name'] ?? '')),
        'service' => (string) ($row['service'] ?? ($row['cookie_service'] ?? '')),
        'duration' => (string) ($row['duration'] ?? ($row['cookie_duration'] ?? '')),
        'description' => (string) ($row['description'] ?? ($row['cookie_description'] ?? '')),
      ];
    }

    return $groups;
  }

  public function render_banner() {
    if ($this->banner_rendered) return;
    $this->banner_rendered = true;

    $arrow_svg = '';
    if (function_exists('icon_1')) {
      $arrow_svg = icon_1('18', '#101010', 'lcc-cookie-arrow', 0, null);
    }
    $settings_groups = [
      [
        'key' => 'functional',
        'label' => esc_html__('Necessari', 'lcc'),
        'supports_toggle' => false,
      ],
      [
        'key' => 'preferences',
        'label' => esc_html__('Preferenze', 'lcc'),
        'supports_toggle' => true,
      ],
      [
        'key' => 'statistics-anonymous',
        'label' => esc_html__('Analytics anonimi', 'lcc'),
        'supports_toggle' => true,
      ],
      [
        'key' => 'statistics',
        'label' => esc_html__('Analytics', 'lcc'),
        'supports_toggle' => true,
      ],
      [
        'key' => 'marketing',
        'label' => esc_html__('Marketing', 'lcc'),
        'supports_toggle' => true,
      ],
    ];

    $cookie_policy_url = esc_url(home_url('/cookie-policy/'));

    $template_candidates = [
      get_stylesheet_directory() . '/template-parts/lcc-banner.php',
      plugin_dir_path(__FILE__) . 'templates/lcc-banner.php',
    ];

    $template_path = '';
    foreach ($template_candidates as $candidate) {
      if (file_exists($candidate)) {
        $template_path = $candidate;
        break;
      }
    }

    if ($template_path) {
      include $template_path;
      return;
    }
  }

  public function ajax_set_consent() {
    $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
    $nonce_ok = $nonce ? wp_verify_nonce($nonce, 'lcc_nonce') : false;

    if (!$nonce_ok) {
      if (is_user_logged_in()) {
        wp_send_json_error(['error' => 'invalid_nonce'], 403);
      }

      $allow_missing = (bool) apply_filters('lcc_allow_missing_nonce', true);
      if (!$allow_missing) {
        wp_send_json_error(['error' => 'invalid_nonce'], 403);
      }
    }

    $preferences = isset($_POST['preferences']) ? (int) wp_unslash($_POST['preferences']) : 0;
    $statsAnon   = isset($_POST['statistics_anonymous']) ? (int) wp_unslash($_POST['statistics_anonymous']) : 0;
    $statistics  = isset($_POST['statistics']) ? (int) wp_unslash($_POST['statistics']) : 0;
    $marketing   = isset($_POST['marketing']) ? (int) wp_unslash($_POST['marketing']) : 0;

    // Se dai consenso pieno statistics, ha senso consentire anche statistics-anonymous.
    if ($statistics) $statsAnon = 1;

    $map = [
      'functional'           => 'allow',
      'preferences'          => $preferences ? 'allow' : 'deny',
      'statistics-anonymous' => $statsAnon ? 'allow' : 'deny',
      'statistics'           => $statistics ? 'allow' : 'deny',
      'marketing'            => $marketing ? 'allow' : 'deny',
    ];

    if (function_exists('wp_set_consent')) {
      foreach ($map as $cat => $val) {
        wp_set_consent($cat, $val); // allow|deny :contentReference[oaicite:8]{index=8}
      }
      wp_send_json_success(['consent' => $map]);
    }

    self::persist_cookie_consent($map);
    wp_send_json_success(['consent' => $map, 'fallback' => true]);
  }

  public function register_cookies_info() {
    if (!function_exists('wp_add_cookie_info')) return;

    $entries = $this->read_cookie_entries_from_registry();
    foreach ($entries as $entry) {
      $name = (string) ($entry['name'] ?? '');
      $service = (string) ($entry['service'] ?? '');
      $category = self::normalize_registry_category($entry['category'] ?? '');
      if ($name === '' || $service === '' || $category === '') {
        continue;
      }

      $duration = (string) ($entry['duration'] ?? '');
      $description = (string) ($entry['description'] ?? '');
      $first_party = array_key_exists('first_party', $entry) && $entry['first_party'] !== null
        ? (bool) $entry['first_party']
        : !self::is_third_party_service($service);

      wp_add_cookie_info(
        $name,
        $service,
        $category,
        $duration,
        $description,
        $first_party,
        false,
        false
      );
    }

    /**
     * Hook per registrare cookie di terze parti o custom.
     * Esempio:
     * add_action('lcc_register_cookies', function() {
     *   wp_add_cookie_info('ga_*','Google Analytics','statistics',__('2 anni'),__('Misurazione traffico.'),false,false,false);
     * });
     */
    do_action('lcc_register_cookies');
  }

  public function shortcode_cookie_table($atts) {
    if (!function_exists('wp_get_cookie_info')) {
      return '<p>' . esc_html__('WP Consent API non disponibile.', 'lcc') . '</p>';
    }

    $items = wp_get_cookie_info();
    if (!is_array($items) || empty($items)) {
      return '<p>' . esc_html__('Nessun cookie registrato.', 'lcc') . '</p>';
    }

    // Struttura può variare tra versioni. Render “safe” con fallback.
    $out  = '<div class="lcc-cookie-table-wrap">';
    $out .= '<table class="lcc-cookie-table">';
    $out .= '<thead><tr>';
    $out .= '<th>' . esc_html__('Cookie', 'lcc') . '</th>';
    $out .= '<th>' . esc_html__('Servizio', 'lcc') . '</th>';
    $out .= '<th>' . esc_html__('Categoria', 'lcc') . '</th>';
    $out .= '<th>' . esc_html__('Durata', 'lcc') . '</th>';
    $out .= '<th>' . esc_html__('Descrizione', 'lcc') . '</th>';
    $out .= '</tr></thead><tbody>';

    foreach ($items as $row) {
      if (!is_array($row)) continue;

      $name = $row['cookie_name'] ?? ($row['name'] ?? '');
      $service = $row['service'] ?? ($row['cookie_service'] ?? '');
      $cat = $row['category'] ?? ($row['purpose'] ?? '');
      $duration = $row['duration'] ?? ($row['cookie_duration'] ?? '');
      $desc = $row['description'] ?? ($row['cookie_description'] ?? '');

      $out .= '<tr>';
      $out .= '<td>' . esc_html((string)$name) . '</td>';
      $out .= '<td>' . esc_html((string)$service) . '</td>';
      $out .= '<td>' . esc_html((string)$cat) . '</td>';
      $out .= '<td>' . esc_html((string)$duration) . '</td>';
      $out .= '<td>' . esc_html((string)$desc) . '</td>';
      $out .= '</tr>';
    }

    $out .= '</tbody></table></div>';
    return $out;
  }

  public static function fallback_add_cookie_info($name, $service, $category, $duration, $description, $first_party = false, $personal = false, $non_eu = false) : void {
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

  public static function fallback_get_cookie_info() : array {
    return self::$fallback_cookie_store;
  }
}

new Logical_Cookie_Consent_WPCA();

if (!function_exists('wp_add_cookie_info')) {
  function wp_add_cookie_info($name, $service, $category, $duration, $description, $first_party = false, $personal = false, $non_eu = false) {
    Logical_Cookie_Consent_WPCA::fallback_add_cookie_info($name, $service, $category, $duration, $description, $first_party, $personal, $non_eu);
    return true;
  }
}

if (!function_exists('wp_get_cookie_info')) {
  function wp_get_cookie_info() {
    return Logical_Cookie_Consent_WPCA::fallback_get_cookie_info();
  }
}

/**
 * Helper: stampa uno script bloccato finché non c’è consenso.
 * category: functional | preferences | statistics-anonymous | statistics | marketing
 */
function lcc_blocked_script(string $category, string $inline_js) : void {
  $category = sanitize_key($category);
  echo '<script type="text/plain" data-wp-consent-category="' . esc_attr($category) . '">' . $inline_js . '</script>';
}

if (!function_exists('lcc_render_consent_embed')) {
  function lcc_render_consent_embed($provider_key, $payload, $message = '', $button_label = '') {
    if (!$payload) return '';

    $provider_key = sanitize_key($provider_key ?: 'generic');
    $message = $message ?: __('Per visualizzare questo contenuto devi accettare i cookie di marketing.', 'lcc');
    $button_label = $button_label ?: __('Modifica impostazioni', 'lcc');

    ob_start();
    ?>
    <div class="pap-consent-embed pap-consent-embed--<?php echo esc_attr($provider_key); ?>" data-pap-embed="<?php echo esc_attr($provider_key); ?>" data-pap-blocked="marketing" data-pap-embed-html="<?php echo esc_attr($payload); ?>">
      <div class="pap-consent-embed__placeholder h-100 p-3 bg-gray border border-2 border-black" role="status">
        <p class="paragraph regular text-black text-base"><?php echo esc_html($message); ?></p>
        <button type="button" class="lcc-btn lcc-primary paragraph bold text-white text-sm text-uppercase" data-lcc-open-settings="1"><?php echo esc_html($button_label); ?></button>
      </div>
      <div class="pap-consent-embed__body" hidden aria-live="polite"></div>
    </div>
    <?php
    return ob_get_clean();
  }
}
