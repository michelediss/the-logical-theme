<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

$section_class   = 'pap-privacy-policy__section mb-4';
$title_class     = 'heading text-lg text-uppercase text-secondary mb-3';
$paragraph_class = 'paragraph text-base text-gray mb-2';
$link_class      = 'text-primary paragraph bold';
$titolare_item_class = (string) apply_filters('pap_privacy_policy_titolare_class', 'paragraph bold text-base text-primary');

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

// Recupera i dati del titolare
$dati_titolare = get_field('dati_del_titolare');
$nome = '';
$cognome = '';
$email = '';
$telefono = '';
$indirizzo = '';
if ($dati_titolare) {
    $nome = $dati_titolare['nome'] ?? '';
    $cognome = $dati_titolare['cognome'] ?? '';
    $email = $dati_titolare['indirizzo_email'] ?? '';
    $telefono = $dati_titolare['numero_di_telefono'] ?? '';
    $indirizzo = $dati_titolare['indirizzo'] ?? '';
}

$contact_email = $admin_email ?: $email;
$titolare_wrapper_class = $paragraph_class;
?>

<main id="pap-privacy-policy" class="pap-privacy-policy">
    <div class="bg-light py-5">
        <div class="container px-3 px-lg-0">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-9 col-xl-7">
                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h1 class="<?php echo esc_attr($title_class); ?> text-xl">Informativa sulla privacy</h1>
                        <div class="divider bg-primary rounded-pill my-3"></div>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">1. Titolare del trattamento</h2>
                        <p class="<?php echo esc_attr($titolare_wrapper_class); ?>">
                            Il titolare del trattamento dei dati personali raccolti su <?php echo esc_html($site_domain); ?> è:<br>
                        <div class="pt-1"></div>
                        <span class="<?php echo esc_attr($titolare_item_class); ?>"><?php echo esc_html($nome . ' ' . $cognome); ?></span><br>
                        <span class="<?php echo esc_attr($titolare_item_class); ?>"><?php echo esc_html($contact_email); ?></span> <br>
                        <span class="<?php echo esc_attr($titolare_item_class); ?>"><?php echo nl2br(esc_html($indirizzo)); ?></span><br>
                        </p>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">2. Dati personali raccolti</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">
                            Attraverso il modulo di firma, raccogliamo i seguenti dati personali: nome, cognome, data di nascita, paese e città di nascita, luogo di residenza, tipo e numero di documento d’identità, email e firma.
                        </p>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">3. Finalità del trattamento dei dati</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">
                            I dati sono utilizzati per autenticare la firma e verificarne la validità per l’invio della petizione, per comunicazioni legate alla petizione e per conformità legale ove necessario.
                        </p>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">4. Base giuridica del trattamento</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">
                            Il trattamento dei dati è lecito in quanto basato sul consenso esplicito dell’utente e sull’adempimento di obblighi legali.
                        </p>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">5. Modalità di trattamento</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">
                            I dati personali sono trattati con strumenti elettronici e manuali, adottando misure di sicurezza per proteggere i dati da accessi non autorizzati.
                        </p>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">6. Conservazione dei dati</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">
                            I dati personali sono conservati fino alla conclusione della petizione e successivamente per un periodo di 2 anni, per finalità di verifica.
                        </p>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">7. Divulgazione e trasferimento dei dati</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">
                            I dati possono essere condivisi con autorità competenti e provider di servizi tecnici per il funzionamento del sito, garantendo la protezione dei dati.
                        </p>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">8. Diritti dell’utente</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">
                            L’utente ha il diritto di accedere, rettificare, cancellare i propri dati, limitare il trattamento, opporsi e revocare il consenso, contattandoci via email a <strong><a class="<?php echo esc_attr($link_class); ?> text-decoration-none" href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a>.</strong>
                        </p>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">9. Modifiche alla privacy policy</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">
                            Ci riserviamo il diritto di modificare questa Privacy Policy. Le modifiche saranno pubblicate su questa pagina.
                        </p>
                    </section>

                    <section class="<?php echo esc_attr($section_class); ?>">
                        <h2 class="<?php echo esc_attr($title_class); ?>">10. Contatti</h2>
                        <p class="<?php echo esc_attr($paragraph_class); ?>">
                            Per ulteriori informazioni, contattaci all'indirizzo <strong><a class="<?php echo esc_attr($link_class); ?> text-decoration-none" href="mailto:<?php echo esc_attr($contact_email); ?>"><?php echo esc_html($contact_email); ?></a></strong>
                        </p>
                    </section>
                </div>
            </div>
        </div>
    </div>
</main>

<?php get_footer(); ?>