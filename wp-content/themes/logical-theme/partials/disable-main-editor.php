<?php
/**
 * Disable the main text editor for selected pages.
 */
function logical_disable_wysiwyg_for_selected_pages() {
    // Ottieni le pagine selezionate dall'opzione
    $disabled_pages = get_option('disable_editor_pages', []);

    // Ottieni l'ID della pagina corrente
    if ( isset($_GET['post']) ) {
        $current_page_id = intval($_GET['post']);

        // Controlla se l'ID corrente è nelle pagine selezionate
        if ( in_array($current_page_id, $disabled_pages, true) ) {
            remove_post_type_support('page', 'editor');
        }
    }
}
add_action('add_meta_boxes', 'logical_disable_wysiwyg_for_selected_pages', 10);
?>