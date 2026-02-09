<?php
// Disabilita gli stili frontend di Gutenberg
function disable_gutenberg_wp_enqueue_scripts() {
    wp_dequeue_style('wp-block-library'); // Stili dei blocchi
    wp_dequeue_style('wp-block-library-theme'); // Stili dei blocchi del tema
    wp_dequeue_style('global-styles'); // Stili globali di Gutenberg
    wp_dequeue_style('classic-theme-styles'); // Stili del tema classico
}
add_action('wp_enqueue_scripts', 'disable_gutenberg_wp_enqueue_scripts', 100);

// Disabilita Gutenberg come editor per tutti i tipi di post
function disable_gutenberg_editor_by_default($can_edit, $post_type) {
    return false; // Impedisce l'uso di Gutenberg su tutti i tipi di post
}
add_filter('use_block_editor_for_post_type', 'disable_gutenberg_editor_by_default', 10, 2);

// Disabilita ulteriori script e funzionalità legate a Gutenberg nel backend
function disable_gutenberg_admin_scripts() {
    wp_dequeue_script('wp-edit-post'); // Rimuove script di editing dei blocchi
}
add_action('admin_enqueue_scripts', 'disable_gutenberg_admin_scripts', 100);

// Disabilita i CSS degli widget di blocchi in WP 5.8 e successivi
function disable_block_widgets() {
    remove_theme_support('widgets-block-editor'); // Rimuove il supporto ai widget basati su blocchi
}
add_action('after_setup_theme', 'disable_block_widgets');

// Disabilita il CSS degli SVG utilizzati dai blocchi Gutenberg
function disable_svg_block_styles() {
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
}
add_action('init', 'disable_svg_block_styles');

?>
