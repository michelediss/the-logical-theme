<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();


$section_class   = 'pap-privacy-policy__section mb-4';
$title_class     = 'heading text-lg text-uppercase text-secondary mb-3';
$paragraph_class = 'paragraph text-base text-gray mb-2';
$link_class      = 'text-primary bold';

$admin_email = sanitize_email((string) get_option('admin_email'));
$site_domain = '';
$domain_candidates = array_filter(
    [
        defined('WP_HOME') ? WP_HOME : '',
        defined('WP_SITEURL') ? WP_SITEURL : '',
        home_url(),
        site_url(),
    ]
);
foreach ($domain_candidates as $candidate) {
    $host = wp_parse_url($candidate, PHP_URL_HOST);
    if ($host && strpos($host, '.') !== false) {
        $site_domain = $host;
        break;
    }
}
if (!$site_domain) {
    $site_domain = wp_parse_url(home_url(), PHP_URL_HOST);
}
if (!$site_domain) {
    $site_domain = preg_replace('~^https?://~', '', home_url());
    $site_domain = trim($site_domain, '/');
    $site_domain = strtok($site_domain, '/');
}

$render_list = static function (array $items) {
    if (empty($items)) {
        return;
    }
    echo '<ul class="list-unstyled m-0 p-0">';
    foreach ($items as $item) {
        $text = is_string($item) ? trim($item) : '';
        if ($text === '') {
            continue;
        }
        echo '<li class="paragraph  text-base text-gray mb-2">';
        echo '<span>' . $text . '</span>'; // Removed esc_html to allow HTML links inside render_list items if needed, though mostly text here.
        echo '</li>';
    }
    echo '</ul>';
};
?>

<main id="pap-cookie-policy" class="pap-privacy-policy">
    <div class="bg-light py-5">
        <div class="container px-3 px-lg-0">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-9 col-xl-7">
                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h1 class="<?php echo esc_attr($title_class); ?> text-xl">Informativa sull'utilizzo dei cookie</h1>
                        <div class="divider bg-primary rounded-pill my-3"></div>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">Ultimo aggiornamento: <span class="paragraph bold text-primary">14 Dicembre 2025</span></p>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">Questa Cookie Policy spiega cosa sono i cookie, come li utilizza il sito <?php echo esc_html($site_domain); ?> e come l'utente può gestirli.</p>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">1. Cosa sono i cookie?</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">I cookie sono piccoli file di testo che i siti visitati dagli utenti inviano ai loro terminali (solitamente al browser), dove vengono memorizzati per essere poi ritrasmessi agli stessi siti alla visita successiva. I cookie hanno diverse funzioni: permettono di navigare in modo efficiente tra le pagine, ricordano le preferenze dell'utente e, in generale, migliorano l'esperienza di navigazione.</p>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">2. Tipologie di cookie utilizzati</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">Questo sito utilizza due macro-categorie di cookie:</p>

                        <div class="border-top border-2 border-black pt-4 mt-3">
                            <h3 class="heading bold-italic text-base text-uppercase text-secondary mb-2">A. Cookie Tecnici (Strettamente necessari e di Funzionalità)</h3>
                            <p class="<?php echo esc_attr($paragraph_class); ?>">Questi cookie sono indispensabili per il corretto funzionamento del sito o per salvare le preferenze di navigazione dell'utente (es. lingua, login). Per l'installazione di tali cookie non è richiesto il preventivo consenso dell'utente, ma solo l'obbligo di informativa.</p>
                            <p class="<?php echo esc_attr($paragraph_class); ?>">In base ai dati rilevati, utilizziamo i seguenti cookie tecnici:</p>
                            <div class="border border-2 border-black p-0">
                                <?php echo do_shortcode('[lcc_cookie_table]'); ?>
                            </div>
                            <p class="mt-2 text-sm <?php echo esc_attr($paragraph_class); ?>">Nota: i cookie relativi all'accesso (wordpress_logged_in, wp-settings) vengono installati solo se si effettua il login all'area riservata/amministrativa del sito.</p>
                        </div>

                        <div class="border-top border-2 border-black pt-4 mt-4">
                            <h3 class="heading bold-italic text-base text-uppercase text-secondary mb-2">B. Cookie di Terze Parti (Social Embed e Media)</h3>
                            <p class="<?php echo esc_attr($paragraph_class); ?>">Il sito <?php echo esc_html($site_domain); ?> incorpora contenuti multimediali da piattaforme esterne (Social Network, piattaforme video e audio) per arricchire l'informazione politica e culturale.</p>
                            <p class="<?php echo esc_attr($paragraph_class); ?>">Quando visualizzi questi contenuti (es. guardi un video YouTube, un TikTok o ascolti una traccia Spotify incorporata nella pagina), queste terze parti potrebbero installare cookie di profilazione o tracciamento sul tuo dispositivo.</p>
                            <p class="<?php echo esc_attr($paragraph_class); ?>">Questi cookie vengono installati solo se l'utente accetta i cookie nel banner iniziale o interagisce direttamente con il contenuto.</p>
                            <p class="<?php echo esc_attr($paragraph_class); ?>">Le piattaforme utilizzate sono:</p>

                            <ul class="list-unstyled m-0 p-0">
                                <li class="paragraph  text-base text-black mb-2">
                                    <span>
                                        <span class="heading text-base text-uppercase text-secondary">YouTube (Google):</span>
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://policies.google.com/privacy?hl=it'); ?>" target="_blank" rel="noopener noreferrer">Privacy Policy</a>;
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://policies.google.com/technologies/cookies?hl=it'); ?>" target="_blank" rel="noopener noreferrer">Cookie Policy</a>
                                    </span>
                                </li>

                                <li class="paragraph  text-base text-black mb-2">
                                    <span>
                                        <span class="heading text-base text-uppercase text-secondary">Facebook (Meta):</span>
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://www.facebook.com/privacy/policy'); ?>" target="_blank" rel="noopener noreferrer">Informativa Privacy</a>;
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://www.facebook.com/privacy/policies/cookies'); ?>" target="_blank" rel="noopener noreferrer">Normativa sui Cookie</a>
                                    </span>
                                </li>
                                <li class="paragraph  text-base text-black mb-2">
                                    <span>
                                        <span class="heading text-base text-uppercase text-secondary">Instagram (Meta):</span>
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://privacycenter.instagram.com/policy'); ?>" target="_blank" rel="noopener noreferrer">Informativa Privacy</a>;
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://privacycenter.instagram.com/policies/cookies'); ?>" target="_blank" rel="noopener noreferrer">Cookie Policy</a>
                                    </span>
                                </li>

                                <li class="paragraph  text-base text-black mb-2">
                                    <span>
                                        <span class="heading text-base text-uppercase text-secondary">TikTok:</span>
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://www.tiktok.com/legal/page/eea/privacy-policy/it'); ?>" target="_blank" rel="noopener noreferrer">Informativa Privacy</a>;
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://www.tiktok.com/legal/page/global/cookie-policy/it'); ?>" target="_blank" rel="noopener noreferrer">Cookie Policy</a>
                                    </span>
                                </li>

                                <li class="paragraph  text-base text-black mb-2">
                                    <span>
                                        <span class="heading text-base text-uppercase text-secondary">X (Twitter):</span>
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://twitter.com/it/privacy'); ?>" target="_blank" rel="noopener noreferrer">Privacy Policy</a>;
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://help.twitter.com/it/rules-and-policies/twitter-cookies'); ?>" target="_blank" rel="noopener noreferrer">Uso dei Cookie</a>
                                    </span>
                                </li>

                                <li class="paragraph  text-base text-black mb-2">
                                    <span>
                                        <span class="heading text-base text-uppercase text-secondary">Spotify:</span>
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://www.spotify.com/it/legal/privacy-policy/'); ?>" target="_blank" rel="noopener noreferrer">Informativa Privacy</a>;
                                        <a class="<?php echo esc_attr($link_class); ?>" href="<?php echo esc_url('https://www.spotify.com/it/legal/cookies-policy/'); ?>" target="_blank" rel="noopener noreferrer">Cookie Policy</a>
                                    </span>
                                </li>
                            </ul>

                            <p class="<?php echo esc_attr($paragraph_class); ?>">Poiché il Titolare del sito non ha accesso diretto ai dati raccolti e trattati in autonomia da queste terze parti, si rimanda alle relative informative privacy linkate qui sopra.</p>
                        </div>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">3. Gestione dei Cookie e Consenso</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">Al primo accesso al sito, un banner ti informa dell'utilizzo dei cookie. Hai le seguenti opzioni:</p>
                        <?php
                        $render_list([
                            'Accetta tutto: acconsenti all\'uso di tutti i cookie, inclusi quelli delle piattaforme social.',
                            'Rifiuta / Continua senza accettare: verranno installati solo i cookie tecnici necessari. I contenuti multimediali esterni (video, post social) potrebbero non essere visualizzati o richiedere un clic aggiuntivo per essere attivati.',
                            'Personalizza: puoi scegliere quali categorie di cookie attivare.',
                            'Puoi modificare le tue scelte in qualsiasi momento cliccando sull\'icona delle impostazioni cookie presente in basso a destra su qualsiasi pagina del sito.',
                        ]);
                        ?>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">4. Disabilitare i cookie dal browser</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">In aggiunta alle opzioni fornite dal sito, l'utente può gestire le preferenze sui cookie direttamente dalle impostazioni del proprio browser.</p>
                        <?php
                        $browser_links = [
                            sprintf(
                                '<a class="%s" href="%s" target="_blank" rel="noopener noreferrer">Google Chrome</a>',
                                esc_attr($link_class),
                                esc_url('https://support.google.com/chrome/answer/95647?hl=it')
                            ),
                            sprintf(
                                '<a class="%s" href="%s" target="_blank" rel="noopener noreferrer">Mozilla Firefox</a>',
                                esc_attr($link_class),
                                esc_url('https://support.mozilla.org/it/kb/Gestione%20dei%20cookie')
                            ),
                            sprintf(
                                '<a class="%s" href="%s" target="_blank" rel="noopener noreferrer">Apple Safari</a>',
                                esc_attr($link_class),
                                esc_url('https://support.apple.com/it-it/guide/safari/sfri11471/mac')
                            ),
                            sprintf(
                                '<a class="%s" href="%s" target="_blank" rel="noopener noreferrer">Microsoft Edge</a>',
                                esc_attr($link_class),
                                esc_url('https://support.microsoft.com/it-it/windows/gestire-i-cookie-in-microsoft-edge-visualizzare-consentire-bloccare-eliminare-e-usare-168dab11-0753-043d-7c16-ede5947fc64d')
                            ),
                        ];
                        $render_list($browser_links);
                        ?>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">5. Diritti dell'interessato</h2>
                        <?php if (!empty($admin_email)) : ?>
                            <p class="<?php echo esc_attr($paragraph_class); ?>">L'utente può esercitare in ogni momento i diritti previsti dal GDPR (accesso, rettifica, cancellazione dei dati, ecc.) contattando il Titolare del trattamento all'indirizzo email: <a class=" text-primary text-decoration-none paragraph bold" href="mailto:<?php echo esc_attr($admin_email); ?>"><?php echo esc_html($admin_email); ?></a>.</p>
                        <?php endif; ?>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">Per maggiori informazioni sul trattamento dei dati personali, consulta la nostra <a class="paragraph bold text-decoration-none text-primary" href="<?php echo esc_url(ltc_home_url('/privacy-policy/')); ?>">Privacy Policy</a> completa.</p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>
