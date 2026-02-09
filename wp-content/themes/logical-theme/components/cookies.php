<?php
/**
 * Cookie registrations for LCC banner (based on Cookie Policy).
 */

if (!defined('ABSPATH')) {
    exit;
}

$pap_cookie_entries = [
    [
        'name'        => 'lcc_consent',
        'service'     => 'Logical Cookie Consent',
        'category'    => 'functional',
        'duration'    => __('1 anno', 'pap'),
        'description' => __('Memorizza lo stato di consenso per ogni categoria di cookie.', 'pap'),
    ],
    [
        'name'        => 'wordpress_[hash]',
        'service'     => 'WordPress',
        'category'    => 'functional',
        'duration'    => __('Sessione', 'pap'),
        'description' => __('Mantiene la sessione autenticata nell’area wp-admin.', 'pap'),
    ],
    [
        'name'        => 'wordpress_[hash]',
        'service'     => 'WordPress',
        'category'    => 'functional',
        'duration'    => __('Sessione', 'pap'),
        'description' => __('Mantiene la sessione autenticata durante l’uso dei plugin.', 'pap'),
    ],
    [
        'name'        => 'wordpress_logged_in_[hash]',
        'service'     => 'WordPress',
        'category'    => 'functional',
        'duration'    => __('Sessione', 'pap'),
        'description' => __('Memorizza l’utente autenticato dopo il login.', 'pap'),
    ],
    [
        'name'        => 'wordpress_test_cookie',
        'service'     => 'WordPress',
        'category'    => 'functional',
        'duration'    => __('Sessione', 'pap'),
        'description' => __('Verifica se il browser accetta i cookie.', 'pap'),
    ],
    [
        'name'        => 'wp_lang',
        'service'     => 'WordPress',
        'category'    => 'functional',
        'duration'    => __('Sessione', 'pap'),
        'description' => __('Salva la lingua corrente dell’interfaccia.', 'pap'),
    ],
    [
        'name'        => 'wp-settings-1',
        'service'     => 'WordPress',
        'category'    => 'functional',
        'duration'    => __('1 anno', 'pap'),
        'description' => __('Conserva le preferenze personali della dashboard.', 'pap'),
    ],
    [
        'name'        => 'wp-settings-time-1',
        'service'     => 'WordPress',
        'category'    => 'functional',
        'duration'    => __('1 anno', 'pap'),
        'description' => __('Registra il timestamp di aggiornamento delle impostazioni utente.', 'pap'),
    ],
    [
        'name'        => 'youtube_*',
        'service'     => 'YouTube',
        'category'    => 'tereze parti',
        'duration'    => __('Fino a 2 anni', 'pap'),
        'description' => __('Memorizza preferenze di riproduzione e traccia le visualizzazioni dei video incorporati.', 'pap'),
    ],
    [
        'name'        => 'spotify_*',
        'service'     => 'Spotify',
        'category'    => 'tereze parti',
        'duration'    => __('Sessione / 1 anno', 'pap'),
        'description' => __('Permette a Spotify di caricare player embed e raccogliere statistiche sull’ascolto.', 'pap'),
    ],
    [
        'name'        => 'x.com_*',
        'service'     => 'X (Twitter)',
        'category'    => 'tereze parti',
        'duration'    => __('Fino a 2 anni', 'pap'),
        'description' => __('Traccia interazioni con i tweet incorporati e personalizza i contenuti pubblicitari.', 'pap'),
    ],
    [
        'name'        => 'facebook_*',
        'service'     => 'Facebook',
        'category'    => 'tereze parti',
        'duration'    => __('Fino a 2 anni', 'pap'),
        'description' => __('Cookie usati da Facebook per misurare e personalizzare i contenuti degli embed.', 'pap'),
    ],
    [
        'name'        => 'instagram_*',
        'service'     => 'Instagram',
        'category'    => 'tereze parti',
        'duration'    => __('Sessione / 1 anno', 'pap'),
        'description' => __('Gestisce il caricamento dei post e delle storie incorporati e raccoglie metriche.', 'pap'),
    ],
    [
        'name'        => 'tiktok_*',
        'service'     => 'TikTok',
        'category'    => 'tereze parti',
        'duration'    => __('Fino a 13 mesi', 'pap'),
        'description' => __('Serve a TikTok per riprodurre i video embed e analizzare l’engagement.', 'pap'),
    ],
];

add_action('lcc_register_cookies', function () use ($pap_cookie_entries) {
    if (!function_exists('wp_add_cookie_info')) {
        return;
    }

    foreach ($pap_cookie_entries as $entry) {
        $is_third_party = in_array(
            $entry['service'],
            ['YouTube', 'Spotify', 'X (Twitter)', 'Facebook', 'Instagram', 'TikTok'],
            true
        );

        wp_add_cookie_info(
            $entry['name'],
            $entry['service'],
            $entry['category'],
            $entry['duration'],
            $entry['description'],
            $is_third_party ? false : true,
            false,
            false
        );
    }
});
