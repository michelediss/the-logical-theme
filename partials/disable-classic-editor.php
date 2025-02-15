<?php
/**
 * Disabilita l'editor classico per tutte le pagine (non tocca i campi ACF).
 */

function logical_disable_classic_editor_globally() {
    // Rimuove il supporto "editor" (il classico WYSIWYG) per il post type "page"
    remove_post_type_support('page', 'editor');
}
// Hook su 'admin_init' o 'init'. Entrambi funzionano in questo caso,
// ma 'admin_init' viene eseguito solo in area admin, riducendo possibili conflitti in frontend.
add_action('admin_init', 'logical_disable_classic_editor_globally');
