<?php
/**
 * Aggiunge la classe del ruolo utente al body dell'admin
 */
function add_user_role_class_to_admin_body() {
    if ( is_user_logged_in() ) {
        add_filter('admin_body_class', 'add_admin_body_classes');
    }
}
add_action('init', 'add_user_role_class_to_admin_body');

/**
 * Aggiunge il ruolo utente e l'ID utente alle classi del body nell'admin
 *
 * @param string $classes Classi esistenti del body nell'admin.
 * @return string Classi modificate del body nell'admin.
 */
function add_admin_body_classes( $classes ) {
    $current_user = wp_get_current_user();

    if ( ! empty( $current_user->roles ) ) {
        $user_role = esc_attr( array_shift( $current_user->roles ) );
        $user_ID = esc_attr( $current_user->ID );

        // Aggiunge le nuove classi alle classi esistenti
        $classes .= ' ' . $user_role . ' user-id-' . $user_ID;
    }

    return $classes;
}

// Carica lo stile CSS per l'admin
function carica_admin_css() {
    wp_enqueue_style( 'custom-admin-css', get_template_directory_uri() . '/assets/css/admin-style.css' );
}
add_action( 'admin_enqueue_scripts', 'carica_admin_css' );

