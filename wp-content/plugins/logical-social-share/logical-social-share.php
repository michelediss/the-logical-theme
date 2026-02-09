<?php
/**
* Plugin Name: Logical Social Share 
* Description: Social buttons component
* Version: 0.1.0
* Plugin URI: https://github.com/michelediss/the-logical-theme
* Author: Michele Paolino
* Author URI: https://michelepaolino.com
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

// 1. Stili e Script (Caricati sempre se ci sono i pulsanti)
function sss_should_enqueue_assets() {
    $should_enqueue = is_singular();

    return (bool) apply_filters( 'simple_social_share_should_enqueue_assets', $should_enqueue );
}

function sss_enqueue_assets() {
    if ( ! sss_should_enqueue_assets() ) {
        return;
    }

    $version = '0.1.0';

    wp_enqueue_script(
        'logical-social-share-js',
        plugins_url( 'assets/js/logical-social-share.js', __FILE__ ),
        [],
        $version,
        true
    );
}
add_action( 'wp_enqueue_scripts', 'sss_enqueue_assets' );

// 2. Helper Icone SVG
function sss_get_svg($name) {
    $icons = [
        'facebook' => '<svg viewBox="0 0 16 16" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M16 8.049c0-4.446-3.582-8.05-8-8.05C3.58 0-.002 3.603-.002 8.05c0 4.017 2.926 7.347 6.75 7.951v-5.625h-2.03V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258v1.51h2.218l-.354 2.326H9.25V16c3.824-.604 6.75-3.934 6.75-7.951z"/></svg>',
        'twitter'  => '<svg viewBox="0 0 16 16" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M12.6.75h2.454l-5.36 6.142L16 15.25h-4.937l-3.867-5.07-4.425 5.07H.316l5.733-6.57L0 .75h5.063l3.495 4.633L12.601.75Zm-.86 13.028h1.36L4.323 2.145H2.865l8.875 11.633Z"/></svg>',
        'whatsapp' => '<svg viewBox="0 0 16 16" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M13.601 2.326A7.854 7.854 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.933 7.933 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.898 7.898 0 0 0 13.6 2.326zM7.994 14.521a6.573 6.573 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.557 6.557 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592zm3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.729.729 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232z"/></svg>',
        'linkedin' => '<svg viewBox="0 0 16 16" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M0 1.146C0 .513.526 0 1.175 0h13.65C15.474 0 16 .513 16 1.146v13.708c0 .633-.526 1.146-1.175 1.146H1.175C.526 16 0 15.487 0 14.854V1.146zm4.943 12.248V6.169H2.542v7.225h2.401zm-1.2-8.212c.837 0 1.358-.554 1.358-1.248-.015-.709-.52-1.248-1.342-1.248-.822 0-1.359.54-1.359 1.248 0 .694.521 1.248 1.327 1.248h.015zm4.908 8.212V9.359c0-.216.016-.432.08-.586.173-.431.568-.878 1.232-.878.869 0 1.216.662 1.216 1.634v3.865h2.401V9.25c0-2.22-1.184-3.252-2.764-3.252-1.274 0-1.845.7-2.165 1.193v.025h-.016a5.54 5.54 0 0 1 .016-.025V6.169h-2.4c.03.678 0 7.225 0 7.225h2.4z"/></svg>',
        'telegram' => '<svg viewBox="0 0 16 16" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.287 5.906c-.778.324-2.334.994-4.666 2.01-.378.15-.53.298-.486.494.07.318.639.475.875.551 1.12.353 2.76.162 2.924-.04.09-.11.75-1.043.83-1.15.056-.075.127-.087.165-.01.026.052-.39.42-.614.646-.226.225-.395.42-.584.629-.19.208-.05.508.06.677.266.4.524.787.79 1.178.43.63.905.79 1.258.74.39-.055.705-.838.868-1.748.163-.91.565-3.41.652-3.953.016-.1.015-.22-.046-.28-.061-.06-.153-.058-.22-.03-.122.05-1.956.78-2.072.83z"/></svg>',
        'mastodon' => '<svg viewBox="0 0 16 16" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M11.19 12.195c2.016-.24 3.77-1.475 3.99-2.603.348-1.778.32-4.339.32-4.339 0-3.47-2.286-4.488-2.286-4.488C12.062.238 10.083.017 8.027 0h-.05C5.92.017 3.942.238 2.79.765c0 0-2.285 1.017-2.285 4.488l-.002.662c-.004.64-.007 1.35.011 2.091.083 3.394.626 6.74 3.78 7.57 1.454.383 2.703.463 3.709.408 1.823-.1 2.847-.647 2.847-.647l-.615-1.917s-1.306.41-2.767.36c-1.45-.05-2.98-.38-2.98-1.932 0-.09.003-.182.008-.275.945.645 2.27.935 3.71.935 1.056 0 2.031-.121 2.852-.25zM16.002 8.929H14.187V5.594c0-1.217-.53-1.838-1.595-1.838-1.185 0-1.775.76-1.775 2.274v2.793h-1.638V5.998c0-1.514-.59-2.274-1.775-2.274-1.065 0-1.595.62-1.595 1.838v3.335H3.998V5.594c0-2.21 1.197-3.567 3.238-3.567 1.258 0 2.164.55 2.765 1.67.601-1.12 1.507-1.67 2.765-1.67 2.04 0 3.236 1.357 3.236 3.567v3.335z"/></svg>',
        'email'    => '<svg viewBox="0 0 16 16" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V4Zm2-1a1 1 0 0 0-1 1v.217l7 4.2 7-4.2V4a1 1 0 0 0-1-1H2Zm13 2.383-4.708 2.825L15 11.105V5.383Zm-.034 6.876-5.64-3.471L8 9.583l-1.326-.795-5.64 3.47A1 1 0 0 0 2 13h12a1 1 0 0 0 .966-.741ZM1 11.105l4.708-2.897L1 5.383v5.722Z"/></svg>',
        'copy'     => '<svg viewBox="0 0 16 16" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M4.715 6.542 3.343 7.914a3 3 0 1 0 4.243 4.243l1.828-1.829A3 3 0 0 0 8.586 5.5L8 6.086a1.002 1.002 0 0 0-.154.199 2 2 0 0 1 .861 3.337L6.88 11.45a2 2 0 1 1-2.83-2.83l.793-.792a4.018 4.018 0 0 1-.128-1.287z"/><path d="M6.586 4.672A3 3 0 0 0 7.414 9.5l.775-.776a2 2 0 0 1-.896-3.346L9.12 3.55a2 2 0 1 1 2.83 2.83l-.793.792c.112.42.155.855.128 1.287l1.372-1.372a3 3 0 1 0-4.243-4.243L6.586 4.672z"/></svg>'
    ];
    return $icons[$name] ?? '';
}

// 3. Funzione Principale per GENERARE HTML (senza stamparlo)
function sss_get_buttons_html($args = []) {
    $color = '';
    if (is_array($args) && isset($args['color'])) {
        $color = trim((string) $args['color']);
    } elseif (is_string($args)) {
        $color = trim($args);
    }
    $allowed_colors = ['primary', 'secondary', 'gray', 'light'];
    $color_class = '';
    $color_style = '';
    if ($color) {
        if (preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $color)) {
            $color_style = ' style="color: ' . esc_attr($color) . ';"';
        } elseif (in_array($color, $allowed_colors, true)) {
            $color_class = ' text-' . $color;
        }
    }

    $url_raw = get_permalink();
    $url     = urlencode($url_raw);
    $title   = urlencode(get_the_title());
    $title_attr = esc_attr( get_the_title() );
    
    // Links
    $fb = "https://www.facebook.com/sharer/sharer.php?u={$url}";
    $tw = "https://twitter.com/intent/tweet?url={$url}&text={$title}";
    $wa = "https://api.whatsapp.com/send?text={$title}%20{$url}";
    $tg = "https://t.me/share/url?url={$url}&text={$title}";
    $li = "https://www.linkedin.com/sharing/share-offsite/?url={$url}";
    $em = "mailto:?subject={$title}&body=Guarda%20questo:%20{$url}";

    $button_classes = 'sss-btn btn d-inline-flex align-items-center justify-content-center p-1' . $color_class;
    $button_color_style = $color_style;

    return '
    <div class="sss-share-container my-3'.$color_class.'"'.$color_style.'>
        <div class="sss-buttons d-flex flex-wrap gap-2 align-items-center">
            <a href="'.esc_url($fb).'" target="_blank" class="'.$button_classes.' sss-facebook" title="Facebook" aria-label="Condividi su Facebook"'.$button_color_style.'>'.sss_get_svg('facebook').'</a>
            <a href="'.esc_url($tw).'" target="_blank" class="'.$button_classes.' sss-twitter" title="X" aria-label="Condividi su X"'.$button_color_style.'>'.sss_get_svg('twitter').'</a>
            <a href="'.esc_url($wa).'" target="_blank" class="'.$button_classes.' sss-whatsapp" title="WhatsApp" aria-label="Condividi su WhatsApp"'.$button_color_style.'>'.sss_get_svg('whatsapp').'</a>
            <a href="'.esc_url($tg).'" target="_blank" class="'.$button_classes.' sss-telegram" title="Telegram" aria-label="Condividi su Telegram"'.$button_color_style.'>'.sss_get_svg('telegram').'</a>
            <a href="'.esc_url($li).'" target="_blank" class="'.$button_classes.' sss-linkedin" title="LinkedIn" aria-label="Condividi su LinkedIn"'.$button_color_style.'>'.sss_get_svg('linkedin').'</a>
            <a href="#" class="'.$button_classes.' sss-mastodon sss-mastodon-action" data-title="'.$title_attr.'" data-url="'.esc_attr($url_raw).'" title="Mastodon" aria-label="Condividi su Mastodon"'.$button_color_style.'>'.sss_get_svg('mastodon').'</a>
            <a href="'.esc_url($em).'" class="'.$button_classes.' sss-email" title="Email" aria-label="Condividi via Email"'.$button_color_style.'>'.sss_get_svg('email').'</a>
            <a href="#" class="'.$button_classes.' sss-copy sss-copy-action" data-url="'.esc_attr($url_raw).'" title="Copia Link" aria-label="Copia il link"'.$button_color_style.'>'.sss_get_svg('copy').'</a>
        </div>
    </div>';
}

// 4. Shortcode [simple_social_share]
function sss_get_buttons_html_shortcode($atts = []) {
    $args = shortcode_atts(
        [
            'color' => '',
        ],
        $atts,
        'simple_social_share'
    );

    return sss_get_buttons_html($args);
}
add_shortcode('simple_social_share', 'sss_get_buttons_html_shortcode');
