<?php get_header(); ?>

<?php echo spacer(5); ?>

<div class="container pb-5">
    <div class="row d-flex justify-content-center">
        <div class="col-12 col-xl-10 col-3xl-8 col-5xl-7">
            <div class="post-content">
                <?php

                $privacy_text = get_field('testo');

                if (!empty($privacy_text)) {
                    echo wp_kses_post($privacy_text);
                } else {
                    return;
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
// Template per la pagina Privacy Policy
if (is_page('privacy-policy')) {
    get_header(); // Include l'header del tema

    // Recupera i dati dinamici
    $site_url_full = get_site_url(); // URL completo del sito
    $site_url = preg_replace('/^https?:\\/\\/(www\\.)?/', '', $site_url_full); // Rimuovi https://, http://, www.

    $admin_email = get_option('admin_email'); // Email dell'amministratore
    $acf_data = get_field('dati_informative'); // Dati ACF

    // Estrai i campi necessari
    $intestatario = $acf_data['intestatario'] ?? '[Nome Azienda o Titolare]';
    $indirizzo_1 = $acf_data['indirizzo']['indirizzo_1'] ?? '';
    $indirizzo_2 = $acf_data['indirizzo']['indirizzo_2'] ?? '';
    $indirizzo = trim($indirizzo_1 . ', ' . $indirizzo_2, ', ');

    // Data di ultima modifica
    $last_modified = get_the_modified_date('d/m/Y', get_the_ID());

    // Prepara i dati per il JS
    $data = [
        'siteUrl' => $site_url,
        'adminEmail' => $admin_email,
        'intestatario' => $intestatario,
        'indirizzo' => $indirizzo,
        'lastModified' => $last_modified,
    ];
    ?>

    <div class="post-content">
        <!-- Il contenuto dinamico del sito viene generato qui -->
        <?php the_content(); ?>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Dati dinamici generati da PHP
        const data = <?php echo json_encode($data); ?>;

        // Definisci le sostituzioni
        const replacements = {
            '[Nome del sito]': `<span class="bold">${data.siteUrl}</span>`,
            '[Nome Azienda o Titolare]': `<span class="bold">${data.intestatario}</span>`,
            '[Indirizzo]': `<span class="bold">${data.indirizzo}</span>`,
            '[Email]': `<span class="bold">${data.adminEmail}</span>`,
            '[Data]': `<span class="bold">${data.lastModified}</span>`,
        };

        // Funzione per sostituire i segnaposto nel DOM
        const replacePlaceholders = (node) => {
            if (node.nodeType === Node.TEXT_NODE) {
                let text = node.nodeValue;
                let hasMatch = false;

                for (const [placeholder, value] of Object.entries(replacements)) {
                    if (text.includes(placeholder)) {
                        text = text.replaceAll(placeholder, value);
                        hasMatch = true;
                    }
                }

                if (hasMatch) {
                    const spanWrapper = document.createElement('span');
                    spanWrapper.innerHTML = text;
                    node.parentNode.replaceChild(spanWrapper, node);
                }
            } else {
                node.childNodes.forEach(replacePlaceholders);
            }
        };

        // Applica le sostituzioni al contenuto della pagina
        replacePlaceholders(document.body);
    });
    </script>

    <?php
    get_footer(); // Include il footer del tema
}
?>
