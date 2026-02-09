<?php
/**
 * Plugin Name: Logical Cookie Consent
 * Description: Banner cookie CMP opt-in con WP Consent API + preferenze riapribili + tabella cookie.
 * Version: 1.4.0
 * Author: Michele Paolino
 * Author URI: https://michelepaolino.com
 * Text Domain: lcc
 */

if (!defined('ABSPATH')) exit;

final class Logical_Cookie_Consent_WPCA {
  const VER = '1.4.0';
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
  }

  public function load_textdomain() {
    load_plugin_textdomain('lcc', false, dirname(plugin_basename(__FILE__)) . '/languages');
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

    // Cookie “tecnici” esempio. Aggiungi i tuoi qui o via hook.
    // Firma mostrata nell’esempio ufficiale :contentReference[oaicite:9]{index=9}
    wp_add_cookie_info(
      'wordpress_logged_in_*',
      'WordPress',
      'functional',
      __('Sessione', 'lcc'),
      __('Gestisce login e sessione utente.', 'lcc'),
      true,
      false,
      false
    );

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
